<?php
use App\Core\Csrf;
$title = "Modifier la Tâche";
require __DIR__ . '/../partials/header.php';
?>

<div class="max-w-2xl mx-auto glass p-8 rounded-3xl border border-slate-800">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">⚙️ Modifier la Tâche</h1>
            <p class="text-slate-400 text-sm">Ajustez les livrables et attributions de cette tâche.</p>
        </div>
        <form action="/tasks/<?php echo $task['id']; ?>/delete" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?');">
            <?php echo Csrf::field(); ?>
            <button type="submit" class="bg-red-950/25 hover:bg-red-950/40 border border-red-900/60 text-red-400 font-bold p-2 px-4 rounded-xl text-xs transition">Supprimer</button>
        </form>
    </div>

    <form action="/tasks/<?php echo $task['id']; ?>/update" method="POST" class="space-y-4">
        <?php echo Csrf::field(); ?>
        <input type="hidden" name="step_id" value="<?php echo htmlspecialchars($task['step_id'], ENT_QUOTES, 'UTF-8'); ?>">

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Intitulé de la Tâche</label>
            <input type="text" name="titre" value="<?php echo htmlspecialchars($task['titre'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Description détaillée</label>
            <textarea name="description" rows="3" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition"><?php echo htmlspecialchars($task['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Estimation (JH)</label>
                <input type="number" step="0.25" name="estimation_jours" value="<?php echo htmlspecialchars($task['estimation_jours'] ?? '1.0', ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Attribuer à</label>
                <select name="assigne_id" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition">
                    <option value="">Non assignée</option>
                    <?php foreach ($developers as $dev): ?>
                        <option value="<?php echo $dev['id']; ?>" <?php echo $dev['id'] == $task['assigne_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($dev['nom'] . ' (' . $dev['role_nom'] . ')', ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Statut</label>
                <select name="statut" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition">
                    <?php foreach (['À faire', 'En cours', 'En revue', 'Terminé'] as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo $st == $task['statut'] ? 'selected' : ''; ?>><?php echo $st; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="space-y-4 pt-4 border-t border-slate-900/60">
            <h3 class="text-sm font-black uppercase text-cyan-400 tracking-wider">🔒 Critères techniques & Sécurité</h3>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Livrable(s) attendu(s)</label>
                <input type="text" name="livrable" value="<?php echo htmlspecialchars($task['livrable'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Critères d'acceptation</label>
                <textarea name="criteres_acceptation" rows="2" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition"><?php echo htmlspecialchars($task['criteres_acceptation'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Exigences de Sécurité</label>
                    <textarea name="exigences_securite" rows="2" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition"><?php echo htmlspecialchars($task['exigences_securite'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Plan de Tests Préconisés</label>
                    <textarea name="tests" rows="2" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition"><?php echo htmlspecialchars($task['tests'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-4 rounded-xl shadow-lg hover:brightness-110 transition mt-6">Mettre à jour la tâche</button>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
