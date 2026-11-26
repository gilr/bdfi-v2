-- =====================================================================================
-- Propositions de contributeurs : nouvelle table proposals, et fin du statut de
-- publication « proposal » (les propositions ne passent plus par la table publications).
--
-- Équivalent "à la main" (pour phpMyAdmin, sans artisan) de la migration Laravel :
--   - 2026_11_26_100000_create_proposals_table
-- Les deux doivent rester strictement identiques.
--
-- À exécuter en 2 ÉTAPES SÉPARÉES (chacune son propre "Exécuter" dans phpMyAdmin).
-- =====================================================================================


-- ======================== ÉTAPE 1/2 : table proposals ========================
-- À exécuter une seule fois (erreur "Table already exists" sinon : sans gravité, rien n'est modifié).
CREATE TABLE `proposals` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted',
  `target_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_id` int unsigned DEFAULT NULL,
  `title` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `publisher_name` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approximate_parution` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isbn` varchar(18) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` json NOT NULL,
  `review_note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `result_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proposals_user_id_status_index` (`user_id`,`status`),
  KEY `proposals_status_type_index` (`status`,`type`),
  CONSTRAINT `proposals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ======================== ÉTAPE 2/2 : fin du statut de publication « proposal » ========================
-- Le statut n'existe plus dans l'application : une ligne qui le porterait ferait échouer l'affichage.
-- Contrôle (lecture seule) : doit renvoyer 0 ligne.
SELECT `id`, `name`, `created_by`, `created_at` FROM `publications` WHERE `status` = 'proposal';

-- S'il reste des lignes : les examiner une à une (les supprimer, ou les passer à 'paru' / 'annonce' / 'abandon'
-- selon le cas, par exemple : UPDATE `publications` SET `status` = 'paru' WHERE `id` = ...;).
-- Aucune modification automatique ici : la décision revient à un membre.
