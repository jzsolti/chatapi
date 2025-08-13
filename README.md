# Tesztelési útmutató

# Előfeltételek

Windows + WSL2
Docker Desktop (WSL2 integrációval)

Ez az útmutató windows-on WSL-ben lett tesztelve.
Természetesen linuxon is mennie kell, docker és git kell.

# Klónozás WSL fájlrendszerbe (pl. ~/dev/chatapi)

cd ~/dev
git clone https://github.com/jzsolti/chatapi.git
cd chatapi

# Környezeti fájl

cp .env.example .env

# PHP függőségek telepítése Dockeres Composerrel

docker run --rm \
 -u "$(id -u):$(id -g)" \
 -v "$PWD":/app -w /app \
 composer:2 install --no-interaction --prefer-dist --ignore-platform-reqs

# Sail indítása

./vendor/bin/sail up -d

# App kulcs + migrációk

./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate

# Mailpit Elérés email teszthez

UI: http://localhost:8025

# Feaure test

./vendor/bin/sail artisan test

# Postman collection és environment fájlok:

/postman mappában
