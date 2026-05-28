# Rapport de Performances
## Analyse et optimisation — Site Web d'Ina Zaoui

## 1. Mesures avant optimisation

### Page Invités

| Métrique | Valeur |
| --- | --- |
| Backend time | 112 ms |
| Frontend time | 96 ms |
| Requêtes ORM | 26 |

### Page d'accueil (référence)

| Métrique | Valeur |
| --- | --- |
| Backend time | 19 ms |
| Frontend time | 2 ms |
| Requêtes ORM | 1 |

## 2. Identification du problème

À l'affichage de la page invités, pour chaque invité listé, l'ORM effectue une requête distincte afin de récupérer les médias associés (dans le but de les compter). Le nombre de requêtes croît donc linéairement avec le nombre d'entités : c'est le problème N+1.

> **Problème N+1 :** Avec 25 invités, l'ORM exécute 1 requête pour lister les invités + 25 requêtes pour récupérer leurs médias, soit 26 requêtes au total.

## 3. Action corrective

Lors de la récupération des entités User en base de données, mise en place d'un LEFT JOIN permettant de charger les Médias associés en une seule et même requête (eager loading). Cette approche réduit drastiquement le nombre de requêtes nécessaires, indépendamment du nombre d'entités retournées.

Mise en cache également de toutes les routes qui appèlent des médias, permettant ainsi d'eviter des requettes inutile à la base de données.

## 4. Mesures après optimisation

### Page Invités

| Métrique | Valeur |
| --- | --- |
| Backend time | 1 ms |
| Frontend time | 2 ms |
| Requêtes ORM | 2 |

### Page d'accueil (référence)

_Les différences observées ne sont pas significatives._

| Métrique | Valeur |
| --- | --- |
| Backend time | 17 ms |
| Frontend time | 1 ms |
| Requêtes ORM | 1 |

## 5. Synthèse des gains (Page Invités)

| Métrique | Avant | Après | Gain |
| --- | --- | --- | --- |
| Backend time | 112 ms | 1 ms | − 99 % |
| Frontend time | 96 ms | 2 ms | − 98 % |
| Requêtes ORM | 26 | 2 | − 92 % |

## 6. Front office : Etat actuel

### Page d'accueil

| Métrique | Valeur |
| --- | --- |
| Backend time | 17 ms |
| Frontend time | 1 ms |
| Requêtes ORM | 1 |

### Page Invités

| Métrique | Valeur |
| --- | --- |
| Backend time | 1 ms |
| Frontend time | 2 ms |
| Requêtes ORM | 2 |

### Page Invité Spécifique

| Métrique | Valeur |
| --- | --- |
| Backend time | 1 ms |
| Frontend time | 3 ms |
| Requêtes ORM | 2 |

### Page Qui-suis-je

| Métrique | Valeur |
| --- | --- |
| Backend time | 1 ms |
| Frontend time | 2 ms |
| Requêtes ORM | 1 |

### Page login

| Métrique | Valeur |
| --- | --- |
| Backend time | 1 ms |
| Frontend time | 1 ms |
| Requêtes ORM | 1 |