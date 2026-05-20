<!doctype html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Menu</title>

    @vite(['resources/css/menu.css', 'resources/js/menu.js'])
</head>
<body>
<header></header>
<nav class="flex items-center justify-center">
    @auth
        @if(auth()->user()->isManager())
            <a href="{{ route('manager.podglad') }}" class="btn-add">Podgląd managera</a>
        @endif
    @endauth
</nav>
<main>
    <div class="container-main">
        <div class="menu-header">
            <h1>Menu</h1>
        </div>
        <div class="food">

            <div class="box">
                <h2>Zupy</h2>
                <div class="box-content">
                    <p>rosół</p>
                    <p>6.99</p>
                </div>
                <div class="box-content">
                    <p>rosół</p>
                    <p>6.99</p>
                </div>
                <div class="box-content">
                    <p>rosół</p>
                    <p>6.99</p>
                </div>
            </div>

            <div class="box">
                <h2>Coś innego</h2>
                <div class="box-content">
                    <p>Nazwa</p>
                    <p>Cena</p>
                </div>
                <div class="box-content">
                    <p>Nazwa</p>
                    <p>Cena</p>
                </div>
                <div class="box-content">
                    <p>Nazwa</p>
                    <p>Cena</p>
                </div>
            </div>

        </div>
    </div>
</main>
<footer></footer>
</body>
</html>
