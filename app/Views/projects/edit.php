<?php
use App\Core\Csrf;
$title = "Modifier le Projet";
require __DIR__ . '/../partials/header.php';
?>

<div class="max-w-2xl mx-auto glass p-8 rounded-3xl border border-slate-800">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">⚙️ Modifier le Projet</h1>
            <p class="text-slate-400 text-sm">Modifiez les paramètres globaux de la roadmap.</p>
        </div>
        <form action="/projects/<?php echo $project['id']; ?>/delete" method="POST" onsubmit="return confirm('Êtes-vous absolument sûr de vouloir supprimer ce projet ainsi que toutes ses étapes et ses tâches ? Cette action est irréversible.');">
            <?php echo Csrf::field(); ?>
            <button type="submit" class="bg-red-950/20 hover:bg-red-950/40 border border-red-900/60 text-red-400 font-bold p-2 px-4 rounded-xl text-xs transition">Supprimer</button>
        </form>
    </div>

    <form action="/projects/<?php echo $project['id']; ?>/update" method="POST" class="space-y-4">
        <?php echo Csrf::field(); ?>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Titre du Projet</label>
            <input type="text" name="titre" value="<?php echo htmlspecialchars($project['titre'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Description</label>
            <textarea name="description" rows="4" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition"><?php echo htmlspecialchars($project['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Client Associé</label>
                <select name="client_id" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition">
                    <option value="">Aucun (Projet Interne)</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $c['id'] == $project['client_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Statut</label>
                <select name="statut" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition">
                    <?php foreach (['Planifié', 'En cours', 'En pause', 'Livré', 'Annulé'] as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo $st == $project['statut'] ? 'selected' : ''; ?>><?php echo $st; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Date de Début</label>
                <input type="date" name="date_debut" value="<?php echo htmlspecialchars($project['date_debut'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Date de Fin Prévue</label>
                <input type="date" name="date_fin_prevue" value="<?php echo htmlspecialchars($project['date_fin_prevue'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
            </div>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-4 rounded-xl shadow-lg hover:brightness-110 transition mt-6">Mettre à jour le projet</button>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
