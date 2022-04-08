## How to start project

1. Clone repo
2. cp .env.example .env
3. docker run --rm \
   -u "$(id -u):$(id -g)" \
   -v $(pwd):/opt \
   -w /opt \
   laravelsail/php81-composer:latest \
   composer install --ignore-platform-reqs
4. (./vendor/bin/sail if missing alias) sail up -d
5. sail artisan migrate
6. sail artisan db:seed
