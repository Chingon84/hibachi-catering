<?php

namespace Tests\Feature\Admin;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuAdminPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_admin_persists_catalog_rows_in_database(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'web')->post(route('admin.menu.update'), [
            'items' => [
                'STARTERS' => [
                    [
                        'key' => 'gyoza-2pc',
                        'name' => 'Gyoza (2 pc)',
                        'desc' => 'Pan seared dumplings',
                        'price' => '6.00',
                    ],
                ],
                'SEAFOOD' => [
                    [
                        'key' => 'garlic-scallops',
                        'name' => 'Garlic Scallops (6oz)',
                        'desc' => 'Butter garlic scallops',
                        'price' => '72.00',
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.menu'));

        $this->assertDatabaseHas('menus', [
            'item_key' => 'gyoza-2pc',
            'name' => 'Gyoza (2 pc)',
            'description' => 'Pan seared dumplings',
            'category' => 'STARTERS',
            'category_sort' => 0,
            'sort' => 0,
        ]);

        $this->assertDatabaseHas('menus', [
            'item_key' => 'garlic-scallops',
            'category' => 'SEAFOOD',
            'category_sort' => 1,
        ]);

        $this->assertSame(2, Menu::query()->count());
    }
}
