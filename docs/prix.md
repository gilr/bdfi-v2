[Accueil documentation](welcome.md)

# Tables de la zone 'Prix'

## Table des prix

- name : exemples : "Nebula", "Rosny-ainé"
- alt_names : exemples pour le "Grand Prix de l'Imaginaire" : [ Grand Prix de la SF Française; GPI; GPSFF ]
- year_start : l'année de création du prix
- year_end : l'année de fin du prix
- given_for : le pourquoi du prix; indique ce qu'il récompense, les genres et/ou longueurs et/ou types et/ou spécificités...

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

- name : nom complet de la catégorie - exemples : "Nouvelle de science-fiction", "Anthologie de fantasy"
- award_id = l'identifiant dans la table des prix ci-dessus (une catégorie n'appartient qu'à un seul prix)
- internal_order : l'ordre d'affichage ; permet le tri à l'affichage des catégories d'un même prix
- type : le type de la catégorie; codé comme un énuméré : 'auteur', 'roman', 'novella', 'nouvelle', 'texte', 'anthologie', 'recueil', 'special'
- genre : Le genre de la catégorie; codé comme un énuméré : 'sf', 'fantastique', 'fantasy', 'horreur', 'imaginaire', 'mainstream'
- subgenre : texte libre pour genre plus ciblé si besoin de plus de précision ; exemple : "Uchronie"

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

## Table des lauréats

** Note : ne contiendra dans un premier temps que les gagnants, mais prévu pour pouvoir évoluer pour stocker également tous les nominés **

- year : l'année de récompense
- award_category_id : l'id de la catégorie de prix (table ci-dessus); donne donc indirectement le prix (première table)
- position : la position =  1 si gagnant, 50 si mention spéciale; 99 si pas de récompense cette année-là; 2 à N si nominés
- name : le nom du ou des auteurs récompensés
- author_id : l'identifiant de l'auteur dans la table des auteurs
- author2_id : l'identifiant d'un second auteur dans la table des auteurs
- author3_id : l'identifiant d'un troisième auteur dans la table des auteurs
- title : le titre de l'oeuvre récompensée (vide si auteur, ou si non attribué)
- vo_title : titre original de l'oeuvre
- title_id : FUTUR - l'identifiant sur le titre fr dans la table des oeuvres

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

