['Accueil docs'](welcome.md)

# Tables de la zone 'Publications'

## Table des éditeurs

<code>
+-------------+-------------------+------+-----+---------+----------------+
| Field       | Type              | Null | Key | Default | Extra          |
+-------------+-------------------+------+-----+---------+----------------+
| id          | int unsigned      | NO   | PRI | NULL    | auto_increment |
| name        | varchar(128)      | NO   |     | NULL    |                |
| sigle_bdfi  | varchar(8)        | YES  |     | NULL    |                |
| alt_names   | varchar(512)      | YES  |     | NULL    |                |
| country_id  | smallint unsigned | YES  | MUL | NULL    |                |
| type        | varchar(255)      | NO   |     | editeur |                |
| year_start  | int unsigned      | NO   |     | NULL    |                |
| year_end    | int unsigned      | YES  |     | NULL    |                |
| address     | text              | YES  |     | NULL    |                |
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

## Table des collections

<code>
+---------------+-------------------+------+-----+---------+----------------+
| Field         | Type              | Null | Key | Default | Extra          |
+---------------+-------------------+------+-----+---------+----------------+
| id            | int unsigned      | NO   | PRI | NULL    | auto_increment |
| name          | varchar(128)      | NO   |     | NULL    |                |
| shortname     | varchar(128)      | NO   |     | NULL    |                |
| type          | varchar(255)      | NO   |     | NULL    |                |
| sigle_bdfi    | varchar(8)        | YES  |     | NULL    |                |
| alt_names     | varchar(512)      | YES  |     | NULL    |                |
| publisher_id  | int unsigned      | YES  | MUL | NULL    |                |
| publisher2_id | int unsigned      | YES  | MUL | NULL    |                |
| publisher3_id | int unsigned      | YES  | MUL | NULL    |                |
| parent_id     | int unsigned      | YES  | MUL | NULL    |                |
| year_start    | int unsigned      | NO   |     | NULL    |                |
| year_end      | int unsigned      | YES  |     | NULL    |                |
| support       | varchar(255)      | NO   |     | NULL    |                |
| format        | varchar(255)      | YES  |     | NULL    |                |
| dimensions    | varchar(10)       | YES  |     | NULL    |                |
| cible         | varchar(255)      | YES  |     | NULL    |                |
| genre         | varchar(255)      | YES  |     | NULL    |                |
| information   | text              | YES  |     | NULL    |                |
| private       | text              | YES  |     | NULL    |                |
| quality       | varchar(255)      | NO   |     | NULL    |                |
| created_at    | timestamp         | YES  |     | NULL    |                |
| updated_at    | timestamp         | YES  |     | NULL    |                |
| created_by    | smallint unsigned | YES  |     | NULL    |                |
| updated_by    | smallint unsigned | YES  |     | NULL    |                |
| deleted_by    | smallint unsigned | YES  |     | NULL    |                |
| deleted_at    | timestamp         | YES  |     | NULL    |                |
+---------------+-------------------+------+-----+---------+----------------+
</code>

## Table des publications

<code>
+----------------------+-------------------+------+-----+---------+----------------+
| Field                | Type              | Null | Key | Default | Extra          |
+----------------------+-------------------+------+-----+---------+----------------+
| id                   | int unsigned      | NO   | PRI | NULL    | auto_increment |
| name                 | varchar(128)      | NO   |     | NULL    |                |
| status               | varchar(255)      | NO   |     | paru    |                |
| cycle                | varchar(128)      | YES  |     | NULL    |                |
| cyclenum             | varchar(10)       | YES  |     | NULL    |                |
| publisher_id         | int unsigned      | YES  | MUL | NULL    |                |
| publisher_name       | varchar(128)      | YES  |     | NULL    |                |
| is_visible           | tinyint(1)        | NO   |     | 1       |                |
| isbn                 | varchar(18)       | YES  |     | NULL    |                |
| cover                | varchar(256)      | YES  |     | NULL    |                |
| illustrators         | varchar(512)      | YES  |     | NULL    |                |
| information          | text              | YES  |     | NULL    |                |
| private              | text              | YES  |     | NULL    |                |
| cover_front          | varchar(64)       | YES  |     | NULL    |                |
| cover_back           | varchar(64)       | YES  |     | NULL    |                |
| cover_spine          | varchar(64)       | YES  |     | NULL    |                |
| withband_front       | varchar(64)       | YES  |     | NULL    |                |
| withband_back        | varchar(64)       | YES  |     | NULL    |                |
| withband_spine       | varchar(64)       | YES  |     | NULL    |                |
| dustjacket_front     | varchar(64)       | YES  |     | NULL    |                |
| dustjacket_back      | varchar(64)       | YES  |     | NULL    |                |
| dustjacket_spine     | varchar(64)       | YES  |     | NULL    |                |
| is_hardcover         | tinyint(1)        | NO   |     | 0       |                |
| has_dustjacket       | tinyint(1)        | NO   |     | 0       |                |
| has_coverflaps       | tinyint(1)        | NO   |     | 0       |                |
| is_verified          | tinyint(1)        | NO   |     | NULL    |                |
| verified_by          | varchar(256)      | YES  |     | NULL    |                |
| dl                   | varchar(10)       | YES  |     | NULL    |                |
| ai                   | varchar(10)       | YES  |     | NULL    |                |
| edition              | varchar(64)       | YES  |     | NULL    |                |
| dimensions           | varchar(10)       | YES  |     | NULL    |                |
| thickness            | varchar(4)        | YES  |     | NULL    |                |
| printer              | varchar(128)      | YES  |     | NULL    |                |
| printed_price        | varchar(32)       | YES  |     | NULL    |                |
| pagination           | varchar(32)       | YES  |     | NULL    |                |
| pages_dpi            | varchar(255)      | YES  |     | NULL    |                |
| pages_dpu            | varchar(255)      | YES  |     | NULL    |                |
| approximate_pages    | varchar(255)      | YES  |     | NULL    |                |
| approximate_parution | varchar(10)       | YES  |     | NULL    |                |
| approximate_price    | varchar(32)       | YES  |     | NULL    |                |
| support              | varchar(255)      | NO   |     | NULL    |                |
| format               | varchar(255)      | YES  |     | NULL    |                |
| type                 | varchar(255)      | NO   |     | NULL    |                |
| is_genre             | varchar(255)      | NO   |     | NULL    |                |
| genre_stat           | varchar(255)      | NO   |     | NULL    |                |
| target_audience      | varchar(255)      | NO   |     | NULL    |                |
| target_age           | varchar(255)      | YES  |     | NULL    |                |
| created_at           | timestamp         | YES  |     | NULL    |                |
| updated_at           | timestamp         | YES  |     | NULL    |                |
| created_by           | smallint unsigned | YES  |     | NULL    |                |
| updated_by           | smallint unsigned | YES  |     | NULL    |                |
| deleted_by           | smallint unsigned | YES  |     | NULL    |                |
| deleted_at           | timestamp         | YES  |     | NULL    |                |
+----------------------+-------------------+------+-----+---------+----------------+
</code>

## Table des retirages

<code>
+----------------------+-------------------+------+-----+---------+----------------+
| Field                | Type              | Null | Key | Default | Extra          |
+----------------------+-------------------+------+-----+---------+----------------+
| id                   | int unsigned      | NO   | PRI | NULL    | auto_increment |
| publication_id       | int unsigned      | YES  | MUL | NULL    |                |
| ai                   | varchar(10)       | YES  |     | NULL    |                |
| approximate_parution | varchar(10)       | YES  |     | NULL    |                |
| is_verified          | tinyint(1)        | NO   |     | NULL    |                |
| verified_by          | varchar(256)      | YES  |     | NULL    |                |
| information          | text              | YES  |     | NULL    |                |
| private              | text              | YES  |     | NULL    |                |
| created_at           | timestamp         | YES  |     | NULL    |                |
| updated_at           | timestamp         | YES  |     | NULL    |                |
| created_by           | smallint unsigned | YES  |     | NULL    |                |
| updated_by           | smallint unsigned | YES  |     | NULL    |                |
| deleted_by           | smallint unsigned | YES  |     | NULL    |                |
| deleted_at           | timestamp         | YES  |     | NULL    |                |
+----------------------+-------------------+------+-----+---------+----------------+
</code>

## Table des rattachements des publications aux collections

<code>
+----------------+-------------------+------+-----+---------+----------------+
| Field          | Type              | Null | Key | Default | Extra          |
+----------------+-------------------+------+-----+---------+----------------+
| id             | int unsigned      | NO   | PRI | NULL    | auto_increment |
| order          | int unsigned      | NO   |     | NULL    |                |
| number         | varchar(16)       | YES  |     | NULL    |                |
| collection_id  | int unsigned      | NO   | MUL | NULL    |                |
| publication_id | int unsigned      | NO   | MUL | NULL    |                |
| created_at     | timestamp         | YES  |     | NULL    |                |
| updated_at     | timestamp         | YES  |     | NULL    |                |
| created_by     | smallint unsigned | YES  |     | NULL    |                |
| updated_by     | smallint unsigned | YES  |     | NULL    |                |
| deleted_by     | smallint unsigned | YES  |     | NULL    |                |
| deleted_at     | timestamp         | YES  |     | NULL    |                |
+----------------+-------------------+------+-----+---------+----------------+
</code>

## Table des rattachements des oeuvres aux auteurs

<code>
+----------------+-------------------+------+-----+---------+----------------+
| Field          | Type              | Null | Key | Default | Extra          |
+----------------+-------------------+------+-----+---------+----------------+
| id             | int unsigned      | NO   | PRI | NULL    | auto_increment |
| role           | varchar(255)      | NO   |     | NULL    |                |
| author_id      | int unsigned      | NO   | MUL | NULL    |                |
| publication_id | int unsigned      | NO   | MUL | NULL    |                |
| created_at     | timestamp         | YES  |     | NULL    |                |
| updated_at     | timestamp         | YES  |     | NULL    |                |
| created_by     | smallint unsigned | YES  |     | NULL    |                |
| updated_by     | smallint unsigned | YES  |     | NULL    |                |
| deleted_by     | smallint unsigned | YES  |     | NULL    |                |
| deleted_at     | timestamp         | YES  |     | NULL    |                |
+----------------+-------------------+------+-----+---------+----------------+
</code>
