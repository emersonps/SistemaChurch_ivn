<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/members" class="text-decoration-none">Membros</a></li>
                <li class="breadcrumb-item active">Importar</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Importar Membros</h1>
    </div>
    <a href="/admin/members" class="btn btn-outline-secondary rounded-pill fw-semibold px-3">
        <i class="fas fa-arrow-left me-1"></i> Voltar
    </a>
</div>

<style>
    .member-form-topbar {
        position: sticky;
        top: 0;
        z-index: 1030;
        background: #f8f9fa;
        padding-bottom: .85rem;
    }
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        margin-bottom: 1.25rem;
        overflow: hidden;
    }
    .member-form-card-header {
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        background: #fafafa;
    }
    .member-form-badge {
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #eef0f2;
        color: #212529;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: .95rem;
    }
    .member-form-card-title {
        font-weight: 800;
        font-size: 1.05rem;
        color: #1a1a1a;
        line-height: 1.2;
    }
    .member-form-card-subtitle {
        font-size: .82rem;
        color: #868e96;
        margin-top: .1rem;
    }
    .member-form-card-body { padding: 1.25rem; }
    .member-form-card-body .form-label {
        font-weight: 600;
        font-size: .88rem;
        color: #343a40;
    }
    .member-form-card-body .form-control,
    .member-form-card-body .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
        padding: .55rem .8rem;
    }
    .member-form-card-body .form-control:focus,
    .member-form-card-body .form-select:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }

    .import-columns-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .import-columns-table td {
        vertical-align: middle;
        padding-top: .55rem;
        padding-bottom: .55rem;
    }
    .import-columns-table code {
        background: #f1f3f5;
        padding: .15rem .4rem;
        border-radius: 6px;
        font-size: .82rem;
    }
    .req-pill {
        display: inline-block;
        padding: .2rem .55rem;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 700;
    }
    .req-pill.is-required { background: rgba(179,0,0,0.10); color: #b30000; }
    .req-pill.is-optional { background: rgba(0,0,0,0.06); color: #6c757d; }
</style>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="member-form-card">
            <div class="member-form-card-header">
                <div class="member-form-badge"><i class="fas fa-file-import"></i></div>
                <div>
                    <div class="member-form-card-title">Enviar Planilha</div>
                    <div class="member-form-card-subtitle">Importe membros em lote a partir de um arquivo CSV.</div>
                </div>
            </div>
            <div class="member-form-card-body">
                <form action="/admin/members/import" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Congregação de Destino</label>
                        <select name="congregation_id" class="form-select" <?= !empty($_SESSION['user_congregation_id']) ? 'disabled' : 'required' ?>>
                            <option value="">Selecione...</option>
                            <?php foreach (($congregations ?? []) as $congregation): ?>
                                <option value="<?= $congregation['id'] ?>" <?= !empty($_SESSION['user_congregation_id']) && (int)$_SESSION['user_congregation_id'] === (int)$congregation['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($congregation['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($_SESSION['user_congregation_id'])): ?>
                            <input type="hidden" name="congregation_id" value="<?= (int)$_SESSION['user_congregation_id'] ?>">
                            <div class="form-text">A importação será vinculada automaticamente à congregação do usuário logado.</div>
                        <?php else: ?>
                            <div class="form-text">Selecione a congregação para a qual toda a lista será importada.</div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Arquivo CSV</label>
                        <input type="file" name="spreadsheet" class="form-control" accept=".csv,text/csv" required>
                        <div class="form-text">Use o modelo disponibilizado ao lado. O sistema aceita CSV separado por vírgula, ponto e vírgula ou tabulação.</div>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill fw-semibold w-100">
                        <i class="fas fa-file-import me-1"></i> Importar Membros
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="member-form-card">
            <div class="member-form-card-header">
                <div class="member-form-badge"><i class="fas fa-table"></i></div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="member-form-card-title">Modelo da Planilha</div>
                            <div class="member-form-card-subtitle">Colunas esperadas no CSV.</div>
                        </div>
                        <a href="/admin/members/import/template" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold px-3">
                            <i class="fas fa-download me-1"></i> Baixar Modelo
                        </a>
                    </div>
                </div>
            </div>
            <div class="member-form-card-body">
                <div class="small text-muted mb-3"><?= htmlspecialchars(implode('; ', $columns ?? [])) ?></div>
                <div class="table-responsive">
                    <table class="table table-hover import-columns-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>Obrigatória</th>
                                <th>Coluna</th>
                                <th>Exemplo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><span class="req-pill is-required">Sim</span></td><td><code>name</code></td><td>Maria Souza</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>email</code></td><td>maria@email.com</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>phone</code></td><td>(11)99999-1111</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>birth_date</code></td><td>1990-08-15</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>gender</code></td><td>Feminino</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>cpf</code></td><td>123.456.789-00</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>rg</code></td><td>12.345.678-9</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>address</code></td><td>Rua Central</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>address_number</code></td><td>100</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>neighborhood</code></td><td>Centro</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>zip_code</code></td><td>01000-000</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>state</code></td><td>SP</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>city</code></td><td>São Paulo</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>role</code></td><td>Membro</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>nationality</code></td><td>Brasileira</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>birthplace</code></td><td>São Paulo</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>father_name</code></td><td>José da Silva</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>mother_name</code></td><td>Maria da Silva</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>children_count</code></td><td>2</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>profession</code></td><td>Professora</td></tr>
                            <tr><td><span class="req-pill is-optional">Não</span></td><td><code>admission_date</code></td><td><?= date('Y-m-d') ?></td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-warning py-2 mb-0">
                    Se o CPF já existir, a linha será ignorada para evitar duplicidade. A congregação é escolhida nesta tela e aplicada à planilha inteira.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
