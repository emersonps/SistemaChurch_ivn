<?php include __DIR__ . '/layout_developer.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Importação em Massa (Saídas / Despesas)</h1>
</div>

<?php if (isset($_SESSION['import_result'])): ?>
    <?php $res = $_SESSION['import_result']; ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> <strong><?= $res['success'] ?? 0 ?></strong> registros novos importados com sucesso!
        <?php if (!empty($res['updated'])): ?>
            <br><i class="fas fa-info-circle me-2"></i> <strong><?= $res['updated'] ?></strong> registros já existiam no sistema e foram apenas atualizados/ignorados (sem duplicar).
        <?php endif; ?>
        
        <?php if (!empty($res['errors'])): ?>
            <hr>
            <p class="mb-0 text-danger">Avisos e Erros:</p>
            <ul class="mb-0 text-danger small">
                <?php foreach ($res['errors'] as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['import_result']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-file-export me-2"></i> Colar Dados de Saída</h5>
            </div>
            <div class="card-body">
                <form action="/developer/import/expenses" method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Congregação de Destino</label>
                            <select class="form-select" name="congregation_id">
                                <?php foreach ($congregations as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $c['id'] == 6 ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ano de Referência</label>
                            <input type="number" name="year" class="form-control" value="<?= date('Y') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dados Brutos (Copie e Cole)</label>
                        <textarea name="raw_data" class="form-control border-danger" rows="15" placeholder="Exemplo com 3 colunas:
10/mar 	 Instalação do ar condicionado 	 R$ 450,00
15/abr 	 Compra de cadeiras 	 R$ 1.200,00

Exemplo com 4 colunas (Categoria inclusa):
10/mar 	 Instalação do ar 	 Manutenção 	 R$ 450,00"></textarea>
                        <div class="form-text">
                            O formato deve ser: <strong>DIA/MÊS [TAB] DESCRIÇÃO [TAB] CATEGORIA (Opcional) [TAB] VALOR</strong>.<br>
                            Separado por TABs ou múltiplos espaços. Se não tiver categoria, será salva como "Outros".
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-upload me-2"></i> Processar Importação de Saídas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Instruções</h5>
            </div>
            <div class="card-body">
                <p>Use esta ferramenta para importar listas rápidas de <strong>Despesas / Saídas</strong>.</p>
                <ul>
                    <li>As datas devem estar no formato <code>DD/mmm</code> (ex: 10/mar).</li>
                    <li>O ano será o definido no campo "Ano de Referência".</li>
                    <li>Valores monetários podem ter "R$", pontos e vírgulas.</li>
                    <li>Se a sua lista tem 3 colunas (Data, Descrição, Valor), o sistema vai colocar a categoria "Outros".</li>
                    <li>Se tiver 4 colunas, a terceira será usada como Categoria.</li>
                </ul>
            </div>
        </div>

        <div class="card shadow-sm border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Zona de Perigo</h5>
            </div>
            <div class="card-body text-center">
                <p class="text-muted small mb-3">Isso apagará <strong>TODAS AS SAÍDAS</strong> (Despesas) de todas as congregações no banco de dados. Esta ação não pode ser desfeita.</p>
                <form action="/developer/import/clear-expenses" method="POST" onsubmit="return confirm('ATENÇÃO: Você tem CERTEZA ABSOLUTA que deseja APAGAR TODAS as saídas de todas as congregações? Essa ação é irreversível!');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="fas fa-trash-alt me-2"></i> Limpar Todas as Saídas
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>