<x-app>
    <x-slot:title>Edycja kategorii - SmakPrzeszłości</x-slot>

    <section class="w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('manager.podglad') }}" class="text-sm font-bold text-brand-accent hover:text-brand-dark">← Wróć do menu</a>

        <div class="mt-6 rounded-lg border border-brand-dark/15 bg-white p-6 shadow-sm">
            <h1 class="text-3xl font-black text-brand-dark">Edytuj kategorię</h1>

            @if($errors->any())
                <div class="mt-5 rounded-lg border border-red-700 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <strong>Popraw błędy formularza:</strong>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('manager.menu-categories.update', $category) }}" class="mt-6 space-y-4">
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
