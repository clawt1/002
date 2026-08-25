<?php
use App\Core\Csrf;
$title = "Nouveau Projet";
require __DIR__ . '/../partials/header.php';
?>

<div class="max-w-2xl mx-auto glass p-8 rounded-3xl border border-slate-800">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">🚀 Lancer un Nouveau Projet</h1>
        <p class="text-slate-400 text-sm">Définissez les paramètres initiaux de votre roadmap.</p>
    </div>

    <form action="/projects/store" method="POST" class="space-y-4">
        <?php echo Csrf::field(); ?>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Titre du Projet</label>
            <input type="text" name="titre" placeholder="ex. Refonte de l'API e-commerce" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Description</label>
            <textarea name="description" placeholder="Présentez les objectifs de ce projet..." rows="4" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Client Associé (Optionnel)</label>
                <select name="client_id" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition">
                    <option value="">Aucun (Projet Interne)</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Chef de Projet (Propriétaire)</label>
                <select name="owner_id" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition">
                    <?php foreach ($users as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo $u['id'] == \App\Core\Auth::id() ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['nom'] . ' (' . $u['role_nom'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Date de Début</label>
                <input type="date" name="date_debut" value="<?php echo date('Y-m-d'); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Date de Fin Prévue</label>
                <input type="date" name="date_fin_prevue" value="<?php echo date('Y-m-d', strtotime('+3 months')); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
            </div>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-4 rounded-xl shadow-lg shadow-cyan-500/10 hover:brightness-110 transition mt-6">Créer le projet et démarrer la roadmap</button>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
