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
        4 => 'congregations.manage',
        5 => 'financial_accounts.manage',
        6 => 'ebd.manage',
        7 => 'studies.manage',
        8 => 'events.manage',
        9 => 'financial.manage',
        10 => 'gallery.manage',
        11 => 'groups.manage',
        12 => 'members.manage',
        13 => 'permissions.manage',
        14 => 'service_reports.manage',
        15 => 'users.manage',
        16 => 'ebd.lessons',
        17 => 'banners.view',
        18 => 'settings.view',
        19 => 'congregations.view',
        20 => 'dashboard.view',
        21 => 'ebd.view',
        22 => 'studies.view',
        23 => 'events.view',
        24 => 'financial.view',
        25 => 'gallery.view',
        26 => 'groups.view',
        27 => 'settings.card.view',
        28 => 'settings.layout.view',
        29 => 'members.view',
        30 => 'system_payments.view',
        31 => 'service_reports.view',
        32 => 'users.view',
        33 => 'signatures.view',
        34 => 'general_reports.view',
        35 => 'donations.view',
        36 => 'donations.manage',
        37 => 'video_wall.view',
        38 => 'video_wall.manage',
        39 => 'campaigns.view',
        40 => 'campaigns.manage',
        41 => 'liturgy_schedules.view',
        42 => 'liturgy_schedules.manage',
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
        3 => 'congregations.view',
        4 => 'congregations.manage',
        5 => 'events.view',
        6 => 'events.manage',
        7 => 'service_reports.view',
        8 => 'service_reports.manage',
        9 => 'general_reports.view',
        10 => 'signatures.view',
        11 => 'signatures.manage',
        12 => 'groups.view',
        13 => 'groups.manage',
        14 => 'gallery.view',
        15 => 'gallery.manage',
        16 => 'banners.view',
        17 => 'banners.manage',
        18 => 'video_wall.view',
        19 => 'video_wall.manage',
        20 => 'liturgy_schedules.view',
        21 => 'liturgy_schedules.manage',
        22 => 'campaigns.view',
        23 => 'campaigns.manage',
        24 => 'ebd.view',
        25 => 'ebd.manage',
        26 => 'ebd.lessons',
        27 => 'studies.view',
        28 => 'studies.manage',
        29 => 'settings.layout.view',
        30 => 'settings.card.view',
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
    'treasurer' => 
    array (
      'label' => 'Tesoureiro',
      'permissions' => 
      array (
        0 => 'dashboard.view',
        1 => 'financial.view',
        2 => 'financial.manage',
        3 => 'financial_accounts.manage',
        4 => 'financial_ofx.manage',
        5 => 'donations.view',
        6 => 'donations.manage',
        7 => 'general_reports.view',
      ),
    ),
  ),
);
