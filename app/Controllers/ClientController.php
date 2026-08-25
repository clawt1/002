<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Helpers\Flash;
use App\Models\Client;

class ClientController extends Controller {
    public function index() {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }
        $clientModel = new Client();
        $clients = $clientModel->all();
        $this->view('clients/index', ['clients' => $clients]);
    }

    public function create() {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }
        $this->view('clients/create');
    }

    public function store() {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect('/clients');
        }

        $nom = trim($_POST['nom'] ?? '');
        if (strlen($nom) < 2) {
            Flash::error("Le nom du client doit faire au moins 2 caractères.");
            $this->redirect('/clients/create');
        }

        $logoName = null;
        if (!empty($_FILES['logo']['name'])) {
            $file = $_FILES['logo'];
            $tmpPath = $file['tmp_name'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            // Validation extension
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif'])) {
                // Validation du type MIME
                $mimeType = mime_content_type($tmpPath);
                if (in_array($mimeType, ['image/png', 'image/jpeg', 'image/gif'])) {
                    $uploadDir = __DIR__ . '/../../public_html/uploads';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $logoName = 'logo_' . bin2hex(random_bytes(8)) . '.' . $ext;
                    move_uploaded_file($tmpPath, $uploadDir . '/' . $logoName);
                }
            }
        }

        $clientModel = new Client();
        $clientModel->create([
            'nom' => $nom,
            'logo' => $logoName,
            'couleur_primaire' => $_POST['couleur_primaire'] ?? '#3b82f6'
        ]);

        Flash::success("Client enregistré !");
        $this->redirect('/clients');
    }
}
