
# Swoole App Sample

by [Savio Resende](https://savioresende.com)

This sample is for the Project Jacked PHP:
- YouTube: https://www.youtube.com/user/lotharthesavior

This is an App built on top of [Slim 4](https://www.slimframework.com/).

## Table of Contents

- [File Structure](#file-structure)
- [Installation](#installation)
- [HTTP Server](#http-server)
- [Other Commands](#other-commands)
  * [Migrate](#migrate)
  * [Seed](#seed)
  * [Generate JWT Token](#generate-jwt-token)
- [Tests](#tests)

## File Structure

```
├── docker
│   └── mysql_data
├── logs
│   ├── .gitignore
│   └── app.log
├── src
│   ├── Bootstrap
│   │   ├── App.php
│   │   └── Dependencies.php
│   ├── Commands
│   │   ├── GenerateJwtToken.php
│   │   ├── HttpServer.php
│   │   ├── Migrate.php
│   │   └── Seed.php
│   ├── DB
│   │   └── Models
│   │       ├── Token.php
│   │       └── User.php
│   └── Events
│   │   ├── EventInterface.php
│   │   ├── UserLogin.php
│   │   ├── UserLoginFail.php
│   │   └── UserLogout.php
│   ├── Http
│   │   ├── Controllers
│   │   │   ├── Api
│   │   │   │   └── UserController.php
│   │   │   ├── AdminController.php
│   │   │   ├── HomeController.php
│   │   │   └── LoginController.php
│   │   └── Middlewares
│   │   │   ├── AuthorizationMiddleware.php
│   │   │   ├── CheckUsersExistenceMiddleware.php
│   │   │   ├── JwtAuthMiddleware.php
│   │   │   └── SessionMiddleware.php
│   ├── Services
│   │   ├── Events.php
│   │   ├── JwtToken.php
│   │   ├── Session.php
│   │   └── SessionTable.php
│   ├── api-routes.php
│   └── routes.php
├── tests
│   ├── ApiUserTest.php
│   └── TestCase.php
├── views
│   ├── admin.php
│   ├── login.php
│   └── home.php
└── README.md
```

## Installation

To run this app you'll need the following dependencies:

- PHP ^8.1
- PHP openswoole extension ^4.11 (https://openswoole.com)
- PHP Composer (https://getcomposer.org)

```shell
composer install
```

## HTTP Server

To start the HTTP server, run this command:

```shell
php my-app http-sever
```

## Other Commands

### Migrate

```shell
php my-app migrate
```

### Seed

```shell
php my-app seed
```

### Generate JWT Token

```shell
php my-app jwt-token:generate
```

## Tests

```shell
./vendor/bin/phpunit
```
