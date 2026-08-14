<?php
use App\Core\Csrf;
$title = "Conformité RGPD";
require __DIR__ . '/../partials/header.php';
?>

<div class="max-w-3xl mx-auto space-y-8">
    <div>
        <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">🛡️ Registre de Conformité RGPD</h1>
        <p class="text-slate-400 text-sm mt-1">Exercez les obligations de confidentialité, de portabilité des données et le droit à l'oubli.</p>
    </div>

    <!-- GDPR Exporter (Portability) -->
    <div class="glass p-8 rounded-3xl border border-slate-800 space-y-4">
        <h2 class="text-lg font-black text-cyan-400 flex items-center gap-2">
            <i data-lucide="download" class="w-5 h-5"></i> Portabilité des Données (Export JSON)
        </h2>
        <p class="text-xs text-slate-400">Téléchargez l'intégralité des informations personnelles, des relevés d'activité et des journaux de logs d'un collaborateur au format standardisé JSON.</p>

        <form action="/admin/gdpr/export" method="POST" class="flex gap-3">
            <?php echo Csrf::field(); ?>
            <input type="email" name="email" placeholder="Saisir l'adresse email de l'utilisateur..." class="flex-grow bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 text-xs focus:outline-none focus:border-cyan-500" required>
            <button type="submit" class="bg-cyan-950/40 hover:bg-cyan-950/60 border border-cyan-800/40 text-cyan-400 font-bold p-3 px-6 rounded-xl text-xs transition">Générer l'export</button>
        </form>
    </div>

    <!-- GDPR Eraser (Right to be Forgotten) -->
    <div class="glass p-8 rounded-3xl border border-slate-800 border-red-900/30 space-y-4 relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-red-500/5 rounded-full blur-2xl"></div>

        <h2 class="text-lg font-black text-red-400 flex items-center gap-2">
            <i data-lucide="trash-2" class="w-5 h-5"></i> Droit à l'oubli (Suppression Définitive)
        </h2>
        <p class="text-xs text-slate-400">Cette procédure supprime définitivement toutes les données personnelles du collaborateur de la base de données. <strong>Cette action est immédiate, totale et irréversible.</strong></p>

        <form action="/admin/gdpr/delete" method="POST" class="flex gap-3" onsubmit="return confirm('ÊTES-VOUS ABSOLUMENT SÛR ? La suppression définitive de toutes les données personnelles est irréversible et détruira le compte associé.');">
            <?php echo Csrf::field(); ?>
            <input type="email" name="email" placeholder="Saisir l'adresse email de l'utilisateur à effacer..." class="flex-grow bg-slate-950 border border-red-900/20 rounded-xl p-3 text-slate-100 text-xs focus:outline-none focus:border-red-500" required>
            <button type="submit" class="bg-red-950/40 hover:bg-red-950/60 border border-red-800/40 text-red-400 font-bold p-3 px-6 rounded-xl text-xs transition">Effacer les données</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
