@props([
    'title' => 'Try-On Commerce Studio',
    'hideHeader' => false,
])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
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
@if(!$hideHeader)
    <header>
        <div><strong>Try-On Commerce Studio</strong></div>
        <nav>
            @auth
                <a href="{{ route('seller.dashboard') }}">Dashboard</a>
                <a href="{{ route('seller.products.index') }}">Products</a>
                <a href="{{ route('seller.settings.index') }}">Settings</a>
                @php $mySeller = auth()->user()->seller; @endphp
                @if($mySeller)
                    <a href="/{{ $mySeller->slug }}" target="_blank">Open Store</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            @endauth
        </nav>
    </header>
@endif
<main>
    @if(session('success'))
        <p class="text-success">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <div class="card text-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{ $slot }}
</main>
</body>
</html>
