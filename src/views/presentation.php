<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestão para Igrejas - Organização e Excelência</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #333;
            overflow-x: hidden;
        }
        .hero-section {
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
            color: white;
            padding: 100px 0 80px;
            position: relative;
        }
        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 0;
            width: 100%;
            height: 100px;
            background: white;
            transform: skewY(-2deg);
            z-index: 1;
        }
        .feature-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        .icon-box {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }
        .cta-btn {
            padding: 15px 30px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
        }
        .whatsapp-btn {
            background-color: #25D366;
            color: white;
            border: none;
        }
        .whatsapp-btn:hover {
            background-color: #128C7E;
            color: white;
            transform: scale(1.05);
        }
        .section-title {
            font-weight: 800;
            position: relative;
            display: inline-block;
            margin-bottom: 50px;
        }
        .section-title::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 4px;
            background: #0d6efd;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        .stats-section {
            background: #f8f9fa;
            padding: 80px 0;
        }
        .testimonial-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            font-style: italic;
            position: relative;
        }
        .testimonial-card::before {
            content: '\201C';
            font-size: 80px;
            color: rgba(13, 110, 253, 0.1);
            position: absolute;
            top: -10px;
            left: 20px;
            font-family: serif;
        }
        .footer {
            background: #0f2027;
            color: rgba(255,255,255,0.7);
            padding: 50px 0 20px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark position-absolute w-100" style="z-index: 10;">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3" href="/"><i class="fas fa-church text-primary"></i> Church<span class="text-primary">Sys</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#recursos">Funcionalidades</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#vantagens">Por que escolher?</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a href="https://wa.me/5592994791168?text=Ol%C3%A1!%20Gostaria%20de%20saber%20mais%20sobre%20o%20Sistema%20para%20Igrejas." target="_blank" class="btn btn-outline-light rounded-pill px-4">
                            <i class="fab fa-whatsapp me-2"></i> Falar com Consultor
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container position-relative" style="z-index: 2;">
            <div class="row align-items-center">
                <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
                    <span class="badge bg-primary px-3 py-2 rounded-pill mb-3 fs-6">Gestão Eclesiástica Inteligente</span>
                    <h1 class="display-4 fw-bold mb-4 lh-sm">Organize sua Igreja com <span class="text-info">Excelência e Facilidade</span></h1>
                    <p class="lead mb-5 opacity-75">Liberte sua liderança das planilhas de papel. Tenha o controle financeiro, membresia, EBD e células na palma da sua mão, em qualquer lugar.</p>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                        <a href="https://wa.me/5592994791168?text=Ol%C3%A1!%20Gostaria%20de%20uma%20demonstra%C3%A7%C3%A3o%20do%20sistema." target="_blank" class="btn whatsapp-btn cta-btn shadow-lg">
                            <i class="fab fa-whatsapp fa-lg me-2"></i> Solicitar Demonstração
                        </a>
                        <a href="#recursos" class="btn btn-outline-light cta-btn">Conhecer Recursos</a>
                    </div>
                </div>
                <div class="col-lg-6 position-relative">
                    <!-- Placeholder for an interface mockup -->
                    <div class="bg-white rounded-4 p-2 shadow-lg" style="transform: rotate(2deg); border: 8px solid rgba(255,255,255,0.1);">
                        <div class="rounded-3 overflow-hidden bg-light" style="height: 400px; position: relative;">
                            <!-- Simulating Dashboard UI -->
                            <div class="d-flex h-100">
                                <div class="w-25 bg-dark p-3 d-none d-md-block">
                                    <div class="bg-secondary rounded mb-3 opacity-50" style="height: 20px;"></div>
                                    <div class="bg-secondary rounded mb-2 opacity-25" style="height: 15px;"></div>
                                    <div class="bg-secondary rounded mb-2 opacity-25" style="height: 15px;"></div>
                                    <div class="bg-secondary rounded mb-2 opacity-25" style="height: 15px;"></div>
                                </div>
                                <div class="w-100 p-4">
                                    <div class="d-flex justify-content-between mb-4">
                                        <div class="bg-secondary rounded w-25 opacity-25" style="height: 20px;"></div>
                                        <div class="bg-info rounded w-25 opacity-50" style="height: 20px;"></div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-4"><div class="bg-success rounded opacity-25 h-100" style="min-height: 80px;"></div></div>
                                        <div class="col-4"><div class="bg-primary rounded opacity-25 h-100"></div></div>
                                        <div class="col-4"><div class="bg-warning rounded opacity-25 h-100"></div></div>
                                    </div>
                                    <div class="bg-secondary rounded w-100 opacity-10" style="height: 150px;"></div>
                                </div>
                            </div>
                            <div class="position-absolute top-50 start-50 translate-middle text-center w-100">
                                <i class="fas fa-chart-line fa-4x text-primary mb-3 opacity-75"></i>
                                <h4 class="text-dark fw-bold">Dashboard Intuitivo</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recursos Section -->
    <section id="recursos" class="py-5 mt-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title text-dark">Tudo que seu Ministério Precisa</h2>
                <p class="text-muted lead mx-auto" style="max-width: 700px;">Um sistema completo, pensado por quem entende a dinâmica e as necessidades reais de uma igreja moderna.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card p-4">
                        <div class="icon-box text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Gestão de Membros</h4>
                        <p class="text-muted">Cadastro completo, carteirinhas digitais, controle de batismos, rol de membros e acompanhamento de novos convertidos.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card p-4">
                        <div class="icon-box text-success bg-success bg-opacity-10">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Gestão Financeira e Contábil</h4>
                        <p class="text-muted">Plano de contas estruturado, conciliação bancária (OFX), balancetes mensais e integração total para o seu contador.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card p-4">
                        <div class="icon-box text-warning bg-warning bg-opacity-10">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Escola Bíblica (EBD)</h4>
                        <p class="text-muted">Criação de classes, matrículas, controle de presença via QR Code, registro de ofertas da EBD e histórico de lições.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card p-4">
                        <div class="icon-box text-danger bg-danger bg-opacity-10">
                            <i class="fas fa-home"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Células e Grupos</h4>
                        <p class="text-muted">Organize os pequenos grupos, defina líderes, anfitriões e acompanhe o crescimento e multiplicação das células.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card p-4">
                        <div class="icon-box text-info bg-info bg-opacity-10">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Agenda e Eventos</h4>
                        <p class="text-muted">Calendário da igreja, controle de presença em cultos/eventos, publicação de banners e organização das programações.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card p-4">
                        <div class="icon-box text-dark bg-dark bg-opacity-10">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Portal do Membro</h4>
                        <p class="text-muted">Acesso exclusivo para os irmãos verem seus dízimos, carteirinha digital, agenda da igreja e baixar materiais de estudo.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

        <!-- Por que Escolher Section -->
    <section id="vantagens" class="stats-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-5 mb-lg-0">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill mb-3 fs-6">Exclusivo</span>
                    <h2 class="fw-bold mb-4">Desenvolvido para Pastores, Focado no Reino</h2>
                    <p class="lead text-muted mb-4">Diferente de outros sistemas comerciais, não somos apenas um sistema. Somos a extensão digital da sua congregação.</p>
                    
                    <ul class="list-unstyled mt-4">
                        <li class="mb-3 d-flex align-items-center">
                            <i class="fas fa-check-circle text-success fs-4 me-3"></i>
                            <span class="fs-5 text-dark fw-bold">Site + Sistema integrados</span>
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="fas fa-check-circle text-success fs-4 me-3"></i>
                            <span class="fs-5 text-dark fw-bold">Cada igreja com seu próprio ambiente</span>
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="fas fa-check-circle text-success fs-4 me-3"></i>
                            <span class="fs-5 text-dark fw-bold">Fácil de usar (Interface amigável)</span>
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="fas fa-check-circle text-success fs-4 me-3"></i>
                            <span class="fs-5 text-dark fw-bold">Acesso de qualquer lugar (100% Nuvem)</span>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6 offset-lg-1">
                    <div class="row g-4">
                        <div class="col-6">
                            <div class="bg-white p-4 rounded-4 shadow-sm text-center mb-4">
                                <i class="fas fa-globe fa-3x text-primary mb-3"></i>
                                <h4 class="fw-bold text-dark">Site Público</h4>
                                <p class="text-muted small mb-0">Divulgue seus cultos e eventos para todos</p>
                            </div>
                            <div class="bg-white p-4 rounded-4 shadow-sm text-center">
                                <i class="fas fa-lock fa-3x text-success mb-3"></i>
                                <h4 class="fw-bold text-dark">Painel Privado</h4>
                                <p class="text-muted small mb-0">Gestão segura só para a liderança</p>
                            </div>
                        </div>
                        <div class="col-6 mt-4">
                            <div class="bg-white p-4 rounded-4 shadow-sm text-center mb-4">
                                <i class="fas fa-id-badge fa-3x text-warning mb-3"></i>
                                <h4 class="fw-bold text-dark">Portal do Irmão</h4>
                                <p class="text-muted small mb-0">O membro acompanha seus dízimos e carteirinha</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- Telas do Sistema (Screenshots) -->
    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="section-title text-dark">Por dentro do Sistema</h2>
                <p class="text-muted lead mx-auto" style="max-width: 700px;">Uma interface limpa, moderna e pensada para facilitar o dia a dia da secretaria e tesouraria da sua igreja.</p>
            </div>

            <div class="row g-5">
                <!-- Tela 1: Dashboard -->
                <div class="col-lg-12">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="row g-0">
                            <div class="col-md-5 bg-dark text-white p-5 d-flex flex-column justify-content-center">
                                <div class="icon-box bg-white text-dark mb-4">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h3 class="fw-bold mb-3">Visão Geral (Dashboard)</h3>
                                <p class="opacity-75 fs-5">Acompanhe o crescimento da sua igreja em tempo real. Gráficos de entradas financeiras, aniversariantes do mês e resumo de membros ativos, tudo em uma única tela.</p>
                            </div>
                            <div class="col-md-7 bg-white p-4 d-flex align-items-center justify-content-center">
                                <!-- Mockup HTML do Dashboard -->
                                <div class="w-100 border rounded shadow-sm p-3 bg-light">
                                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                        <div class="fw-bold">Painel Administrativo</div>
                                        <div class="text-muted small"><i class="fas fa-user-circle"></i> Pr. Presidente</div>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-4"><div class="bg-white p-2 rounded border border-start border-primary border-4"><small class="text-muted d-block">Membros</small><span class="fw-bold fs-5">150</span></div></div>
                                        <div class="col-4"><div class="bg-white p-2 rounded border border-start border-success border-4"><small class="text-muted d-block">Entradas</small><span class="fw-bold fs-5 text-success">R$ 5.430</span></div></div>
                                        <div class="col-4"><div class="bg-white p-2 rounded border border-start border-warning border-4"><small class="text-muted d-block">Células</small><span class="fw-bold fs-5">12</span></div></div>
                                    </div>
                                    <div class="bg-white p-3 rounded border" style="height: 120px;">
                                        <small class="text-muted mb-2 d-block">Balanço Mensal</small>
                                        <div class="d-flex align-items-end h-75 gap-2">
                                            <div class="bg-success rounded-top w-25" style="height: 40%;"></div>
                                            <div class="bg-success rounded-top w-25" style="height: 70%;"></div>
                                            <div class="bg-success rounded-top w-25" style="height: 50%;"></div>
                                            <div class="bg-success rounded-top w-25" style="height: 90%;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tela 2: Financeiro Avançado -->
                <div class="col-lg-12">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="row g-0 flex-row-reverse">
                            <div class="col-md-6 bg-primary text-white p-5 d-flex flex-column justify-content-center">
                                <div class="icon-box bg-white text-primary mb-4">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <h3 class="fw-bold mb-3">Gestão Financeira e Contábil</h3>
                                <p class="opacity-75 fs-5 mb-4">Solução completa para controle, organização e integração contábil das finanças da igreja, garantindo total transparência.</p>
                                
                                <ul class="list-unstyled mb-0 fs-6">
                                    <li class="mb-2"><i class="fas fa-check-circle text-warning me-2"></i> <strong>Plano de Contas:</strong> Classificação contábil (ativo, passivo, receitas).</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-warning me-2"></i> <strong>Conciliação OFX:</strong> Leitura automática de extratos bancários.</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-warning me-2"></i> <strong>Integração:</strong> Exportação de dados formatados para contadores.</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-warning me-2"></i> <strong>Prestação de Contas:</strong> Balancetes mensais para a diretoria.</li>
                                </ul>
                            </div>
                            <div class="col-md-6 bg-white p-4 d-flex align-items-center justify-content-center">
                                <!-- Mockup HTML do Financeiro -->
                                <div class="w-100 border rounded shadow-sm p-3 bg-light">
                                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                        <div class="fw-bold text-primary"><i class="fas fa-university me-2"></i> Conciliação Bancária</div>
                                        <div class="btn btn-sm btn-outline-primary"><i class="fas fa-file-import"></i> OFX</div>
                                    </div>
                                    <div class="bg-white rounded border">
                                        <table class="table table-sm table-borderless mb-0">
                                            <thead class="table-light text-muted small">
                                                <tr><th>Data</th><th>Descrição (Extrato)</th><th>Valor</th><th>Status</th></tr>
                                            </thead>
                                            <tbody class="small">
                                                <tr><td>15/10</td><td>PIX RECEBIDO - JOAO</td><td class="text-success">+ 250,00</td><td><span class="badge bg-success"><i class="fas fa-check"></i> OK</span></td></tr>
                                                <tr><td>15/10</td><td>PAG TITULO ENERGIA</td><td class="text-danger">- 450,00</td><td><span class="badge bg-warning text-dark"><i class="fas fa-search"></i> Revisar</span></td></tr>
                                                <tr><td>16/10</td><td>DEP DINHEIRO AG 123</td><td class="text-success">+ 1.200,00</td><td><span class="badge bg-success"><i class="fas fa-check"></i> OK</span></td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="mt-3 pt-2 border-top">
                                        <div class="fw-bold text-secondary mb-2 small"><i class="fas fa-chart-pie me-1"></i> Balancete Resumido</div>
                                        <div class="row g-2">
                                            <div class="col-6"><div class="p-2 border rounded bg-white text-center"><small class="d-block text-muted">Ativo Circulante</small><strong>R$ 45.230,00</strong></div></div>
                                            <div class="col-6"><div class="p-2 border rounded bg-white text-center"><small class="d-block text-muted">Despesas Mês</small><strong class="text-danger">R$ 12.400,00</strong></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tela 3: EBD -->
                <div class="col-lg-12">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="row g-0">
                            <div class="col-md-5 bg-warning text-dark p-5 d-flex flex-column justify-content-center">
                                <div class="icon-box bg-white text-warning mb-4">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <h3 class="fw-bold mb-3">Escola Bíblica (EBD)</h3>
                                <p class="opacity-75 fs-5">Organize turmas, professores e alunos. Faça a chamada pelo celular e controle as ofertas de cada classe sem complicações.</p>
                            </div>
                            <div class="col-md-7 bg-white p-4 d-flex align-items-center justify-content-center">
                                <!-- Mockup HTML EBD -->
                                <div class="w-100 border rounded shadow-sm p-3 bg-light">
                                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                        <div class="fw-bold text-warning"><i class="fas fa-chalkboard-teacher me-2"></i> Classes da EBD</div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="card border-0 shadow-sm">
                                                <div class="card-body p-3">
                                                    <h6 class="fw-bold">Jovens Vencedores</h6>
                                                    <small class="text-muted d-block mb-2">Prof: Marcos</small>
                                                    <span class="badge bg-primary">25 Alunos</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="card border-0 shadow-sm">
                                                <div class="card-body p-3">
                                                    <h6 class="fw-bold">Crianças (Kids)</h6>
                                                    <small class="text-muted d-block mb-2">Profª: Sara</small>
                                                    <span class="badge bg-success">18 Alunos</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tela 4: Células -->
                <div class="col-lg-12">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="row g-0 flex-row-reverse">
                            <div class="col-md-5 bg-danger text-white p-5 d-flex flex-column justify-content-center">
                                <div class="icon-box bg-white text-danger mb-4">
                                    <i class="fas fa-home"></i>
                                </div>
                                <h3 class="fw-bold mb-3">Células / Grupos</h3>
                                <p class="opacity-75 fs-5">Acompanhe de perto os pequenos grupos da sua igreja. Saiba onde se reúnem, quem são os líderes e os participantes de cada casa.</p>
                            </div>
                            <div class="col-md-7 bg-white p-4 d-flex align-items-center justify-content-center">
                                <!-- Mockup HTML Células -->
                                <div class="w-100 border rounded shadow-sm p-3 bg-light">
                                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                        <div class="fw-bold text-danger"><i class="fas fa-users me-2"></i> Lista de Células</div>
                                    </div>
                                    <div class="bg-white rounded border p-3 mb-2 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 fw-bold">Célula Betel</h6>
                                            <small class="text-muted"><i class="far fa-clock"></i> Terça, 19:30</small>
                                        </div>
                                        <div class="text-end">
                                            <small class="d-block text-muted">Líder: Pedro</small>
                                            <span class="badge bg-secondary">12 membros</span>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded border p-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 fw-bold">Célula Manancial</h6>
                                            <small class="text-muted"><i class="far fa-clock"></i> Quinta, 20:00</small>
                                        </div>
                                        <div class="text-end">
                                            <small class="d-block text-muted">Líder: Ana</small>
                                            <span class="badge bg-secondary">8 membros</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tela 5: Relatórios -->
                <div class="col-lg-12">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="row g-0">
                            <div class="col-md-5 bg-success text-white p-5 d-flex flex-column justify-content-center">
                                <div class="icon-box bg-white text-success mb-4">
                                    <i class="fas fa-print"></i>
                                </div>
                                <h3 class="fw-bold mb-3">Relatórios Precisos</h3>
                                <p class="opacity-75 fs-5">Gere PDFs e relatórios instantâneos para reuniões de diretoria. Tudo o que entra e sai documentado de forma clara e transparente.</p>
                            </div>
                            <div class="col-md-7 bg-white p-4 d-flex align-items-center justify-content-center">
                                <!-- Mockup HTML Relatórios -->
                                <div class="w-100 border rounded shadow-sm p-3 bg-light text-center">
                                    <i class="fas fa-file-pdf fa-4x text-danger mb-3"></i>
                                    <h5 class="fw-bold">Relatório Mensal - Outubro</h5>
                                    <div class="progress mt-3 mb-2" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 70%">Receitas (70%)</div>
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 30%">Despesas (30%)</div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3 px-4">
                                        <span class="text-success fw-bold"><i class="fas fa-arrow-up"></i> R$ 12.450</span>
                                        <span class="text-danger fw-bold"><i class="fas fa-arrow-down"></i> R$ 4.200</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tela 6: Presença / Eventos -->
                <div class="col-lg-12">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="row g-0 flex-row-reverse">
                            <div class="col-md-5 bg-info text-white p-5 d-flex flex-column justify-content-center">
                                <div class="icon-box bg-white text-info mb-4">
                                    <i class="fas fa-qrcode"></i>
                                </div>
                                <h3 class="fw-bold mb-3">Controle de Presença (QR Code)</h3>
                                <p class="opacity-75 fs-5">Fim das listas de papel. Leia o QR Code da carteirinha do membro pelo celular e registre a presença em cultos e eventos instantaneamente.</p>
                            </div>
                            <div class="col-md-7 bg-white p-4 d-flex align-items-center justify-content-center">
                                <!-- Mockup HTML Presença -->
                                <div class="w-100 border rounded shadow-sm p-3 bg-light text-center">
                                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2 text-start">
                                        <div class="fw-bold text-info"><i class="fas fa-calendar-check me-2"></i> Culto de Celebração</div>
                                        <span class="badge bg-success">145 Presentes</span>
                                    </div>
                                    <div class="bg-white rounded border p-4 d-inline-block shadow-sm position-relative mb-3">
                                        <!-- Simulate QR Scanner border -->
                                        <div class="position-absolute top-0 start-0 w-100 h-100 border border-info border-4 rounded" style="opacity: 0.3; clip-path: polygon(0 0, 20% 0, 20% 10%, 10% 10%, 10% 20%, 0 20%, 0 0, 80% 0, 100% 0, 100% 20%, 90% 20%, 90% 10%, 80% 10%, 80% 0, 100% 80%, 100% 100%, 80% 100%, 80% 90%, 90% 90%, 90% 80%, 100% 80%, 0 80%, 0 100%, 20% 100%, 20% 90%, 10% 90%, 10% 80%, 0 80%);"></div>
                                        <i class="fas fa-qrcode fa-5x text-dark"></i>
                                    </div>
                                    <h5 class="text-success fw-bold"><i class="fas fa-check-circle"></i> Presença Registrada!</h5>
                                    <p class="text-muted small">Irmão: João Silva</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tela 7: Ferramentas Práticas -->
                <div class="col-lg-12">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="row g-0">
                            <div class="col-md-5 bg-primary text-white p-5 d-flex flex-column justify-content-center" style="background: linear-gradient(135deg, #128C7E, #25D366);">
                                <div class="icon-box bg-white text-success mb-4">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <h3 class="fw-bold mb-3">Tudo no WhatsApp</h3>
                                <p class="opacity-75 fs-5">Envie recibos de dízimo direto no WhatsApp do membro. Acompanhe os aniversariantes do dia e mande mensagens automáticas para não esquecer ninguém!</p>
                            </div>
                            <div class="col-md-7 bg-white p-4 d-flex align-items-center justify-content-center">
                                <!-- Mockup HTML WhatsApp/Alerts -->
                                <div class="w-100 border rounded shadow-sm p-3 bg-light">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="card border-success shadow-sm">
                                                <div class="card-body d-flex justify-content-between align-items-center p-3">
                                                    <div>
                                                        <h6 class="fw-bold mb-1"><i class="fas fa-birthday-cake text-warning me-2"></i> Aniversariante do Dia</h6>
                                                        <span class="text-muted small">Maria Sousa completa 35 anos hoje!</span>
                                                    </div>
                                                    <button class="btn btn-sm btn-success rounded-pill"><i class="fab fa-whatsapp"></i> Parabéns</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="card border-primary shadow-sm">
                                                <div class="card-body d-flex justify-content-between align-items-center p-3">
                                                    <div>
                                                        <h6 class="fw-bold mb-1"><i class="fas fa-file-invoice text-primary me-2"></i> Recibo Gerado</h6>
                                                        <span class="text-muted small">Recibo #1042 (R$ 250,00)</span>
                                                    </div>
                                                    <button class="btn btn-sm btn-outline-success rounded-pill"><i class="fab fa-whatsapp me-1"></i> Enviar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Vídeo Demonstração Section -->
    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-5 mb-lg-0">
                    <span class="badge bg-danger text-white px-3 py-2 rounded-pill mb-3 fs-6"><i class="fab fa-youtube me-1"></i> Na Prática</span>
                    <h2 class="fw-bold mb-4">Portal do Membro</h2>
                    <p class="lead text-muted mb-4">Assista a este rápido manual e veja como é fácil para o membro acessar o sistema, baixar sua carteirinha digital e acompanhar seus dízimos.</p>
                    <a href="https://wa.me/5592994791168?text=Ol%C3%A1!%20Vi%20o%20v%C3%ADdeo%20do%20Portal%20do%20Membro%20e%20gostei." target="_blank" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="fab fa-whatsapp me-2"></i> Quero esse sistema na minha igreja
                    </a>
                </div>
                <div class="col-lg-7">
                    <div class="ratio ratio-16x9 rounded-4 shadow-lg overflow-hidden border border-4 border-light">
                        <iframe src="https://www.youtube.com/embed/OUzyvWY4eis?start=245" title="Manual do Portal do Membro" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-5" style="background: linear-gradient(45deg, #0d6efd, #0dcaf0); color: white;">
        <div class="container text-center py-5">
            <h2 class="display-5 fw-bold mb-4">Pronto para transformar a gestão da sua Igreja?</h2>
            <p class="lead mb-5 opacity-75 mx-auto" style="max-width: 600px;">Entre em contato agora mesmo, tire suas dúvidas e leve seu ministério para o próximo nível de organização.</p>
            <a href="https://wa.me/5592994791168?text=Ol%C3%A1!%20Tenho%20interesse%20em%20implantar%20o%20sistema%20na%20minha%20igreja." target="_blank" class="btn btn-light btn-lg rounded-pill fw-bold text-primary px-5 shadow">
                <i class="fab fa-whatsapp text-success me-2"></i> Falar com o Desenvolvedor
            </a>
            <div class="mt-4">
                <p class="mb-0 fw-bold fs-4"><i class="fas fa-phone-alt me-2"></i> (92) 99479-1168</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer text-center">
        <div class="container">
            <div class="mb-4">
                <a href="/" class="text-decoration-none text-white fs-4 fw-bold"><i class="fas fa-church text-primary"></i> ChurchSys</a>
            </div>
            <p class="mb-0">© <?= date('Y') ?> - Todos os direitos reservados.</p>
            <p class="small opacity-50 mt-2">Feito com dedicação para o Reino de Deus.</p>
            <div class="mt-3">
                <a href="/admin/login" class="text-white opacity-50 text-decoration-none small"><i class="fas fa-lock me-1"></i> Acesso Restrito</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>