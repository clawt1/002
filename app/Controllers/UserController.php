<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Helpers\Flash;
use App\Helpers\Validator;
use App\Models\User;
use App\Models\Role;

class UserController extends Controller {
    public function index() {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }
        $userModel = new User();
        $users = $userModel->all();
        $this->view('users/index', ['users' => $users]);
    }

    public function create() {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }
        $roleModel = new Role();
        $roles = $roleModel->all();
        $this->view('users/create', ['roles' => $roles]);
    }

    public function store() {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect('/users');
        }

        $validator = new Validator();
        $validated = $validator->validate($_POST, [
            'nom' => ['required', 'min:2', 'max:50'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:4']
        ]);

        if (!$validated) {
            foreach ($validator->getErrors() as $errs) {
                foreach ($errs as $err) {
                    Flash::error($err);
                }
            }
            $this->redirect('/users/create');
        }

        $userModel = new User();
        $userModel->create([
            'nom' => $_POST['nom'],
            'email' => $_POST['email'],
            'mot_de_passe_hash' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            'role_id' => intval($_POST['role_id'] ?? 3),
            'actif' => 1
        ]);

        Flash::success("Utilisateur créé avec succès !");
        $this->redirect('/users');
    }

    public function edit($id) {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }
        $userModel = new User();
        $user = $userModel->find($id);
        if (!$user) {
            $this->redirect('/users');
        }

        $roleModel = new Role();
        $roles = $roleModel->all();

        $this->view('users/edit', [
            'user' => $user,
            'roles' => $roles
        ]);
    }

    public function update($id) {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }

        $userModel = new User();
        $user = $userModel->find($id);
        if (!$user) {
            $this->redirect('/users');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect('/users');
        }

        $validator = new Validator();
        $validated = $validator->validate($_POST, [
            'nom' => ['required', 'min:2', 'max:50'],
            'email' => ['required', 'email', "unique:users,email,{$id}"]
        ]);

        if (!$validated) {
            foreach ($validator->getErrors() as $errs) {
                foreach ($errs as $err) {
                    Flash::error($err);
                }
            }
            $this->redirect("/users/{$id}/edit");
        }

        $userModel->update($id, [
            'nom' => $_POST['nom'],
            'email' => $_POST['email'],
            'role_id' => intval($_POST['role_id']),
            'avatar' => $user['avatar'],
            'actif' => intval($_POST['actif'] ?? 1)
        ]);

        if (!empty($_POST['password'])) {
            $userModel->updatePassword($id, password_hash($_POST['password'], PASSWORD_BCRYPT));
        }

        Flash::success("Informations utilisateur mises à jour !");
        $this->redirect('/users');
    }

    public function toggleActive($id) {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }
        $userModel = new User();
        $user = $userModel->find($id);
        if ($user) {
            $newActif = $user['actif'] ? 0 : 1;
            $userModel->toggleActive($id, $newActif);
            Flash::success($newActif ? "Compte réactivé !" : "Compte suspendu !");
        }
        $this->redirect('/users');
    }

    public function profile() {
        $userModel = new User();
        $user = $userModel->find(Auth::id());
        $this->view('users/profile', ['user' => $user]);
    }

    public function updateProfile() {
        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect('/profile');
        }

        $userModel = new User();
        $user = $userModel->find(Auth::id());

        $validator = new Validator();
        $validated = $validator->validate($_POST, [
            'nom' => ['required', 'min:2', 'max:50'],
            'email' => ['required', 'email', "unique:users,email," . Auth::id()]
        ]);

        if (!$validated) {
            foreach ($validator->getErrors() as $errs) {
                foreach ($errs as $err) {
                    Flash::error($err);
                }
            }
            $this->redirect('/profile');
        }

        $avatarName = $user['avatar'];
        if (!empty($_FILES['avatar']['name'])) {
            $file = $_FILES['avatar'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif'])) {
                $uploadDir = __DIR__ . '/../../public_html/uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $avatarName = 'avatar_' . Auth::id() . '_' . time() . '.' . $ext;
                $destPath = $uploadDir . '/' . $avatarName;

                $this->resizeAvatar($file['tmp_name'], $destPath, 150, 150);
            }
        }

        $userModel->update(Auth::id(), [
            'nom' => $_POST['nom'],
            'email' => $_POST['email'],
            'role_id' => $user['role_id'],
            'avatar' => $avatarName,
            'actif' => 1
        ]);

        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 4) {
                Flash::error("Le nouveau mot de passe doit faire au moins 4 caractères.");
            } else {
                $userModel->updatePassword(Auth::id(), password_hash($_POST['password'], PASSWORD_BCRYPT));
            }
        }

        Flash::success("Profil mis à jour !");
        $this->redirect('/profile');
    }

    private function resizeAvatar($tmpPath, $destPath, $newWidth = 150, $newHeight = 150) {
        list($width, $height, $type) = getimagesize($tmpPath);
        switch ($type) {
            case IMAGETYPE_JPEG:
                $src = @imagecreatefromjpeg($tmpPath);
                break;
            case IMAGETYPE_PNG:
                $src = @imagecreatefrompng($tmpPath);
                break;
            case IMAGETYPE_GIF:
                $src = @imagecreatefromgif($tmpPath);
                break;
            default:
                return false;
        }

        if (!$src) return false;

        $dst = imagecreatetruecolor($newWidth, $newHeight);
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($dst, $destPath, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($dst, $destPath);
                break;
            case IMAGETYPE_GIF:
                imagegif($dst, $destPath);
                break;
        }
        imagedestroy($src);
        imagedestroy($dst);
        return true;
    }

    public function permissionsMatrix() {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }
        $roleModel = new Role();
        $roles = $roleModel->all();
        $this->view('users/permissions', ['roles' => $roles]);
    }

    public function updatePermissions() {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect('/roles/permissions');
        }

        $roleModel = new Role();
        $roles = $roleModel->all();

        foreach ($roles as $role) {
            if ($role['id'] == 1) continue;

            $perms = [];
            $inputs = $_POST['perms'][$role['id']] ?? [];
            foreach ($inputs as $permName => $val) {
                if ($val == '1') {
                    $perms[$permName] = true;
                }
            }
            $roleModel->updatePermissions($role['id'], json_encode($perms));
        }

        Flash::success("Matrice des permissions mise à jour avec succès !");
        $this->redirect('/roles/permissions');
    }

    public function gdprDashboard() {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }
        $this->view('users/gdpr');
    }

    public function gdprExport() {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect('/admin/gdpr');
        }

        $email = $_POST['email'] ?? '';
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            Flash::error("Aucun utilisateur trouvé avec cet email.");
            $this->redirect('/admin/gdpr');
        }

        $db = Database::getInstance();
        $logs = $db->query("SELECT * FROM activity_logs WHERE user_id = ?", [$user['id']])->fetchAll();
        $timeEntries = $db->query("SELECT * FROM time_entries WHERE user_id = ?", [$user['id']])->fetchAll();

        $gdprData = [
            'identity' => [
                'id' => $user['id'],
                'nom' => $user['nom'],
                'email' => $user['email'],
                'actif' => $user['actif'],
                'role' => $user['role_nom'],
                'date_creation' => $user['date_creation']
            ],
            'time_tracking' => $timeEntries,
            'audit_logs' => $logs
        ];

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="gdpr_export_' . $user['id'] . '.json"');
        echo json_encode($gdprData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function gdprDelete() {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect('/admin/gdpr');
        }

        $email = $_POST['email'] ?? '';
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user) {
            $userModel->delete($user['id']);
            Auth::logAudit(Auth::id(), 'RGPD_SUPPRESSION', 'users', $user['id'], "Suppression RGPD du compte : " . $email);
            Flash::success("Les données personnelles de l'utilisateur ont été supprimées définitivement (Droit à l'oubli).");
        } else {
            Flash::error("Utilisateur introuvable.");
        }
        $this->redirect('/admin/gdpr');
    }
}
