<x-app>
    <x-slot:title>Edycja kategorii - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8 flex flex-col gap-5 border-b border-brand-dark/10 pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="text-sm font-bold uppercase text-brand-accent">Panel managera</span>
                <h1 class="mt-1 text-3xl font-black text-brand-dark">Edytuj kategorię</h1>
            </div>

            <a href="{{ route('manager.dashboard') }}"
               class="w-fit rounded-md border border-brand-dark/20 bg-white px-4 py-2 text-sm font-bold text-brand-dark hover:bg-brand-light">
                Powrót do panelu
            </a>
        </div>

        @if($errors->any())
            <div class="rounded-lg border border-red-700 bg-red-50 px-4 py-3 text-sm text-red-800">
                <strong>Popraw błędy formularza:</strong>
                <ul class="mt-2 list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-lg border border-brand-dark/15 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('manager.menu-categories.update', $category) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-bold text-brand-dark">Nazwa</label>
                    <input id="name" name="name" value="{{ old('name', $category->name) }}" required maxlength="255" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-bold text-brand-dark">Kolejność</label>
                    <input id="sort_order" name="sort_order" type="number" min="0" max="999" value="{{ old('sort_order', $category->sort_order) }}" class="mt-1 w-full rounded-md border border-brand-dark/20 px-3 py-2">
                </div>

                <label class="flex items-center gap-2 text-sm font-semibold text-brand-dark">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active)) class="rounded border-brand-dark/30">
                    Aktywna
                </label>

                <button type="submit" class="rounded-md bg-brand-dark px-4 py-2 text-sm font-bold text-brand-light hover:bg-brand-accent">
                    Zapisz zmiany
                </button>
            </form>
        </div>
    </section>
</x-app>
