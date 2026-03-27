<?php

return [
    'catalog' => [
        ['code' => 'dashboard.view_ops', 'name' => 'View Operations Dashboard', 'group' => 'dashboard'],
        ['code' => 'dashboard.view_own_performance', 'name' => 'View Own Performance Dashboard', 'group' => 'dashboard'],

        ['code' => 'organization.manage', 'name' => 'Manage Organization Data', 'group' => 'master_data'],
        ['code' => 'workforce.manage', 'name' => 'Manage Workforce Data', 'group' => 'master_data'],
        ['code' => 'asset.manage', 'name' => 'Manage Asset Data', 'group' => 'master_data'],
        ['code' => 'taxonomy.manage', 'name' => 'Manage Ticket Taxonomy', 'group' => 'master_data'],
        ['code' => 'sla.manage', 'name' => 'Manage SLA Policies', 'group' => 'master_data'],
        ['code' => 'inspection_template.manage', 'name' => 'Manage Inspection Templates', 'group' => 'master_data'],
        ['code' => 'access.manage', 'name' => 'Manage Access Control', 'group' => 'security'],
        ['code' => 'inspection_task.view_assigned', 'name' => 'View Assigned Inspection Tasks', 'group' => 'inspection'],
        ['code' => 'inspection_result.view_assigned', 'name' => 'View Assigned Inspection Results', 'group' => 'inspection'],

        ['code' => 'ticket.view_all', 'name' => 'View All Tickets', 'group' => 'ticket'],
        ['code' => 'ticket.view_department', 'name' => 'View Department Tickets', 'group' => 'ticket'],
        ['code' => 'ticket.view_assigned', 'name' => 'View Assigned Tickets', 'group' => 'ticket'],
        ['code' => 'ticket.view_own', 'name' => 'View Own Tickets', 'group' => 'ticket'],
        ['code' => 'ticket.create_any', 'name' => 'Create Tickets For Any Requester', 'group' => 'ticket'],
        ['code' => 'ticket.create_self', 'name' => 'Create Own Tickets', 'group' => 'ticket'],
        ['code' => 'ticket.assign_all', 'name' => 'Assign Any Ticket', 'group' => 'ticket'],
        ['code' => 'ticket.assign_department', 'name' => 'Assign Department Tickets', 'group' => 'ticket'],
        ['code' => 'ticket.approve_all', 'name' => 'Approve Any Ticket', 'group' => 'ticket'],
        ['code' => 'ticket.approve_department', 'name' => 'Approve Department Tickets', 'group' => 'ticket'],
        ['code' => 'ticket.mark_ready_all', 'name' => 'Mark Any Ticket Ready', 'group' => 'ticket'],
        ['code' => 'ticket.mark_ready_department', 'name' => 'Mark Department Tickets Ready', 'group' => 'ticket'],

        ['code' => 'engineer_task.view_assigned', 'name' => 'View Assigned Engineer Tasks', 'group' => 'engineer'],
        ['code' => 'engineer_task.transition_assigned', 'name' => 'Transition Assigned Engineer Tasks', 'group' => 'engineer'],
        ['code' => 'engineer_task.worklog_assigned', 'name' => 'Add Worklog To Assigned Tasks', 'group' => 'engineer'],
    ],

    'role_defaults' => [
        'super_admin' => ['*'],
        'operational_admin' => [
            'dashboard.view_ops',
            'organization.manage',
            'workforce.manage',
            'asset.manage',
            'taxonomy.manage',
            'sla.manage',
            'inspection_template.manage',
            'access.manage',
            'ticket.view_all',
            'ticket.create_any',
            'ticket.assign_all',
            'ticket.approve_all',
            'ticket.mark_ready_all',
        ],
        'supervisor' => [
            'dashboard.view_ops',
            'ticket.view_department',
            'ticket.create_any',
            'ticket.assign_department',
            'ticket.approve_department',
            'ticket.mark_ready_department',
        ],
        'engineer' => [
            'dashboard.view_own_performance',
            'ticket.view_assigned',
            'engineer_task.view_assigned',
            'engineer_task.transition_assigned',
            'engineer_task.worklog_assigned',
        ],
        'requester' => [
            'ticket.view_own',
            'ticket.create_self',
        ],
        'inspection_officer' => [
            'inspection_task.view_assigned',
            'inspection_result.view_assigned',
        ],
    ],
];
