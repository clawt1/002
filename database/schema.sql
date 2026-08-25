SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(50) NOT NULL UNIQUE,
  `permissions` JSON DEFAULT NULL,
  `cree_le` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `mot_de_passe_hash` VARCHAR(255) NOT NULL,
  `role_id` INT DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `actif` TINYINT(1) DEFAULT 1,
  `date_creation` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(100) NOT NULL UNIQUE,
  `logo` VARCHAR(255) DEFAULT NULL,
  `couleur_primaire` VARCHAR(7) DEFAULT '#3b82f6',
  `cree_le` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tech_stacks`;
CREATE TABLE `tech_stacks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(100) NOT NULL UNIQUE,
  `type` ENUM('backend', 'frontend', 'fullstack') NOT NULL,
  `logo` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titre` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `stack_technique` VARCHAR(255) DEFAULT NULL,
  `tech_stack_id` INT DEFAULT NULL,
  `owner_id` INT DEFAULT NULL,
  `client_id` INT DEFAULT NULL,
  `token_public` VARCHAR(64) DEFAULT NULL UNIQUE,
  `date_debut` DATE DEFAULT NULL,
  `date_fin_prevue` DATE DEFAULT NULL,
  `statut` ENUM('Planifié', 'En cours', 'En pause', 'Livré', 'Annulé') DEFAULT 'Planifié',
  `cree_le` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_projects_statut` (`statut`),
  INDEX `idx_projects_owner` (`owner_id`),
  INDEX `idx_projects_client` (`client_id`),
  CONSTRAINT `fk_projects_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_projects_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_projects_tech_stack` FOREIGN KEY (`tech_stack_id`) REFERENCES `tech_stacks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `steps`;
CREATE TABLE `steps` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `titre` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `definition_of_done` JSON DEFAULT NULL,
  `deadline` DATE DEFAULT NULL,
  `branche_git` VARCHAR(100) DEFAULT NULL,
  `ordre` INT DEFAULT 0,
  `statut` ENUM('À faire', 'En cours', 'Terminé') DEFAULT 'À faire',
  `note` INT DEFAULT NULL,
  `commentaire_formateur` TEXT DEFAULT NULL,
  `cree_le` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_steps_project` (`project_id`),
  INDEX `idx_steps_statut` (`statut`),
  INDEX `idx_steps_deadline` (`deadline`),
  CONSTRAINT `fk_steps_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `step_id` INT NOT NULL,
  `titre` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `livrable` VARCHAR(255) DEFAULT NULL,
  `criteres_acceptation` TEXT DEFAULT NULL,
  `tests` TEXT DEFAULT NULL,
  `exigences_securite` TEXT DEFAULT NULL,
  `estimation_jours` DECIMAL(5,2) DEFAULT 0.00,
  `assigne_id` INT DEFAULT NULL,
  `statut` ENUM('À faire', 'En cours', 'En revue', 'Terminé') DEFAULT 'À faire',
  `cree_le` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `maj_le` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_tasks_step` (`step_id`),
  INDEX `idx_tasks_statut` (`statut`),
  INDEX `idx_tasks_assigne` (`assigne_id`),
  CONSTRAINT `fk_tasks_step` FOREIGN KEY (`step_id`) REFERENCES `steps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tasks_assigne` FOREIGN KEY (`assigne_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `cible_type` VARCHAR(50) DEFAULT NULL,
  `cible_id` INT DEFAULT NULL,
  `message` TEXT NOT NULL,
  `lu` TINYINT(1) DEFAULT 0,
  `cree_le` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_notifications_user_lu` (`user_id`, `lu`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `entite_type` VARCHAR(50) NOT NULL,
  `entite_id` INT DEFAULT NULL,
  `detail` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `date_action` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_logs_user` (`user_id`),
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `attachments`;
CREATE TABLE `attachments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_id` INT NOT NULL,
  `chemin_fichier` VARCHAR(255) NOT NULL,
  `nom_original` VARCHAR(255) NOT NULL,
  `uploaded_by` INT DEFAULT NULL,
  `date_upload` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_attachments_task` (`task_id`),
  CONSTRAINT `fk_attachments_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_attachments_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `risks`;
CREATE TABLE `risks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `description` TEXT NOT NULL,
  `probabilite` ENUM('Faible', 'Moyenne', 'Élevée') DEFAULT 'Faible',
  `impact` ENUM('Faible', 'Moyen', 'Élevé') DEFAULT 'Faible',
  `plan_mitigation` TEXT DEFAULT NULL,
  `statut` ENUM('Identifié', 'Atténué', 'Survenu', 'Fermé') DEFAULT 'Identifié',
  `cree_le` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_risks_project` (`project_id`),
  CONSTRAINT `fk_risks_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `time_entries`;
CREATE TABLE `time_entries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `duree_minutes` INT NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `facturable` TINYINT(1) DEFAULT 1,
  `cree_le` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_time_entries_task` (`task_id`),
  INDEX `idx_time_entries_user` (`user_id`),
  CONSTRAINT `fk_time_entries_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_time_entries_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `project_permissions`;
CREATE TABLE `project_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `niveau` ENUM('lecture', 'ecriture', 'admin') DEFAULT 'lecture',
  UNIQUE KEY `uk_project_user` (`project_id`, `user_id`),
  CONSTRAINT `fk_permissions_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_permissions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
