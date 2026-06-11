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
                Witamy w <strong>SmakPrzeszłości</strong>! Nasza restauracja powstała z pasji do odkrywania tradycyjnych receptur i podawania ich w nowoczesnej, przystępnej formie. Dbamy o itu, aby każdy kęs przeniósł Cię w podróż pełną kulinarnych wspomnień.
            </p>

            <p class="leading-relaxed">
                Wszystkie nasze dania przygotowujemy od podstaw, korzystając wyłącznie ze świeżych, lokalnych produktów od sprawdzonych dostawców. Nasz zespół kucharzy dba o niepowtarzalny smak, a profesjonalna obsługa kelnerska dokłada starań, aby wizyta u nas była niezapomnianym przeżyciem.
            </p>

            {{-- AUTOMATYCZNA SEKCJA ZESPOŁU Z BAZY DANYCH --}}
            @if($teamByRole && $teamByRole->count() > 0)
                <div class="border-t border-brand-dark/10 pt-6 mt-6">
                    <h2 class="font-black text-xl mb-6 text-brand-dark tracking-tight">Nasz Zespół</h2>

                    <div class="space-y-6">
                        @php
                            $roleNames = [
                                'admin' => 'Dyrekcja',
                                'manager' => 'Managerowie',
                                'kuchnia' => 'Szefowie Kuchni',
                                'kelner' => 'Obsługa Kelnerska',
                                'bar' => 'Barmani'
                            ];
                        @endphp

                        @foreach($roleNames as $roleKey => $roleLabel)
                            {{-- Pętla odpala się raz na każdą rolę --}}
                            @if(isset($teamByRole[$roleKey]))
                                <div class="bg-brand-dark/[0.02] p-6 rounded-xl border border-brand-dark/5">
                                    <h3 class="text-sm font-bold uppercase tracking-wider text-brand-accent mb-4">
                                        {{ $roleLabel }}
                                    </h3>

                                    {{-- Siatka na pracowników wewnątrz danej kategorii --}}
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 justify-items-center sm:justify-items-start">
                                        @foreach($teamByRole[$roleKey] as $employee)
                                            <div class="flex flex-col items-center text-center space-y-2 p-2">

                                                {{-- Miejsce na awatar wstrzykiwany przez JS --}}
                                                <div
                                                    class="user-avatar-placeholder rounded-full bg-gray-200 flex items-center justify-center shadow-sm overflow-hidden text-gray-500"
                                                    data-firstname="{{ explode(' ', $employee->name)[0] }}"
                                                    style="width: 80px; height: 80px;">
                                                </div>

                                                {{-- Imię i nazwisko pod spodem --}}
                                                <span class="text-sm font-semibold text-brand-dark/90 block mt-1 leading-tight">
                                                    {{ $employee->name }}
                                                </span>

                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Uniwersalna sylwetka kobiety z jasnoczerwonym / różowym tłem
            const femaleSvg = `
        <svg class="w-full h-full p-2 text-gray-500" style="background-color: #fce7f3;" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
        </svg>`;

            // Uniwersalna sylwetka mężczyzny z jasnoniebieskim tłem
            const maleSvg = `
        <svg class="w-full h-full p-2 text-gray-500" style="background-color: #dbeafe;" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
        </svg>`;

            // Łapiemy wszystkie kontenery awatarów
            const placeholders = document.querySelectorAll('.user-avatar-placeholder');

            placeholders.forEach(div => {
                const firstName = div.getAttribute('data-firstname').toLowerCase().trim();

                // Wyjątki w polskim języku (męskie imiona kończące się na "a")
                const maleExceptions = ['kuba', 'jakub', 'bonawentura', 'jarema', 'kosma'];

                // Logika: Jeśli kończy się na "a" i nie ma go na liście wyjątków -> dostaje czerwone tło
                if (firstName.endsWith('a') && !maleExceptions.includes(firstName)) {
                    div.innerHTML = femaleSvg;
                } else {
                    // W każdym innym wypadku -> niebieskie tło
                    div.innerHTML = maleSvg;
                }
            });
        });
    </script>
</x-app>
