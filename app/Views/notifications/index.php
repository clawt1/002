<?php
$title = "Vos Notifications";
require __DIR__ . '/../partials/header.php';
?>

<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex justify-between items-center border-b border-slate-900 pb-4">
        <div>
            <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">🔔 Centre d'Alertes</h1>
            <p class="text-slate-400 text-sm mt-1">Suivez les assignations et alertes d'échéances.</p>
        </div>
    </div>

    <div class="space-y-4">
        <?php if (empty($notifications)): ?>
            <div class="text-center py-16 glass rounded-2xl border border-slate-850">
                <i data-lucide="check-circle-2" class="w-10 h-10 text-slate-650 mx-auto mb-3"></i>
                <p class="text-slate-400 text-sm font-semibold">Aucune notification disponible.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $n): ?>
                <div class="glass p-5 rounded-2xl border <?php echo $n['lu'] ? 'border-slate-900/60 opacity-60' : 'border-cyan-500/20 shadow-lg shadow-cyan-500/2'; ?> transition flex justify-between items-center gap-4">
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-black uppercase tracking-wider <?php echo $n['lu'] ? 'text-slate-500' : 'text-cyan-400'; ?>">
                                <?php echo htmlspecialchars($n['type'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="text-[9px] text-slate-550 font-mono"><?php echo htmlspecialchars($n['cree_le'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <p class="text-xs <?php echo $n['lu'] ? 'text-slate-400' : 'text-slate-100 font-bold'; ?>"><?php echo htmlspecialchars($n['message'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>

                    <?php if (!$n['lu']): ?>
                        <a href="/notifications/<?php echo $n['id']; ?>/read" class="bg-cyan-950/40 hover:bg-cyan-950/60 border border-cyan-800/40 text-cyan-400 font-bold p-1.5 px-3 rounded-lg text-[10px] transition">
                            Marquer lu
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
