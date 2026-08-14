<?php
$title = "Gestion des Utilisateurs";
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">👥 Collaborateurs & Comptes</h1>
        <p class="text-slate-400 text-sm mt-1">Gérez les comptes utilisateurs, leurs attributions de rôles et habilitations.</p>
    </div>
    <div class="flex gap-2">
        <a href="/roles/permissions" class="bg-slate-900 border border-slate-800 text-slate-300 font-bold p-3 px-5 rounded-xl hover:bg-slate-850 text-xs transition flex items-center gap-2">
            <i data-lucide="lock" class="w-4 h-4"></i> Matrice de Droits
        </a>
        <a href="/users/create" class="bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-3 px-5 rounded-xl hover:brightness-110 shadow-lg shadow-cyan-500/15 transition flex items-center gap-2 text-xs">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Nouveau Compte
        </a>
    </div>
</div>

<!-- Users List -->
<div class="glass rounded-3xl border border-slate-800 overflow-hidden shadow-2xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-900 bg-slate-950/40 text-slate-400 uppercase font-black text-[10px] tracking-wider">
                    <th class="p-4">Identité</th>
                    <th class="p-4">Adresse Email</th>
                    <th class="p-4">Rôle</th>
                    <th class="p-4">Date de Création</th>
                    <th class="p-4">Statut</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-900">
                <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-slate-900/30 transition text-slate-350">
                        <!-- Identity (Avatar + Name) -->
                        <td class="p-4 flex items-center gap-3">
                            <?php if ($u['avatar']): ?>
                                <img src="/uploads/<?php echo htmlspecialchars($u['avatar'], ENT_QUOTES, 'UTF-8'); ?>" class="w-8 h-8 rounded-lg object-cover">
                            <?php else: ?>
                                <div class="w-8 h-8 rounded-lg bg-slate-950 border border-slate-800 flex items-center justify-center font-bold text-cyan-400">
                                    <?php echo htmlspecialchars(strtoupper(substr($u['nom'], 0, 2)), ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                            <span class="font-bold text-slate-200"><?php echo htmlspecialchars($u['nom'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </td>

                        <!-- Email -->
                        <td class="p-4 font-mono"><?php echo htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8'); ?></td>

                        <!-- Role -->
                        <td class="p-4">
                            <span class="p-1 px-2.5 rounded bg-slate-900 text-slate-400 font-bold border border-slate-850">
                                <?php echo htmlspecialchars($u['role_nom'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>

                        <!-- Date creation -->
                        <td class="p-4"><?php echo htmlspecialchars($u['date_creation'], ENT_QUOTES, 'UTF-8'); ?></td>

                        <!-- Status (Active / Inactive) -->
                        <td class="p-4">
                            <?php if ($u['actif']): ?>
                                <span class="text-emerald-400 font-bold flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Actif</span>
                            <?php else: ?>
                                <span class="text-red-400 font-bold flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Suspendu</span>
                            <?php endif; ?>
                        </td>

                        <!-- Actions -->
                        <td class="p-4 text-right flex items-center justify-end gap-1.5">
                            <a href="/users/<?php echo $u['id']; ?>/edit" class="p-1.5 rounded bg-slate-950 border border-slate-850 text-slate-400 hover:text-slate-100 transition" title="Modifier">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            </a>

                            <?php if ($u['id'] != \App\Core\Auth::id()): ?>
                                <a href="/users/<?php echo $u['id']; ?>/toggle-active" class="p-1.5 rounded border <?php echo $u['actif'] ? 'border-red-950/40 text-red-400 bg-red-950/10 hover:bg-red-950/20' : 'border-emerald-950/40 text-emerald-400 bg-emerald-950/10 hover:bg-emerald-950/20'; ?> transition" title="<?php echo $u['actif'] ? 'Suspendre' : 'Activer'; ?>">
                                    <i data-lucide="<?php echo $u['actif'] ? 'user-x' : 'user-check'; ?>" class="w-3.5 h-3.5"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
