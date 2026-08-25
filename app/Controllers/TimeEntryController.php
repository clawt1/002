<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Helpers\Flash;
use App\Models\TimeEntry;
use App\Models\Project;

class TimeEntryController extends Controller {
    public function store() {
        $taskId = intval($_POST['task_id'] ?? 0);
        $projectId = intval($_POST['project_id'] ?? 0);
        $duree = intval($_POST['duree_minutes'] ?? 0);
        $date = $_POST['date'] ?? date('Y-m-d');

        if (!$taskId || !$projectId || !$duree) {
            Flash::error("Veuillez remplir correctement la saisie de temps.");
            $this->redirect($projectId ? "/projects/{$projectId}" : "/");
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect("/projects/{$projectId}");
        }

        $timeModel = new TimeEntry();
        $timeModel->create([
            'task_id' => $taskId,
            'user_id' => Auth::id(),
            'date' => $date,
            'duree_minutes' => $duree,
            'description' => $_POST['description'] ?? '',
            'facturable' => 1
        ]);

        Auth::logAudit(Auth::id(), 'SAISIE_TEMPS', 'time_entries', 0, "Saisie de {$duree} minutes de travail.");
        Flash::success("Temps de travail enregistré avec succès !");
        $this->redirect("/projects/{$projectId}");
    }

    public function billing() {
        if (!Auth::hasPermission('all')) {
            $this->redirect('/');
        }

        $projectModel = new Project();
        $projects = $projectModel->all();

        $projectId = intval($_GET['project_id'] ?? ($projects[0]['id'] ?? 0));
        $dateStart = $_GET['date_debut'] ?? null;
        $dateEnd = $_GET['date_fin'] ?? null;

        $timeModel = new TimeEntry();
        $entries = [];
        $totalHours = 0;
        $totalBilling = 0;
        $rate = floatval($_GET['taux_horaire'] ?? 450);

        if ($projectId) {
            $entries = $timeModel->getBillingReport($projectId, $dateStart, $dateEnd);
            $totalMinutes = 0;
            foreach ($entries as $entry) {
                $totalMinutes += $entry['duree_minutes'];
            }
            $totalHours = round($totalMinutes / 60, 2);
            $hourlyRate = $rate / 7;
            $totalBilling = round($totalHours * $hourlyRate, 2);
        }

        $this->view('time/billing', [
            'projects' => $projects,
            'projectId' => $projectId,
            'entries' => $entries,
            'totalHours' => $totalHours,
            'totalBilling' => $totalBilling,
            'rate' => $rate,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd
        ]);
    }
}
