<?php $__env->startComponent('layouts.app', ['title' => 'Daftar Room']); ?>
<div class="max-w-4xl mx-auto px-4 pt-6 pb-8">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="mb-4 p-4 rounded-xl bg-red-900/40 border border-red-700 text-red-200 text-sm">
            <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold font-expedition text-mountain-100">Daftar Room</h1>
        <form method="POST" action="<?php echo e(route('rooms.store')); ?>">
            <?php echo csrf_field(); ?>
            <button class="px-4 py-2 rounded-xl bg-trust-500 text-mountain-950 font-bold text-sm">+ Buat Room</button>
        </form>
    </div>

    <div class="mb-6 p-4 rounded-xl bg-mountain-900/50 border border-mountain-800">
        <label class="text-sm text-mountain-300 block mb-1">Gabung dengan kode:</label>
        <div class="flex gap-2">
            <input type="text" id="jc" placeholder="Kode room" maxlength="8"
                   class="flex-1 px-4 py-2 rounded-xl bg-mountain-800 border border-mountain-700 text-mountain-100 font-mono uppercase text-sm focus:border-trust-400 outline-none"
                   onkeydown="if(event.key==='Enter'){event.preventDefault();goJoin();}">
            <button type="button"
                    onclick="goJoin()"
                    class="px-4 py-2 rounded-xl bg-mountain-700 text-mountain-200 text-sm">Gabung</button>
        </div>
    </div>

    <script>
        function goJoin() {
            const code = document.getElementById('jc').value.trim().toUpperCase();
            if (code) window.location.href = '/rooms/join/' + encodeURIComponent(code);
        }
    </script>
</div>
<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\laragon\www\thesummit\resources\views/room/index.blade.php ENDPATH**/ ?>