<x-app>
    <x-slot:title>Kody rabatowe - SmakPrzeszlosci</x-slot>

    <section class="w-full max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-5 border-b border-brand-dark/10 pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="text-sm font-bold uppercase text-brand-accent">Panel managera</span>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-brand-dark">Kody rabatowe</h1>
                <p class="mt-1 text-sm text-brand-accent">Tworzenie i kontrola kodow obnizajacych wartosc platnosci.</p>
            </div>

            <a href="{{ route('manager.dashboard') }}"
               class="ml-auto w-56 rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-center text-sm font-bold text-brand-dark transition-all duration-200 ease-out hover:bg-brand-light hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40">
                Powrot do panelu
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg border border-green-700 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-700 bg-red-50 px-4 py-3 text-sm text-red-800">
                <strong>Popraw bledy formularza:</strong>
                <ul class="mt-2 list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid min-w-0 gap-6 xl:grid-cols-[360px_1fr]">
            <div class="h-fit rounded-lg border border-brand-dark/15 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="text-xl font-black text-brand-dark">Dodaj kod</h2>

                <form method="POST" action="{{ route('manager.discount-codes.store') }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label for="code" class="block text-sm font-bold text-brand-dark">Kod</label>
                        <input id="code" name="code" type="text" maxlength="50" value="{{ old('code') }}" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2 uppercase">
                        <p class="mt-1 text-xs text-brand-accent">Zostaw puste, aby wygenerować kod automatycznie.</p>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-bold text-brand-dark">Typ</label>
                        <select id="type" name="type" required class="mt-1 w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2">
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="value" class="block text-sm font-bold text-brand-dark">Wartosc</label>
                        <input id="value" name="value" type="number" min="0.01" step="0.01" value="{{ old('value') }}" required class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                    </div>

                    <div>
                        <label for="usage_limit" class="block text-sm font-bold text-brand-dark">Limit uzyc</label>
                        <input id="usage_limit" name="usage_limit" type="number" min="1" value="{{ old('usage_limit') }}" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                    </div>

                    <div>
                        <label for="starts_at" class="block text-sm font-bold text-brand-dark">Wazny od</label>
                        <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at') }}" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                    </div>

                    <div>
                        <label for="expires_at" class="block text-sm font-bold text-brand-dark">Wazny do</label>
                        <input id="expires_at" name="expires_at" type="datetime-local" value="{{ old('expires_at') }}" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                    </div>

                    <label class="flex items-center gap-2 text-sm font-bold text-brand-dark">
                        <input name="is_active" type="checkbox" value="1" class="rounded border-brand-dark/20" @checked(old('is_active', '1'))>
                        Aktywny
                    </label>

                    <button type="submit" class="w-full rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light transition-all duration-200 ease-out hover:bg-brand-accent hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40 sm:w-auto">
                        Dodaj kod
                    </button>
                </form>
            </div>

            <div class="min-w-0 rounded-lg border border-brand-dark/15 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="text-xl font-black text-brand-dark">Lista kodow</h2>

                <div class="mt-5 w-full overflow-x-auto">
                    <table class="min-w-full whitespace-nowrap text-left text-sm">
                        <thead class="border-b border-brand-dark/10 text-xs uppercase text-brand-accent">
                            <tr>
                                <th class="py-3 pr-4">Kod</th>
                                <th class="py-3 pr-4">Typ</th>
                                <th class="py-3 pr-4">Wartosc</th>
                                <th class="py-3 pr-4">Status</th>
                                <th class="py-3 pr-4">Limit</th>
                                <th class="py-3 pr-4">Uzycia</th>
                                <th class="py-3 pr-4">Waznosc</th>
                                <th class="py-3 pr-4">Utworzyl</th>
                                <th class="py-3 pr-4">Utworzono</th>
                                <th class="py-3 pr-4 text-right">Akcje</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-dark/10">
                            @forelse($discountCodes as $discountCode)
                                <tr>
                                    <td class="py-3 pr-4 font-mono font-black text-brand-dark">{{ $discountCode->code }}</td>
                                    <td class="py-3 pr-4">{{ $types[$discountCode->type] ?? $discountCode->type }}</td>
                                    <td class="py-3 pr-4">
                                        @if($discountCode->type === \App\Models\DiscountCode::TYPE_PERCENT)
                                            {{ number_format((float) $discountCode->value, 2, ',', ' ') }}%
                                        @else
                                            {{ number_format((float) $discountCode->value, 2, ',', ' ') }} zl
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $discountCode->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                                            {{ $discountCode->is_active ? 'Aktywny' : 'Nieaktywny' }}
                                        </span>
                                    </td>
                                    <td class="py-3 pr-4">{{ $discountCode->usage_limit ?? 'Bez limitu' }}</td>
                                    <td class="py-3 pr-4">{{ $discountCode->used_count }}</td>
                                    <td class="py-3 pr-4 text-xs">
                                        <span class="block">Od: {{ $discountCode->starts_at?->format('Y-m-d H:i') ?? '-' }}</span>
                                        <span class="block">Do: {{ $discountCode->expires_at?->format('Y-m-d H:i') ?? '-' }}</span>
                                    </td>
                                    <td class="py-3 pr-4">{{ $discountCode->createdBy?->name ?? '-' }}</td>
                                    <td class="py-3 pr-4">{{ $discountCode->created_at?->format('Y-m-d H:i') }}</td>
                                    <td class="py-3 pr-4">
                                        <div class="flex flex-col justify-end gap-2 sm:flex-row">
                                            <a href="{{ route('manager.discount-codes.edit', $discountCode) }}" class="rounded-md border border-brand-dark/20 px-3 py-2 text-center text-xs font-bold text-brand-dark transition-all duration-200 ease-out hover:bg-brand-light hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40">
                                                Edytuj
                                            </a>
                                            <form method="POST" action="{{ route('manager.discount-codes.toggle', $discountCode) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-full rounded-md border border-brand-dark/20 bg-white px-3 py-2 text-xs font-bold text-brand-dark transition-all duration-200 ease-out hover:bg-brand-light hover:shadow-md active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent/40">
                                                    {{ $discountCode->is_active ? 'Dezaktywuj' : 'Aktywuj' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-5 text-center text-brand-accent">Brak kodow rabatowych.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5">
                    {{ $discountCodes->links() }}
                </div>
            </div>
        </div>
    </section>
</x-app>
