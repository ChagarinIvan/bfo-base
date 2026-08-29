.PHONY: install backend frontend dev test build-spa

install:
	composer update laravel/sanctum --with-all-dependencies
	php artisan key:generate
	npm install
	php artisan migrate

backend:
	php artisan serve

frontend:
	npm run dev:spa

dev:
	$(MAKE) -j2 backend frontend

test:
	composer test

build-spa:
	npm run build:spa
