<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="member-form-topbar d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/attendance" class="text-decoration-none">Controle de Presença</a></li>
                <li class="breadcrumb-item active">Lista</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Lista de Presença</h1>
        <?php
        $now = new DateTimeImmutable('now');
        $next = eventNextOccurrence($event, $now);
        $dateBadges = eventGetDateBadges($event);
        $primary = $next ? $next->format('d/m/Y H:i') : (!empty($dateBadges) ? ($dateBadges[0]['date'] . ' ' . $dateBadges[0]['time']) : '-');
        ?>
        <p class="text-muted mb-0 small">
            Evento: <strong><?= htmlspecialchars($event['title']) ?></strong> (<?= htmlspecialchars($primary) ?>)
            <?php if (count($dateBadges) > 1): ?>
                <span class="ms-1 badge bg-light text-dark border">+<?= count($dateBadges) - 1 ?> datas</span>
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/events/attendance/print/<?= $event['id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill fw-semibold px-3">
            <i class="fas fa-print me-1"></i> Imprimir Lista
        </a>
        <a href="/admin/events" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
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
        overflow: hidden;
    }
    .member-form-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .85rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        background: #fafafa;
    }
    .member-form-card-header-title {
        display: flex;
        align-items: center;
        gap: .6rem;
        font-weight: 800;
        font-size: 1.05rem;
        color: #1a1a1a;
    }
    .member-form-card-header-title i { color: #b30000; }
    .count-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .3rem .7rem;
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 700;
        background: rgba(25,135,84,0.12);
        color: #198754;
    }
    .attendance-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .attendance-table td {
        vertical-align: middle;
        padding-top: .55rem;
        padding-bottom: .55rem;
    }
</style>

<div class="row">
    <!-- Câmera Scanner -->
    <div class="col-md-5 mb-4">
        <div class="member-form-card h-100">
            <div class="member-form-card-header">
                <div class="member-form-card-header-title"><i class="fas fa-qrcode"></i> Leitor de Carteirinha</div>
            </div>
            <div class="p-3 text-center d-flex flex-column align-items-center">
                <!-- Seletor de Câmera (Oculto por padrão, ativado via JS) -->
                <div id="camera-select-container" class="mb-2 w-100 d-none">
                    <select id="camera-select" class="form-select form-select-sm mb-2"></select>
                    <button id="btn-swap-camera" class="btn btn-sm btn-outline-secondary rounded-pill w-100">
                        <i class="fas fa-sync-alt me-1"></i> Trocar Câmera
                    </button>
                </div>

                <div id="reader" style="width: 100%; max-width: 400px;" class="mb-3 border rounded bg-light"></div>

                <div id="scan-result-alert" class="alert d-none w-100" role="alert"></div>

                <p class="text-muted small mt-auto">Posicione o QR Code da carteirinha do membro em frente à câmera para registrar a presença automaticamente.</p>
            </div>
        </div>
    </div>

    <!-- Lista de Presenças -->
    <div class="col-md-7 mb-4">
        <div class="member-form-card h-100">
            <div class="member-form-card-header">
                <div class="member-form-card-header-title"><i class="fas fa-list-check"></i> Membros Presentes</div>
                <span class="count-pill" id="attendee-count"><?= count($attendees) ?></span>
            </div>
            <div class="p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover attendance-table mb-0" id="attendance-table">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Foto</th>
                                <th>Nome</th>
                                <th>Congregação</th>
                                <th>Horário</th>
                            </tr>
                        </thead>
                        <tbody id="attendance-tbody">
                            <?php if (empty($attendees)): ?>
                                <tr id="empty-row">
                                    <td colspan="4" class="text-center py-4 text-muted">Nenhuma presença registrada ainda.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($attendees as $att): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($att['photo'])): ?>
                                                <img src="/uploads/members/<?= $att['photo'] ?>" class="rounded-circle object-fit-cover" width="30" height="30">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 30px; height: 30px; font-size: 12px;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold"><?= mb_convert_case($att['name'], MB_CASE_TITLE, "UTF-8") ?></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($att['congregation_name'] ?? '-') ?></small></td>
                                        <td><small><?= date('H:i:s', strtotime($att['scanned_at'])) ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSRF Token para requisição AJAX -->
<input type="hidden" id="csrf_token" value="<?= csrf_token() ?>">

<!-- Biblioteca HTML5-QRCode -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let isProcessing = false;
    // Variável global para instância do scanner
    let html5QrCode;
    let currentCameraId = null;

    // Inicialização
    Html5Qrcode.getCameras().then(devices => {
        if (devices && devices.length) {
            const cameraSelect = document.getElementById('camera-select');
            const swapBtn = document.getElementById('btn-swap-camera');
            const container = document.getElementById('camera-select-container');

            // Popula select
            devices.forEach(device => {
                const option = document.createElement('option');
                option.value = device.id;
                option.text = device.label || `Câmera ${cameraSelect.options.length + 1}`;
                cameraSelect.appendChild(option);
            });

            // Se tiver mais de uma câmera, mostra controles
            if (devices.length > 1) {
                container.classList.remove('d-none');
            }

            // Tenta encontrar câmera traseira
            const backCamera = devices.find(d => d.label.toLowerCase().includes('back') || d.label.toLowerCase().includes('traseira') || d.label.toLowerCase().includes('environment'));
            currentCameraId = backCamera ? backCamera.id : devices[0].id;
            cameraSelect.value = currentCameraId;

            startScanner(currentCameraId);

            // Evento de troca via botão
            swapBtn.addEventListener('click', () => {
                const currentIndex = devices.findIndex(d => d.id === currentCameraId);
                const nextIndex = (currentIndex + 1) % devices.length;
                currentCameraId = devices[nextIndex].id;
                cameraSelect.value = currentCameraId;
                restartScanner(currentCameraId);
            });

            // Evento de troca via select
            cameraSelect.addEventListener('change', (e) => {
                currentCameraId = e.target.value;
                restartScanner(currentCameraId);
            });
        } else {
            Swal.fire({ icon: 'warning', title: 'Nenhuma câmera encontrada', confirmButtonColor: '#3085d6' });
        }
    }).catch(err => {
        console.error("Erro ao listar câmeras", err);
        Swal.fire({ icon: 'error', title: 'Erro ao acessar câmeras', text: 'Verifique as permissões do navegador.', confirmButtonColor: '#d33' });
    });

    function startScanner(cameraId) {
        html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start(
            cameraId,
            {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            },
            onScanSuccess,
            onScanFailure
        ).catch(err => {
            console.error("Erro ao iniciar scanner", err);
        });
    }

    function restartScanner(cameraId) {
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                startScanner(cameraId);
            }).catch(err => {
                console.error("Erro ao parar scanner", err);
            });
        }
    }

    function onScanFailure(error) {
        // Ignorar falhas contínuas
    }

    function onScanSuccess(decodedText, decodedResult) {
        // Prevent multiple simultaneous requests
        if (isProcessing) return;
        isProcessing = true;

        // Pausar o scanner brevemente para feedback visual (apenas UI, não stop real)
        html5QrCode.pause();

        const alertBox = document.getElementById('scan-result-alert');
        alertBox.className = 'alert alert-info w-100';
        alertBox.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';

        // Fazer requisição AJAX
        const formData = new FormData();
        formData.append('qr_data', decodedText);
        formData.append('csrf_token', document.getElementById('csrf_token').value);

        fetch('/admin/events/attendance/register/<?= $event['id'] ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Sucesso
                alertBox.className = 'alert alert-success w-100';
                alertBox.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;

                // Adicionar linha na tabela
                addAttendeeRow(data.member);

                // Tocar som de sucesso (opcional)
                playBeep(true);
            } else {
                // Erro (ex: Já registrado)
                alertBox.className = 'alert alert-warning w-100';
                alertBox.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + data.message;
                playBeep(false);
            }

            // Retomar scanner após 2 segundos
            setTimeout(() => {
                alertBox.className = 'alert d-none w-100';
                html5QrCode.resume();
                isProcessing = false;
            }, 2500);
        })
        .catch(error => {
            console.error('Error:', error);
            alertBox.className = 'alert alert-danger w-100';
            alertBox.innerHTML = '<i class="fas fa-times-circle"></i> Erro de conexão ao registrar.';

            setTimeout(() => {
                alertBox.className = 'alert d-none w-100';
                html5QrCode.resume();
                isProcessing = false;
            }, 3000);
        });
    }

    // Função para adicionar linha na tabela dinamicamente
    function addAttendeeRow(member) {
        const tbody = document.getElementById('attendance-tbody');
        const emptyRow = document.getElementById('empty-row');
        if (emptyRow) {
            emptyRow.remove();
        }

        let photoHtml = '';
        if (member.photo) {
            photoHtml = `<img src="/uploads/members/${member.photo}" class="rounded-circle object-fit-cover" width="30" height="30">`;
        } else {
            photoHtml = `<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 30px; height: 30px; font-size: 12px;"><i class="fas fa-user"></i></div>`;
        }

        const newRow = document.createElement('tr');
        newRow.className = 'table-success'; // Highlight nova linha
        newRow.innerHTML = `
            <td>${photoHtml}</td>
            <td class="fw-bold">${member.name}</td>
            <td><small class="text-muted">Adicionado agora</small></td>
            <td><small>${member.scanned_at}</small></td>
        `;

        // Inserir no topo
        tbody.insertBefore(newRow, tbody.firstChild);

        // Atualizar contador
        const countBadge = document.getElementById('attendee-count');
        countBadge.innerText = parseInt(countBadge.innerText) + 1;

        // Remover highlight após 3 segundos
        setTimeout(() => {
            newRow.classList.remove('table-success');
        }, 3000);
    }

    // Feedback sonoro
    function playBeep(success) {
        try {
            const context = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = context.createOscillator();
            const gainNode = context.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(context.destination);

            if (success) {
                oscillator.type = 'sine';
                oscillator.frequency.value = 800; // Tom mais alto para sucesso
                gainNode.gain.setValueAtTime(0.1, context.currentTime);
                oscillator.start();
                oscillator.stop(context.currentTime + 0.15);
            } else {
                oscillator.type = 'sawtooth';
                oscillator.frequency.value = 300; // Tom mais baixo/grave para alerta
                gainNode.gain.setValueAtTime(0.1, context.currentTime);
                oscillator.start();
                oscillator.stop(context.currentTime + 0.3);
            }
        } catch (e) {
            console.log('Audio not supported');
        }
    }
});
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
