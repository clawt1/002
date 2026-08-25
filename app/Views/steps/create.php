<?php
use App\Core\Csrf;
$title = "Nouvelle Étape";
require __DIR__ . '/../partials/header.php';
?>

<div class="max-w-2xl mx-auto glass p-8 rounded-3xl border border-slate-800">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">📍 Ajouter une Étape de Roadmap</h1>
        <p class="text-slate-400 text-sm">Créez un jalon structurant pour suivre l'avancée.</p>
    </div>

    <form action="/steps/store" method="POST" class="space-y-4">
        <?php echo Csrf::field(); ?>
        <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project_id, ENT_QUOTES, 'UTF-8'); ?>">

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Titre du Jalon / de l'Étape</label>
            <input type="text" name="titre" placeholder="ex. V1.0 - Backend & API" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Description de l'Étape</label>
            <textarea name="description" placeholder="Objectifs et périmètre de cette étape..." rows="3" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Ordre de Passage</label>
                <input type="number" name="ordre" value="1" min="1" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Branche Git Associée (Optionnel)</label>
                <input type="text" name="branche_git" placeholder="ex. release-v1.0 or main" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Date d'Échéance (Deadline)</label>
            <input type="date" name="deadline" value="<?php echo date('Y-m-d', strtotime('+15 days')); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Definition of Done (DoD) - Un critère par ligne</label>
            <textarea name="dod_items" placeholder="ex. Code revu par un pair&#10;Tests unitaires validés&#10;Déploiement en préproduction OK" rows="4" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition"></textarea>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-4 rounded-xl shadow-lg hover:brightness-110 transition mt-6">Enregistrer l'étape</button>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
