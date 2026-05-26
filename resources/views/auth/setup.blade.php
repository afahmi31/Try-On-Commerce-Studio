<x-layouts.app :title="'Initial Setup'" :hide-header="true">
    <style>
        .setup-wrap {
            min-height: calc(100vh - 140px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px 40px;
        }
        .setup-card {
            width: 100%;
            max-width: 560px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 28px 24px 22px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }
        .setup-title {
            margin: 0;
            font-size: 36px;
            line-height: 1.1;
            text-align: center;
            color: #0f172a;
        }
        .setup-subtitle {
            margin: 10px 0 18px;
            font-size: 14px;
            text-align: center;
            color: #64748b;
        }
        .setup-form label {
            margin: 12px 0 6px;
            font-size: 13px;
            color: #0f172a;
            font-weight: 600;
        }
        .setup-form input {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            height: 44px;
            margin: 0;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .setup-form input:focus {
            outline: none;
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.14);
        }
        .setup-btn {
            width: 100%;
            margin-top: 18px;
            height: 44px;
            border: 0;
            border-radius: 8px;
            background: linear-gradient(135deg, #0e7490, #0369a1);
            color: #ffffff;
            font-weight: 700;
            letter-spacing: .2px;
        }
        .setup-btn:hover {
            background: linear-gradient(135deg, #0f766e, #075985);
        }
        .setup-section-title {
            margin: 16px 0 4px;
            font-size: 14px;
            color: #0f172a;
            font-weight: 700;
        }
        @media (max-width: 640px) {
            .setup-wrap {
                min-height: auto;
                padding-top: 10px;
            }
            .setup-card {
                padding: 22px 16px 18px;
                border-radius: 10px;
            }
            .setup-title {
                font-size: 30px;
            }
        }
    </style>

    <div class="setup-wrap">
        <div class="setup-card">
            <h1 class="setup-title">Initial Setup</h1>
            <p class="setup-subtitle">Buat akun owner dan toko pertama untuk mulai memakai aplikasi.</p>

            <form method="POST" action="{{ route('setup.store') }}" class="setup-form">
                @csrf

                <p class="setup-section-title">Owner Account</p>
                <label>Nama Owner</label>
                <input type="text" name="owner_name" value="{{ old('owner_name') }}" required>

                <label>Email Owner</label>
                <input type="email" name="owner_email" value="{{ old('owner_email') }}" required>

                <label>Password</label>
                <input type="password" name="owner_password" required>

                <label>Konfirmasi Password</label>
                <input type="password" name="owner_password_confirmation" required>

                <p class="setup-section-title">Store</p>
                <label>Nama Toko</label>
                <input type="text" name="store_name" value="{{ old('store_name') }}" required>

                <label>Store Slug (URL)</label>
                <input type="text" name="store_slug" value="{{ old('store_slug') }}" placeholder="contoh: tokomu" required>

                <button type="submit" class="setup-btn">Simpan dan Masuk Dashboard</button>
            </form>
        </div>
    </div>
</x-layouts.app>
