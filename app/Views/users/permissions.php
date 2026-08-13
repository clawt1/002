<?php
use App\Core\Csrf;
$title = "Habilitations & Rôles";
require __DIR__ . '/../partials/header.php';

// Liste des permissions disponibles dans le système
$availablePermissions = [
    'projects.create' => 'Créer des projets',
    'projects.edit' => 'Modifier des projets',
    'projects.delete' => 'Supprimer des projets',
    'steps.create' => 'Créer des étapes',
    'steps.grade' => 'Noter/Évaluer des étapes',
    'tasks.create' => 'Créer des tâches',
    'tasks.edit_status' => 'Mettre à jour statut tâche',
    'risks.manage' => 'Gérer les risques',
    'time.track' => 'Enregistrer du temps'
];
?>

<div class="mb-8">
    <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">🔒 Matrice des Droits & Permissions</h1>
    <p class="text-slate-400 text-sm mt-1">Configurez de manière granulaire les habilitations des différents rôles de l'application.</p>
</div>

<form action="/roles/permissions" method="POST" class="space-y-6">
    <?php echo Csrf::field(); ?>

    <div class="glass rounded-3xl border border-slate-800 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-900 bg-slate-950/40 text-slate-400 uppercase font-black text-[10px] tracking-wider">
                        <th class="p-4">Rôles globaux</th>
                        <?php foreach ($availablePermissions as $key => $label): ?>
                            <th class="p-4 text-center" title="<?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900">
                    <?php foreach ($roles as $role): ?>
                        <tr class="hover:bg-slate-900/30 transition text-slate-350">
                            <!-- Role Name -->
                            <td class="p-4 font-bold text-slate-200">
                                <?php echo htmlspecialchars($role['nom'], ENT_QUOTES, 'UTF-8'); ?>
                                <?php if ($role['id'] == 1): ?>
                                    <span class="block text-[9px] text-cyan-400 font-bold uppercase tracking-wider mt-0.5">Habilitation Absolue</span>
                                <?php endif; ?>
                            </td>

                            <!-- Checkboxes for each permission -->
                            <?php
                            $rolePerms = json_decode($role['permissions'] ?? '[]', true) ?: [];
                            foreach ($availablePermissions as $key => $label):
                            ?>
                                <td class="p-4 text-center">
                                    <?php if ($role['id'] == 1): ?>
                                        <!-- Admin cannot be limited -->
                                        <input type="checkbox" checked disabled class="rounded bg-slate-950 border-slate-800 text-cyan-500 focus:ring-0 focus:ring-offset-0 opacity-50">
                                    <?php else: ?>
                                        <input type="hidden" name="perms[<?php echo $role['id']; ?>][<?php echo $key; ?>]" value="0">
                                        <input type="checkbox" name="perms[<?php echo $role['id']; ?>][<?php echo $key; ?>]" value="1" <?php echo (!empty($rolePerms[$key]) && $rolePerms[$key] === true) ? 'checked' : ''; ?> class="rounded bg-slate-950 border-slate-800 text-cyan-500 focus:ring-0 focus:ring-offset-0 cursor-pointer">
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a href="/users" class="bg-slate-900 border border-slate-800 text-slate-300 font-bold p-3 px-6 rounded-xl hover:bg-slate-850 text-xs transition">Retour aux utilisateurs</a>
        <button type="submit" class="bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-3 px-6 rounded-xl hover:brightness-110 shadow-lg shadow-cyan-500/15 transition text-xs uppercase">Enregistrer la matrice</button>
    </div>
</form>

<?php require __DIR__ . '/../partials/footer.php'; ?>
