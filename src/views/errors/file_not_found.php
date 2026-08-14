<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arquivo não encontrado</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }
        .not-found-card {
            background: #fff;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        }
        .not-found-icon {
            width: 74px;
            height: 74px;
            border-radius: 50%;
            background: rgba(179,0,0,0.10);
            color: #b30000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.25rem;
        }
        .not-found-title {
            font-weight: 800;
            font-size: 1.3rem;
            color: #1a1a1a;
            margin-bottom: .5rem;
        }
        .not-found-desc {
            color: #6c757d;
            font-size: .92rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="not-found-card">
        <div class="not-found-icon"><i class="fas fa-file-circle-exclamation"></i></div>
        <div class="not-found-title">Arquivo não encontrado</div>
        <p class="not-found-desc">
            <?php if (!empty($title)): ?>
                O PDF do estudo "<strong><?= htmlspecialchars($title) ?></strong>" não está mais disponível no servidor.
            <?php else: ?>
                Este estudo não foi encontrado ou o PDF não está mais disponível no servidor.
            <?php endif; ?>
            Ele pode ter sido removido ou movido. Entre em contato com a secretaria se precisar dele novamente.
        </p>
        <a href="<?= htmlspecialchars($backUrl ?? '/') ?>" class="btn btn-primary rounded-pill fw-semibold px-4">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</body>
</html>
