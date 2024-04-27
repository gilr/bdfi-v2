['Accueil docs'](welcome.md)

# Tables de la zone 'Prix'

## Table des prix

<code>
+-------------+-------------------+------+-----+---------+----------------+
| Field       | Type              | Null | Key | Default | Extra          |
+-------------+-------------------+------+-----+---------+----------------+
| id          | int unsigned      | NO   | PRI | NULL    | auto_increment |
| name        | varchar(128)      | NO   |     | NULL    |                |
| country_id  | smallint unsigned | YES  | MUL | NULL    |                |
| alt_names   | varchar(512)      | YES  |     | NULL    |                |
| year_start  | varchar(4)        | NO   |     | NULL    |                |
| year_end    | varchar(4)        | YES  |     | NULL    |                |
| given_for   | varchar(256)      | NO   |     | NULL    |                |
| url         | varchar(256)      | YES  |     | NULL    |                |
| information | text              | NO   |     | NULL    |                |
| created_at  | timestamp         | YES  |     | NULL    |                |
| updated_at  | timestamp         | YES  |     | NULL    |                |
| created_by  | smallint unsigned | YES  |     | NULL    |                |
| updated_by  | smallint unsigned | YES  |     | NULL    |                |
| deleted_by  | smallint unsigned | YES  |     | NULL    |                |
| deleted_at  | timestamp         | YES  |     | NULL    |                |
+-------------+-------------------+------+-----+---------+----------------+
</code>

## Table des catégories

<code>
+----------------+-------------------+------+-----+---------+----------------+
| Field          | Type              | Null | Key | Default | Extra          |
+----------------+-------------------+------+-----+---------+----------------+
| id             | int unsigned      | NO   | PRI | NULL    | auto_increment |
| name           | varchar(128)      | NO   |     | NULL    |                |
| award_id       | int unsigned      | NO   | MUL | NULL    |                |
| internal_order | int unsigned      | YES  |     | NULL    |                |
| type           | varchar(255)      | NO   |     | NULL    |                |
| genre          | varchar(255)      | NO   |     | NULL    |                |
| subgenre       | varchar(256)      | YES  |     | NULL    |                |
| information    | text              | YES  |     | NULL    |                |
| created_at     | timestamp         | YES  |     | NULL    |                |
| updated_at     | timestamp         | YES  |     | NULL    |                |
| created_by     | smallint unsigned | YES  |     | NULL    |                |
| updated_by     | smallint unsigned | YES  |     | NULL    |                |
| deleted_by     | smallint unsigned | YES  |     | NULL    |                |
| deleted_at     | timestamp         | YES  |     | NULL    |                |
+----------------+-------------------+------+-----+---------+----------------+
</code>

## Table des gagnants

<code>
+-------------------+-------------------+------+-----+---------+----------------+
| Field             | Type              | Null | Key | Default | Extra          |
+-------------------+-------------------+------+-----+---------+----------------+
| id                | int unsigned      | NO   | PRI | NULL    | auto_increment |
| year              | year              | NO   |     | NULL    |                |
| award_category_id | int unsigned      | NO   | MUL | NULL    |                |
| position          | tinyint unsigned  | NO   |     | NULL    |                |
| name              | varchar(256)      | YES  |     | NULL    |                |
| author_id         | int unsigned      | YES  | MUL | NULL    |                |
| author2_id        | int unsigned      | YES  | MUL | NULL    |                |
| author3_id        | int unsigned      | YES  | MUL | NULL    |                |
| title             | varchar(256)      | YES  |     | NULL    |                |
| vo_title          | varchar(256)      | YES  |     | NULL    |                |
| title_id          | int unsigned      | YES  |     | NULL    |                |
| information       | text              | YES  |     | NULL    |                |
| created_at        | timestamp         | YES  |     | NULL    |                |
| updated_at        | timestamp         | YES  |     | NULL    |                |
| created_by        | smallint unsigned | YES  |     | NULL    |                |
| updated_by        | smallint unsigned | YES  |     | NULL    |                |
| deleted_by        | smallint unsigned | YES  |     | NULL    |                |
| deleted_at        | timestamp         | YES  |     | NULL    |                |
+-------------------+-------------------+------+-----+---------+----------------+
</code>

