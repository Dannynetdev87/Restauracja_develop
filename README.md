# System obsługi restauracji

## Aktualny stan projektu

Projekt jest stabilną wersją MVP systemu obsługi restauracji. Aplikacja obsługuje główne procesy restauracyjne: logowanie pracowników, role użytkowników, zarządzanie menu i stolikami, tworzenie zamówień, obsługę kuchni i baru, statusy pozycji, wydawanie zamówień, rachunki oraz płatności.

Wersja funkcjonalnie domyka Sprint 1 i Sprint 2 oraz zawiera część funkcji ze Sprintu 3 i Sprintu 4.

## Co aktualnie działa

- logowanie użytkowników,
- dostęp do paneli zależnie od roli,
- role: admin, manager, kelner, kuchnia, bar,
- zarządzanie menu,
- zarządzanie stolikami,
- ekran kelnera,
- wybór stolika przez kelnera,
- tworzenie zamówienia,
- dodawanie pozycji do zamówienia,
- obsługa ilości i notatek,
- zapisywanie ceny historycznej pozycji zamówienia,
- panel kuchni,
- panel baru,
- zmiana statusów pozycji,
- historia zmian statusów,
- oznaczanie pozycji jako dostarczonych,
- generowanie rachunku,
- zapis płatności,
- automatyczne zwalnianie stolika po płatności,
- podstawowe testy funkcjonalne.

## Główne role użytkowników

### Manager / Admin

- zarządzanie menu,
- zarządzanie stolikami,
- dostęp do części administracyjnej systemu.

### Kelner

- wybór stolika,
- otwieranie zamówienia,
- dodawanie pozycji,
- podgląd statusów,
- oznaczanie pozycji jako dostarczonych,
- generowanie rachunku,
- zapis płatności.

### Kuchnia

- podgląd pozycji do przygotowania,
- zmiana statusu pozycji na w przygotowaniu,
- zmiana statusu pozycji na gotowe.

### Bar

- podgląd napojów do przygotowania,
- zmiana statusu pozycji barowych,
- obsługa napojów niezależnie od kuchni.

## Konta testowe

Po uruchomieniu seederów dostępne są konta testowe:

```text
admin@example.com / password
manager@example.com / password
kelner@example.com / password
kuchnia@example.com / password
bar@example.com / password
```

Hasła są zapisywane w bazie w formie haszowanej.

## Instrukcja uruchomienia projektu

Poniższa instrukcja zakłada, że projekt został sklonowany z GitHuba, np. przez PhpStorm.

### 1. Instalacja zależności PHP

```bash
composer install
```

### 2. Instalacja zależności frontendowych

```bash
npm install
```

### 3. Przygotowanie pliku środowiskowego

```bash
cp .env.example .env
```

Na Windowsie, jeśli `cp` nie działa w terminalu, można użyć:

```powershell
copy .env.example .env
```

### 4. Konfiguracja bazy danych

W pliku `.env` należy ustawić dane do PostgreSQL, np.:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=restauracja
DB_USERNAME=postgres
DB_PASSWORD=
```

Przed migracjami trzeba upewnić się, że baza danych istnieje w PostgreSQL.

Przykładowa nazwa bazy:

```text
restauracja
```

Jeżeli baza nie istnieje, trzeba ją utworzyć ręcznie w pgAdminie, PhpStormie albo przez terminal PostgreSQL.

### 5. Ważne ustawienia sesji, cache i kolejki

Dla lokalnego uruchomienia najbezpieczniej ustawić:

```env
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Takie wartości są już ustawione w `.env.example`. Zapobiegają one błędom typu:

```text
relation "sessions" does not exist
table sessions does not exist
```

Błąd ten pojawia się, gdy aplikacja próbuje używać sesji w bazie danych, ale nie została utworzona tabela `sessions`.

### 6. Wygenerowanie klucza aplikacji

```bash
php artisan key:generate
```

### 7. Migracje i dane testowe

```bash
php artisan migrate --seed
```

Ta komenda tworzy tabele i dodaje przykładowe dane, w tym użytkowników testowych, stoliki, kategorie menu, pozycje menu i przykładowe zamówienia.

Jeżeli baza była wcześniej używana i trzeba ją wyczyścić, można użyć:

```bash
php artisan migrate:fresh --seed
```

Uwaga: ta komenda usuwa istniejące dane z bazy.

### 8. Zbudowanie plików frontendowych

```bash
npm run build
```

### 9. Uruchomienie aplikacji

```bash
php artisan serve
```

Domyślnie aplikacja będzie dostępna pod adresem:

```text
http://127.0.0.1:8000
```

## Najczęstsze problemy

### Błąd: APP_KEY is not set

Należy uruchomić:

```bash
php artisan key:generate
```

### Błąd: database does not exist

Trzeba utworzyć bazę danych PostgreSQL o nazwie ustawionej w `.env`, np.:

```env
DB_DATABASE=restauracja
```

### Błąd: table sessions does not exist

Należy sprawdzić w `.env`:

```env
SESSION_DRIVER=file
```

Jeżeli ustawione jest:

```env
SESSION_DRIVER=database
```

Laravel będzie oczekiwał tabeli `sessions`.

### Błąd po zmianach w `.env`

Warto wyczyścić cache konfiguracji:

```bash
php artisan config:clear
php artisan cache:clear
```

## Testy i weryfikacja

Testy funkcjonalne można uruchomić poleceniem:

```bash
php artisan test
```

Sprawdzenie formatowania kodu:

```bash
vendor/bin/pint --dirty --test
```

Sprawdzenie buildu frontendu:

```bash
npm run build
```

Testy obejmują między innymi:

- logowanie,
- role użytkowników,
- CRUD menu,
- CRUD stolików,
- workflow kelnera,
- panel kuchni,
- panel baru,
- zmianę statusów,
- wydawanie pozycji,
- rachunki,
- płatności.

## Status sprintów

### Sprint 1

Zrealizowany funkcjonalnie, bez części DevOps.

Obejmuje:

- logowanie,
- role,
- migracje,
- CRUD menu,
- CRUD stolików,
- podstawowy layout,
- panel logowania,
- widok menu.

### Sprint 2

Zrealizowany.

Obejmuje:

- ekran kelnera,
- ekran stolików,
- tworzenie zamówień,
- dodawanie pozycji do zamówienia,
- przekazywanie pozycji do kuchni,
- działający workflow zamówień.

### Sprint 3

Częściowo zrealizowany.

Obejmuje:

- panel kuchni,
- panel baru,
- zmianę statusów pozycji,
- historię statusów,
- wydawanie pozycji przez kelnera.

Nie obejmuje jeszcze realtime.

### Sprint 4

Częściowo zrealizowany.

Obejmuje:

- rachunek,
- płatność,
- zwolnienie stolika po płatności.

Do zrobienia pozostaje między innymi historia zamówień managera i dashboard managera.

## Dalszy rozwój

Planowane lub możliwe do dodania funkcje:

- historia zamówień managera,
- dashboard managera,
- raporty sprzedaży,
- podział rachunku,
- drukowanie rachunku,
- powiadomienia realtime,
- dalsze dopracowanie widoków mobilnych.
