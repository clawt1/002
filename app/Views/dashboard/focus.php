<?php
$title = "Mode Focus & Pomodoro";
require __DIR__ . '/../partials/header.php';
?>

<div class="max-w-4xl mx-auto space-y-8">
    <div class="text-center space-y-2">
        <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-500 flex items-center justify-center gap-3">
            <i data-lucide="brain-circuit" class="w-8 h-8 text-amber-400"></i> Mode Focus Interstellaire
        </h1>
        <p class="text-slate-400 text-sm max-w-lg mx-auto">Isolez-vous de toute distraction avec le minuteur Pomodoro intégré et restez concentré sur vos objectifs.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        <div class="lg:col-span-2 glass p-8 rounded-3xl border border-slate-800 text-center space-y-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/5 rounded-full blur-2xl"></div>

            <div class="flex justify-center gap-2 bg-slate-950/60 p-1.5 rounded-xl border border-slate-900 max-w-sm mx-auto">
                <button onclick="setTimerMode('work')" id="btn-mode-work" class="flex-1 py-2 px-3 text-xs font-black rounded-lg bg-amber-500 text-slate-950 transition">Focus (25m)</button>
                <button onclick="setTimerMode('short')" id="btn-mode-short" class="flex-1 py-2 px-3 text-xs font-black rounded-lg text-slate-400 hover:text-amber-400 transition">Pause Courte (5m)</button>
                <button onclick="setTimerMode('long')" id="btn-mode-long" class="flex-1 py-2 px-3 text-xs font-black rounded-lg text-slate-400 hover:text-amber-400 transition">Pause Longue (15m)</button>
            </div>

            <div class="py-10">
                <div id="time-display" class="text-7xl md:text-8xl font-black tracking-tight text-slate-100 font-mono drop-shadow-[0_0_20px_rgba(245,158,11,0.25)]">25:00</div>
                <div id="timer-status" class="text-xs uppercase font-bold tracking-widest text-slate-500 mt-2">Prêt à focaliser</div>
            </div>

            <div class="flex justify-center gap-4">
                <button onclick="toggleTimer()" id="btn-timer-toggle" class="bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black p-4 px-8 rounded-2xl shadow-lg shadow-amber-500/10 hover:brightness-110 transition flex items-center gap-2 text-base">
                    <i data-lucide="play" class="w-5 h-5"></i> Démarrer
                </button>
                <button onclick="resetTimer()" class="bg-slate-950/60 hover:bg-slate-900 border border-slate-850 p-4 px-6 rounded-2xl transition text-slate-400 hover:text-slate-200">
                    Réinitialiser
                </button>
            </div>
        </div>

        <div class="glass p-6 rounded-3xl border border-slate-800">
            <h2 class="text-base font-extrabold text-amber-400 mb-4 flex items-center gap-2">
                <i data-lucide="target" class="w-5 h-5"></i> Cible actuelle
            </h2>

            <?php if (empty($tasks)): ?>
                <p class="text-slate-500 text-xs text-center py-8">Aucune tâche assignée disponible pour focaliser.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($tasks as $idx => $t): ?>
                        <label class="flex items-start gap-3 p-3 rounded-xl bg-slate-950/40 border border-slate-900 hover:border-amber-500/30 cursor-pointer transition">
                            <input type="radio" name="focus-target" value="<?php echo htmlspecialchars($t['titre'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $idx === 0 ? 'checked' : ''; ?> onchange="updateFocusTarget(this.value)" class="mt-1 accent-amber-500">
                            <div>
                                <span class="block text-[10px] text-slate-500 font-bold uppercase"><?php echo htmlspecialchars($t['project_titre'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="block text-xs font-semibold text-slate-200 leading-tight"><?php echo htmlspecialchars($t['titre'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
    let timer = null;
    let secondsLeft = 1500;
    let isRunning = false;
    let currentMode = 'work';

    const timerSettings = {
        work: 1500,
        short: 300,
        long: 900
    };

    function updateDisplay() {
        const minutes = Math.floor(secondsLeft / 60);
        const seconds = secondsLeft % 60;
        const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        document.getElementById('time-display').textContent = display;
    }

    function setTimerMode(mode) {
        currentMode = mode;
        secondsLeft = timerSettings[mode];
        updateDisplay();

        ['work', 'short', 'long'].forEach(m => {
            const btn = document.getElementById(`btn-mode-${m}`);
            if (m === mode) {
                btn.className = "flex-1 py-2 px-3 text-xs font-black rounded-lg bg-amber-500 text-slate-950 transition";
            } else {
                btn.className = "flex-1 py-2 px-3 text-xs font-black rounded-lg text-slate-400 hover:text-amber-400 transition";
            }
        });

        document.getElementById('timer-status').textContent = mode === 'work' ? 'Temps de focaliser' : 'Temps de respirer';
        if (isRunning) {
            toggleTimer();
        }
    }

    function toggleTimer() {
        const toggleBtn = document.getElementById('btn-timer-toggle');
        if (isRunning) {
            clearInterval(timer);
            isRunning = false;
            toggleBtn.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Démarrer';
            document.getElementById('timer-status').textContent = 'Minuteur suspendu';
        } else {
            isRunning = true;
            toggleBtn.innerHTML = '<i data-lucide="pause" class="w-5 h-5"></i> Suspendre';
            document.getElementById('timer-status').textContent = currentMode === 'work' ? 'Concentration en cours...' : 'Pause active...';

            timer = setInterval(() => {
                secondsLeft--;
                updateDisplay();

                if (secondsLeft <= 0) {
                    clearInterval(timer);
                    isRunning = false;
                    playBeepAlert();
                    alert(currentMode === 'work' ? 'Temps écoulé ! Prenez une pause bien méritée.' : 'La pause est finie, retour au travail !');
                    setTimerMode(currentMode === 'work' ? 'short' : 'work');
                }
            }, 1000);
        }
        lucide.createIcons();
    }

    function resetTimer() {
        clearInterval(timer);
        isRunning = false;
        secondsLeft = timerSettings[currentMode];
        updateDisplay();
        document.getElementById('btn-timer-toggle').innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Démarrer';
        document.getElementById('timer-status').textContent = 'Minuteur réinitialisé';
        lucide.createIcons();
    }

    function updateFocusTarget(title) {
        if (currentMode === 'work') {
            document.getElementById('timer-status').textContent = `Focus sur : ${title}`;
        }
    }

    function playBeepAlert() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);

            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(880, audioCtx.currentTime);
            gainNode.gain.setValueAtTime(0.3, audioCtx.currentTime);

            oscillator.start();
            oscillator.stop(audioCtx.currentTime + 0.5);
        } catch(e) {
            console.warn("L'API Web Audio n'est pas supportée sur ce navigateur.");
        }
    }

    const activeRadio = document.querySelector('input[name="focus-target"]:checked');
    if (activeRadio) {
        updateFocusTarget(activeRadio.value);
    }
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
