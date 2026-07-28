<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['level'=>'basecamp','mp'=>0,'sp'=>0,'tt'=>0,'showThresholds'=>false,'playerName'=>'','compact'=>false]));

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

foreach (array_filter((['level'=>'basecamp','mp'=>0,'sp'=>0,'tt'=>0,'showThresholds'=>false,'playerName'=>'','compact'=>false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<?php
$levels=['basecamp','camp','summit'];$ci=array_search($level,$levels);
$mpPct=$level==='summit'?min(100,($mp/15)*100):min(100,($mp/12)*100);
$spPct=$level==='summit'?min(100,($sp/15)*100):min(100,($sp/12)*100);
$nt=null;$nl=null;
if($level==='basecamp'){$nt=config('summit.thresholds.to_camp');$nl='Camp';}elseif($level==='camp'){$nt=config('summit.thresholds.to_summit');$nl='Summit';}
?>
<div class="<?php echo e($compact?'p-2':'p-4'); ?> rounded-lg bg-mountain-900/50 border border-mountain-700">
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($playerName): ?><div class="text-sm font-semibold text-mountain-200 mb-2"><?php echo e($playerName); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<div class="flex items-center gap-1 mb-3">
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $levels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx=>$lvl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="flex items-center"><div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold <?php echo e($idx<$ci?'bg-camp-500 text-white':($idx===$ci?'bg-basecamp-500 text-white ring-2 ring-trust-400':'bg-mountain-700 text-mountain-400')); ?>"><?php echo e($idx===0?'B':($idx===1?'C':'S')); ?></div><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($idx<2): ?><div class="w-6 h-0.5 <?php echo e($idx<$ci?'bg-camp-400':'bg-mountain-700'); ?>"></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<div class="mb-2"><div class="flex justify-between text-xs mb-0.5"><span class="text-mountain-300">MP</span><span class="text-mountain-200 font-mono"><?php echo e($mp); ?></span></div><div class="w-full h-2 bg-mountain-800 rounded-full overflow-hidden"><div class="h-full bg-basecamp-400 rounded-full transition-all duration-500" style="width:<?php echo e($mpPct); ?>%"></div></div></div>
<div class="mb-2"><div class="flex justify-between text-xs mb-0.5"><span class="text-mountain-300">SP</span><span class="text-mountain-200 font-mono"><?php echo e($sp); ?></span></div><div class="w-full h-2 bg-mountain-800 rounded-full overflow-hidden"><div class="h-full bg-camp-400 rounded-full transition-all duration-500" style="width:<?php echo e($spPct); ?>%"></div></div></div>
<?php if (isset($component)) { $__componentOriginalf595d9b4427dd9f6c63feb3d42dea612 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf595d9b4427dd9f6c63feb3d42dea612 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.rope-meter','data' => ['tt' => $tt,'compact' => $compact]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('rope-meter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tt),'compact' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($compact)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf595d9b4427dd9f6c63feb3d42dea612)): ?>
<?php $attributes = $__attributesOriginalf595d9b4427dd9f6c63feb3d42dea612; ?>
<?php unset($__attributesOriginalf595d9b4427dd9f6c63feb3d42dea612); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf595d9b4427dd9f6c63feb3d42dea612)): ?>
<?php $component = $__componentOriginalf595d9b4427dd9f6c63feb3d42dea612; ?>
<?php unset($__componentOriginalf595d9b4427dd9f6c63feb3d42dea612); ?>
<?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showThresholds && $nt): ?><div class="mt-2 text-xs text-mountain-400 border-t border-mountain-700 pt-2">Naik ke <?php echo e($nl); ?>: MP <?php echo e($nt['mp']); ?> | SP <?php echo e($nt['sp']); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nt['tt']>0): ?>| TT <?php echo e($nt['tt']); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\thesummit\resources\views/components/progress-bar.blade.php ENDPATH**/ ?>