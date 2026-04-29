<?php include __DIR__ . '/../../layout/header.php'; ?>

<style>
    .manual-section {
        display: none;
        padding-top: 1rem;
    }
    .manual-section.active {
        display: block;
    }
    .mockup-window {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        font-size: 0.85rem;
    }
    .mockup-header {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 10px;
        margin-bottom: 15px;
        font-weight: bold;
        color: #495057;
    }
    .nav-pills .nav-link {
        color: #495057;
        background-color: #fff;
        border: 1px solid #dee2e6;
        margin-bottom: 0.5rem;
        text-align: left;
    }
    .nav-pills .nav-link.active {
        background-color: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }
    .nav-pills .nav-link i {
        width: 25px;
    }
    @media (max-width: 991.98px) {
        #manualContent {
            display: flex;
            gap: 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
            padding: .25rem .25rem .35rem;
        }
        #manualContent::-webkit-scrollbar { display: none; }
        #manualContent > .tab-pane {
            display: block !important;
            flex: 0 0 100%;
            min-width: 100%;
            scroll-snap-align: center;
            opacity: 1 !important;
            padding: 1rem 1rem 1.25rem;
        }
        #manualContent > .tab-pane.fade { transition: none; }
        .manual-carousel-indicator {
            border-radius: 14px;
            border: 1px solid rgba(0,0,0,0.08);
            overflow: hidden;
            background: linear-gradient(135deg, rgba(13,110,253,0.10), rgba(212,175,55,0.14));
        }
        .manual-carousel-title {
            font-weight: 900;
            letter-spacing: .01em;
            color: #1b1b2a;
        }
        .manual-carousel-hint {
            font-size: .72rem;
            letter-spacing: .08em;
            font-weight: 800;
            color: rgba(0,0,0,0.52);
            text-transform: uppercase;
        }
    }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manual do Sistema</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir Manual
        </button>
    </div>
</div>

<div class="row">
    <!-- Menu Lateral do Manual -->
    <div class="col-lg-3 mb-4 d-none d-lg-block">
        <div class="list-group nav-pills" id="manualTabs" role="tablist">
            <?php 
            $first = true;
            foreach ($sections as $key => $section): 
                if ($section['allowed']):
            ?>
                <button class="list-group-item list-group-item-action <?= $first ? 'active' : '' ?>" 
                        id="tab-<?= $key ?>" 
                        data-bs-toggle="pill" 
                        data-bs-target="#content-<?= $key ?>" 
                        type="button" 
                        role="tab">
                    <i class="<?= $section['icon'] ?> me-2"></i> <?= $section['title'] ?>
                </button>
            <?php 
                $first = false;
                endif; 
            endforeach; 
            ?>
        </div>
    </div>

    <!-- Conteúdo do Manual -->
    <div class="col-lg-9">
        <div class="manual-carousel-indicator d-lg-none mb-2 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="manual-carousel-title text-primary" id="manualCarouselTitle">Manual</div>
                <span class="badge bg-dark" id="manualCarouselStep">1/1</span>
            </div>
            <div class="manual-carousel-hint mt-1">
                <i class="fas fa-arrows-left-right me-2"></i>Deslize para mudar de seção
            </div>
        </div>
        <div class="tab-content bg-white p-4 rounded shadow-sm border" id="manualContent">
            
            <!-- Introdução -->
            <div class="tab-pane fade show active" id="content-intro" role="tabpanel">
                <h3 class="border-bottom pb-2 mb-3">Bem-vindo ao Sistema de Gestão de Igrejas</h3>
                <p>Este manual foi desenvolvido para auxiliar no uso de todas as funcionalidades do sistema.</p>
                <p>Utilize o menu lateral à esquerda para navegar entre os módulos disponíveis para o seu perfil de usuário.</p>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Nota:</strong> Os recursos exibidos neste manual dependem das permissões do seu usuário.
                </div>
            </div>

            <!-- Dashboard -->
            <div class="tab-pane fade" id="content-dashboard" role="tabpanel">
                <h3 class="border-bottom pb-2 mb-3">Painel Principal (Dashboard)</h3>
                <p>Resumo rápido das informações mais importantes.</p>
                
                <div class="mockup-window">
                    <div class="mockup-header"><i class="fas fa-home me-2"></i> Dashboard</div>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <div class="bg-primary text-white p-3 rounded">
                                <h6>Membros</h6>
                                <h3>150</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-success text-white p-3 rounded">
                                <h6>Entradas</h6>
                                <h3>R$ 5.000</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-danger text-white p-3 rounded">
                                <h6>Saídas</h6>
                                <h3>R$ 2.000</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-info text-white p-3 rounded">
                                <h6>Saldo</h6>
                                <h3>R$ 3.000</h3>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="border rounded p-2 bg-white">
                                <h6>Aniversariantes do Mês</h6>
                                <ul class="list-unstyled mb-0 small">
                                    <li>🎂 João Silva (10/03)</li>
                                    <li>🎂 Maria Souza (15/03)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <ul>
                    <li><strong>Total de Membros:</strong> Contagem atualizada.</li>
                    <li><strong>Entradas do Mês:</strong> Soma de dízimos e ofertas.</li>
                    <li><strong>Saldo:</strong> Saldo atual disponível.</li>
                </ul>
            </div>

            <!-- Membros -->
            <?php if ($sections['members']['allowed']): ?>
            <div class="tab-pane fade" id="content-members" role="tabpanel">
                <h3 class="border-bottom pb-2 mb-3">Gestão de Membros</h3>
                <p>Cadastro, edição e listagem de membros.</p>
                
                <div class="mockup-window">
                    <div class="mockup-header d-flex justify-content-between">
                        <span><i class="fas fa-users me-2"></i> Lista de Membros</span>
                        <button class="btn btn-primary btn-sm btn-xs">Novo Membro</button>
                    </div>
                    <table class="table table-sm table-bordered bg-white mb-0">
                        <thead class="table-light">
                            <tr><th>Nome</th><th>Status</th><th>Ações</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>João Silva</td>
                                <td><span class="badge bg-success">Ativo</span></td>
                                <td><button class="btn btn-xs btn-outline-secondary">Editar</button></td>
                            </tr>
                            <tr>
                                <td>Maria Souza</td>
                                <td><span class="badge bg-success">Ativo</span></td>
                                <td><button class="btn btn-xs btn-outline-secondary">Editar</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <ul>
                    <li><strong>Novo Membro:</strong> Preencha os dados e marque se é Dizimista ou Professor EBD.</li>
                    <li><strong>Carteirinha:</strong> Gere a carteirinha para impressão.</li>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Congregações -->
            <?php if ($sections['congregations']['allowed']): ?>
            <div class="tab-pane fade" id="content-congregations" role="tabpanel">
                <h3 class="border-bottom pb-2 mb-3">Congregações</h3>
                <p>Gestão das filiais ou congregações da igreja.</p>
                
                <div class="mockup-window">
                    <div class="mockup-header d-flex justify-content-between">
                        <span><i class="fas fa-church me-2"></i> Congregações</span>
                        <button class="btn btn-primary btn-sm btn-xs">Nova Congregação</button>
                    </div>
                    <table class="table table-sm table-bordered bg-white mb-0">
                        <thead class="table-light">
                            <tr><th>Nome</th><th>Cidade</th><th>Ações</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Sede</td>
                                <td>São Paulo</td>
                                <td><button class="btn btn-xs btn-outline-secondary">Editar</button></td>
                            </tr>
                            <tr>
                                <td>Filial 1</td>
                                <td>Osasco</td>
                                <td><button class="btn btn-xs btn-outline-secondary">Editar</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <ul>
                    <li>Cadastre cada congregação com seu endereço.</li>
                    <li>Membros e Financeiro são vinculados a uma congregação específica.</li>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Grupos -->
            <?php if (isset($sections['groups']) && $sections['groups']['allowed']): ?>
            <div class="tab-pane fade" id="content-groups" role="tabpanel">
                <h3 class="border-bottom pb-2 mb-3">Grupos e Células</h3>
                <p>Gestão completa de pequenos grupos, células ou núcleos.</p>
                
                <h5 class="mt-4 text-primary">1. Criação e Edição</h5>
                <ul>
                    <li><strong>Líder e Anfitrião:</strong> Ao definir o líder e anfitrião, o sistema sincroniza automaticamente com a lista de participantes.</li>
                    <li><strong>Elegibilidade:</strong> Apenas membros com status "Congregando" podem ser líderes.</li>
                </ul>

                <h5 class="mt-4 text-primary">2. Participantes</h5>
                <p>Você pode adicionar participantes de duas formas:</p>
                <ul>
                    <li><strong>Membro Existente:</strong> Selecione da lista.</li>
                    <li><strong>Convidado (Novo):</strong> Digite o nome de uma pessoa nova. O sistema criará o cadastro automaticamente como "Convidado".</li>
                </ul>
                
                <h5 class="mt-4 text-primary">3. Conversão de Convidados</h5>
                <p>Participantes marcados como <strong>Convidado</strong> possuem um botão verde de conversão. Utilize-o para:</p>
                <ul>
                    <li>Registrar que o convidado aceitou Jesus (Novo Convertido).</li>
                    <li>Registrar reconciliação.</li>
                    <li>Torná-lo membro efetivo.</li>
                </ul>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Dica:</strong> Se um membro sair da igreja (status diferente de Congregando), ele será removido automaticamente da liderança de qualquer grupo.
                </div>
            </div>
            <?php endif; ?>

            <!-- Financeiro (Entradas) -->
            <?php if ($sections['financial']['allowed']): ?>
            <div class="tab-pane fade" id="content-financial" role="tabpanel">
                <h3 class="border-bottom pb-2 mb-3">Financeiro: Entradas</h3>
                <p>Registro de Dízimos, Ofertas e outras receitas.</p>
                
                <div class="mockup-window">
                    <div class="mockup-header">
                        <span><i class="fas fa-hand-holding-usd me-2"></i> Lançar Entrada</span>
                    </div>
                    <div class="row g-2 bg-white p-2 border rounded">
                        <div class="col-6">
                            <label class="small">Membro</label>
                            <select class="form-select form-select-sm"><option>Selecione...</option></select>
                        </div>
                        <div class="col-6">
                            <label class="small">Valor (R$)</label>
                            <input type="text" class="form-control form-control-sm" value="0,00">
                        </div>
                        <div class="col-6">
                            <label class="small">Tipo</label>
                            <select class="form-select form-select-sm"><option>Dízimo</option><option>Oferta</option></select>
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <button class="btn btn-success btn-sm w-100">Salvar</button>
                        </div>
                    </div>
                </div>

                <ul>
                    <li><strong>Lançar Dízimo:</strong> Selecione o membro e o valor.</li>
                    <li><strong>Lançar Oferta:</strong> Registre ofertas de cultos (pode ser anônimo).</li>
                    <li><strong>Filtros:</strong> Pesquise por data, membro ou congregação.</li>
                    <li><strong>Privacidade:</strong> No relatório financeiro, os valores dos dízimos são ocultos por padrão (exibindo ****). Utilize o botão "Valores de Dízimos" para revelá-los quando necessário.</li>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Saídas (Despesas) -->
            <?php if ($sections['expenses']['allowed']): ?>
            <div class="tab-pane fade" id="content-expenses" role="tabpanel">
                <h3 class="border-bottom pb-2 mb-3">Financeiro: Saídas (Despesas)</h3>
                <p>Controle de contas pagas e despesas da igreja.</p>
                
                <div class="mockup-window">
                    <div class="mockup-header d-flex justify-content-between">
                        <span><i class="fas fa-file-invoice-dollar me-2"></i> Despesas</span>
                        <button class="btn btn-danger btn-sm btn-xs">Nova Despesa</button>
                    </div>
                    <table class="table table-sm table-bordered bg-white mb-0">
                        <thead class="table-light">
                            <tr><th>Descrição</th><th>Valor</th><th>Data</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Conta de Luz</td>
                                <td class="text-danger">- R$ 350,00</td>
                                <td>10/03/2026</td>
                            </tr>
                            <tr>
                                <td>Material de Limpeza</td>
                                <td class="text-danger">- R$ 120,00</td>
                                <td>12/03/2026</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <ul>
                    <li>Registre contas de luz, água, aluguel, ajuda de custo, etc.</li>
                    <li><strong>Filtros e Paginação:</strong> Utilize os filtros de data para encontrar registros e navegue pelas páginas usando os controles no rodapé da tabela. Você também pode escolher quantos itens ver por página (10, 25, 50, 100).</li>
                    <li><strong>Limpar Filtros:</strong> Use o botão "Limpar" para resetar a busca rapidamente.</li>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Fechamentos -->
            <?php if ($sections['closures']['allowed']): ?>
            <div class="tab-pane fade" id="content-closures" role="tabpanel">
                <h3 class="border-bottom pb-2 mb-3">Financeiro: Fechamentos</h3>
                <p>Fechamento de caixa mensal ou por período.</p>
                
                <div class="mockup-window">
                    <div class="mockup-header"><i class="fas fa-lock me-2"></i> Fechamento Mensal</div>
                    <div class="border rounded p-2 bg-white">
                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                            <span>Total Entradas:</span> <span class="text-success">R$ 5.000,00</span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                            <span>Total Saídas:</span> <span class="text-danger">- R$ 2.000,00</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Saldo Final:</span> <span class="text-primary">R$ 3.000,00</span>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm w-100 mt-2">Gerar Relatório PDF</button>
                </div>

                <ul>
                    <li>Gere um relatório consolidado do período.</li>
                    <li>O sistema calcula automaticamente: Saldo Anterior + Entradas - Saídas = Saldo Final.</li>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Eventos -->
            <?php if ($sections['events']['allowed']): ?>
            <div class="tab-pane fade" id="content-events" role="tabpanel">
                <h3 class="border-bottom pb-2 mb-3">Eventos</h3>
                <p>Agenda de eventos da igreja.</p>
                
                <div class="mockup-window">
                    <div class="mockup-header"><i class="fas fa-calendar-alt me-2"></i> Próximos Eventos</div>
                    <div class="list-group">
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Culto de Santa Ceia</strong><br>
                                <small class="text-muted">Domingo, 19:00</small>
                            </div>
                            <span class="badge bg-primary">Culto</span>
                        </div>
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Congresso de Jovens</strong><br>
                                <small class="text-muted">Sábado, 14:00</small>
                            </div>
                            <span class="badge bg-warning text-dark">Especial</span>
                        </div>
                    </div>
                </div>

                <ul>
                    <li>Cadastre cultos especiais, festividades e reuniões.</li>
                    <li>Eventos aparecem no site público e no painel.</li>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Relatórios de Culto -->
            <?php if ($sections['service_reports']['allowed']): ?>
            <div class="tab-pane fade" id="content-service_reports" role="tabpanel">
                <h3 class="border-bottom pb-2 mb-3">Relatórios de Culto</h3>
                <p>O módulo de Relatórios de Culto serve para registrar estatisticamente a frequência e o crescimento da igreja. É fundamental que seja preenchido após cada culto oficial.</p>
                
                <div class="mockup-window">
                    <div class="mockup-header"><i class="fas fa-clipboard-list me-2"></i> Novo Relatório</div>
                    <div class="bg-white p-3 border rounded">
                        <div class="mb-3">
                            <label class="small fw-bold">Data do Culto</label>
                            <input type="date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                        </div>
                        <h6 class="small text-muted border-bottom pb-1">Contagem de Presentes</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="small">Homens</label>
                                <input type="number" class="form-control form-control-sm" value="20">
                            </div>
                            <div class="col-4">
                                <label class="small">Mulheres</label>
                                <input type="number" class="form-control form-control-sm" value="30">
                            </div>
                            <div class="col-4">
                                <label class="small">Crianças</label>
                                <input type="number" class="form-control form-control-sm" value="10">
                            </div>
                        </div>
                        <div class="alert alert-light border p-2 mb-3 small">
                            <strong>Total de Presentes:</strong> 60 pessoas
                        </div>
                        
                        <h6 class="small text-muted border-bottom pb-1">Visitantes e Decisões</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="small">Visitantes</label>
                                <input type="number" class="form-control form-control-sm" value="5">
                                <div class="form-text x-small">Pessoas que não são membros.</div>
                            </div>
                            <div class="col-6">
                                <label class="small">Conversões</label>
                                <input type="number" class="form-control form-control-sm" value="1">
                                <div class="form-text x-small">Pessoas que aceitaram a Jesus.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="mt-4">Por que preencher?</h5>
                <ul>
                    <li><strong>Histórico de Crescimento:</strong> O sistema gera gráficos que mostram a evolução da frequência ao longo dos meses e anos.</li>
                    <li><strong>Acompanhamento de Visitantes:</strong> Permite saber quantos visitantes a igreja recebe em média.</li>
                    <li><strong>Registro de Decisões:</strong> Mantém o histórico de quantas almas foram ganhas para o Reino.</li>
                </ul>

                <h5 class="mt-4">Dicas de Preenchimento:</h5>
                <ul>
                    <li>Preencha logo após o culto para não esquecer os números.</li>
                    <li>Se não houver contagem exata, faça uma estimativa aproximada.</li>
                    <li>O campo "Visitantes" já deve estar incluído na contagem de Homens/Mulheres/Crianças (é um subconjunto, não uma soma adicional, dependendo de como sua igreja conta). <em>No nosso sistema, a contagem total é a soma de Homens + Mulheres + Crianças. Visitantes é apenas informativo extra.</em></li>
                </ul>

                <div class="alert alert-info mt-3">
                    <h6 class="alert-heading"><i class="fas fa-info-circle me-1"></i> E o Financeiro do Culto?</h6>
                    <p class="mb-0 small">As ofertas e dízimos recolhidos no culto devem ser lançados separadamente no menu <strong>Financeiro > Entradas</strong>. O Relatório de Culto foca na estatística de pessoas (presença), enquanto o módulo Financeiro cuida dos valores monetários.</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- EBD -->
            <?php if ($sections['ebd']['allowed']): ?>
            <div class="tab-pane fade" id="content-ebd" role="tabpanel">
                <h3 class="border-bottom pb-2 mb-3">Escola Bíblica Dominical (EBD)</h3>
                <p>Gestão completa da escola bíblica: Classes, Alunos, Professores, Chamada e Relatórios.</p>

                <ul class="nav nav-tabs mb-3" id="ebdManualTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#ebd-classes">1. Classes</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ebd-matricula">2. Matrícula</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ebd-aula">3. Lançar Aula</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ebd-relatorios">4. Relatórios</a></li>
                </ul>

                <div class="tab-content border p-3 rounded bg-light mb-3">
                    <!-- Aba 1: Classes -->
                    <div class="tab-pane fade show active" id="ebd-classes">
                        <h5>Criando Classes</h5>
                        <p>O primeiro passo é criar as turmas da EBD. Elas são divididas por faixa etária.</p>
                        <div class="mockup-window bg-white">
                            <div class="mb-2"><strong>Nome da Classe:</strong> Jovens</div>
                            <div class="mb-2"><strong>Faixa Etária:</strong> 15 a 25 anos</div>
                            <div class="mb-2"><strong>Congregação:</strong> Sede (ou Global)</div>
                        </div>
                        <p class="small text-muted">Exemplo: Berçário (0-2 anos), Kids (3-8 anos), Teens (9-14 anos), Jovens, Adultos.</p>
                    </div>

                    <!-- Aba 2: Matrícula -->
                    <div class="tab-pane fade" id="ebd-matricula">
                        <h5>Matriculando Alunos</h5>
                        <p>Para um aluno aparecer na chamada, ele deve estar matriculado na classe.</p>
                        <div class="alert alert-warning py-2 small">
                            <i class="fas fa-exclamation-triangle me-1"></i> <strong>Regra Importante:</strong> Um aluno só pode estar matriculado em <strong>UMA</strong> classe ativa por vez. Se ele não aparecer na lista de matrícula, verifique se já não está em outra turma.
                        </div>
                        <ul>
                            <li>Acesse a Classe desejada.</li>
                            <li>Clique em "Matricular Aluno".</li>
                            <li>Selecione o membro na lista (apenas membros da congregação da classe aparecerão).</li>
                        </ul>
                        <p><strong>Professores:</strong> O processo é similar. Apenas membros marcados como "Professor de EBD" no cadastro de membros aparecerão na lista de seleção.</p>
                    </div>

                    <!-- Aba 3: Aula -->
                    <div class="tab-pane fade" id="ebd-aula">
                        <h5>Lançando a Aula (Chamada e Oferta)</h5>
                        <p>No dia da aula (geralmente domingo), o secretário ou professor deve lançar o registro.</p>
                        
                        <div class="mockup-window bg-white">
                            <div class="mockup-header">Nova Aula - Jovens</div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="small">Data</label>
                                    <input type="date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="small">Tema da Lição</label>
                                    <input type="text" class="form-control form-control-sm" value="A Criação">
                                </div>
                                <div class="col-12 mt-2">
                                    <h6 class="small border-bottom">Chamada</h6>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" checked>
                                        <label class="form-check-label small">João Silva</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox">
                                        <label class="form-check-label small">Maria Souza (Ausente)</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <h6 class="small border-bottom">Financeiro da Classe</h6>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Oferta R$</span>
                                        <input type="text" class="form-control" value="50,00">
                                    </div>
                                    <div class="form-text x-small text-success">
                                        <i class="fas fa-check-circle"></i> Este valor entrará automaticamente no Caixa da Igreja como "Oferta EBD".
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aba 4: Relatórios -->
                    <div class="tab-pane fade" id="ebd-relatorios">
                        <h5>Acompanhamento e Relatórios</h5>
                        <p>O sistema oferece relatórios detalhados para a superintendência da EBD.</p>
                        <ul>
                            <li><strong>Por Dia:</strong> Resumo de quantos alunos vieram no domingo, total de ofertas e visitantes.</li>
                            <li><strong>Por Classe:</strong> Comparativo de qual classe é mais assídua.</li>
                            <li><strong>Financeiro:</strong> Total arrecadado pela EBD no mês/ano.</li>
                        </ul>
                        <p>Acesse o menu "Relatórios" dentro do módulo EBD para visualizar os gráficos.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Pagamento Sistema -->
            <?php if ($sections['system_payments']['allowed']): ?>
            <div class="tab-pane fade" id="content-system_payments" role="tabpanel">
                <h3 class="border-bottom pb-2 mb-3">Pagamento do Sistema</h3>
                <p>Área para gestão do pagamento da mensalidade/hospedagem do sistema.</p>
                
                <div class="mockup-window">
                    <div class="mockup-header"><i class="fas fa-credit-card me-2"></i> Mensalidade</div>
                    <div class="alert alert-warning py-2 mb-2">
                        <i class="fas fa-exclamation-circle me-1"></i> Vencimento: 05/<?= date('m') ?>
                    </div>
                    <div class="bg-white p-3 border rounded text-center">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/d/d0/QR_code_for_mobile_English_Wikipedia.svg" alt="QR Code" style="width: 100px; height: 100px;" class="mb-2">
                        <p class="small mb-0">Escaneie para pagar com Pix</p>
                        <button class="btn btn-sm btn-outline-primary mt-2">Copiar Código Pix</button>
                    </div>
                </div>

                <ul>
                    <li>Visualize o status do pagamento mensal.</li>
                    <li>Gere o código Pix para pagamento.</li>
                    <li>Envie o comprovante.</li>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Usuários -->
            <?php if ($sections['users']['allowed']): ?>
            <div class="tab-pane fade" id="content-users" role="tabpanel">
                <h3 class="border-bottom pb-2 mb-3">Usuários e Permissões</h3>
                <p>Gerencie quem acessa o sistema.</p>
                
                <div class="mockup-window">
                    <div class="mockup-header d-flex justify-content-between">
                        <span><i class="fas fa-user-shield me-2"></i> Usuários</span>
                        <button class="btn btn-primary btn-sm btn-xs">Novo Usuário</button>
                    </div>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>admin (Administrador)</span>
                            <span class="badge bg-success">Ativo</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>secretaria (Secretária)</span>
                            <span class="badge bg-success">Ativo</span>
                        </li>
                    </ul>
                </div>

                <ul>
                    <li>Crie logins para secretários, tesoureiros, etc.</li>
                    <li>Defina o nível de acesso (Role) de cada um.</li>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Alterar Senha -->
            <div class="tab-pane fade" id="content-password" role="tabpanel">
                <h3 class="border-bottom pb-2 mb-3">Alterar Senha</h3>
                <p>Segurança da sua conta.</p>
                
                <div class="mockup-window">
                    <div class="mockup-header"><i class="fas fa-key me-2"></i> Alterar Senha</div>
                    <div class="bg-white p-2 border rounded">
                        <div class="mb-2">
                            <label class="small">Senha Atual</label>
                            <input type="password" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="small">Nova Senha</label>
                            <input type="password" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="small">Confirmar Nova Senha</label>
                            <input type="password" class="form-control form-control-sm">
                        </div>
                        <button class="btn btn-primary btn-sm w-100">Salvar Nova Senha</button>
                    </div>
                </div>

                <ul>
                    <li>Acesse este menu para trocar sua senha de acesso periodicamente.</li>
                    <li>Recomendamos usar senhas fortes.</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>

<script>
    (function () {
        const carousel = document.getElementById('manualContent');
        const titleEl = document.getElementById('manualCarouselTitle');
        const stepEl = document.getElementById('manualCarouselStep');
        if (!carousel || !titleEl || !stepEl) return;

        const panes = Array.from(carousel.querySelectorAll(':scope > .tab-pane'));
        if (panes.length === 0) return;

        const total = panes.length;
        const titleById = {};
        const tabButtons = Array.from(document.querySelectorAll('#manualTabs [data-bs-target]'));
        tabButtons.forEach((btn) => {
            const target = btn.getAttribute('data-bs-target');
            if (!target) return;
            const id = target.replace('#', '');
            titleById[id] = (btn.textContent || '').trim();
        });

        const clampIndex = (i) => Math.max(0, Math.min(i, total - 1));
        const getIndex = () => {
            const w = carousel.clientWidth || 1;
            return clampIndex(Math.round(carousel.scrollLeft / w));
        };

        const render = (i) => {
            const pane = panes[i];
            const paneId = pane ? pane.id : '';
            const title = titleById[paneId] || (paneId || 'Manual');
            titleEl.textContent = title;
            stepEl.textContent = (i + 1) + '/' + total;
        };

        let raf = 0;
        const onScroll = () => {
            if (raf) return;
            raf = requestAnimationFrame(() => {
                raf = 0;
                render(getIndex());
            });
        };

        carousel.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', () => render(getIndex()));
        render(getIndex());
    })();
</script>
