<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Helpers\Flash;
use App\Models\Notification;

class NotificationController extends Controller {
    public function index() {
        $notifModel = new Notification();
        $notifications = $notifModel->allByUser(Auth::id());

        $this->view('notifications/index', [
            'notifications' => $notifications
        ]);
    }

    public function unreadCount() {
        $notifModel = new Notification();
        $count = $notifModel->unreadCount(Auth::id());
        return $this->json(['count' => $count]);
    }

    public function markAsRead($id) {
        $notifModel = new Notification();
        $notifModel->markAsRead($id);

        Flash::success("Notification marquée comme lue.");
        $this->redirect('/notifications');
    }
}
