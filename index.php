<?php
// public/index.php
date_default_timezone_set('America/Sao_Paulo');

// Configuração Robusta de Sessão
$sessionPath = dirname(__DIR__) . '/tmp';
if (!file_exists($sessionPath)) {
    @mkdir($sessionPath, 0777, true);
}

// Forçar configurações de garbage collection e cookie
ini_set('session.gc_maxlifetime', 86400); // 24 horas
ini_set('session.cookie_lifetime', 86400); // 24 horas
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

if (is_writable($sessionPath)) {
    session_save_path($sessionPath);
}

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';

// Autoload simple implementation
spl_autoload_register(function ($class_name) {
    $paths = [
        __DIR__ . '/../src/controllers/',
        __DIR__ . '/../src/models/',
        __DIR__ . '/../config/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Simple Router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Normalize URI: remove trailing slash if not root
if ($uri !== '/' && substr($uri, -1) === '/') {
    $uri = rtrim($uri, '/');
}
$method = $_SERVER['REQUEST_METHOD'];

// Registrar acesso
if (function_exists('logAccess')) {
    logAccess();
}

// Security Headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Global CSRF Check for POST requests to /admin (except login)
if ($method === 'POST' && strpos($uri, '/admin') === 0) {
    // Exception for login (we will handle it inside AuthController if needed, 
    // but better to enforce everywhere and add field to login form)
    // Actually, verify_csrf() checks $_POST['csrf_token']. 
    // We MUST add the token to the login form before enabling this, or login will break.
    // Let's enable it generally.
    verify_csrf();
}

// Routes
if ($uri == '/' || $uri == '/home') {
    (new HomeController())->index();
} 
// Migration Manager Routes (Priority)
elseif ($uri == '/developer/migrations') {
    (new MigrationController())->index();
}
elseif ($uri == '/developer/migrations/run') {
    (new MigrationController())->run();
}
elseif ($uri == '/developer/migrations/rollback' && $method == 'POST') {
    (new MigrationController())->rollback($_POST['filename'] ?? '');
}
elseif ($uri == '/admin/manual') {
    (new ManualController())->index();
}
elseif ($uri == '/admin/login') {
    if ($method == 'POST') {
        (new AuthController())->login();
    } else {
        (new AuthController())->showLogin();
    }
}
elseif ($uri == '/admin/site-settings') {
    (new SiteSettingsController())->index();
}
elseif ($uri == '/admin/site-settings/update' && $method == 'POST') {
    (new SiteSettingsController())->updateTheme();
}
elseif ($uri == '/admin/change-password') {
    (new AuthController())->changePassword();
}
elseif ($uri == '/admin/signatures') {
    (new SignatureController())->index();
}
elseif ($uri == '/admin/signatures/store') {
    (new SignatureController())->store();
}
elseif (preg_match('#^/admin/signatures/delete/(\d+)$#', $uri, $matches)) {
    (new SignatureController())->delete($matches[1]);
}
elseif ($uri == '/admin/system-payments') {
    (new SystemPaymentController())->index();
}
elseif ($uri == '/admin/system-payments/pay') {
    (new SystemPaymentController())->pay();
}
elseif ($uri == '/developer/dashboard') {
    (new DeveloperController())->index();
}
elseif ($uri == '/developer/payments') {
    (new DeveloperController())->payments();
}
elseif ($uri == '/developer/payments/generate') {
    (new DeveloperController())->generateCharge();
}
elseif ($uri == '/developer/payments/delete') {
    (new DeveloperController())->deletePayment();
}
elseif ($uri == '/developer/payments/update-status') {
    (new DeveloperController())->updateStatus();
}
elseif ($uri == '/developer/settings') {
    (new DeveloperController())->settings();
}
elseif ($uri == '/developer/import') {
    (new DeveloperController())->import();
}
elseif ($uri == '/developer/import/sync-members') {
    (new DeveloperController())->syncMembers();
}
elseif ($uri == '/developer/import/clear-entries') {
    (new DeveloperController())->clearEntries();
}
elseif ($uri == '/developer/import/expenses') {
    (new DeveloperController())->importExpenses();
}
elseif ($uri == '/developer/import/clear-expenses') {
    (new DeveloperController())->clearExpenses();
}
elseif ($uri == '/developer/logs') {
    (new DeveloperController())->logs();
}
elseif ($uri == '/developer/users') {
    (new DeveloperController())->users();
}
elseif ($uri == '/developer/users/password') {
    (new DeveloperController())->changeUserPassword();
}
elseif ($uri == '/admin/logout') {
    (new AuthController())->logout();
}
elseif ($uri == '/admin' || $uri == '/admin/dashboard') {
    (new DashboardController())->index();
}
elseif ($uri == '/admin/members') {
    (new MemberController())->index();
}
// API Roles (Cargos)
elseif ($uri == '/api/roles/list') {
    (new RoleController())->list();
}
elseif ($uri == '/api/roles/create' && $method == 'POST') {
    (new RoleController())->create();
}
elseif ($uri == '/api/roles/update' && $method == 'POST') {
    (new RoleController())->update();
}
elseif ($uri == '/api/roles/delete' && $method == 'POST') {
    (new RoleController())->delete();
}
// API Members (Lista simples para autocomplete)
elseif ($uri == '/api/members/list') {
    (new MemberController())->listApi();
}
elseif ($uri == '/admin/members/create') {
    if ($method == 'POST') {
        (new MemberController())->store();
    } else {
        (new MemberController())->create();
    }
}
elseif (preg_match('#^/admin/members/edit/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new MemberController())->update($matches[1]);
    } else {
        (new MemberController())->edit($matches[1]);
    }
}
elseif (preg_match('#^/admin/members/card/(\d+)$#', $uri, $matches)) {
    (new MemberController())->card($matches[1]);
}
elseif (preg_match('#^/admin/members/delete/(\d+)$#', $uri, $matches)) {
    (new MemberController())->delete($matches[1]);
}
elseif ($uri == '/admin/tithes') {
    (new TitheController())->index();
}
elseif ($uri == '/admin/tithes/create') {
    if ($method == 'POST') {
        (new TitheController())->store();
    } else {
        (new TitheController())->create();
    }
}
elseif ($uri == '/admin/tithes/store') { // Fix for 404
    if ($method == 'POST') {
        (new TitheController())->store();
    }
}
elseif (preg_match('#^/admin/tithes/receipt/(\d+)$#', $uri, $matches)) {
    (new TitheController())->receipt($matches[1]);
}
elseif (preg_match('#^/admin/tithes/edit/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new TitheController())->update($matches[1]);
    } else {
        (new TitheController())->edit($matches[1]);
    }
}
elseif (preg_match('#^/admin/tithes/delete/(\d+)$#', $uri, $matches)) {
    (new TitheController())->delete($matches[1]);
}
// Financial - Bank Accounts
elseif ($uri == '/admin/financial/bank-accounts') {
    (new BankAccountController())->index();
}
elseif ($uri == '/admin/financial/bank-accounts/create') {
    (new BankAccountController())->create();
}
elseif ($uri == '/admin/financial/bank-accounts/store' && $method == 'POST') {
    (new BankAccountController())->store();
}
elseif (preg_match('#^/admin/financial/bank-accounts/edit/(\d+)$#', $uri, $matches)) {
    (new BankAccountController())->edit($matches[1]);
}
elseif (preg_match('#^/admin/financial/bank-accounts/update/(\d+)$#', $uri, $matches) && $method == 'POST') {
    (new BankAccountController())->update($matches[1]);
}
// Financial - Chart of Accounts
elseif ($uri == '/admin/financial/chart-accounts') {
    (new ChartOfAccountController())->index();
}
elseif ($uri == '/admin/financial/chart-accounts/create') {
    (new ChartOfAccountController())->create();
}
elseif ($uri == '/admin/financial/chart-accounts/store' && $method == 'POST') {
    (new ChartOfAccountController())->store();
}
elseif (preg_match('#^/admin/financial/chart-accounts/edit/(\d+)$#', $uri, $matches)) {
    (new ChartOfAccountController())->edit($matches[1]);
}
elseif (preg_match('#^/admin/financial/chart-accounts/update/(\d+)$#', $uri, $matches) && $method == 'POST') {
    (new ChartOfAccountController())->update($matches[1]);
}
// Financial - Reports
elseif ($uri == '/admin/financial/report') {
    (new FinancialReportController())->index();
}
// Financial - OFX
elseif ($uri == '/admin/financial/ofx') {
    (new OfxController())->index();
}
elseif ($uri == '/admin/financial/ofx/import' && $method == 'POST') {
    (new OfxController())->import();
}
elseif (preg_match('#^/admin/financial/ofx/conciliate/(\d+)$#', $uri, $matches)) {
    (new OfxController())->conciliate($matches[1]);
}
elseif (preg_match('#^/admin/financial/ofx/save/(\d+)$#', $uri, $matches) && $method == 'POST') {
    (new OfxController())->saveConciliation($matches[1]);
}
// Rotas de Saídas/Despesas
elseif ($uri == '/admin/expenses') {
    (new ExpenseController())->index();
}
elseif ($uri == '/admin/expenses/create') {
    if ($method == 'POST') {
        (new ExpenseController())->store();
    } else {
        (new ExpenseController())->create();
    }
}
elseif ($uri == '/admin/expenses/store') {
    if ($method == 'POST') {
        (new ExpenseController())->store();
    }
}
elseif (preg_match('#^/admin/expenses/edit/(\d+)$#', $uri, $matches)) {
    (new ExpenseController())->edit($matches[1]);
}
elseif (preg_match('#^/admin/expenses/update/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new ExpenseController())->update($matches[1]);
    }
}
elseif (preg_match('#^/admin/expenses/delete/(\d+)$#', $uri, $matches)) {
    (new ExpenseController())->delete($matches[1]);
}

// Relatórios Financeiros
elseif ($uri == '/admin/financial/report') {
    (new FinancialReportController())->index();
}
elseif (preg_match('#^/admin/financial/export/(csv|excel)$#', $uri, $matches)) {
    (new FinancialReportController())->export($matches[1]);
}

// Fechamentos Financeiros
elseif ($uri == '/admin/financial/closures') {
    (new FinancialClosureController())->index();
}
elseif ($uri == '/admin/financial/closures/store') {
    if ($method == 'POST') {
        (new FinancialClosureController())->store();
    }
}
elseif (preg_match('#^/admin/financial/closures/show/(\d+)$#', $uri, $matches)) {
    (new FinancialClosureController())->show($matches[1]);
}
elseif (preg_match('#^/admin/financial/closures/delete/(\d+)$#', $uri, $matches)) {
    (new FinancialClosureController())->delete($matches[1]);
}

// Rotas de Eventos
elseif ($uri == '/admin/events') {
    (new EventController())->index(); 
}
elseif ($uri == '/admin/attendance') {
    (new EventController())->attendanceList();
}
elseif (preg_match('#^/admin/events/attendance/enable/(\d+)$#', $uri, $matches)) {
    (new EventController())->enableAttendance($matches[1]);
}
elseif (preg_match('#^/admin/events/attendance/delete/(\d+)$#', $uri, $matches)) {
    (new EventController())->deleteAttendanceList($matches[1]);
}
elseif ($uri == '/admin/events/create') {
    if ($method == 'POST') {
        (new EventController())->store();
    } else {
        (new EventController())->create();
    }
}
elseif (preg_match('#^/admin/events/edit/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new EventController())->update($matches[1]);
    } else {
        (new EventController())->edit($matches[1]);
    }
}
elseif (preg_match('#^/admin/events/toggle/(\d+)$#', $uri, $matches)) {
    (new EventController())->toggleStatus($matches[1]);
}
elseif (preg_match('#^/admin/events/delete/(\d+)$#', $uri, $matches)) {
    (new EventController())->delete($matches[1]);
}
// Lista de Presença de Eventos
elseif (preg_match('#^/admin/events/attendance/print/(\d+)$#', $uri, $matches)) {
    (new EventController())->printAttendance($matches[1]);
}
elseif (preg_match('#^/admin/events/attendance/(\d+)$#', $uri, $matches)) {
    (new EventController())->attendance($matches[1]);
}
elseif (preg_match('#^/admin/events/attendance/register/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new EventController())->registerAttendance($matches[1]);
    }
}
// Rotas de Estudos
elseif ($uri == '/admin/studies') {
    (new StudyController())->index();
}
elseif ($uri == '/admin/studies/create') {
    if ($method == 'POST') {
        (new StudyController())->create();
    } else {
        (new StudyController())->create();
    }
}
elseif (preg_match('#^/admin/studies/delete/(\d+)$#', $uri, $matches)) {
    (new StudyController())->delete($matches[1]);
}
elseif ($uri == '/portal/studies') {
    (new StudyController())->portalIndex();
}

// Rotas de EBD (Escola Bíblica Dominical)
elseif (preg_match('#^/admin/ebd/lessons/show/(\d+)$#', $uri, $matches)) {
    (new EbdController())->showLesson($matches[1]);
}
elseif (preg_match('#^/admin/ebd/lessons/edit/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new EbdController())->updateLesson($matches[1]);
    } else {
        (new EbdController())->editLesson($matches[1]);
    }
}
elseif (preg_match('#^/admin/ebd/lessons/delete/(\d+)$#', $uri, $matches)) {
    (new EbdController())->deleteLesson($matches[1]);
}
elseif ($uri == '/admin/ebd/classes') {
    (new EbdController())->index();
}
elseif ($uri == '/admin/ebd/reports') {
    (new EbdController())->reports();
}
elseif ($uri == '/admin/ebd/classes/create') {
    if ($method == 'POST') {
        (new EbdController())->storeClass();
    } else {
        (new EbdController())->createClass();
    }
}
elseif (preg_match('#^/admin/ebd/classes/edit/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new EbdController())->updateClass($matches[1]);
    } else {
        (new EbdController())->editClass($matches[1]);
    }
}
elseif (preg_match('#^/admin/ebd/classes/delete/(\d+)$#', $uri, $matches)) {
    (new EbdController())->deleteClass($matches[1]);
}
elseif (preg_match('#^/admin/ebd/classes/show/(\d+)$#', $uri, $matches)) {
    (new EbdController())->showClass($matches[1]);
}
elseif (preg_match('#^/admin/ebd/classes/enroll/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new EbdController())->enrollStudent($matches[1]);
    }
}
elseif (preg_match('#^/admin/ebd/students/remove/(\d+)$#', $uri, $matches)) {
    (new EbdController())->removeStudent($matches[1]);
}
elseif (preg_match('#^/admin/ebd/classes/assign-teacher/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new EbdController())->assignTeacher($matches[1]);
    }
}
elseif (preg_match('#^/admin/ebd/teachers/remove/(\d+)$#', $uri, $matches)) {
    (new EbdController())->removeTeacher($matches[1]);
}
elseif (preg_match('#^/admin/ebd/lessons/create/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new EbdController())->storeLesson($matches[1]);
    } else {
        (new EbdController())->createLesson($matches[1]);
    }
}

elseif ($uri == '/admin/events/create') {
    if ($method == 'POST') {
        (new EventController())->store();
    } else {
        (new EventController())->create();
    }
}
elseif (preg_match('#^/admin/events/edit/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new EventController())->update($matches[1]);
    } else {
        (new EventController())->edit($matches[1]);
    }
}
elseif (preg_match('#^/admin/events/delete/(\d+)$#', $uri, $matches)) {
    (new EventController())->delete($matches[1]);
}
elseif (preg_match('#^/admin/events/toggle/(\d+)$#', $uri, $matches)) {
    (new EventController())->toggleStatus($matches[1]);
}

elseif ($uri == '/admin/settings') {
    (new SettingsController())->index();
}
elseif ($uri == '/admin/settings/store') {
    (new SettingsController())->store();
}
elseif ($uri == '/admin/settings/card-layout') {
    if ($method == 'POST') {
        (new SettingsController())->storeCardLayout();
    } else {
        (new SettingsController())->cardLayout();
    }
}
elseif ($uri == '/admin/settings/connect') {
    (new SettingsController())->connect();
}
elseif ($uri == '/admin/settings/test-birthdays') {
    (new SettingsController())->testBirthdays();
}

// Rotas da Galeria
elseif ($uri == '/oracao') {
    if ($method == 'POST') {
        (new PrayerController())->store();
    } else {
        (new PrayerController())->index();
    }
}
elseif (preg_match('#^/oracao/amem/(\d+)$#', $uri, $matches) && $method == 'POST') {
    (new PrayerController())->amen($matches[1]);
}
elseif (preg_match('#^/oracao/editar/(\d+)$#', $uri, $matches) && $method == 'POST') {
    (new PrayerController())->update($matches[1]);
}
elseif (preg_match('#^/oracao/excluir/(\d+)$#', $uri, $matches) && $method == 'POST') {
    (new PrayerController())->delete($matches[1]);
}
elseif ($uri == '/galeria') {
    (new GalleryController())->publicIndex();
}
elseif ($uri == '/admin/gallery') {
    (new GalleryController())->index();
}
elseif ($uri == '/admin/gallery/create') {
    if ($method == 'POST') {
        (new GalleryController())->store();
    } else {
        (new GalleryController())->create();
    }
}
// Rotas de Congregações
elseif ($uri == '/admin/congregations') {
    (new CongregationController())->index();
}
elseif ($uri == '/admin/congregations/create') {
    if ($method == 'POST') {
        (new CongregationController())->store();
    } else {
        (new CongregationController())->create();
    }
}
elseif (preg_match('#^/admin/congregations/edit/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new CongregationController())->update($matches[1]);
    } else {
        (new CongregationController())->edit($matches[1]);
    }
}
elseif (preg_match('#^/admin/congregations/delete/(\d+)$#', $uri, $matches)) {
    (new CongregationController())->delete($matches[1]);
}
elseif (preg_match('#^/admin/gallery/edit/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new GalleryController())->update($matches[1]);
    } else {
        (new GalleryController())->edit($matches[1]);
    }
}
elseif (preg_match('#^/admin/gallery/manage/(\d+)$#', $uri, $matches)) {
    (new GalleryController())->manage($matches[1]);
}
elseif (preg_match('#^/admin/gallery/upload/(\d+)$#', $uri, $matches)) {
    (new GalleryController())->upload($matches[1]);
}
elseif (preg_match('#^/admin/gallery/delete_photo/(\d+)$#', $uri, $matches)) {
    (new GalleryController())->deletePhoto($matches[1]);
}
elseif (preg_match('#^/admin/gallery/delete/(\d+)$#', $uri, $matches)) {
    (new GalleryController())->deleteAlbum($matches[1]);
}

// Rotas de Usuários
elseif ($uri == '/admin/users') {
    (new UserController())->index();
}
elseif ($uri == '/admin/users/create') {
    if ($method == 'POST') {
        (new UserController())->store();
    } else {
        (new UserController())->create();
    }
}
elseif (preg_match('#^/admin/users/edit/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new UserController())->update($matches[1]);
    } else {
        (new UserController())->edit($matches[1]);
    }
}
elseif (preg_match('#^/admin/users/delete/(\d+)$#', $uri, $matches)) {
    (new UserController())->delete($matches[1]);
}
elseif (preg_match('#^/admin/users/members-by-congregation/(.+)$#', $uri, $matches)) {
    (new UserController())->getMembersByCongregation($matches[1]);
}

// RBAC Permissions Route
elseif ($uri == '/admin/permissions') {
    (new UserController())->permissions();
}

// Rotas de Relatórios de Culto
elseif ($uri == '/admin/service_reports') {
    (new ServiceReportController())->index();
}
elseif ($uri == '/admin/service_reports/create') {
    if ($method == 'POST') {
        (new ServiceReportController())->store();
    } else {
        (new ServiceReportController())->create();
    }
}
elseif (preg_match('#^/admin/service_reports/show/(\d+)$#', $uri, $matches)) {
    (new ServiceReportController())->show($matches[1]);
}
elseif (preg_match('#^/admin/service_reports/visitors/(\d+)$#', $uri, $matches)) {
    (new ServiceReportController())->getVisitors($matches[1]);
}
elseif ($uri == '/admin/reports/general') {
    (new GeneralReportController())->index();
}
elseif (preg_match('#^/admin/service_reports/delete/(\d+)$#', $uri, $matches)) {
    (new ServiceReportController())->delete($matches[1]);
}
elseif (preg_match('#^/admin/service_reports/edit/(\d+)$#', $uri, $matches)) {
    (new ServiceReportController())->edit($matches[1]);
}
elseif (preg_match('#^/admin/service_reports/update/(\d+)$#', $uri, $matches)) {
    (new ServiceReportController())->update($matches[1]);
}

// Rotas de Banners
elseif ($uri == '/admin/banners') {
    (new BannerController())->index();
}
elseif ($uri == '/admin/banners/create') {
    if ($method == 'POST') {
        (new BannerController())->store();
    } else {
        (new BannerController())->create();
    }
}
elseif (preg_match('#^/admin/banners/edit/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new BannerController())->update($matches[1]);
    } else {
        (new BannerController())->edit($matches[1]);
    }
}
elseif (preg_match('#^/admin/banners/delete/(\d+)$#', $uri, $matches)) {
    (new BannerController())->delete($matches[1]);
}

// Portal Routes
elseif ($uri == '/portal/login') {
    if ($method == 'POST') {
        (new MemberAuthController())->login();
    } else {
        (new MemberAuthController())->showLogin();
    }
}
elseif ($uri == '/portal/register') {
    if ($method == 'POST') {
        (new MemberAuthController())->register();
    } else {
        (new MemberAuthController())->showRegister();
    }
}
elseif ($uri == '/portal/logout') {
    (new MemberAuthController())->logout();
}
elseif ($uri == '/portal/dashboard' || $uri == '/portal') {
    (new PortalController())->index();
}
elseif ($uri == '/portal/profile') {
    (new PortalController())->profile();
}
elseif ($uri == '/portal/financial') {
    (new PortalController())->financial();
}
elseif ($uri == '/portal/card') {
    (new PortalController())->card();
}
elseif ($uri == '/portal/agenda') {
    (new PortalController())->agenda();
}

// Rotas de Grupos/Células
elseif ($uri == '/admin/groups') {
    (new GroupController())->index();
}
elseif ($uri == '/admin/groups/create') {
    if ($method == 'POST') {
        (new GroupController())->store();
    } else {
        (new GroupController())->create();
    }
}
elseif (preg_match('#^/admin/groups/show/(\d+)$#', $uri, $matches)) {
    (new GroupController())->show($matches[1]);
}
elseif (preg_match('#^/admin/groups/edit/(\d+)$#', $uri, $matches)) {
    if ($method == 'POST') {
        (new GroupController())->update($matches[1]);
    } else {
        (new GroupController())->edit($matches[1]);
    }
}
elseif (preg_match('#^/admin/groups/delete/(\d+)$#', $uri, $matches)) {
    (new GroupController())->delete($matches[1]);
}
elseif (preg_match('#^/admin/groups/report/(\d+)$#', $uri, $matches)) {
    (new GroupController())->report($matches[1]);
}
elseif ($uri == '/admin/groups/members/add') {
    if ($method == 'POST') {
        (new GroupController())->addMember();
    }
}
elseif ($uri == '/admin/groups/members/remove') {
    if ($method == 'POST') {
        (new GroupController())->removeMember();
    }
}
elseif ($uri == '/admin/groups/members/transfer') {
    if ($method == 'POST') {
        (new GroupController())->transferMember();
    }
}
elseif ($uri == '/admin/groups/members/convert') {
    if ($method == 'POST') {
        (new GroupController())->convertVisitor();
    }
}

else {
    http_response_code(404);
    echo "404 - Página não encontrada";
}
