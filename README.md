# To Install the project run the below command

```bash
composer install
```

Rename the

`.env.example to .env`

Create a database and setup the DB_USERNAME & DB_DATABASE in the .env file

## Run migration and generate keys

```bash
php artisan migrate
php artisan key:generate
```

Run the below command to run the application

```bash
php aritsan serve
```