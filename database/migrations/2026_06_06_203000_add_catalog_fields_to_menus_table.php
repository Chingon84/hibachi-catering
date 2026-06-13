<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            if (!Schema::hasColumn('menus', 'item_key')) {
                $table->string('item_key')->nullable()->after('id');
            }
            if (!Schema::hasColumn('menus', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('menus', 'category_sort')) {
                $table->integer('category_sort')->default(0)->after('category');
            }
        });

        $categorySort = 0;
        $categoryMap = [];

        DB::table('menus')
            ->orderBy('category')
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'name', 'category'])
            ->each(function ($row) use (&$categorySort, &$categoryMap) {
                $category = trim((string) ($row->category ?? ''));
                if (!array_key_exists($category, $categoryMap)) {
                    $categoryMap[$category] = $categorySort++;
                }

                $name = trim((string) ($row->name ?? ''));
                $key = Str::slug($name !== '' ? $name : ('menu-' . $row->id));
                if ($key === '') {
                    $key = 'menu-' . $row->id;
                }

                DB::table('menus')
                    ->where('id', $row->id)
                    ->update([
                        'item_key' => $key,
                        'category_sort' => $categoryMap[$category],
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            if (Schema::hasColumn('menus', 'category_sort')) {
                $table->dropColumn('category_sort');
            }
            if (Schema::hasColumn('menus', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('menus', 'item_key')) {
                $table->dropColumn('item_key');
            }
        });
    }
};
