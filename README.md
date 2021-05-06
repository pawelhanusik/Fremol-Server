<p align="center"><a href="http://pawel.hanusik.pl/fremol" target="_blank"><img src="public/logo.svg" width="170"></a></p>

# Fremol (backend)

Messaging app with backend you host yourself.


# Installation

Clone repository:

```
git clone https://github.com/pawelhanusik/Fremol-Server.git
cd Fremol-Server
```

Install php dependencies:

```
composer install
```

Create default config file:

```
cp .env.example .env
```

Generate app key:

```
php artisan key:generate
```

Configure database:

- setup for MySQL

    - install and enable mysql php extension

    - in .env file: 
        ```
        DB_CONNECTION=mysql
        DB_HOST=<database_host>
        DB_PORT=<database_port>
        DB_DATABASE=<database_name>
        DB_USERNAME=<database_username>
        DB_PASSWORD=<database_password>
        ```

- setup for SQLite

    - install and enable sqlite php extension.

    - in .env:

        ```
        DB_CONNECTION=sqlite
        ```

        and REMOVE all other vars prefixed with DB_

    - create db file:
        
        ```
        touch database/database.sqlite
        ```

Configure WebSockets:

In .env:

```
PUSHER_APP_HOST=<hostname>
PUSHER_APP_PORT=<websockets_port>
PUSHER_APP_SECRET=<some_secret_key>
```

*PUSHER_APP_SECRET should be any random alphanumeric string.  
The hostname have to be reachable by the users.*

*Note: If you want to change PUSHER_APP_CLUSTER or PUSHER_APP_KEY, make sure to update these settings in frontent app as well.*

Generate database:

```
php artisan migrate
```

Make storage publicly available:

```
php artisan storage:link
```

# SSL

Fremol is designed to support only encrypted traffic, thats why you have to get certificates. If you don't have one yet, I recommend using [Let's Encrypt](https://letsencrypt.org/)

In .env set following variables to match your needs:

```
LARAVEL_WEBSOCKETS_SSL_LOCAL_CERT=null
LARAVEL_WEBSOCKETS_SSL_CA=null
LARAVEL_WEBSOCKETS_SSL_LOCAL_PK=null
LARAVEL_WEBSOCKETS_SSL_PASSPHRASE=null
```

Example for Let's Encrypt certificates:

```
LARAVEL_WEBSOCKETS_SSL_LOCAL_CERT=/etc/letsencrypt/live/<domain>/fullchain.pem
LARAVEL_WEBSOCKETS_SSL_CA=null
LARAVEL_WEBSOCKETS_SSL_LOCAL_PK=/etc/letsencrypt/live/<domain>/privkey.pem
LARAVEL_WEBSOCKETS_SSL_PASSPHRASE=null
```

# Running

Just host `public` directory as a root directory in your domain.

And then for the WebSockets part, you have to use the artisan command:

```
php artisan websockets:serve --port <websockets_port>
```

# Testing

```
php artisan test
```

# Clearing database

```
php artisan migrate:fresh
```

# Production

In .env:

```
APP_ENV=production
APP_DEBUG=false
```
