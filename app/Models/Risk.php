<?php
namespace App\Models;

use App\Core\Model;

class Risk extends Model {
    public function allByProject($projectId) {
        return $this->db->query("SELECT * FROM risks WHERE project_id = ? ORDER BY id DESC", [$projectId])->fetchAll();
    }

    public function all() {
        return $this->db->query("SELECT r.*, p.titre AS project_titre FROM risks r JOIN projects p ON r.project_id = p.id ORDER BY r.id DESC")->fetchAll();
    }

    public function create($data) {
        $this->db->query("INSERT INTO risks (project_id, description, probabilite, impact, plan_mitigation, statut) VALUES (?, ?, ?, ?, ?, ?)", [
            $data['project_id'], $data['description'], $data['probabilite'] ?? 'Faible', $data['impact'] ?? 'Faible', $data['plan_mitigation'] ?? null, $data['statut'] ?? 'Identifié'
        ]);
        return $this->db->getPdo()->lastInsertId();
    }

    public function delete($id) {
        $this->db->query("DELETE FROM risks WHERE id = ?", [$id]);
    }
}
