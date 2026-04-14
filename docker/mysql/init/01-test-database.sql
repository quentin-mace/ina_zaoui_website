-- Exécuté au premier démarrage du conteneur MySQL (volume vide uniquement).
-- Base utilisée par Symfony en APP_ENV=test (suffixe _test sur le nom de la base applicative).
CREATE DATABASE IF NOT EXISTS `ina_zaoui_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON `ina_zaoui_test`.* TO 'app'@'%';
FLUSH PRIVILEGES;
