# Test Drupal

## Description de l'existant
Le site est déjà installé (profile standard), la db est à la racine du projet.
Un **type de contenu** `Événement` a été créé et des contenus générés avec Devel. Il y a également une **taxonomie** `Type d'événement` avec des termes.

La version du core est la 10.0.9 et le composer lock a été généré sur PHP 8.1.

Les files sont versionnées sous forme d'une archive compressée. Vous êtes invité à créer un fichier `settings.local.php` pour renseigner vos accès à la DB. Le fichier `settings.php` est lui versionné.

## Consignes

### 1. Faire un bloc custom (plugin annoté)
* s'affichant sur la page de détail d'un événement ;
* et affichant 3 autres événements du même type (taxonomie) que l'événement courant, ordonnés par date de début (asc), et dont la date de fin n'est pas dépassée ;
* S'il y a moins de 3 événements du même type, compléter avec un ou plusieurs événements d'autres types, ordonnés par date de début (asc), et dont la date de fin n'est pas dépassée.

### 2. Faire une tache cron
qui dépublie, **de la manière la plus optimale,** les événements dont la date de fin est dépassée à l'aide d'un **QueueWorker**.


## Rendu attendu
**Vous devez cloner ce repo, MERCI DE NE PAS LE FORKER,** et nous envoyer soit un lien vers votre propre repo, soit un package avec :

* votre configuration exportée ;
* **et/ou** un dump de base de données ;
* **et pourquoi pas** des readme, des commentaires etc. :)

**Le temps que vous avez passé** : par mail ou dans un readme par exemple.

# ==========================

## Correction

### 1. Inialiser le projet

* Exécuter le projet avec : docker-compose up -d

* Importer la base de données existante :

  docker cp dump.sql  my_drupal10_project_mariadb:/dump.sql
  docker exec -it my_drupal10_project_mariadb bash
  mysql -u drupal -pdrupal drupal < /dump.sql

* Copier le fichier base.settings.local.php de la racine sous 'web/sites/default' et le renommer par settings.local.php.

* Ajouter le dossier files sous web/sites/default et assurer l'accéssibilité d'écriture par le serveur web

* Install les dépendances de projet : 

  make composer install 

* Importer les configs du projet (le bloc de trois événements et l'activation du module adimeo_test ): 
 
  make drush cim

* Modifier le mot de passe admin :

  make drush uli

* L'URL de projet : http://drupal.docker.localhost:8000/

* L'URL de la base de données : http://pma.drupal.docker.localhost:8000/

### 2. tester les fonctionnalitées :

* Nous pouvons visualiser le bloc 'Events list" dans la page événement détail en haut et en dessus du titre,  exemple : http://drupal.docker.localhost:8000/node-872-event
* Pour afficher le bloc, les tâches réalisées sont :
  - Création de bloc
  - Récupération des trois événements de même type que l'évenement actuel.
  - Vérifier si on a trois événements sinon on récupère le reste de trois, indépendamment de type.
  - Gestion de cache du bloc.

* La tâche cron pour dépublier les événements expirés exécutent à travers hook_cron sous le module "adimeo_test" puis de faire un appel à notre queueWorker sous Plugin de ce module pour changer le status d'évenement.

### 3. Temps passé : 

* 1,5 heure : initialiser le projet avec docker-compose et configurer le site.
* 3 heures : créer et tester le bloc "Events list".
* 2 heures : créer et tester cron déplubier les évenements expirés.
* 30 min : améliorer le code sources (commentaires, qualité de code ..).
* 30 min : préparer ce readme.


