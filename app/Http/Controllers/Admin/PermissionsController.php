<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class PermissionsController extends Controller
{
    public function index()
    {
        $displayRoles = ['owner', 'admin', 'manager', 'office', 'staff'];
        $perms = config('permissions.permissions');
        $selectedRole = (string) request('role', 'admin');
        if (!in_array($selectedRole, $displayRoles, true)) {
            $selectedRole = 'admin';
        }

        $assigned = RolePermission::all()->groupBy('role')->map->pluck('permission')->toArray();

        // Prefill from config for roles with no DB entries yet (excluding owner)
        $configRoles = config('permissions.roles');
        foreach (array_keys($configRoles) as $r) {
            if ($r === 'owner') continue; // owner is always all
            if (!isset($assigned[$r]) || empty($assigned[$r])) {
                $assigned[$r] = $configRoles[$r] ?? [];
            }
        }

        $resolvedPermissions = $this->resolvedRolePermissions($selectedRole, $assigned);

        $roleSummaries = [
            'owner' => 'Full access to every admin module. Owner protections remain enforced for owner account changes and deletion.',
            'admin' => 'Access to all current admin modules. Owner-only safeguards still block deleting the owner account or changing the owner from non-owner workflows.',
            'manager' => 'Access to day-to-day operations and management modules, excluding Financial Overview, Reports, Settings, Trash, and Access Control.',
            'office' => 'Access limited to Reservations and Operations modules only, including clients, staff booking, calendar, schedule, timeslots, inventory, and order breakdown.',
            'staff' => 'Staff stays on the limited operational access profile currently configured for this role.',
        ];

        $groupedPermissions = collect($perms)
            ->groupBy(fn (string $permission) => $this->permissionModule($permission))
            ->map(fn ($group) => $group
                ->map(fn (string $permission) => [
                    'key' => $permission,
                    'label' => $this->permissionLabel($permission),
                    'description' => $this->permissionDescription($permission),
                ])
                ->values())
            ->all();

        return view('admin.team.permissions', [
            'roles' => $displayRoles,
            'selectedRole' => $selectedRole,
            'assigned' => $assigned,
            'roleSummaries' => $roleSummaries,
            'groupedPermissions' => $groupedPermissions,
            'permissionMatrix' => $this->permissionMatrix($selectedRole, $resolvedPermissions),
        ]);
    }

    public function update(Request $request)
    {
        $roles = ['admin', 'manager', 'office', 'staff'];
        $perms = config('permissions.permissions');
        $validPerms = array_flip($perms);
        $selectedRole = (string) $request->input('selected_role', 'admin');

        if (!in_array($selectedRole, ['owner', ...$roles], true)) {
            return redirect()->route('admin.team.permissions')->with('ok', 'Invalid role selection.');
        }

        if ($selectedRole === 'owner') {
            return redirect()->route('admin.team.permissions', ['role' => 'owner'])->with('ok', 'Owner access is always fully enabled.');
        }

        $matrix = (array)$request->input('matrix', []);
        $selected = array_values(array_filter(array_unique((array)($matrix[$selectedRole] ?? [])), function($p) use ($validPerms){
            return isset($validPerms[$p]);
        }));

        RolePermission::where('role', $selectedRole)->delete();
        $rows = array_map(fn($p)=>['role'=>$selectedRole,'permission'=>$p,'created_at'=>now(),'updated_at'=>now()], $selected);
        if (!empty($rows)) {
            RolePermission::insert($rows);
        }

        return redirect()->route('admin.team.permissions', ['role' => $selectedRole])->with('ok', 'Access control updated');
    }

    private function permissionModule(string $permission): string
    {
        return match (strtok($permission, '.')) {
            'reservations', 'timeslots' => 'Reservations',
            'calendar' => 'Calendar',
            'schedule' => 'Schedule',
            'clients' => 'Clients',
            'menu' => 'Menu',
            'financial' => 'Financial Overview',
            'reports' => 'Reports',
            'orders' => 'Order Breakdown',
            'team' => 'Team',
            'staff' => 'Staff Booking',
            'feedback' => 'Feedback Center',
            'settings' => 'Settings',
            'trash' => 'Trash',
            'inventory' => 'Inventory',
            default => 'System',
        };
    }

    private function permissionLabel(string $permission): string
    {
        return match ($permission) {
            'reservations.view' => 'View reservations',
            'reservations.manage' => 'Manage reservations',
            'timeslots.view' => 'View timeslots',
            'timeslots.manage' => 'Manage timeslots',
            'clients.view' => 'View clients',
            'clients.manage' => 'Manage clients',
            'menu.view' => 'View menu',
            'menu.manage' => 'Manage menu',
            'reports.view' => 'View reports',
            'financial.view' => 'View financial overview',
            'financial.manage' => 'Manage expenses',
            'orders.view' => 'View order reporting',
            'team.view' => 'View team directory',
            'team.manage' => 'Manage team access',
            'staff.view' => 'View staff bookings',
            'staff.manage' => 'Manage staff bookings',
            'calendar.view' => 'View calendar',
            'schedule.view' => 'View schedule',
            'schedule.manage' => 'Manage schedule',
            'feedback.view' => 'View feedback center',
            'feedback.manage' => 'Manage feedback center',
            'settings.view' => 'View system settings',
            'trash.view' => 'View deleted records',
            'trash.manage' => 'Manage deleted records',
            'inventory.view' => 'View inventory',
            'inventory.manage' => 'Manage inventory',
            default => ucwords(str_replace(['.', '_'], [' ', ' '], $permission)),
        };
    }

    private function permissionDescription(string $permission): string
    {
        return match ($permission) {
            'reservations.view' => 'See reservation records, timelines, and service details.',
            'reservations.manage' => 'Create, update, and coordinate reservation workflows.',
            'timeslots.view' => 'See capacity and availability windows.',
            'timeslots.manage' => 'Open, close, and adjust schedule availability.',
            'clients.view' => 'Access client profiles and contact details.',
            'clients.manage' => 'Edit client records and relationship details.',
            'menu.view' => 'See menu items and package details.',
            'menu.manage' => 'Update menu items, pricing, and package configuration.',
            'reports.view' => 'Access management reporting and dashboards.',
            'financial.view' => 'Open the financial overview and view sensitive revenue and expense summaries.',
            'financial.manage' => 'Create, edit, and delete expense records in the financial overview.',
            'orders.view' => 'Review order and operational breakdown reporting.',
            'team.view' => 'See team directory and profile information.',
            'team.manage' => 'Create users, edit records, and manage access levels.',
            'staff.view' => 'View staff bookings and scheduling details.',
            'staff.manage' => 'Manage staff bookings and staffing assignments.',
            'calendar.view' => 'See the operational calendar and event schedule.',
            'schedule.view' => 'Review chef priority rankings and assignment recommendations.',
            'schedule.manage' => 'Adjust schedule scores and assign chefs to events.',
            'feedback.view' => 'Open the Feedback Center and review cases.',
            'feedback.manage' => 'Update complaint workflows and feedback records.',
            'settings.view' => 'View protected system configuration.',
            'trash.view' => 'Inspect archived and deleted records.',
            'trash.manage' => 'Restore or permanently remove archived records.',
            'inventory.view' => 'Open warehouse, checklist, and van inventory records.',
            'inventory.manage' => 'Create, edit, and update inventory data.',
            default => 'Access for this module.',
        };
    }

    private function permissionMatrix(string $selectedRole, array $permissions): array
    {
        if ($selectedRole === 'owner' || in_array('*', $permissions, true)) {
            $permissions = config('permissions.permissions', []);
        }

        $has = fn (?string $permission) => $permission !== null && in_array($permission, $permissions, true);
        $allowed = fn (string $label = 'Allowed') => ['type' => 'allowed', 'label' => $label];
        $denied = fn (string $label = 'Not allowed') => ['type' => 'denied', 'label' => $label];
        $restricted = fn (string $label = 'N/A') => ['type' => 'restricted', 'label' => $label];

        $modules = [
            ['module' => 'Reservations', 'view' => 'reservations.view', 'manage' => 'reservations.manage', 'export' => true, 'approve' => true],
            ['module' => 'Clients', 'view' => 'clients.view', 'manage' => 'clients.manage'],
            ['module' => 'Staff Booking', 'view' => 'staff.view', 'manage' => 'staff.manage'],
            ['module' => 'Calendar', 'view' => 'calendar.view', 'manage' => null],
            ['module' => 'Schedule', 'view' => 'schedule.view', 'manage' => 'schedule.manage'],
            ['module' => 'Timeslots', 'view' => 'timeslots.view', 'manage' => 'timeslots.manage'],
            ['module' => 'Inventory', 'view' => 'inventory.view', 'manage' => 'inventory.manage'],
            ['module' => 'Order Breakdown', 'view' => 'orders.view', 'manage' => null, 'export' => true],
            ['module' => 'Menu', 'view' => 'menu.view', 'manage' => 'menu.manage', 'approve' => true],
            ['module' => 'Feedback Center', 'view' => 'feedback.view', 'manage' => 'feedback.manage'],
            ['module' => 'Team Directory', 'view' => 'team.view', 'manage' => null],
            ['module' => 'Access Control', 'view' => 'team.manage', 'manage' => 'team.manage', 'approve' => true],
            ['module' => 'Financial Overview', 'view' => 'financial.view', 'manage' => 'financial.manage', 'export' => true],
            ['module' => 'Reports', 'view' => 'reports.view', 'manage' => null, 'export' => true],
            ['module' => 'Settings', 'view' => 'settings.view', 'manage' => null],
            ['module' => 'Trash', 'view' => 'trash.view', 'manage' => 'trash.manage'],
        ];

        return collect($modules)->map(function (array $module) use ($has, $allowed, $denied, $restricted) {
            $canView = $has($module['view'] ?? null) || $has($module['manage'] ?? null);
            $canManage = $has($module['manage'] ?? null);
            $canExport = !empty($module['export']) && $canView;
            $canApprove = !empty($module['approve']) && $canManage;

            return [
                'module' => $module['module'],
                'view' => $canView ? $allowed() : $denied(),
                'create' => $module['manage'] !== null ? ($canManage ? $allowed('Managed') : $denied()) : $restricted(),
                'edit' => $module['manage'] !== null ? ($canManage ? $allowed('Managed') : $denied()) : $restricted(),
                'delete' => $module['manage'] !== null ? ($canManage ? $allowed('Managed') : $denied()) : $restricted(),
                'export' => !empty($module['export']) ? ($canExport ? $allowed('Available') : $denied()) : $restricted(),
                'approve' => !empty($module['approve']) ? ($canApprove ? $allowed('Managed') : $denied()) : $restricted(),
            ];
        })->all();
    }

    private function resolvedRolePermissions(string $role, array $assigned): array
    {
        if ($role === 'owner') {
            return ['*'];
        }

        if (!empty($assigned[$role])) {
            return array_values($assigned[$role]);
        }

        return config('permissions.roles.' . $role, []);
    }
}
