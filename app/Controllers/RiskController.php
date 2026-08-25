<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Helpers\Flash;
use App\Models\Risk;

class RiskController extends Controller {
    public function store() {
        $projectId = intval($_POST['project_id'] ?? 0);
        if (!$projectId) {
            $this->redirect('/');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect("/projects/{$projectId}");
        }

        $riskModel = new Risk();
        $riskModel->create([
            'project_id' => $projectId,
            'description' => $_POST['description'] ?? '',
            'probabilite' => $_POST['probabilite'] ?? 'Faible',
            'impact' => $_POST['impact'] ?? 'Faible',
            'statut' => 'Identifié'
        ]);

        Auth::logAudit(Auth::id(), 'CREATION_RISQUE', 'risks', 0, "Ajout d'un risque sur le projet ID : " . $projectId);
        Flash::success("Risque ajouté au registre !");
        $this->redirect("/projects/{$projectId}");
    }

    public function destroy($id) {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }

        $riskModel = new Risk();
        $riskModel->delete($id);

        Flash::success("Risque supprimé !");
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}
