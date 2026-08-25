<?php
$clientColor = $project['client_couleur'] ?? '#3b82f6';
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Portail Client - <?php echo htmlspecialchars($project['titre'], ENT_QUOTES, 'UTF-8'); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            background-color: #020617;
            background-image: radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 0);
            background-size: 32px 32px;
        }
        .glass {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .brand-text {
            color: <?php echo $clientColor; ?>;
        }
        .brand-bg {
            background-color: <?php echo $clientColor; ?>;
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen flex flex-col font-sans">

    <header class="glass py-5 px-6 border-b border-slate-800">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <?php if ($project['client_logo']): ?>
                    <img src="/uploads/<?php echo htmlspecialchars($project['client_logo'], ENT_QUOTES, 'UTF-8'); ?>" class="h-10 object-contain">
                <?php else: ?>
                    <div class="w-10 h-10 rounded-xl brand-bg flex items-center justify-center font-black text-slate-950 text-lg shadow-lg">
                        <?php echo htmlspecialchars(strtoupper(substr($project['client_nom'] ?: 'CP', 0, 2)), ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                <div>
                    <span class="text-xs text-slate-500 font-bold uppercase tracking-widest">Espace Client Partagé</span>
                    <h1 class="text-lg font-black tracking-tight text-slate-200"><?php echo htmlspecialchars($project['client_nom'] ?: 'Interne', ENT_QUOTES, 'UTF-8'); ?></h1>
                </div>
            </div>

            <span class="text-xs font-bold text-slate-400 bg-slate-950 border border-slate-850 p-2 py-1.5 rounded-xl flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full brand-bg"></span> Lecture Seule
            </span>
        </div>
    </header>

    <main class="max-w-6xl w-full mx-auto p-6 md:p-8 flex-grow space-y-8">

        <div class="glass p-8 rounded-3xl border border-slate-800/80 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
            <div class="space-y-2 max-w-xl">
                <h2 class="text-2xl font-black text-slate-100 leading-tight"><?php echo htmlspecialchars($project['titre'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="text-slate-400 text-sm"><?php echo htmlspecialchars($project['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <div class="glass p-5 rounded-2xl border border-slate-850 w-full md:w-72 flex flex-col justify-between">
                <div class="flex justify-between items-center mb-2 text-xs font-bold text-slate-550 uppercase">
                    <span>Avancement</span>
                    <span class="brand-text"><?php echo $progressPct; ?>%</span>
                </div>
                <div class="w-full bg-slate-950 rounded-full h-2 overflow-hidden border border-slate-900">
                    <div class="brand-bg h-full rounded-full" style="width: <?php echo $progressPct; ?>%;"></div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <h3 class="text-lg font-extrabold text-slate-200 flex items-center gap-2">
                <i data-lucide="milestone" class="w-5 h-5 brand-text"></i> Jalons de développement
            </h3>

            <?php foreach ($steps as $step): ?>
                <div class="glass rounded-2xl border border-slate-850 overflow-hidden">
                    <div class="p-5 bg-slate-900/10 border-b border-slate-850 flex justify-between items-center flex-wrap gap-4">
                        <div class="space-y-1">
                            <span class="text-[10px] uppercase font-black text-slate-500 tracking-wider">Étape #<?php echo $step['ordre']; ?></span>
                            <h4 class="font-bold text-slate-200"><?php echo htmlspecialchars($step['titre'], ENT_QUOTES, 'UTF-8'); ?></h4>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider <?php echo $step['statut'] === 'Terminé' ? 'bg-emerald-950/20 text-emerald-400 border border-emerald-900/30' : 'bg-amber-950/20 text-amber-400 border border-amber-900/30'; ?>">
                            <?php echo htmlspecialchars($step['statut'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>

                    <div class="p-5 bg-slate-950/10">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php if (empty($tasksByStep[$step['id']])): ?>
                                <p class="text-slate-500 text-xs col-span-full">Aucune tâche enregistrée sous ce jalon.</p>
                            <?php else: ?>
                                <?php foreach ($tasksByStep[$step['id']] as $tk): ?>
                                    <div class="p-4 bg-slate-950/40 border border-slate-900/80 rounded-xl space-y-2">
                                        <div class="flex justify-between items-start gap-2 text-[10px] font-bold text-slate-500">
                                            <span>Statut : <?php echo htmlspecialchars($tk['statut'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                        <h5 class="font-bold text-xs text-slate-200"><?php echo htmlspecialchars($tk['titre'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                        <p class="text-[11px] text-slate-400 leading-normal line-clamp-2"><?php echo htmlspecialchars($tk['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <footer class="border-t border-slate-900 bg-slate-950/80 py-8 px-6 text-center text-slate-650 text-xs">
        Partagé avec vous par <span class="font-bold text-slate-500"><?php echo htmlspecialchars($project['owner_nom'] ?? 'l\'équipe de développement', ENT_QUOTES, 'UTF-8'); ?></span>. Tous droits réservés.
    </footer>

    <script>lucide.createIcons();</script>
</body>
</html>
