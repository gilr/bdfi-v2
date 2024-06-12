[Accueil documentation](welcome.md)

# Tables de la zone 'Oeuvres'

## Table des oeuvres

Une "oeuvre" n'est pas un "ouvrage" (publication). L'ouvrage est l'objet physique (ou numérique) vendu. Une oeuvre est un contenu. Ce contenu peut être  fictionnel (roman, nouvelle), ou non (essai, article, paratexte). Le contenu complet d'un ouvrage (par exemple un recueil) est aussi stocké comme "oeuvre", car il peut être repris dans de multiples ouvrages.

- name : Le titre indiqué en page de titre de l'ouvrage (pas en couverture)
- TBD ajouter alt-names pour éventuel titre en couv ?
- type : énuméré : roman, novella, nouvelle, preface, postface, poeme, chanson, biblio, bio, essai, article, partie/groupe de textes
- parent_id : premier texte en français, identifiant dans cette même table [Nécessite informations complémentaire surtout pour cas particuliers]
- copyright : la première date de publication
- is_novelization : flag novelisation
- is_visible : flag affichage = permet de préciser si ce texte ne doit pas être affiché - hors genre stocké en base par exemple
- is_nongenre : flag précisant si hors genre - Distinct du précédent pour pouvoir exceptionnellement de rendre affichage un texte hors genre
- title_vo : titre VO si traduit
- copyright_vo : date VO
- translators : les traducteurs = une liste "nom prénom, nom prénom, etc..." [A REVOIR] (pour gérer en base)
- synopsis : pitch de l'histoire
- information : information sur le texte (pas le synopsis, mais relativement à son historique - écrit bien avant par exemple, ou anecdote)

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

Le choix effectué est de n'utiliser qu'une seule table, en partant du principe qu'une série peut avoir plusieurs séries filles (sous-séries), mais ne pourra jamais avoir plusieurs séries "parentes". Donc un identifiant "parent" unique au sein de la table est suffisant. De la même façon, les titres alternatifs et VO ne sont stockés là que pour fournir de l'information complémentaire et permettre des recherches plus larges. Pas besoin donc de table annexe pour stocker ces "titres alternatifs".

- name : nom le plus habituel - exemple : "La Guerre éternelle", "Luna"
- nom_bdfi : [TEMPORAIRE] nom actuel site BDFI pour permettre la transition (exemple pour un "Luna" ci-dessus : "Luna (Ian McDonald)")
- alt_names : autres noms usuels ou admis ; exemples pour "Doc Savage" : "Franck Sauvage; Franck Sauvage, l'homme miracle"
- vo_names : le nom ou les noms en VO - au même format : "Titre VO 1; Titre VO 2; Titre VO 3")
- type : [A RETRAVAILLER/REDISCUTER] le type de série; codé sous forme d'énuméré : 'serie', 'cycle', 'univers', 'feuilleton', 'autre' - Serie & cycle seront sans doute traités pareil, ou unifiés...
- parent_id : la série parente, si existe = identifiant dans la même table de la série/cycle parent - Une série ne peut avoir qu'une seule série parente (à noter : un ouvrage, lui, pourra appartenir à plusieurs séries)

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

(à compléter)

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

(à compléter)

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

