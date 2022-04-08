## How to start project

1. Clone repo
2. docker run --rm \
   -u "$(id -u):$(id -g)" \
   -v $(pwd):/opt \
   -w /opt \
   laravelsail/php81-composer:latest \
   composer install --ignore-platform-reqs
3. (./vendor/bin/sail if missing alias) sail up -d
4. sail composer install
5. sail artisan migrate
6. sail artisan db:seed
