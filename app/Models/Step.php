<?php
namespace App\Models;

use App\Core\Model;

class Step extends Model {
    public function allByProject($projectId) {
        return $this->db->query("SELECT * FROM steps WHERE project_id = ? ORDER BY ordre ASC", [$projectId])->fetchAll();
    }

    public function find($id) {
        return $this->db->query("SELECT s.*, p.titre AS project_titre, p.owner_id AS project_owner FROM steps s JOIN projects p ON s.project_id = p.id WHERE s.id = ?", [$id])->fetch();
    }

    public function create($data) {
        $this->db->query("INSERT INTO steps (project_id, titre, description, definition_of_done, deadline, branche_git, ordre, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            $data['project_id'], $data['titre'], $data['description'], $data['definition_of_done'] ?? null, $data['deadline'], $data['branche_git'] ?? null, $data['ordre'] ?? 0, $data['statut'] ?? 'À faire'
        ]);
        return $this->db->getPdo()->lastInsertId();
    }

    public function update($id, $data) {
        $this->db->query("UPDATE steps SET titre = ?, description = ?, definition_of_done = ?, deadline = ?, branche_git = ?, ordre = ?, statut = ? WHERE id = ?", [
            $data['titre'], $data['description'], $data['definition_of_done'] ?? null, $data['deadline'], $data['branche_git'] ?? null, $data['ordre'] ?? 0, $data['statut'], $id
        ]);
    }

    public function updateStatus($id, $status) {
        $this->db->query("UPDATE steps SET statut = ? WHERE id = ?", [$status, $id]);
    }

    public function grade($id, $note, $commentaire) {
        $this->db->query("UPDATE steps SET note = ?, commentaire_formateur = ? WHERE id = ?", [$note, $commentaire, $id]);
    }

    public function delete($id) {
        $this->db->query("DELETE FROM steps WHERE id = ?", [$id]);
    }
}
