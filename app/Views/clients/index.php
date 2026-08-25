<?php
$title = "Clients";
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">🏢 Base de Clients</h1>
        <p class="text-slate-400 text-sm">Gérez les comptes clients pour la personnalisation en marque blanche.</p>
    </div>
    <a href="/clients/create" class="bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-3 px-6 rounded-xl hover:brightness-110 shadow-lg shadow-cyan-500/15 transition flex items-center gap-2">
        <i data-lucide="plus-circle" class="w-4 h-4"></i> Ajouter un Client
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($clients)): ?>
        <p class="text-slate-500 text-sm col-span-full">Aucun client enregistré.</p>
    <?php else: ?>
        <?php foreach ($clients as $c): ?>
            <div class="glass p-6 rounded-3xl border border-slate-800 flex items-center gap-5 relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1" style="background-color: <?php echo $c['couleur_primaire']; ?>;"></div>

                <?php if ($c['logo']): ?>
                    <img src="/uploads/<?php echo htmlspecialchars($c['logo'], ENT_QUOTES, 'UTF-8'); ?>" class="w-12 h-10 object-contain rounded">
                <?php else: ?>
                    <div class="w-12 h-10 rounded-lg flex items-center justify-center font-bold text-slate-950 text-sm shadow-inner" style="background-color: <?php echo $c['couleur_primaire']; ?>;">
                        <?php echo htmlspecialchars(strtoupper(substr($c['nom'], 0, 2)), ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                <div>
                    <h3 class="font-bold text-slate-200"><?php echo htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p class="text-xs text-slate-500">Code couleur : <?php echo htmlspecialchars($c['couleur_primaire'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
