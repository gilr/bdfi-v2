[Accueil documentation](welcome.md)

#Tables communes

## Table des articles

La table Articles stocke comme son nom l'indique les articles, qui sont des contenus (au format HTML) qui seront affichés pour un élément donné de la base. Aujourd'hui utilisé pour les collections et prévu pour les éditeurs, pourrait être étendu à n'importe quel autre type dans le futur.

- item_type : stocke le modèle associé au type d'élément auquel est associé l'article ('App\Models\Collection')
- item_id : l'id de l'élément auquel l'article est associé
- content : le contenu de l'article lui même, au format HTML

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

La table Documents stocke comme son nom l'indique des documents qui doivent être accessible à partir d'un élément donné de la base. Aujourd'hui utilisé uniquement pour les auteurs (bibliographie ou biographie), le format est prévu pour pouvoir être étendu dans le futur à n'importe quel autre type dans le futur.

- name : titre du document, par exemple 'Bibliographie de Jack Vance, par Manu Macron')
- file : le nom du fichier uploadé
- author_id : Si l'auteur du document est référencé en base, son ID.
- item_type : stocke le modèle associé au type d'élément auquel est associé le document ('App\Models\Author' s'il s'agit d'une bibliographie)
- item_id : l'id de l'élément auquel le document est associé

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


## Table des pays

La table des pays du monde.

- name : le nom du pays
- nationality : le nom du fichier uploadé
- code : le nom du fichier uploadé
- internal_order : l'ordre d'affichage interne. Permet d'afficher d'abord les pays principaux, qu'ils soient de langue française (France, Québec) ou pourvoyeur de nombre d'euvres traduites (USA, GB)

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

- name : le nom descriptif du type de site
- information : complément d'information sur le type de site
- displayed_text : le texte a afficher pour ce type de site

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

Table stockant les relations entre auteurs

- name : le nom descriptif de la relation
- relationship : le texte à afficher pour la relation
- eeverse_relationship : le texte à afficher pour la relation inverse

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


