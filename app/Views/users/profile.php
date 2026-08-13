<?php
use App\Core\Csrf;
$title = "Mon Profil";
require __DIR__ . '/../partials/header.php';
?>

<div class="max-w-2xl mx-auto glass p-8 rounded-3xl border border-slate-800 space-y-6">
    <div class="border-b border-slate-900 pb-4">
        <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">🌌 Votre Profil d'Équipage</h1>
        <p class="text-slate-400 text-sm">Gérez vos identifiants spatiaux et votre avatar d'identification.</p>
    </div>

    <form action="/profile" method="POST" enctype="multipart/form-data" class="space-y-6">
        <?php echo Csrf::field(); ?>

        <!-- Avatar Selection with preview & explanation -->
        <div class="flex flex-col sm:flex-row items-center gap-6 bg-slate-950/40 p-4 rounded-2xl border border-slate-900">
            <div class="relative group">
                <?php if ($user['avatar']): ?>
                    <img src="/uploads/<?php echo htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8'); ?>" class="w-24 h-24 rounded-2xl object-cover border border-cyan-800/40">
                <?php else: ?>
                    <div class="w-24 h-24 rounded-2xl bg-cyan-950/30 border border-cyan-800/40 flex items-center justify-center font-black text-cyan-400 text-3xl">
                        <?php echo htmlspecialchars(strtoupper(substr($user['nom'], 0, 2)), ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="space-y-1.5 text-center sm:text-left flex-grow">
                <label class="block text-xs font-bold uppercase tracking-wider text-cyan-400">Photo d'identité (Avatar)</label>
                <input type="file" name="avatar" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-950/40 file:text-cyan-400 hover:file:bg-cyan-950/60 file:cursor-pointer">
                <span class="block text-[10px] text-slate-550 leading-tight">Redimensionné automatiquement à <strong>150x150 pixels</strong> côté serveur (via extension GD PHP) pour un affichage optimal et des performances maximales.</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Nom complet</label>
                <input type="text" name="nom" value="<?php echo htmlspecialchars($user['nom'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition text-xs" required>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Adresse Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition text-xs" required>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Modifier le mot de passe (Laisser vide pour ne pas changer)</label>
            <input type="password" name="password" placeholder="••••••••" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition text-xs">
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-4 rounded-xl shadow-lg hover:brightness-110 transition mt-6 text-xs uppercase">Enregistrer mon profil</button>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
