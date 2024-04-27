
#Tables communes

## Table des pays

<code>
+----------------+-------------------+------+-----+---------+----------------+
| Field          | Type              | Null | Key | Default | Extra          |
+----------------+-------------------+------+-----+---------+----------------+
| id             | smallint unsigned | NO   | PRI | NULL    | auto_increment |
| name           | varchar(32)       | NO   | UNI | NULL    |                |
| nationality    | varchar(32)       | NO   | UNI | NULL    |                |
| code           | char(2)           | NO   | UNI | NULL    |                |
| internal_order | int unsigned      | NO   |     | NULL    |                |
| created_at     | timestamp         | YES  |     | NULL    |                |
| updated_at     | timestamp         | YES  |     | NULL    |                |
| created_by     | smallint unsigned | YES  |     | NULL    |                |
| updated_by     | smallint unsigned | YES  |     | NULL    |                |
| deleted_by     | smallint unsigned | YES  |     | NULL    |                |
| deleted_at     | timestamp         | YES  |     | NULL    |                |
+----------------+-------------------+------+-----+---------+----------------+
</code>

## Table des types de site

<code>
+----------------+-------------------+------+-----+---------+----------------+
| Field          | Type              | Null | Key | Default | Extra          |
+----------------+-------------------+------+-----+---------+----------------+
| id             | tinyint unsigned  | NO   | PRI | NULL    | auto_increment |
| name           | varchar(32)       | NO   | UNI | NULL    |                |
| information    | varchar(128)      | YES  |     | NULL    |                |
| displayed_text | varchar(64)       | NO   | UNI | NULL    |                |
| is_obsolete    | tinyint(1)        | NO   |     | NULL    |                |
| created_at     | timestamp         | YES  |     | NULL    |                |
| updated_at     | timestamp         | YES  |     | NULL    |                |
| created_by     | smallint unsigned | YES  |     | NULL    |                |
| updated_by     | smallint unsigned | YES  |     | NULL    |                |
| deleted_by     | smallint unsigned | YES  |     | NULL    |                |
| deleted_at     | timestamp         | YES  |     | NULL    |                |
+----------------+-------------------+------+-----+---------+----------------+
</code>

## Table des types de lien

<code>
+----------------------+-------------------+------+-----+---------+----------------+
| Field                | Type              | Null | Key | Default | Extra          |
+----------------------+-------------------+------+-----+---------+----------------+
| id                   | tinyint unsigned  | NO   | PRI | NULL    | auto_increment |
| name                 | varchar(64)       | NO   | UNI | NULL    |                |
| relationship         | varchar(32)       | NO   |     | NULL    |                |
| reverse_relationship | varchar(32)       | NO   |     | NULL    |                |
| created_at           | timestamp         | YES  |     | NULL    |                |
| updated_at           | timestamp         | YES  |     | NULL    |                |
| created_by           | smallint unsigned | YES  |     | NULL    |                |
| updated_by           | smallint unsigned | YES  |     | NULL    |                |
| deleted_by           | smallint unsigned | YES  |     | NULL    |                |
| deleted_at           | timestamp         | YES  |     | NULL    |                |
+----------------------+-------------------+------+-----+---------+----------------+
</code>


