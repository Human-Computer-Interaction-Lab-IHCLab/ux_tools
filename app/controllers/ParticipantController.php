<?php

require_once __DIR__ . '/../auth.php';

class ParticipantController
{
    public static function landing(string $token): void
    {
        $stmt = db()->prepare('SELECT ai.*, at.type, at.title, at.instructions FROM activity_instances ai JOIN activity_templates at ON at.id=ai.template_id WHERE ai.participant_token=? AND ai.status="published"');
        $stmt->execute([$token]);
        $instance = $stmt->fetch();
        if (!$instance) { http_response_code(404); exit('Estudio no disponible'); }
        render('participant/landing', compact('instance', 'token'));
    }

    public static function start(string $token): void
    {
        $alias = trim((string)post('alias', 'Invitado')) ?: 'Invitado';
        $stmt = db()->prepare('SELECT ai.*, at.type FROM activity_instances ai JOIN activity_templates at ON at.id=ai.template_id WHERE ai.participant_token=? AND ai.status="published"');
        $stmt->execute([$token]);
        $instance = $stmt->fetch();
        if (!$instance) { http_response_code(404); exit('No disponible'); }

        if ($instance['type'] === 'card_sorting') {
            db()->prepare('INSERT INTO cs_participants (instance_id,alias,started_at) VALUES (?,?,NOW())')->execute([$instance['id'], $alias]);
            $pid = (int)db()->lastInsertId();
            $_SESSION['participant'][$token] = ['id' => $pid, 'instance_id' => $instance['id'], 'started_at' => microtime(true)];
            redirect('/p/' . $token . '/card');
        }

        db()->prepare('INSERT INTO tt_participants (instance_id,alias,started_at) VALUES (?,?,NOW())')->execute([$instance['id'], $alias]);
        $pid = (int)db()->lastInsertId();
        $_SESSION['participant'][$token] = ['id' => $pid, 'instance_id' => $instance['id'], 'started_at' => microtime(true)];
        redirect('/p/' . $token . '/tree');
    }

    public static function cardForm(string $token): void
    {
        $part = $_SESSION['participant'][$token] ?? null;
        if (!$part) redirect('/p/' . $token);
        $cards = db()->prepare('SELECT * FROM cs_cards WHERE instance_id=? ORDER BY id'); $cards->execute([$part['instance_id']]);
        $seed = db()->prepare('SELECT * FROM cs_seed_categories WHERE instance_id=? ORDER BY id'); $seed->execute([$part['instance_id']]);
        $inst = db()->prepare('SELECT cs_mode,allow_multi_category FROM activity_instances WHERE id=?'); $inst->execute([$part['instance_id']]);
        render('participant/card', ['token' => $token, 'cards' => $cards->fetchAll(), 'seed' => $seed->fetchAll(), 'instance' => $inst->fetch()]);
    }

    public static function submitCard(string $token): void
    {
        $part = $_SESSION['participant'][$token] ?? null;
        if (!$part) redirect('/p/' . $token);
        $payload = json_decode((string)post('payload', '{}'), true) ?: [];
        $cardsStmt = db()->prepare('SELECT id FROM cs_cards WHERE instance_id=?');
        $cardsStmt->execute([$part['instance_id']]);
        $cardIds = array_column($cardsStmt->fetchAll(), 'id');

        foreach ($cardIds as $cid) {
            if (empty($payload[(string)$cid])) {
                exit('Faltan tarjetas por categorizar');
            }
        }

        db()->prepare('DELETE FROM cs_assignments WHERE participant_id=?')->execute([$part['id']]);
        foreach ($payload as $cardId => $catName) {
            $catName = trim((string)$catName);
            if ($catName === '') continue;
            $catId = self::findOrCreateCategory($part['id'], $part['instance_id'], $catName);
            db()->prepare('INSERT INTO cs_assignments (participant_id,card_id,category_id) VALUES (?,?,?)')->execute([$part['id'], (int)$cardId, $catId]);
        }

        $ms = (int)((microtime(true) - $part['started_at']) * 1000);
        db()->prepare('UPDATE cs_participants SET finished_at=NOW(), time_spent_ms=? WHERE id=?')->execute([$ms, $part['id']]);
        unset($_SESSION['participant'][$token]);
        render('participant/thanks');
    }

    private static function findOrCreateCategory(int $participantId, int $instanceId, string $name): int
    {
        $stmt = db()->prepare('SELECT id FROM cs_categories WHERE participant_id=? AND name=?');
        $stmt->execute([$participantId, $name]);
        $id = $stmt->fetchColumn();
        if ($id) return (int)$id;
        db()->prepare('INSERT INTO cs_categories (participant_id,instance_id,name) VALUES (?,?,?)')->execute([$participantId, $instanceId, $name]);
        return (int)db()->lastInsertId();
    }

    public static function treeForm(string $token): void
    {
        $part = $_SESSION['participant'][$token] ?? null;
        if (!$part) redirect('/p/' . $token);
        $nodes = db()->prepare('SELECT * FROM tt_nodes WHERE instance_id=? ORDER BY parent_id IS NULL DESC,parent_id,position');
        $nodes->execute([$part['instance_id']]);
        $tasks = db()->prepare('SELECT * FROM tt_tasks WHERE instance_id=? ORDER BY id');
        $tasks->execute([$part['instance_id']]);
        render('participant/tree', ['token' => $token, 'nodes' => $nodes->fetchAll(), 'tasks' => $tasks->fetchAll()]);
    }

    public static function submitTree(string $token): void
    {
        $part = $_SESSION['participant'][$token] ?? null;
        if (!$part) redirect('/p/' . $token);
        $answers = json_decode((string)post('answers', '[]'), true) ?: [];
        foreach ($answers as $ans) {
            $taskId = (int)$ans['task_id']; $nodeId = (int)$ans['selected_node_id'];
            $task = db()->prepare('SELECT correct_node_id FROM tt_tasks WHERE id=? AND instance_id=?');
            $task->execute([$taskId, $part['instance_id']]);
            $correct = (int)$task->fetchColumn();
            $isCorrect = $correct === $nodeId ? 1 : 0;
            db()->prepare('INSERT INTO tt_responses (participant_id,task_id,selected_node_id,path_text,time_spent_ms,is_correct) VALUES (?,?,?,?,?,?)')
                ->execute([$part['id'], $taskId, $nodeId, trim((string)$ans['path_text']), (int)$ans['time_ms'], $isCorrect]);
        }
        $ms = (int)((microtime(true) - $part['started_at']) * 1000);
        db()->prepare('UPDATE tt_participants SET finished_at=NOW(), time_spent_ms=? WHERE id=?')->execute([$ms, $part['id']]);
        unset($_SESSION['participant'][$token]);
        render('participant/thanks');
    }
}
