<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class PermissionsController extends Controller
{
    public function index()
    {
        $roles = array_keys(config('permissions.roles'));
        // Ensure owner is first for display
        usort($roles, fn($a,$b)=>($a==='owner'?-1:($b==='owner'?1:strcmp($a,$b))));
        $perms = config('permissions.permissions');

        $assigned = RolePermission::all()->groupBy('role')->map->pluck('permission')->toArray();

        // Prefill from config for roles with no DB entries yet (excluding owner)
        $configRoles = config('permissions.roles');
        foreach ($roles as $r) {
            if ($r === 'owner') continue; // owner is always all
            if (!isset($assigned[$r]) || empty($assigned[$r])) {
                $assigned[$r] = $configRoles[$r] ?? [];
            }
        }

        return view('admin.team.permissions', [
            'roles' => $roles,
            'permissions' => $perms,
            'assigned' => $assigned,
        ]);
    }

    public function update(Request $request)
    {
        $roles = array_keys(config('permissions.roles'));
        $perms = config('permissions.permissions');
        $validPerms = array_flip($perms);

        $matrix = (array)$request->input('matrix', []);

        foreach ($roles as $role) {
            if ($role === 'owner') {
                // Owner always has full access; skip changes
                continue;
            }
            $selected = array_values(array_filter(array_unique((array)($matrix[$role] ?? [])), function($p) use ($validPerms){
                return isset($validPerms[$p]);
            }));

            // Replace role's permissions
            RolePermission::where('role', $role)->delete();
            $rows = array_map(fn($p)=>['role'=>$role,'permission'=>$p,'created_at'=>now(),'updated_at'=>now()], $selected);
            if (!empty($rows)) {
                RolePermission::insert($rows);
            }
        }

        return redirect()->route('admin.team.permissions')->with('ok','Permissions updated');
    }
}
