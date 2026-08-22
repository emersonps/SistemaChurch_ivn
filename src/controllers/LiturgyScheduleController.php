<?php
// src/controllers/LiturgyScheduleController.php

class LiturgyScheduleController {
    private function scopeCongregationWhere($alias = '') {
        $col = $alias !== '' ? "$alias.congregation_id" : 'congregation_id';
        if (($_SESSION['user_role'] ?? '') !== 'admin' && !empty($_SESSION['user_congregation_id'])) {
            return ["($col = ? OR $col IS NULL)", [$_SESSION['user_congregation_id']]];
        }
        return ['1=1', []];
    }

    private function getCongregations() {
        $db = (new Database())->connect();
        return $db->query('SELECT id, name, service_schedule FROM congregations ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    // ---------------- Modelos ----------------

    public function templates() {
        requirePermission('liturgy_schedules.view');
        $db = (new Database())->connect();

        $rows = $db->query("
            SELECT t.*, c.name AS congregation_name,
                (SELECT COUNT(*) FROM liturgy_schedules s WHERE s.template_id = t.id) AS schedules_count
            FROM liturgy_schedule_templates t
            LEFT JOIN congregations c ON c.id = t.congregation_id
            ORDER BY t.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        view('admin/liturgy_schedules/templates_index', ['templates' => $rows]);
    }

    public function templateCreate() {
        requirePermission('liturgy_schedules.manage');
        view('admin/liturgy_schedules/templates_form', [
            'template' => null,
            'roleCatalog' => getLiturgyScheduleRoleCatalog(),
            'activeRoles' => [],
            'congregations' => $this->getCongregations(),
        ]);
    }

    public function templateEdit($id) {
        requirePermission('liturgy_schedules.manage');
        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT * FROM liturgy_schedule_templates WHERE id = ?');
        $stmt->execute([$id]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$template) {
            redirect('/admin/liturgy-schedules/templates');
        }

        $rolesConfig = json_decode($template['roles_config'], true);
        if (!is_array($rolesConfig)) {
            $rolesConfig = [];
        }

        view('admin/liturgy_schedules/templates_form', [
            'template' => $template,
            'roleCatalog' => getLiturgyScheduleRoleCatalog(),
            'activeRoles' => $rolesConfig,
            'congregations' => $this->getCongregations(),
        ]);
    }

    private function buildRolesConfigFromRequest() {
        $catalog = getLiturgyScheduleRoleCatalog();
        $checked = is_array($_POST['roles'] ?? null) ? $_POST['roles'] : [];
        $labels = $_POST['role_label'] ?? [];

        $rolesConfig = [];
        foreach ($catalog as $key => $defaultLabel) {
            if (!in_array($key, $checked, true)) {
                continue;
            }
            $label = trim((string)($labels[$key] ?? $defaultLabel));
            $rolesConfig[] = ['key' => $key, 'label' => $label !== '' ? $label : $defaultLabel];
        }

        foreach (['custom_1', 'custom_2'] as $customKey) {
            $customLabel = trim((string)($_POST[$customKey . '_label'] ?? ''));
            if ($customLabel !== '') {
                $rolesConfig[] = ['key' => $customKey, 'label' => $customLabel];
            }
        }

        return $rolesConfig;
    }

    public function templateStore() {
        requirePermission('liturgy_schedules.manage');
        $db = (new Database())->connect();

        $name = trim((string)($_POST['name'] ?? ''));
        $congregationId = !empty($_POST['congregation_id']) ? (int)$_POST['congregation_id'] : null;
        $rolesConfig = $this->buildRolesConfigFromRequest();

        if ($name === '' || empty($rolesConfig)) {
            $_SESSION['error'] = 'Informe um nome e selecione ao menos um papel para o modelo.';
            redirect('/admin/liturgy-schedules/templates/create');
        }

        $stmt = $db->prepare('INSERT INTO liturgy_schedule_templates (name, congregation_id, roles_config) VALUES (?, ?, ?)');
        $stmt->execute([$name, $congregationId, json_encode($rolesConfig, JSON_UNESCAPED_UNICODE)]);

        $_SESSION['success'] = 'Modelo de escala criado com sucesso.';
        redirect('/admin/liturgy-schedules/templates');
    }

    public function templateUpdate($id) {
        requirePermission('liturgy_schedules.manage');
        $db = (new Database())->connect();

        $name = trim((string)($_POST['name'] ?? ''));
        $congregationId = !empty($_POST['congregation_id']) ? (int)$_POST['congregation_id'] : null;
        $rolesConfig = $this->buildRolesConfigFromRequest();

        if ($name === '' || empty($rolesConfig)) {
            $_SESSION['error'] = 'Informe um nome e selecione ao menos um papel para o modelo.';
            redirect('/admin/liturgy-schedules/templates/edit/' . (int)$id);
        }

        $stmt = $db->prepare('UPDATE liturgy_schedule_templates SET name = ?, congregation_id = ?, roles_config = ? WHERE id = ?');
        $stmt->execute([$name, $congregationId, json_encode($rolesConfig, JSON_UNESCAPED_UNICODE), $id]);

        $_SESSION['success'] = 'Modelo de escala atualizado com sucesso.';
        redirect('/admin/liturgy-schedules/templates');
    }

    public function templateDelete($id) {
        requirePermission('liturgy_schedules.manage');
        $db = (new Database())->connect();

        $stmt = $db->prepare('SELECT COUNT(*) FROM liturgy_schedules WHERE template_id = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Este modelo já tem escalas criadas e não pode ser excluído.';
            redirect('/admin/liturgy-schedules/templates');
        }

        $db->prepare('DELETE FROM liturgy_schedule_templates WHERE id = ?')->execute([$id]);
        $_SESSION['success'] = 'Modelo removido com sucesso.';
        redirect('/admin/liturgy-schedules/templates');
    }

    // ---------------- Escalas ----------------

    public function index() {
        requirePermission('liturgy_schedules.view');
        $db = (new Database())->connect();

        [$whereSql, $whereParams] = $this->scopeCongregationWhere('s');

        $stmt = $db->prepare("
            SELECT s.*, c.name AS congregation_name, t.name AS template_name,
                (SELECT COUNT(*) FROM liturgy_schedule_entries e WHERE e.schedule_id = s.id) AS entries_count,
                (SELECT MIN(service_date) FROM liturgy_schedule_entries e WHERE e.schedule_id = s.id AND service_date >= CURRENT_DATE) AS next_date
            FROM liturgy_schedules s
            LEFT JOIN congregations c ON c.id = s.congregation_id
            LEFT JOIN liturgy_schedule_templates t ON t.id = s.template_id
            WHERE $whereSql
            ORDER BY s.created_at DESC
        ");
        $stmt->execute($whereParams);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stats = [
            'total' => count($schedules),
            'monthly' => count(array_filter($schedules, fn($s) => $s['period_type'] === 'monthly')),
            'entries' => array_sum(array_column($schedules, 'entries_count')),
        ];

        view('admin/liturgy_schedules/index', ['schedules' => $schedules, 'stats' => $stats]);
    }

    public function create() {
        requirePermission('liturgy_schedules.manage');
        $db = (new Database())->connect();
        $templates = $db->query('SELECT * FROM liturgy_schedule_templates ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);

        if (empty($templates)) {
            $_SESSION['error'] = 'Crie um modelo de escala antes de montar uma escala.';
            redirect('/admin/liturgy-schedules/templates/create');
        }

        view('admin/liturgy_schedules/create', [
            'templates' => $templates,
            'congregations' => $this->getCongregations(),
        ]);
    }

    private function generateMonthlyEntries($congregationId, $referenceMonth) {
        if (empty($congregationId) || empty($referenceMonth)) {
            return [];
        }

        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT service_schedule FROM congregations WHERE id = ?');
        $stmt->execute([$congregationId]);
        $raw = $stmt->fetchColumn();
        $serviceSchedule = json_decode((string)$raw, true);
        if (!is_array($serviceSchedule) || empty($serviceSchedule)) {
            return [];
        }

        $weekdayMap = ['Domingo' => 0, 'Segunda' => 1, 'Terça' => 2, 'Quarta' => 3, 'Quinta' => 4, 'Sexta' => 5, 'Sábado' => 6];

        [$year, $month] = array_map('intval', explode('-', $referenceMonth));
        $daysInMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));

        $entries = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $weekday = (int)date('w', mktime(0, 0, 0, $month, $day, $year));

            foreach ($serviceSchedule as $item) {
                $itemDay = trim((string)($item['day'] ?? ''));
                $itemWeekday = $weekdayMap[$itemDay] ?? null;
                if ($itemWeekday === null || $itemWeekday !== $weekday) {
                    continue;
                }
                $entries[] = [
                    'service_date' => $date,
                    'service_time' => $item['start_time'] ?? null,
                    'service_label' => $item['name'] ?? null,
                ];
            }
        }

        usort($entries, fn($a, $b) => [$a['service_date'], $a['service_time']] <=> [$b['service_date'], $b['service_time']]);

        return $entries;
    }

    public function store() {
        requirePermission('liturgy_schedules.manage');
        $db = (new Database())->connect();

        $templateId = (int)($_POST['template_id'] ?? 0);
        $title = trim((string)($_POST['title'] ?? ''));
        $congregationId = !empty($_POST['congregation_id']) ? (int)$_POST['congregation_id'] : null;
        $periodType = in_array($_POST['period_type'] ?? '', ['daily', 'weekly', 'monthly'], true) ? $_POST['period_type'] : 'monthly';
        $referenceMonth = trim((string)($_POST['reference_month'] ?? ''));

        if ($templateId <= 0 || $title === '') {
            $_SESSION['error'] = 'Selecione um modelo e informe um título para a escala.';
            redirect('/admin/liturgy-schedules/create');
        }

        $stmt = $db->prepare('INSERT INTO liturgy_schedules (template_id, title, congregation_id, period_type, reference_month) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$templateId, $title, $congregationId, $periodType, $referenceMonth !== '' ? $referenceMonth : null]);
        $scheduleId = $db->lastInsertId();

        if ($periodType === 'monthly') {
            $entries = $this->generateMonthlyEntries($congregationId, $referenceMonth);
            if (!empty($entries)) {
                $insertEntry = $db->prepare('INSERT INTO liturgy_schedule_entries (schedule_id, service_date, service_time, service_label, values_json) VALUES (?, ?, ?, ?, ?)');
                foreach ($entries as $entry) {
                    $insertEntry->execute([$scheduleId, $entry['service_date'], $entry['service_time'], $entry['service_label'], json_encode(new stdClass())]);
                }
            }
        }

        $_SESSION['success'] = 'Escala criada com sucesso.';
        redirect('/admin/liturgy-schedules/edit/' . $scheduleId);
    }

    private function findScheduleScoped($id) {
        $db = (new Database())->connect();
        [$whereSql, $whereParams] = $this->scopeCongregationWhere('s');

        $stmt = $db->prepare("
            SELECT s.*, t.roles_config, t.name AS template_name, c.name AS congregation_name
            FROM liturgy_schedules s
            JOIN liturgy_schedule_templates t ON t.id = s.template_id
            LEFT JOIN congregations c ON c.id = s.congregation_id
            WHERE s.id = ? AND $whereSql
        ");
        $stmt->execute(array_merge([$id], $whereParams));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function edit($id) {
        requirePermission('liturgy_schedules.manage');
        $schedule = $this->findScheduleScoped($id);
        if (!$schedule) {
            redirect('/admin/liturgy-schedules');
        }

        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT * FROM liturgy_schedule_entries WHERE schedule_id = ? ORDER BY service_date ASC, service_time ASC');
        $stmt->execute([$id]);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rolesConfig = json_decode($schedule['roles_config'], true);
        if (!is_array($rolesConfig)) {
            $rolesConfig = [];
        }

        foreach ($entries as &$entry) {
            $values = json_decode($entry['values_json'], true);
            $entry['values'] = is_array($values) ? $values : [];
            $entry['weekday'] = getLiturgyScheduleWeekdayPt($entry['service_date']);
        }
        unset($entry);

        view('admin/liturgy_schedules/edit', [
            'schedule' => $schedule,
            'rolesConfig' => $rolesConfig,
            'entries' => $entries,
        ]);
    }

    public function update($id) {
        requirePermission('liturgy_schedules.manage');
        $schedule = $this->findScheduleScoped($id);
        if (!$schedule) {
            redirect('/admin/liturgy-schedules');
        }

        $db = (new Database())->connect();
        $notes = trim((string)($_POST['notes'] ?? ''));
        $db->prepare('UPDATE liturgy_schedules SET notes = ? WHERE id = ?')->execute([$notes !== '' ? $notes : null, $id]);

        $rolesConfig = json_decode($schedule['roles_config'], true);
        $roleKeys = array_column(is_array($rolesConfig) ? $rolesConfig : [], 'key');

        $db->prepare('DELETE FROM liturgy_schedule_entries WHERE schedule_id = ?')->execute([$id]);

        $rows = $_POST['rows'] ?? [];
        if (is_array($rows)) {
            $insert = $db->prepare('INSERT INTO liturgy_schedule_entries (schedule_id, service_date, service_time, service_label, values_json) VALUES (?, ?, ?, ?, ?)');
            foreach ($rows as $row) {
                $date = trim((string)($row['service_date'] ?? ''));
                if ($date === '') {
                    continue;
                }
                $time = trim((string)($row['service_time'] ?? '')) ?: null;
                $label = trim((string)($row['service_label'] ?? '')) ?: null;

                $values = [];
                foreach ($roleKeys as $key) {
                    $values[$key] = trim((string)($row['values'][$key] ?? ''));
                }
                $values['observacoes'] = trim((string)($row['values']['observacoes'] ?? ''));

                $insert->execute([$id, $date, $time, $label, json_encode($values, JSON_UNESCAPED_UNICODE)]);
            }
        }

        $_SESSION['success'] = 'Escala salva com sucesso.';
        redirect('/admin/liturgy-schedules/edit/' . $id);
    }

    public function delete($id) {
        requirePermission('liturgy_schedules.manage');
        $schedule = $this->findScheduleScoped($id);
        if (!$schedule) {
            redirect('/admin/liturgy-schedules');
        }

        $db = (new Database())->connect();
        $db->prepare('DELETE FROM liturgy_schedule_entries WHERE schedule_id = ?')->execute([$id]);
        $db->prepare('DELETE FROM liturgy_schedules WHERE id = ?')->execute([$id]);

        $_SESSION['success'] = 'Escala removida com sucesso.';
        redirect('/admin/liturgy-schedules');
    }

    public function print($id) {
        requirePermission('liturgy_schedules.view');
        $schedule = $this->findScheduleScoped($id);
        if (!$schedule) {
            redirect('/admin/liturgy-schedules');
        }

        $db = (new Database())->connect();
        $stmt = $db->prepare('SELECT * FROM liturgy_schedule_entries WHERE schedule_id = ? ORDER BY service_date ASC, service_time ASC');
        $stmt->execute([$id]);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rolesConfig = json_decode($schedule['roles_config'], true);
        if (!is_array($rolesConfig)) {
            $rolesConfig = [];
        }

        foreach ($entries as &$entry) {
            $values = json_decode($entry['values_json'], true);
            $entry['values'] = is_array($values) ? $values : [];
            $entry['weekday'] = getLiturgyScheduleWeekdayPt($entry['service_date']);
        }
        unset($entry);

        view('admin/liturgy_schedules/print', [
            'schedule' => $schedule,
            'rolesConfig' => $rolesConfig,
            'entries' => $entries,
        ]);
    }
}
