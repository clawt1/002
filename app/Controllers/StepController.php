<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Helpers\Flash;
use App\Helpers\Validator;
use App\Models\Step;

class StepController extends Controller {
    public function create($projectId) {
        if (!Auth::hasPermission('steps.create') && !Auth::hasPermission('all')) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }
        $this->view('steps/create', ['project_id' => $projectId]);
    }

    public function store() {
        if (!Auth::hasPermission('steps.create') && !Auth::hasPermission('all')) {
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
            'project_id' => ['required'],
            'titre' => ['required', 'min:3', 'max:150'],
            'deadline' => ['required']
        ]);

        if (!$validated) {
            foreach ($validator->getErrors() as $errs) {
                foreach ($errs as $err) {
                    Flash::error($err);
                }
            }
            $this->redirect("/projects/{$_POST['project_id']}");
        }

        $dod = [];
        if (!empty($_POST['dod_items'])) {
            $items = explode("\n", $_POST['dod_items']);
            foreach ($items as $item) {
                $trimmed = trim($item);
                if (!empty($trimmed)) {
                    $dod[] = $trimmed;
                }
            }
        }
        $_POST['definition_of_done'] = json_encode($dod);

        $stepModel = new Step();
        $newId = $stepModel->create($_POST);

        Auth::logAudit(Auth::id(), 'CREATION_ETAPE', 'steps', $newId, "Création de l'étape : " . $_POST['titre']);
        Flash::success("Étape ajoutée avec succès !");
        $this->redirect("/projects/{$_POST['project_id']}");
    }

    public function edit($id) {
        if (!Auth::hasPermission('steps.create') && !Auth::hasPermission('all')) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }

        $stepModel = new Step();
        $step = $stepModel->find($id);
        if (!$step) {
            $this->redirect('/');
        }
        $this->view('steps/edit', ['step' => $step]);
    }

    public function update($id) {
        if (!Auth::hasPermission('steps.create') && !Auth::hasPermission('all')) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }

        $stepModel = new Step();
        $step = $stepModel->find($id);
        if (!$step) {
            $this->redirect('/');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect("/projects/{$step['project_id']}");
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
            $this->redirect("/steps/{$id}/edit");
        }

        $dod = [];
        if (!empty($_POST['dod_items'])) {
            $items = explode("\n", $_POST['dod_items']);
            foreach ($items as $item) {
                $trimmed = trim($item);
                if (!empty($trimmed)) {
                    $dod[] = $trimmed;
                }
            }
        }
        $_POST['definition_of_done'] = json_encode($dod);

        $stepModel->update($id, $_POST);

        Auth::logAudit(Auth::id(), 'MODIFICATION_ETAPE', 'steps', $id, "Modification de l'étape : " . $_POST['titre']);
        Flash::success("Étape mise à jour !");
        $this->redirect("/projects/{$step['project_id']}");
    }

    public function destroy($id) {
        if (!Auth::hasPermission('projects.delete') && !Auth::hasPermission('all')) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }

        $stepModel = new Step();
        $step = $stepModel->find($id);
        if ($step) {
            $stepModel->delete($id);
            Auth::logAudit(Auth::id(), 'SUPPRESSION_ETAPE', 'steps', $id, "Suppression de l'étape : " . $step['titre']);
            Flash::success("Étape supprimée !");
            $this->redirect("/projects/{$step['project_id']}");
        } else {
            $this->redirect('/');
        }
    }

    public function toggleStatus($id) {
        if (!Auth::hasPermission('steps.create') && !Auth::hasPermission('all') && Auth::user()['role_nom'] !== 'Apprenant') {
            return $this->json(['success' => false, 'message' => "Accès non autorisé"], 403);
        }

        $stepModel = new Step();
        $step = $stepModel->find($id);
        if (!$step) {
            return $this->json(['success' => false, 'message' => "Étape introuvable"], 404);
        }

        $newStatus = ($step['statut'] === 'Terminé') ? 'À faire' : 'Terminé';
        $stepModel->updateStatus($id, $newStatus);

        Auth::logAudit(Auth::id(), 'TOGGLE_ETAPE', 'steps', $id, "Changement de statut de l'étape {$step['titre']} vers {$newStatus}");

        return $this->json([
            'success' => true,
            'newStatus' => $newStatus,
            'message' => "Statut de l'étape mis à jour."
        ]);
    }

    public function grade($id) {
        if (!Auth::hasPermission('steps.grade') && Auth::user()['role_nom'] !== 'Formateur') {
            Flash::error("Seuls les formateurs peuvent évaluer les étapes.");
            $this->redirect('/');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect('/');
        }

        $stepModel = new Step();
        $step = $stepModel->find($id);
        if (!$step) {
            $this->redirect('/');
        }

        $note = intval($_POST['note'] ?? 0);
        $commentaire = $_POST['commentaire_formateur'] ?? '';

        $stepModel->grade($id, $note, $commentaire);

        Auth::logAudit(Auth::id(), 'EVALUATION_ETAPE', 'steps', $id, "Évaluation de l'étape {$step['titre']} avec la note {$note}/20");
        Flash::success("Étape évaluée avec succès !");
        $this->redirect("/projects/{$step['project_id']}");
    }
}
