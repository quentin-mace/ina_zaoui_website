# Ina Zaoui

Application Symfony 7.4 (PHP ≥ 8.4).

## Prérequis

- PHP 8.4 ou plus, avec les extensions requises par Composer (`ctype`, `iconv`, etc.)
- [Composer](https://getcomposer.org/)
- [Docker](https://docs.docker.com/get-docker/) (pour lancer MySQL via le fichier `compose.yml`)

## Installation sur une nouvelle machine

1. **Cloner** le dépôt, puis à la racine du projet :

   ```bash
   composer install
   ```

2. **Configurer l’environnement local** : copier l’exemple et garder les identifiants alignés sur Docker (utilisateur / mot de passe `app`, port **3307**).

   ```bash
   cp .env.local.example .env.local
   ```

   Si vous changez `MYSQL_PASSWORD` dans `compose.yml`, mettez à jour `DATABASE_URL` dans `.env.local` en conséquence.

3. **Démarrer MySQL** :

   ```bash
   docker compose up -d
   ```

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

   Ensuite, télécharger [ce dossier](https://s3.eu-west-1.amazonaws.com/course.oc-static.com/projects/876_DA_PHP_Sf_V2/P15/backup.zip) et 
   copiez le contenu de `uploads` dans `public/uploads`. 

   ⚠️ **Attention** : Ces fichiers ne doivent pas être commités.

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
