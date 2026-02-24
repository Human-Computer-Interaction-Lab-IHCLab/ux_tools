<?php

require_once __DIR__ . '/../auth.php';

class TeacherController
{
    public static function dashboard(): void
    {
        require_role('teacher');
        $groups = db()->query('SELECT * FROM groups ORDER BY name')->fetchAll();
        $templates = db()->query('SELECT * FROM activity_templates ORDER BY id DESC')->fetchAll();
        $instances = db()->query('SELECT ai.*, t.name AS team_name, at.title, at.type FROM activity_instances ai JOIN teams t ON t.id=ai.team_id JOIN activity_templates at ON at.id=ai.template_id ORDER BY ai.id DESC')->fetchAll();
        render('teacher/dashboard', compact('groups', 'templates', 'instances'));
    }

    public static function createGroup(): void
    {
        require_role('teacher'); verify_csrf();
        $name = trim((string)post('name', ''));
        if ($name !== '') {
            db()->prepare('INSERT INTO groups (name) VALUES (?)')->execute([$name]);
        }
        redirect('/teacher');
    }

    public static function createTeam(): void
    {
        require_role('teacher'); verify_csrf();
        db()->prepare('INSERT INTO teams (group_id, name) VALUES (?, ?)')->execute([(int)post('group_id'), trim((string)post('name'))]);
        redirect('/teacher');
    }

    public static function importStudents(): void
    {
        require_role('teacher'); verify_csrf();
        $teamId = (int)post('team_id');
        $rows = preg_split('/\r\n|\r|\n/', trim((string)post('csv_data', '')));
        foreach ($rows as $row) {
            if (!$row) continue;
            [$name, $email] = array_map('trim', explode(',', $row) + ['', '']);
            if (!validate_email($email)) continue;
            $token = random_token();
            db()->prepare('INSERT INTO users (role,name,email,activation_token,is_active) VALUES ("student",?,?,?,0)')->execute([$name, $email, $token]);
            $userId = (int)db()->lastInsertId();
            db()->prepare('INSERT IGNORE INTO team_members (team_id,user_id) VALUES (?,?)')->execute([$teamId, $userId]);
        }
        redirect('/teacher/students?team_id=' . $teamId);
    }

    public static function students(): void
    {
        require_role('teacher');
        $teamId = (int)get('team_id', 0);
        $teams = db()->query('SELECT t.*, g.name AS group_name FROM teams t JOIN groups g ON g.id=t.group_id ORDER BY g.name,t.name')->fetchAll();
        $students = [];
        if ($teamId) {
            $stmt = db()->prepare('SELECT u.* FROM users u JOIN team_members tm ON tm.user_id=u.id WHERE tm.team_id=? AND u.role="student" ORDER BY u.name');
            $stmt->execute([$teamId]);
            $students = $stmt->fetchAll();
        }
        render('teacher/students', compact('teams', 'students', 'teamId'));
    }

    public static function createTemplate(): void
    {
        require_role('teacher'); verify_csrf();
        db()->prepare('INSERT INTO activity_templates (type,title,instructions) VALUES (?,?,?)')->execute([post('type'), trim((string)post('title')), trim((string)post('instructions'))]);
        redirect('/teacher');
    }

    public static function assignTemplate(): void
    {
        require_role('teacher'); verify_csrf();
        $templateId = (int)post('template_id');
        $groupId = (int)post('group_id');

        db()->prepare('INSERT INTO activity_assignments (template_id,group_id) VALUES (?,?)')->execute([$templateId, $groupId]);

        $teamsStmt = db()->prepare('SELECT id FROM teams WHERE group_id=?');
        $teamsStmt->execute([$groupId]);
        $teams = $teamsStmt->fetchAll();

        $tplStmt = db()->prepare('SELECT * FROM activity_templates WHERE id=?');
        $tplStmt->execute([$templateId]);
        $tpl = $tplStmt->fetch();

        foreach ($teams as $team) {
            $token = random_token();
            db()->prepare('INSERT INTO activity_instances (template_id,team_id,status,participant_token,cs_mode,allow_multi_category,max_responses) VALUES (?,?,"draft",?,?,0,NULL)')
                ->execute([$templateId, (int)$team['id'], $token, 'open']);
            $instanceId = (int)db()->lastInsertId();
            if ($tpl['type'] === 'card_sorting') {
                self::seedCardSorting($templateId, $instanceId);
            }
        }

        redirect('/teacher');
    }

    private static function seedCardSorting(int $templateId, int $instanceId): void
    {
        $stmt = db()->prepare('SELECT title FROM activity_templates WHERE id=?');
        $stmt->execute([$templateId]);
        $title = strtolower((string)$stmt->fetchColumn());
        $cards = ['Inicio', 'Perfil', 'Ajustes', 'Soporte', 'Pagos'];
        if (str_contains($title, 'ecommerce')) {
            $cards = ['Inicio', 'Productos', 'Carrito', 'Pedidos', 'Ayuda'];
        }
        foreach ($cards as $card) {
            db()->prepare('INSERT INTO cs_cards (instance_id,label) VALUES (?,?)')->execute([$instanceId, $card]);
        }
    }

    public static function globalResults(): void
    {
        require_role('teacher');
        $type = get('type', 'card_sorting');
        $instanceId = (int)get('instance_id', 0);
        $instances = db()->query('SELECT ai.id, at.title, at.type, t.name team_name FROM activity_instances ai JOIN activity_templates at ON at.id=ai.template_id JOIN teams t ON t.id=ai.team_id ORDER BY ai.id DESC')->fetchAll();
        $data = $instanceId ? TeamController::buildResultsForInstance($instanceId, $type) : null;
        render('teacher/results', compact('instances', 'data', 'instanceId', 'type'));
    }

    public static function exportGlobalCsv(): void
    {
        require_role('teacher');
        $instanceId = (int)get('instance_id');
        $type = get('kind', 'assignments');
        TeamController::exportCsvForInstance($instanceId, $type);
    }
}
