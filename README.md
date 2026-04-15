# Ina Zaoui

Site vitrine et portfolio (photographe), avec un espace d’administration pour gérer invités et médias. Application **Symfony 7.4** (PHP ≥ 8.4).

## Prérequis

- PHP 8.4 ou plus, avec les extensions requises par Composer (`ctype`, `iconv`, etc.)
- [Composer](https://getcomposer.org/)
- [Docker](https://docs.docker.com/get-docker/) et Docker Compose (pour MySQL et Adminer via `compose.yml`)

**Optionnel** : [Symfony CLI](https://symfony.com/download) pour lancer le serveur de développement avec `symfony server:start` (sinon le serveur intégré PHP suffit, voir plus bas).

## Installation sur une nouvelle machine

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

6. **Charger des données de dev (fixtures + uploads)** :

   Requiert `doctrine/doctrine-fixtures-bundle` (installé en dépendance `require-dev`).

   ```bash
   php bin/console doctrine:fixtures:load -n
   ```

   Ensuite, téléchargez [cette archive](https://s3.eu-west-1.amazonaws.com/course.oc-static.com/projects/876_DA_PHP_Sf_V2/P15/backup.zip), extrayez-la et **copiez** le contenu du dossier `uploads` dans `public/uploads`.

   **Attention** : ne pas versionner le contenu de `public/uploads` (fichiers médias locaux).

7. **Lancer l’application en dev** (au choix) :

   ```bash
   symfony server:start
   ```

   ou, sans Symfony CLI :

   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

   Ouvrir l’URL indiquée (souvent `http://127.0.0.1:8000`).

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
