<?php
use App\Core\Csrf;
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié - Roadmap Manager</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            background-color: #020617;
            background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 0);
            background-size: 32px 32px;
        }
        .glass {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="text-slate-100 min-h-screen flex flex-col justify-center items-center p-6">
    <div class="max-w-md w-full glass rounded-3xl p-8 shadow-2xl relative overflow-hidden">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">Mot de passe perdu ?</h1>
            <p class="text-slate-400 text-xs mt-1">Saisissez votre email pour recevoir un lien de réinitialisation.</p>
        </div>

        <?php require __DIR__ . '/../partials/flash-messages.php'; ?>

        <form action="/forgot-password" method="POST" class="space-y-4">
            <?php echo Csrf::field(); ?>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Adresse Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </span>
                    <input type="email" name="email" placeholder="admin@roadmap.local" class="w-full bg-slate-950/80 border border-slate-800 rounded-xl p-3 pl-10 text-slate-100 focus:outline-none focus:border-cyan-500 transition" required>
                </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-500 text-slate-950 font-black p-3.5 rounded-xl shadow-lg hover:brightness-110 transition mt-6">
                Envoyer le lien de récupération
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-400 border-t border-slate-800/40 pt-4">
            <a href="/login" class="text-cyan-400 hover:underline font-bold">Retour à la connexion</a>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
