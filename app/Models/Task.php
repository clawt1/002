<?php
namespace App\Models;

use App\Core\Model;

class Task extends Model {
    public function allByStep($stepId) {
        return $this->db->query("SELECT t.*, u.nom AS assigne_nom, u.avatar AS assigne_avatar FROM tasks t LEFT JOIN users u ON t.assigne_id = u.id WHERE t.step_id = ? ORDER BY t.cree_le ASC", [$stepId])->fetchAll();
    }

    public function allByProject($projectId) {
        $sql = "SELECT t.*, s.titre AS step_titre, u.nom AS assigne_nom FROM tasks t
                JOIN steps s ON t.step_id = s.id
                LEFT JOIN users u ON t.assigne_id = u.id
                WHERE s.project_id = ?
                ORDER BY s.ordre ASC, t.cree_le ASC";
        return $this->db->query($sql, [$projectId])->fetchAll();
    }

    public function find($id) {
        $sql = "SELECT t.*, s.project_id, s.titre AS step_titre, p.titre AS project_titre, u.nom AS assigne_nom
                FROM tasks t
                JOIN steps s ON t.step_id = s.id
                JOIN projects p ON s.project_id = p.id
                LEFT JOIN users u ON t.assigne_id = u.id
                WHERE t.id = ?";
        return $this->db->query($sql, [$id])->fetch();
    }

    public function create($data) {
        $this->db->query("INSERT INTO tasks (step_id, titre, description, livrable, criteres_acceptation, tests, exigences_securite, estimation_jours, assigne_id, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $data['step_id'], $data['titre'], $data['description'], $data['livrable'] ?? null, $data['criteres_acceptation'] ?? null, $data['tests'] ?? null, $data['exigences_securite'] ?? null, $data['estimation_jours'] ?? 0.00, $data['assigne_id'] ?? null, $data['statut'] ?? 'À faire'
        ]);
        return $this->db->getPdo()->lastInsertId();
    }

    public function update($id, $data) {
        $this->db->query("UPDATE tasks SET step_id = ?, titre = ?, description = ?, livrable = ?, criteres_acceptation = ?, tests = ?, exigences_securite = ?, estimation_jours = ?, assigne_id = ?, statut = ? WHERE id = ?", [
            $data['step_id'], $data['titre'], $data['description'], $data['livrable'] ?? null, $data['criteres_acceptation'] ?? null, $data['tests'] ?? null, $data['exigences_securite'] ?? null, $data['estimation_jours'] ?? 0.00, $data['assigne_id'] ?? null, $data['statut'], $id
        ]);
    }

    public function updateStatus($id, $status) {
        $this->db->query("UPDATE tasks SET statut = ? WHERE id = ?", [$status, $id]);
    }

    public function delete($id) {
        $this->db->query("DELETE FROM tasks WHERE id = ?", [$id]);
    }

    public function getAttachments($taskId) {
        return $this->db->query("SELECT a.*, u.nom AS uploader_nom FROM attachments a LEFT JOIN users u ON a.uploaded_by = u.id WHERE a.task_id = ? ORDER BY a.date_upload DESC", [$taskId])->fetchAll();
    }

    public function addAttachment($taskId, $chemin, $nomOriginal, $userId) {
        $this->db->query("INSERT INTO attachments (task_id, chemin_fichier, nom_original, uploaded_by) VALUES (?, ?, ?, ?)", [
            $taskId, $chemin, $nomOriginal, $userId
        ]);
    }
}
