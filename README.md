# klónozás WSL fájlrendszerbe (pl. ~/dev/chatapi)

mkdir -p ~/dev && cd ~/dev
git clone https://github.com/jzsolti/chatapi.git
cd chatapi

# környezeti fájl

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

# Mailpit Elérés

UI: http://localhost:8025

# test

./vendor/bin/sail artisan test

# Postman fájlok:

/postman mappában
