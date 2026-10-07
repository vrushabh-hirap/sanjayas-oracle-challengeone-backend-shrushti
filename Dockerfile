FROM php:8.2-cli

# pdo_sqlite is bundled with the official PHP image.

WORKDIR /app

COPY . .

# Render sets $PORT; the PHP built-in server binds to it.
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t public"]
