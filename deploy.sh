#!/bin/bash

# Define o script para interromper imediatamente se qualquer comando retornar um status de erro.
set -e

# Exibe uma mensagem informando que o processo de deployment foi iniciado.
echo "Deployment started ..."

# Coloca a aplicação em modo de manutenção.
(php artisan down) || true

# Permite execução do composer como root
export COMPOSER_ALLOW_SUPERUSER=1

# Instala as dependências do Composer sem os pacotes de desenvolvimento,
# preferindo pacotes distribuídos e otimizando o autoloader.
composer update --no-dev --prefer-dist --optimize-autoloader

# Executa as migrações do banco de dados forçando a aplicação das alterações e rodando as seeds.
php artisan migrate --force --seed

# Exclui os arquivos de armazenamento padrão
php artisan storage:unlink

# Cria os arquivos de armazenamento padrão
php artisan storage:link

# Define permissões para diretórios (755) e arquivos (644) na aplicação.
find * -type d -exec chmod 755 {} \; && find * -type f -exec chmod 644 {} \;

# Definir permissões de storage
# chown -R $USER:www-data storage/ bootstrap/cache/
chmod -R ug+w storage/ bootstrap/cache/

# Tira a aplicação do modo de manutenção.
php artisan up

# Limpa o cache de otimização do Artisan.
php artisan optimize:clear

# Exibe uma mensagem informando que o processo de deployment foi concluído.
echo "Deployment finished!"
