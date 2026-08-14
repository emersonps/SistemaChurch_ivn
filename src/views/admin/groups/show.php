<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb" class="mb-1">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="/admin/groups" class="text-decoration-none">Grupos e Células</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($group['name']) ?></li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><?= htmlspecialchars($group['name']) ?></h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="/admin/groups" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
        <a href="/admin/groups/report/<?= $group['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-3">
            <i class="fas fa-file-alt me-1"></i> Relatório
        </a>
        <?php if (hasPermission('groups.manage')): ?>
        <a href="/admin/groups/edit/<?= $group['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold px-3">
            <i class="fas fa-edit me-1"></i> Editar
        </a>
        <form action="/admin/groups/delete/<?= $group['id'] ?>" method="POST" class="d-inline" id="deleteGroupForm">
            <?= csrf_field() ?>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill fw-semibold px-3" id="btnDeleteGroup">
                <i class="fas fa-trash me-1"></i> Excluir
            </button>
        </form>
        <?php endif; ?>
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
        gap: .85rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        background: #fafafa;
    }
    .member-form-card-header-title {
        font-weight: 800;
        font-size: 1.05rem;
        color: #1a1a1a;
    }
    .member-form-card-body { padding: 1.25rem; }

    .info-field .info-label {
        font-size: .76rem;
        color: #868e96;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .info-field .info-value {
        font-weight: 600;
        color: #212529;
    }
    .info-field .info-value.text-muted-value { color: #adb5bd; font-weight: 500; }

    .participants-table thead th {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #868e96;
        font-weight: 700;
        border-bottom-width: 1px;
    }
    .participants-table td {
        vertical-align: middle;
        padding-top: .6rem;
        padding-bottom: .6rem;
    }
    .role-pill {
        display: inline-block;
        padding: .25rem .6rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
    }
    .role-pill.role-leader { background: rgba(179,0,0,0.10); color: #b30000; }
    .role-pill.role-host { background: rgba(13,110,253,0.10); color: #0d6efd; }
    .role-pill.role-assistant { background: rgba(212,175,55,0.18); color: #a6790a; }
    .role-pill.role-member { background: rgba(0,0,0,0.06); color: #495057; }
    .role-pill.role-visitor { background: #eef0f2; color: #868e96; }

    .status-mini-pill {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .15rem .45rem;
        border-radius: 999px;
        font-size: .66rem;
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .status-mini-pill.sp-success { background: rgba(25,135,84,0.12); color: #198754; }
    .status-mini-pill.sp-primary { background: rgba(179,0,0,0.10); color: #b30000; }
    .status-mini-pill.sp-info { background: rgba(13,110,253,0.10); color: #0d6efd; }

    .icon-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
    }
    .modal-content { border-radius: 16px; border: none; }
    .modal-header { border-bottom: 1px solid rgba(0,0,0,0.07); }
    .modal-footer { border-top: 1px solid rgba(0,0,0,0.07); }
    .modal-body .form-label {
        font-weight: 600;
        font-size: .88rem;
        color: #343a40;
    }
    .modal-body .form-control,
    .modal-body .form-select {
        border-radius: 10px;
        border-color: rgba(0,0,0,0.14);
        padding: .55rem .8rem;
    }
    .modal-body .form-control:focus,
    .modal-body .form-select:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 .2rem rgba(179,0,0,0.12);
    }
</style>

<div class="row g-3">
    <!-- Info Lateral -->
    <div class="col-md-4">
        <div class="member-form-card h-100">
            <div class="member-form-card-header">
                <div class="member-form-card-header-title">Informações</div>
            </div>
            <div class="member-form-card-body">
                <div class="info-field mb-3">
                    <div class="info-label"><i class="fas fa-church me-1"></i> Congregação</div>
                    <div class="info-value <?= empty($group['congregation_name']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($group['congregation_name'] ?? 'Não informada') ?></div>
                </div>
                <div class="info-field mb-3">
                    <div class="info-label"><i class="fas fa-user-tie me-1"></i> Líder</div>
                    <div class="info-value <?= empty($group['leader_name']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($group['leader_name'] ?? 'Não definido') ?></div>
                </div>
                <div class="info-field mb-3">
                    <div class="info-label"><i class="fas fa-home me-1"></i> Anfitrião</div>
                    <div class="info-value <?= empty($group['host_name_display']) ? 'text-muted-value' : '' ?>"><?= htmlspecialchars($group['host_name_display'] ?? 'Não definido') ?></div>
                </div>
                <div class="info-field mb-3">
                    <div class="info-label"><i class="far fa-clock me-1"></i> Encontros</div>
                    <div class="info-value">
                        <?= htmlspecialchars((string)$group['meeting_day']) ?>
                        <?php if (!empty($group['meeting_time'])): ?>
                             às <?= substr($group['meeting_time'], 0, 5) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-field">
                    <div class="info-label"><i class="fas fa-map-marker-alt me-1"></i> Endereço</div>
                    <div class="info-value"><?= htmlspecialchars((string)$group['address']) ?></div>
                </div>

                <?php if ($group['description']): ?>
                <hr>
                <p class="small text-muted mb-0"><?= nl2br(htmlspecialchars($group['description'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Lista de Membros -->
    <div class="col-md-8">
        <div class="member-form-card h-100">
            <div class="member-form-card-header">
                <div class="member-form-card-header-title">Participantes (<?= count($members) ?>)</div>

                <?php if (hasPermission('groups.manage')): ?>
                <button type="button" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                    <i class="fas fa-user-plus me-1"></i> Adicionar
                </button>
                <?php endif; ?>
            </div>
            <div class="p-2">
                <div class="table-responsive">
                    <table class="table table-hover participants-table align-middle mb-0">
                        <thead>
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
                                <tr><td colspan="5" class="text-center py-4 text-muted">Nenhum participante cadastrado.</td></tr>
                            <?php else: ?>
                                <?php foreach ($members as $m): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($m['name']) ?></td>
                                    <td>
                                        <?php
                                            $roleLabel = [
                                                'leader' => 'Líder',
                                                'host' => 'Anfitrião',
                                                'assistant' => 'Auxiliar',
                                                'member' => 'Membro',
                                                'visitor' => 'Convidado'
                                            ];
                                            $roleClass = 'role-' . ($m['role'] ?? 'member');
                                        ?>
                                        <span class="role-pill <?= $roleClass ?>"><?= $roleLabel[$m['role']] ?? ucfirst($m['role']) ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($m['is_new_convert'])): ?>
                                            <span class="status-mini-pill sp-success" title="Novo Convertido"><i class="fas fa-seedling"></i> NC</span>
                                        <?php endif; ?>
                                        <?php if (!empty($m['accepted_jesus_at'])): ?>
                                            <span class="status-mini-pill sp-primary" title="Aceitou Jesus em <?= date('d/m/Y', strtotime($m['accepted_jesus_at'])) ?>"><i class="fas fa-cross"></i> AJ</span>
                                        <?php endif; ?>
                                        <?php if (!empty($m['reconciled_at'])): ?>
                                            <span class="status-mini-pill sp-info" title="Reconciliado em <?= date('d/m/Y', strtotime($m['reconciled_at'])) ?>"><i class="fas fa-undo"></i> RC</span>
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
                                        <button type="button" class="btn btn-sm btn-outline-success icon-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#convertVisitorModal"
                                                data-member-id="<?= $m['member_id'] ?>"
                                                data-member-name="<?= htmlspecialchars($m['name']) ?>"
                                                title="Converter Convidado">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-outline-warning icon-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#transferMemberModal"
                                                data-member-id="<?= $m['member_id'] ?>"
                                                data-member-name="<?= htmlspecialchars($m['name']) ?>"
                                                title="Transferir Membro">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                        <form action="/admin/groups/members/remove" method="POST" class="d-inline btn-remove-member-form">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
                                            <input type="hidden" name="member_id" value="<?= $m['member_id'] ?>">
                                            <button type="button" class="btn btn-sm btn-outline-danger icon-btn btn-remove-member" data-member-name="<?= htmlspecialchars($m['name']) ?>" title="Remover do grupo">
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
                    <h5 class="modal-title fw-bold">Adicionar Participante</h5>
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
                        <div class="form-text">Se o nome não existir, será cadastrado como novo Convidado.</div>
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
                    <button type="button" class="btn btn-outline-secondary rounded-pill fw-semibold px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-semibold px-3">Adicionar</button>
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
                    <h5 class="modal-title fw-bold">Transferir Membro</h5>
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
                    <button type="button" class="btn btn-outline-secondary rounded-pill fw-semibold px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill fw-semibold px-3">Transferir</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Converter Visitante -->
<div class="modal fade" id="convertVisitorModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/admin/groups/members/convert" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
            <input type="hidden" name="member_id" id="convertMemberId">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Converter Convidado</h5>
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
                    <button type="button" class="btn btn-outline-secondary rounded-pill fw-semibold px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill fw-semibold px-3">Confirmar</button>
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

<script>
    // Confirmação de exclusão do grupo via SweetAlert (substitui confirm() nativo)
    var btnDeleteGroup = document.getElementById('btnDeleteGroup');
    if (btnDeleteGroup) {
        btnDeleteGroup.addEventListener('click', function () {
            Swal.fire({
                title: 'Excluir grupo?',
                text: 'Tem certeza que deseja excluir este grupo? Todos os participantes serão desvinculados.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteGroupForm').submit();
                }
            });
        });
    }

    // Confirmação de remoção de participante via SweetAlert (substitui confirm() nativo)
    document.querySelectorAll('.btn-remove-member').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const name = btn.getAttribute('data-member-name');
            const form = btn.closest('.btn-remove-member-form');
            Swal.fire({
                title: 'Remover participante?',
                text: `Remover "${name}" deste grupo?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, remover',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
