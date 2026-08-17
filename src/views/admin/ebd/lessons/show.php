<?php $suppressMobileTopbar = true; include __DIR__ . '/../../../layout/header.php'; ?>

<div class="d-none d-lg-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Detalhes da Aula</h1>
        <h5 class="text-muted"><?= htmlspecialchars($lesson['class_name']) ?></h5>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/ebd/lessons/edit/<?= $lesson['id'] ?>" class="btn btn-sm btn-outline-primary me-2">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="/admin/ebd/classes/show/<?= $lesson['class_id'] ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar para Classe
        </a>
    </div>
</div>

<?php
$elsPresentCount = 0;
foreach ($attendance as $elsA) { if (!empty($elsA['present'])) $elsPresentCount++; }
$elsWeekdays = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
$elsWeekday = $elsWeekdays[(int)date('w', strtotime($lesson['lesson_date']))];
$elsCode = '#' . date('Y-md', strtotime($lesson['lesson_date'])) . '-' . strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $lesson['class_name']), 0, 3));
?>
<style>
    .els-wrap { padding-bottom: 100px; }
    .els-topbar { display: flex; align-items: center; gap: .6rem; padding: .3rem 0 1.1rem; }
    .els-back { flex: 0 0 auto; width: 36px; height: 36px; border-radius: 50%; background: #fff; border: 1px solid #eef1f5; color: #101828; display: flex; align-items: center; justify-content: center; }
    .els-id { flex: 1 1 auto; min-width: 0; display: flex; align-items: baseline; gap: .5rem; overflow: hidden; }
    .els-class-name { font-weight: 800; font-size: 1rem; color: #101828; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .els-topbar-sep { color: #c2c8d2; }
    .els-topbar-date { font-size: .8rem; font-weight: 700; color: #c2790a; white-space: nowrap; }
    .els-menu-btn { flex: 0 0 auto; width: 36px; height: 36px; border-radius: 50%; background: #fff; border: 1px solid #eef1f5; color: #101828; display: flex; align-items: center; justify-content: center; }

    .els-topic-pill { display: inline-block; background: #10162b; color: #fff; font-size: .76rem; font-weight: 700; padding: .35rem .8rem; border-radius: 999px; margin-bottom: .8rem; }
    .els-date-big { font-size: 1.7rem; font-weight: 800; color: #101828; line-height: 1.1; }
    .els-weekday { font-size: 1rem; color: #8b93a3; margin-bottom: 1.1rem; }

    .els-notes-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #9aa4b2; margin-bottom: .3rem; }
    .els-notes-text { font-size: .86rem; color: #495057; margin-bottom: 1.3rem; }

    .els-section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: .7rem; }
    .els-section-title { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #9aa4b2; }
    .els-swipe-hint { font-size: .74rem; color: #b7bec8; }
    .els-count-pill { background: #10162b; color: #fff; font-size: .72rem; font-weight: 700; padding: .25rem .7rem; border-radius: 999px; }

    .els-stat-scroll-wrap { position: relative; display: flex; align-items: center; gap: .4rem; margin-bottom: 1.3rem; }
    .els-stat-arrow { flex: 0 0 auto; width: 30px; height: 30px; border-radius: 50%; border: 1px solid #eef1f5; background: #fff; color: #5b6472; display: flex; align-items: center; justify-content: center; font-size: .78rem; }
    .els-stat-scroll { flex: 1 1 auto; display: flex; gap: .6rem; overflow-x: auto; scroll-snap-type: x proximity; padding-bottom: .3rem; scrollbar-width: none; }
    .els-stat-scroll::-webkit-scrollbar { display: none; }
    .els-stat-card { flex: 0 0 auto; scroll-snap-align: start; width: 108px; background: #fff; border: 1px solid #eef1f5; border-radius: 14px; padding: .8rem .85rem; }
    .els-stat-icon { width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .72rem; margin-bottom: .5rem; }
    .els-stat-card.is-red .els-stat-icon { background: rgba(224,83,60,.12); color: #e0533c; }
    .els-stat-card.is-blue .els-stat-icon { background: rgba(59,111,239,.12); color: #3b6fef; }
    .els-stat-card.is-amber .els-stat-icon { background: rgba(194,121,10,.12); color: #c2790a; }
    .els-stat-card.is-green .els-stat-icon { background: rgba(24,165,88,.12); color: #18a558; }
    .els-stat-label { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .02em; }
    .els-stat-card.is-red .els-stat-label { color: #e0533c; }
    .els-stat-card.is-blue .els-stat-label { color: #3b6fef; }
    .els-stat-card.is-amber .els-stat-label { color: #c2790a; }
    .els-stat-card.is-green .els-stat-label { color: #18a558; }
    .els-stat-value { font-size: 1.15rem; font-weight: 800; color: #101828; margin-top: .2rem; }
    .els-stat-unit { font-size: .68rem; font-weight: 600; color: #9aa4b2; }

    .els-att-card { background: #fff; border: 1px solid #eef1f5; border-radius: 16px; padding: 1rem; margin-bottom: 1.1rem; }
    .els-att-empty { text-align: center; padding: 1.2rem .5rem .4rem; }
    .els-att-empty-icon { width: 56px; height: 56px; margin: 0 auto .8rem; border-radius: 16px; background: #f1f2f5; color: #ced4da; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
    .els-att-empty-title { font-weight: 800; font-size: .92rem; color: #101828; margin-bottom: .3rem; }
    .els-att-empty-sub { font-size: .78rem; color: #9aa4b2; margin-bottom: 1rem; }
    .els-att-empty-btn { display: inline-flex; align-items: center; gap: .4rem; background: #10162b; color: #fff; font-weight: 700; font-size: .82rem; padding: .6rem 1.1rem; border-radius: 999px; text-decoration: none; }

    .els-att-row { display: flex; align-items: center; gap: .7rem; padding: .55rem 0; border-bottom: 1px solid #f1f2f5; }
    .els-att-row:last-child { border-bottom: none; }
    .els-att-avatar { flex: 0 0 auto; width: 36px; height: 36px; border-radius: 50%; background: #e7ebf5; color: #5b6472; font-weight: 700; font-size: .72rem; display: flex; align-items: center; justify-content: center; }
    .els-att-name-wrap { flex: 1 1 auto; min-width: 0; }
    .els-att-name { font-weight: 700; font-size: .86rem; color: #101828; }
    .els-att-teacher { font-size: .66rem; font-weight: 700; color: #3b6fef; margin-left: .3rem; }
    .els-att-badge { flex: 0 0 auto; font-size: .68rem; font-weight: 700; padding: .25rem .65rem; border-radius: 999px; }
    .els-att-badge.is-present { background: rgba(24,165,88,.12); color: #18a558; }
    .els-att-badge.is-absent { background: rgba(0,0,0,.06); color: #6c757d; }

    .els-finance-card { background: #18a558; border-radius: 16px; padding: 1rem 1.1rem; color: #fff; display: flex; align-items: center; justify-content: space-between; gap: .8rem; margin-bottom: 1rem; }
    .els-finance-left { display: flex; align-items: center; gap: .8rem; min-width: 0; }
    .els-finance-icon { flex: 0 0 auto; width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,.18); display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .els-finance-label { font-size: .64rem; font-weight: 700; letter-spacing: .04em; opacity: .85; }
    .els-finance-value { font-size: 1.15rem; font-weight: 800; }
    .els-finance-sub { font-size: .68rem; opacity: .8; margin-top: .1rem; }
    .els-finance-right { text-align: right; font-size: .68rem; opacity: .9; max-width: 130px; }

    .els-footer-line { display: flex; align-items: center; justify-content: space-between; font-size: .72rem; color: #adb5bd; margin-bottom: 1.3rem; }
    .els-footer-dot { width: 8px; height: 8px; border-radius: 50%; background: #18a558; margin-left: .4rem; display: inline-block; }

    .els-bottom-cta { position: fixed; left: 0; right: 0; bottom: 0; padding: 14px 18px calc(18px + env(safe-area-inset-bottom)); background: #f6f7f9; z-index: 1025; }
    .els-bottom-cta a { display: flex; align-items: center; justify-content: center; gap: .4rem; background: #fff; border: 1px solid #e3e7ee; color: #101828; text-align: center; font-weight: 700; font-size: .92rem; padding: 14px 0; border-radius: 999px; text-decoration: none; }
</style>

<div class="els-wrap d-lg-none">
    <div class="els-topbar">
        <button type="button" id="elsBackBtn" class="els-back" data-fallback="/admin/ebd/classes/show/<?= $lesson['class_id'] ?>" aria-label="Voltar"><i class="fas fa-arrow-left"></i></button>
        <div class="els-id">
            <span class="els-class-name"><?= htmlspecialchars($lesson['class_name']) ?></span>
            <span class="els-topbar-sep">•</span>
            <span class="els-topbar-date"><?= date('d/m/Y', strtotime($lesson['lesson_date'])) ?></span>
        </div>
        <div class="dropdown">
            <button type="button" class="els-menu-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mais opções"><i class="fas fa-ellipsis"></i></button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><a class="dropdown-item" href="/admin/ebd/lessons/edit/<?= $lesson['id'] ?>"><i class="fas fa-edit me-2 text-muted"></i>Editar Aula</a></li>
                <li><a class="dropdown-item text-danger btn-delete-lesson" href="/admin/ebd/lessons/delete/<?= $lesson['id'] ?>" data-topic="<?= htmlspecialchars($lesson['topic'] ?: 'sem tema') ?>"><i class="fas fa-trash me-2"></i>Excluir Aula</a></li>
            </ul>
        </div>
    </div>

    <?php if (!empty($lesson['topic'])): ?>
        <span class="els-topic-pill"><?= htmlspecialchars($lesson['topic']) ?></span>
    <?php endif; ?>
    <div class="els-date-big"><?= date('d/m/Y', strtotime($lesson['lesson_date'])) ?></div>
    <div class="els-weekday"><?= $elsWeekday ?></div>

    <?php if (!empty($lesson['notes'])): ?>
        <div class="els-notes-label">Observações</div>
        <div class="els-notes-text"><?= nl2br(htmlspecialchars($lesson['notes'])) ?></div>
    <?php endif; ?>

    <div class="els-section-header">
        <span class="els-section-title">Resumo da Aula</span>
        <span class="els-swipe-hint">deslize <i class="fas fa-arrow-right"></i></span>
    </div>
    <div class="els-stat-scroll-wrap">
        <button type="button" class="els-stat-arrow" id="elsStatPrev" aria-label="Anterior"><i class="fas fa-chevron-left"></i></button>
        <div class="els-stat-scroll" id="elsStatScroll">
            <div class="els-stat-card is-red">
                <div class="els-stat-icon"><i class="fas fa-user-plus"></i></div>
                <div class="els-stat-label">Visitantes</div>
                <div class="els-stat-value"><?= (int)$lesson['visitors_count'] ?></div>
                <div class="els-stat-unit"><?= (int)$lesson['visitors_count'] === 1 ? 'pessoa' : 'pessoas' ?></div>
            </div>
            <div class="els-stat-card is-blue">
                <div class="els-stat-icon"><i class="fas fa-bible"></i></div>
                <div class="els-stat-label">Bíblias</div>
                <div class="els-stat-value"><?= (int)$lesson['bibles_count'] ?></div>
                <div class="els-stat-unit">unid.</div>
            </div>
            <div class="els-stat-card is-amber">
                <div class="els-stat-icon"><i class="fas fa-book"></i></div>
                <div class="els-stat-label">Revistas</div>
                <div class="els-stat-value"><?= (int)$lesson['magazines_count'] ?></div>
                <div class="els-stat-unit">unid.</div>
            </div>
            <div class="els-stat-card is-green">
                <div class="els-stat-icon"><i class="fas fa-hand-holding-usd"></i></div>
                <div class="els-stat-label">Oferta</div>
                <div class="els-stat-value" style="font-size: .95rem;">R$ <?= number_format($lesson['offerings'], 2, ',', '.') ?></div>
            </div>
        </div>
        <button type="button" class="els-stat-arrow" id="elsStatNext" aria-label="Próximo"><i class="fas fa-chevron-right"></i></button>
    </div>

    <div class="els-section-header">
        <span class="els-section-title">Chamada / Presença</span>
        <span class="els-count-pill"><?= $elsPresentCount ?> aluno<?= $elsPresentCount === 1 ? '' : 's' ?></span>
    </div>
    <div class="els-att-card">
        <?php if (empty($attendance)): ?>
            <div class="els-att-empty">
                <div class="els-att-empty-icon"><i class="fas fa-user-group"></i></div>
                <div class="els-att-empty-title">Nenhum registro de presença</div>
                <div class="els-att-empty-sub">A chamada ainda não foi feita para esta aula.</div>
                <a href="/admin/ebd/lessons/edit/<?= $lesson['id'] ?>" class="els-att-empty-btn"><i class="fas fa-user-plus"></i> Registrar Presença</a>
            </div>
        <?php else: ?>
            <?php foreach ($attendance as $att):
                $elsParts = preg_split('/\s+/', trim((string)$att['student_name']));
                $elsInitials = mb_strtoupper(mb_substr($elsParts[0], 0, 1) . (count($elsParts) > 1 ? mb_substr(end($elsParts), 0, 1) : ''), 'UTF-8');
            ?>
                <div class="els-att-row">
                    <span class="els-att-avatar"><?= htmlspecialchars($elsInitials) ?></span>
                    <div class="els-att-name-wrap">
                        <span class="els-att-name"><?= htmlspecialchars($att['student_name']) ?></span>
                        <?php if (!empty($att['is_teacher'])): ?><span class="els-att-teacher">Professor</span><?php endif; ?>
                    </div>
                    <span class="els-att-badge <?= !empty($att['present']) ? 'is-present' : 'is-absent' ?>"><?= !empty($att['present']) ? 'Presente' : 'Ausente' ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($lesson['offerings'] > 0): ?>
        <div class="els-finance-card">
            <div class="els-finance-left">
                <div class="els-finance-icon"><i class="fas fa-dollar-sign"></i></div>
                <div>
                    <div class="els-finance-label">ARRECADADO</div>
                    <div class="els-finance-value">R$ <?= number_format($lesson['offerings'], 2, ',', '.') ?></div>
                </div>
            </div>
            <div class="els-finance-right">Integrado ao Caixa Geral como Oferta EBD</div>
        </div>
    <?php endif; ?>

    <div class="els-footer-line">
        <span>ID da aula: <?= htmlspecialchars($elsCode) ?> • <?= date('d/m/Y', strtotime($lesson['lesson_date'])) ?></span>
        <span class="els-footer-dot"></span>
    </div>
</div>

<div class="els-bottom-cta d-lg-none">
    <a href="/admin/ebd/lessons/edit/<?= $lesson['id'] ?>"><i class="fas fa-pen"></i> Editar Aula</a>
</div>

<script>
(function () {
    var backBtn = document.getElementById('elsBackBtn');
    if (backBtn) {
        backBtn.addEventListener('click', function () {
            var cameFromSameSite = document.referrer && document.referrer.indexOf(window.location.origin) === 0;
            if (cameFromSameSite && window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = backBtn.getAttribute('data-fallback');
            }
        });
    }

    var scroller = document.getElementById('elsStatScroll');
    var prevBtn = document.getElementById('elsStatPrev');
    var nextBtn = document.getElementById('elsStatNext');
    if (scroller && prevBtn && nextBtn) {
        prevBtn.addEventListener('click', function () { scroller.scrollBy({ left: -124, behavior: 'smooth' }); });
        nextBtn.addEventListener('click', function () { scroller.scrollBy({ left: 124, behavior: 'smooth' }); });
    }

    document.querySelectorAll('.btn-delete-lesson').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var href = btn.getAttribute('href');
            var topic = btn.getAttribute('data-topic');
            Swal.fire({
                title: 'Excluir aula?',
                text: 'Tem certeza que deseja excluir a aula "' + topic + '"? O registro financeiro (se houver) não será excluído automaticamente.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.isConfirmed) window.location.href = href;
            });
        });
    });
})();
</script>

<div class="row d-none d-lg-flex">
    <!-- Card Principal -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i> 
                        <?= date('d/m/Y', strtotime($lesson['lesson_date'])) ?>
                    </h5>
                    <span class="badge bg-secondary"><?= htmlspecialchars($lesson['topic']) ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center mb-4">
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted d-block text-uppercase">Visitantes</small>
                            <h3 class="mb-0 text-primary"><?= $lesson['visitors_count'] ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted d-block text-uppercase">Bíblias</small>
                            <h3 class="mb-0 text-info"><?= $lesson['bibles_count'] ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted d-block text-uppercase">Revistas</small>
                            <h3 class="mb-0 text-warning"><?= $lesson['magazines_count'] ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted d-block text-uppercase">Oferta</small>
                            <h3 class="mb-0 text-success">R$ <?= number_format($lesson['offerings'], 2, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>

                <?php if ($lesson['notes']): ?>
                <div class="alert alert-secondary">
                    <strong><i class="fas fa-sticky-note me-1"></i> Observações:</strong><br>
                    <?= nl2br(htmlspecialchars($lesson['notes'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Lista de Presença -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h6 class="mb-0">Chamada / Presença</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Aluno</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance as $att): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($att['student_name']) ?>
                                <?php if (!empty($att['is_teacher'])): ?>
                                    <span class="badge bg-info text-dark ms-2" style="font-size: 0.75em;">Professor</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($att['present']): ?>
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i> Presente</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i> Ausente</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($attendance)): ?>
                        <tr>
                            <td colspan="2" class="text-center py-3 text-muted">Nenhum registro de presença encontrado.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Card Lateral Financeiro -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-success text-white">
            <div class="card-body">
                <h5 class="card-title border-bottom border-white pb-2 mb-3">Resumo Financeiro</h5>
                <p class="mb-1">Valor Arrecadado:</p>
                <h2 class="mb-3">R$ <?= number_format($lesson['offerings'], 2, ',', '.') ?></h2>
                <p class="small opacity-75 mb-0">
                    <i class="fas fa-check-circle me-1"></i> 
                    Integrado ao Caixa Geral como "Oferta EBD"
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
