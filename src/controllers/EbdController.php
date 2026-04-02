<?php
// src/controllers/EbdController.php

class EbdController {
    
    // Lista de Classes
    public function index() {
        requirePermission('ebd.view');
        $db = (new Database())->connect();
        
        $congregation_id = $_SESSION['user_congregation_id'] ?? null;
        
        $sql = "SELECT c.*, cong.name as congregation_name,
                (SELECT COUNT(*) FROM ebd_students s WHERE s.class_id = c.id AND s.status = 'active') as students_count,
                (SELECT GROUP_CONCAT(m.name SEPARATOR ', ') FROM ebd_teachers t JOIN members m ON t.member_id = m.id WHERE t.class_id = c.id AND t.status = 'active') as teachers_names
                FROM ebd_classes c 
                LEFT JOIN congregations cong ON c.congregation_id = cong.id
                WHERE 1=1";
        
        $params = [];
        if ($congregation_id) {
            $sql .= " AND c.congregation_id = ?";
            $params[] = $congregation_id;
        }
        
        $sql .= " ORDER BY c.name ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $classes = $stmt->fetchAll();
        
        view('admin/ebd/classes/index', ['classes' => $classes]);
    }

    // Relatórios da EBD
    public function reports() {
        requirePermission('ebd.view');
        $db = (new Database())->connect();
        
        $cong_id = $_SESSION['user_congregation_id'] ?? null;
        $whereCong = "";
        $params = [];
        
        if ($cong_id) {
            $whereCong = "AND c.congregation_id = ?";
            $params[] = $cong_id;
        }

        // Filtros de Data
        $start_date = $_GET['start_date'] ?? date('Y-m-01'); // Início do mês atual
        $end_date = $_GET['end_date'] ?? date('Y-m-t'); // Fim do mês atual
        
        // Se quiser ver apenas um dia específico
        if (isset($_GET['date'])) {
            $start_date = $_GET['date'];
            $end_date = $_GET['date'];
        }

        // 1. Resumo do Período (Soma de tudo no intervalo selecionado)
        $sqlPeriod = "
            SELECT 
                COUNT(DISTINCT l.id) as lessons_count,
                SUM(l.visitors_count) as total_visitors,
                SUM(l.bibles_count) as total_bibles,
                SUM(l.magazines_count) as total_magazines,
                SUM(l.offerings) as total_offerings,
                (SELECT COUNT(*) FROM ebd_attendance a JOIN ebd_lessons l2 ON a.lesson_id = l2.id JOIN ebd_classes c ON l2.class_id = c.id WHERE l2.lesson_date BETWEEN ? AND ? AND a.present = 1 $whereCong) as total_attendance
            FROM ebd_lessons l
            JOIN ebd_classes c ON l.class_id = c.id
            WHERE l.lesson_date BETWEEN ? AND ? $whereCong
        ";
        
        $paramsPeriod = array_merge([$start_date, $end_date], $params, [$start_date, $end_date], $params);
        // Ajuste: array_merge com params duplicados para subquery e main query
        // Correção na ordem dos parametros:
        // Subquery: start, end, [cong]
        // Main: start, end, [cong]
        
        $paramsCorrected = [$start_date, $end_date];
        if($cong_id) $paramsCorrected[] = $cong_id;
        $paramsCorrected[] = $start_date;
        $paramsCorrected[] = $end_date;
        if($cong_id) $paramsCorrected[] = $cong_id;

        $stmtP = $db->prepare($sqlPeriod);
        $stmtP->execute($paramsCorrected);
        $period_stats = $stmtP->fetch();

        // 2. Detalhado por Dia (Agrupado por Data)
        $sqlDaily = "
            SELECT 
                l.lesson_date,
                COUNT(DISTINCT l.class_id) as classes_count,
                SUM(l.visitors_count) as total_visitors,
                SUM(l.bibles_count) as total_bibles,
                SUM(l.magazines_count) as total_magazines,
                SUM(l.offerings) as total_offerings,
                (SELECT COUNT(*) FROM ebd_attendance a JOIN ebd_lessons l2 ON a.lesson_id = l2.id JOIN ebd_classes c ON l2.class_id = c.id WHERE l2.lesson_date = l.lesson_date AND a.present = 1 $whereCong) as total_attendance
            FROM ebd_lessons l
            JOIN ebd_classes c ON l.class_id = c.id
            WHERE l.lesson_date BETWEEN ? AND ? $whereCong
            GROUP BY l.lesson_date
            ORDER BY l.lesson_date DESC
        ";
        
        // Params Order:
        // 1. Subquery $whereCong (if exists)
        // 2. Main Query BETWEEN start AND end
        // 3. Main Query $whereCong (if exists)
        
        $paramsDaily = [];
        if($cong_id) $paramsDaily[] = $cong_id; // Subquery
        $paramsDaily[] = $start_date;
        $paramsDaily[] = $end_date;
        if($cong_id) $paramsDaily[] = $cong_id; // Main query
        
        $stmtD = $db->prepare($sqlDaily);
        $stmtD->execute($paramsDaily);
        $daily_stats = $stmtD->fetchAll();

        // 3. Detalhado por Classe (No Período)
        $sqlClasses = "
            SELECT 
                c.name,
                COUNT(l.id) as lessons_given,
                SUM(l.visitors_count) as total_visitors,
                SUM(l.bibles_count) as total_bibles,
                SUM(l.magazines_count) as total_magazines,
                SUM(l.offerings) as total_offerings,
                (SELECT COUNT(*) FROM ebd_attendance a JOIN ebd_lessons l2 ON a.lesson_id = l2.id WHERE l2.class_id = c.id AND l2.lesson_date BETWEEN ? AND ? AND a.present = 1) as total_presence,
                (SELECT COUNT(*) FROM ebd_students s WHERE s.class_id = c.id AND s.status = 'active') as current_students
            FROM ebd_classes c
            LEFT JOIN ebd_lessons l ON c.id = l.class_id AND l.lesson_date BETWEEN ? AND ?
            WHERE 1=1 $whereCong
            GROUP BY c.id
            ORDER BY c.name ASC
        ";
        
        $paramsClasses = [$start_date, $end_date, $start_date, $end_date];
        if($cong_id) $paramsClasses[] = $cong_id;
        
        $stmtC = $db->prepare($sqlClasses);
        $stmtC->execute($paramsClasses);
        $classes_stats = $stmtC->fetchAll();
        
        // Calcular Faltas e Estatísticas
        foreach ($classes_stats as &$cls) {
            // Total Possível = Alunos * Aulas
            $total_possible = $cls['current_students'] * $cls['lessons_given'];
            
            // Faltas = Possível - Presença Real
            // Note: Se alunos saíram da classe no período, o cálculo é aproximado
            $absences = $total_possible - $cls['total_presence'];
            $cls['total_absences'] = max(0, $absences);
            
            $cls['avg_attendance'] = ($cls['lessons_given'] > 0) ? round($cls['total_presence'] / $cls['lessons_given'], 1) : 0;
            $cls['attendance_rate'] = ($total_possible > 0) ? round(($cls['total_presence'] / $total_possible) * 100, 1) : 0;
        }

        view('admin/ebd/reports/index', [
            'period_stats' => $period_stats,
            'daily_stats' => $daily_stats,
            'classes_stats' => $classes_stats,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
    }

    // Criar Classe
    public function createClass() {
        requirePermission('ebd.manage');
        $db = (new Database())->connect();
        
        $sql = "SELECT * FROM congregations";
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql .= " WHERE id = " . $_SESSION['user_congregation_id'];
        }
        $sql .= " ORDER BY name ASC";
        
        $congregations = $db->query($sql)->fetchAll();
        view('admin/ebd/classes/create', ['congregations' => $congregations]);
    }

    public function storeClass() {
        requirePermission('ebd.manage');
        $name = $_POST['name'];
        $description = $_POST['description'];
        $min_age = $_POST['min_age'] ?: null;
        $max_age = $_POST['max_age'] ?: null;
        $congregation_id = $_POST['congregation_id'] ?: null;

        $db = (new Database())->connect();
        $stmt = $db->prepare("INSERT INTO ebd_classes (name, description, min_age, max_age, congregation_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $min_age, $max_age, $congregation_id]);

        redirect('/admin/ebd/classes');
    }

    // Editar Classe
    public function editClass($id) {
        requirePermission('ebd.manage');
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT * FROM ebd_classes WHERE id = ?");
        $stmt->execute([$id]);
        $class = $stmt->fetch();
        
        if (!$class) redirect('/admin/ebd/classes');
        
        $sql = "SELECT * FROM congregations";
        if (!empty($_SESSION['user_congregation_id'])) {
            $sql .= " WHERE id = " . $_SESSION['user_congregation_id'];
        }
        $sql .= " ORDER BY name ASC";
        
        $congregations = $db->query($sql)->fetchAll();
        
        view('admin/ebd/classes/edit', ['class' => $class, 'congregations' => $congregations]);
    }

    public function updateClass($id) {
        requirePermission('ebd.manage');
        $name = $_POST['name'];
        $description = $_POST['description'];
        $min_age = $_POST['min_age'] ?: null;
        $max_age = $_POST['max_age'] ?: null;
        $congregation_id = $_POST['congregation_id'] ?: null;
        $status = $_POST['status'] ?? 'active';

        $db = (new Database())->connect();
        $stmt = $db->prepare("UPDATE ebd_classes SET name=?, description=?, min_age=?, max_age=?, congregation_id=?, status=? WHERE id=?");
        $stmt->execute([$name, $description, $min_age, $max_age, $congregation_id, $status, $id]);

        redirect("/admin/ebd/classes/show/$id");
    }

    // Excluir Classe
    public function deleteClass($id) {
        requirePermission('ebd.manage');
        $db = (new Database())->connect();
        
        // Verificar se existem lições (histórico deve ser preservado ou impedido de apagar)
        $checkLessons = $db->prepare("SELECT COUNT(*) FROM ebd_lessons WHERE class_id = ?");
        $checkLessons->execute([$id]);
        if ($checkLessons->fetchColumn() > 0) {
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Não é possível excluir',
                        text: 'Existem aulas registradas no histórico desta classe. Apague as aulas primeiro se realmente deseja excluir a classe.',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href='/admin/ebd/classes';
                    });
                });
            </script>";
            return;
        }
        
        // Liberar alunos (remover da tabela de alunos)
        $delStudents = $db->prepare("DELETE FROM ebd_students WHERE class_id = ?");
        $delStudents->execute([$id]);

        // Excluir professores vinculados
        $delTeachers = $db->prepare("DELETE FROM ebd_teachers WHERE class_id = ?");
        $delTeachers->execute([$id]);
        
        // Excluir classe
        $stmt = $db->prepare("DELETE FROM ebd_classes WHERE id = ?");
        $stmt->execute([$id]);
        
        redirect('/admin/ebd/classes');
    }

    // Detalhes da Classe (Alunos, Professores, Aulas)
    public function showClass($id) {
        requirePermission('ebd.view');
        $db = (new Database())->connect();
        
        // Classe Info
        $stmt = $db->prepare("SELECT c.*, cong.name as congregation_name FROM ebd_classes c LEFT JOIN congregations cong ON c.congregation_id = cong.id WHERE c.id = ?");
        $stmt->execute([$id]);
        $class = $stmt->fetch();
        
        if (!$class) redirect('/admin/ebd/classes');

        // Professores
        $stmtT = $db->prepare("SELECT t.*, m.name as member_name FROM ebd_teachers t JOIN members m ON t.member_id = m.id WHERE t.class_id = ? AND t.status = 'active'");
        $stmtT->execute([$id]);
        $teachers = $stmtT->fetchAll();

        // Alunos
        $stmtS = $db->prepare("SELECT s.*, m.name as member_name, m.birth_date, 
                               (SELECT COUNT(*) FROM ebd_teachers t WHERE t.member_id = s.member_id AND t.class_id = s.class_id AND t.status = 'active') as is_teacher
                               FROM ebd_students s 
                               JOIN members m ON s.member_id = m.id 
                               WHERE s.class_id = ? AND s.status = 'active' 
                               ORDER BY m.name ASC");
        $stmtS->execute([$id]);
        $students = $stmtS->fetchAll();

        // Últimas Aulas
        $stmtL = $db->prepare("SELECT * FROM ebd_lessons WHERE class_id = ? ORDER BY lesson_date DESC LIMIT 5");
        $stmtL->execute([$id]);
        $lessons = $stmtL->fetchAll();

        // Buscar todos os membros ativos para o select de matrícula (Alunos)
        // Filtrar pela congregação da classe, se houver
        // E EXCLUIR membros já matriculados em QUALQUER classe ativa
        $sqlM = "SELECT id, name FROM members 
                 WHERE status IN ('active', 'Congregando', 'Membro')
                 AND id NOT IN (SELECT member_id FROM ebd_students WHERE status = 'active')";
        $paramsM = [];
        
        if (!empty($class['congregation_id'])) {
            $sqlM .= " AND congregation_id = ?";
            $paramsM[] = $class['congregation_id'];
        }
        $sqlM .= " ORDER BY name ASC";
        
        $stmtM = $db->prepare($sqlM);
        $stmtM->execute($paramsM);
        $all_members = $stmtM->fetchAll();

        // Buscar apenas membros marcados como Professor de EBD
        // Filtrar pela congregação da classe também
        // E EXCLUIR professores já alocados em QUALQUER classe ativa
        $sqlP = "SELECT id, name FROM members 
                 WHERE status IN ('active', 'Congregando', 'Membro') 
                 AND is_ebd_teacher = 1
                 AND id NOT IN (SELECT member_id FROM ebd_teachers WHERE status = 'active')";
        $paramsP = [];
        
        if (!empty($class['congregation_id'])) {
            $sqlP .= " AND congregation_id = ?";
            $paramsP[] = $class['congregation_id'];
        }
        $sqlP .= " ORDER BY name ASC";
        
        $stmtP = $db->prepare($sqlP);
        $stmtP->execute($paramsP);
        $ebd_teachers_list = $stmtP->fetchAll();

        view('admin/ebd/classes/show', [
            'class' => $class,
            'teachers' => $teachers,
            'students' => $students,
            'lessons' => $lessons,
            'all_members' => $all_members,
            'ebd_teachers_list' => $ebd_teachers_list
        ]);
    }
    
    // Matricular Aluno
    public function enrollStudent($class_id) {
        requirePermission('ebd.manage');
        $member_id = $_POST['member_id'];
        
        $db = (new Database())->connect();
        
        // Check if already enrolled in ANY active class
        $check = $db->prepare("SELECT c.name FROM ebd_students s JOIN ebd_classes c ON s.class_id = c.id WHERE s.member_id = ? AND s.status = 'active'");
        $check->execute([$member_id]);
        $existing = $check->fetch();
        
        if ($existing) {
            // Already enrolled in another class (or even this one)
            // Redirect with error
            redirect("/admin/ebd/classes/show/$class_id?error=already_enrolled&other_class=" . urlencode($existing['name']));
            return;
        }
        
        $stmt = $db->prepare("INSERT INTO ebd_students (class_id, member_id, status, enrolled_at) VALUES (?, ?, 'active', NOW())");
        $stmt->execute([$class_id, $member_id]);
        
        redirect("/admin/ebd/classes/show/$class_id?success=enrolled");
    }
    
    // Remover Aluno
    public function removeStudent($id) {
        requirePermission('ebd.manage');
        $db = (new Database())->connect();
        
        // Get class_id before deleting
        $stmt = $db->prepare("SELECT class_id FROM ebd_students WHERE id = ?");
        $stmt->execute([$id]);
        $student = $stmt->fetch();
        
        if ($student) {
            $del = $db->prepare("DELETE FROM ebd_students WHERE id = ?");
            $del->execute([$id]);
            redirect("/admin/ebd/classes/show/" . $student['class_id']);
        } else {
            redirect("/admin/ebd/classes");
        }
    }
    
    // Adicionar Professor
    public function assignTeacher($class_id) {
        requirePermission('ebd.manage');
        $member_id = $_POST['member_id'];
        
        $db = (new Database())->connect();
        // Check if already assigned
        $check = $db->prepare("SELECT id FROM ebd_teachers WHERE class_id = ? AND member_id = ?");
        $check->execute([$class_id, $member_id]);
        
        if (!$check->fetch()) {
            $stmt = $db->prepare("INSERT INTO ebd_teachers (class_id, member_id, status) VALUES (?, ?, 'active')");
            $stmt->execute([$class_id, $member_id]);
            
            // Auto-enroll as student if not already enrolled in THIS class
            // (If enrolled in another class, we skip to avoid conflict with the strict rule, 
            // or the user should manually move them first)
            $checkStudent = $db->prepare("SELECT id FROM ebd_students WHERE class_id = ? AND member_id = ?");
            $checkStudent->execute([$class_id, $member_id]);
            
            if (!$checkStudent->fetch()) {
                 // Check if free (not in other active class)
                 $checkOther = $db->prepare("SELECT id FROM ebd_students WHERE member_id = ? AND status = 'active'");
                 $checkOther->execute([$member_id]);
                 
                 if (!$checkOther->fetch()) {
                     $stmtS = $db->prepare("INSERT INTO ebd_students (class_id, member_id, status, enrolled_at) VALUES (?, ?, 'active', NOW())");
                     $stmtS->execute([$class_id, $member_id]);
                 }
            }
        }
        
        redirect("/admin/ebd/classes/show/$class_id");
    }
    
    // Remover Professor
    public function removeTeacher($id) {
        requirePermission('ebd.manage');
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT class_id FROM ebd_teachers WHERE id = ?");
        $stmt->execute([$id]);
        $teacher = $stmt->fetch();
        
        if ($teacher) {
            $del = $db->prepare("DELETE FROM ebd_teachers WHERE id = ?");
            $del->execute([$id]);
            redirect("/admin/ebd/classes/show/" . $teacher['class_id']);
        } else {
            redirect("/admin/ebd/classes");
        }
    }

    // Detalhes da Aula
    public function showLesson($id) {
        requirePermission('ebd.view');
        $db = (new Database())->connect();
        
        // Dados da Aula
        $stmt = $db->prepare("SELECT l.*, c.name as class_name, c.id as class_id FROM ebd_lessons l JOIN ebd_classes c ON l.class_id = c.id WHERE l.id = ?");
        $stmt->execute([$id]);
        $lesson = $stmt->fetch();
        
        if (!$lesson) redirect('/admin/ebd/classes');
        
        // Lista de Presença
        $stmtA = $db->prepare("
            SELECT a.*, m.name as student_name,
            (SELECT COUNT(*) FROM ebd_teachers t WHERE t.member_id = s.member_id AND t.class_id = s.class_id AND t.status = 'active') as is_teacher
            FROM ebd_attendance a 
            JOIN ebd_students s ON a.student_id = s.id 
            JOIN members m ON s.member_id = m.id 
            WHERE a.lesson_id = ?
            ORDER BY m.name ASC
        ");
        $stmtA->execute([$id]);
        $attendance = $stmtA->fetchAll();
        
        view('admin/ebd/lessons/show', ['lesson' => $lesson, 'attendance' => $attendance]);
    }

    // Excluir Aula
    public function deleteLesson($id) {
        requirePermission('ebd.manage');
        $db = (new Database())->connect();
        
        // Buscar info da aula para redirect
        $stmt = $db->prepare("SELECT class_id, offerings FROM ebd_lessons WHERE id = ?");
        $stmt->execute([$id]);
        $lesson = $stmt->fetch();
        
        if ($lesson) {
            // Excluir Presença
            $delAtt = $db->prepare("DELETE FROM ebd_attendance WHERE lesson_id = ?");
            $delAtt->execute([$id]);
            
            // Excluir Aula
            $delLesson = $db->prepare("DELETE FROM ebd_lessons WHERE id = ?");
            $delLesson->execute([$id]);
            
            // Aviso sobre financeiro
            if ($lesson['offerings'] > 0) {
                // Aqui poderíamos tentar excluir do financeiro se tivéssemos o ID, mas vamos apenas avisar
                // Idealmente, usar Flash Messages (não implementado neste sistema simples, então vai sem aviso visual persistente)
            }
            
            redirect("/admin/ebd/classes/show/" . $lesson['class_id']);
        } else {
            redirect("/admin/ebd/classes");
        }
    }

    // Editar Aula (Formulário)
    public function editLesson($id) {
        requirePermission('ebd.manage');
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT * FROM ebd_lessons WHERE id = ?");
        $stmt->execute([$id]);
        $lesson = $stmt->fetch();
        
        if (!$lesson) redirect('/admin/ebd/classes');
        
        $class_id = $lesson['class_id'];
        
        $stmtC = $db->prepare("SELECT * FROM ebd_classes WHERE id = ?");
        $stmtC->execute([$class_id]);
        $class = $stmtC->fetch();
        
        // Buscar lista de presença (alunos presentes na aula + alunos ativos atuais)
        // 1. Alunos com registro na aula
        $sql1 = "SELECT s.id as student_record_id, m.name, a.present,
                 (SELECT COUNT(*) FROM ebd_teachers t WHERE t.member_id = s.member_id AND t.class_id = s.class_id AND t.status = 'active') as is_teacher
                 FROM ebd_attendance a 
                 JOIN ebd_students s ON a.student_id = s.id 
                 JOIN members m ON s.member_id = m.id 
                 WHERE a.lesson_id = ?";
                 
        // 2. Alunos ativos que não têm registro (novos ou esquecidos)
        $sql2 = "SELECT s.id as student_record_id, m.name, 0 as present,
                 (SELECT COUNT(*) FROM ebd_teachers t WHERE t.member_id = s.member_id AND t.class_id = s.class_id AND t.status = 'active') as is_teacher
                 FROM ebd_students s 
                 JOIN members m ON s.member_id = m.id 
                 WHERE s.class_id = ? AND s.status = 'active' 
                 AND s.id NOT IN (SELECT student_id FROM ebd_attendance WHERE lesson_id = ?)";
                 
        $sql = "$sql1 UNION $sql2 ORDER BY name ASC";
        
        $stmtS = $db->prepare($sql);
        $stmtS->execute([$id, $class_id, $id]);
        $students = $stmtS->fetchAll();
        
        view('admin/ebd/lessons/edit', ['lesson' => $lesson, 'class' => $class, 'students' => $students]);
    }

    public function updateLesson($id) {
        requirePermission('ebd.manage');
        $db = (new Database())->connect();
        
        $date = $_POST['date'];
        $topic = $_POST['topic'];
        $visitors = $_POST['visitors'] ?? 0;
        $bibles = $_POST['bibles'] ?? 0;
        $magazines = $_POST['magazines'] ?? 0;
        $offerings = $_POST['offerings'];
        
        if (empty($offerings)) {
            $offerings = 0;
        } elseif (is_string($offerings)) {
            if (strpos($offerings, ',') !== false) {
                $offerings = str_replace('.', '', $offerings); 
                $offerings = str_replace(',', '.', $offerings); 
            }
        }
        
        $notes = $_POST['notes'];
        $attendance = $_POST['attendance'] ?? [];
        
        try {
            $db->beginTransaction();
            
            // 1. Atualizar Aula
            $stmt = $db->prepare("UPDATE ebd_lessons SET lesson_date=?, topic=?, notes=?, visitors_count=?, bibles_count=?, magazines_count=?, offerings=? WHERE id=?");
            $stmt->execute([$date, $topic, $notes, $visitors, $bibles, $magazines, $offerings, $id]);
            
            // 2. Atualizar Chamada (Delete all + Insert new is easier, or Update existing)
            // Vou usar Delete + Insert para lidar com novos alunos
            $delAtt = $db->prepare("DELETE FROM ebd_attendance WHERE lesson_id = ?");
            $delAtt->execute([$id]);
            
            $stmtAtt = $db->prepare("INSERT INTO ebd_attendance (lesson_id, student_id, present) VALUES (?, ?, ?)");
            
            // Iterar sobre todos os alunos que apareceram no formulário (hidden inputs podem ser necessários para os ausentes)
            // Na verdade, o formulário de edit vai listar os alunos.
            // O array $attendance só traz os marcados (checkbox).
            // Preciso iterar sobre $_POST['students'] (todos os IDs listados) se eu quiser registrar "Ausente" explicitamente.
            // Mas minha lógica de 'attendance' é baseada em quem tem registro.
            
            // Melhor: O form deve enviar um array de todos os student_ids exibidos.
            $all_students_ids = $_POST['student_ids'] ?? [];
            
            foreach ($all_students_ids as $student_id) {
                $isPresent = isset($attendance[$student_id]) ? 1 : 0;
                $stmtAtt->execute([$id, $student_id, $isPresent]);
            }
            
            // Nota: Não atualizamos o Financeiro automaticamente na edição para evitar bagunça (estorno, etc).
            // O usuário deve corrigir no módulo Financeiro se o valor mudou.
            
            $db->commit();
            
            // Get class_id for redirect
            $stmtL = $db->prepare("SELECT class_id FROM ebd_lessons WHERE id = ?");
            $stmtL->execute([$id]);
            $lid = $stmtL->fetchColumn();
            
            redirect("/admin/ebd/classes/show/$lid");
            
        } catch (Exception $e) {
            $db->rollBack();
            die("Erro ao atualizar aula: " . $e->getMessage());
        }
    }

    // Registrar Aula (Chamada e Oferta)
    public function createLesson($class_id) {
        requirePermission('ebd.lessons');
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT * FROM ebd_classes WHERE id = ?");
        $stmt->execute([$class_id]);
        $class = $stmt->fetch();
        
        if (!$class) redirect('/admin/ebd/classes');
        
        // Buscar alunos ativos para a chamada
        $stmtS = $db->prepare("SELECT s.id as student_record_id, m.name, m.id as member_id,
                               (SELECT COUNT(*) FROM ebd_teachers t WHERE t.member_id = s.member_id AND t.class_id = s.class_id AND t.status = 'active') as is_teacher
                               FROM ebd_students s 
                               JOIN members m ON s.member_id = m.id 
                               WHERE s.class_id = ? AND s.status = 'active' 
                               ORDER BY m.name ASC");
        $stmtS->execute([$class_id]);
        $students = $stmtS->fetchAll();
        
        view('admin/ebd/lessons/create', ['class' => $class, 'students' => $students]);
    }
    
    public function storeLesson($class_id) {
        requirePermission('ebd.lessons');
        $db = (new Database())->connect();
        
        // Buscar dados da classe para log e financeiro
        $stmtC = $db->prepare("SELECT * FROM ebd_classes WHERE id = ?");
        $stmtC->execute([$class_id]);
        $class = $stmtC->fetch();
        
        if (!$class) die("Classe não encontrada.");
        
        $date = $_POST['date'];
        $topic = $_POST['topic'];
        $visitors = $_POST['visitors'] ?? 0;
        $bibles = $_POST['bibles'] ?? 0;
        $magazines = $_POST['magazines'] ?? 0;
        $offerings = $_POST['offerings'];
        
        // Fix decimal format for offerings (e.g. "50,00" -> "50.00")
        if (empty($offerings)) {
            $offerings = 0;
        } elseif (is_string($offerings)) {
            // Remove thousands separator (.) and replace decimal separator (,) with (.)
            // Only if comma exists, otherwise assume standard or integer
            if (strpos($offerings, ',') !== false) {
                $offerings = str_replace('.', '', $offerings); 
                $offerings = str_replace(',', '.', $offerings); 
            }
        }
        
        $notes = $_POST['notes'];
        $attendance = $_POST['attendance'] ?? []; // array of student_record_id => status (on/off)
        
        // Detalhes extras de presença (bíblia/revista por aluno, se quiser implementar detalhado futuramente)
        // Por enquanto, vamos assumir contagem geral ou checkbox simples
        
        try {
            $db->beginTransaction();
            
            // 1. Criar Aula
            $stmt = $db->prepare("INSERT INTO ebd_lessons (class_id, lesson_date, topic, notes, visitors_count, bibles_count, magazines_count, offerings, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$class_id, $date, $topic, $notes, $visitors, $bibles, $magazines, $offerings, $_SESSION['user_id']]);
            $lesson_id = $db->lastInsertId();
            
            // 2. Salvar Chamada
            $stmtAtt = $db->prepare("INSERT INTO ebd_attendance (lesson_id, student_id, present) VALUES (?, ?, ?)");
            
            // Buscar todos os alunos da classe para saber quem faltou
            $stmtS = $db->prepare("SELECT s.id, s.member_id FROM ebd_students s WHERE s.class_id = ? AND s.status = 'active'");
            $stmtS->execute([$class_id]);
            $all_students = $stmtS->fetchAll();
            
            foreach ($all_students as $student) {
                // Check if present in POST data
                // O array attendance vem com chaves sendo o ID do registro de estudante (ebd_students.id)
                $isPresent = isset($attendance[$student['id']]) ? 1 : 0;
                
                // Se quisermos salvar detalhes por aluno (bíblia/revista), precisaríamos de inputs mais complexos
                // Por hora, simplificado: Presente/Ausente
                $stmtAtt->execute([$lesson_id, $student['member_id'], $isPresent]);
            }
            
            // 3. Integração Financeira: Registrar Oferta na tabela 'tithes'
            if ($offerings > 0) {
                // Montar descrição
                $finance_desc = "Oferta EBD - {$class['name']}";
                $finance_notes = "Aula: " . date('d/m/Y', strtotime($date)) . " - Tema: $topic";
                
                // Usar congregação da classe ou do usuário logado (se classe for global)
                $finance_cong_id = $class['congregation_id'] ?? ($_SESSION['user_congregation_id'] ?? null);
                
                $stmtFinance = $db->prepare("INSERT INTO tithes (type, amount, payment_date, payment_method, giver_name, notes, congregation_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtFinance->execute([
                    'Oferta', // Tipo
                    $offerings, // Valor
                    $date, // Data da aula
                    'Dinheiro', // Método (assumindo dinheiro por padrão na EBD)
                    $finance_desc, // Nome do Doador (usado como descrição da origem)
                    $finance_notes, // Observações
                    $finance_cong_id // Congregação
                ]);
            }
            
            $db->commit();
            redirect("/admin/ebd/classes/show/$class_id");
            
        } catch (Exception $e) {
            $db->rollBack();
            // Handle error
            die($e->getMessage());
        }
    }
}
