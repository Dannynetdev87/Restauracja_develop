<x-app>
    <x-slot:title>O nas - SmakPrzeszłości</x-slot>

    <section class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <span class="welcome-badge">Poznaj nas bliżej</span>
            <h1 class="welcome-title mt-4">O restauracji SmakPrzeszłości</h1>
            <p class="welcome-desc">
                Tradycja połączona z nowoczesnym podejściem do gastronomii.
            </p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-brand-dark/5 space-y-6 text-brand-dark">
            <p class="leading-relaxed">
                Witamy w <strong>SmakPrzeszłości</strong>! Nasza restauracja powstała z pasji do odkrywania tradycyjnych receptur i podawania ich w nowoczesnej, przystępnej formie. Dbamy o to, aby każdy kęs przeniósł Cię w podróż pełną kulinarnych wspomnień.
            </p>

            <p class="leading-relaxed">
                Wszystkie nasze dania przygotowujemy od podstaw, korzystając wyłącznie ze świeżych, lokalnych produktów od sprawdzonych dostawców. Nasz zespół kucharzy dba o niepowtarzalny smak, a profesjonalna obsługa kelnerska dokłada starań, aby wizyta u nas była niezapomnianym przeżyciem.
            </p>

            <div class="border-t border-brand-dark/10 pt-6">
                <h3 class="font-bold text-lg mb-2">Godziny otwarcia:</h3>
                <ul class="text-sm text-brand-accent space-y-1">
                    <li>Poniedziałek - Czwartek: 12:00 - 21:00</li>
                    <li>Piątek - Sobota: 12:00 - 23:00</li>
                    <li>Niedziela: 12:00 - 20:00</li>
                </ul>
            </div>
        </div>

        <div class="welcome-actions mt-10">
            <a href="{{ route('home') }}" class="btn-welcome-secondary">
                Powrót do stolików
            </a>
            <a href="{{ route('menu.index') }}" class="btn-welcome-primary">
                Zobacz menu
            </a>
        </div>
    </section>
</x-app>
