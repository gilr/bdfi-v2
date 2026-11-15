-- =====================================================================================
-- Bibliothèque : état des ouvrages possédés (table user_publication, colonne status).
--
-- Équivalent "à la main" (pour phpMyAdmin, sans artisan) de la migration Laravel :
--   - 2024_03_02_231920_create_user_publication_table (colonne status ajoutée)
-- Les deux doivent rester strictement identiques.
--
-- Valeurs : voir enum App\Enums\BiblioPublicationStatus
--   'possede' (par défaut : toutes les lignes existantes) ou 'abime'.
--
-- ├Ç exécuter une seule fois (erreur "Duplicate column" sinon : sans gravité, rien n'est modifié).
-- =====================================================================================

ALTER TABLE `user_publication`
  ADD COLUMN `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'possede' AFTER `publication_id`;
