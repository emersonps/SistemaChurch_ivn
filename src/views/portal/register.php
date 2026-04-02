<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Membro - Cadastro</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/img/logo.png?v=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-red: #b30000;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        
        /* Simple Header Style */
        .simple-header {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .brand-logo-header {
            font-weight: 700;
            color: var(--primary-red);
            font-size: 1.5rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .brand-logo-header img {
            height: 40px;
            width: auto;
        }
        .nav-link-home {
            color: #333;
            font-weight: 600;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 20px;
            transition: all 0.3s;
        }
        .nav-link-home:hover {
            background-color: var(--primary-red);
            color: white;
        }
        
        .login-card { max-width: 450px; margin: 50px auto; border-radius: 15px; overflow: hidden; }
        .card-header-custom { background-color: var(--primary-red); color: white; padding: 20px; text-align: center; }
    </style>
</head>
<body>

<!-- Simple Public Header -->
<header class="simple-header">
    <div class="container header-container">
        <a href="/" class="brand-logo-header">
            <img src="/assets/img/logo.png" alt="IVN Logo">
            IVN
        </a>
        <a href="/" class="nav-link-home">
            <i class="fas fa-home me-1"></i> Início
        </a>
    </div>
</header>

<div class="container">
    <div class="card login-card shadow-lg border-0">
        <div class="card-header-custom">
            <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i> Primeiro Acesso</h4>
        </div>
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <p class="text-muted mb-1">Bem-vindo ao Portal do Membro!</p>
                <small class="text-secondary">Informe seus dados para localizar seu cadastro e criar uma senha de acesso.</small>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro no Cadastro',
                            text: '<?= addslashes($error) ?>',
                            confirmButtonColor: '#d33'
                        });
                    });
                </script>
            <?php endif; ?>

            <form action="/portal/register" method="POST">
                <div class="mb-3">
                    <label class="form-label">CPF (Apenas números)</label>
                    <input type="text" name="cpf" class="form-control" placeholder="000.000.000-00" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Data de Nascimento</label>
                    <input type="text" name="birth_date" id="birth_date" class="form-control" placeholder="DD/MM/AAAA" inputmode="numeric" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Crie uma Senha</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirme a Senha</label>
                    <div class="input-group">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-danger">Ativar Conta</button>
                    <a href="/portal/login" class="btn btn-outline-secondary">Já tenho senha</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function(){
        $('input[name="cpf"]').mask('000.000.000-00');
        $('#birth_date').mask('00/00/0000');

        function togglePasswordVisibility(buttonId, inputId) {
            $(buttonId).click(function(){
                var passwordField = $(inputId);
                var passwordFieldType = passwordField.attr('type');
                if(passwordFieldType == 'password'){
                    passwordField.attr('type', 'text');
                    $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        }

        togglePasswordVisibility('#togglePassword', '#password');
        togglePasswordVisibility('#toggleConfirmPassword', '#confirm_password');
    });
</script>

</body>
</html>
