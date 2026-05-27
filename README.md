# System obsługi restauracji

## Aktualny stan projektu

Projekt jest stabilną wersją MVP systemu obsługi restauracji lub baru. Aplikacja obsługuje główny workflow restauracji: logowanie pracowników, role użytkowników, zarządzanie menu i stolikami, składanie zamówień, obsługę kuchni i baru, zmianę statusów pozycji, wydawanie zamówień, rachunek, płatność oraz podstawowy grafik pracy.

Wersja funkcjonalnie domyka Sprint 1 i Sprint 2. Dodatkowo zawiera istotne elementy Sprintu 3 i Sprintu 4: panel kuchni, panel baru, statusy pozycji, historię statusów, obsługę braków produktu, dashboard managera, historię zamówień, rachunki, płatności i grafik pracy.

## Najważniejsze funkcjonalności

- logowanie użytkowników,
- haszowanie haseł w bazie danych,
- role: `admin`, `manager`, `kelner`, `kuchnia`, `bar`,
- przekierowanie użytkownika do panelu zgodnego z jego rolą,
- zarządzanie kategoriami menu,
- zarządzanie pozycjami menu,
- blokada zamawiania niedostępnych pozycji menu,
- zarządzanie stolikami,
- przypisywanie stolików do konkretnych kelnerów,
- blokada usuwania stolika z historią zamówień,
- dashboard kelnera,
- lista stolików kelnera filtrowana po przypisaniu,
- tworzenie zamówienia dla stolika,
- dodawanie pozycji do aktywnego zamówienia,
- obsługa ilości, notatek i cen historycznych pozycji,
- panel kuchni z bieżącym zamówieniem,
- dashboard kuchni z grupowaniem po zamówieniach/stolikach,
- panel baru z bieżącym zamówieniem,
- dashboard baru z grupowaniem po zamówieniach/stolikach,
- informacje czasowe w kuchni i barze: godzina złożenia, czas oczekiwania, start przygotowania,
- zmiana statusów pozycji,
- historia zmian statusów pozycji,
- oznaczanie pozycji jako niemożliwej do przygotowania,
- informacja dla kelnera o brakach/anulowanych pozycjach,
- nieuwzględnianie anulowanych pozycji w rachunku,
- oznaczanie gotowych pozycji jako dostarczonych,
- generowanie rachunku,
- zapis płatności,
- automatyczne zwalnianie stolika po płatności,
- dashboard managera liczony z bazy danych,
- historia zamówień managera z filtrami,
- grafik pracy personelu z widokiem tygodniowym i miesięcznym,
- testy feature dla głównych procesów.

## Role użytkowników

### Admin

- dostęp do panelu administratora,
- dostęp do funkcji managera,
- możliwość wejścia w zarządzanie menu, stolikami, historię zamówień i grafik.

### Manager

- zarządzanie menu,
- zarządzanie stolikami,
- przypisywanie stolików do kelnerów,
- podgląd dashboardu operacyjnego restauracji,
- podgląd sprzedaży dziennej,
- podgląd liczby zamówień i statusów stolików,
- przegląd ostatnich zamówień,
- przegląd najczęściej zamawianych pozycji,
- historia zamówień z filtrowaniem,
- zarządzanie grafikiem pracy.

### Kelner

- dashboard z aktywnymi pozycjami,
- widok wyłącznie własnych przypisanych stolików,
- podgląd pozycji w realizacji,
- podgląd pozycji gotowych do odbioru,
- podgląd anulowanych pozycji lub braków produktu,
- wybór wolnego stolika,
- otwieranie zamówienia,
- dodawanie pozycji do zamówienia,
- podgląd statusów pozycji,
- oznaczanie pozycji jako dostarczonych,
- generowanie rachunku,
- zapis płatności,
- podgląd własnego grafiku w trybie tylko do odczytu.

### Kuchnia

- podgląd pozycji kuchennych do przygotowania,
- widok bieżącego najstarszego zamówienia,
- dashboard wszystkich pozycji kuchennych,
- zmiana statusu pozycji na `w przygotowaniu`,
- zmiana statusu pozycji na `gotowe`,
- oznaczenie pozycji jako niemożliwej do przygotowania,
- podgląd godziny złożenia i czasu oczekiwania.

### Bar

- podgląd napojów do przygotowania,
- widok bieżącego najstarszego zamówienia barowego,
- dashboard wszystkich pozycji barowych,
- zmiana statusu pozycji barowych,
- oznaczenie napoju jako niemożliwego do przygotowania,
- podgląd godziny złożenia i czasu oczekiwania.

## Konta testowe

Po uruchomieniu seederów dostępne są konta testowe:

| Imię i nazwisko | Rola | Login | Hasło |
| --- | --- | --- | --- |
| Administrator Systemu | admin | `admin@example.com` | `password` |
| Monika Majewska | manager | `manager@example.com` | `password` |
| Michał Nowak | kelner | `kelner@example.com` | `password` |
| Agata Kowalska | kelner | `kelner1@example.com` | `password` |
| Jacek Wiśniewski | kelner | `kelner2@example.com` | `password` |
| Marta Zielińska | kelner | `kelner3@example.com` | `password` |
| Tomasz Wójcik | kuchnia | `kuchnia@example.com` | `password` |
| Paweł Baran | bar | `bar@example.com` | `password` |

Hasła są zapisywane w bazie w formie haszowanej przez mechanizm castów modelu `User`.

Przykładowy podział stolików po uruchomieniu seederów:

```text
Michał Nowak (kelner@example.com)       -> stoliki 1, 2, 3
Agata Kowalska (kelner1@example.com)    -> stoliki 4, 5, 6
Jacek Wiśniewski (kelner2@example.com)  -> stoliki 7, 8, 9
Marta Zielińska (kelner3@example.com)   -> stoliki 10, 11, 12
```

Stoliki 13, 14 i 15 pozostają bez przypisanego kelnera jako przykład danych, które manager może później przydzielić ręcznie.

Scenariusz testowania separacji kelnerów:

```text
1. Zaloguj się jako kelner@example.com.
2. Wejdź w panel kelnera i sprawdź widoczne stoliki.
3. Wyloguj się.
4. Zaloguj się jako kelner1@example.com.
5. Sprawdź, że widoczne są inne stoliki.
6. Spróbuj ręcznie wejść w adres formularza zamówienia dla cudzego stolika - system powinien zablokować dostęp.
```

## Główne adresy w aplikacji

```text
/                       strona startowa
/login                  logowanie
/dashboard              przekierowanie zależne od roli
/menu                   publiczny widok menu
/manager/dashboard      dashboard managera
/manager/menu           zarządzanie menu
/manager/tables         zarządzanie stolikami
/manager/orders/history historia zamówień
/schedule               grafik pracy
/waiter/dashboard       dashboard kelnera
/waiter/tables          stoliki kelnera
/kitchen/current        bieżące zamówienie kuchni
/kitchen/dashboard      dashboard kuchni
/bar/current            bieżące zamówienie baru
/bar/dashboard          dashboard baru
```

## Technologie

- Laravel,
- PHP,
- PostgreSQL,
- Blade,
- Tailwind CSS,
- Eloquent ORM,
- Vite,
- PHPUnit / testy feature.

## Szybki start dla nowych osób

Minimalna ścieżka po pobraniu aktualnej wersji projektu:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan optimize:clear
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

Na Windows PowerShell zamiast `cp .env.example .env` można użyć:

```powershell
copy .env.example .env
```

Przed uruchomieniem migracji trzeba utworzyć lokalną bazę PostgreSQL i wpisać poprawne dane połączenia w `.env`.
Domyślnie w README zakładana jest baza `restauracja`.

Po uruchomieniu aplikacja będzie dostępna pod adresem:

```text
http://127.0.0.1:8000
```

## Instalacja projektu

### 1. Instalacja zależności PHP

```bash
composer install
```

### 2. Instalacja zależności frontendowych

```bash
npm install
```

### 3. Przygotowanie pliku środowiskowego

Linux / Git Bash:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
copy .env.example .env
```

### 4. Konfiguracja PostgreSQL

W pliku `.env` należy ustawić dane połączenia z bazą:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=restauracja
DB_USERNAME=postgres
DB_PASSWORD=
```

Przed migracjami baza danych musi istnieć w PostgreSQL. Można ją utworzyć w pgAdminie, PhpStormie albo przez terminal PostgreSQL.

### 5. Zalecane ustawienia lokalne

Dla lokalnego uruchomienia zalecane są ustawienia:

```env
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Takie ustawienia zapobiegają problemom typu `relation "sessions" does not exist`, jeżeli projekt nie korzysta z sesji trzymanych w bazie.

### 6. Wygenerowanie klucza aplikacji

```bash
php artisan key:generate
```

## Migracje, seedery i czysty start na dziś

Standardowe uruchomienie migracji i seederów:

```bash
php artisan migrate --seed
```

Czyste odtworzenie bazy na demo lub zajęcia:

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

Uwaga: `php artisan migrate:fresh --seed` usuwa wszystkie dane z aktualnej bazy i tworzy je od nowa. Używać tylko na bazie lokalnej albo testowej.

Aktualne migracje obejmują:

- użytkowników,
- stoliki restauracyjne z przypisaniem kelnera,
- kategorie menu,
- pozycje menu,
- zamówienia,
- pozycje zamówień,
- historię statusów pozycji,
- płatności,
- grafik pracy (`schedules`).

Aktualne seedery obejmują:

- konta testowe użytkowników,
- stoliki z przykładowym podziałem między kelnerów,
- kategorie i pozycje menu,
- przykładowe zamówienia,
- przykładowe statusy i dane potrzebne do przetestowania workflow.

## Uruchomienie aplikacji

```bash
php artisan serve
```

Domyślny adres lokalny:

```text
http://127.0.0.1:8000
```

## Testy i weryfikacja

Pełny zestaw testów:

```bash
php artisan test
```

Build frontendu:

```bash
npm run build
```

Opcjonalne sprawdzenie stylu kodu:

```bash
vendor/bin/pint --dirty --test
```

Przykładowe testy wybranych obszarów:

```bash
php artisan test --filter=WaiterTableWorkflowTest
php artisan test --filter=KitchenDashboardTest
php artisan test --filter=BarDashboardTest
php artisan test --filter=ManagerDashboardTest
php artisan test --filter=ScheduleManagementTest
php artisan test --filter=EmployeeAccountsSeederTest
```

Testy obejmują między innymi:

- logowanie,
- przekierowanie zależne od roli,
- dostęp do paneli według roli,
- CRUD menu,
- CRUD stolików,
- przypisywanie stolików do kelnerów,
- separację widoczności stolików między kelnerami,
- workflow kelnera,
- tworzenie zamówień,
- dodawanie pozycji do zamówień,
- obsługę kuchni,
- obsługę baru,
- zmianę statusów pozycji,
- anulowanie pozycji z powodu braku produktu,
- widoczność braków dla kelnera,
- nieuwzględnianie anulowanych pozycji w rachunku,
- wydawanie pozycji,
- generowanie rachunku,
- zapis płatności,
- zwalnianie stolika po płatności,
- dashboard managera,
- historię zamówień managera,
- grafik pracy.

## Status sprintów

### Sprint 1

Zrealizowany funkcjonalnie.

Obejmuje:

- konfigurację Laravel,
- migracje,
- seedery,
- logowanie,
- role,
- CRUD menu,
- CRUD stolików,
- layout,
- panel logowania,
- widok menu.

### Sprint 2

Zrealizowany.

Obejmuje:

- ekran kelnera,
- ekran stolików,
- tworzenie zamówień,
- dodawanie pozycji do zamówienia,
- notatki i ilości pozycji,
- przekazywanie pozycji do kuchni i baru,
- działający workflow zamówień.

### Sprint 3

W dużej części zrealizowany.

Obejmuje:

- panel kuchni,
- panel baru,
- dashboard kuchni,
- dashboard baru,
- zmianę statusów pozycji,
- historię statusów,
- obsługę braków produktu,
- wydawanie pozycji przez kelnera.

Nie obejmuje jeszcze pełnego realtime przez WebSockets/Livewire.

### Sprint 4

Częściowo zrealizowany.

Obejmuje:

- rachunek,
- płatność,
- zwolnienie stolika po płatności,
- historię zamówień managera,
- dashboard managera.

Do dalszego rozwoju pozostają między innymi podział rachunku, raporty rozszerzone, drukowanie rachunku i płatności online.

## Najczęstsze problemy

### `APP_KEY is not set`

```bash
php artisan key:generate
```

### Baza danych nie istnieje

Utworzyć bazę PostgreSQL o nazwie ustawionej w `.env`, np.:

```env
DB_DATABASE=restauracja
```

### `relation "sessions" does not exist`

Sprawdzić w `.env`:

```env
SESSION_DRIVER=file
```

### Zmiany w `.env` nie działają

Wyczyścić cache konfiguracji:

```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

### Widoki pokazują starą wersję

Wyczyścić cache widoków:

```bash
php artisan view:clear
```

## Dalszy rozwój

Najbardziej sensowne kolejne kroki:

- pełne automatyczne odświeżanie paneli przez Livewire albo Reverb,
- rozbudowę przypisywania kelnerów o większe strefy sali,
- podział rachunku,
- raporty sprzedażowe managera,
- drukowanie rachunku,
- QR menu,
- dopracowanie widoków mobilnych,
- rozszerzenie dokumentacji wdrożeniowej.
