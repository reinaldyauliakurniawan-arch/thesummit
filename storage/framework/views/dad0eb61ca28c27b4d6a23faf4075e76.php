<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['card'=>null,'showEffects'=>false,'effects'=>[],'riskDieResult'=>null,'dysfunction'=>null,'choosing'=>false]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['card'=>null,'showEffects'=>false,'effects'=>[],'riskDieResult'=>null,'dysfunction'=>null,'choosing'=>false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($card): ?>
<div class="max-w-lg mx-auto">
<div class="flex items-center justify-center gap-2 mb-3">
<span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider <?php echo e($card->level==='basecamp'?'bg-basecamp-500 text-white':($card->level==='camp'?'bg-camp-600 text-white':'bg-summit-600 text-summit-50')); ?>"><?php echo e(ucfirst($card->level)); ?></span>
<span class="px-2 py-0.5 rounded-full text-xs bg-mountain-700 text-mountain-200"><?php echo e(ucfirst($card->kategori)); ?></span>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($card->tipe==='krisis'): ?><span class="px-2 py-0.5 rounded-full text-xs bg-crisis-600 text-white animate-pulse">Krisis</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<div class="bg-mountain-800 rounded-2xl border-2 <?php echo e($card->tipe==='krisis'?'border-crisis-500':'border-mountain-600'); ?> p-6 shadow-xl <?php echo e($choosing?'animate-card-flip':'animate-fade-in'); ?>">
<div class="mb-6"><h4 class="text-xs uppercase tracking-wider text-mountain-400 mb-2 font-semibold">Situasi Ekspedisi</h4><p class="text-mountain-100 leading-relaxed text-sm"><?php echo e($card->teks_situasi); ?></p></div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($choosing): ?>
<div class="space-y-3">
<button wire:click="chooseOption('A')" class="w-full text-left p-4 rounded-xl border-2 border-mountain-600 hover:border-trust-400 bg-mountain-900/50 hover:bg-mountain-900 transition-all group">
<div class="flex items-start gap-3"><span class="w-8 h-8 rounded-lg bg-mountain-700 group-hover:bg-trust-500 flex items-center justify-center text-sm font-bold text-mountain-200 group-hover:text-white transition-colors flex-shrink-0">A</span><div>
<p class="text-mountain-100 text-sm leading-relaxed"><?php echo e($card->opsi_a_teks); ?></p>
<div class="flex gap-2 mt-1.5 text-xs"><span class="px-1.5 py-0.5 rounded bg-basecamp-900/50 text-basecamp-300">MP <?php echo e($card->opsi_a_mp>=0?'+':''); ?><?php echo e($card->opsi_a_mp); ?></span><span class="px-1.5 py-0.5 rounded bg-camp-900/50 text-camp-300">SP <?php echo e($card->opsi_a_sp>=0?'+':''); ?><?php echo e($card->opsi_a_sp); ?></span><span class="px-1.5 py-0.5 rounded bg-trust-900/50 text-trust-300">TT <?php echo e($card->opsi_a_tt>=0?'+':''); ?><?php echo e($card->opsi_a_tt); ?></span></div>
</div></div></button>
<button wire:click="chooseOption('B')" class="w-full text-left p-4 rounded-xl border-2 border-mountain-600 hover:border-trust-400 bg-mountain-900/50 hover:bg-mountain-900 transition-all group">
<div class="flex items-start gap-3"><span class="w-8 h-8 rounded-lg bg-mountain-700 group-hover:bg-trust-500 flex items-center justify-center text-sm font-bold text-mountain-200 group-hover:text-white transition-colors flex-shrink-0">B</span><div>
<p class="text-mountain-100 text-sm leading-relaxed"><?php echo e($card->opsi_b_teks); ?></p>
<div class="flex gap-2 mt-1.5 text-xs"><span class="px-1.5 py-0.5 rounded bg-basecamp-900/50 text-basecamp-300">MP <?php echo e($card->opsi_b_mp>=0?'+':''); ?><?php echo e($card->opsi_b_mp); ?></span><span class="px-1.5 py-0.5 rounded bg-camp-900/50 text-camp-300">SP <?php echo e($card->opsi_b_sp>=0?'+':''); ?><?php echo e($card->opsi_b_sp); ?></span><span class="px-1.5 py-0.5 rounded bg-trust-900/50 text-trust-300">TT <?php echo e($card->opsi_b_tt>=0?'+':''); ?><?php echo e($card->opsi_b_tt); ?></span></div>
</div></div></button>
</div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($card->tipe==='krisis' && $choosing): ?><p class="text-center text-crisis-400 text-xs mt-3 animate-pulse">Kartu Krisis! Risk Die otomatis setelah pilihan.</p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEffects && !empty($effects)): ?>
<div class="max-w-lg mx-auto animate-slide-up">
<div class="bg-mountain-800 rounded-2xl border border-mountain-600 p-6 shadow-xl text-center">
<h4 class="text-xs uppercase tracking-wider text-mountain-400 mb-4 font-semibold">Efek Diterapkan</h4>
<div class="flex justify-center gap-4 mb-4">
<div class="text-center"><div class="text-2xl font-bold font-mono <?php echo e($effects['mp']>=0?'text-basecamp-300':'text-crisis-400'); ?>"><?php echo e($effects['mp']>=0?'+':''); ?><?php echo e($effects['mp']); ?></div><div class="text-xs text-mountain-400">MP</div></div>
<div class="text-center"><div class="text-2xl font-bold font-mono <?php echo e($effects['sp']>=0?'text-camp-300':'text-crisis-400'); ?>"><?php echo e($effects['sp']>=0?'+':''); ?><?php echo e($effects['sp']); ?></div><div class="text-xs text-mountain-400">SP</div></div>
<div class="text-center"><div class="text-2xl font-bold font-mono <?php echo e($effects['tt']>=0?'text-trust-300':'text-crisis-400'); ?>"><?php echo e($effects['tt']>=0?'+':''); ?><?php echo e($effects['tt']); ?></div><div class="text-xs text-mountain-400">TT</div></div>
</div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($riskDieResult !== null): ?>
<div class="border-t border-mountain-700 pt-3 mt-3">
<div class="text-sm text-mountain-300 mb-1">Risk Die: <span class="font-bold text-lg"><?php echo e($riskDieResult); ?></span></div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($riskDieResult<=2): ?><div class="text-crisis-400 text-xs font-semibold animate-pulse">Dysfunction: <?php echo e(config("summit.dysfunctions.$dysfunction",$dysfunction)); ?> (TT -2)</div>
<?php elseif($riskDieResult>=5): ?><div class="text-trust-400 text-xs font-semibold">Bonus! TT +1</div>
<?php else: ?><div class="text-mountain-400 text-xs">Netral</div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\thesummit\resources\views/components/expedition-card.blade.php ENDPATH**/ ?>