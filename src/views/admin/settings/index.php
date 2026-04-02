<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Configurações do Sistema</h1>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Configurações salvas com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fab fa-whatsapp text-success me-2"></i> Integração WhatsApp (Evolution API)</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">Configure aqui a sua API de WhatsApp para que o sistema possa enviar notificações automáticas (como aniversariantes do dia) para os administradores.</p>
                
                <form action="/admin/settings/store" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">URL da API (Evolution API)</label>
                        <input type="text" class="form-control" name="whatsapp_api_url" value="<?= htmlspecialchars($settings['whatsapp_api_url'] ?? '') ?>" placeholder="Ex: https://api.seudominio.com">
                        <div class="form-text">O endereço onde sua Evolution API está hospedada. Não inclua a barra final (/).</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nome da Instância</label>
                        <input type="text" class="form-control" name="whatsapp_api_instance" value="<?= htmlspecialchars($settings['whatsapp_api_instance'] ?? '') ?>" placeholder="Ex: sistema_igreja">
                        <div class="form-text">O nome da instância criada na API.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Global API Key / Token</label>
                        <input type="password" class="form-control" name="whatsapp_api_token" value="<?= htmlspecialchars($settings['whatsapp_api_token'] ?? '') ?>">
                        <div class="form-text">O token de segurança para autenticar as requisições na sua API.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                </form>
                
                <hr class="my-4">
                
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Status da Conexão</h6>
                    <div>
                         <button id="btnTestBirthdays" class="btn btn-outline-info me-2"><i class="fas fa-birthday-cake me-2"></i> Testar Envio de Aniversários</button>
                         <button id="btnConnect" class="btn btn-success"><i class="fas fa-qrcode me-2"></i> Gerar QR Code</button>
                    </div>
                </div>
                
                <div id="qrCodeContainer" class="mt-3 text-center" style="display:none;">
                    <div class="alert alert-info">Abra o WhatsApp no seu celular > Menu > Aparelhos conectados > Conectar um aparelho e leia o código abaixo:</div>
                    <img id="qrCodeImage" src="" alt="QR Code WhatsApp" class="img-fluid border p-2" style="max-width: 300px;">
                </div>
                
                <div id="connectionStatus" class="mt-3" style="display:none;"></div>

                <script>
                document.getElementById('btnTestBirthdays').addEventListener('click', function() {
                    const btn = this;
                    const originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Enviando...';
                    
                    document.getElementById('connectionStatus').style.display = 'none';

                    fetch('/admin/settings/test-birthdays')
                        .then(response => response.text()) // Change to text() to debug
                        .then(text => {
                            console.log('Raw response:', text); // Log the raw response
                            try {
                                const data = JSON.parse(text);
                                let alertClass = 'alert-info';
                                if (data.status === 'success') alertClass = 'alert-success';
                                if (data.status === 'error') alertClass = 'alert-danger';
                                if (data.status === 'warning') alertClass = 'alert-warning';
                                
                                document.getElementById('connectionStatus').innerHTML = '<div class="alert ' + alertClass + '">' + data.message + '</div>';
                                document.getElementById('connectionStatus').style.display = 'block';
                            } catch (e) {
                                console.error('JSON Parse Error:', e);
                                alert('Erro ao processar resposta do servidor. Verifique o console.');
                                document.getElementById('connectionStatus').innerHTML = '<div class="alert alert-danger">Erro técnico: <pre>' + text + '</pre></div>';
                                document.getElementById('connectionStatus').style.display = 'block';
                            }
                        })
                        .catch(error => {
                            alert('Erro na requisição.');
                            console.error(error);
                        })
                        .finally(() => {
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        });
                });

                document.getElementById('btnConnect').addEventListener('click', function() {
                    const btn = this;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Gerando...';
                    
                    document.getElementById('qrCodeContainer').style.display = 'none';
                    document.getElementById('connectionStatus').style.display = 'none';

                    fetch('/admin/settings/connect')
                        .then(response => response.json())
                        .then(data => {
                            if (data.qrcode) {
                                document.getElementById('qrCodeImage').src = data.qrcode;
                                document.getElementById('qrCodeContainer').style.display = 'block';
                            } else if (data.status === 'connected') {
                                document.getElementById('connectionStatus').innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i> ' + data.message + '</div>';
                                document.getElementById('connectionStatus').style.display = 'block';
                            } else if (data.error) {
                                alert('Erro: ' + data.error);
                            } else {
                                alert('Resposta inesperada da API. Verifique se a URL está correta.');
                            }
                        })
                        .catch(error => {
                            alert('Erro ao conectar com o servidor.');
                            console.error(error);
                        })
                        .finally(() => {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-qrcode me-2"></i> Gerar QR Code';
                        });
                });
                </script>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-light shadow-sm">
            <div class="card-body">
                <h6>Como configurar as notificações automáticas?</h6>
                <p class="small text-muted mb-2">1. Instale a <strong>Evolution API</strong> em um servidor (pode ser gratuito como Render, Railway ou VPS própria).</p>
                <p class="small text-muted mb-2">2. Crie uma instância e conecte seu WhatsApp lendo o QR Code.</p>
                <p class="small text-muted mb-2">3. Preencha os dados de conexão ao lado.</p>
                <p class="small text-muted mb-0">4. Configure o <strong>Cron Job</strong> na sua hospedagem cPanel apontando para o script: <br><code>php /caminho/do/sistema/cron_birthdays.php</code></p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
