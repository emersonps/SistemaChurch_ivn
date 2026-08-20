<?php
// src/controllers/CampaignController.php
//
// Campanhas de arrecadação com meta. Um membro participante recebe um
// cronograma de parcelas mensais (campaign_installments); marcar uma
// parcela como paga também cria um lançamento em `tithes` (type=Oferta),
// então o valor entra nos relatórios/fechamentos financeiros normais.

class CampaignController {
    public function index() {
        requirePermission('campaigns.view');
        $db = (new Database())->connect();
        $campaigns = $db->query("SELECT * FROM campaigns ORDER BY status = 'active' DESC, created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

        $goalTotal = 0;
        $raisedTotal = 0;
        $activeCount = 0;
        foreach ($campaigns as &$campaign) {
            $campaign['progress'] = getCampaignProgress($campaign['id']);
            $goalTotal += $campaign['progress']['goal'];
            $raisedTotal += $campaign['progress']['raised'];
            if ($campaign['status'] === 'active') {
                $activeCount++;
            }
        }
        unset($campaign);

        view('admin/campaigns/index', [
            'campaigns' => $campaigns,
            'stats' => [
                'total' => count($campaigns),
                'active' => $activeCount,
                'goal_total' => $goalTotal,
                'raised_total' => $raisedTotal,
            ],
        ]);
    }

    public function create() {
        requirePermission('campaigns.manage');
        $db = (new Database())->connect();
        view('admin/campaigns/create', ['congregations' => $this->getCongregationsForSelect($db)]);
    }

    public function store() {
        requirePermission('campaigns.manage');
        verify_csrf();
        $db = (new Database())->connect();

        $data = $this->parseCampaignInput();
        if ($data === null) {
            redirect('/admin/campaigns/create');
        }

        $stmt = $db->prepare('INSERT INTO campaigns (title, description, goal_amount, commitment_type, congregation_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['title'], $data['description'], $data['goal_amount'], $data['commitment_type'], $data['congregation_id'], $data['start_date'], $data['end_date'], $data['status']]);

        $_SESSION['success'] = 'Campanha criada com sucesso.';
        redirect('/admin/campaigns');
    }

    public function edit($id) {
        requirePermission('campaigns.manage');
        $db = (new Database())->connect();
        $campaign = $this->findCampaign($db, $id);
        if (!$campaign) {
            redirect('/admin/campaigns');
        }
        view('admin/campaigns/edit', ['campaign' => $campaign, 'congregations' => $this->getCongregationsForSelect($db)]);
    }

    public function update($id) {
        requirePermission('campaigns.manage');
        verify_csrf();
        $db = (new Database())->connect();

        $data = $this->parseCampaignInput();
        if ($data === null) {
            redirect('/admin/campaigns/edit/' . $id);
        }

        $stmt = $db->prepare('UPDATE campaigns SET title = ?, description = ?, goal_amount = ?, commitment_type = ?, congregation_id = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?');
        $stmt->execute([$data['title'], $data['description'], $data['goal_amount'], $data['commitment_type'], $data['congregation_id'], $data['start_date'], $data['end_date'], $data['status'], $id]);

        $_SESSION['success'] = 'Campanha atualizada com sucesso.';
        redirect('/admin/campaigns');
    }

    public function delete($id) {
        requirePermission('campaigns.manage');
        verify_csrf();
        $db = (new Database())->connect();
        $db->prepare('DELETE FROM campaign_installments WHERE campaign_id = ?')->execute([$id]);
        $db->prepare('DELETE FROM campaign_participants WHERE campaign_id = ?')->execute([$id]);
        $db->prepare('DELETE FROM campaigns WHERE id = ?')->execute([$id]);
        $_SESSION['success'] = 'Campanha removida.';
        redirect('/admin/campaigns');
    }

    public function show($id) {
        requirePermission('campaigns.view');
        $db = (new Database())->connect();

        $campaign = $this->findCampaign($db, $id);
        if (!$campaign) {
            redirect('/admin/campaigns');
        }

        $participantsStmt = $db->prepare("
            SELECT cp.*, m.name AS member_name,
                COALESCE(SUM(ci.paid_amount), 0) AS total_paid,
                COUNT(ci.id) AS total_installments,
                SUM(CASE WHEN ci.status = 'paid' THEN 1 ELSE 0 END) AS paid_installments
            FROM campaign_participants cp
            JOIN members m ON cp.member_id = m.id
            LEFT JOIN campaign_installments ci ON ci.participant_id = cp.id
            WHERE cp.campaign_id = ? AND cp.status = 'active'
            GROUP BY cp.id, m.name
            ORDER BY m.name ASC
        ");
        $participantsStmt->execute([$id]);
        $participants = $participantsStmt->fetchAll(PDO::FETCH_ASSOC);

        $memberSql = "SELECT id, name FROM members WHERE id NOT IN (SELECT member_id FROM campaign_participants WHERE campaign_id = ? AND status = 'active')";
        $params = [$id];
        if (!empty($_SESSION['user_congregation_id'])) {
            $memberSql .= ' AND congregation_id = ?';
            $params[] = $_SESSION['user_congregation_id'];
        }
        $memberSql .= ' ORDER BY name ASC';
        $availableStmt = $db->prepare($memberSql);
        $availableStmt->execute($params);

        view('admin/campaigns/show', [
            'campaign' => $campaign,
            'progress' => getCampaignProgress($id),
            'participants' => $participants,
            'availableMembers' => $availableStmt->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    public function addParticipant($id) {
        requirePermission('campaigns.manage');
        verify_csrf();
        $db = (new Database())->connect();

        $campaign = $this->findCampaign($db, $id);
        if (!$campaign) {
            redirect('/admin/campaigns');
        }

        $memberId = (int)($_POST['member_id'] ?? 0);
        if ($memberId <= 0) {
            $_SESSION['error'] = 'Selecione um membro.';
            redirect('/admin/campaigns/' . $id);
        }

        $dupStmt = $db->prepare("SELECT id FROM campaign_participants WHERE campaign_id = ? AND member_id = ? AND status = 'active'");
        $dupStmt->execute([$id, $memberId]);
        if ($dupStmt->fetchColumn()) {
            $_SESSION['error'] = 'Esse membro já participa desta campanha.';
            redirect('/admin/campaigns/' . $id);
        }

        $monthlyAmount = null;
        $monthsCommitted = null;

        if ($campaign['commitment_type'] === 'fixed') {
            $monthlyAmount = (float)str_replace(',', '.', (string)($_POST['monthly_amount'] ?? '0'));
            $monthsCommitted = (int)($_POST['months_committed'] ?? 0);
            if ($monthlyAmount <= 0 || $monthsCommitted <= 0) {
                $_SESSION['error'] = 'Informe o valor mensal e a quantidade de meses.';
                redirect('/admin/campaigns/' . $id);
            }
        }

        $db->prepare('INSERT INTO campaign_participants (campaign_id, member_id, monthly_amount, months_committed) VALUES (?, ?, ?, ?)')
            ->execute([$id, $memberId, $monthlyAmount, $monthsCommitted]);
        $participantId = (int)$db->lastInsertId();

        if ($campaign['commitment_type'] === 'fixed') {
            // Starts from whichever comes later: the current month, or the
            // campaign's own start month (Y-m-d string comparison works
            // fine here since both sides are zero-padded ISO dates).
            $campaignStartMonth = date('Y-m-01', strtotime($campaign['start_date']));
            $thisMonth = date('Y-m-01');
            $cursor = new DateTime(max($campaignStartMonth, $thisMonth));

            $insertInstallment = $db->prepare('INSERT INTO campaign_installments (campaign_id, participant_id, member_id, reference_month, committed_amount) VALUES (?, ?, ?, ?, ?)');
            for ($i = 0; $i < $monthsCommitted; $i++) {
                $insertInstallment->execute([$id, $participantId, $memberId, $cursor->format('Y-m'), $monthlyAmount]);
                $cursor->modify('+1 month');
            }
        }

        $_SESSION['success'] = 'Participante adicionado com sucesso.';
        redirect('/admin/campaigns/' . $id);
    }

    public function removeParticipant($id) {
        requirePermission('campaigns.manage');
        verify_csrf();
        $db = (new Database())->connect();

        $stmt = $db->prepare('SELECT campaign_id FROM campaign_participants WHERE id = ?');
        $stmt->execute([$id]);
        $campaignId = $stmt->fetchColumn();
        if (!$campaignId) {
            redirect('/admin/campaigns');
        }

        $db->prepare("UPDATE campaign_participants SET status = 'withdrawn' WHERE id = ?")->execute([$id]);
        // Drop installments that were never paid; paid ones stay as history.
        $db->prepare("DELETE FROM campaign_installments WHERE participant_id = ? AND status = 'pending'")->execute([$id]);

        $_SESSION['success'] = 'Participante removido da campanha.';
        redirect('/admin/campaigns/' . $campaignId);
    }

    public function showParticipant($id) {
        requirePermission('campaigns.view');
        $db = (new Database())->connect();

        $stmt = $db->prepare('
            SELECT cp.*, m.name AS member_name, c.id AS campaign_id, c.title AS campaign_title, c.commitment_type
            FROM campaign_participants cp
            JOIN members m ON cp.member_id = m.id
            JOIN campaigns c ON cp.campaign_id = c.id
            WHERE cp.id = ?
        ');
        $stmt->execute([$id]);
        $participant = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$participant) {
            redirect('/admin/campaigns');
        }

        $installmentsStmt = $db->prepare('SELECT * FROM campaign_installments WHERE participant_id = ? ORDER BY reference_month ASC');
        $installmentsStmt->execute([$id]);

        view('admin/campaigns/participant', [
            'participant' => $participant,
            'installments' => $installmentsStmt->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    public function addInstallment($participantId) {
        requirePermission('campaigns.manage');
        verify_csrf();
        $db = (new Database())->connect();

        $stmt = $db->prepare('SELECT cp.*, c.commitment_type FROM campaign_participants cp JOIN campaigns c ON cp.campaign_id = c.id WHERE cp.id = ?');
        $stmt->execute([$participantId]);
        $participant = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$participant) {
            redirect('/admin/campaigns');
        }

        $redirectPath = '/admin/campaigns/participants/' . $participantId;

        if ($participant['commitment_type'] !== 'flexible') {
            $_SESSION['error'] = 'Essa campanha usa valor fixo; os meses já foram gerados automaticamente.';
            redirect($redirectPath);
        }

        $referenceMonth = trim((string)($_POST['reference_month'] ?? ''));
        $amount = (float)str_replace(',', '.', (string)($_POST['committed_amount'] ?? '0'));

        if (!preg_match('/^\d{4}-\d{2}$/', $referenceMonth) || $amount <= 0) {
            $_SESSION['error'] = 'Informe o mês e o valor corretamente.';
            redirect($redirectPath);
        }

        $dupStmt = $db->prepare('SELECT id FROM campaign_installments WHERE participant_id = ? AND reference_month = ?');
        $dupStmt->execute([$participantId, $referenceMonth]);
        if ($dupStmt->fetchColumn()) {
            $_SESSION['error'] = 'Já existe uma parcela cadastrada para esse mês.';
            redirect($redirectPath);
        }

        $db->prepare('INSERT INTO campaign_installments (campaign_id, participant_id, member_id, reference_month, committed_amount) VALUES (?, ?, ?, ?, ?)')
            ->execute([$participant['campaign_id'], $participantId, $participant['member_id'], $referenceMonth, $amount]);

        $_SESSION['success'] = 'Parcela adicionada.';
        redirect($redirectPath);
    }

    public function payInstallment($id) {
        requirePermission('campaigns.manage');
        verify_csrf();
        $db = (new Database())->connect();

        $stmt = $db->prepare('SELECT ci.*, c.title AS campaign_title, c.congregation_id FROM campaign_installments ci JOIN campaigns c ON ci.campaign_id = c.id WHERE ci.id = ?');
        $stmt->execute([$id]);
        $installment = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$installment) {
            redirect('/admin/campaigns');
        }

        $redirectPath = '/admin/campaigns/participants/' . $installment['participant_id'];

        $paidAmount = (float)str_replace(',', '.', (string)($_POST['paid_amount'] ?? $installment['committed_amount']));
        $paidDate = trim((string)($_POST['paid_date'] ?? date('Y-m-d')));
        if ($paidAmount <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $paidDate)) {
            $_SESSION['error'] = 'Informe um valor e uma data válidos.';
            redirect($redirectPath);
        }

        $congregationId = $installment['congregation_id'];
        if (empty($congregationId)) {
            $memberStmt = $db->prepare('SELECT congregation_id FROM members WHERE id = ?');
            $memberStmt->execute([$installment['member_id']]);
            $congregationId = $memberStmt->fetchColumn() ?: null;
        }

        $closureWarning = '';
        $closure = $this->getClosureForDate($db, $congregationId, $paidDate);
        if ($closure) {
            $closureWarning = " Aviso: o período financeiro de {$paidDate} já está fechado ({$closure['type']} {$closure['period']}) — o lançamento em Dízimos/Ofertas foi criado mesmo assim.";
        }

        $notes = 'Campanha: ' . $installment['campaign_title'] . ' (' . $installment['reference_month'] . ')';
        $hasAccountableField = $this->tableHasColumn($db, 'tithes', 'is_accountable');

        if ($hasAccountableField) {
            $titheStmt = $db->prepare("INSERT INTO tithes (member_id, amount, payment_date, payment_method, type, notes, congregation_id, is_accountable) VALUES (?, ?, ?, ?, 'Oferta', ?, ?, 1)");
        } else {
            $titheStmt = $db->prepare("INSERT INTO tithes (member_id, amount, payment_date, payment_method, type, notes, congregation_id) VALUES (?, ?, ?, ?, 'Oferta', ?, ?)");
        }
        $titheStmt->execute([$installment['member_id'], $paidAmount, $paidDate, 'Campanha', $notes, $congregationId]);
        $titheId = (int)$db->lastInsertId();

        $db->prepare("UPDATE campaign_installments SET paid_amount = ?, paid_date = ?, status = 'paid', tithe_id = ? WHERE id = ?")
            ->execute([$paidAmount, $paidDate, $titheId, $id]);

        $_SESSION['success'] = 'Pagamento registrado com sucesso.' . $closureWarning;
        redirect($redirectPath);
    }

    public function unpayInstallment($id) {
        requirePermission('campaigns.manage');
        verify_csrf();
        $db = (new Database())->connect();

        $stmt = $db->prepare('SELECT * FROM campaign_installments WHERE id = ?');
        $stmt->execute([$id]);
        $installment = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$installment) {
            redirect('/admin/campaigns');
        }

        $redirectPath = '/admin/campaigns/participants/' . $installment['participant_id'];

        if (!empty($installment['tithe_id'])) {
            $db->prepare('DELETE FROM tithes WHERE id = ?')->execute([$installment['tithe_id']]);
        }

        $db->prepare("UPDATE campaign_installments SET paid_amount = NULL, paid_date = NULL, status = 'pending', tithe_id = NULL WHERE id = ?")->execute([$id]);

        $_SESSION['success'] = 'Pagamento desfeito.';
        redirect($redirectPath);
    }

    private function findCampaign(PDO $db, $id) {
        $stmt = $db->prepare('SELECT * FROM campaigns WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function getCongregationsForSelect(PDO $db) {
        $sql = 'SELECT id, name FROM congregations';
        $params = [];
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql .= ' WHERE id = ?';
            $params[] = $_SESSION['user_congregation_id'];
        }
        $sql .= ' ORDER BY name ASC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function parseCampaignInput() {
        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $goalAmount = (float)str_replace(',', '.', (string)($_POST['goal_amount'] ?? '0'));
        $commitmentType = ($_POST['commitment_type'] ?? 'fixed') === 'flexible' ? 'flexible' : 'fixed';
        $congregationId = !empty($_POST['congregation_id']) ? (int)$_POST['congregation_id'] : null;
        $startDate = trim((string)($_POST['start_date'] ?? ''));
        $endDate = trim((string)($_POST['end_date'] ?? ''));
        $statusInput = (string)($_POST['status'] ?? 'active');
        $status = in_array($statusInput, ['active', 'completed', 'cancelled'], true) ? $statusInput : 'active';

        if ($title === '' || $goalAmount <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $_SESSION['error'] = 'Preencha o título, a meta (maior que zero) e a data de início.';
            return null;
        }
        if ($endDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = '';
        }

        return [
            'title' => $title,
            'description' => $description !== '' ? $description : null,
            'goal_amount' => $goalAmount,
            'commitment_type' => $commitmentType,
            'congregation_id' => $congregationId,
            'start_date' => $startDate,
            'end_date' => $endDate !== '' ? $endDate : null,
            'status' => $status,
        ];
    }

    private function tableHasColumn(PDO $db, $table, $column) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $stmt = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
            return (bool)$stmt->fetch();
        }
        $stmt = $db->query("PRAGMA table_info($table)");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            $name = $col['Field'] ?? ($col['name'] ?? null);
            if ($name && strtolower($name) === strtolower($column)) {
                return true;
            }
        }
        return false;
    }

    private function getClosureForDate(PDO $db, $congregationId, $date) {
        if (empty($date)) {
            return null;
        }
        if (empty($congregationId)) {
            $stmt = $db->prepare("SELECT id, type, period FROM financial_closures WHERE congregation_id IS NULL AND status = 'Fechado' AND ? BETWEEN start_date AND end_date ORDER BY end_date DESC LIMIT 1");
            $stmt->execute([$date]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        $stmt = $db->prepare("SELECT id, type, period FROM financial_closures WHERE congregation_id = ? AND status = 'Fechado' AND ? BETWEEN start_date AND end_date ORDER BY end_date DESC LIMIT 1");
        $stmt->execute([$congregationId, $date]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
