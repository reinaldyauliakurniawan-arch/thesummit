<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['tt'=>0,'max'=>8,'compact'=>false,'showLabel'=>true]));

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

foreach (array_filter((['tt'=>0,'max'=>8,'compact'=>false,'showLabel'=>true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<?php $pct=min(100,($tt/$max)*100);$filled=min($max,$tt); ?>
<div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLabel): ?><div class="flex justify-between text-xs mb-0.5"><span class="text-trust-300 font-semibold">Trust Token</span><span class="text-trust-200 font-mono"><?php echo e($tt); ?>/<?php echo e($max); ?></span></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<div class="flex items-center gap-0.5"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i=1;$i<=$max;$i++): ?><div class="flex-1 h-<?php echo e($compact?'1.5':'2.5'); ?> rounded-full transition-all duration-500 <?php echo e($i<=$filled?'bg-trust-400 shadow-sm shadow-trust-400/50':'bg-mountain-800'); ?>"></div><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
</div>
<?php /**PATH C:\laragon\www\thesummit\resources\views/components/rope-meter.blade.php ENDPATH**/ ?>