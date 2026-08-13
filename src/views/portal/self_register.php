<?php $siteProfile = getChurchSiteProfileSettings(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Cadastro - <?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>?v=1">

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

        .login-card { max-width: 500px; margin: 50px auto; border-radius: 15px; overflow: hidden; }
        .card-header-custom { background-color: var(--primary-red); color: white; padding: 20px; text-align: center; }
    </style>
</head>
<body>

<!-- Simple Public Header -->
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
        <div class="card-header-custom">
            <h4 class="mb-0"><i class="fas fa-file-signature me-2"></i> Ficha de Cadastro de Membro</h4>
        </div>
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <small class="text-secondary">Preencha seus dados básicos para se cadastrar como membro.</small>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="/portal/cadastro" method="POST">
                <div class="mb-3">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required autofocus>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">CPF *</label>
                        <input type="text" name="cpf" id="cpf" class="form-control" placeholder="000.000.000-00" value="<?= htmlspecialchars($old['cpf'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Data de Nascimento *</label>
                        <input type="text" name="birth_date" id="birth_date" class="form-control" placeholder="DD/MM/AAAA" inputmode="numeric" value="<?= htmlspecialchars($old['birth_date'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">WhatsApp *</label>
                        <input type="text" name="phone" id="phone" class="form-control" placeholder="(00) 00000-0000" value="<?= htmlspecialchars($old['phone'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Endereço</label>
                    <input type="text" name="address" class="form-control" placeholder="Rua, número, bairro, cidade" value="<?= htmlspecialchars($old['address'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Congregação *</label>
                    <select class="form-select" name="congregation_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($congregations as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (isset($old['congregation_id']) && $old['congregation_id'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-danger">Enviar Cadastro</button>
                    <a href="/portal/register" class="btn btn-outline-secondary">Já sou cadastrado (Primeiro Acesso)</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function(){
        $('#cpf').mask('000.000.000-00');
        $('#birth_date').mask('00/00/0000');
        $('#phone').mask('(00) 00000-0000');
    });
</script>

</body>
</html>
