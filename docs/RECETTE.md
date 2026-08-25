# Plan de Recette Manuel & Tests 🧪

Ce guide répertorie la liste de contrôle exhaustive (checklist) pour valider manuellement le déploiement ou les modifications de l'application **Roadmap Manager**, ainsi que l'utilisation du script de smoke-test automatisé.

---

## 🚦 1. Diagnostics d'Intégrité Automatisés

Avant de commencer la recette manuelle, exécutez le script d'intégrité système.

### Exécution via CLI (Ligne de commande) :
Si vous avez un accès SSH ou sur votre machine locale, exécutez la commande suivante :
```bash
php app/Console/smoke_test.php
```
Le script doit afficher un statut `[OK]` pour la version PHP, les extensions chargées (GD, PDO), la connexion à la base de données, la présence de l'ensemble des tables et les permissions d'écriture.

### Exécution via Navigateur (Hébergement FTP) :
Rendez-vous sur l'adresse :
```
https://votre-domaine.com/healthcheck.php
```
Connectez-vous d'abord en tant qu'administrateur (`admin@roadmap.local`). Le tableau de diagnostic doit s'afficher entièrement en vert.

---

## 🔒 2. Protocole de Recette Applicative (Checklist)

### 🔹 Module 1 : Authentification & Sécurité
- [ ] **Inscription** : Tenter d'ouvrir `/register`. Saisir un compte valide. Vérifier que la soumission redirige vers `/login` avec un message flash de succès. Tenter de soumettre le même email et vérifier le message d'erreur d'unicité.
- [ ] **Connexion** : Se connecter à `/login` avec les identifiants d'administration par défaut (`admin@roadmap.local` / `0420`). Vérifier que la connexion réussit et redirige vers le tableau de bord.
- [ ] **Mot de passe oublié** : Tenter de faire "mot de passe perdu" sur `/forgot-password`. Renseigner l'email. Vérifier qu'un fichier de log d'envoi d'email est créé sous `app/logs/emails.log` contenant le lien d'activation unique. Cliquer sur ce lien et redéfinir le mot de passe.
- [ ] **Déconnexion** : Cliquer sur le bouton de déconnexion. Tenter d'accéder au dashboard (`/`) et vérifier qu'on est automatiquement redirigé vers le formulaire de connexion.
- [ ] **Limitation des tentatives (Rate Limiting)** : Tenter de se connecter 6 fois d'affilée avec un mot de passe erroné sur la même adresse email. Vérifier que le système bloque les soumissions ultérieures avec un message d'erreur "Trop de tentatives".

### 🔹 Module 2 : Projets & Roadmaps (CRUD)
- [ ] **Création de Projet** : Ouvrir la page de création. Remplir le titre, la description, les dates et sélectionner un client de démo. Soumettre. Vérifier que le projet est créé et que sa progression de départ affiche `0%`.
- [ ] **Création d'Étape** : Sur la page du projet, cliquer sur "Nouvelle Étape". Renseigner un titre, un ordre de passage (ex: 1), une branche git et des critères DoD (un par ligne). Enregistrer.
- [ ] **Évaluation de DoD** : Cocher un critère DoD dans la checklist de l'étape. Recharger la page et vérifier que l'état de la coche est bien conservé (si stocké localement ou persisté).
- [ ] **Validation d'Étape (AJAX)** : Cliquer sur le bouton "Terminer" d'une étape. Vérifier que l'étape passe en "Validé" instantanément sans rechargement de page et que la progression globale du projet s'ajuste dynamiquement.

### 🔹 Module 3 : Kanban & Pièces Jointes
- [ ] **Création de Tâche** : Cliquer sur "Ajouter une tâche". Renseigner le titre, description, estimation JH, et assigner à un collaborateur développeur.
- [ ] **Tableau Kanban** : Se rendre sur le planificateur Kanban au bas de la page du projet. Faire glisser une tâche de la colonne "À faire" vers "En cours". Vérifier que la persistance s'effectue correctement en base (un rafraîchissement de page doit confirmer la nouvelle position).
- [ ] **Téléversement de fichier** : Sur une carte de tâche, cliquer sur "Choisir un fichier". Sélectionner une image (PNG/JPG) ou un PDF. Soumettre. Vérifier que le fichier est stocké dans le dossier `public_html/uploads/tasks/{id}/` avec un nom aléatoire chiffré et qu'il est téléchargeable de façon sécurisée.
- [ ] **Refus de scripts (Sécurité)** : Tenter d'uploader un fichier de script malveillant (ex: `test.php`). Vérifier que le système rejette catégoriquement le téléversement pour extension et type MIME non autorisés.

### 🔹 Module 4 : Modules Éditions Spécifiques
- [ ] **Saisie de temps (ESN)** : Dans la boîte de saisie de temps, sélectionner une tâche, renseigner une durée en minutes (ex: 120 pour 2h) et valider. Vérifier la confirmation.
- [ ] **Rapport de Facturation (ESN)** : Ouvrir `/billing`. Sélectionner le projet et renseigner un taux journalier (TJM). Vérifier que le montant de facturation estimé se calcule et s'affiche instantanément.
- [ ] **Rapports d'Impression (PDF)** : Cliquer sur le bouton d'export PDF d'un projet. Vérifier qu'une mise en page print-friendly épurée s'ouvre, masquant les contrôles d'interface et ouvrant la boîte d'impression du système.
- [ ] **Matrice de Permissions (Enterprise)** : Ouvrir `/roles/permissions`. Modifier une permission pour le rôle Développeur. Enregistrer. Se connecter en Développeur et valider que l'accès est bien refusé ou autorisé selon la modification effectuée.
- [ ] **Conformité RGPD** : Ouvrir `/admin/gdpr`. Saisir l'adresse email d'un utilisateur de test pour exporter ses données au format JSON structuré et tester la suppression définitive.

---

## 🪵 3. Analyse des Logs d'Erreur

En cas de dysfonctionnement sur votre hébergement mutualisé, inspectez immédiatement le fichier suivant qui enregistre toutes les anomalies de base de données de manière confidentielle :
```
/app/logs/error.log
```
Ce fichier est protégé contre tout accès public HTTP direct via le fichier `.htaccess` de protection.
