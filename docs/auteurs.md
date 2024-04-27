['Accueil docs'](welcome.md)


# Tables de la zone 'Auteurs'

## Table principale des (signatures d') auteurs

<code>
+--------------+-------------------+------+-----+---------+----------------+
| Field        | Type              | Null | Key | Default | Extra          |
+--------------+-------------------+------+-----+---------+----------------+
| id           | int unsigned      | NO   | PRI | NULL    | auto_increment |
| name         | varchar(32)       | NO   |     | NULL    |                |
| first_name   | varchar(32)       | YES  |     | NULL    |                |
| nom_bdfi     | varchar(64)       | YES  |     | NULL    |                |
| legal_name   | varchar(128)      | YES  |     | NULL    |                |
| alt_names    | varchar(512)      | YES  |     | NULL    |                |
| is_pseudonym | tinyint(1)        | NO   |     | NULL    |                |
| gender       | varchar(255)      | NO   |     | ?       |                |
| country_id   | smallint unsigned | YES  | MUL | NULL    |                |
| country2_id  | smallint unsigned | YES  | MUL | NULL    |                |
| birth_date   | varchar(10)       | YES  |     | NULL    |                |
| birthplace   | varchar(64)       | YES  |     | NULL    |                |
| date_death   | varchar(10)       | YES  |     | NULL    |                |
| place_death  | varchar(64)       | YES  |     | NULL    |                |
| is_visible   | tinyint(1)        | NO   |     | NULL    |                |
| information  | text              | YES  |     | NULL    |                |
| private      | text              | YES  |     | NULL    |                |
| quality      | varchar(255)      | NO   |     | NULL    |                |
| created_at   | timestamp         | YES  |     | NULL    |                |
| updated_at   | timestamp         | YES  |     | NULL    |                |
| created_by   | smallint unsigned | YES  |     | NULL    |                |
| updated_by   | smallint unsigned | YES  |     | NULL    |                |
| deleted_by   | smallint unsigned | YES  |     | NULL    |                |
| deleted_at   | timestamp         | YES  |     | NULL    |                |
+--------------+-------------------+------+-----+---------+----------------+
</code>

## Table des signatures et pseudonymes

<code>
+--------------+-------------------+------+-----+---------+----------------+
| Field        | Type              | Null | Key | Default | Extra          |
+--------------+-------------------+------+-----+---------+----------------+
| id           | int unsigned      | NO   | PRI | NULL    | auto_increment |
| author_id    | int unsigned      | NO   | MUL | NULL    |                |
| signature_id | int unsigned      | NO   | MUL | NULL    |                |
| created_at   | timestamp         | YES  |     | NULL    |                |
| updated_at   | timestamp         | YES  |     | NULL    |                |
| created_by   | smallint unsigned | YES  |     | NULL    |                |
| updated_by   | smallint unsigned | YES  |     | NULL    |                |
| deleted_by   | smallint unsigned | YES  |     | NULL    |                |
| deleted_at   | timestamp         | YES  |     | NULL    |                |
+--------------+-------------------+------+-----+---------+----------------+
</code>

## Table des sites web

<code>
+-----------------+-------------------+------+-----+---------+----------------+
| Field           | Type              | Null | Key | Default | Extra          |
+-----------------+-------------------+------+-----+---------+----------------+
| id              | int unsigned      | NO   | PRI | NULL    | auto_increment |
| author_id       | int unsigned      | NO   | MUL | NULL    |                |
| url             | varchar(256)      | NO   |     | NULL    |                |
| website_type_id | tinyint unsigned  | NO   | MUL | NULL    |                |
| country_id      | smallint unsigned | NO   | MUL | NULL    |                |
| created_at      | timestamp         | YES  |     | NULL    |                |
| updated_at      | timestamp         | YES  |     | NULL    |                |
| created_by      | smallint unsigned | YES  |     | NULL    |                |
| updated_by      | smallint unsigned | YES  |     | NULL    |                |
| deleted_by      | smallint unsigned | YES  |     | NULL    |                |
| deleted_at      | timestamp         | YES  |     | NULL    |                |
+-----------------+-------------------+------+-----+---------+----------------+
</code>

## Table des relations entre auteurs

<code>
+----------------------+-------------------+------+-----+---------+----------------+
| Field                | Type              | Null | Key | Default | Extra          |
+----------------------+-------------------+------+-----+---------+----------------+
| id                   | int unsigned      | NO   | PRI | NULL    | auto_increment |
| author1_id           | int unsigned      | NO   | MUL | NULL    |                |
| author2_id           | int unsigned      | NO   | MUL | NULL    |                |
| relationship_type_id | tinyint unsigned  | NO   | MUL | NULL    |                |
| created_at           | timestamp         | YES  |     | NULL    |                |
| updated_at           | timestamp         | YES  |     | NULL    |                |
| created_by           | smallint unsigned | YES  |     | NULL    |                |
| updated_by           | smallint unsigned | YES  |     | NULL    |                |
| deleted_by           | smallint unsigned | YES  |     | NULL    |                |
| deleted_at           | timestamp         | YES  |     | NULL    |                |
+----------------------+-------------------+------+-----+---------+----------------+
</code>

## Table des traducteurs

<code>
    A venir
</code>

## Table des illustrateurs

<code>
    A venir
</code>

