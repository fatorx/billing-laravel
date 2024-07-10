#  Billing

This system aims to control billings and manage payments of these billings.

------

## Functionalities

------

### Sequence Receive File

------
### Technologies
- PHP 8.3 with Laminas
- MySQL 8.0.32
- RabbitMQ 3.10.1

------

## Instructions for run this app:

### First time

Clone project in your projects folder.
```shell script
$ git clone git@github.com:fatorx/app-billing.git && cd app-billing
```
Copy .env.dist to .env and adjust values in the .env file to your preferences.
```shell script
cp .env.dist .env 
```

Configure environment to local application.
```shell script
cp api/config/autoload/local.php.dist api/config/autoload/local.php 
```

Add permissions to folder data (MySQL and RabbitMQ) and api/data (logs, storage files), this is where the persistence files will be kept.
```shell script
chmod 755 data
chmod 755 api/data
```

Mount the environment based in docker-compose.yml.
```shell script
docker-compose up -d --build
```
Access database to create tables (the name app-billing-database is based in the parameter config APP in .env).
```shell script
docker exec -it app-billing-database mysql -u root -p -D billings
```
After access the docker with above command, at the MySQL prompt type:
```shell script
source /tmp/dump.sql
```

------
### Working routine
```shell script
docker-compose up -d
```
------

### Access to environment
###
Test to send a file:
```shell script
curl --location '0.0.0.0:8009/v1/billing/send-file' \
--form 'file=@"/home/yourpath/projects/app-billing/temp/test_length_ok.csv"'
```
###
Test to send a request to webhook (file size limited in 1MB):
```shell script
curl --location '0.0.0.0:8009/v1/billing/webhook' \
     --header 'Content-Type: application/json' \
     --data '{
        "debtId": "123",
        "paidAt": "2022-06-09 10:00:00",
        "paidAmount": 1001.10,
        "paidBy": "John Doe"
     }'
```
------

### Tests Inside Docker
```shell script
docker exec -it app-billing-php-fpm bash
```
And then do this
```shell script
vendor/bin/phpunit --testdox --testsuite "Game Test Suite"
```
or
```shell script
vendor/bin/phpunit --testdox --testsuite "Game Test Suite" --group games
```

------
### Tests Outside Docker
```shell script
docker exec -it app-billing-php-fpm vendor/bin/phpunit --testdox --testsuite "Game Test Suite"
```

------
### Coverage
```shell script
docker exec -it app-billing-php-fpm bash 
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html data/report --group billing
```

------
## Licence

[MIT](https://github.com/fatorx/php-gamer/blob/main/LICENSE.md)


