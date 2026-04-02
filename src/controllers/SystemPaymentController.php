<?php
// src/controllers/SystemPaymentController.php

class SystemPaymentController {
    private function tableHasColumn(PDO $db, $table, $column) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $stmt = $db->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
            $stmt->execute([$column]);
            return (bool)$stmt->fetch();
        }

        $stmt = $db->query("PRAGMA table_info($table)");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            $name = $col['name'] ?? ($col['Field'] ?? null);
            if ($name && strtolower($name) === strtolower($column)) {
                return true;
            }
        }
        return false;
    }

    private function normalizePaymentRow(array $payment, $hasDueDateColumn, $today) {
        $referenceMonth = $payment['reference_month'] ?? date('Y-m');
        $fallbackDueDate = $referenceMonth . '-05 00:00:00';
        $rawStatus = strtolower((string)($payment['status'] ?? 'pending'));
        $isPaid = $rawStatus === 'paid';

        if ($hasDueDateColumn) {
            $effectiveDueDate = !empty($payment['due_date']) ? $payment['due_date'] : $fallbackDueDate;
        } else {
            $effectiveDueDate = $isPaid
                ? $fallbackDueDate
                : (!empty($payment['payment_date']) ? $payment['payment_date'] : $fallbackDueDate);
        }

        $displayStatus = $rawStatus;
        if (!$isPaid) {
            $daysRemaining = (int)floor((strtotime(date('Y-m-d', strtotime($effectiveDueDate))) - strtotime($today)) / 86400);
            if ($daysRemaining < 0) {
                $displayStatus = 'overdue';
            } elseif ($daysRemaining === 0) {
                $displayStatus = 'today';
            } elseif ($daysRemaining <= 2) {
                $displayStatus = 'alert';
            } else {
                $displayStatus = 'pending';
            }
            $payment['days_remaining'] = $daysRemaining;
        } else {
            $payment['days_remaining'] = null;
        }

        $payment['due_date_effective'] = $effectiveDueDate;
        $payment['due_date_display'] = date('d/m/Y', strtotime($effectiveDueDate));
        $payment['history_due_date_display'] = !empty($effectiveDueDate)
            ? date('d/m/Y', strtotime($effectiveDueDate))
            : ('05/' . date('m/Y', strtotime($referenceMonth . '-01')));
        $payment['is_paid'] = $isPaid;
        $payment['paid_at_display'] = $isPaid && !empty($payment['payment_date'])
            ? date('d/m/Y H:i', strtotime($payment['payment_date']))
            : '-';
        $payment['history_payment_date_display'] = !empty($payment['payment_date'])
            ? date('d/m/Y H:i', strtotime($payment['payment_date']))
            : '-';
        $payment['display_status'] = $displayStatus;

        return $payment;
    }
    
    public function index() {
        requirePermission('system_payments.view');
        $db = (new Database())->connect();
        $hasDueDateColumn = $this->tableHasColumn($db, 'system_payments', 'due_date');
        
        if ($hasDueDateColumn) {
            $payments = $db->query("SELECT * FROM system_payments ORDER BY reference_month DESC")->fetchAll();
        } else {
            $payments = $db->query("SELECT * FROM system_payments ORDER BY reference_month DESC")->fetchAll();
        }
        
        $currentMonth = date('Y-m');
        $today = date('Y-m-d');
        
        $currentPayment = null;
        $latestPaidPayment = null;
        $nextPendingPayment = null;
        foreach ($payments as &$p) {
            $p = $this->normalizePaymentRow($p, $hasDueDateColumn, $today);
            if ($p['reference_month'] === $currentMonth) {
                $currentPayment = $p;
            }
            if (($p['status'] ?? '') === 'paid') {
                if ($latestPaidPayment === null) {
                    $latestPaidPayment = $p;
                } else {
                    $currentPaidAt = !empty($p['payment_date']) ? strtotime($p['payment_date']) : strtotime($p['reference_month'] . '-01');
                    $latestPaidAt = !empty($latestPaidPayment['payment_date']) ? strtotime($latestPaidPayment['payment_date']) : strtotime($latestPaidPayment['reference_month'] . '-01');
                    if ($currentPaidAt > $latestPaidAt) {
                        $latestPaidPayment = $p;
                    }
                }
            } else {
                if ($nextPendingPayment === null || strtotime($p['due_date_effective']) < strtotime($nextPendingPayment['due_date_effective'])) {
                    $nextPendingPayment = $p;
                }
            }
        }
        unset($p);
        
        $isCurrentMonthPaid = false;
        if ($currentPayment && ($currentPayment['status'] ?? '') === 'paid') {
            $isCurrentMonthPaid = true;
        }

        $status = 'pending'; 
        $dueDay = 5;
        $dueDateDisplay = '05/' . date('m/Y');
        $daysRemaining = null;

        if ($nextPendingPayment && !empty($nextPendingPayment['due_date_effective'])) {
            $dueDay = (int)date('d', strtotime($nextPendingPayment['due_date_effective']));
            $dueDateDisplay = date('d/m/Y', strtotime($nextPendingPayment['due_date_effective']));
            $daysRemaining = $nextPendingPayment['days_remaining'] ?? null;
        } elseif ($latestPaidPayment && !empty($latestPaidPayment['due_date_effective'])) {
            $dueDay = (int)date('d', strtotime($latestPaidPayment['due_date_effective']));
            $dueDateDisplay = date('d/m/Y', strtotime($latestPaidPayment['due_date_effective']));
        } elseif ($currentPayment && !empty($currentPayment['due_date_effective'])) {
            $dueDay = (int)date('d', strtotime($currentPayment['due_date_effective']));
            $dueDateDisplay = date('d/m/Y', strtotime($currentPayment['due_date_effective']));
        }

        if ($nextPendingPayment) {
            $status = $nextPendingPayment['display_status'] ?? 'pending';
        } elseif ($isCurrentMonthPaid || $latestPaidPayment) {
            $status = 'paid';
        } elseif (!$currentPayment) {
            $status = 'no_charge';
        }
        
        $billToPay = $nextPendingPayment;
        
        // Generate Pix Payload
        $pixKey = '85258598268';
        $pixName = 'EMERSON PINHEIRO DE SOUZA';
        $pixCity = 'SAO PAULO';
        $pixAmount = $billToPay['amount'] ?? 59.99;
        $pixTxid = '***';
        
        $pixPayload = generatePixPayload($pixKey, $pixName, $pixCity, $pixAmount, $pixTxid);
        
        view('admin/system_payments/index', [
            'payments' => $payments,
            'status' => $status,
            'daysRemaining' => $daysRemaining,
            'currentMonth' => $currentMonth,
            'isCurrentMonthPaid' => $isCurrentMonthPaid,
            'currentPayment' => $currentPayment,
            'latestPaidPayment' => $latestPaidPayment,
            'nextPendingPayment' => $nextPendingPayment,
            'dueDateDisplay' => $dueDateDisplay,
            'pixPayload' => $pixPayload,
            'billToPay' => $billToPay,
            'dueDay' => $dueDay
        ]);
    }
    
    public function pay() {
        requirePermission('system_payments.manage');
        
        // Only developer can mark as paid (confirm payment)
        // Or if the logic is: Developer generates charge (pending), Admin pays (maybe upload receipt or just manual confirm?)
        // The user requirement: "Somente ele [Emerson] poderá gerar cobranças".
        // It implies the control is on Emerson's side.
        // Let's assume Emerson marks as paid when he receives the money.
        // So this action should be restricted to developer OR 
        // if we want to allow admin to "self-declare" payment, we leave it open.
        // Given "Somente ele poderá gerar cobranças", I'll restrict "generating" (creating the record).
        // If "pay" creates the record, then restrict it.
        
        if (!hasPermission('system_payments.manage')) {
             redirect('/admin/system-payments');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $month = $_POST['month']; // YYYY-MM
            
            // Basic validation
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                redirect('/admin/system-payments');
            }
            
            $db = (new Database())->connect();
            $hasDueDateColumn = $this->tableHasColumn($db, 'system_payments', 'due_date');
            
            // Check if already exists (paid or pending)
            $stmt = $db->prepare("SELECT id, status, amount FROM system_payments WHERE reference_month = ?");
            $stmt->execute([$month]);
            $existingPayment = $stmt->fetch();
            
            $now = date('Y-m-d H:i:s');
            $dueDate = $month . '-05 00:00:00';
            
            if ($existingPayment) {
                if ($existingPayment['status'] === 'paid') {
                     // redirect('/admin/system-payments?error=already_paid');
                     // DEBUG: Allow execution even if paid to force expense check
                } else {
                    // Update pending to paid
                    if ($hasDueDateColumn) {
                        $stmt = $db->prepare("UPDATE system_payments SET status = 'paid', payment_date = ? WHERE id = ?");
                        $stmt->execute([$now, $existingPayment['id']]);
                    } else {
                        $stmt = $db->prepare("UPDATE system_payments SET status = 'paid', payment_date = ? WHERE id = ?");
                        $stmt->execute([$now, $existingPayment['id']]);
                    }
                }
            } else {
                // Insert New Payment Record
                if ($hasDueDateColumn) {
                    $stmt = $db->prepare("INSERT INTO system_payments (reference_month, status, amount, due_date, payment_date) VALUES (?, 'paid', 59.99, ?, ?)");
                    $stmt->execute([$month, $dueDate, $now]);
                } else {
                    $stmt = $db->prepare("INSERT INTO system_payments (reference_month, status, amount, payment_date) VALUES (?, 'paid', 59.99, ?)");
                    $stmt->execute([$month, $now]);
                }
            }

            // AUTOMATIC EXPENSE RECORDING FOR HEADQUARTERS
            // Use helper function for consistency, passing the current payment amount
            $currentPaymentAmount = $existingPayment['amount'] ?? 59.99;
            
            // If we just inserted a new record (else block above), use the default 59.99 or whatever logic
            if (!$existingPayment) {
                $currentPaymentAmount = 59.99;
            } else {
                // If existing payment, fetch fresh amount just in case it was updated
                $stmtAmt = $db->prepare("SELECT amount FROM system_payments WHERE id = ?");
                $stmtAmt->execute([$existingPayment['id']]);
                $currentPaymentAmount = $stmtAmt->fetchColumn();
            }

            if (!registerSystemPaymentExpense($month, $currentPaymentAmount)) {
                // If it fails, log is already written by helper.
            }

            // Auto-generate next month charge as pending
            $nextMonthDate = DateTime::createFromFormat('!Y-m-d', $month . '-01');
            $nextMonthDate->modify('+1 month');
            $nextMonth = $nextMonthDate->format('Y-m');
            
            // Check if next month already exists
            $stmt = $db->prepare("SELECT COUNT(*) FROM system_payments WHERE reference_month = ?");
            $stmt->execute([$nextMonth]);
            if ($stmt->fetchColumn() == 0) {
                $nextDueDate = $nextMonth . '-05 00:00:00';
                if ($hasDueDateColumn) {
                    $stmt = $db->prepare("INSERT INTO system_payments (reference_month, status, amount, due_date, payment_date) VALUES (?, 'pending', 59.99, ?, NULL)");
                    $stmt->execute([$nextMonth, $nextDueDate]);
                } else {
                    $stmt = $db->prepare("INSERT INTO system_payments (reference_month, status, amount, payment_date) VALUES (?, 'pending', 59.99, ?)");
                    $stmt->execute([$nextMonth, $nextDueDate]);
                }
            }
            
            redirect('/admin/system-payments?success=1');
        }
    }
}
