<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($show): ?>
        <?php if (isset($component)) { $__componentOriginal6ecddd9352040ff0560becbad84342c9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ecddd9352040ff0560becbad84342c9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.onboarding-slide','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('onboarding-slide'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ecddd9352040ff0560becbad84342c9)): ?>
<?php $attributes = $__attributesOriginal6ecddd9352040ff0560becbad84342c9; ?>
<?php unset($__attributesOriginal6ecddd9352040ff0560becbad84342c9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ecddd9352040ff0560becbad84342c9)): ?>
<?php $component = $__componentOriginal6ecddd9352040ff0560becbad84342c9; ?>
<?php unset($__componentOriginal6ecddd9352040ff0560becbad84342c9); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\thesummit\resources\views/livewire/onboarding.blade.php ENDPATH**/ ?>