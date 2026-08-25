<?php
use App\Helpers\Flash;

$successMessages = Flash::get('success');
$errorMessages = Flash::get('error');
?>

<?php if (!empty($successMessages)): ?>
    <div class="space-y-2 mb-6">
        <?php foreach ($successMessages as $msg): ?>
            <div class="bg-emerald-950/40 border border-emerald-800 text-emerald-400 p-4 rounded-xl flex items-center gap-3 shadow-lg">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($errorMessages)): ?>
    <div class="space-y-2 mb-6">
        <?php foreach ($errorMessages as $msg): ?>
            <div class="bg-red-950/40 border border-red-800 text-red-400 p-4 rounded-xl flex items-center gap-3 shadow-lg">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
