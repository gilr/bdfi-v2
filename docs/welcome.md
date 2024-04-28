

# Description des tables de la base de données

- Les tables des la zone ['Auteurs'](auteurs.md)
- Les tables des la zone ['publications'](publications.md)
- Les tables des la zone ['prix'](prix.md)
- Les tables des la zone ['oeuvres'](oeuvres.md)
- Les tables des la zone ['site'](site.md)
- Les tables [communes](communs.md)

Les informations communes à plusieurs tables sont décrites plus bas.

# Les valeurs énumérées

(à venir)

# Informations communes
Ci-dessous la liste des principaux champs communs à beaucoup ou plusieurs tables. Ces champs seront listés dans les pages par zone mais  non décrits; des exemples peuvent pas contre être donnés.

- id : évidemment commun à toutes les tables sans exception, c'est l'identifiant unique dans une table
- country_id : l'identifiant du pays, lorsque nécessaire (auteur, prix, éditeur...)
- name : le nom principal de l'élément dans une table
- alt_names : la liste des possibles autre noms, ou noms alternatifs, lorsque nécessaire. Ceux-ci permettent entre-autres les recherches "élargies"
- url : lorsque utile, le lien sur un site web officiel ou informatif
- information : le champ qui permet la description de l'élément, et qui sera affiché sur sa page
- private : le champ qui permet le stockage d'informations internes ou de travail, qui ne seront visibles que par les membres
- quality : [A REVOIR] indicateur si reste du travail sur cette fiche série
- created_at : date et heure de création
- updated_at : date et heure de dernière mise à jour
- created_by : id de l'utilisateur qui a créé l'élément
- updated_by : id du dernier utilisateur a mettre à jour l'élément
- deleted_by : id de l'utilisateur ayant supprimé l'élément
- deleted_at : date et heure de suppression (soft-delete)

<code>
+--------------+-------------------+------+-----+---------+----------------+
| Field        | Type              | Null | Key | Default | Extra          |
+--------------+-------------------+------+-----+---------+----------------+
| id           | int unsigned      | NO   | PRI | NULL    | auto_increment |
| name         | varchar(128)      | NO   |     | NULL    |                |
| country_id   | smallint unsigned | YES  | MUL | NULL    |                |
| alt_names    | varchar(512)      | YES  |     | NULL    |                |
| url          | varchar(256)      | YES  |     | NULL    |                |
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