<?php
use App\Core\Csrf;
$title = "Nouvelle Tâche";
require __DIR__ . '/../partials/header.php';
?>

<div class="max-w-2xl mx-auto glass p-8 rounded-3xl border border-slate-800">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">📝 Créer une Nouvelle Tâche</h1>
        <p class="text-slate-400 text-sm">Définissez les livrables techniques et exigences de sécurité.</p>
    </div>

    <form action="/tasks/store" method="POST" class="space-y-4">
        <?php echo Csrf::field(); ?>
        <input type="hidden" name="step_id" value="<?php echo htmlspecialchars($step['id'], ENT_QUOTES, 'UTF-8'); ?>">

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Intitulé de la Tâche</label>
            <input type="text" name="titre" placeholder="ex. Création de l'interface de connexion" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Description détaillée</label>
            <textarea name="description" placeholder="Présentez le travail à accomplir..." rows="3" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Estimation en Jours-Homme (JH)</label>
                <input type="number" step="0.25" name="estimation_jours" value="1.0" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Assigner la tâche à</label>
                <select name="assigne_id" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition">
                    <option value="">Non assignée (En attente)</option>
                    <?php foreach ($developers as $dev): ?>
                        <option value="<?php echo $dev['id']; ?>"><?php echo htmlspecialchars($dev['nom'] . ' (' . $dev['role_nom'] . ')', ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="space-y-4 pt-4 border-t border-slate-900/60">
            <h3 class="text-sm font-black uppercase text-cyan-400 tracking-wider">🔒 Critères techniques & Sécurité</h3>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Livrable(s) attendu(s)</label>
                <input type="text" name="livrable" placeholder="ex. Fichier AuthController.php + Vue login.php" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Critères d'acceptation</label>
                <textarea name="criteres_acceptation" placeholder="ex. Le formulaire rejette les identifiants invalides." rows="2" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Exigences de Sécurité</label>
                    <textarea name="exigences_securite" placeholder="ex. Hashage BCRYPT systématique des mots de passe." rows="2" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Plan de Tests Préconisés</label>
                    <textarea name="tests" placeholder="ex. Injection SQL de démonstration, Brute force." rows="2" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition"></textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-4 rounded-xl shadow-lg hover:brightness-110 transition mt-6">Créer la tâche</button>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
