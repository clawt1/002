<?php
namespace App\Models;

use App\Core\Model;

class Notification extends Model {
    public function allByUser($userId) {
        return $this->db->query("SELECT * FROM notifications WHERE user_id = ? ORDER BY cree_le DESC", [$userId])->fetchAll();
    }

    public function unreadCount($userId) {
        $stmt = $this->db->query("SELECT COUNT(*) AS count FROM notifications WHERE user_id = ? AND lu = 0", [$userId]);
        $res = $stmt->fetch();
        return $res ? intval($res['count']) : 0;
    }

    public function create($userId, $type, $cibleType, $cibleId, $message) {
        $this->db->query("INSERT INTO notifications (user_id, type, cible_type, cible_id, message) VALUES (?, ?, ?, ?, ?)", [
            $userId, $type, $cibleType, $cibleId, $message
        ]);
        return $this->db->getPdo()->lastInsertId();
    }

    public function markAsRead($id) {
        $this->db->query("UPDATE notifications SET lu = 1 WHERE id = ?", [$id]);
    }
}
