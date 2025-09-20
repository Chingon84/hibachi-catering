<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\MenuLabel;

class MenuAdminController extends Controller
{
    public function index()
    {
        $path = base_path('config/menu.php');
        if (is_file($path)) {
            try {
                $cfg = include $path; // always read freshest file, bypass config cache
                if (!is_array($cfg)) { $cfg = (array) config('menu'); }
            } catch (\Throwable $e) {
                $cfg = (array) config('menu');
            }
        } else {
            $cfg = (array) config('menu');
        }
        return view('admin.menu_admin', ['cfg' => $cfg]);
    }

    public function update(Request $req)
    {
        $curr = (array) config('menu');
        $items = (array) $req->input('items', []); // [cat => [ [key,name,desc,price], ... ]]
        $out = [];
        $errors = [];
        $seen = [];
        foreach ($items as $cat => $rows) {
            $catName = trim((string) $cat);
            if ($catName === '') { $catName = 'Uncategorized'; }
            $out[$catName] = [];
            foreach ((array) $rows as $row) {
                $key = trim((string) ($row['key'] ?? ''));
                $name = MenuLabel::standardizeText(trim((string) ($row['name'] ?? '')));
                // Auto-generate key from name if key is empty but name provided
                if ($key === '' && $name !== '') {
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));
                    if ($slug === '') { $slug = 'item'; }
                    $base = $slug; $n = 1;
                    while (isset($seen[$catName][$slug])) { $slug = $base . '-' . (++$n); }
                    $key = $slug;
                }
                if ($key === '' && $name === '') { continue; }
                if ($name === '') { $name = MenuLabel::standardizeText($key); }
                $priceRaw = $row['price'] ?? 0;
                $desc = MenuLabel::standardizeText(trim((string) ($row['desc'] ?? '')));
                if (!is_numeric($priceRaw) || (float)$priceRaw < 0) {
                    $errors[] = "Invalid price for {$catName}/{$key}";
                    $priceRaw = 0;
                }
                $price = (float) $priceRaw;

                if (isset($seen[$catName][$key])) {
                    $errors[] = "Duplicate key '{$key}' in category '{$catName}'";
                }
                $seen[$catName][$key] = true;
                $out[$catName][] = [
                    'key'   => $key,
                    'name'  => $name !== '' ? $name : MenuLabel::standardizeText($key),
                    'desc'  => $desc,
                    'price' => round($price, 2),
                ];
            }
        }

        if (!empty($errors)) {
            return back()->withErrors(['menu' => implode('; ', $errors)])->withInput();
        }

        // Count changes vs current config
        $changed = 0;
        $indexByKey = function(array $rows): array {
            $map = [];
            foreach ($rows as $r) {
                if (!isset($r['key'])) continue;
                $map[$r['key']] = $r;
            }
            return $map;
        };
        $cats = array_unique(array_merge(array_keys($curr), array_keys($out)));
        foreach ($cats as $cat) {
            $a = $indexByKey((array) ($curr[$cat] ?? []));
            $b = $indexByKey((array) ($out[$cat] ?? []));
            $keys = array_unique(array_merge(array_keys($a), array_keys($b)));
            foreach ($keys as $k) {
                $ra = $a[$k] ?? null; $rb = $b[$k] ?? null;
                if (!$ra || !$rb) { $changed++; continue; }
                $na = (string) ($ra['name'] ?? '');
                $da = (string) ($ra['desc'] ?? '');
                $pa = (float) ($ra['price'] ?? 0);
                $nb = (string) ($rb['name'] ?? '');
                $db = (string) ($rb['desc'] ?? '');
                $pb = (float) ($rb['price'] ?? 0);
                if ($na !== $nb || $da !== $db || abs($pa - $pb) > 0.0001) { $changed++; }
            }
        }

        // Build PHP file contents
        $export = var_export($out, true);
        $php = "<?php\nreturn " . $export . ";\n";

        $path = base_path('config/menu.php');
        try {
            file_put_contents($path, $php);
            // Also update runtime config so changes reflect immediately
            config(['menu' => $out]);
        } catch (\Throwable $e) {
            return back()->withErrors(['menu' => 'Failed to write config/menu.php: ' . $e->getMessage()])->withInput();
        }

        $msg = 'Menu updated';
        if ($changed > 0) { $msg .= " ({$changed} changed)"; }
        return redirect()->route('admin.menu')->with('ok', $msg);
    }
}
