<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accès Interdit - 403</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-center items-center p-6">
    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl text-center">
        <div class="w-16 h-16 rounded-full bg-red-950/40 border border-red-800/80 flex items-center justify-center mx-auto mb-6">
            <i data-lucide="shield-alert" class="w-8 h-8 text-red-500"></i>
        </div>
        <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-amber-500">Accès Refusé (403)</h1>
        <p class="text-slate-400 mt-4 text-sm leading-relaxed">
            Désolé, votre niveau d'habilitation ou votre rôle utilisateur ne vous permet pas d'accéder à cette ressource.
        </p>
        <div class="mt-8 flex justify-center">
            <a href="/" class="bg-slate-800 border border-slate-700 text-slate-100 font-bold p-3 px-6 rounded-xl hover:bg-slate-750 transition flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Retourner au Dashboard
            </a>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
