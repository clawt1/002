# Notice de Déploiement Manuel 🚀 (FTP + phpMyAdmin)

Ce guide est destiné à toute personne, technique ou non, souhaitant installer et mettre à jour **Roadmap Manager** sur un hébergement mutualisé standard (ex. o2switch, OVH, Hostinger) ou un VPS simple, sans accès SSH avancé.

---

## 📋 Pré-requis de l'Hébergeur

Avant de commencer, vérifiez que votre hébergement dispose de :
1. **PHP 8.5** ou version supérieure.
2. **MySQL 8** (or MariaDB équivalent).
3. Les extensions PHP standard suivantes activées : `PDO`, `PDO_MySQL`, `GD` (pour le redimensionnement d'avatars), `mbstring` et `json`.
   * *Comment vérifier ?* Créez un fichier temporaire nommé `phpinfo.php` contenant uniquement `<?php phpinfo(); ?>`, uploadez-le et ouvrez-le dans votre navigateur. Pensez à le supprimer immédiatement après vérification.

---

## 🗄️ Étape 1 : Initialisation de la Base de Données (phpMyAdmin)

1. Connectez-vous à l'outil **phpMyAdmin** fourni par votre hébergeur (accessible depuis votre panneau de contrôle cPanel, Plesk, etc.).
2. Créez une nouvelle base de données en lui donnant le nom de votre choix (ex: `votrepseudo_roadmap`). Sélectionnez le codage de caractères `utf8mb4_unicode_ci`.
3. Cliquez sur l'onglet **Importer** en haut de la page.
4. Cliquez sur **Choisir un fichier** et sélectionnez le fichier `database/schema.sql` depuis votre ordinateur.
5. Laissez les options par défaut et cliquez sur le bouton **Exécuter** (ou Go) en bas de page. Les tables de l'application sont maintenant créées !
6. *(Optionnel)* Pour importer des données d'exemple, répétez la procédure d'import avec le fichier `database/seed.sql`.
7. **Notez précieusement les informations de connexion** transmises par votre hébergeur :
   * Hôte MySQL (généralement `localhost` ou une adresse IP).
   * Nom de la base de données.
   * Utilisateur de la base de données.
   * Mot de passe associé.

---

## ⚙️ Étape 2 : Configuration locale de l'Application

Avant de téléverser les fichiers sur votre serveur FTP, configurez vos identifiants :

1. Ouvrez le dossier du projet sur votre ordinateur.
2. Créez un fichier nommé **`.env`** (sans extension) à la racine du projet. Vous pouvez dupliquer et renommer le fichier `app/Config/.env.example`.
3. Ouvrez ce fichier `.env` avec un éditeur de texte simple (comme le Bloc-notes, VS Code, ou TextEdit) et adaptez les valeurs avec vos identifiants réels :
   ```env
   DB_HOST=localhost
   DB_NAME=votre_nom_de_base
   DB_USER=votre_utilisateur_mysql
   DB_PASS=votre_mot_de_passe_mysql
   APP_URL=https://www.votredomaine.com
   APP_ENV=production
   APP_EDITION=enterprise
   ```
4. Enregistrez les modifications.

---

## 📤 Étape 3 : Upload des fichiers via FileZilla (FTP)

1. Téléchargez et lancez le logiciel gratuit **FileZilla**.
2. Renseignez les paramètres de connexion FTP fournis par votre hébergeur dans la barre de connexion rapide (Hôte, Identifiant, Mot de passe, Port 21 ou SFTP 22) et cliquez sur **Connexion rapide**.
3. Dans la partie droite (votre serveur), ouvrez le dossier racine de votre site web, généralement nommé **`public_html`**, **`htdocs`**, ou **`www`**.
4. Dans la partie gauche (votre ordinateur), sélectionnez le contenu du projet :
   * **Glissez-déposez le contenu du dossier `public_html`** (index.php, .htaccess, assets, etc.) directement à l'intérieur du dossier racine de votre hébergeur.
   * **Recommandation de sécurité** : Si votre hébergement le permet (accès FTP à la racine du compte, un niveau au-dessus du dossier public), uploadez le dossier `app/` et le dossier `database/` en dehors du dossier web public.
   * **Fallback Mutualisé** : Si vous ne pouvez uploader qu'à l'intérieur du dossier public, glissez simplement le dossier `app/` et `database/` à côté de `index.php`. Notre configuration de sécurité intégrée bloquera tout accès via les fichiers `.htaccess` qui y interdisent l'accès ("Deny from all").

---

## 🩺 Étape 4 : Vérification du Bon Fonctionnement

Ouvrez votre navigateur et accédez à votre site :
1. **Connexion d'administration** : Rendez-vous sur `https://votredomaine.com/login`. Connectez-vous avec le compte admin par défaut :
   * **Email** : `admin@roadmap.local`
   * **Mot de passe** : `0420`
   * *N'oubliez pas de modifier ce mot de passe ou de créer votre propre compte dès votre première connexion via l'onglet Profil !*
2. **Page de diagnostic** : Rendez-vous sur `https://votredomaine.com/healthcheck.php` après vous être connecté en administrateur pour lancer le bilan santé de l'hébergement (connexion BDD, permissions d'écriture, extensions PHP).
3. **Pièces jointes** : Créez un projet, une étape, et uploadez un fichier dans l'une des tâches pour vérifier que le serveur de fichiers accepte l'écriture.

---

## 🔄 Étape 5 : Mises à jour ultérieures

Lorsque nous publions une mise à jour applicative :
1. **Mise à jour du code** : Connectez-vous à FileZilla, activez l'option de comparaison de dossiers (Affichage > Comparaison de dossiers) pour mettre en évidence les fichiers modifiés récemment, et remplacez uniquement ces fichiers sur votre hébergement.
2. **Mise à jour BDD (Migrations)** : Si une modification de structure de base de données est requise, ouvrez le dossier `database/migrations/` localement. Prenez le script SQL correspondant (ex. `002_ajout_stack_technique_generique.sql`), copiez son contenu, connectez-vous à phpMyAdmin, cliquez sur l'onglet **SQL** de votre base de données, collez le script et cliquez sur **Exécuter**.
