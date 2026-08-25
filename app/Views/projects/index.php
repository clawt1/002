<?php
$title = "Tous les Projets";
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">🌌 Liste des Projets</h1>
        <p class="text-slate-400 text-sm mt-1">Explorez toutes les constellations de projets en développement.</p>
    </div>
    <?php if (\App\Core\Auth::hasPermission('projects.create')): ?>
        <a href="/projects/create" class="bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-3 px-6 rounded-xl hover:brightness-110 shadow-lg shadow-cyan-500/15 transition flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Créer un Projet
        </a>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php if (empty($projects)): ?>
        <div class="col-span-full text-center py-24 glass rounded-3xl border border-slate-800">
            <i data-lucide="rocket" class="w-12 h-12 text-slate-650 mx-auto mb-4 animate-bounce"></i>
            <p class="text-slate-500 font-bold">Aucun projet n'a encore été lancé.</p>
            <?php if (\App\Core\Auth::hasPermission('projects.create')): ?>
                <a href="/projects/create" class="text-cyan-400 hover:underline text-sm font-bold mt-2 inline-block">Créer le tout premier maintenant !</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($projects as $p): ?>
            <div class="glass p-6 rounded-3xl border border-slate-800 flex flex-col justify-between hover:border-cyan-500/30 transition shadow-xl relative group">
                <div class="absolute top-0 left-0 right-0 h-1.5" style="background-color: <?php echo $p['client_couleur'] ?: '#0891b2'; ?>;"></div>

                <div class="pt-2">
                    <div class="flex justify-between items-start gap-4 mb-3">
                        <span class="text-[10px] uppercase font-black tracking-widest text-slate-500 flex items-center gap-1">
                            <i data-lucide="building" class="w-3 h-3"></i> <?php echo htmlspecialchars($p['client_nom'] ?: 'Interne', ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider bg-slate-950/80 border border-slate-850 text-slate-300">
                            <?php echo htmlspecialchars($p['statut'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-slate-100 mb-2 group-hover:text-cyan-400 transition">
                        <a href="/projects/<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['titre'], ENT_QUOTES, 'UTF-8'); ?></a>
                    </h3>

                    <p class="text-slate-400 text-xs line-clamp-3 mb-6 leading-relaxed"><?php echo htmlspecialchars($p['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>

                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-[11px] font-bold text-slate-500">
                            <span>Avancement général</span>
                            <span class="text-cyan-400"><?php echo $p['progress_pct']; ?>%</span>
                        </div>
                        <div class="w-full bg-slate-950 rounded-full h-2 overflow-hidden border border-slate-900">
                            <div class="bg-gradient-to-r from-cyan-400 to-blue-500 h-full rounded-full" style="width: <?php echo $p['progress_pct']; ?>%;"></div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-900 flex justify-between items-center text-[10px] text-slate-550 font-bold">
                        <span class="flex items-center gap-1">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-650"></i> Fin : <?php echo htmlspecialchars($p['date_fin_prevue'] ?: 'Indéterminée', ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <a href="/projects/<?php echo $p['id']; ?>" class="text-cyan-400 hover:brightness-110 flex items-center gap-1 font-bold">
                            Consulter <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
