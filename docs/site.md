
# Autres tables du site

## Table des évènements

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
