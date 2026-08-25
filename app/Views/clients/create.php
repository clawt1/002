<?php
use App\Core\Csrf;
$title = "Nouveau Client";
require __DIR__ . '/../partials/header.php';
?>

<div class="max-w-xl mx-auto glass p-8 rounded-3xl border border-slate-800">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">🏢 Enregistrer un Nouveau Client</h1>
        <p class="text-slate-400 text-sm">Configurez la charte graphique de la marque blanche client.</p>
    </div>

    <form action="/clients/store" method="POST" enctype="multipart/form-data" class="space-y-4">
        <?php echo Csrf::field(); ?>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Nom de l'entreprise</label>
            <input type="text" name="nom" placeholder="Acme Corporation" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Couleur primaire de marque</label>
                <input type="color" name="couleur_primaire" value="#10b981" class="w-full h-12 bg-slate-950 border border-slate-850 rounded-xl p-1 cursor-pointer">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Logo officiel (PNG / JPG)</label>
                <input type="file" name="logo" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-2.5 text-xs text-slate-400">
            </div>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-4 rounded-xl shadow-lg hover:brightness-110 transition mt-6">Enregistrer le client</button>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
