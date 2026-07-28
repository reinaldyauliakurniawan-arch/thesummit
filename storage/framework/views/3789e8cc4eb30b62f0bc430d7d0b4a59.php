<div wire:poll.5s>
    <div class="max-w-2xl mx-auto px-4 pt-4 pb-8">

        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <span class="font-mono text-trust-400 font-bold"><?php echo e($room->code); ?></span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($room->status->value === 'final_round'): ?>
                    <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-trust-800 text-trust-200 font-bold animate-pulse">FINAL ROUND</span>
                <?php else: ?>
                    <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-camp-800 text-camp-200">Bermain</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <button wire:click="refreshBoard" class="text-xs text-mountain-400 px-3 py-1 rounded-lg border border-mountain-700">
                Refresh
            </button>
        </div>

        <!-- Player grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $player): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="relative p-2 rounded-xl border <?php echo e($room->current_turn_player_id === $player->id ? 'border-trust-500 bg-mountain-800' : 'border-mountain-800 bg-mountain-900/50'); ?>">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($room->current_turn_player_id === $player->id): ?>
                    <div class="absolute -top-1.5 -right-1.5 w-3 h-3 bg-trust-400 rounded-full animate-pulse"></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="text-xs font-semibold <?php echo e($player->user_id === auth()->id() ? 'text-trust-300' : 'text-mountain-300'); ?> truncate">
                    <?php echo e($player->user->name); ?><?php echo e($player->user_id === auth()->id() ? ' (kamu)' : ''); ?>

                </div>
                <div class="flex gap-1 mt-1">
                    <span class="text-xs text-basecamp-300">M<?php echo e($player->mp); ?></span>
                    <span class="text-xs text-camp-300">S<?php echo e($player->sp); ?></span>
                    <span class="text-xs text-trust-300">T<?php echo e($player->tt); ?></span>
                </div>
                <div class="text-xs text-mountain-500 mt-0.5"><?php echo e(ucfirst($player->current_level)); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <!-- My progress bar -->
        <div class="mb-6">
            <?php if (isset($component)) { $__componentOriginalc1838dab69175fa625a76ca35492c358 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1838dab69175fa625a76ca35492c358 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.progress-bar','data' => ['level' => $myPlayer->current_level,'mp' => $myPlayer->mp,'sp' => $myPlayer->sp,'tt' => $myPlayer->tt,'showThresholds' => true,'playerName' => $myPlayer->user->name . ' (kamu)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('progress-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['level' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($myPlayer->current_level),'mp' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($myPlayer->mp),'sp' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($myPlayer->sp),'tt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($myPlayer->tt),'showThresholds' => true,'playerName' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($myPlayer->user->name . ' (kamu)')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1838dab69175fa625a76ca35492c358)): ?>
<?php $attributes = $__attributesOriginalc1838dab69175fa625a76ca35492c358; ?>
<?php unset($__attributesOriginalc1838dab69175fa625a76ca35492c358); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1838dab69175fa625a76ca35492c358)): ?>
<?php $component = $__componentOriginalc1838dab69175fa625a76ca35492c358; ?>
<?php unset($__componentOriginalc1838dab69175fa625a76ca35492c358); ?>
<?php endif; ?>
        </div>

        <!-- Status message -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($message): ?>
            <div class="mb-4 p-3 rounded-xl bg-trust-900/30 border border-trust-500/30 text-sm text-center">
                <?php echo e($message); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Waiting for turn -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isMyTurn && $room->status->value !== 'finished'): ?>
            <div class="text-center py-8 text-mountain-500">
                <div class="text-sm mb-1">Bukan giliranmu.</div>
                <div class="text-xs text-mountain-600">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($room->currentPlayer): ?>
                        Giliran <span class="text-mountain-400 font-semibold"><?php echo e($room->currentPlayer->user->name); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <button wire:click="refreshBoard" class="mt-3 text-xs text-trust-400 hover:underline">Refresh</button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Draw card button -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isMyTurn && !$showCard && !$showEffects): ?>
            <div class="text-center py-6">
                <p class="text-mountain-200 mb-4 font-semibold">Giliranmu!</p>
                <button wire:click="drawCard"
                        class="px-8 py-3 rounded-xl bg-trust-500 text-mountain-950 font-bold text-lg hover:bg-trust-400 animate-pulse-gold">
                    Ambil Expedition Card
                </button>
                <p class="text-xs text-mountain-500 mt-2">
                    Turn #<?php echo e($myPlayer->turns()->count() + 1); ?>

                    — <?php echo e($myPlayer->turns()->count() % 2 === 0 ? 'Mindset' : 'Skillset'); ?>

                </p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Card display (choosing) -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCard && $currentCard): ?>
            <?php if (isset($component)) { $__componentOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.expedition-card','data' => ['card' => $currentCard,'choosing' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('expedition-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['card' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currentCard),'choosing' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4)): ?>
<?php $attributes = $__attributesOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4; ?>
<?php unset($__attributesOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4)): ?>
<?php $component = $__componentOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4; ?>
<?php unset($__componentOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Effects display -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEffects && !empty($lastEffects)): ?>
            <?php if (isset($component)) { $__componentOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.expedition-card','data' => ['showEffects' => true,'effects' => $lastEffects,'riskDieResult' => $riskDieResult,'dysfunction' => $dysfunctionTriggered]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('expedition-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['showEffects' => true,'effects' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($lastEffects),'riskDieResult' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($riskDieResult),'dysfunction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($dysfunctionTriggered)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4)): ?>
<?php $attributes = $__attributesOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4; ?>
<?php unset($__attributesOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4)): ?>
<?php $component = $__componentOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4; ?>
<?php unset($__componentOriginal4a46a8fd1dcab9d0e17e7dd71b8772c4); ?>
<?php endif; ?>
            <div class="text-center mt-4">
                <button wire:click="refreshBoard"
                        class="px-6 py-2 rounded-xl border border-mountain-600 text-mountain-300 text-sm">
                    Lanjut
                </button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Rope Bridge check modal -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showRopeBridge): ?>
            <?php if (isset($component)) { $__componentOriginal800d74d9c7fa7cd61128768a58752902 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal800d74d9c7fa7cd61128768a58752902 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rope-bridge-check','data' => ['player' => $myPlayer,'thresholdKey' => $myPlayer->current_level === 'basecamp' ? 'to_camp' : 'to_summit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rope-bridge-check'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['player' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($myPlayer),'thresholdKey' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($myPlayer->current_level === 'basecamp' ? 'to_camp' : 'to_summit')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal800d74d9c7fa7cd61128768a58752902)): ?>
<?php $attributes = $__attributesOriginal800d74d9c7fa7cd61128768a58752902; ?>
<?php unset($__attributesOriginal800d74d9c7fa7cd61128768a58752902); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal800d74d9c7fa7cd61128768a58752902)): ?>
<?php $component = $__componentOriginal800d74d9c7fa7cd61128768a58752902; ?>
<?php unset($__componentOriginal800d74d9c7fa7cd61128768a58752902); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Turn log -->
        <div class="mt-8">
            <h3 class="text-sm font-semibold text-mountain-300 mb-3">Log Ekspedisi</h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $allTurns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $turn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="p-3 rounded-lg bg-mountain-900/50 border border-mountain-800 text-xs">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-mountain-200"><?php echo e($turn->player->user->name); ?></span>
                        <span class="text-mountain-500">pilih</span>
                        <span class="font-bold text-trust-300"><?php echo e($turn->chosen_option); ?></span>
                    </div>
                    <div class="flex gap-2 text-mountain-400">
                        <span>MP<?php echo e($turn->mp_effect >= 0 ? '+' : ''); ?><?php echo e($turn->mp_effect); ?></span>
                        <span>SP<?php echo e($turn->sp_effect >= 0 ? '+' : ''); ?><?php echo e($turn->sp_effect); ?></span>
                        <span>TT<?php echo e($turn->tt_effect >= 0 ? '+' : ''); ?><?php echo e($turn->tt_effect); ?></span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($turn->risk_die_result): ?>
                            <span class="text-mountain-500">| Die:<?php echo e($turn->risk_die_result); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($turn->dysfunction_triggered): ?>
                            <span class="text-crisis-400">| Dysfunction!</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allTurns->isEmpty()): ?>
                    <p class="text-mountain-600 text-xs text-center py-4">Belum ada giliran.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\thesummit\resources\views/livewire/game-board.blade.php ENDPATH**/ ?>