<x-layout>
    <x-slot:title>Zarządzanie Menu - SmakPrzeszłości</x-slot>

    <x-slot:styles>
        @vite(['resources/css/menu.css'])
    </x-slot:styles>

    <x-slot:scripts>
        @vite(['resources/js/menu.js'])
    </x-slot:scripts>

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
    </div>
</x-layout>
