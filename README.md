# Ina Zaoui

[![PHP](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-7.4-000000?logo=symfony&logoColor=white)](https://symfony.com/)
[![Docker Compose](https://img.shields.io/badge/Docker%20Compose-v2-2496ED?logo=docker&logoColor=white)](https://docs.docker.com/compose/)
[![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-tests-366488?logo=php&logoColor=white)](https://phpunit.de/)

Site vitrine et portfolio (photographe), avec un espace d’administration pour gérer invités et médias. Application **Symfony 7.4** (PHP ≥ 8.4).

## Sommaire

- [Prérequis](#prérequis)
- [Installation](#installation)
- [Usage](#usage)
  - [Démarrer / arrêter l’environnement (Docker)](#démarrer--arrêter-lenvironnement-docker)
  - [Lancer l’application en développement](#lancer-lapplication-en-développement)
  - [Base de données (Doctrine)](#base-de-données-doctrine)
  - [Données de dev (fixtures + uploads)](#données-de-dev-fixtures--uploads)
- [Compte de démo (admin)](#compte-de-démo-admin)
- [Tests](#tests)
- [Crédits](#crédits)

## Prérequis

- PHP 8.4 ou plus, avec les extensions requises par Composer (`ctype`, `iconv`, etc.)
- [Composer](https://getcomposer.org/)
- [Docker](https://docs.docker.com/get-docker/) et Docker Compose (pour MySQL et Adminer via `compose.yml`)

**Optionnel** : [Symfony CLI](https://symfony.com/download) pour lancer le serveur de développement avec `symfony server:start` (sinon le serveur intégré PHP suffit, voir plus bas).

## Installation

Ces étapes installent les dépendances PHP, démarrent la base MySQL (Docker) et initialisent le schéma.

1. **Cloner** le dépôt, puis à la racine du projet :

   ```bash
   composer install
   ```

2. **Configurer l’environnement local** : le fichier `.env` versionné contient un `DATABASE_URL` générique ; pour travailler avec le conteneur MySQL, **créer** `.env.local` à partir de l’exemple (identifiants alignés sur Docker : utilisateur / mot de passe `app`, port hôte **3307**) :

   ```bash
   cp .env.local.example .env.local
   ```

   Si vous modifiez `MYSQL_PASSWORD` dans `compose.yml`, mettez à jour `DATABASE_URL` dans `.env.local` en conséquence.

3. **Démarrer les services Docker** (MySQL + Adminer) :

   ```bash
   docker compose up -d
   ```

   Attendre quelques secondes que MySQL soit prêt avant les commandes Doctrine suivantes.

   **Adminer** (interface web SQL) : [http://127.0.0.1:8079](http://127.0.0.1:8079) — système : `MySQL`, serveur : `mysql`, utilisateur : `app`, mot de passe : `app`, base : `ina_zaoui`.

4. **Créer la base de données** (si besoin) :

   ```bash
   php bin/console doctrine:database:create --if-not-exists
   ```

5. **Appliquer les migrations** (création du schéma) :

   ```bash
   php bin/console doctrine:migrations:migrate -n
   ```

## Usage

### Démarrer / arrêter l’environnement (Docker)

- **Démarrer MySQL + Adminer** :

  ```bash
  docker compose up -d
  ```

- **Voir les logs** :

  ```bash
  docker compose logs -f
  ```

- **Arrêter** :

  ```bash
  docker compose down
  ```

### Lancer l’application en développement

Au choix :

```bash
symfony server:start
```

ou, sans Symfony CLI :

```bash
php -S 127.0.0.1:8000 -t public
```

Ouvrir l’URL indiquée (souvent `http://127.0.0.1:8000`).

### Base de données (Doctrine)

Après démarrage de MySQL (Docker) :

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n
```

### Données de dev (fixtures + uploads)

Requiert `doctrine/doctrine-fixtures-bundle` (installé en dépendance `require-dev`).

```bash
php bin/console doctrine:fixtures:load -n
```

Ensuite, téléchargez [cette archive](https://s3.eu-west-1.amazonaws.com/course.oc-static.com/projects/876_DA_PHP_Sf_V2/P15/backup.zip), extrayez-la et **copiez** le contenu du dossier `uploads` dans `public/uploads`.

**Attention** : ne pas versionner le contenu de `public/uploads` (fichiers médias locaux).

## Compte de démo (admin)

Après `doctrine:fixtures:load`, un compte administrateur est disponible :

- **E-mail** : `ina@zaoui.com`  
- **Mot de passe** : `password`

Les invités de démo utilisent le même mot de passe (`password`) avec des adresses du type `invite+0@example.com`, etc.

## Tests

En `APP_ENV=test`, **`DATABASE_URL` est défini dans `.env.test`** (base `ina_zaoui_test`, utilisateur `root`, mot de passe identique à `MYSQL_ROOT_PASSWORD` dans `compose.yml`, soit `root` avec la configuration par défaut). Ce fichier est chargé après `.env` et `.env.local`, donc le développement local continue d’utiliser `.env.local` sans modification pour lancer les tests.

**Prérequis** : conteneur MySQL démarré (`docker compose up -d`).

1. **Créer la base de test** (si elle n’existe pas) :

   ```bash
   php bin/console doctrine:database:create --env=test --if-not-exists
   ```

2. **Appliquer le schéma** :

   ```bash
   php bin/console doctrine:migrations:migrate --env=test -n
   ```

3. **Charger les fixtures** (les mêmes classes que pour le dev : `UserFixtures`, `AlbumFixtures`, `MediaFixtures`) :

   ```bash
   php bin/console doctrine:fixtures:load --env=test -n
   ```

   Cette commande **vide puis repeuple** la base de test.

4. **Lancer la suite PHPUnit** :

   ```bash
   php bin/phpunit
   ```

Les médias référencés par les fixtures pointent vers des chemins du type `public/uploads/….jpg`. Pour des vérifications manuelles ou des tests qui servent ces fichiers, réutilisez la même étape que pour le dev (archive S3 + copie du dossier `uploads` dans `public/uploads`).

## Crédits

Projet réalisé dans le cadre du cours **« Refactorisez le code d'un site pour l'optimiser »** du parcours **Concepteur Développeur d’Application** (OpenClassrooms).

Le code de base a été fourni par OpenClassrooms via le dépôt : `https://github.com/OpenClassrooms-Student-Center/876-p15-inazaoui`.
