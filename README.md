# Ina Zaoui

Application Symfony 5.4 (PHP ≥ 8.0).

## Prérequis

- PHP 8.0 ou plus, avec les extensions requises par Composer (`ctype`, `iconv`, etc.)
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

4. **Créer les tables** (base vide) :

   ```bash
   php bin/console doctrine:schema:update --force
   ```

   **Optionnel** : un dump SQL anonymisé et les fichiers `public/uploads` peuvent être récupérés depuis `backup.zip` (fichier volumineux, > 1 Go).

5. **Lancer l’application en dev** (au choix) :

   ```bash
   symfony server:start
   ```

   ou, sans Symfony CLI :

   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

   Ouvrir l’URL indiquée (souvent `http://127.0.0.1:8000`).

## Compte de démo

- Identifiant : `ina`  
- Mot de passe : `password`
