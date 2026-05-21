# Restauracja 2026

Projekt Laravel dla akademickiego systemu obsługi restauracji.

## Wymagania

- PHP 8.3+
- Composer
- Node.js + npm
- PostgreSQL
- Git

## Szybkie uruchomienie na Windows

Najprostsza ścieżka dla osób pobierających projekt na Windows:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
.\scripts\setup-windows.ps1 -DbUsername postgres -DbPassword "twoje_haslo"
php artisan serve
```

Jeżeli lokalny PostgreSQL nie ma hasła, można pominąć parametr `-DbPassword`. Jeżeli plik `.env` już istnieje, skrypt nie nadpisuje hasła bazy, dopóki nie podasz `-DbPassword`.

Skrypt wykona kolejno:

- instalację zależności PHP przez Composer,
- instalację zależności frontendu przez npm,
- utworzenie pliku `.env`, jeśli go nie ma,
- ustawienie PostgreSQL, sesji, cache i kolejki w `.env`,
- wygenerowanie `APP_KEY`,
- uruchomienie migracji i seederów,
- zbudowanie frontendu przez `npm run build`.

Jeżeli baza ma zostać wyczyszczona i utworzona od nowa:

```powershell
.\scripts\setup-windows.ps1 -DbUsername postgres -DbPassword "twoje_haslo" -Fresh
```

Jeżeli zależności są już zainstalowane i chcesz tylko poprawić konfigurację oraz migracje:

```powershell
.\scripts\setup-windows.ps1 -DbUsername postgres -DbPassword "twoje_haslo" -SkipInstall -SkipBuild -SkipSeed
```

## Pierwsze uruchomienie

1. Sklonuj repozytorium:

```bash
git clone https://github.com/imielowskia/restauracja-2026.git
cd restauracja-2026
```

2. Zainstaluj zależności PHP:

```bash
composer install
```

3. Zainstaluj zależności frontendu:

```bash
npm install
```

4. Utwórz plik `.env`:

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Linux/macOS/Git Bash:

```bash
cp .env.example .env
```

5. Wygeneruj klucz aplikacji:

```bash
php artisan key:generate
```

6. Utwórz bazę danych PostgreSQL, np.:

```sql
CREATE DATABASE restauracja;
```

7. Ustaw dane bazy w `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=restauracja
DB_USERNAME=postgres
DB_PASSWORD=twoje_haslo

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

8. Uruchom migracje i seedery:

```bash
php artisan migrate --seed
```

9. Zbuduj frontend:

```bash
npm run build
```

10. Uruchom aplikację:

```bash
php artisan serve
```

Aplikacja będzie dostępna pod adresem:

```text
http://127.0.0.1:8000
```

## Konta testowe

Po uruchomieniu seederów dostępne są konta:

```text
admin@example.com / password
manager@example.com / password
kelner@example.com / password
kuchnia@example.com / password
bar@example.com / password
```

Hasła są zapisywane w bazie jako hashe.

## Tryb developerski

W jednym terminalu:

```bash
php artisan serve
```

W drugim terminalu:

```bash
npm run dev
```

Alternatywnie można uruchomić build produkcyjny:

```bash
npm run build
```

## Testy

```bash
php artisan test
```

Formatowanie kodu:

```bash
vendor/bin/pint app database routes tests
```

Na Windows:

```powershell
vendor\bin\pint app database routes tests
```

## Najczęstsze problemy

### Błąd: `relacja "sessions" nie istnieje`

Przyczyna: `SESSION_DRIVER=database`, ale nie wykonano migracji.

Rozwiązanie:

```bash
php artisan migrate
php artisan config:clear
```

### Błąd: `relacja "cache" nie istnieje`

Przyczyna: `CACHE_STORE=database`, ale nie wykonano migracji.

Rozwiązanie:

```bash
php artisan migrate
php artisan config:clear
php artisan cache:clear
```

### Błąd: `Vite manifest not found`

Przyczyna: brak zbudowanego frontendu w `public/build`.

Rozwiązanie:

```bash
npm install
npm run build
```

Albo w trybie developerskim:

```bash
npm run dev
```

### Logowanie nie działa

Sprawdź:

```bash
php artisan config:clear
php artisan migrate --seed
```

Upewnij się, że w `.env` jest poprawny `APP_KEY`. Jeśli nie:

```bash
php artisan key:generate
```

## Aktualne funkcjonalności

- logowanie użytkowników,
- role: admin, manager, kelner, kuchnia, bar,
- CRUD menu dla managera/admina,
- CRUD stolików dla managera/admina,
- publiczny widok menu,
- panel kelnera z widokiem stolików,
- rozpoczęcie zamówienia dla wolnego stolika,
- podstawowe testy funkcjonalne.
