# Contributing

## ⚙️ Setup

Clone the repository and install the dependencies:

```bash
git clone git@github.com:owowagency/laravel-notification-bundler.git
cd laravel-notification-bundler
docker compose up -d
docker compose exec php composer install
```

## 📝 Code Style

This project uses [Pint](https://laravel.com/docs/pint/).
You can run it with:

```bash
# Check the code style
docker compose exec php composer lint
# Fix the code style
docker compose exec php composer lint:fix
```

## 🧪 Testing

All code of this package is tested using [Pest](https://pestphp.com/).
You can run the tests with:

```bash
docker compose exec php composer test
```

## 🦑 Github Actions

You can run the Github actions locally with [act](https://github.com/nektos/act). You have to use a [custom image](https://github.com/shivammathur/setup-php#local-testing-setup) for the ubuntu-latest platform to get PHP up and running properly. To run the tests locally, run:

```bash
act -P ubuntu-latest=shivammathur/node:latest
```

To run a specific workflow, for example `run-tests.yml` run:

```bash
act -P ubuntu-latest=shivammathur/node:latest -j run-tests
```
