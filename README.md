# Formation Développeur PHP / Symfony

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/e298d43c8c75443fa18801643335069a)](https://app.codacy.com/gh/RLouet/Formation-OC-P5?utm_source=github.com&utm_medium=referral&utm_content=RLouet/Formation-OC-P5&utm_campaign=Badge_Grade)

## Projet 5 : Créez votre premier blog en PHP
### Introduction
Projet 5 de la formation **OpenClassrooms** *Développeur d'application PHP / Symfony* : 
[Créez votre premier blog en PHP](https://openclassrooms.com/fr/projects/7/assignment)

Vous pouvez voir la démo du projet [ici](https://blog.romainlouet.fr/)

### Installation
#### Prérequis
* Version minimum de PHP : 7.3
* Git
* Composer
#### Clonage du projet 
Tout d'abord, clonez le projet depuis github :

`git clone https://github.com/RLouet/Formation-OC-P5.git .`

#### Installation des dépendances
Installez ensuite les dépendances composer

`composer update`

#### Mise en place de la base de données
Importez le fichier *data/database.sql* dans votre système de gestion de base de données.
    
#### Configurez le blog
Vous devez ensuite configurer le blog, en éditant le fichier *config/config.xml*.
##### Configurer base
Par défaut, le blog a l'id 1. Cependant, vous pouvez créer une autre configuration dans la base de données (en ajoutant une ligne dans la table "blog"). Cela peut vous permettre de changer rapidement de configuration. 

* Les utilisateurs, posts et commentaires sont communs à tous les blogs configurés
* Seront modifiés en changeant de blog :
  * Le logo
  * Les skills
  * La phrase d'accroche
  * La destination du formulaire de contact
  * Le CV
  * les coordonnées
  * les réseaux sociaux et leurs logos

Pour l'utiliser, vous devrez configurer son id ici :
```xml
<define var="blog_id" value="1" />
```
##### Configurer erreurs
Pour afficher les erreurs, passer la ligne suivante à 'true' :
```xml
<define var="show_errors" value="true" />
```
##### Configurer https
Si vous utilisez le protocole https, passez la ligne suivante à 'true' :
```xml
<define var="https" value="true" />
```
Cela permettra le chargement correct des ressources, et de sécuriser les cookies.
##### Configurer clé de chiffrement
Afin de sécuriser l'encodage des tokens, je vous conseille de générer une clé 256-bit qui vous est propre, et de la configurer sur la ligne suivante :
```xml
<define var="secret_key" value="votre clé" />
```
Vous pouvez en générer une [ici](https://randomkeygen.com/), et en choisir une dans *CodeIgniter Encryption Keys (256-bit key)*.
##### Configurer base de donnée
Configurez ensuite votre base de données :
###### Host :
```xml
<define var="db_host" value="localhost" />
```
###### Nom de la base :
```xml
<define var="db_name" value="nom_de_votre_base" />
```
###### Username :
```xml
<define var="db_user" value="username" />
```
###### Mot de passe :
```xml
<define var="db_password" value="password" />
```
##### Configurer mail
Ensuite, vous devez configurer le serveur mail pour l'envoie des mails de contact, et de fonctionnement (smtp) :
###### Host :
```xml
<define var="mailer_host" value="smtp.fournisseur.com" />
```
###### Port :
```xml
<define var="mailer_port" value="465" />
```
###### Encodage :
```xml
<define var="mailer_encryption" value="ssl" />
```
* "" => pas d'encodage
* "ssl" => ssl
* "tls" => tls
###### Login :
```xml
<define var="mailer_username" value="votreLogin" />
```
###### Mot de passe :
```xml
<define var="mailer_password" value="votreMotDePasse" />
```
###### Adresse From :
Afin d'améliorer la délivrabilité des emails, il est conseillé de mettre ici l'adresse email configurée en smtp:
```xml
<define var="mailer_from_mail" value="adresse@domaine.com" />
```
###### Nom :
C'est le nom qui sera affiché à la réception des emails.
```xml
<define var="mailer_from_name" value="Romain LOUET" />
```
##### Configuer pagination
Enfin, vous pouvez configurer le nombre de posts et de commentaires affichés sur les pages :
```xml
<define var="pagination" value="12" />
```
#### .htaccess
Si vous utilisez le protocole https, vous pouvez décommenter les dernières lignes du fichier *public/.htaccess* :
```
# BEGIN Redirect HTTP to HTTPS
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{SERVER_PORT} 80
RewriteCond %{HTTP_HOST} ^sousdomaine.domaine\.fr$ [NC]
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R,L]
</IfModule>
# END Redirect HTTP to HTTPS
```
**Pensez à remplacer ```sousdomaine.domaine\.fr``` par votre domaine.**

#### User
Afin de pouvoir administrer le blog, il vous faudra effectuer quelques manipulations :
##### S'enregistrer
* S'enregistrer en tant que nouvel utlisateur
* Valider votre inscription depuis le mail qui vous a été envoyé.

##### Modifier la base de donnée pour vous promouvoir administrateur : 
* Accédez à votre base de donnée
* ouvrez la table "user".
* Passer "role" à "ROLE_ADMIN" sur la ligne correspondante à l'utilisateur que vous venez de créer.
* Vous pouvez vous connecter en administrateur.

#### Favicon
Afin d'améliorer son accessibilité, le blog est optimisé pour le jeux de favicons généré par [realfavicongenerator](https://realfavicongenerator.net/).

* Générez vos fichiers sur le lien ci dessus
* Décompressez le fichier généré
* Mettre son contenu dans le répertoire *public/favicons* 
#### Heros
Vous pouvez changer les image utilisées en hero sur la page d'accueil et sur la page du book :

Remplacez simplement les images qui se trouvent dans public/uploads/heros :
  * Home :
    * home-hero.jpg
    * 1920*1024 px
  * Book :
    * book-hero.jpg
    * 1920*1024 px