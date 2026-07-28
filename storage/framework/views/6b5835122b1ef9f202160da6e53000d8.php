<?php $__env->startComponent('layouts.app'); ?>
<div class="max-w-sm mx-auto px-4 py-12">
    <div class="text-center mb-8">
        <svg class="w-12 h-12 mx-auto text-trust-400 mb-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2z"/></svg>
        <h1 class="text-2xl font-bold font-expedition">Kembali ke Pendakian</h1>
    </div>
    <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-4">
        <?php echo csrf_field(); ?>
        <div>
            <label class="block text-sm text-mountain-300 mb-1">Email</label>
            <input type="email" name="email" value="<?php echo e(old('email')); ?>" required class="w-full px-4 py-2.5 rounded-xl bg-mountain-800 border border-mountain-700 text-mountain-100 focus:border-trust-400 focus:ring-1 focus:ring-trust-400 outline-none text-sm">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-crisis-400 text-xs mt-1"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div>
            <label class="block text-sm text-mountain-300 mb-1">Password</label>
            <input type="password" name="password" required class="w-full px-4 py-2.5 rounded-xl bg-mountain-800 border border-mountain-700 text-mountain-100 focus:border-trust-400 focus:ring-1 focus:ring-trust-400 outline-none text-sm">
        </div>
        <button type="submit" class="w-full py-2.5 rounded-xl bg-trust-500 text-mountain-950 font-bold hover:bg-trust-400">Login</button>
    </form>
    <p class="text-center text-sm text-mountain-400 mt-6">Belum punya akun? <a href="<?php echo e(route('register')); ?>" class="text-trust-400 hover:underline">Daftar</a></p>
</div>
<?php echo $__env->renderComponent(); ?><?php /**PATH C:\laragon\www\thesummit\resources\views/auth/login.blade.php ENDPATH**/ ?>