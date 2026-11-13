-- =====================================================================================
-- Évènements, PHASE 1 : création de la table des occurrences (event_occurrences),
-- et reprise des données de la table events (sans rien supprimer).
--
-- Équivalent "à la main" (pour phpMyAdmin, sans artisan) des migrations Laravel :
--   - 2023_08_03_173806_create_events_table          (colonnes rendues facultatives + periodicity)
--   - 2023_08_03_173807_create_event_occurrences_table (nouvelle table)
-- Les deux doivent rester strictement identiques à ce script.
--
-- Après ce script, le site actuel continue de fonctionner sans changement : les colonnes
-- start_date, end_date, is_confirmed, publication_date de events restent remplies,
-- elles seront supprimées en phase 3 seulement.
--
-- À exécuter en 4 ÉTAPES SÉPARÉES (chacune son propre "Exécuter" dans phpMyAdmin).
-- Un ALTER/CREATE TABLE valide ("commit") silencieusement toute transaction ouverte :
-- les étapes de structure (1, 2) sont donc séparées de l'étape de données (3).
--
-- Les étapes 1 et 2 ne peuvent être exécutées qu'une seule fois (erreur "Duplicate column"
-- ou "Table already exists" sinon : sans gravité, rien n'est modifié).
-- Les étapes 3 et 4 peuvent être relancées sans risque (elles ne font rien si déjà faites).
-- =====================================================================================


-- ======================== ÉTAPE 1/4 : table events ========================
ALTER TABLE `events`
  ADD COLUMN `periodicity` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `type`,
  ADD COLUMN `year_start` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `periodicity`,
  ADD COLUMN `year_end` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `year_start`,
  MODIFY `start_date` date DEFAULT NULL,
  MODIFY `end_date` date DEFAULT NULL,
  MODIFY `place` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  MODIFY `information` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  MODIFY `is_confirmed` tinyint(1) DEFAULT NULL;


-- ======================== ÉTAPE 2/4 : table event_occurrences ========================
CREATE TABLE `event_occurrences` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int unsigned NOT NULL,
  `label` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `place` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `information` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `publication_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` smallint unsigned DEFAULT NULL,
  `updated_by` smallint unsigned DEFAULT NULL,
  `deleted_by` smallint unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_occurrences_event_id_foreign` (`event_id`),
  KEY `event_occurrences_start_date_index` (`start_date`),
  KEY `event_occurrences_end_date_index` (`end_date`),
  CONSTRAINT `event_occurrences_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ======================== ÉTAPE 3/4 : reprise des données ========================
-- Une occurrence par évènement existant (même dates, lieu, information, confirmation,
-- date de publication, auteurs et dates de création/modification/suppression).
-- Les informations et le lieu sont recopiés, pas déplacés : events les garde jusqu'à la phase 2.
-- Rien n'est modifié dans events : en cas de souci, "DELETE FROM event_occurrences;" et relancer.
INSERT INTO `event_occurrences`
  (`event_id`, `start_date`, `end_date`, `place`, `information`, `is_confirmed`, `publication_date`,
   `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`)
SELECT e.`id`, e.`start_date`, e.`end_date`, NULLIF(TRIM(e.`place`), ''), NULLIF(e.`information`, ''),
       COALESCE(e.`is_confirmed`, 0), e.`publication_date`,
       e.`created_at`, e.`updated_at`, e.`created_by`, e.`updated_by`, e.`deleted_by`, e.`deleted_at`
FROM `events` e
WHERE e.`start_date` IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM `event_occurrences` o WHERE o.`event_id` = e.`id`);

-- Vérification : les deux nombres doivent être égaux
SELECT (SELECT COUNT(*) FROM `events` WHERE `start_date` IS NOT NULL) AS nb_events,
       (SELECT COUNT(*) FROM `event_occurrences`) AS nb_occurrences;


-- ======================== ÉTAPE 4/4 : table des migrations Laravel ========================
-- Enregistre la nouvelle migration comme déjà exécutée (pour qu'un éventuel "artisan migrate"
-- futur ne tente pas de recréer la table).
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2023_08_03_173807_create_event_occurrences_table', t.`next_batch`
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS `next_batch` FROM `migrations`) t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2023_08_03_173807_create_event_occurrences_table');
