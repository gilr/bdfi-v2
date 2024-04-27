
# Tables de la zone 'Oeuvres'

## Table des oeuvres

<code>
+-----------------+-------------------+------+-----+---------+----------------+
| Field           | Type              | Null | Key | Default | Extra          |
+-----------------+-------------------+------+-----+---------+----------------+
| id              | int unsigned      | NO   | PRI | NULL    | auto_increment |
| name            | varchar(256)      | NO   |     | NULL    |                |
| type            | varchar(255)      | NO   |     | NULL    |                |
| parent_id       | int unsigned      | YES  | MUL | NULL    |                |
| variant_type    | varchar(255)      | NO   |     | NULL    |                |
| copyright       | varchar(10)       | YES  |     | NULL    |                |
| is_novelization | varchar(255)      | NO   |     | non     |                |
| is_visible      | tinyint(1)        | NO   |     | 1       |                |
| is_serial       | tinyint(1)        | NO   |     | 0       |                |
| is_fullserial   | tinyint(1)        | NO   |     | 0       |                |
| serial_info     | varchar(512)      | YES  |     | NULL    |                |
| synopsis        | text              | YES  |     | NULL    |                |
| title_vo        | varchar(512)      | YES  |     | NULL    |                |
| copyright_fr    | varchar(10)       | YES  |     | NULL    |                |
| pub_vo          | varchar(256)      | YES  |     | NULL    |                |
| translators     | varchar(512)      | YES  |     | NULL    |                |
| is_genre        | varchar(255)      | NO   |     | NULL    |                |
| genre_stat      | varchar(255)      | NO   |     | NULL    |                |
| target_audience | varchar(255)      | NO   |     | NULL    |                |
| target_age      | varchar(255)      | YES  |     | NULL    |                |
| information     | text              | YES  |     | NULL    |                |
| private         | text              | YES  |     | NULL    |                |
| created_at      | timestamp         | YES  |     | NULL    |                |
| updated_at      | timestamp         | YES  |     | NULL    |                |
| created_by      | smallint unsigned | YES  |     | NULL    |                |
| updated_by      | smallint unsigned | YES  |     | NULL    |                |
| deleted_by      | smallint unsigned | YES  |     | NULL    |                |
| deleted_at      | timestamp         | YES  |     | NULL    |                |
+-----------------+-------------------+------+-----+---------+----------------+
</code>

## Table des cycles

<code>
+-------------+-------------------+------+-----+---------+----------------+
| Field       | Type              | Null | Key | Default | Extra          |
+-------------+-------------------+------+-----+---------+----------------+
| id          | int unsigned      | NO   | PRI | NULL    | auto_increment |
| name        | varchar(128)      | NO   |     | NULL    |                |
| nom_bdfi    | varchar(128)      | YES  |     | NULL    |                |
| alt_names   | varchar(512)      | YES  |     | NULL    |                |
| vo_names    | varchar(256)      | YES  |     | NULL    |                |
| type        | varchar(255)      | NO   |     | NULL    |                |
| parent_id   | int unsigned      | YES  | MUL | NULL    |                |
| information | text              | YES  |     | NULL    |                |
| private     | text              | YES  |     | NULL    |                |
| quality     | varchar(255)      | NO   |     | NULL    |                |
| created_at  | timestamp         | YES  |     | NULL    |                |
| updated_at  | timestamp         | YES  |     | NULL    |                |
| created_by  | smallint unsigned | YES  |     | NULL    |                |
| updated_by  | smallint unsigned | YES  |     | NULL    |                |
| deleted_by  | smallint unsigned | YES  |     | NULL    |                |
| deleted_at  | timestamp         | YES  |     | NULL    |                |
+-------------+-------------------+------+-----+---------+----------------+
</code>

## Table des rattachements des oeuvres aux cycles

<code>
+------------+-------------------+------+-----+---------+----------------+
| Field      | Type              | Null | Key | Default | Extra          |
+------------+-------------------+------+-----+---------+----------------+
| id         | int unsigned      | NO   | PRI | NULL    | auto_increment |
| number     | varchar(16)       | YES  |     | NULL    |                |
| order      | decimal(5,2)      | NO   |     | 0.00    |                |
| cycle_id   | int unsigned      | NO   | MUL | NULL    |                |
| title_id   | int unsigned      | NO   | MUL | NULL    |                |
| created_at | timestamp         | YES  |     | NULL    |                |
| updated_at | timestamp         | YES  |     | NULL    |                |
| created_by | smallint unsigned | YES  |     | NULL    |                |
| updated_by | smallint unsigned | YES  |     | NULL    |                |
| deleted_by | smallint unsigned | YES  |     | NULL    |                |
| deleted_at | timestamp         | YES  |     | NULL    |                |
+------------+-------------------+------+-----+---------+----------------+
</code>

## Table des rattachements des oeuvres aux auteurs

<code>
+------------+-------------------+------+-----+---------+----------------+
| Field      | Type              | Null | Key | Default | Extra          |
+------------+-------------------+------+-----+---------+----------------+
| id         | int unsigned      | NO   | PRI | NULL    | auto_increment |
| author_id  | int unsigned      | NO   | MUL | NULL    |                |
| title_id   | int unsigned      | NO   | MUL | NULL    |                |
| created_at | timestamp         | YES  |     | NULL    |                |
| updated_at | timestamp         | YES  |     | NULL    |                |
| created_by | smallint unsigned | YES  |     | NULL    |                |
| updated_by | smallint unsigned | YES  |     | NULL    |                |
| deleted_by | smallint unsigned | YES  |     | NULL    |                |
| deleted_at | timestamp         | YES  |     | NULL    |                |
+------------+-------------------+------+-----+---------+----------------+
</code>

## Table des rattachements des oeuvres aux publications

<code>
+----------------+-------------------+------+-----+---------+----------------+
| Field          | Type              | Null | Key | Default | Extra          |
+----------------+-------------------+------+-----+---------+----------------+
| id             | int unsigned      | NO   | PRI | NULL    | auto_increment |
| level          | decimal(2,1)      | NO   |     | 0.0     |                |
| order          | int unsigned      | NO   |     | 1       |                |
| start_page     | varchar(8)        | YES  |     | NULL    |                |
| end_page       | varchar(8)        | YES  |     | NULL    |                |
| publication_id | int unsigned      | NO   | MUL | NULL    |                |
| title_id       | int unsigned      | NO   | MUL | NULL    |                |
| created_at     | timestamp         | YES  |     | NULL    |                |
| updated_at     | timestamp         | YES  |     | NULL    |                |
| created_by     | smallint unsigned | YES  |     | NULL    |                |
| updated_by     | smallint unsigned | YES  |     | NULL    |                |
| deleted_by     | smallint unsigned | YES  |     | NULL    |                |
| deleted_at     | timestamp         | YES  |     | NULL    |                |
+----------------+-------------------+------+-----+---------+----------------+
</code>

