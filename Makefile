.PHONY: install backend frontend dev test build-spa

install:
	composer install
	php artisan key:generate
	npm ci
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
