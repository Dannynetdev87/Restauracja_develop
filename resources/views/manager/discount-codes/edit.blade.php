<x-app>
    <x-slot:title>Edycja kodu rabatowego - SmakPrzeszlosci</x-slot>

    <section class="w-full max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-5 border-b border-brand-dark/10 pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="text-sm font-bold uppercase text-brand-accent">Panel managera</span>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-brand-dark">Edycja kodu rabatowego</h1>
                <p class="mt-1 text-sm text-brand-accent">Kod i licznik uzyc sa pokazane informacyjnie.</p>
            </div>

            <a href="{{ route('manager.discount-codes.index') }}"
               class="w-fit rounded-md border border-brand-dark/20 bg-white px-4 py-2 text-sm font-bold text-brand-dark hover:bg-brand-light">
                Powrot do listy
            </a>
        </div>

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

        <div class="rounded-lg border border-brand-dark/15 bg-white p-5 shadow-sm">
            <div class="grid gap-4 border-b border-brand-dark/10 pb-5 sm:grid-cols-2">
                <div>
                    <span class="block text-xs font-bold uppercase text-brand-accent">Kod</span>
                    <strong class="mt-1 block font-mono text-xl text-brand-dark">{{ $discountCode->code }}</strong>
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase text-brand-accent">Uzycia</span>
                    <strong class="mt-1 block text-xl text-brand-dark">{{ $discountCode->used_count }}</strong>
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase text-brand-accent">Utworzyl</span>
                    <span class="mt-1 block text-brand-dark">{{ $discountCode->createdBy?->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase text-brand-accent">Utworzono</span>
                    <span class="mt-1 block text-brand-dark">{{ $discountCode->created_at?->format('Y-m-d H:i') }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('manager.discount-codes.update', $discountCode) }}" class="mt-5 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="type" class="block text-sm font-bold text-brand-dark">Typ</label>
                    <select id="type" name="type" required class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', $discountCode->type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="value" class="block text-sm font-bold text-brand-dark">Wartosc</label>
                    <input id="value" name="value" type="number" min="0.01" step="0.01" value="{{ old('value', $discountCode->value) }}" required class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                </div>

                <div>
                    <label for="usage_limit" class="block text-sm font-bold text-brand-dark">Limit uzyc</label>
                    <input id="usage_limit" name="usage_limit" type="number" min="1" value="{{ old('usage_limit', $discountCode->usage_limit) }}" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                </div>

                <div>
                    <label for="starts_at" class="block text-sm font-bold text-brand-dark">Wazny od</label>
                    <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $discountCode->starts_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                </div>

                <div>
                    <label for="expires_at" class="block text-sm font-bold text-brand-dark">Wazny do</label>
                    <input id="expires_at" name="expires_at" type="datetime-local" value="{{ old('expires_at', $discountCode->expires_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                </div>

                <label class="flex items-center gap-2 text-sm font-bold text-brand-dark">
                    <input name="is_active" type="checkbox" value="1" class="rounded border-brand-dark/20" @checked(old('is_active', $discountCode->is_active))>
                    Aktywny
                </label>

                <button type="submit" class="rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light hover:bg-brand-accent">
                    Zapisz zmiany
                </button>
            </form>
        </div>
    </section>
</x-app>
