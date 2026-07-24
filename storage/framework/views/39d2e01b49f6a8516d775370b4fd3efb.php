<div wire:poll.10s>
    <div class="max-w-4xl mx-auto px-4 pt-6 pb-4">
        <h1 class="text-2xl font-bold font-expedition text-mountain-100">Basecamp Dashboard</h1>
        <p class="text-mountain-400 text-sm mt-1">Selamat datang, <?php echo e(auth()->user()->name); ?>!</p>
    </div>

    <div class="max-w-4xl mx-auto px-4 pb-8 space-y-6">
        <!-- Create room CTA -->
        <div class="bg-mountain-900/50 rounded-2xl border border-mountain-800 p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-mountain-100">Mulai Ekspedisi Baru</h2>
                <p class="text-sm text-mountain-400">Buat room dan undang 2-5 pendaki lainnya.</p>
            </div>
            <form method="POST" action="<?php echo e(route('rooms.store')); ?>">
                <?php echo csrf_field(); ?>
                <button class="px-6 py-2.5 rounded-xl bg-trust-500 text-mountain-950 font-bold hover:bg-trust-400 text-sm whitespace-nowrap">
                    + Buat Room
                </button>
            </form>
        </div>

        <!-- Notifications -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($un->count() > 0): ?>
        <div class="bg-mountain-900/50 rounded-2xl border border-trust-500/30 p-4">
            <h2 class="font-semibold text-mountain-200 text-sm mb-3 flex items-center gap-2">
                <span class="w-2 h-2 bg-trust-400 rounded-full animate-pulse"></span>
                Notifikasi
            </h2>
            <div class="space-y-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $un; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e($notification->data['url'] ?? '#'); ?>"
                   wire:click="markRead('<?php echo e($notification->id); ?>')"
                   class="block p-3 rounded-lg bg-mountain-800/50 hover:bg-mountain-800">
                    <p class="text-sm text-mountain-200"><?php echo e($notification->data['message'] ?? ''); ?></p>
                    <p class="text-xs text-mountain-500 mt-1"><?php echo e($notification->created_at->diffForHumans()); ?></p>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Waiting rooms -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($wr->count() > 0): ?>
        <div>
            <h2 class="font-semibold text-mountain-200 text-sm mb-3">Menunggu Pemain</h2>
            <div class="space-y-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $wr; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('rooms.lobby', $room)); ?>"
                   class="block p-4 rounded-xl bg-mountain-900/50 border border-mountain-800 hover:border-trust-500/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold text-trust-400"><?php echo e($room->code); ?></span>
                            <span class="text-sm text-mountain-300 ml-2"><?php echo e($room->players->count()); ?>/<?php echo e(config('summit.max_players')); ?></span>
                        </div>
                        <span class="text-xs text-mountain-500"><?php echo e($room->host->name); ?></span>
                    </div>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Active/finished games -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ag->count() > 0): ?>
        <div>
            <h2 class="font-semibold text-mountain-200 text-sm mb-3">Game Aktif</h2>
            <div class="space-y-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ag; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gamePlayer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $gameRoom = $gamePlayer->room; ?>
                <a href="<?php echo e($gameRoom->status === 'finished' ? route('game.summary', $gameRoom) : route('game.board', $gameRoom)); ?>"
                   class="block p-4 rounded-xl bg-mountain-900/50 border border-mountain-800 hover:border-trust-500/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold text-trust-400"><?php echo e($gameRoom->code); ?></span>
                            <span class="text-xs ml-2 px-2 py-0.5 rounded-full bg-camp-800 text-camp-200">
                                <?php echo e($gameRoom->status->label()); ?>

                            </span>
                        </div>
                    </div>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\laragon\www\thesummit\resources\views/livewire/dashboard.blade.php ENDPATH**/ ?>