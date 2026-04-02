<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Lista de Presença</h1>
        <p class="text-muted mb-0">Evento: <strong><?= htmlspecialchars($event['title']) ?></strong> (<?= date('d/m/Y', strtotime($event['event_date'])) ?>)</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/events/attendance/print/<?= $event['id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark me-2">
            <i class="fas fa-print"></i> Imprimir Lista
        </a>
        <a href="/admin/events" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <!-- Câmera Scanner -->
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-qrcode"></i> Leitor de Carteirinha</h5>
            </div>
            <div class="card-body text-center d-flex flex-column align-items-center">
                <!-- Seletor de Câmera (Oculto por padrão, ativado via JS) -->
                <div id="camera-select-container" class="mb-2 w-100 d-none">
                    <select id="camera-select" class="form-select form-select-sm mb-2"></select>
                    <button id="btn-swap-camera" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fas fa-sync-alt"></i> Trocar Câmera
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
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list-check"></i> Membros Presentes</h5>
                <span class="badge bg-success rounded-pill" id="attendee-count"><?= count($attendees) ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover table-striped mb-0" id="attendance-table">
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
            alert('Nenhuma câmera encontrada.');
        }
    }).catch(err => {
        console.error("Erro ao listar câmeras", err);
        alert('Erro ao acessar câmeras. Verifique as permissões.');
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