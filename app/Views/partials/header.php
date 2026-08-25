<?php
use App\Core\Auth;
$currentUser = Auth::user();
$config = require __DIR__ . '/../../Config/config.php';
$edition = $config['app']['edition'];
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Roadmap Manager'; ?> - Enterprise</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            background-color: #020617;
            background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 0), radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 0);
            background-size: 32px 32px;
            background-position: 0 0, 16px 16px;
        }
        .glass {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen flex flex-col font-sans">

    <header class="glass sticky top-0 z-50 px-6 py-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex justify-between items-center">

            <a href="/" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/20">
                    <i data-lucide="orbit" class="w-6 h-6 text-slate-950"></i>
                </div>
                <div>
                    <span class="text-lg font-black tracking-wider uppercase text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">Roadmap Manager</span>
                    <span class="block text-[10px] uppercase tracking-widest text-slate-500 font-bold -mt-1"><?php echo htmlspecialchars(ucfirst($edition), ENT_QUOTES, 'UTF-8'); ?> Edition</span>
                </div>
            </a>

            <?php if ($currentUser): ?>
                <nav class="hidden md:flex items-center gap-1 bg-slate-950/40 p-1.5 rounded-xl border border-slate-800">
                    <a href="/" class="px-4 py-2 rounded-lg text-sm font-medium hover:text-cyan-400 transition flex items-center gap-2">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
                    </a>
                    <a href="/projects" class="px-4 py-2 rounded-lg text-sm font-medium hover:text-cyan-400 transition flex items-center gap-2">
                        <i data-lucide="folders" class="w-4 h-4"></i> Projets
                    </a>
                    <?php if (Auth::hasPermission('all')): ?>
                        <a href="/users" class="px-4 py-2 rounded-lg text-sm font-medium hover:text-cyan-400 transition flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4"></i> Utilisateurs
                        </a>
                        <a href="/clients" class="px-4 py-2 rounded-lg text-sm font-medium hover:text-cyan-400 transition flex items-center gap-2">
                            <i data-lucide="briefcase" class="w-4 h-4"></i> Clients
                        </a>
                        <a href="/billing" class="px-4 py-2 rounded-lg text-sm font-medium hover:text-cyan-400 transition flex items-center gap-2">
                            <i data-lucide="receipt" class="w-4 h-4"></i> Facturation
                        </a>
                    <?php endif; ?>
                    <a href="/focus" class="px-4 py-2 rounded-lg text-sm font-medium text-amber-400 hover:brightness-110 transition flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4"></i> Focus & Pomodoro
                    </a>
                </nav>
            <?php endif; ?>

            <div class="flex items-center gap-4">
                <?php if ($currentUser): ?>
                    <a href="/notifications" class="relative p-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-cyan-500/50 transition">
                        <i data-lucide="bell" class="w-5 h-5 text-slate-400"></i>
                        <span id="unread-notifs-badge" class="absolute -top-1 -right-1 bg-red-500 text-slate-950 text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center border border-slate-950 hidden">0</span>
                    </a>

                    <div class="flex items-center gap-3 bg-slate-900 border border-slate-800 rounded-xl p-1.5 pr-4">
                        <?php if ($currentUser['avatar']): ?>
                            <img src="/uploads/<?php echo htmlspecialchars($currentUser['avatar'], ENT_QUOTES, 'UTF-8'); ?>" class="w-8 h-8 rounded-lg object-cover">
                        <?php else: ?>
                            <div class="w-8 h-8 rounded-lg bg-cyan-950 flex items-center justify-center font-bold text-cyan-400 text-sm border border-cyan-800/40">
                                <?php echo htmlspecialchars(strtoupper(substr($currentUser['nom'], 0, 2)), ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>
                        <div class="text-left hidden sm:block">
                            <a href="/profile" class="block text-xs font-bold hover:text-cyan-400 transition leading-tight"><?php echo htmlspecialchars($currentUser['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
                            <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider"><?php echo htmlspecialchars($currentUser['role_nom'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <a href="/logout" class="p-1 rounded-lg hover:bg-slate-850 text-slate-400 hover:text-red-400 transition ml-2" title="Déconnexion">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="/login" class="bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-2.5 px-6 rounded-xl shadow-lg shadow-cyan-500/10 hover:brightness-110 transition text-sm">Connexion</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-7xl w-full mx-auto p-6 md:p-8">
        <?php require __DIR__ . '/flash-messages.php'; ?>
