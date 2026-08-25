<?php
namespace App\Models;

use App\Core\Model;

class TimeEntry extends Model {
    public function allByTask($taskId) {
        return $this->db->query("SELECT te.*, u.nom AS user_nom FROM time_entries te JOIN users u ON te.user_id = u.id WHERE te.task_id = ? ORDER BY te.date DESC", [$taskId])->fetchAll();
    }

    public function create($data) {
        $this->db->query("INSERT INTO time_entries (task_id, user_id, date, duree_minutes, description, facturable) VALUES (?, ?, ?, ?, ?, ?)", [
            $data['task_id'], $data['user_id'], $data['date'], $data['duree_minutes'], $data['description'] ?? null, $data['facturable'] ?? 1
        ]);
        return $this->db->getPdo()->lastInsertId();
    }

    public function getBillingReport($projectId, $dateStart = null, $dateEnd = null) {
        $sql = "SELECT te.*, u.nom AS user_nom, t.titre AS task_titre, s.titre AS step_titre
                FROM time_entries te
                JOIN tasks t ON te.task_id = t.id
                JOIN steps s ON t.step_id = s.id
                JOIN users u ON te.user_id = u.id
                WHERE s.project_id = ?";

        $params = [$projectId];
        if ($dateStart) {
            $sql .= " AND te.date >= ?";
            $params[] = $dateStart;
        }
        if ($dateEnd) {
            $sql .= " AND te.date <= ?";
            $params[] = $dateEnd;
        }

        $sql .= " ORDER BY te.date DESC";
        return $this->db->query($sql, $params)->fetchAll();
    }
}
