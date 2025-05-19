# Symphony

Projet réalisé par **Florent**, **Kilian** et **Corentin**

Lien vers le [dépôt GitHub](https://github.com/damodred53/symfony)

Pour lancer le back-end de Symphony, il suffit d’exécuter la commande suivante :
```
docker-compose up -d --build
```

Nous avons rencontré de nombreuses difficultés pour ajouter un second projet Symphony via Docker. N’ayant pas réussi,
nous avons cherché une autre solution. La solution retenue a été d’ajouter un serveur web via PhpStorm. Pour cela, il
faut ajouter une configuration dans l’IDE. Voici les étapes à suivre :

1. Cliquer sur le bouton en haut à droite pour modifier la configuration.
   (Voir le rectangle rouge sur l’image ci-dessous)
   ![img.png](pictures/img.png)


2. Une fois cliqué dessus, il faut sélectionner “Edit configuration”
   (voir rectangle rouge sur l'image ci-dessous)
   ![img.png](pictures/img2.png)


3. Dans le menu qui s’ouvre, cliquer sur “Add” puis choisir “PHP Built-in Web Server”
   Bien faire attention à "Document root" qui doit pointer vers le dossier public du projet (Voir la capture d'écran 
   ci-dessous).
   (voir rectangle rouge sur l'image ci-dessous)
   ![img.png](pictures/img3.png)


4. Une fois le menu ouvert. Il faut choisir un port qui n’est pas utilisé sur votre machine.
   Dans “Host”, mettre “localhost”.
   Ensuite, cliquer sur Apply puis sur OK en bas à droite.
   (Voir le rectangle rouge sur l’image ci-dessous)
   ![img4.png](pictures/img4.png)


5. Fermer le menu, puis cliquer sur le bouton vert en haut à droite pour lancer le serveur.
   (Voir le rectangle rouge sur l’image ci-dessous)
   ![img5.png](pictures/img5.png)

6. Ajoutez le fichier `.env` à la racine du projet :
```
# In all environments, the following files are loaded if they exist,
# the latter taking precedence over the former:
#
#  * .env                contains default values for the environment variables needed by the app
#  * .env.local          uncommitted file with local overrides
#  * .env.$APP_ENV       committed environment-specific defaults
#  * .env.$APP_ENV.local uncommitted environment-specific overrides
#
# Real environment variables win over .env files.
#
# DO NOT DEFINE PRODUCTION SECRETS IN THIS FILE NOR IN ANY OTHER COMMITTED FILES.
# https://symfony.com/doc/current/configuration/secrets.html
#
# Run "composer dump-env prod" to compile .env files for production use (requires symfony/flex >=1.2).
# https://symfony.com/doc/current/best_practices.html#use-environment-variables-for-infrastructure-configuration

###> symfony/framework-bundle ###
APP_ENV=dev
APP_SECRET=
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
# Format described at https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
# IMPORTANT: You MUST configure your server version, either here or in config/packages/doctrine.yaml
#
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_%kernel.environment%.db"
# DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8.0.32&charset=utf8mb4"
# DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
###< doctrine/doctrine-bundle ###

###> lexik/jwt-authentication-bundle ###
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=8d757b3b845bf34a457866aa65d1028acbc1070082bf0913f5559f18f8c6a104
###< lexik/jwt-authentication-bundle ###
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=ton_mot_de_passe_de_clé
BACKEND_AUTH_TOKEN=WebSiteToken
API_URL=http://localhost
```

7. Exécutez la commande suivante pour installer les dépendances PHP : `composer install` ou `composer update`.

8. Créer le token API :
```
php bin/console app:create-api-token
```
9. Créer un utilisateur :
```
php bin/console  app:create-user
```

10. Une fois le serveur lancé, ouvrez l’URL suivante dans votre navigateur :
   http://localhost:PortChoisi
    Pour accéder au front, ajoutez /home à l’URL.
    (Exemple sur l’image ci-dessous)
![img6.png](pictures/img6.png)

Un user par défault est déjà créé avec les identifiants suivants :
- Nom utilisateur : `test`
- Mot de passe : `password123`

## Etat du projet

- L’authentification est fonctionnelle.
- On peut créer un utilisateur, ajouter des posts (équivalents à des tweets), et commenter.
- La création d’un tweet fonctionne uniquement pour le premier utilisateur créé (donc test).
- Les likes sont fonctionnels côté back, mais pas encore intégrés côté front.

## Fonctionnalités
- Sécurisation des routes (JWT + Token API)
- API REST
- Gestion des logs via Monolog
- Interface front simple (via Twig)
