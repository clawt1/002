<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Helpers\Flash;
use App\Helpers\Validator;
use App\Models\Project;
use App\Models\Step;
use App\Models\Task;
use App\Models\Client;
use App\Models\Risk;
use App\Models\User;

class ProjectController extends Controller {
    public function index() {
        $projectModel = new Project();
        $projects = $projectModel->all();
        $this->view('projects/index', ['projects' => $projects]);
    }

    public function show($id) {
        $projectModel = new Project();
        $project = $projectModel->withProgress($id);
        if (!$project) {
            $this->redirect('/projects');
        }

        $stepModel = new Step();
        $steps = $stepModel->allByProject($id);

        $taskModel = new Task();
        $tasks = $taskModel->allByProject($id);

        $tasksByStep = [];
        foreach ($tasks as $t) {
            $tasksByStep[$t['step_id']][] = $t;
        }

        $riskModel = new Risk();
        $risks = $riskModel->allByProject($id);

        $userModel = new User();
        $developers = $userModel->all();

        $this->view('projects/show', [
            'project' => $project,
            'steps' => $steps,
            'tasksByStep' => $tasksByStep,
            'risks' => $risks,
            'developers' => $developers
        ]);
    }

    public function create() {
        if (!Auth::hasPermission('projects.create')) {
            $this->redirect('/');
        }
        $clientModel = new Client();
        $clients = $clientModel->all();

        $userModel = new User();
        $users = $userModel->all();

        $this->view('projects/create', [
            'clients' => $clients,
            'users' => $users
        ]);
    }

    public function store() {
        if (!Auth::hasPermission('projects.create')) {
            $this->redirect('/');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect('/projects/create');
        }

        $validator = new Validator();
        $validated = $validator->validate($_POST, [
            'titre' => ['required', 'min:3', 'max:150'],
            'date_debut' => ['required'],
            'date_fin_prevue' => ['required']
        ]);

        if (!$validated) {
            foreach ($validator->getErrors() as $errs) {
                foreach ($errs as $err) {
                    Flash::error($err);
                }
            }
            $this->redirect('/projects/create');
        }

        $projectModel = new Project();
        $newId = $projectModel->create($_POST);

        Auth::logAudit(Auth::id(), 'CREATION_PROJET', 'projects', $newId, "Création du projet : " . $_POST['titre']);
        Flash::success("Projet créé avec succès !");
        $this->redirect("/projects/{$newId}");
    }

    public function edit($id) {
        if (!Auth::hasPermission('projects.edit')) {
            $this->redirect('/');
        }

        $projectModel = new Project();
        $project = $projectModel->find($id);
        if (!$project) {
            $this->redirect('/projects');
        }

        $clientModel = new Client();
        $clients = $clientModel->all();

        $userModel = new User();
        $users = $userModel->all();

        $this->view('projects/edit', [
            'project' => $project,
            'clients' => $clients,
            'users' => $users
        ]);
    }

    public function update($id) {
        if (!Auth::hasPermission('projects.edit')) {
            $this->redirect('/');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect("/projects/{$id}/edit");
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
            $this->redirect("/projects/{$id}/edit");
        }

        $projectModel = new Project();
        $projectModel->update($id, $_POST);

        Auth::logAudit(Auth::id(), 'MODIFICATION_PROJET', 'projects', $id, "Modification du projet : " . $_POST['titre']);
        Flash::success("Projet mis à jour !");
        $this->redirect("/projects/{$id}");
    }

    public function destroy($id) {
        if (!Auth::hasPermission('projects.delete')) {
            $this->redirect('/');
        }

        $projectModel = new Project();
        $project = $projectModel->find($id);
        if ($project) {
            $projectModel->delete($id);
            Auth::logAudit(Auth::id(), 'SUPPRESSION_PROJET', 'projects', $id, "Suppression du projet : " . $project['titre']);
            Flash::success("Projet supprimé !");
        }
        $this->redirect('/projects');
    }

    public function duplicate($id) {
        $projectModel = new Project();
        $newId = $projectModel->duplicateProjectForUser($id, Auth::id());
        if ($newId) {
            Flash::success("Projet dupliqué avec succès pour l'évaluation !");
            $this->redirect("/projects/{$newId}");
        } else {
            Flash::error("Impossible de dupliquer ce projet.");
            $this->redirect('/projects');
        }
    }

    public function publicPortal($token) {
        $projectModel = new Project();
        $project = $projectModel->findByToken($token);
        if (!$project) {
            die("Lien d'accès public invalide ou expiré.");
        }

        $stepModel = new Step();
        $steps = $stepModel->allByProject($project['id']);

        $taskModel = new Task();
        $tasks = $taskModel->allByProject($project['id']);

        $tasksByStep = [];
        foreach ($tasks as $t) {
            $tasksByStep[$t['step_id']][] = $t;
        }

        $totalTasks = count($tasks);
        $completedTasks = count(array_filter($tasks, function($t) { return $t['statut'] === 'Terminé'; }));
        $progressPct = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        $this->view('projects/portal', [
            'project' => $project,
            'steps' => $steps,
            'tasksByStep' => $tasksByStep,
            'progressPct' => $progressPct
        ]);
    }
}
