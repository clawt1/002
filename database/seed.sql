SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `roles`;
INSERT INTO `roles` (`id`, `nom`, `permissions`) VALUES
(1, 'Administrateur', '{"all": true}'),
(2, 'Chef de projet', '{"projects.create": true, "projects.view": true, "projects.edit": true, "projects.delete": true, "steps.create": true, "steps.edit": true, "steps.delete": true, "tasks.create": true, "tasks.edit": true, "tasks.delete": true, "risks.manage": true, "time.view": true}'),
(3, 'Développeur', '{"projects.view": true, "steps.view": true, "tasks.view": true, "tasks.edit_status": true, "time.track": true}'),
(4, 'Manager', '{"projects.view": true, "steps.view": true, "tasks.view": true, "time.view": true}'),
(5, 'Client/Observateur', '{"projects.view": true, "steps.view": true, "tasks.view": true}'),
(6, 'Direction', '{"projects.view": true, "dashboard.consolidated": true, "risks.view": true}'),
(7, 'Formateur', '{"projects.template": true, "projects.create": true, "projects.view": true, "steps.grade": true, "tasks.view": true}'),
(8, 'Apprenant', '{"projects.view": true, "steps.view": true, "tasks.edit_status": true}');

TRUNCATE TABLE `tech_stacks`;
INSERT INTO `tech_stacks` (`id`, `nom`, `type`, `logo`) VALUES
(1, 'PHP Natif + MySQL', 'fullstack', 'php.png'),
(2, 'React + Laravel', 'fullstack', 'react.png'),
(3, 'Node.js + Vue.js', 'fullstack', 'node.png');

TRUNCATE TABLE `users`;
INSERT INTO `users` (`id`, `nom`, `email`, `mot_de_passe_hash`, `role_id`, `actif`) VALUES
(1, 'Super Admin', 'admin@roadmap.local', '$2y$10$cQW.yHNa6PspaOZCtwjgtOFDWpajxrD1NqzTWVpuUlbYAolDkzcvG', 1, 1),
(2, 'Alice Chef', 'alice@roadmap.local', '$2y$10$x4F28MLUjAZWECLaQ9vjUObMoZ1xWxXDWgqawVfPZQkB6sBiLL3ta', 2, 1),
(3, 'Bob Dev', 'bob@roadmap.local', '$2y$10$x4F28MLUjAZWECLaQ9vjUObMoZ1xWxXDWgqawVfPZQkB6sBiLL3ta', 3, 1),
(4, 'Charlie Client', 'charlie@roadmap.local', '$2y$10$x4F28MLUjAZWECLaQ9vjUObMoZ1xWxXDWgqawVfPZQkB6sBiLL3ta', 5, 1);

TRUNCATE TABLE `clients`;
INSERT INTO `clients` (`id`, `nom`, `logo`, `couleur_primaire`) VALUES
(1, 'Acme Corp', 'acme.png', '#10b981'),
(2, 'Globex Inc', 'globex.png', '#8b5cf6');

TRUNCATE TABLE `projects`;
INSERT INTO `projects` (`id`, `titre`, `description`, `stack_technique`, `tech_stack_id`, `owner_id`, `client_id`, `token_public`, `date_debut`, `date_fin_prevue`, `statut`) VALUES
(1, 'Roadmap Manager MVP', 'Développement de l''application de gestion de roadmap projet.', 'PHP Natif + MySQL', 1, 2, 1, 'token_demo_acme_123456789', '2026-03-01', '2026-06-30', 'En cours');

TRUNCATE TABLE `steps`;
INSERT INTO `steps` (`id`, `project_id`, `titre`, `description`, `definition_of_done`, `deadline`, `branche_git`, `ordre`, `statut`) VALUES
(1, 1, 'Architecture & Base de données', 'Initialisation du dépôt, configuration de la base de données et de l''autoloading.', '["Schéma SQL validé", "Connexion BDD fonctionnelle", "Routeur de base opérationnel"]', '2026-03-15', 'main', 1, 'Terminé'),
(2, 1, 'Authentification & Sécurité', 'Mise en place de l''authentification avec sécurité CSRF et sessions sécurisées.', '["Connexion / Déconnexion opérationnelles", "Protection CSRF active sur tout formulaire", "Toutes les requêtes sont préparées"]', '2026-04-15', 'feature/auth', 2, 'En cours'),
(3, 1, 'Modules Métiers & Rapports', 'Développement des CRUD Projets/Etapes/Tâches et exports PDF/CSV.', '["CRUD opérationnels", "Rapports d''audit exportables", "Calculateur d''avancement fonctionnel"]', '2026-05-30', 'feature/crud', 3, 'À faire');

TRUNCATE TABLE `tasks`;
INSERT INTO `tasks` (`id`, `step_id`, `titre`, `description`, `livrable`, `criteres_acceptation`, `tests`, `exigences_securite`, `estimation_jours`, `assigne_id`, `statut`) VALUES
(1, 1, 'Élaboration du schéma SQL', 'Créer le fichier schema.sql complet avec clés étrangères et index.', 'schema.sql', 'Le script s''exécute sans erreur sur phpMyAdmin', 'Vérifier la création des index', 'Utiliser InnoDB et UTF8MB4', 1.50, 3, 'Terminé'),
(2, 1, 'Mise en place du routeur', 'Création d''un routeur MVC simple supportant les routes dynamiques.', 'Router.php', 'Le routeur redirige correctement vers les contrôleurs', 'Routes de démo testées avec succès', 'Échappement des routes d''URL', 2.00, 3, 'Terminé'),
(3, 2, 'Création du formulaire de login', 'Développer la vue de login et le contrôleur de connexion.', 'login.php + AuthController.php', 'Validation de l''email et du mot de passe hashé', 'Test d''injection SQL bloquée', 'Régénération d''ID session + Cookie Secure', 1.00, 3, 'En cours'),
(4, 2, 'Mise en place des jetons CSRF', 'Générer et valider un jeton CSRF à chaque requête POST.', 'Csrf.php', 'Rejet des formulaires si le token CSRF est absent ou invalide', 'Test d''envoi de formulaire forgé', 'Jeton aléatoire par session cryptographique', 1.50, 3, 'À faire');

TRUNCATE TABLE `risks`;
INSERT INTO `risks` (`id`, `project_id`, `description`, `probabilite`, `impact`, `plan_mitigation`, `statut`) VALUES
(1, 1, 'Retard sur les validations de maquettes par le client', 'Moyenne', 'Élevé', 'Mettre en place des réunions de validation hebdomadaires avec le client.', 'Identifié');

TRUNCATE TABLE `time_entries`;
INSERT INTO `time_entries` (`id`, `task_id`, `user_id`, `date`, `duree_minutes`, `description`, `facturable`) VALUES
(1, 1, 3, '2026-03-05', 90, 'Rédaction du schéma initial de la base de données', 1),
(2, 2, 3, '2026-03-08', 120, 'Implémentation du routeur et du front controller', 1);

TRUNCATE TABLE `project_permissions`;
INSERT INTO `project_permissions` (`project_id`, `user_id`, `niveau`) VALUES
(1, 3, 'ecriture'),
(1, 4, 'lecture');

SET FOREIGN_KEY_CHECKS = 1;
