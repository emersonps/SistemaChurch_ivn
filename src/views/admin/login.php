<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração - Login</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/img/logo.png?v=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #0d6efd;
            --primary-red: #b30000;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #e9ecef; }
        
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
        
        .login-card { max-width: 400px; margin: 50px auto; border-radius: 15px; overflow: hidden; }
        .card-header-custom { background-color: var(--primary-blue); color: white; padding: 20px; text-align: center; }
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
            <h4 class="mb-0"><i class="fas fa-lock me-2"></i> Área Administrativa</h4>
        </div>
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <p class="text-muted">Acesso Restrito</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro de Acesso',
                            text: '<?= addslashes($error) ?>',
                            confirmButtonColor: '#d33'
                        });
                    });
                </script>
            <?php endif; ?>

            <form method="POST" action="/admin/login">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Usuário</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
            <div class="mt-3 text-center">
                <a href="/" class="text-decoration-none text-muted small">Voltar para o site</a>
            </div>
        </div>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function (e) {
        // toggle the type attribute
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // toggle the eye slash icon
        this.querySelector('i').classList.toggle('fa-eye-slash');
        this.querySelector('i').classList.toggle('fa-eye');
    });
</script>

</body>
</html>
