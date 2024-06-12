[Accueil documentation](welcome.md)

# Tables de la zone 'Prix'

Les récompenses sont stockées en utilisant 3 tables. La table des "prix" (générique, le nom attribué par l'organisme qui décerne un ensemble de récompenses sous ce nom), la table des catégories (pour un prix encore actif, on va donc y trouver autant d'entrée que de récompenses décernées une année donnée) et enfin la table des récompenses/lauréats, qui indique l'année et les autres infos décrivant ce qui est récompensé (que ce soit un ouvrage ou un auteur).

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

A noter qu'il n'y a pas de catégories génériques (par exemple "meilleur roman" ou "meilleur roman d'horreur") : chaque catégorie d'un prix donné a une entrée catégorie distinctes dans la table catégories (on peut donc y trouver plusieurs "Meilleur roman de fantasy" par exemple, un par prix).

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

Le titre peut-être indiqué en double dans la table des récompenses ("awards"), via son nom en clair, et via un lien vers ce titre (title_award). Celà permettra de lister des œuvres qui ne sont pas en base soit parce qu'elle n'est pas traduite, soit parce qu'il s'agit d'une oeuvre hors-genres).

Idem pour les auteurs, qui sont à la fois dans le champ name et possiblement stockés dans les liens auteurs (1, 2, et 3); Pour la même raison, un auteur jamais traduit n'est pas dans notre base. Trois liens, ce n'est pas top, il aurait mieux valu une table annexe (id, lien vers le lauréat, lien vers l'auteur) qui permet les relations N to M . C'est historique, et non bloquant pour l'instant. A voir plus tard si le besoin s'en fait sentir.

- year : l'année de récompense
- award_category_id : l'id de la catégorie de prix (table ci-dessus); donne donc indirectement le prix (première table)
- position : la position =  1 si gagnant, 50 si mention spéciale; 99 si pas de récompense cette année-là; 2 à N si nominés
- name : le nom du ou des auteurs récompensés (format "auteur1+auteur2")
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

