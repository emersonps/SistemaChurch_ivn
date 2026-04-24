<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($group['name']) ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/groups" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <a href="/admin/groups/report/<?= $group['id'] ?>" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-file-alt"></i> Relatório
        </a>
        <?php if (hasPermission('groups.manage')): ?>
        <a href="/admin/groups/edit/<?= $group['id'] ?>" class="btn btn-sm btn-outline-primary me-2">
            <i class="fas fa-edit"></i> Editar
        </a>
        <form action="/admin/groups/delete/<?= $group['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este grupo?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-trash"></i> Excluir
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- Info Lateral -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light fw-bold">Informações</div>
            <div class="card-body">
                <p><strong><i class="fas fa-church me-2"></i> Congregação:</strong><br>
                <?= htmlspecialchars($group['congregation_name'] ?? 'Não informada') ?></p>
                
                <p><strong><i class="fas fa-user-tie me-2"></i> Líder:</strong><br>
                <?= htmlspecialchars($group['leader_name'] ?? 'Não definido') ?></p>
                
                <p><strong><i class="fas fa-home me-2"></i> Anfitrião:</strong><br>
                <?= htmlspecialchars($group['host_name_display'] ?? 'Não definido') ?></p>
                
                <p><strong><i class="fas fa-clock me-2"></i> Encontros:</strong><br>
                <?= htmlspecialchars((string)$group['meeting_day']) ?>
                <?php if (!empty($group['meeting_time'])): ?>
                     às <?= substr($group['meeting_time'], 0, 5) ?>
                <?php endif; ?>
                </p>
                
                <p><strong><i class="fas fa-map-marker-alt me-2"></i> Endereço:</strong><br>
                <?= htmlspecialchars((string)$group['address']) ?></p>
                
                <?php if ($group['description']): ?>
                <hr>
                <p class="small text-muted"><?= nl2br(htmlspecialchars($group['description'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Lista de Membros -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Participantes (<?= count($members) ?>)</h5>
                
                <?php if (hasPermission('groups.manage')): ?>
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                    <i class="fas fa-user-plus"></i> Adicionar
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Função</th>
                                <th>Status</th>
                                <th>Contato</th>
                                <?php if (hasPermission('groups.manage')): ?>
                                <th class="text-end">Ação</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($members)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">Nenhum participante cadastrado.</td></tr>
                            <?php else: ?>
                                <?php foreach ($members as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['name']) ?></td>
                                    <td>
                                        <?php 
                                            $badges = [
                                                'leader' => 'bg-primary', // Embora lider seja na tabela groups, podemos simular se quisermos, mas aqui é role da tabela group_members
                                                'host' => 'bg-info',
                                                'assistant' => 'bg-warning text-dark',
                                                'member' => 'bg-secondary',
                                                'visitor' => 'bg-light text-dark border'
                                            ];
                                            $roleLabel = [
                                                'leader' => 'Líder',
                                                'host' => 'Anfitrião',
                                                'assistant' => 'Auxiliar',
                                                'member' => 'Membro',
                                                'visitor' => 'Convidado'
                                            ];
                                            $bg = $badges[$m['role']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?= $bg ?>"><?= $roleLabel[$m['role']] ?? ucfirst($m['role']) ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($m['is_new_convert'])): ?>
                                            <span class="badge bg-success mb-1" title="Novo Convertido"><i class="fas fa-seedling"></i> NC</span>
                                        <?php endif; ?>
                                        <?php if (!empty($m['accepted_jesus_at'])): ?>
                                            <span class="badge bg-primary mb-1" title="Aceitou Jesus em <?= date('d/m/Y', strtotime($m['accepted_jesus_at'])) ?>"><i class="fas fa-cross"></i> AJ</span>
                                        <?php endif; ?>
                                        <?php if (!empty($m['reconciled_at'])): ?>
                                            <span class="badge bg-info text-dark mb-1" title="Reconciliado em <?= date('d/m/Y', strtotime($m['reconciled_at'])) ?>"><i class="fas fa-undo"></i> RC</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($m['phone']): ?>
                                            <a href="https://wa.me/55<?= preg_replace('/\D/', '', $m['phone']) ?>" target="_blank" class="text-success text-decoration-none">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <?php if (hasPermission('groups.manage')): ?>
                                    <td class="text-end">
                                        <?php if ($m['role'] === 'visitor'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success border-0" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#convertVisitorModal" 
                                                data-member-id="<?= $m['member_id'] ?>" 
                                                data-member-name="<?= htmlspecialchars($m['name']) ?>"
                                                title="Converter Convidado">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-outline-warning border-0" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#transferMemberModal" 
                                                data-member-id="<?= $m['member_id'] ?>" 
                                                data-member-name="<?= htmlspecialchars($m['name']) ?>"
                                                title="Transferir Membro">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                        <form action="/admin/groups/members/remove" method="POST" class="d-inline" onsubmit="return confirm('Remover este membro do grupo?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
                                            <input type="hidden" name="member_id" value="<?= $m['member_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <?php endif; ?>
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

<!-- Modal Adicionar Membro -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/admin/groups/members/add" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Participante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome do Participante</label>
                        <input type="text" id="memberSearchInput" name="member_name" list="membersList" class="form-control" placeholder="Digite o nome para buscar ou criar novo..." autocomplete="off" required>
                        <datalist id="membersList">
                            <?php foreach ($available_members as $am): ?>
                                <option data-id="<?= $am['id'] ?>" value="<?= htmlspecialchars($am['name'] . ($am['congregation_name'] ? ' (' . $am['congregation_name'] . ')' : '')) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <input type="hidden" name="member_id" id="memberIdInput">
                        <div class="form-text text-muted">Se o nome não existir, será cadastrado como novo Convidado.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Função no Grupo</label>
                        <select name="role" id="roleSelect" class="form-select">
                            <option value="member">Membro</option>
                            <option value="assistant">Auxiliar</option>
                            <option value="visitor">Convidado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Adicionar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Transferir Membro -->
<div class="modal fade" id="transferMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/admin/groups/members/transfer" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="from_group_id" value="<?= $group['id'] ?>">
            <input type="hidden" name="member_id" id="transferMemberId">
            
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transferir Membro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Transferir <strong id="transferMemberName"></strong> para:</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Novo Grupo</label>
                        <select name="to_group_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($all_groups as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Função no Novo Grupo</label>
                        <select name="role" class="form-select">
                            <option value="member">Membro</option>
                            <option value="assistant">Auxiliar</option>
                            <option value="visitor">Convidado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Transferir</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Gerenciamento de Novo Participante / Busca
    document.addEventListener('DOMContentLoaded', function() {
        const memberInput = document.getElementById('memberSearchInput');
        const memberIdInput = document.getElementById('memberIdInput');
        const roleSelect = document.getElementById('roleSelect');
        const membersList = document.getElementById('membersList');
        
        if (memberInput && membersList) {
            memberInput.addEventListener('input', function() {
                const val = this.value;
                const options = membersList.options;
                let foundId = '';
                
                // Tenta encontrar ID correspondente ao nome digitado
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value === val) {
                        foundId = options[i].getAttribute('data-id');
                        break;
                    }
                }
                
                memberIdInput.value = foundId;
                
                // Se não encontrou ID (novo nome), força Visitante
                if (!foundId && val.length > 0) {
                    roleSelect.value = 'visitor';
                    // Desabilita outras opções
                    Array.from(roleSelect.options).forEach(opt => {
                        if (opt.value !== 'visitor') {
                            opt.disabled = true;
                        }
                    });
                } else {
                    // Se encontrou ou vazio, habilita tudo
                    Array.from(roleSelect.options).forEach(opt => {
                        opt.disabled = false;
                    });
                }
            });
            
            // Ao abrir modal, limpar campos
            var addMemberModal = document.getElementById('addMemberModal');
            addMemberModal.addEventListener('show.bs.modal', function () {
                memberInput.value = '';
                memberIdInput.value = '';
                roleSelect.value = 'member'; // Default reset
                Array.from(roleSelect.options).forEach(opt => opt.disabled = false);
            });
        }
    });
</script>

<!-- Modal Converter Visitante -->
<div class="modal fade" id="convertVisitorModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/admin/groups/members/convert" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
            <input type="hidden" name="member_id" id="convertMemberId">
            
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Converter Convidado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Alterar status de <strong id="convertMemberName"></strong> para:</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipo de Conversão</label>
                        <select name="conversion_type" class="form-select" required>
                            <option value="accepted_jesus">Aceitou Jesus</option>
                            <option value="reconciled">Reconciliou-se</option>
                            <option value="became_member">Tornou-se Membro</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Data</label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Confirmar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    var convertModal = document.getElementById('convertVisitorModal');
    if (convertModal) {
        convertModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var memberId = button.getAttribute('data-member-id');
            var memberName = button.getAttribute('data-member-name');
            
            var modalMemberIdInput = convertModal.querySelector('#convertMemberId');
            var modalMemberNameSpan = convertModal.querySelector('#convertMemberName');
            
            modalMemberIdInput.value = memberId;
            modalMemberNameSpan.textContent = memberName;
        });
    }
</script>

<script>
    var transferModal = document.getElementById('transferMemberModal');
    transferModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var memberId = button.getAttribute('data-member-id');
        var memberName = button.getAttribute('data-member-name');
        
        var modalMemberIdInput = transferModal.querySelector('#transferMemberId');
        var modalMemberNameSpan = transferModal.querySelector('#transferMemberName');
        
        modalMemberIdInput.value = memberId;
        modalMemberNameSpan.textContent = memberName;
    });
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
