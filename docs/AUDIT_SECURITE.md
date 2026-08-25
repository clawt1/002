# Rapport d'Audit de Sécurité 🛡️

Ce document présente l'évaluation de sécurité de **Roadmap Manager** et certifie l'application des contrôles de robustesse indispensables pour un déploiement sécurisé sur des hébergements mutualisés et des serveurs de production.

---

## 📊 Résumé Exécutif des Contrôles

| Point Vérifié | Mécanisme de Protection Appliqué | Statut |
| :--- | :--- | :---: |
| **Injections SQL** | Requêtes préparées systématiques via PDO (pas de concaténation). | **OK** |
| **Faille XSS** | Échappement systématique des sorties avec `htmlspecialchars(..., ENT_QUOTES)`. | **OK** |
| **Failles CSRF** | Jetons CSRF cryptographiques uniques par session validés sur chaque POST. | **OK** |
| **Sécurité des Sessions** | Paramètres `httponly=1`, `secure=1` (si HTTPS), `use_strict_mode=1`, `samesite=Lax`. | **OK** |
| **Téléversement de Fichiers** | Liste blanche stricte d'extensions + validation MIME + renommage aléatoire. | **OK** |
| **Exécution de Code (.php)** | Désactivation de l'engine PHP et blocage d'accès dans le dossier `/uploads`. | **OK** |
| **Divulgation d'Informations** | Fichiers de configuration, logs, et base SQL protégés par `.htaccess` restrictifs. | **OK** |

---

## 🔍 Détail technique des Protections Implémentées

### 1. Injections SQL (SQLi)
* **Menace** : Un attaquant insère des commandes SQL malveillantes dans un champ de saisie pour manipuler ou exfiltrer la base de données.
* **Mise en œuvre** : Notre classe de connexion `/app/Core/Database.php` utilise le singleton PDO. Toutes les requêtes de l'application utilisent systématiquement la méthode `query($sql, $params)` qui effectue des requêtes préparées via l'instruction `prepare()` et lie les paramètres. Aucune concaténation de variables utilisateurs n'est tolérée dans les requêtes.

### 2. Cross-Site Scripting (XSS)
* **Menace** : Un attaquant injecte des scripts JavaScript dans un champ texte (ex: description d'une tâche) pour qu'il soit exécuté par les navigateurs des autres utilisateurs.
* **Mise en œuvre** : Dans l'ensemble de nos fichiers de template `/app/Views/`, toutes les variables dynamiques provenant d'une saisie utilisateur sont entourées de la fonction de protection de base de PHP :
  `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`. Cela neutralise l'exécution de balises `<script>` ou d'événements malveillants.

### 3. Cross-Site Request Forgery (CSRF)
* **Menace** : Un site tiers tente de faire exécuter à un utilisateur authentifié une action administrative (ex: supprimer un projet) à son insu.
* **Mise en œuvre** : Notre composant `/app/Core/Csrf.php` génère un jeton cryptographique aléatoire de 32 octets stocké de manière sécurisée en session. Chaque formulaire HTML intègre un champ masqué généré par `Csrf::field()`. Lors de la soumission en POST, le contrôleur vérifie la validité du jeton reçu via `Csrf::verify()`. En cas d'absence ou d'invalidité, la requête est immédiatement avortée et détruite.

### 4. Sessions Web Sécurisées
* **Menace** : Vol d'identifiant de session ou fixation de session.
* **Mise en œuvre** : Notre classe de démarrage de session `/app/Core/Session.php` configure explicitement l'environnement PHP lors du démarrage :
  * `cookie_httponly = true` : Empêche le vol de session via des failles XSS en interdisant l'accès au cookie de session via JavaScript (`document.cookie`).
  * `cookie_secure = true` : Restreint la transmission du cookie de session aux connexions chiffrées HTTPS uniquement (activé dynamiquement si le site est configuré en HTTPS).
  * `cookie_samesite = Lax` : Limite l'envoi du cookie de session lors de requêtes d'origine croisée pour contrecarrer les attaques CSRF.
  * `use_strict_mode = true` : Prévient la fixation de session.
  * Régénération de l'ID de session à la connexion via `session_regenerate_id(true)` pour invalider l'ancien identifiant de session.

### 5. Téléversements Sécurisés (Uploads)
* **Menace** : Un utilisateur uploade un script PHP malveillant déguisé en image pour l'exécuter sur le serveur à distance et prendre le contrôle total du site (Remote Code Execution - RCE).
* **Mise en œuvre** : Notre mécanisme de téléversement dans `TaskController.php` et `ClientController.php` applique un triptyque de défense hermétique :
  1. **Validation d'extension** : Seule une liste blanche restreinte d'extensions (images, PDF, documents office) est autorisée.
  2. **Validation de type MIME** : Le système utilise la fonction native `mime_content_type()` pour analyser le contenu réel du fichier temporaire et vérifier qu'il correspond à un format autorisé, bloquant l'envoi d'un script renommé en `.png`.
  3. **Renommage aléatoire** : Le fichier est stocké sous un nom de 16 octets générés de façon cryptographique aléatoire, rendant impossible de deviner le nom du fichier physique pour l'exécuter directement.
  4. **Contrôle d'accès Apache** : Un fichier `.htaccess` restrictif est positionné dans `/public_html/uploads/.htaccess`. Il désactive l'interpréteur de scripts PHP (`php_flag engine off`) et refuse l'accès direct aux fichiers ayant une extension exécutable (php, py, pl, sh, exe, cgi).

### 6. Protection des Fichiers Sensibles (Cloisonnement)
* **Menace** : Un visiteur tente d'ouvrir directement un fichier de log, de base de données ou d'environnement pour voler des identifiants (ex: `/.env` ou `/database/schema.sql`).
* **Mise en œuvre** :
  * Notre architecture sépare la racine web public (`/public_html`) qui est le seul point exposed, tandis que le dossier `/app` et le dossier `/database` sont placés en dehors.
  * Pour parer aux limites des hébergements mutualisés d'entrée de gamme, nous fournissons un fichier de sécurité `/app/.htaccess` contenant l'instruction de blocage absolue `Require all denied` ("Deny from all").
  * Le fichier `.htaccess` de `/public_html` bloque également l'accès public direct à toute extension sensible (`.env`, `.sql`, `.md`, `.json`, `.log`).
