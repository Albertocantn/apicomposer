# Instalación de Infraestructura

## Docker

Copiar `dist.env` a `.env` y modificar los valores necesarios.
```
cp dist.env .env
```
Levantar los contenedores
```
docker-compose up -d
```

Agregar entrada al /etc/hosts
```
127.0.0.1 gameoftheday.local
```


Configurar base de datos
```
docker exec -it gameoftheday-db mysql -e "CREATE DATABASE gameoftheday"
docker exec -it gameoftheday-db mysql -e "GRANT ALL ON gameoftheday.* TO 'gameoftheday'@'%' IDENTIFIED BY 'gameoftheday'"
```

Instalar vendor y configuración
```
docker exec -it gameoftheday-php bash
composer install
```

Probar: http://gameoftheday.local
