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
<nav></nav>
<main>
    <div class="container-main">
        <div class="menu-header">
            <h1>Menu</h1>
            <a href="#" class="btn-add">Dodaj pozycję</a>
        </div>

        <div class="food">
            <div class="box">
                <h2>Zupy</h2>
                <div class="box-content">
                    <p class="nazwa-potrawy">rosół</p>
                    <div class="dish-actions">
                        <span class="dish-price">6.99</span>
                        <a href="#" class="action-link edit-link">Edytuj</a>
                        <a href="#" class="action-link delete-link">Usuń</a>
                    </div>
                </div>
            </div>

            <div class="box">
                <h2>Inna kat.</h2>
                <div class="box-content">
                    <p class="nazwa-potrawy">Coś innego</p>
                    <div class="dish-actions">
                        <span class="dish-price">Cena</span>
                        <a href="#" class="action-link edit-link">Edytuj</a>
                        <a href="#" class="action-link delete-link">Usuń</a>
                    </div>
                </div>
            </div>
        </div>
    </div> </main>
<footer></footer>
</body>
</html>
