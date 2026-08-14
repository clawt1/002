<?php
use App\Core\Csrf;
$title = "Créer un Compte";
require __DIR__ . '/../partials/header.php';
?>

<div class="max-w-xl mx-auto glass p-8 rounded-3xl border border-slate-800">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">👥 Nouveau Collaborateur</h1>
        <p class="text-slate-400 text-sm">Enregistrez un nouveau compte utilisateur dans le système.</p>
    </div>

    <form action="/users/store" method="POST" class="space-y-4">
        <?php echo Csrf::field(); ?>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Nom complet</label>
            <input type="text" name="nom" placeholder="Jeanne dev" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition text-xs" required>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Adresse Email</label>
            <input type="email" name="email" placeholder="jeanne@example.com" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition text-xs" required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Mot de passe de départ</label>
                <input type="password" name="password" placeholder="••••••••" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition text-xs" required>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Rôle global</label>
                <select name="role_id" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition text-xs">
                    <?php foreach ($roles as $r): ?>
                        <option value="<?php echo $r['id']; ?>" <?php echo $r['id'] == 3 ? 'selected' : ''; ?>><?php echo htmlspecialchars($r['nom'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-4 rounded-xl shadow-lg hover:brightness-110 transition mt-6 text-xs uppercase">Enregistrer l'utilisateur</button>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
