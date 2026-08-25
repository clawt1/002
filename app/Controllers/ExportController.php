<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Project;
use App\Models\Task;

class ExportController extends Controller {
    public function projectCsv($id) {
        $projectModel = new Project();
        $project = $projectModel->find($id);
        if (!$project) {
            $this->redirect('/');
        }

        $taskModel = new Task();
        $tasks = $taskModel->allByProject($id);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="export_projet_' . $id . '.csv"');

        $output = fopen('php://output', 'w');

        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['ID Tâche', 'Jalon/Étape', 'Intitulé', 'Description', 'Estimation (JH)', 'Assigné', 'Statut', 'Livrable attendu']);

        foreach ($tasks as $t) {
            fputcsv($output, [
                $t['id'],
                $t['step_titre'],
                $t['titre'],
                $t['description'],
                $t['estimation_jours'],
                $t['assigne_nom'] ?: 'Non assigné',
                $t['statut'],
                $t['livrable']
            ]);
        }

        fclose($output);
        exit;
    }

    public function projectPdf($id) {
        $projectModel = new Project();
        $project = $projectModel->withProgress($id);
        if (!$project) {
            $this->redirect('/');
        }

        $taskModel = new Task();
        $tasks = $taskModel->allByProject($id);

        $this->view('projects/print', [
            'project' => $project,
            'tasks' => $tasks
        ]);
    }
}
