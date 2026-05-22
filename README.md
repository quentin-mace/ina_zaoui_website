# Ina Zaoui

[![PHP](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-7.4-000000?logo=symfony&logoColor=white)](https://symfony.com/)
[![Docker Compose](https://img.shields.io/badge/Docker%20Compose-v2-2496ED?logo=docker&logoColor=white)](https://docs.docker.com/compose/)
[![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-tests-366488?logo=php&logoColor=white)](https://phpunit.de/)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%206-8892BF?logo=php&logoColor=white)](https://phpstan.org/)
[![PHP CS Fixer](https://img.shields.io/badge/PHP--CS--Fixer-PSR--12-8892BF?logo=php&logoColor=white)](https://cs.symfony.com/)

Site vitrine et portfolio (photographe), avec un espace d'administration pour gérer invités et médias. Application **Symfony 7.4** (PHP ≥ 8.4).

## Sommaire

- [Prérequis](#prérequis)
- [Installation](#installation)
- [Usage](#usage)
  - [Démarrer / arrêter l'environnement (Docker)](#démarrer--arrêter-lenvironnement-docker)
  - [Lancer l'application en développement](#lancer-lapplication-en-développement)
  - [Base de données (Doctrine)](#base-de-données-doctrine)
  - [Données de dev (fixtures + uploads)](#données-de-dev-fixtures--uploads)
- [Compte de démo (admin)](#compte-de-démo-admin)
- [Architecture et fonctionnement](#architecture-et-fonctionnement)
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

2. **Configurer l'environnement local** : le fichier `.env` versionné contient un `DATABASE_URL` générique ; pour travailler avec le conteneur MySQL, **créer** `.env.local` à partir de l'exemple (identifiants alignés sur Docker : utilisateur / mot de passe `app`, port hôte **3307**) :

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

### Démarrer / arrêter l'environnement (Docker)

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

### Lancer l'application en développement

Au choix :

```bash
symfony server:start
```

ou, sans Symfony CLI :

```bash
php -S 127.0.0.1:8000 -t public
```

Ouvrir l'URL indiquée (souvent `http://127.0.0.1:8000`).

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

## Architecture et fonctionnement

L'application expose deux faces distinctes, chacune avec ses propres contrôleurs et templates.

### Face publique (`/`)

Accessible à tous. Présente le portfolio et les pages invités.

| Contrôleur | Route | Rôle |
|---|---|---|
| `HomeController` | `/` | Page d'accueil, portfolio, à propos |
| — | `/guests` | Liste des invités |
| — | `/guest/{id}` | Page personnelle d'un invité (médias) |

L'accès à la page d'un invité est contrôlé par `UserAccessChecker` (`src/Security/`) : seul l'invité concerné (ou un admin) peut voir ses médias.

### Espace d'administration (`/admin`)

Réservé aux utilisateurs avec le rôle `ROLE_ADMIN`. Protégé par un formulaire de connexion (`SecurityController`).

| Contrôleur | Périmètre |
|---|---|
| `Admin/AlbumController` | CRUD albums |
| `Admin/GuestController` | CRUD invités |
| `Admin/MediaController` | Upload et gestion des médias |
| `Admin/SecurityController` | Authentification |

### Entités

```
User        — compte utilisateur (admin ou invité)
  └── Album — album photo appartenant à un invité
        └── Media — fichier média appartenant à un album
```

- `User` : roles `ROLE_ADMIN` ou `ROLE_USER`. Les invités sont des `User` sans accès à l'admin.
- `Album` : appartient à un `User`. Contient une collection de `Media`.
- `Media` : référence un fichier stocké dans `public/uploads/`.

### Structure des répertoires clés

```
src/
  Controller/       — contrôleurs (Admin/ + HomeController)
  Entity/           — entités Doctrine (User, Album, Media)
  Form/             — formulaires Symfony
  Repository/       — requêtes Doctrine personnalisées
  Security/         — UserAccessChecker
  Migrations/       — migrations Doctrine versionnées
templates/
  front/            — templates face publique
  admin/            — templates espace admin
tests/
  Unit/             — tests unitaires (entités, UserAccessChecker)
  Functional/       — tests fonctionnels (requêtes HTTP via WebTestCase)
```

Pour contribuer, exécuter les tests ou utiliser les outils d'analyse, voir [CONTRIBUTING.md](CONTRIBUTING.md).

## Crédits

Projet réalisé dans le cadre du cours **« Refactorisez le code d'un site pour l'optimiser »** du parcours **Concepteur Développeur d'Application** (OpenClassrooms).

Le code de base a été fourni par OpenClassrooms via le dépôt : `https://github.com/OpenClassrooms-Student-Center/876-p15-inazaoui`.