


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