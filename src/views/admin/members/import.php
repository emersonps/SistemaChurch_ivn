<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Importar Membros</h1>
    <a href="/admin/members" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Voltar
    </a>
</div>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Enviar Planilha</div>
            <div class="card-body">
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
                            <small class="text-muted">A importação será vinculada automaticamente à congregação do usuário logado.</small>
                        <?php else: ?>
                            <small class="text-muted">Selecione a congregação para a qual toda a lista será importada.</small>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Arquivo CSV</label>
                        <input type="file" name="spreadsheet" class="form-control" accept=".csv,text/csv" required>
                        <small class="text-muted">Use o modelo disponibilizado abaixo. O sistema aceita CSV separado por vírgula, ponto e vírgula ou tabulação.</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-file-import me-1"></i> Importar Membros
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Modelo da Planilha</span>
                <a href="/admin/members/import/template" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-download me-1"></i> Baixar Modelo
                </a>
            </div>
            <div class="card-body">
                <p class="mb-2">Colunas esperadas no CSV:</p>
                <div class="small text-muted mb-3"><?= htmlspecialchars(implode('; ', $columns ?? [])) ?></div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Obrigatória</th>
                                <th>Coluna</th>
                                <th>Exemplo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Sim</td><td>name</td><td>Maria Souza</td></tr>
                            <tr><td>Não</td><td>email</td><td>maria@email.com</td></tr>
                            <tr><td>Não</td><td>phone</td><td>(11)99999-1111</td></tr>
                            <tr><td>Não</td><td>birth_date</td><td>1990-08-15</td></tr>
                            <tr><td>Não</td><td>gender</td><td>Feminino</td></tr>
                            <tr><td>Não</td><td>cpf</td><td>123.456.789-00</td></tr>
                            <tr><td>Não</td><td>rg</td><td>12.345.678-9</td></tr>
                            <tr><td>Não</td><td>address</td><td>Rua Central</td></tr>
                            <tr><td>Não</td><td>address_number</td><td>100</td></tr>
                            <tr><td>Não</td><td>neighborhood</td><td>Centro</td></tr>
                            <tr><td>Não</td><td>zip_code</td><td>01000-000</td></tr>
                            <tr><td>Não</td><td>state</td><td>SP</td></tr>
                            <tr><td>Não</td><td>city</td><td>São Paulo</td></tr>
                            <tr><td>Não</td><td>role</td><td>Membro</td></tr>
                            <tr><td>Não</td><td>nationality</td><td>Brasileira</td></tr>
                            <tr><td>Não</td><td>birthplace</td><td>São Paulo</td></tr>
                            <tr><td>Não</td><td>father_name</td><td>José da Silva</td></tr>
                            <tr><td>Não</td><td>mother_name</td><td>Maria da Silva</td></tr>
                            <tr><td>Não</td><td>children_count</td><td>2</td></tr>
                            <tr><td>Não</td><td>profession</td><td>Professora</td></tr>
                            <tr><td>Não</td><td>admission_date</td><td><?= date('Y-m-d') ?></td></tr>
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
