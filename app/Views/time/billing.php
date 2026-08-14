<?php
$title = "Rapports de Facturation";
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-8">
    <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">💰 Relevé d'Activité & Facturation</h1>
    <p class="text-slate-400 text-sm mt-1">Calculez les heures facturables par projet pour générer vos relevés d'activité.</p>
</div>

<!-- Filters Form -->
<div class="glass p-6 rounded-3xl border border-slate-800 mb-8">
    <form action="/billing" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Projet</label>
            <select name="project_id" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 text-xs">
                <?php foreach ($projects as $p): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo $p['id'] == $projectId ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['titre'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">TJM (Taux Journalier Moyen)</label>
            <input type="number" name="taux_horaire" value="<?php echo htmlspecialchars($rate, ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 text-xs" required>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Du</label>
            <input type="date" name="date_debut" value="<?php echo htmlspecialchars($dateStart ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 text-xs">
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Au</label>
            <input type="date" name="date_fin" value="<?php echo htmlspecialchars($dateEnd ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-slate-950 border border-slate-850 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500 text-xs">
        </div>

        <button type="submit" class="bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-3.5 rounded-xl shadow-lg hover:brightness-110 transition text-xs flex justify-center items-center gap-1.5 h-12">
            <i data-lucide="filter" class="w-4 h-4"></i> Filtrer les temps
        </button>
    </form>
</div>

<!-- Results & Stats -->
<?php if ($projectId): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="glass p-6 rounded-2xl border border-slate-800 flex items-center gap-5">
            <div class="w-12 h-12 rounded-xl bg-cyan-950/40 border border-cyan-800/40 flex items-center justify-center">
                <i data-lucide="clock" class="w-6 h-6 text-cyan-400"></i>
            </div>
            <div>
                <span class="block text-slate-500 text-xs font-bold uppercase tracking-wider">Total d'Heures Réalisées</span>
                <span class="text-2xl font-black text-cyan-400 leading-none"><?php echo $totalHours; ?> h</span>
            </div>
        </div>

        <div class="glass p-6 rounded-2xl border border-slate-800 flex items-center gap-5">
            <div class="w-12 h-12 rounded-xl bg-emerald-950/40 border border-emerald-800/40 flex items-center justify-center">
                <i data-lucide="banknote" class="w-6 h-6 text-emerald-400"></i>
            </div>
            <div>
                <span class="block text-slate-500 text-xs font-bold uppercase tracking-wider">Montant Estimé à Facturer (base 7h/jour)</span>
                <span class="text-2xl font-black text-emerald-400 leading-none"><?php echo number_format($totalBilling, 2, ',', ' '); ?> €</span>
            </div>
        </div>
    </div>

    <!-- Timesheet Table -->
    <div class="glass rounded-3xl border border-slate-800 overflow-hidden">
        <div class="p-5 border-b border-slate-850 flex justify-between items-center bg-slate-900/10">
            <h3 class="font-extrabold text-sm text-slate-200">📋 Détail des heures travaillées</h3>
            <span class="text-xs text-slate-500">Filtré sur <?php echo count($entries); ?> saisies</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-900 bg-slate-950/40 text-slate-400 uppercase font-black text-[10px] tracking-wider">
                        <th class="p-4">Date</th>
                        <th class="p-4">Collaborateur</th>
                        <th class="p-4">Étape / Tâche</th>
                        <th class="p-4">Commentaire</th>
                        <th class="p-4 text-right">Durée</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900">
                    <?php if (empty($entries)): ?>
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500 italic">Aucune saisie de temps ne correspond aux critères.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($entries as $e): ?>
                            <tr class="hover:bg-slate-900/30 transition text-slate-300">
                                <td class="p-4 font-mono"><?php echo htmlspecialchars($e['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="p-4 font-bold text-slate-200"><?php echo htmlspecialchars($e['user_nom'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="p-4">
                                    <span class="block text-[10px] text-slate-500 uppercase font-bold"><?php echo htmlspecialchars($e['step_titre'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="font-semibold"><?php echo htmlspecialchars($e['task_titre'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td class="p-4 text-slate-400 max-w-xs truncate"><?php echo htmlspecialchars($e['description'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="p-4 text-right font-bold text-cyan-400 font-mono"><?php echo round($e['duree_minutes'] / 60, 2); ?> h</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
