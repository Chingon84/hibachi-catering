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

        $roleSummaries = [
            'owner' => 'Full administrative control across all modules and protected system settings.',
            'admin' => 'Broad management access for daily operations, reporting, and team administration.',
            'manager' => 'Operational oversight for reservations, clients, reporting, and team visibility.',
            'office' => 'Front-of-house and coordination access for reservations, calendar, staff bookings, and feedback.',
            'staff' => 'Basic read access for daily scheduling, reservations, clients, and menu information.',
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
            'clients' => 'Clients',
            'menu' => 'Menu',
            'reports', 'orders' => 'Reports',
            'team' => 'Team',
            'staff' => 'Staff Bookings',
            'complains' => 'Feedback Center',
            'settings', 'trash' => 'System',
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
            'orders.view' => 'View order reporting',
            'team.view' => 'View team directory',
            'team.manage' => 'Manage team access',
            'staff.view' => 'View staff bookings',
            'staff.manage' => 'Manage staff bookings',
            'calendar.view' => 'View calendar',
            'complains.view' => 'View feedback center',
            'complains.manage' => 'Manage feedback center',
            'settings.view' => 'View system settings',
            'trash.view' => 'View deleted records',
            'trash.manage' => 'Manage deleted records',
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
            'orders.view' => 'Review order and operational breakdown reporting.',
            'team.view' => 'See team directory and profile information.',
            'team.manage' => 'Create users, edit records, and manage access levels.',
            'staff.view' => 'View staff bookings and scheduling details.',
            'staff.manage' => 'Manage staff bookings and staffing assignments.',
            'calendar.view' => 'See the operational calendar and event schedule.',
            'complains.view' => 'Open the Feedback Center and review cases.',
            'complains.manage' => 'Update complaint workflows and feedback records.',
            'settings.view' => 'View protected system configuration.',
            'trash.view' => 'Inspect archived and deleted records.',
            'trash.manage' => 'Restore or permanently remove archived records.',
            default => 'Access for this module.',
        };
    }
}
