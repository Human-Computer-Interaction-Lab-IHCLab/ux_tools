<?php

require_once __DIR__ . '/../auth.php';

class TeamController
{
    public static function dashboard(): void
    {
        $user = require_role('student');
        $stmt = db()->prepare('SELECT ai.*, at.title, at.type, t.name AS team_name FROM activity_instances ai JOIN activity_templates at ON at.id=ai.template_id JOIN teams t ON t.id=ai.team_id JOIN team_members tm ON tm.team_id=t.id WHERE tm.user_id=? ORDER BY ai.id DESC');
        $stmt->execute([$user['id']]);
        $instances = $stmt->fetchAll();
        render('team/dashboard', compact('instances'));
    }

    public static function editInstance(int $id): void
    {
        $user = require_role('student');
        $instance = self::loadInstance($id, $user['id']);
        if ($instance['type'] === 'card_sorting') {
            $cards = db()->prepare('SELECT * FROM cs_cards WHERE instance_id=? ORDER BY id');
            $cards->execute([$id]);
            $cards = $cards->fetchAll();
            $catsStmt = db()->prepare('SELECT * FROM cs_seed_categories WHERE instance_id=? ORDER BY id');
            $catsStmt->execute([$id]);
            $categories = $catsStmt->fetchAll();
            render('team/edit_card_sorting', compact('instance', 'cards', 'categories'));
            return;
        }
        $nodes = db()->prepare('SELECT * FROM tt_nodes WHERE instance_id=? ORDER BY parent_id IS NULL DESC, parent_id, position');
        $nodes->execute([$id]);
        $tasks = db()->prepare('SELECT * FROM tt_tasks WHERE instance_id=? ORDER BY id');
        $tasks->execute([$id]);
        render('team/edit_tree_testing', ['instance' => $instance, 'nodes' => $nodes->fetchAll(), 'tasks' => $tasks->fetchAll()]);
    }

    public static function updateCardSorting(int $id): void
    {
        $user = require_role('student'); verify_csrf(); self::loadInstance($id, $user['id']);
        db()->prepare('UPDATE activity_instances SET cs_mode=?, allow_multi_category=?, max_responses=? WHERE id=?')
            ->execute([post('cs_mode', 'open'), post('allow_multi_category') ? 1 : 0, post('max_responses') ?: null, $id]);

        db()->prepare('DELETE FROM cs_cards WHERE instance_id=?')->execute([$id]);
        foreach (preg_split('/\r\n|\r|\n/', trim((string)post('cards', ''))) as $line) {
            $label = trim($line);
            if ($label !== '') {
                db()->prepare('INSERT INTO cs_cards (instance_id,label) VALUES (?,?)')->execute([$id, $label]);
            }
        }

        db()->prepare('DELETE FROM cs_seed_categories WHERE instance_id=?')->execute([$id]);
        foreach (preg_split('/\r\n|\r|\n/', trim((string)post('seed_categories', ''))) as $line) {
            $name = trim($line);
            if ($name !== '') {
                db()->prepare('INSERT INTO cs_seed_categories (instance_id,name) VALUES (?,?)')->execute([$id, $name]);
            }
        }
        redirect('/team/instance/' . $id);
    }

    public static function updateTreeTesting(int $id): void
    {
        $user = require_role('student'); verify_csrf(); self::loadInstance($id, $user['id']);
        db()->prepare('DELETE FROM tt_nodes WHERE instance_id=?')->execute([$id]);
        db()->prepare('DELETE FROM tt_tasks WHERE instance_id=?')->execute([$id]);

        $nodes = json_decode((string)post('nodes_json', '[]'), true) ?: [];
        $map = [];
        foreach ($nodes as $n) {
            db()->prepare('INSERT INTO tt_nodes (instance_id,parent_id,label,position) VALUES (?,?,?,?)')->execute([$id, null, trim($n['label'] ?? ''), (int)($n['position'] ?? 0)]);
            $map[$n['tmp_id']] = (int)db()->lastInsertId();
        }
        foreach ($nodes as $n) {
            if (!empty($n['parent_tmp_id']) && isset($map[$n['tmp_id']], $map[$n['parent_tmp_id']])) {
                db()->prepare('UPDATE tt_nodes SET parent_id=? WHERE id=?')->execute([$map[$n['parent_tmp_id']], $map[$n['tmp_id']]]);
            }
        }

        $tasks = json_decode((string)post('tasks_json', '[]'), true) ?: [];
        foreach ($tasks as $task) {
            if (!isset($map[$task['correct_tmp_id']])) continue;
            db()->prepare('INSERT INTO tt_tasks (instance_id,prompt,correct_node_id) VALUES (?,?,?)')
                ->execute([$id, trim((string)$task['prompt']), $map[$task['correct_tmp_id']]]);
        }

        redirect('/team/instance/' . $id);
    }

    public static function setStatus(int $id): void
    {
        $user = require_role('student'); verify_csrf(); self::loadInstance($id, $user['id']);
        $status = post('status', 'draft');
        if (!in_array($status, ['draft', 'published', 'closed'], true)) $status = 'draft';
        db()->prepare('UPDATE activity_instances SET status=? WHERE id=?')->execute([$status, $id]);
        redirect('/team/instance/' . $id);
    }

    public static function results(int $id): void
    {
        $user = require_role('student');
        $instance = self::loadInstance($id, $user['id']);
        $data = self::buildResultsForInstance($id, $instance['type']);
        render('team/results', compact('instance', 'data'));
    }

    public static function exportCsv(int $id): void
    {
        $user = require_role('student');
        self::loadInstance($id, $user['id']);
        self::exportCsvForInstance($id, get('kind', 'assignments'));
    }

    public static function exportCsvForInstance(int $id, string $kind): void
    {
        if ($kind === 'similarity') {
            csv_response('card_similarity.csv');
            $rows = self::cardSimilarity($id);
            $out = fopen('php://output', 'w');
            fputcsv($out, ['card_a', 'card_b', 'similarity_percent']);
            foreach ($rows as $row) fputcsv($out, $row);
            fclose($out);
            exit;
        }
        if ($kind === 'tt') {
            csv_response('tree_testing.csv');
            $stmt = db()->prepare('SELECT p.alias, t.prompt, r.path_text, r.is_correct, r.time_spent_ms FROM tt_responses r JOIN tt_participants p ON p.id=r.participant_id JOIN tt_tasks t ON t.id=r.task_id WHERE t.instance_id=? ORDER BY r.id');
            $stmt->execute([$id]);
            $out = fopen('php://output', 'w');
            fputcsv($out, ['participant', 'task', 'path', 'correct', 'time_ms']);
            foreach ($stmt->fetchAll() as $row) fputcsv($out, array_values($row));
            fclose($out);
            exit;
        }

        csv_response('card_assignments.csv');
        $stmt = db()->prepare('SELECT p.alias, c.label card, cat.name category FROM cs_assignments a JOIN cs_participants p ON p.id=a.participant_id JOIN cs_cards c ON c.id=a.card_id JOIN cs_categories cat ON cat.id=a.category_id WHERE p.instance_id=? ORDER BY p.id,c.id');
        $stmt->execute([$id]);
        $out = fopen('php://output', 'w');
        fputcsv($out, ['participant', 'card', 'category']);
        foreach ($stmt->fetchAll() as $row) fputcsv($out, array_values($row));
        fclose($out);
        exit;
    }

    private static function loadInstance(int $id, int $userId): array
    {
        $stmt = db()->prepare('SELECT ai.*, at.type, at.title FROM activity_instances ai JOIN activity_templates at ON at.id=ai.template_id WHERE ai.id=?');
        $stmt->execute([$id]);
        $instance = $stmt->fetch();
        if (!$instance) {
            http_response_code(404); exit('Instancia no encontrada');
        }
        ensure_team_member($userId, (int)$instance['team_id']);
        return $instance;
    }

    public static function buildResultsForInstance(int $id, string $type): array
    {
        return $type === 'tree_testing' ? self::ttResults($id) : self::csResults($id);
    }

    private static function csResults(int $id): array
    {
        $participants = (int)db()->query("SELECT COUNT(*) FROM cs_participants WHERE instance_id={$id}")->fetchColumn();
        $times = db()->query("SELECT AVG(time_spent_ms) FROM cs_participants WHERE instance_id={$id}")->fetchColumn();
        $cardRows = db()->query("SELECT c.id, c.label FROM cs_cards c WHERE c.instance_id={$id}")->fetchAll();

        $perCard = [];
        $globalDominance = [];
        foreach ($cardRows as $card) {
            $stmt = db()->prepare('SELECT cat.name, COUNT(*) qty FROM cs_assignments a JOIN cs_categories cat ON cat.id=a.category_id WHERE a.card_id=? GROUP BY cat.name ORDER BY qty DESC');
            $stmt->execute([$card['id']]);
            $rows = $stmt->fetchAll();
            $total = array_sum(array_column($rows, 'qty')) ?: 1;
            $top = array_slice($rows, 0, 3);
            $dominant = $rows[0]['name'] ?? '-';
            $percent = isset($rows[0]) ? round(($rows[0]['qty'] / $total) * 100, 2) : 0;
            $perCard[] = ['card' => $card['label'], 'dominant' => $dominant, 'percent' => $percent, 'top' => $top];
            $globalDominance[] = $percent;
        }

        $catStmt = db()->prepare('SELECT cat.name, COUNT(*) assignments FROM cs_assignments a JOIN cs_categories cat ON cat.id=a.category_id JOIN cs_participants p ON p.id=a.participant_id WHERE p.instance_id=? GROUP BY cat.id ORDER BY assignments DESC');
        $catStmt->execute([$id]);
        $perCategory = $catStmt->fetchAll();

        return [
            'summary' => [
                'participants' => $participants,
                'avg_time_ms' => (int)$times,
                'consensus' => $globalDominance ? round(array_sum($globalDominance)/count($globalDominance),2) : 0,
            ],
            'per_card' => $perCard,
            'per_category' => $perCategory,
            'similarity' => self::cardSimilarity($id),
        ];
    }

    private static function cardSimilarity(int $id): array
    {
        $cards = db()->prepare('SELECT id,label FROM cs_cards WHERE instance_id=? ORDER BY id');
        $cards->execute([$id]);
        $cards = $cards->fetchAll();
        $pStmt = db()->prepare('SELECT id FROM cs_participants WHERE instance_id=?');
        $pStmt->execute([$id]);
        $pIds = array_column($pStmt->fetchAll(), 'id');
        $pCount = count($pIds) ?: 1;
        $result = [];
        for ($i = 0; $i < count($cards); $i++) {
            for ($j = $i + 1; $j < count($cards); $j++) {
                $same = 0;
                foreach ($pIds as $pid) {
                    $s = db()->prepare('SELECT a1.category_id c1, a2.category_id c2 FROM cs_assignments a1 JOIN cs_assignments a2 ON a1.participant_id=a2.participant_id WHERE a1.participant_id=? AND a1.card_id=? AND a2.card_id=? LIMIT 1');
                    $s->execute([$pid, $cards[$i]['id'], $cards[$j]['id']]);
                    $pair = $s->fetch();
                    if ($pair && $pair['c1'] == $pair['c2']) $same++;
                }
                $result[] = [$cards[$i]['label'], $cards[$j]['label'], round(($same/$pCount)*100,2)];
            }
        }
        return $result;
    }

    private static function ttResults(int $id): array
    {
        $stmt = db()->prepare('SELECT t.id, t.prompt, AVG(r.is_correct) success, AVG(r.time_spent_ms) avg_time_ms FROM tt_tasks t LEFT JOIN tt_responses r ON r.task_id=t.id WHERE t.instance_id=? GROUP BY t.id ORDER BY t.id');
        $stmt->execute([$id]);
        $tasks = $stmt->fetchAll();
        foreach ($tasks as &$task) {
            $m = db()->prepare('SELECT time_spent_ms FROM tt_responses WHERE task_id=? ORDER BY time_spent_ms');
            $m->execute([$task['id']]);
            $arr = array_column($m->fetchAll(), 'time_spent_ms');
            $task['median_ms'] = $arr ? $arr[(int)floor((count($arr)-1)/2)] : 0;
            $r = db()->prepare('SELECT path_text, COUNT(*) qty FROM tt_responses WHERE task_id=? GROUP BY path_text ORDER BY qty DESC LIMIT 3');
            $r->execute([$task['id']]);
            $task['top_routes'] = $r->fetchAll();
        }
        return ['tasks' => $tasks];
    }
}
