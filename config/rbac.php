<?php
// config/rbac.php

return array (
  'roles' => 
  array (
    'admin' => 
    array (
      'label' => 'Administrador',
      'permissions' => 
      array (
        0 => 'financial_ofx.manage',
        1 => 'signatures.manage',
        2 => 'banners.manage',
        3 => 'settings.manage',
        4 => 'settings.manage',
        5 => 'congregations.manage',
        6 => 'financial_accounts.manage',
        7 => 'ebd.manage',
        8 => 'studies.manage',
        9 => 'events.manage',
        10 => 'financial.manage',
        11 => 'gallery.manage',
        12 => 'groups.manage',
        13 => 'groups.manage',
        14 => 'members.manage',
        15 => 'permissions.manage',
        16 => 'service_reports.manage',
        17 => 'users.manage',
        18 => 'ebd.lessons',
        19 => 'banners.view',
        20 => 'settings.view',
        21 => 'congregations.view',
        22 => 'dashboard.view',
        23 => 'ebd.view',
        24 => 'studies.view',
        25 => 'events.view',
        26 => 'financial.view',
        27 => 'gallery.view',
        28 => 'groups.view',
        29 => 'groups.view',
        30 => 'settings.card.view',
        31 => 'settings.layout.view',
        32 => 'members.view',
        33 => 'system_payments.view',
        34 => 'service_reports.view',
        35 => 'users.view',
        36 => 'signatures.view',
        37 => 'general_reports.view',
      ),
    ),
    'secretary' => 
    array (
      'label' => 'Secretária(o)',
      'permissions' => 
      array (
        0 => 'dashboard.view',
        1 => 'members.view',
        2 => 'members.manage',
        3 => 'service_reports.view',
        4 => 'service_reports.manage',
        5 => 'general_reports.view',
        6 => 'signatures.view',
        7 => 'signatures.manage',
        8 => 'groups.view',
        9 => 'groups.manage',
        10 => 'financial.view',
        11 => 'financial.manage',
      ),
    ),
    'developer' => 
    array (
      'label' => 'Desenvolvedor',
      'permissions' => 
      array (
        0 => 'developer.access',
        1 => 'dashboard.view',
        2 => 'users.manage',
        3 => 'system_payments.manage',
      ),
    ),
    'accountant' => 
    array (
      'label' => 'Contador',
      'permissions' => 
      array (
        0 => 'financial.manage',
        1 => 'financial.view',
      ),
    ),
  ),
);
