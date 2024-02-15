# Configuração de variáveis
DOCKER_PHP_DIR=.docker/php
PROJECT_DIR=../../
COMPOSE=docker-compose

# gerando build do php
build-image:
	@cd $(DOCKER_PHP_DIR) && docker build -t php-laravel .

# voltando para a raiz do projeto
project:
	@cd $(PROJECT_DIR)

# subindo o compose
up-compose:
	@$(COMPOSE) up -d --build

# instalando composer
composer-install:
	@composer install

# gerando chave
generate:
	@php artisan key:generate

# realizando a migration
migrate:
	@php artisan migrate:fresh --seed

# servindo a api
serve:
	@php artisan serve

# Alvo principal que executa todos os alvos acima
all: build-image project up-compose composer-install generate migrate serve
