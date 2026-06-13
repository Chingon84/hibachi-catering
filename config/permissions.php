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
        'financial.view',
        'financial.manage',
        'orders.view',
        'team.view',
        'team.manage',
        // New sections
        'staff.view',
        'staff.manage',
        'calendar.view',
        'schedule.view',
        'schedule.manage',
        'feedback.view',
        'feedback.manage',
        'settings.view',
        'trash.view',
        'trash.manage',
        'inventory.view',
        'inventory.manage',
    ],

    // Minimal view permissions granted to the admin role.
    'admin_base_view_permissions' => [
        'reservations.view',
        'clients.view',
        'reports.view',
        'timeslots.view',
        'calendar.view',
        'schedule.view',
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
            'staff.view','staff.manage',
            'calendar.view',
            'schedule.view','schedule.manage',
            'clients.view','clients.manage',
            'menu.view','menu.manage',
            'feedback.view','feedback.manage',
            'reports.view',
            'financial.view','financial.manage',
            'orders.view',
            'team.view','team.manage',
            'settings.view',
            'trash.view','trash.manage',
            'inventory.view','inventory.manage',
        ],
        'manager' => [
            'reservations.view','reservations.manage',
            'staff.view','staff.manage',
            'calendar.view',
            'timeslots.view','timeslots.manage',
            'schedule.view','schedule.manage',
            'clients.view','clients.manage',
            'menu.view','menu.manage',
            'orders.view',
            'team.view','team.manage',
            'feedback.view','feedback.manage',
            'reports.view',
            'financial.view',
            'inventory.view','inventory.manage',
        ],
        'staff' => [
            'reservations.view',
            'timeslots.view',
            'schedule.view',
            'clients.view',
            'menu.view',
        ],
        'readonly' => [
            'reservations.view','clients.view','reports.view','orders.view','inventory.view'
        ],
        // New role: office
        'office' => [
            'reservations.view','reservations.manage',
            'staff.view','staff.manage',
            'calendar.view',
            'schedule.view','schedule.manage',
            'timeslots.view','timeslots.manage',
            'clients.view','clients.manage',
            'orders.view',
            'reports.view',
            'team.view',
            'feedback.view',
            'inventory.view','inventory.manage',
        ],
    ],
];
