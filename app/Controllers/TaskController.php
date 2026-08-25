<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Helpers\Flash;
use App\Helpers\Validator;
use App\Models\Task;
use App\Models\Step;
use App\Models\Notification;
use App\Models\User;

class TaskController extends Controller {
    public function create($stepId) {
        if (!Auth::hasPermission('tasks.create') && !Auth::hasPermission('all')) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }

        $stepModel = new Step();
        $step = $stepModel->find($stepId);
        if (!$step) {
            $this->redirect('/');
        }

        $userModel = new User();
        $developers = $userModel->all();

        $this->view('tasks/create', [
            'step' => $step,
            'developers' => $developers
        ]);
    }

    public function store() {
        if (!Auth::hasPermission('tasks.create') && !Auth::hasPermission('all')) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect('/');
        }

        $validator = new Validator();
        $validated = $validator->validate($_POST, [
            'step_id' => ['required'],
            'titre' => ['required', 'min:3', 'max:150']
        ]);

        if (!$validated) {
            foreach ($validator->getErrors() as $errs) {
                foreach ($errs as $err) {
                    Flash::error($err);
                }
            }
            $this->redirect('/');
        }

        $taskModel = new Task();
        $newId = $taskModel->create($_POST);

        $stepModel = new Step();
        $step = $stepModel->find($_POST['step_id']);

        if (!empty($_POST['assigne_id'])) {
            $notifModel = new Notification();
            $notifModel->create(
                $_POST['assigne_id'],
                'assignment',
                'tasks',
                $newId,
                "Vous avez été assigné à la tâche '{$_POST['titre']}' (Etape : '{$step['titre']}')"
            );
        }

        Auth::logAudit(Auth::id(), 'CREATION_TACHE', 'tasks', $newId, "Création de la tâche : " . $_POST['titre']);
        Flash::success("Tâche créée !");
        $this->redirect("/projects/{$step['project_id']}");
    }

    public function edit($id) {
        if (!Auth::hasPermission('tasks.create') && !Auth::hasPermission('all')) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }

        $taskModel = new Task();
        $task = $taskModel->find($id);
        if (!$task) {
            $this->redirect('/');
        }

        $userModel = new User();
        $developers = $userModel->all();

        $this->view('tasks/edit', [
            'task' => $task,
            'developers' => $developers
        ]);
    }

    public function update($id) {
        if (!Auth::hasPermission('tasks.create') && !Auth::hasPermission('all')) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }

        $taskModel = new Task();
        $task = $taskModel->find($id);
        if (!$task) {
            $this->redirect('/');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect("/projects/{$task['project_id']}");
        }

        $validator = new Validator();
        $validated = $validator->validate($_POST, [
            'titre' => ['required', 'min:3', 'max:150']
        ]);

        if (!$validated) {
            foreach ($validator->getErrors() as $errs) {
                foreach ($errs as $err) {
                    Flash::error($err);
                }
            }
            $this->redirect("/tasks/{$id}/edit");
        }

        $taskModel->update($id, $_POST);

        if (!empty($_POST['assigne_id']) && $_POST['assigne_id'] != $task['assigne_id']) {
            $notifModel = new Notification();
            $notifModel->create(
                $_POST['assigne_id'],
                'assignment',
                'tasks',
                $id,
                "Vous avez été assigné à la tâche '{$_POST['titre']}'"
            );
        }

        Auth::logAudit(Auth::id(), 'MODIFICATION_TACHE', 'tasks', $id, "Modification de la tâche : " . $_POST['titre']);
        Flash::success("Tâche mise à jour !");
        $this->redirect("/projects/{$task['project_id']}");
    }

    public function destroy($id) {
        if (!Auth::hasPermission('projects.delete') && !Auth::hasPermission('all')) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }

        $taskModel = new Task();
        $task = $taskModel->find($id);
        if ($task) {
            $taskModel->delete($id);
            Auth::logAudit(Auth::id(), 'SUPPRESSION_TACHE', 'tasks', $id, "Suppression de la tâche : " . $task['titre']);
            Flash::success("Tâche supprimée !");
            $this->redirect("/projects/{$task['project_id']}");
        } else {
            $this->redirect('/');
        }
    }

    public function updateStatus($id) {
        $user = Auth::user();
        $role = $user['role_nom'] ?? '';
        if (!Auth::hasPermission('tasks.edit_status') && !Auth::hasPermission('all') && $role !== 'Apprenant') {
            return $this->json(['success' => false, 'message' => "Accès non autorisé"], 403);
        }

        $taskModel = new Task();
        $task = $taskModel->find($id);
        if (!$task) {
            return $this->json(['success' => false, 'message' => "Tâche introuvable"], 404);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $newStatus = $input['statut'] ?? '';

        $allowedStatuses = ['À faire', 'En cours', 'En revue', 'Terminé'];
        if (!in_array($newStatus, $allowedStatuses)) {
            return $this->json(['success' => false, 'message' => "Statut invalide"], 400);
        }

        $taskModel->updateStatus($id, $newStatus);

        Auth::logAudit(Auth::id(), 'STATUT_TACHE_KANBAN', 'tasks', $id, "Changement du statut de la tâche '{$task['titre']}' vers {$newStatus}");

        return $this->json([
            'success' => true,
            'message' => "Statut de la tâche mis à jour vers : {$newStatus}"
        ]);
    }

    public function uploadAttachment($id) {
        $taskModel = new Task();
        $task = $taskModel->find($id);
        if (!$task) {
            $this->redirect('/');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect("/projects/{$task['project_id']}");
        }

        if (empty($_FILES['file']['name'])) {
            Flash::error("Aucun fichier sélectionné.");
            $this->redirect("/projects/{$task['project_id']}");
        }

        $file = $_FILES['file'];
        $originalName = $file['name'];
        $tmpPath = $file['tmp_name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'txt'];
        if (!in_array($extension, $allowedExtensions)) {
            Flash::error("Extension de fichier non autorisée. Seuls les formats d'images, PDF, Office et ZIP sont permis.");
            $this->redirect("/projects/{$task['project_id']}");
        }

        $mimeType = mime_content_type($tmpPath);
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif',
            'application/pdf',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip', 'text/plain'
        ];
        if (!in_array($mimeType, $allowedMimes)) {
            Flash::error("Type de fichier (MIME) non autorisé par la sécurité.");
            $this->redirect("/projects/{$task['project_id']}");
        }

        $uploadDir = __DIR__ . "/../../public_html/uploads/tasks/{$id}";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $randomName = bin2hex(random_bytes(16)) . '.' . $extension;
        $destPath = $uploadDir . '/' . $randomName;

        if (move_uploaded_file($tmpPath, $destPath)) {
            $taskModel->addAttachment($id, "tasks/{$id}/" . $randomName, $originalName, Auth::id());
            Auth::logAudit(Auth::id(), 'UPLOAD_PIECE_JOINTE', 'tasks', $id, "Téléversement du fichier : " . $originalName);
            Flash::success("Fichier téléversé avec succès !");
        } else {
            Flash::error("Erreur lors du déplacement du fichier.");
        }

        $this->redirect("/projects/{$task['project_id']}");
    }
}
