
# Swoole App Sample

by [Savio Resende](https://savioresende.com)

This sample is for the Project Jacked PHP:
- YouTube: https://www.youtube.com/user/lotharthesavior

This is an App built on top of [Slim 4](https://www.slimframework.com/).

## Table of Contents

- [File Structure](#file-structure)
- [Installation](#installation)
- [Other Commands](#other-commands)
  * [HTTP Server](#http-server)
  * [WebSocket Server](#websocket-server)
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
│   │   ├── GenerateFactory.php
│   │   ├── GenerateJwtToken.php
│   │   ├── HttpServer.php
│   │   ├── WebSocketServer.php
│   │   ├── Migrate.php
│   │   └── Seed.php
│   │   └── stubs
│   │       └── ModelFactory.stub
│   ├── DB
│   │   ├── Factories
│   │   │   └── UserFactory.php
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
│   │   ├── Middlewares
│   │   │   ├── AuthorizationMiddleware.php
│   │   │   ├── CheckUsersExistenceMiddleware.php
│   │   │   ├── JwtAuthMiddleware.php
│   │   │   └── SessionMiddleware.php
│   ├── Rules
│   │   ├── RecordExist.php
│   │   └── RecordExistValidator.php
│   ├── Services
│   │   ├── Events.php
│   │   ├── Hash.php
│   │   ├── JwtToken.php
│   │   ├── Resource.php
│   │   ├── Session.php
│   │   ├── SessionTable.php
│   │   └── Validator.php
│   ├── api-routes.php
│   └── routes.php
├── tests
│   ├── Feature
│   │   ├── ApiUserTest.php
│   │   ├── LoginTest.php
│   │   └── TokenTest.php
│   ├── Traits
│   │   └── SwooleAppTestTrait.php
│   ├── Unit
│   │   ├── GenerateFactoryCommandTest.php
│   │   └── GenerateTokenCommandTest.php
│   └── TestCase.php
├── views
│   ├── admin.php
│   ├── login.php
│   └── home.php
│   └── partials
│   │   └── chat.php
│   └── templates
│       └── admin.php
├── public
│   └── js
│       └── ws.js
├── .env.sample
├── .env.testing
├── bootstrap-phpunit.php
├── composer.json
├── composer.lock
├── docker-compose.yml
├── my-app
├── phpunit.xml
└── README.md
```

## Installation

To run this app you'll need the following dependencies:

- PHP ^8.1
- PHP openswoole extension ^4.11 (https://openswoole.com)
- PHP Composer (https://getcomposer.org)

**Step 1**

```shell
composer install
```

**Step 2**

This step is to prepare the database. It can be skipped if you already have another DB source available.

```shell
docker-compose up -d
```

**Step 3**

Copy the `.env.sample` to `.env` and update the necessary configurations such as the database info, the log storage location and the session key.

**Step 4**

Run migrations:

```shell
php my-code migrate
```

**Step 5**

Run seeds:

```shell
php my-code seed
```

**Step 6**

To start HTTP Server:

```shell
php my-code http-server
```

## Commands

This application executes commands through `my-app` PHP file. To see the full list of commands, run the following:

```shell
php my-app
```

### HTTP Server

Start the HTTP server.

```shell
php my-app http-sever
```

### WebSocket Server

Start the WebSocket Server.

```shell
php my-app ws-server
```

To start the WebSocket Server with an HTTP requests support on the same server:

```shell
php my-app ws-server --http
```

### Migrate

Create database tables.

```shell
php my-app migrate
```

### Seed

Populate the db.

```shell
php my-app seed
```

### Generate JWT Token

```shell
php my-app generate:jwt-token
```

### Generate a Model Factory

```shell
php my-app generate:factory
```

## Tests

```shell
./vendor/bin/phpunit
```

