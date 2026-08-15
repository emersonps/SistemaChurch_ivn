<?php include __DIR__ . '/../layout/header.php'; ?>
<?php
$canToggleFinancialValues = in_array($_SESSION['user_role'] ?? '', ['admin', 'secretary'], true);
$tithesSumFormatted = 'R$ ' . number_format($tithes_sum, 2, ',', '.');
$offeringsSumFormatted = 'R$ ' . number_format($offerings_sum, 2, ',', '.');
$totalFinancialFormatted = 'R$ ' . number_format($total_financial, 2, ',', '.');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 mb-0">Painel</h1>
    <div class="d-flex align-items-center gap-2">
        <?php if ($canToggleFinancialValues): ?>
            <button type="button" class="btn btn-sm btn-outline-secondary icon-btn" id="toggle-dashboard-values" title="Exibir valores" aria-label="Exibir valores">
                <i class="fas fa-eye"></i>
            </button>
        <?php endif; ?>
        <form class="d-flex align-items-center gap-2" method="GET">
            <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php
                $months = [
                    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
                    '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto',
                    '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
                ];
                foreach ($months as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $k == $selected_month ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php for($y = date('Y'); $y >= 2015; $y--): ?>
                    <option value="<?= $y ?>" <?= $y == $selected_year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>
</div>

<style>
    .member-form-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        overflow: hidden;
    }
    .member-form-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        background: #fafafa;
    }
    .member-form-card-title {
        font-weight: 800;
        font-size: 1.02rem;
        color: #1a1a1a;
        display: flex;
        align-items: center;
    }
    .icon-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
    }
    .form-select-sm {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
    }
    .form-select-sm:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }

    .stat-box {
        display: flex;
        align-items: center;
        gap: 1rem;
        border-radius: 16px;
        padding: 1.1rem 1.25rem;
        border: 1px solid rgba(0,0,0,0.08);
        background: #fff;
        height: 100%;
    }
    .stat-box .stat-icon {
        flex: 0 0 auto;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }
    .stat-box .stat-label {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 700;
        color: #868e96;
        margin-bottom: .15rem;
    }
    .stat-box .stat-value {
        font-size: 1.45rem;
        font-weight: 800;
        color: #1a1a1a;
        line-height: 1.1;
    }
    .stat-box.stat-members .stat-icon { background: rgba(13,110,253,0.10); color: #0d6efd; }
    .stat-box.stat-tithes .stat-icon { background: rgba(25,135,84,0.10); color: #198754; }
    .stat-box.stat-offerings .stat-icon { background: rgba(13,202,240,0.14); color: #087990; }
    .stat-box.stat-total { background: rgba(179,0,0,0.05); border-color: rgba(179,0,0,0.15); }
    .stat-box.stat-total .stat-icon { background: rgba(179,0,0,0.12); color: #b30000; }
    .stat-box.stat-total .stat-value { color: #b30000; }

    @media (max-width: 767.98px) {
        .dashboard-cards-carousel {
            position: relative;
        }
        .dashboard-cards-carousel::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #ff2a7a 0%, #b30000 52%, #d4af37 100%);
            z-index: 2;
        }
        .dashboard-cards-track {
            display: flex;
            gap: 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
            padding: .25rem .25rem .35rem;
        }
        .dashboard-cards-track::-webkit-scrollbar {
            display: none;
        }
        .dashboard-cards-slide {
            flex: 0 0 100%;
            min-width: 100%;
            scroll-snap-align: center;
            padding: .35rem;
        }
    }

    .congregation-table thead th {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .congregation-table td { vertical-align: middle; }

    .dash-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .7rem .25rem;
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }
    .dash-list-item:last-child { border-bottom: none; }
    .dash-list-item.is-today {
        background: rgba(255,193,7,0.08);
        border-radius: 10px;
        padding: .7rem .6rem;
    }
    .date-pill {
        display: inline-block;
        padding: .2rem .65rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        background: rgba(13,202,240,0.14);
        color: #087990;
    }
    .today-pill {
        display: inline-block;
        padding: .15rem .55rem;
        border-radius: 999px;
        font-size: .66rem;
        font-weight: 700;
        background: #ffc107;
        color: #212529;
        margin-left: .4rem;
    }
    .event-item {
        padding: .7rem .25rem;
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }
    .event-item:last-child { border-bottom: none; }
    .event-date-pill {
        display: inline-block;
        padding: .2rem .65rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        background: rgba(13,110,253,0.10);
        color: #0d6efd;
        margin-right: .5rem;
    }

    #birthdayModal .modal-content { border-radius: 16px; border: none; overflow: hidden; }
</style>

<div class="dashboard-cards-carousel d-md-none mb-2">
    <div class="px-2 pt-2">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small">Dashboard</span>
            <span class="text-muted small"><i class="fas fa-arrows-left-right me-1"></i>Deslize para o lado</span>
        </div>
    </div>
    <div class="dashboard-cards-track">
        <div class="dashboard-cards-slide">
            <div class="stat-box stat-members">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-label">Total Membros</div>
                    <div class="stat-value"><?= $members_count ?></div>
                </div>
            </div>
        </div>
        <div class="dashboard-cards-slide">
            <div class="stat-box stat-tithes">
                <div class="stat-icon"><i class="fas fa-hand-holding-dollar"></i></div>
                <div>
                    <div class="stat-label">Dízimos (<?= $selected_month ?>/<?= $selected_year ?>)</div>
                    <div class="stat-value sensitive-dashboard-value" data-value="<?= htmlspecialchars($tithesSumFormatted) ?>"><?= $canToggleFinancialValues ? 'R$ ••••••' : $tithesSumFormatted ?></div>
                </div>
            </div>
        </div>
        <div class="dashboard-cards-slide">
            <div class="stat-box stat-offerings">
                <div class="stat-icon"><i class="fas fa-donate"></i></div>
                <div>
                    <div class="stat-label">Ofertas (<?= $selected_month ?>/<?= $selected_year ?>)</div>
                    <div class="stat-value sensitive-dashboard-value" data-value="<?= htmlspecialchars($offeringsSumFormatted) ?>"><?= $canToggleFinancialValues ? 'R$ ••••••' : $offeringsSumFormatted ?></div>
                </div>
            </div>
        </div>
        <div class="dashboard-cards-slide">
            <div class="stat-box stat-total">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div>
                    <div class="stat-label">Total Geral</div>
                    <div class="stat-value sensitive-dashboard-value" data-value="<?= htmlspecialchars($totalFinancialFormatted) ?>"><?= $canToggleFinancialValues ? 'R$ ••••••' : $totalFinancialFormatted ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row d-none d-md-flex g-3 mb-1">
    <div class="col-md-3">
        <div class="stat-box stat-members">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div>
                <div class="stat-label">Total Membros</div>
                <div class="stat-value"><?= $members_count ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box stat-tithes">
            <div class="stat-icon"><i class="fas fa-hand-holding-dollar"></i></div>
            <div>
                <div class="stat-label">Dízimos (<?= $selected_month ?>/<?= $selected_year ?>)</div>
                <div class="stat-value sensitive-dashboard-value" data-value="<?= htmlspecialchars($tithesSumFormatted) ?>"><?= $canToggleFinancialValues ? 'R$ ••••••' : $tithesSumFormatted ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box stat-offerings">
            <div class="stat-icon"><i class="fas fa-donate"></i></div>
            <div>
                <div class="stat-label">Ofertas (<?= $selected_month ?>/<?= $selected_year ?>)</div>
                <div class="stat-value sensitive-dashboard-value" data-value="<?= htmlspecialchars($offeringsSumFormatted) ?>"><?= $canToggleFinancialValues ? 'R$ ••••••' : $offeringsSumFormatted ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-box stat-total">
            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
            <div>
                <div class="stat-label">Total Geral</div>
                <div class="stat-value sensitive-dashboard-value" data-value="<?= htmlspecialchars($totalFinancialFormatted) ?>"><?= $canToggleFinancialValues ? 'R$ ••••••' : $totalFinancialFormatted ?></div>
            </div>
        </div>
    </div>
</div>

<?php if ($canToggleFinancialValues): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggleButton = document.getElementById('toggle-dashboard-values');
        var valueNodes = document.querySelectorAll('.sensitive-dashboard-value');
        if (!toggleButton || !valueNodes.length) {
            return;
        }

        var icon = toggleButton.querySelector('i');
        var storageKey = 'dashboard_financial_values_visible_' + <?= (int)($_SESSION['user_id'] ?? 0) ?>;
        var isVisible = localStorage.getItem(storageKey) === '1';

        function renderValues() {
            valueNodes.forEach(function (node) {
                node.textContent = isVisible ? node.dataset.value : 'R$ ••••••';
            });
            var title = isVisible ? 'Ocultar valores' : 'Exibir valores';
            toggleButton.setAttribute('title', title);
            toggleButton.setAttribute('aria-label', title);
            icon.className = isVisible ? 'fas fa-eye-slash' : 'fas fa-eye';
        }

        toggleButton.addEventListener('click', function () {
            isVisible = !isVisible;
            localStorage.setItem(storageKey, isVisible ? '1' : '0');
            renderValues();
        });

        renderValues();
    });
</script>
<?php endif; ?>

<div class="row mt-4">
    <div class="col-12 mb-4">
        <div class="member-form-card">
            <div class="member-form-card-header">
                <div class="member-form-card-title"><i class="fas fa-chart-pie text-secondary me-2"></i> Estatísticas por Congregação (<?= $selected_month ?>/<?= $selected_year ?>)</div>
            </div>
            <div class="table-responsive p-2">
                <table class="table table-hover congregation-table mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th>Congregação</th>
                            <th class="text-center">Membros</th>
                            <th class="text-end">Dízimos</th>
                            <th class="text-end">Ofertas</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($congregation_stats)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">Nenhuma congregação cadastrada.</td></tr>
                        <?php else: ?>
                            <?php foreach ($congregation_stats as $stat):
                                $total = $stat['tithe_sum'] + $stat['offering_sum'];
                                $titheValue = 'R$ ' . number_format($stat['tithe_sum'], 2, ',', '.');
                                $offeringValue = 'R$ ' . number_format($stat['offering_sum'], 2, ',', '.');
                                $totalValue = 'R$ ' . number_format($total, 2, ',', '.');
                            ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($stat['congregation_name']) ?></td>
                                    <td class="text-center"><?= $stat['member_count'] ?></td>
                                    <td class="text-end sensitive-dashboard-value" data-value="<?= htmlspecialchars($titheValue) ?>"><?= $canToggleFinancialValues ? 'R$ ••••••' : $titheValue ?></td>
                                    <td class="text-end sensitive-dashboard-value" data-value="<?= htmlspecialchars($offeringValue) ?>"><?= $canToggleFinancialValues ? 'R$ ••••••' : $offeringValue ?></td>
                                    <td class="text-end fw-bold sensitive-dashboard-value" data-value="<?= htmlspecialchars($totalValue) ?>"><?= $canToggleFinancialValues ? 'R$ ••••••' : $totalValue ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php if (hasPermission('members.view')): ?>
    <div class="col-md-6 mb-4">
        <div class="member-form-card h-100">
            <div class="member-form-card-header">
                <div class="member-form-card-title"><i class="fas fa-birthday-cake text-warning me-2"></i> Aniversariantes do Mês</div>
            </div>
            <div class="p-3">
                <?php if (empty($birthdays)): ?>
                    <p class="text-muted text-center mb-0 py-2">Nenhum aniversariante este mês.</p>
                <?php else: ?>
                    <?php
                    $today_day = date('d');
                    foreach ($birthdays as $b):
                        $b_day = date('d', strtotime($b['birth_date']));
                        $is_today = ($b_day == $today_day);
                    ?>
                        <div class="dash-list-item <?= $is_today ? 'is-today' : '' ?>">
                            <div>
                                <?= htmlspecialchars($b['name']) ?>
                                <?php if ($is_today): ?>
                                    <span class="today-pill">Hoje!</span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="date-pill"><?= date('d/m', strtotime($b['birth_date'])) ?></span>
                                <?php if ($is_today): ?>
                                    <button class="btn btn-sm btn-outline-success icon-btn" style="width:30px;height:30px;" onclick="openBirthdayCard('<?= addslashes(htmlspecialchars($b['name'])) ?>')" title="Gerar Cartão">
                                        <i class="fas fa-gift"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (hasPermission('events.view')): ?>
    <div class="col-md-6 mb-4">
        <div class="member-form-card h-100">
            <div class="member-form-card-header">
                <div class="member-form-card-title"><i class="fas fa-calendar-alt text-primary me-2"></i> Próximos Eventos</div>
            </div>
            <div class="p-3">
                <?php if (empty($next_events)): ?>
                    <p class="text-muted text-center mb-0 py-2">Nenhum evento próximo.</p>
                <?php else: ?>
                    <?php foreach ($next_events as $e): ?>
                        <div class="event-item">
                            <span class="event-date-pill"><?= date('d/m/Y', strtotime($e['event_date'])) ?></span>
                            <?= htmlspecialchars($e['title']) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

<!-- Modal Cartão de Aniversário -->
<?php $siteProfile = getChurchSiteProfileSettings(); ?>
<div class="modal fade" id="birthdayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-primary"><i class="fas fa-birthday-cake me-2"></i> Cartão Virtual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <!-- Arte do Cartão -->
                <div id="birthdayCardArt" class="position-relative mx-auto rounded overflow-hidden mb-3" style="width: 350px; height: 500px; background: url('/assets/img/birthday-bg.jpg') center/cover no-repeat; border: 8px solid #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                    <!-- Overlay Branco Suave (quase transparente para destacar balões) -->
                    <div style="position: absolute; inset: 0; background: rgba(255, 255, 255, 0.1);"></div>

                    <!-- Conteúdo -->
                    <div class="d-flex flex-column justify-content-center align-items-center h-100 p-4" style="position: relative; z-index: 2; font-family: 'Lato', sans-serif;">
                        <div class="mb-4 text-center">
                            <!-- Tenta carregar logo, se falhar mostra ícone -->
                            <img src="<?= htmlspecialchars($siteProfile['logo_url'] ?? '/assets/img/logo.png') ?>" alt="<?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?>" style="max-height: 80px; max-width: 150px; object-fit: contain; filter: drop-shadow(0 2px 2px rgba(0,0,0,0.1));" onerror="this.onerror=null; this.style.display='none'; document.getElementById('fallbackIcon').style.display='block';">
                            <i id="fallbackIcon" class="fas fa-church fa-3x text-warning" style="display:none;"></i>
                        </div>

                        <h5 class="text-uppercase text-secondary small mb-2" style="letter-spacing: 3px; font-weight: 600; color: #8b7d4b;">Feliz Aniversário</h5>

                        <h1 class="text-dark mb-3 px-2" id="cardMemberName" style="font-family: 'Great Vibes', cursive; font-size: 2.8rem; line-height: 1.1; color: #b8860b; text-shadow: 2px 2px 0 rgba(255,255,255,0.8);">Nome do Membro</h1>

                        <p class="text-dark mb-3 text-center px-3" style="font-style: italic; font-weight: 500; font-size: 0.95rem; line-height: 1.5; text-shadow: 0 0 10px rgba(255,255,255,0.8);">
                            "O Senhor te abençoe e te guarde;<br>
                            o Senhor faça resplandecer o seu rosto sobre ti<br>
                            e tenha misericórdia de ti."
                        </p>

                        <div class="border-top border-secondary w-25 mb-3" style="opacity: 0.4;"></div>
                        <span class="fw-bold text-secondary small text-uppercase" style="letter-spacing: 1px; color: #8b7d4b;">Números 6:24-25</span>

                        <div class="mt-auto pt-4">
                            <small class="text-uppercase fw-bold text-muted" style="letter-spacing: 2px; font-size: 0.7rem;">Família <?= htmlspecialchars($siteProfile['alias'] ?? 'IVN') ?></small>
                        </div>
                    </div>
                </div>

                <p class="text-muted small mb-3">Compartilhe este cartão com o aniversariante:</p>

                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary rounded-pill fw-semibold" onclick="downloadCard()">
                        <i class="fas fa-download me-2"></i> Baixar Imagem (JPG)
                    </button>
                    <button class="btn btn-success rounded-pill fw-semibold" onclick="shareWhatsApp()">
                        <i class="fab fa-whatsapp me-2"></i> Enviar Mensagem (Texto)
                    </button>
                </div>
                <div class="text-muted x-small mt-2">
                    * Para enviar a imagem no WhatsApp, baixe-a primeiro e depois anexe na conversa.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- html2canvas Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    let currentMemberName = '';

    function openBirthdayCard(name) {
        currentMemberName = name;
        document.getElementById('cardMemberName').innerText = name;
        new bootstrap.Modal(document.getElementById('birthdayModal')).show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        try {
            var params = new URLSearchParams(window.location.search);
            var name = params.get('birthday_card');
            if (!name) return;
            openBirthdayCard(name);
            params.delete('birthday_card');
            var next = window.location.pathname + (params.toString() ? ('?' + params.toString()) : '') + window.location.hash;
            window.history.replaceState({}, document.title, next);
        } catch (e) {
        }
    });

    function shareWhatsApp() {
        const message = `🎉 *Parabéns, ${currentMemberName}!* 🎉\n\nNeste dia especial, louvamos a Deus pela sua vida! Que o Senhor continue te abençoando grandemente.\n\n"O Senhor te abençoe e te guarde..." (Nm 6:24)\n\nCom carinho,\n*Família <?= addslashes($siteProfile['alias'] ?? 'IVN') ?>*`;
        const url = `https://wa.me/?text=${encodeURIComponent(message)}`;
        window.open(url, '_blank');
    }

    function downloadCard() {
        const card = document.getElementById('birthdayCardArt');

        html2canvas(card, {
            scale: 2,
            useCORS: true,
            backgroundColor: null
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = `Aniversario_${currentMemberName.replace(/\s+/g, '_')}.jpg`;
            link.href = canvas.toDataURL('image/jpeg', 0.9);
            link.click();
        }).catch(err => {
            console.error(err);
            Swal.fire({
                title: 'Erro ao gerar imagem',
                text: 'Não foi possível gerar a imagem do cartão. Tente tirar um print da tela.',
                icon: 'error',
                confirmButtonColor: '#b30000',
                confirmButtonText: 'Entendi'
            });
        });
    }
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
