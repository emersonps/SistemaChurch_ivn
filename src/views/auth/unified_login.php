<?php
// src/views/auth/unified_login.php
//
// Tela de login única com alternância Membro/Administrativo. Incluída por
// admin/login.php e portal/login.php (cada um define $activeTab antes do
// require) — os dois formulários continuam postando pros endpoints
// originais (/portal/login e /admin/login), nada mudou no backend.
$siteProfile = getChurchSiteProfileSettings();
$activeTab = $activeTab ?? 'member';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - <?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>?v=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #0d6efd;
            --primary-red: #b30000;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }

        .simple-header {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-container { display: flex; justify-content: space-between; align-items: center; }
        .brand-logo-header {
            font-weight: 700;
            color: var(--primary-red);
            font-size: 1.5rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .brand-logo-header img { height: 40px; width: auto; }
        .nav-link-home {
            color: #333;
            font-weight: 600;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 20px;
            transition: all 0.3s;
        }
        .nav-link-home:hover { background-color: var(--primary-red); color: white; }

        .login-card { max-width: 420px; margin: 50px auto; border-radius: 18px; overflow: hidden; }

        .tab-switch {
            display: flex;
            background: #eef0f3;
            border-radius: 999px;
            padding: 4px;
            margin: 22px 22px 0;
            gap: 4px;
        }
        .tab-switch button {
            flex: 1;
            border: 0;
            background: transparent;
            padding: 10px 12px;
            border-radius: 999px;
            font-weight: 600;
            font-size: .88rem;
            color: #6c757d;
            transition: all .2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .tab-switch button.active.tab-member { background: var(--primary-red); color: #fff; box-shadow: 0 2px 8px rgba(179,0,0,.25); }
        .tab-switch button.active.tab-admin { background: var(--primary-blue); color: #fff; box-shadow: 0 2px 8px rgba(13,110,253,.25); }

        .login-panel { display: none; padding: 26px 26px 8px; }
        .login-panel.active { display: block; }
        .login-panel-header { text-align: center; margin-bottom: 20px; }
        .login-panel-header .icon-circle {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #fff;
            margin-bottom: 10px;
        }
        .login-panel[data-tab="member"] .icon-circle { background: var(--primary-red); }
        .login-panel[data-tab="admin"] .icon-circle { background: var(--primary-blue); }
        .login-panel h4 { margin: 0; font-weight: 700; }
        .login-panel p { color: #8a8f98; margin: 4px 0 0; font-size: .9rem; }

        .login-footer { padding: 4px 26px 26px; text-align: center; }
    </style>
</head>
<body>

<header class="simple-header">
    <div class="container header-container">
        <a href="/" class="brand-logo-header">
            <img src="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" alt="<?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?> Logo">
            <?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?>
        </a>
        <a href="/" class="nav-link-home">
            <i class="fas fa-home me-1"></i> Início
        </a>
    </div>
</header>

<div class="container">
    <div class="card login-card shadow-lg border-0">
        <div class="tab-switch">
            <button type="button" class="tab-member <?= $activeTab === 'member' ? 'active' : '' ?>" data-target="member">
                <i class="fas fa-user"></i> Membro
            </button>
            <button type="button" class="tab-admin <?= $activeTab === 'admin' ? 'active' : '' ?>" data-target="admin">
                <i class="fas fa-user-shield"></i> Administrativo
            </button>
        </div>

        <!-- Painel: Membro -->
        <div class="login-panel <?= $activeTab === 'member' ? 'active' : '' ?>" data-tab="member">
            <div class="login-panel-header">
                <div class="icon-circle"><i class="fas fa-user"></i></div>
                <h4>Portal do Membro</h4>
                <p>Bem-vindo de volta!</p>
            </div>

            <form action="/portal/login" method="POST" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label">CPF</label>
                    <input type="text" name="cpf" class="form-control" placeholder="000.000.000-00" autocomplete="off" <?= $activeTab === 'member' ? 'required' : '' ?>>
                </div>
                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control member-password-field" <?= $activeTab === 'member' ? 'required' : '' ?>>
                        <button class="btn btn-outline-secondary toggle-password-btn" type="button">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Entrar</button>
                    <a href="/portal/register" class="btn btn-outline-danger">Primeiro Acesso</a>
                </div>
            </form>
        </div>

        <!-- Painel: Administrativo -->
        <div class="login-panel <?= $activeTab === 'admin' ? 'active' : '' ?>" data-tab="admin">
            <div class="login-panel-header">
                <div class="icon-circle"><i class="fas fa-user-shield"></i></div>
                <h4>Área Administrativa</h4>
                <p>Acesso Restrito</p>
            </div>

            <form method="POST" action="/admin/login">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Usuário</label>
                    <input type="text" name="username" class="form-control" <?= $activeTab === 'admin' ? 'required' : '' ?>>
                </div>
                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control admin-password-field" <?= $activeTab === 'admin' ? 'required' : '' ?>>
                        <button class="btn btn-outline-secondary toggle-password-btn" type="button">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>

        <div class="login-footer">
            <a href="/" class="text-decoration-none text-muted small">Voltar para o site</a>
        </div>
    </div>
</div>

<?php if (!empty($error)): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Erro no Login',
                text: '<?= addslashes($error) ?>',
                confirmButtonColor: '#d33'
            });
        });
    </script>
<?php endif; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function () {
        $('input[name="cpf"]').mask('000.000.000-00');

        $('.toggle-password-btn').click(function () {
            var field = $(this).siblings('input');
            var type = field.attr('type') === 'password' ? 'text' : 'password';
            field.attr('type', type);
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });

        $('.tab-switch button').click(function () {
            var target = $(this).data('target');

            $('.tab-switch button').removeClass('active');
            $(this).addClass('active');

            $('.login-panel').removeClass('active');
            $('.login-panel[data-tab="' + target + '"]').addClass('active');

            // Só o painel visível deve exigir os campos — evita bloquear o
            // submit com "required" de um campo escondido no outro painel.
            $('.login-panel').each(function () {
                var isVisible = $(this).attr('data-tab') === target;
                $(this).find('input[name="cpf"], input[name="username"], .member-password-field, .admin-password-field').prop('required', isVisible);
            });
        });
    });
</script>

</body>
</html>
