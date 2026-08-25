<?php
namespace App\Models;

use App\Core\Model;

class User extends Model {
    public function all() {
        return $this->db->query("SELECT u.*, r.nom AS role_nom FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.nom ASC")->fetchAll();
    }

    public function find($id) {
        return $this->db->query("SELECT u.*, r.nom AS role_nom FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?", [$id])->fetch();
    }

    public function findByEmail($email) {
        return $this->db->query("SELECT u.*, r.nom AS role_nom FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.email = ? LIMIT 1", [$email])->fetch();
    }

    public function create($data) {
        $this->db->query("INSERT INTO users (nom, email, mot_de_passe_hash, role_id, avatar, actif) VALUES (?, ?, ?, ?, ?, ?)", [
            $data['nom'], $data['email'], $data['mot_de_passe_hash'], $data['role_id'], $data['avatar'] ?? null, $data['actif'] ?? 1
        ]);
        return $this->db->getPdo()->lastInsertId();
    }

    public function update($id, $data) {
        $this->db->query("UPDATE users SET nom = ?, email = ?, role_id = ?, avatar = ?, actif = ? WHERE id = ?", [
            $data['nom'], $data['email'], $data['role_id'], $data['avatar'] ?? null, $data['actif'] ?? 1, $id
        ]);
    }

    public function updatePassword($id, $hash) {
        $this->db->query("UPDATE users SET mot_de_passe_hash = ? WHERE id = ?", [$hash, $id]);
    }

    public function toggleActive($id, $actif) {
        $this->db->query("UPDATE users SET actif = ? WHERE id = ?", [$actif, $id]);
    }

    public function delete($id) {
        $this->db->query("DELETE FROM users WHERE id = ?", [$id]);
    }
}
