<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Login','hideHeader' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Login'),'hide-header' => true]); ?>
    <style>
        .login-wrap {
            min-height: calc(100vh - 140px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px 40px;
        }
        .login-card {
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 28px 24px 22px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }
        .login-title {
            margin: 0;
            font-size: 38px;
            line-height: 1.1;
            text-align: center;
            color: #0f172a;
        }
        .login-subtitle {
            margin: 8px 0 18px;
            font-size: 14px;
            text-align: center;
            color: #64748b;
        }
        .login-form label {
            margin: 12px 0 6px;
            font-size: 13px;
            color: #0f172a;
            font-weight: 600;
        }
        .login-form input {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            height: 44px;
            margin: 0;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .login-form input:focus {
            outline: none;
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.14);
        }
        .login-btn {
            width: 100%;
            margin-top: 16px;
            height: 44px;
            border: 0;
            border-radius: 8px;
            background: linear-gradient(135deg, #0e7490, #0369a1);
            color: #ffffff;
            font-weight: 700;
            letter-spacing: .2px;
        }
        .login-btn:hover {
            background: linear-gradient(135deg, #0f766e, #075985);
        }
        @media (max-width: 640px) {
            .login-wrap {
                min-height: auto;
                padding-top: 10px;
            }
            .login-card {
                padding: 22px 16px 18px;
                border-radius: 10px;
            }
            .login-title {
                font-size: 32px;
            }
        }
    </style>

    <div class="login-wrap">
        <div class="login-card">
            <h1 class="login-title">Login</h1>
            <p class="login-subtitle">Welcome back to Try-On Commerce Studio.</p>

            <form method="POST" action="<?php echo e(route('login.submit')); ?>" class="login-form">
            <?php echo csrf_field(); ?>
            <label>Email</label>
            <input type="email" name="email" value="<?php echo e(old('email')); ?>" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit" class="login-btn">Login</button>
            </form>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH D:\AI Virtual Try-On Platform\Try-On-Commerce-Studio\resources\views/auth/login.blade.php ENDPATH**/ ?>