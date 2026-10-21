# Lokalni Docker

Isti raspored servisa kao u Andromedi: PHP-FPM, Nginx, MySQL, Mailpit i Node.
Aplikacija ostaje u korenu repozitorijuma, koji se montira na `/var/www/html`.
Okruženje koristi PHP 8.5, Node 24 LTS i MySQL 8.4 LTS.
Aplikacija koristi Laravel 13, React 19, Inertia 3, Vite 8 i Tailwind CSS 4.

## Prvo pokretanje

Ako `.env` ne postoji, kopiraj `.env.example` u `.env` u korenu projekta.
Za HTTPS je potreban `mkcert`. Iz ovog foldera, pre prvog pokretanja:

```sh
mkdir -p certs
mkcert -install
mkcert -cert-file certs/webshop.test.pem -key-file certs/webshop.test-key.pem webshop.test localhost 127.0.0.1 ::1
```

Dodaj `127.0.0.1 webshop.test` u `/etc/hosts`. Sertifikati u `certs/` se ne
čuvaju u Git-u. Nginx koristi lokalni port 443 za HTTPS.

Zatim iz ovog foldera:

```sh
docker compose up --build -d
docker compose exec php composer install
docker compose exec php php artisan config:clear
docker compose exec php php artisan view:clear
docker compose exec node npm ci
```

Ako je `APP_KEY` u `.env` prazan, generiši ga:

```sh
docker compose exec php php artisan key:generate
```

Pripremi novu lokalnu bazu i javni pristup slikama:

```sh
docker compose exec php php artisan migrate
docker compose exec php php artisan storage:link
```

Pokreni frontend (ostavi terminal otvoren):

```sh
docker compose exec node npm run dev -- --host 0.0.0.0 --port 5174 --strictPort
```

- Aplikacija: https://webshop.test (preusmerava na `/login`)
- Prijava u CMS: https://webshop.test/login
- Mailpit: http://localhost:8026
- MySQL sa računara: `127.0.0.1:3322`, baza `webshop`, korisnik `user`, lozinka `password`.
- SMTP sa računara: `127.0.0.1:1026`.

Compose postavlja lokalne DB i mail vrednosti unutar kontejnera. APP_URL je
`https://webshop.test` podrazumevano; za čitanje vrednosti iz projektnog `.env`
koristi `docker compose --env-file ../.env up -d` iz ovog foldera;
postojeći `.env` ne mora da se menja. Baza je zasebna i podaci se čuvaju u
Docker volumenu. Postojeća baza se ne uvozi automatski. Node koristi zaseban
volumen za Linux zavisnosti, pa je `npm ci` potreban i ako postoje lokalni `node_modules`.

## Sledeća pokretanja

```sh
docker compose start
docker compose exec node npm run dev -- --host 0.0.0.0 --port 5174 --strictPort
```

Zaustavljanje:

```sh
docker compose stop
```

Za statički frontend build umesto Vite dev servera:

```sh
docker compose exec node npm run build
```

Sve komande se izvršavaju iz `docker` foldera. Iz korena projekta koristi
`docker compose -f docker/docker-compose.yml` umesto `docker compose`.

## Provera nadogradnje

```sh
docker compose run --rm --no-deps php php artisan test
docker compose run --rm --no-deps php composer audit
docker compose run --rm --no-deps node npm run build
docker compose run --rm --no-deps node npm audit
```

Testovi koriste SQLite bazu u memoriji, nezavisno od MySQL baze aplikacije.
Zaključane verzije su u `composer.lock` i `package-lock.json`; pri pokretanju
koristi `composer install` i `npm ci`.

Pri nadogradnji postojećeg Docker okruženja ponovo izvrši `up --build -d`,
instalaciju zavisnosti i čišćenje konfiguracije i Blade prikaza iz gornjih koraka.
Za postojeću MySQL 8.0 bazu napravi backup pre prvog pokretanja MySQL 8.4:
server nadograđuje format podataka, pa povratak na 8.0 zahteva vraćanje backup-a.
Tailwind 4 zahteva moderne pregledače (Safari 16.4+, Chrome 111+, Firefox 128+).

## Lokalni administrator

Iz ovog foldera pokreni:

```sh
docker compose exec php php artisan db:seed --class=AdminSeeder
```

Prijava: `pera@admin.com`, lozinka: `test1234`. Seeder radi samo u
`local` i `testing` okruženju.
