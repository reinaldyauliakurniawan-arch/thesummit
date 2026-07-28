<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><meta name="csrf-token" content="<?php echo e(csrf_token()); ?>"><title><?php echo e($title ?? 'The Summit'); ?></title><?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css','resources/js/app.js']); ?><style>[x-cloak]{display:none!important}</style></head>
<body class="bg-mountain-950 text-mountain-100 min-h-screen font-sans antialiased">
<nav class="bg-mountain-900/80 backdrop-blur border-b border-mountain-800 sticky top-0 z-40">
<div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between">
<a href="<?php echo e(route('dashboard')); ?>" class="flex items-center gap-2 text-trust-400 font-bold font-expedition text-lg">
<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2z"/></svg>The Summit</a>
<div class="flex items-center gap-3">
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
<a href="<?php echo e(route('dashboard')); ?>" class="text-sm text-mountain-300 hover:text-mountain-100">Dashboard</a>
<a href="<?php echo e(route('rooms.index')); ?>" class="text-sm text-mountain-300 hover:text-mountain-100">Rooms</a>
<form method="POST" action="<?php echo e(route('logout')); ?>" class="inline"><?php echo csrf_field(); ?><button class="text-sm text-mountain-400 hover:text-crisis-400">Logout</button></form>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->guest()): ?>
<a href="<?php echo e(route('login')); ?>" class="text-sm text-mountain-300">Login</a>
<a href="<?php echo e(route('register')); ?>" class="text-sm px-3 py-1 rounded-lg bg-trust-500 text-mountain-950 font-semibold hover:bg-trust-400">Daftar</a>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div></div></nav>
<main><?php echo e($slot); ?></main>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
<?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('onboarding', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1760505537-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?> <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?></body></html>
<?php /**PATH C:\laragon\www\thesummit\resources\views/layouts/app.blade.php ENDPATH**/ ?>