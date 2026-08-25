<?php
$title = "Dashboard principal";
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">Dashboard Spatial</h1>
        <p class="text-slate-400 text-sm mt-1">Vue consolidée de l'avancement et des indicateurs clés.</p>
    </div>
    <div class="flex gap-2">
        <?php if (\App\Core\Auth::hasPermission('projects.create')): ?>
            <a href="/projects/create" class="bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-3 px-5 rounded-xl hover:brightness-110 shadow-lg shadow-cyan-500/10 transition flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Nouveau Projet
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="glass p-6 rounded-2xl flex items-center gap-5 border border-slate-800/80">
        <div class="w-12 h-12 rounded-xl bg-cyan-950/40 border border-cyan-800/40 flex items-center justify-center">
            <i data-lucide="folder-git-2" class="w-6 h-6 text-cyan-400"></i>
        </div>
        <div>
            <span class="block text-slate-500 text-xs font-bold uppercase tracking-wider">Projets Actifs</span>
            <span class="text-3xl font-black text-cyan-400 leading-none"><?php echo $activeProjects; ?></span>
        </div>
    </div>

    <div class="glass p-6 rounded-2xl flex items-center gap-5 border border-slate-800/80">
        <div class="w-12 h-12 rounded-xl bg-red-950/40 border border-red-800/40 flex items-center justify-center">
            <i data-lucide="alert-octagon" class="w-6 h-6 text-red-400"></i>
        </div>
        <div>
            <span class="block text-slate-500 text-xs font-bold uppercase tracking-wider">Tâches en retard</span>
            <span class="text-3xl font-black text-red-400 leading-none"><?php echo $overdueTasks; ?></span>
        </div>
    </div>

    <div class="glass p-6 rounded-2xl flex items-center gap-5 border border-slate-800/80">
        <div class="w-12 h-12 rounded-xl bg-blue-950/40 border border-blue-800/40 flex items-center justify-center">
            <i data-lucide="percent" class="w-6 h-6 text-blue-400"></i>
        </div>
        <div>
            <span class="block text-slate-500 text-xs font-bold uppercase tracking-wider">Progression Moyenne</span>
            <span class="text-3xl font-black text-blue-400 leading-none"><?php echo $avgProgress; ?>%</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <div class="lg:col-span-2 space-y-8">

        <div class="glass p-6 rounded-3xl border border-slate-800">
            <h2 class="text-lg font-extrabold mb-4 flex items-center gap-2 text-cyan-400">
                <i data-lucide="bar-chart-3" class="w-5 h-5"></i> Progression par projet (SVG PHP)
            </h2>
            <?php if (empty($projects)): ?>
                <p class="text-slate-500 text-sm">Aucun projet disponible pour générer les statistiques.</p>
            <?php else: ?>
                <div class="w-full">
                    <svg viewBox="0 0 600 300" class="w-full bg-slate-950/40 rounded-2xl border border-slate-900 p-4">
                        <line x1="50" y1="50" x2="550" y2="50" stroke="#1e293b" stroke-dasharray="4 4" />
                        <line x1="50" y1="150" x2="550" y2="150" stroke="#1e293b" stroke-dasharray="4 4" />
                        <line x1="50" y1="250" x2="550" y2="250" stroke="#334155" stroke-width="1.5" />

                        <text x="40" y="55" fill="#64748b" font-size="10" text-anchor="end">100%</text>
                        <text x="40" y="155" fill="#64748b" font-size="10" text-anchor="end">50%</text>
                        <text x="40" y="255" fill="#64748b" font-size="10" text-anchor="end">0%</text>

                        <?php
                        $count = count($projects);
                        $spacing = 500 / $count;
                        $barWidth = min(35, $spacing * 0.5);
                        foreach ($projects as $idx => $p):
                            $x = 50 + ($idx * $spacing) + ($spacing / 2) - ($barWidth / 2);
                            $barHeight = ($p['progress_pct'] / 100) * 200;
                            $y = 250 - $barHeight;
                        ?>
                            <rect x="<?php echo $x; ?>" y="<?php echo $y; ?>" width="<?php echo $barWidth; ?>" height="<?php echo $barHeight; ?>" rx="6" fill="url(#cyanBlueGrad)" class="transition hover:brightness-125" />
                            <text x="<?php echo $x + ($barWidth / 2); ?>" y="<?php echo $y - 8; ?>" fill="#22d3ee" font-size="10" font-weight="bold" text-anchor="middle"><?php echo $p['progress_pct']; ?>%</text>
                            <text x="<?php echo $x + ($barWidth / 2); ?>" y="272" fill="#94a3b8" font-size="9" text-anchor="middle"><?php echo htmlspecialchars(strlen($p['titre']) > 12 ? substr($p['titre'], 0, 10) . '...' : $p['titre'], ENT_QUOTES, 'UTF-8'); ?></text>
                        <?php endforeach; ?>

                        <defs>
                            <linearGradient id="cyanBlueGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#22d3ee" />
                                <stop offset="100%" stop-color="#3b82f6" />
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-4">
            <h2 class="text-lg font-extrabold flex items-center gap-2 text-cyan-400">
                <i data-lucide="folders" class="w-5 h-5"></i> Vos Projets en cours
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($projects as $p): ?>
                    <div class="glass p-5 rounded-2xl border border-slate-800 hover:border-cyan-500/30 transition relative overflow-hidden flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start gap-4 mb-3">
                                <h3 class="font-bold text-base hover:text-cyan-400 transition">
                                    <a href="/projects/<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['titre'], ENT_QUOTES, 'UTF-8'); ?></a>
                                </h3>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-900 border border-slate-800 text-slate-300">
                                    <?php echo htmlspecialchars($p['statut'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>
                            <p class="text-slate-400 text-xs line-clamp-2 mb-4 leading-relaxed"><?php echo htmlspecialchars($p['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>

                        <div class="space-y-1.5 mt-auto">
                            <div class="flex justify-between text-[11px] font-bold text-slate-500">
                                <span>Avancement</span>
                                <span class="text-cyan-400"><?php echo $p['progress_pct']; ?>%</span>
                            </div>
                            <div class="w-full bg-slate-950 rounded-full h-2 overflow-hidden border border-slate-900">
                                <div class="bg-gradient-to-r from-cyan-400 to-blue-500 h-full rounded-full" style="width: <?php echo $p['progress_pct']; ?>%;"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <div class="lg:col-span-1 space-y-6">
        <div class="glass p-6 rounded-3xl border border-slate-800 h-full">
            <h2 class="text-lg font-extrabold mb-5 flex items-center gap-2 text-cyan-400">
                <i data-lucide="check-square" class="w-5 h-5"></i> Vos tâches assignées
            </h2>

            <?php if (empty($assignedTasks)): ?>
                <div class="text-center py-12 text-slate-500">
                    <i data-lucide="smile" class="w-10 h-10 mx-auto mb-3 text-slate-600"></i>
                    <p class="text-sm">Félicitations ! Vous n'avez aucune tâche assignée en suspens.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2">
                    <?php foreach ($assignedTasks as $t): ?>
                        <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-850 hover:border-cyan-500/20 transition space-y-2">
                            <div class="flex justify-between items-start gap-2">
                                <span class="text-[10px] text-cyan-400 font-bold uppercase tracking-wider block"><?php echo htmlspecialchars($t['project_titre'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="text-[9px] font-bold px-2 py-0.5 rounded <?php echo (strtotime($t['deadline']) < time()) ? 'bg-red-950/40 text-red-400 border border-red-800/40' : 'bg-slate-900 text-slate-400 border border-slate-800'; ?>">
                                    <?php echo htmlspecialchars($t['deadline'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>
                            <h4 class="font-bold text-sm text-slate-200"><?php echo htmlspecialchars($t['titre'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="text-xs text-slate-400 line-clamp-2"><?php echo htmlspecialchars($t['description'], ENT_QUOTES, 'UTF-8'); ?></p>

                            <div class="pt-2 flex justify-between items-center border-t border-slate-900">
                                <span class="text-[10px] bg-cyan-950/20 text-cyan-400 p-1 px-2 rounded font-bold border border-cyan-900/30">
                                    <?php echo htmlspecialchars($t['statut'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <a href="/projects/<?php echo $t['project_id']; ?>" class="text-[10px] font-bold text-slate-500 hover:text-cyan-400 transition flex items-center gap-1">
                                    Voir <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
