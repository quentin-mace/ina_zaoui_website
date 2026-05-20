## Avant optimisation

### Page invités

**Backend time :** 112ms
**Frontend time :** 96ms
**ORM Requests :** 26

### Page d'accueil (référence)

**Backend time :** 19ms
**Frontend time :** 2ms
**ORM Requests :** 1

## Identification du problème

À l'affichage, pour chaque invité, l'ORM fait une requête pour aller rechercher les médias associés (pour les compter).
Le nombre de requêtes augmente donc en fonction du nombre d'entités (problème N+1).

## Action corrective

Au moment de la récupération des entités User en BDD, mise en place d'un left join qui récupère les Médias associés en mème temps. On réduit ainsi le nombre de requêtes nécessaires.

## Après optimisation

### Page invités

**Backend time :** 101ms
**Frontend time :** 2ms
**ORM Requests :** 2

### Page d'accueil (référence)

_Les différences ici ne sont pas significatives_
**Backend time :** 17ms
**Frontend time :** 1ms
**ORM Requests :** 1
