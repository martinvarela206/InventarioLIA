Puedes inicializar el proyecto automáticamente ejecutando:
```bash
./init.sh
```

O paso a paso de forma manual:
```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```