<?php
namespace App\Models;

use App\Core\Model;

class Role extends Model {
    public function all() {
        return $this->db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
    }

    public function find($id) {
        return $this->db->query("SELECT * FROM roles WHERE id = ?", [$id])->fetch();
    }

    public function updatePermissions($id, $permissionsJson) {
        $this->db->query("UPDATE roles SET permissions = ? WHERE id = ?", [$permissionsJson, $id]);
    }
}
