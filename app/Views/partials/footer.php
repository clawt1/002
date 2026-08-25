    </main>

    <footer class="border-t border-slate-900 bg-slate-950/80 py-8 px-6 text-center text-slate-500 text-xs mt-12">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                © <?php echo date('Y'); ?> <span class="font-bold text-slate-400">Roadmap Manager</span>. Conçu pour le déploiement FTP instantané.
            </div>
            <div class="flex gap-4">
                <a href="/admin/gdpr" class="hover:text-slate-300 transition">RGPD & Conformité</a>
                <span class="text-slate-800">|</span>
                <span class="text-slate-600">Statut : Opérationnel</span>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        function pollNotifications() {
            fetch('/api/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('unread-notifs-badge');
                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                })
                .catch(err => console.error('Erreur polling notifications:', err));
        }

        <?php if (\App\Core\Auth::check()): ?>
            pollNotifications();
            setInterval(pollNotifications, 30000);
        <?php endif; ?>
    </script>
</body>
</html>
