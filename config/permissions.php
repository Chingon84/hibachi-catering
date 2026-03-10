<?php

return [
    // Define all permission keys used across the app
    'permissions' => [
        'reservations.view',
        'reservations.manage',
        'timeslots.view',
        'timeslots.manage',
        'clients.view',
        'clients.manage',
        'menu.view',
        'menu.manage',
        'reports.view',
        'orders.view',
        'team.view',
        'team.manage',
        // New sections
        'staff.view',
        'staff.manage',
        'calendar.view',
        'complains.view',
        'complains.manage',
        'settings.view',
        'trash.view',
        'trash.manage',
        'inventory.view',
        'inventory.manage',
    ],

    // Minimal view permissions granted to admin principals
    // (role owner/admin or can_access_admin=1).
    'admin_base_view_permissions' => [
        'reservations.view',
        'clients.view',
        'reports.view',
        'timeslots.view',
        'calendar.view',
        'menu.view',
        'orders.view',
        'inventory.view',
    ],

    // Map roles to permissions
    'roles' => [
        'owner' => ['*'], // everything
        'admin' => [
            'reservations.view','reservations.manage',
            'timeslots.view','timeslots.manage',
            'clients.view','clients.manage',
            'menu.view','menu.manage',
            'reports.view',
            'orders.view',
            'team.view','team.manage',
            'settings.view',
            'trash.view','trash.manage',
            'inventory.view','inventory.manage',
        ],
        'manager' => [
            'reservations.view','reservations.manage',
            'timeslots.view','timeslots.manage',
            'clients.view','clients.manage',
            'menu.view',
            'reports.view',
            'orders.view',
            'team.view',
            'inventory.view','inventory.manage',
        ],
        'staff' => [
            'reservations.view',
            'timeslots.view',
            'clients.view',
            'menu.view',
        ],
        'readonly' => [
            'reservations.view','clients.view','reports.view','orders.view','inventory.view'
        ],
        // New role: office
        'office' => [
            // Reservations
            'reservations.view','reservations.manage',
            // Staff bookings
            'staff.view','staff.manage',
            // Calendar
            'calendar.view',
            // Timeslots
            'timeslots.view','timeslots.manage',
            // Clients
            'clients.view','clients.manage',
            // Menu
            'menu.view','menu.manage',
            // Reports & dashboards
            'reports.view','orders.view','inventory.view','inventory.manage',
            // Complains
            'complains.view','complains.manage',
            // Team (view only)
            'team.view',
        ],
    ],
];
