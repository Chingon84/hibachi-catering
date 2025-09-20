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
        ],
        'manager' => [
            'reservations.view','reservations.manage',
            'timeslots.view','timeslots.manage',
            'clients.view','clients.manage',
            'menu.view',
            'reports.view',
            'orders.view',
            'team.view',
        ],
        'staff' => [
            'reservations.view',
            'timeslots.view',
            'clients.view',
            'menu.view',
        ],
        'readonly' => [
            'reservations.view','clients.view','reports.view','orders.view'
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
            'reports.view','orders.view',
            // Complains
            'complains.view','complains.manage',
            // Team (view only)
            'team.view',
        ],
    ],
];
