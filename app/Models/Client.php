<?php
namespace App\Models;

use App\Core\Model;

class Client extends Model {
    public function all() {
        return $this->db->query("SELECT * FROM clients ORDER BY nom ASC")->fetchAll();
    }

    public function find($id) {
        return $this->db->query("SELECT * FROM clients WHERE id = ?", [$id])->fetch();
    }

    public function create($data) {
        $this->db->query("INSERT INTO clients (nom, logo, couleur_primaire) VALUES (?, ?, ?)", [
            $data['nom'], $data['logo'] ?? null, $data['couleur_primaire'] ?? '#3b82f6'
        ]);
        return $this->db->getPdo()->lastInsertId();
    }
}
