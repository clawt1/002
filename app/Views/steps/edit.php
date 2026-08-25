<?php
use App\Core\Csrf;
$title = "Modifier l'Étape";
require __DIR__ . '/../partials/header.php';

$dodList = json_decode($step['definition_of_done'] ?? '[]', true) ?: [];
$dodText = implode("\n", $dodList);
?>

<div class="max-w-2xl mx-auto glass p-8 rounded-3xl border border-slate-800">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">⚙️ Modifier l'Étape</h1>
            <p class="text-slate-400 text-sm">Modifiez les jalons d'avancement du projet.</p>
        </div>
        <form action="/steps/<?php echo $step['id']; ?>/delete" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cette étape ? Cela supprimera également toutes les tâches associées.');">
            <?php echo Csrf::field(); ?>
            <button type="submit" class="bg-red-950/25 hover:bg-red-950/40 border border-red-900/60 text-red-400 font-bold p-2 px-4 rounded-xl text-xs transition">Supprimer</button>
        </form>
    </div>

    <form action="/steps/<?php echo $step['id']; ?>/update" method="POST" class="space-y-4">
        <?php echo Csrf::field(); ?>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Titre de l'Étape</label>
            <input type="text" name="titre" value="<?php echo htmlspecialchars($step['titre'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Description de l'Étape</label>
            <textarea name="description" rows="3" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition"><?php echo htmlspecialchars($step['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Ordre de Passage</label>
                <input type="number" name="ordre" value="<?php echo htmlspecialchars($step['ordre'] ?? '1', ENT_QUOTES, 'UTF-8'); ?>" min="1" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Branche Git Associée (Optionnel)</label>
                <input type="text" name="branche_git" value="<?php echo htmlspecialchars($step['branche_git'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Date d'Échéance (Deadline)</label>
                <input type="date" name="deadline" value="<?php echo htmlspecialchars($step['deadline'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Statut</label>
                <select name="statut" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition">
                    <option value="À faire" <?php echo $step['statut'] === 'À faire' ? 'selected' : ''; ?>>À faire</option>
                    <option value="En cours" <?php echo $step['statut'] === 'En cours' ? 'selected' : ''; ?>>En cours</option>
                    <option value="Terminé" <?php echo $step['statut'] === 'Terminé' ? 'selected' : ''; ?>>Terminé</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Definition of Done (DoD) - Un critère par ligne</label>
            <textarea name="dod_items" rows="4" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition"><?php echo htmlspecialchars($dodText, ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-4 rounded-xl shadow-lg hover:brightness-110 transition mt-6">Enregistrer les modifications</button>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
