<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Try-On Commerce Studio',
    'hideHeader' => false,
]));

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

foreach (array_filter(([
    'title' => 'Try-On Commerce Studio',
    'hideHeader' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?></title>
    <style>
        :root { --fs-caption: 12px; --fs-label: 13px; --fs-control: 14px; --fs-body: 15px; --fs-heading: 34px; }
        body { font-family: Arial, sans-serif; font-size: var(--fs-body); line-height: 1.5; margin: 0; background: #f5f7fb; color: #1f2937; }
        h1 { font-size: var(--fs-heading); line-height: 1.12; margin: 0 0 16px; }
        label { display: block; font-size: var(--fs-control); margin: 8px 0 4px; }
        p { margin: 10px 0; font-size: var(--fs-control); }
        header { background: #111827; color: #fff; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; }
        header strong { font-size: 18px; line-height: 1.15; }
        nav a { color: #fff; margin-right: 12px; text-decoration: none; font-size: var(--fs-control); }
        main { padding: 20px; max-width: 1100px; margin: 0 auto; }
        .card { background: #fff; border-radius: 8px; padding: 16px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        table { width: 100%; border-collapse: collapse; font-size: var(--fs-body); }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 8px; text-align: left; vertical-align: top; }
        th { font-size: var(--fs-label); }
        input, select, button { padding: 8px 10px; width: 100%; margin: 4px 0; box-sizing: border-box; font-size: var(--fs-control); }
        button { width: auto; cursor: pointer; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); gap: 12px; }
        .text-danger { color: #b91c1c; }
        .text-success { color: #065f46; }
        .inline { display: inline; }
    </style>
</head>
<body>
<?php if(!$hideHeader): ?>
    <header>
        <div><strong>Try-On Commerce Studio</strong></div>
        <nav>
            <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('seller.dashboard')); ?>">Dashboard</a>
                <a href="<?php echo e(route('seller.products.index')); ?>">Products</a>
                <a href="<?php echo e(route('seller.settings.index')); ?>">Settings</a>
                <?php $mySeller = auth()->user()->seller; ?>
                <?php if($mySeller): ?>
                    <a href="/<?php echo e($mySeller->slug); ?>" target="_blank">Open Store</a>
                <?php endif; ?>
                <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit">Logout</button>
                </form>
            <?php endif; ?>
        </nav>
    </header>
<?php endif; ?>
<main>
    <?php if(session('success')): ?>
        <p class="text-success"><?php echo e(session('success')); ?></p>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="card text-danger">
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php echo e($slot); ?>

</main>
</body>
</html>
<?php /**PATH D:\AI Virtual Try-On Platform\Try-On-Commerce-Studio\resources\views/components/layouts/app.blade.php ENDPATH**/ ?>