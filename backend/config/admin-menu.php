<?php

use App\Models\User;

return [
    'sections' => [
        [
            'label' => 'Overview',
            'items' => [
                [
                    'label' => 'System Dashboard',
                    'route' => 'admin.super.dashboard',
                    'roles' => [User::ROLE_SUPER_ADMIN],
                ],
                [
                    'label' => 'University Dashboard',
                    'route' => 'admin.university.dashboard',
                    'roles' => [User::ROLE_UNIVERSITY_ADMIN],
                ],
                [
                    'label' => 'Dorm Dashboard',
                    'route' => 'admin.dorm.dashboard',
                    'roles' => [User::ROLE_DORM_ADMIN],
                ],
            ],
        ],
        [
            'label' => 'University Admin',
            'roles' => [User::ROLE_UNIVERSITY_ADMIN],
            'items' => [
                [
                    'label' => 'Dorms',
                    'route' => 'admin.university.dorms.index',
                ],
                [
                    'label' => 'Dorm Admins',
                    'route' => 'admin.university.dorm-admins.index',
                ],
                [
                    'label' => 'Students',
                    'route' => 'admin.university.students.index',
                ],
                [
                    'label' => 'Reports',
                    'route' => 'admin.university.reports.index',
                ],
                [
                    'label' => 'Tickets',
                    'route' => 'admin.university.tickets.index',
                ],
                [
                    'label' => 'Announcements',
                    'route' => 'admin.university.announcements.index',
                ],
                [
                    'label' => 'Activity Feed',
                    'route' => 'admin.university.activity-feed.index',
                    'feature' => 'activity_feed',
                ],
                [
                    'label' => 'Audit Logs',
                    'route' => 'admin.university.audit-logs.index',
                    'feature' => 'audit_logs',
                ],
                [
                    'label' => 'System Settings',
                    'route' => 'admin.university.settings.index',
                ],
            ],
        ],
        [
            'label' => 'Dorm Admin',
            'roles' => [User::ROLE_DORM_ADMIN],
            'items' => [
                [
                    'label' => 'Floors',
                    'route' => 'admin.dorm.floors.index',
                ],
                [
                    'label' => 'Rooms',
                    'route' => 'admin.dorm.rooms.index',
                ],
                [
                    'label' => 'Students',
                    'route' => 'admin.dorm.students.index',
                ],
                [
                    'label' => 'Assignments',
                    'route' => 'admin.dorm.assignments.index',
                ],
                [
                    'label' => 'Tickets',
                    'route' => 'admin.dorm.tickets.index',
                ],
                [
                    'label' => 'Announcements',
                    'route' => 'admin.dorm.announcements.index',
                ],
            ],
        ],
        [
            'label' => 'System',
            'roles' => [User::ROLE_SUPER_ADMIN],
            'items' => [
                [
                    'label' => 'Universities',
                    'route' => 'admin.super.universities.index',
                ],
                [
                    'label' => 'Users',
                    'route' => 'admin.super.users.index',
                ],
                [
                    'label' => 'Platform Settings',
                    'route' => 'admin.super.settings.index',
                ],
            ],
        ],
    ],
];
