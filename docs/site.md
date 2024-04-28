[Accueil documentation](welcome.md)

# Autres tables du site

## Table des évènements

(à compléter)

<code>
+------------------+-------------------+------+-----+---------+----------------+
| Field            | Type              | Null | Key | Default | Extra          |
+------------------+-------------------+------+-----+---------+----------------+
| id               | int unsigned      | NO   | PRI | NULL    | auto_increment |
| name             | varchar(128)      | NO   |     | NULL    |                |
| type             | varchar(255)      | NO   |     | NULL    |                |
| start_date       | date              | NO   |     | NULL    |                |
| end_date         | date              | NO   |     | NULL    |                |
| place            | varchar(64)       | NO   |     | NULL    |                |
| information      | text              | NO   |     | NULL    |                |
| url              | varchar(256)      | YES  |     | NULL    |                |
| is_confirmed     | tinyint(1)        | NO   |     | NULL    |                |
| is_full_scope    | tinyint(1)        | NO   |     | NULL    |                |
| publication_date | datetime          | YES  |     | NULL    |                |
| created_at       | timestamp         | YES  |     | NULL    |                |
| updated_at       | timestamp         | YES  |     | NULL    |                |
| created_by       | smallint unsigned | YES  |     | NULL    |                |
| updated_by       | smallint unsigned | YES  |     | NULL    |                |
| deleted_by       | smallint unsigned | YES  |     | NULL    |                |
| deleted_at       | timestamp         | YES  |     | NULL    |                |
+------------------+-------------------+------+-----+---------+----------------+
</code>

## Table des annonces

(à compléter)

<code>
+-------------+-------------------+------+-----+---------+----------------+
| Field       | Type              | Null | Key | Default | Extra          |
+-------------+-------------------+------+-----+---------+----------------+
| id          | int unsigned      | NO   | PRI | NULL    | auto_increment |
| name        | varchar(64)       | NO   |     | NULL    |                |
| information | text              | NO   |     | NULL    |                |
| date        | date              | NO   |     | NULL    |                |
| type        | varchar(255)      | NO   |     | NULL    |                |
| url         | varchar(256)      | YES  |     | NULL    |                |
| created_at  | timestamp         | YES  |     | NULL    |                |
| updated_at  | timestamp         | YES  |     | NULL    |                |
| created_by  | smallint unsigned | YES  |     | NULL    |                |
| updated_by  | smallint unsigned | YES  |     | NULL    |                |
| deleted_by  | smallint unsigned | YES  |     | NULL    |                |
| deleted_at  | timestamp         | YES  |     | NULL    |                |
+-------------+-------------------+------+-----+---------+----------------+
</code>

## Table des articles

(à compléter)

<code>
+------------+-------------------+------+-----+---------+----------------+
| Field      | Type              | Null | Key | Default | Extra          |
+------------+-------------------+------+-----+---------+----------------+
| id         | int unsigned      | NO   | PRI | NULL    | auto_increment |
| item_type  | varchar(255)      | NO   |     | NULL    |                |
| item_id    | int unsigned      | NO   |     | NULL    |                |
| content    | text              | NO   |     | NULL    |                |
| created_at | timestamp         | YES  |     | NULL    |                |
| updated_at | timestamp         | YES  |     | NULL    |                |
| created_by | smallint unsigned | YES  |     | NULL    |                |
| updated_by | smallint unsigned | YES  |     | NULL    |                |
| deleted_by | smallint unsigned | YES  |     | NULL    |                |
| deleted_at | timestamp         | YES  |     | NULL    |                |
+------------+-------------------+------+-----+---------+----------------+
</code>

## Table des documents

(à compléter)

<code>
+------------+-------------------+------+-----+---------+----------------+
| Field      | Type              | Null | Key | Default | Extra          |
+------------+-------------------+------+-----+---------+----------------+
| id         | int unsigned      | NO   | PRI | NULL    | auto_increment |
| name       | text              | NO   |     | NULL    |                |
| file       | text              | NO   |     | NULL    |                |
| author_id  | int unsigned      | YES  | MUL | NULL    |                |
| item_type  | varchar(255)      | NO   |     | NULL    |                |
| item_id    | int unsigned      | NO   |     | NULL    |                |
| created_at | timestamp         | YES  |     | NULL    |                |
| updated_at | timestamp         | YES  |     | NULL    |                |
| created_by | smallint unsigned | YES  |     | NULL    |                |
| updated_by | smallint unsigned | YES  |     | NULL    |                |
| deleted_by | smallint unsigned | YES  |     | NULL    |                |
| deleted_at | timestamp         | YES  |     | NULL    |                |
+------------+-------------------+------+-----+---------+----------------+
</code>

## Table des statistiques

(à compléter)

<code>
+---------------+-------------------+------+-----+---------+----------------+
| Field         | Type              | Null | Key | Default | Extra          |
+---------------+-------------------+------+-----+---------+----------------+
| id            | int unsigned      | NO   | PRI | NULL    | auto_increment |
| date          | date              | NO   |     | NULL    |                |
| authors       | int unsigned      | NO   |     | NULL    |                |
| series        | int unsigned      | YES  |     | NULL    |                |
| references    | int unsigned      | NO   |     | NULL    |                |
| novels        | int unsigned      | NO   |     | NULL    |                |
| short_stories | int unsigned      | NO   |     | NULL    |                |
| collections   | int unsigned      | YES  |     | NULL    |                |
| magazines     | int unsigned      | YES  |     | NULL    |                |
| essays        | int unsigned      | YES  |     | NULL    |                |
| created_at    | timestamp         | YES  |     | NULL    |                |
| updated_at    | timestamp         | YES  |     | NULL    |                |
| created_by    | smallint unsigned | YES  |     | NULL    |                |
| updated_by    | smallint unsigned | YES  |     | NULL    |                |
| deleted_by    | smallint unsigned | YES  |     | NULL    |                |
| deleted_at    | timestamp         | YES  |     | NULL    |                |
+---------------+-------------------+------+-----+---------+----------------+
</code>

## Tables des usagers

(à compléter)

<code>
+---------------------------+-------------------+------+-----+---------+----------------+
| Field                     | Type              | Null | Key | Default | Extra          |
+---------------------------+-------------------+------+-----+---------+----------------+
| id                        | bigint unsigned   | NO   | PRI | NULL    | auto_increment |
| name                      | varchar(64)       | NO   |     | NULL    |                |
| role                      | varchar(255)      | NO   |     | user    |                |
| email                     | varchar(64)       | NO   | UNI | NULL    |                |
| email_verified_at         | timestamp         | YES  |     | NULL    |                |
| password                  | varchar(64)       | NO   |     | NULL    |                |
| two_factor_secret         | text              | YES  |     | NULL    |                |
| two_factor_recovery_codes | text              | YES  |     | NULL    |                |
| two_factor_confirmed_at   | timestamp         | YES  |     | NULL    |                |
| remember_token            | varchar(100)      | YES  |     | NULL    |                |
| current_team_id           | bigint unsigned   | YES  |     | NULL    |                |
| profile_photo_path        | varchar(2048)     | YES  |     | NULL    |                |
| format_date               | varchar(255)      | NO   |     | abr     |                |
| items_par_page            | int unsigned      | NO   |     | 1000    |                |
| with_icons                | tinyint(1)        | NO   |     | 1       |                |
| fonction_aide             | tinyint(1)        | NO   |     | 0       |                |
| gestion_biblio            | tinyint(1)        | NO   |     | 0       |                |
| created_at                | timestamp         | YES  |     | NULL    |                |
| updated_at                | timestamp         | YES  |     | NULL    |                |
| created_by                | smallint unsigned | YES  |     | NULL    |                |
| updated_by                | smallint unsigned | YES  |     | NULL    |                |
| deleted_by                | smallint unsigned | YES  |     | NULL    |                |
| deleted_at                | timestamp         | YES  |     | NULL    |                |
+---------------------------+-------------------+------+-----+---------+----------------+
</code>

## Table de bibliothèque : collections suivies par un usager

(à compléter)

<code>
+---------------+-----------------+------+-----+---------+----------------+
| Field         | Type            | Null | Key | Default | Extra          |
+---------------+-----------------+------+-----+---------+----------------+
| id            | int unsigned    | NO   | PRI | NULL    | auto_increment |
| status        | varchar(255)    | NO   |     | NULL    |                |
| user_id       | bigint unsigned | NO   | MUL | NULL    |                |
| collection_id | int unsigned    | NO   | MUL | NULL    |                |
| created_at    | timestamp       | YES  |     | NULL    |                |
| updated_at    | timestamp       | YES  |     | NULL    |                |
+---------------+-----------------+------+-----+---------+----------------+
</code>

## Table de bibliothèque : ouvrages possédés par un usager

(à compléter)

<code>
+----------------+-----------------+------+-----+---------+----------------+
| Field          | Type            | Null | Key | Default | Extra          |
+----------------+-----------------+------+-----+---------+----------------+
| id             | int unsigned    | NO   | PRI | NULL    | auto_increment |
| user_id        | bigint unsigned | NO   | MUL | NULL    |                |
| publication_id | int unsigned    | NO   | MUL | NULL    |                |
| created_at     | timestamp       | YES  |     | NULL    |                |
| updated_at     | timestamp       | YES  |     | NULL    |                |
+----------------+-----------------+------+-----+---------+----------------+
</code>
