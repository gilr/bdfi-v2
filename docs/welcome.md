# Documentation

## Sommaire
La documentation, encore incomplète, contient actuellement quelques informations techniques, suivies de la description des tables de la base de données.

- [Informations techniques et installation](informations.md)
- Les [tables de la zone 'auteurs'](auteurs.md)
- Les [tables de la zone 'publications'](publications.md)
- Les [tables de la zone 'prix'](prix.md)
- Les [tables de la zone 'oeuvres'](oeuvres.md)
- Les [tables de la zone 'site'](site.md)
- Les [tables communes](communs.md)

Les informations communes à plusieurs tables sont décrites plus bas.

## Les valeurs énumérées

(à venir)

## Champs communs à de multiples tables

Ci-dessous la liste des principaux champs communs à plusieurs tables. Ces champs seront listés dans les pages par zone mais sans decription additionnelle; des exemples peuvent pas contre être donnés.

- id : évidemment commun à toutes les tables sans exception, c'est l'identifiant unique dans une table
- country_id : l'identifiant du pays, lorsque nécessaire (auteur, prix, éditeur...)
- name : le nom principal de l'élément dans une table
- alt_names : la liste des possibles autre noms, ou noms alternatifs, lorsque nécessaire. Ceux-ci permettent par exemple les recherches "élargies"
- url : lorsque utile, le lien sur un site web officiel ou informatif
- information : le champ qui permet la description de l'élément, description qui sera affichée sur sa page
- private : le champ qui permet le stockage d'informations internes ou de travail, qui ne seront visibles que par les membres
- quality : [A REVOIR] indique le niveau de qualité (i.e. s'il reste du travail sur cette fiche)
- created_at : date et heure de création de l'élément
- created_by : id de l'utilisateur qui a créé l'élément
- updated_at : date et heure de dernière mise à jour
- updated_by : id du dernier utilisateur a mettre à jour l'élément
- deleted_at : date et heure de suppression (soft-delete)
- deleted_by : id de l'utilisateur ayant supprimé l'élément

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
| created_by   | smallint unsigned | YES  |     | NULL    |                |
| updated_at   | timestamp         | YES  |     | NULL    |                |
| updated_by   | smallint unsigned | YES  |     | NULL    |                |
| deleted_at   | timestamp         | YES  |     | NULL    |                |
| deleted_by   | smallint unsigned | YES  |     | NULL    |                |
+--------------+-------------------+------+-----+---------+----------------+
</code>