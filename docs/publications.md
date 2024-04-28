['Accueil docs'](welcome.md)

# Tables de la zone 'Publications'

## Table des éditeurs

- sigle_bdfi : [TEMPORAIRE] (si pas de collection] - Sigle utilisé actuellement sur BDFI, pour permettre une éventuelle transition
- alt_names : variantes, noms longs, avec prénom, ou par exemple si le nom de l'éditeur à historiquement évolué et qu'il n'y a pas de raisons de scinder en deux
- type : le type d'éditeur ; codé comme  énuméré : 'editeur', 'microediteur', 'autoediteur', 'compte d'auteur', 'autre' - A affiner si besoin
- year_start : l'année de création
- year_end : l'année de dissolution
- address : la dernière localisation connue;  dernière ville/localisation/adresse connue...
- information : Petite description, historique, appartenance à un groupe, autre informations...

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

- name : nom complet collection ou sous-collection ; exemple : "Vertige", "Vertige fantastique", "Super luxe lendemains retrouvés", "Terrific"
- type: le type d'ensemble de publication = énuméré : 'collection', 'ensemble', 'revue', 'fanzine', 'magazine', 'journal', antho-periodique'... - 'collection' par défaut
- alt_names : les autres dénominations, variantes, long noms, nom court - exemple : "Lendemains retrouvés" - Ou si le nom 'officiel' de la même collection a évolué au fil du temps, variantes de présentation, "surnom" officieux.
- sigle_bdfi : [TEMPORAIRE] sigle utilisé actuellement sur BDFI pour permettre une éventuelle transition"
- publisher_id : l'id de l'éditeur (virtuellement non obligatoire, mais accélére les requêtes & affichages)
- publisher2_id : éditeur 2 si besoin
- publisher3_id : éditeur 3 si besoin
- parent id : identifiant de la collection 'mère' dans cette même table - si sous-série, sous-collection, sous-ensemble...
- year_start : année de création
- year_end : année de fin = année du dernier élément de la série/collection
- support : codé en énuméré : 'papier', 'numerique', 'audio', 'mixte', 'autre'
- format : codé en énuméré : 'poche', 'moyen', 'grand', 'mixte'
- les dimensions (dimensions) = texte - dimensions en mm (format 100 x 200)
- cible : le public visé; codé en énuméré : 'jeunesse', 'YA', 'adulte' - A discuter et affiner
- genre : le genre annoncé ou majoritaire = énuméré : 'sf', 'fantasy', 'fantastique', 'gore', 'policier', 'autre', 'N/A'

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
