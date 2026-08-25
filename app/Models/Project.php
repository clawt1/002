<?php
namespace App\Models;

use App\Core\Model;

class Project extends Model {
    public function all($filters = []) {
        $sql = "SELECT p.*, c.nom AS client_nom, c.couleur_primaire AS client_couleur, u.nom AS owner_nom,
                (SELECT COUNT(*) FROM steps s WHERE s.project_id = p.id) AS total_steps,
                (SELECT COUNT(*) FROM steps s WHERE s.project_id = p.id AND s.statut = 'Terminé') AS completed_steps,
                (SELECT COUNT(*) FROM steps s JOIN tasks t ON t.step_id = s.id WHERE s.project_id = p.id) AS total_tasks,
                (SELECT COUNT(*) FROM steps s JOIN tasks t ON t.step_id = s.id WHERE s.project_id = p.id AND t.statut = 'Terminé') AS completed_tasks
                FROM projects p
                LEFT JOIN clients c ON p.client_id = c.id
                LEFT JOIN users u ON p.owner_id = u.id";

        $params = [];
        $where = [];

        if (!empty($filters['owner_id'])) {
            $where[] = "p.owner_id = ?";
            $params[] = $filters['owner_id'];
        }

        if (!empty($filters['client_id'])) {
            $where[] = "p.client_id = ?";
            $params[] = $filters['client_id'];
        }

        if (!empty($filters['statut'])) {
            $where[] = "p.statut = ?";
            $params[] = $filters['statut'];
        }

        if (count($where) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY p.cree_le DESC";

        $projects = $this->db->query($sql, $params)->fetchAll();

        foreach ($projects as &$p) {
            $p['progress_pct'] = 0;
            if ($p['total_tasks'] > 0) {
                $p['progress_pct'] = round(($p['completed_tasks'] / $p['total_tasks']) * 100);
            } elseif ($p['total_steps'] > 0) {
                $p['progress_pct'] = round(($p['completed_steps'] / $p['total_steps']) * 100);
            }
        }

        return $projects;
    }

    public function find($id) {
        $sql = "SELECT p.*, c.nom AS client_nom, c.couleur_primaire AS client_couleur, u.nom AS owner_nom FROM projects p
                LEFT JOIN clients c ON p.client_id = c.id
                LEFT JOIN users u ON p.owner_id = u.id
                WHERE p.id = ?";
        return $this->db->query($sql, [$id])->fetch();
    }

    public function findByToken($token) {
        $sql = "SELECT p.*, c.nom AS client_nom, c.logo AS client_logo, c.couleur_primaire AS client_couleur, u.nom AS owner_nom FROM projects p
                LEFT JOIN clients c ON p.client_id = c.id
                LEFT JOIN users u ON p.owner_id = u.id
                WHERE p.token_public = ?";
        return $this->db->query($sql, [$token])->fetch();
    }

    public function withProgress($id) {
        $sql = "SELECT
                    p.*,
                    c.nom AS client_nom,
                    c.couleur_primaire AS client_couleur,
                    u.nom AS owner_nom,
                    (SELECT COUNT(*) FROM steps s WHERE s.project_id = p.id) AS total_steps,
                    (SELECT COUNT(*) FROM steps s WHERE s.project_id = p.id AND s.statut = 'Terminé') AS completed_steps,
                    (SELECT COUNT(*) FROM steps s JOIN tasks t ON t.step_id = s.id WHERE s.project_id = p.id) AS total_tasks,
                    (SELECT COUNT(*) FROM steps s JOIN tasks t ON t.step_id = s.id WHERE s.project_id = p.id AND t.statut = 'Terminé') AS completed_tasks
                FROM projects p
                LEFT JOIN clients c ON p.client_id = c.id
                LEFT JOIN users u ON p.owner_id = u.id
                WHERE p.id = ?";
        $project = $this->db->query($sql, [$id])->fetch();
        if ($project) {
            $project['progress_pct'] = 0;
            if ($project['total_tasks'] > 0) {
                $project['progress_pct'] = round(($project['completed_tasks'] / $project['total_tasks']) * 100);
            } elseif ($project['total_steps'] > 0) {
                $project['progress_pct'] = round(($project['completed_steps'] / $project['total_steps']) * 100);
            }
        }
        return $project;
    }

    public function create($data) {
        $token = bin2hex(random_bytes(16));
        $this->db->query("INSERT INTO projects (titre, description, tech_stack_id, owner_id, client_id, token_public, date_debut, date_fin_prevue, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $data['titre'], $data['description'], $data['tech_stack_id'] ?? null, $data['owner_id'] ?? null, $data['client_id'] ?? null, $token, $data['date_debut'], $data['date_fin_prevue'], $data['statut'] ?? 'Planifié'
        ]);
        return $this->db->getPdo()->lastInsertId();
    }

    public function update($id, $data) {
        $this->db->query("UPDATE projects SET titre = ?, description = ?, tech_stack_id = ?, owner_id = ?, client_id = ?, date_debut = ?, date_fin_prevue = ?, statut = ? WHERE id = ?", [
            $data['titre'], $data['description'], $data['tech_stack_id'] ?? null, $data['owner_id'] ?? null, $data['client_id'] ?? null, $data['date_debut'], $data['date_fin_prevue'], $data['statut'], $id
        ]);
    }

    public function delete($id) {
        $this->db->query("DELETE FROM projects WHERE id = ?", [$id]);
    }

    public function duplicateProjectForUser($templateId, $userId) {
        $project = $this->find($templateId);
        if (!$project) return false;

        $newTitle = $project['titre'] . " (Copie)";
        $token = bin2hex(random_bytes(16));

        $this->db->query("INSERT INTO projects (titre, description, tech_stack_id, owner_id, client_id, token_public, date_debut, date_fin_prevue, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Planifié')", [
            $newTitle, $project['description'], $project['tech_stack_id'] ?? null, $userId, $project['client_id'] ?? null, $token, date('Y-m-d'), $project['date_fin_prevue'],
        ]);
        $newProjectId = $this->db->getPdo()->lastInsertId();

        $stmt = $this->db->query("SELECT * FROM steps WHERE project_id = ? ORDER BY ordre ASC", [$templateId]);
        $steps = $stmt->fetchAll();
        foreach ($steps as $step) {
            $this->db->query("INSERT INTO steps (project_id, titre, description, definition_of_done, deadline, branche_git, ordre, statut) VALUES (?, ?, ?, ?, ?, ?, ?, 'À faire')", [
                $newProjectId, $step['titre'], $step['description'], $step['definition_of_done'], $step['deadline'], $step['branche_git'], $step['ordre']
            ]);
            $newStepId = $this->db->getPdo()->lastInsertId();

            $stmtTasks = $this->db->query("SELECT * FROM tasks WHERE step_id = ?", [$step['id']]);
            $tasks = $stmtTasks->fetchAll();
            foreach ($tasks as $task) {
                $this->db->query("INSERT INTO tasks (step_id, titre, description, livrable, criteres_acceptation, tests, exigences_securite, estimation_jours, assigne_id, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'À faire')", [
                    $newStepId, $task['titre'], $task['description'], $task['livrable'], $task['criteres_acceptation'], $task['tests'], $task['exigences_securite'], $task['estimation_jours'], $userId
                ]);
            }
        }

        return $newProjectId;
    }
}
