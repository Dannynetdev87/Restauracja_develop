<x-app>
    <x-slot:title>Menu - SmakPrzeszłości</x-slot>

    <x-slot:styles>
        @vite(['resources/css/menu.css'])
    </x-slot:styles>

    <x-slot:scripts>
        @vite(['resources/js/menu.js'])
    </x-slot:scripts>

    <div class="container-main py-10">
        <div class="menu-header mb-6">
            <h1 class="text-3xl font-black text-brand-dark">Menu</h1>
        </div>

        <div class="food">
            <div class="box">
                <h2 class="text-2xl font-bold text-brand-accent mb-4 border-b pb-2">Zupy</h2>
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

            <div class="box mt-8">
                <h2 class="text-2xl font-bold text-brand-accent mb-4 border-b pb-2">Coś innego</h2>
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
</x-app>
