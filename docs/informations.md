[Accueil documentation](welcome.md)

# Informations techniques

**Attention: la population de la base ne peut pas fonctionner actuellement**

## Installation

 composer install

 php artisan key:generate

 npm install

 npm run dev

## Population de la base

Les données insérées sont en grande partie des données réelles, mais limitées à celles qui ont subies ont longue étape de vérification (soit environ 10% à 15% des données existantes du site actuel), ainsi que des données de test (les comptes user et admin, et une poignée d'ouvrages "à paraître").

**!!! Actuellement, la population ne peut pas fonctionner en dehors de mon propre poste local, car elle se base partiellement sur un import depuis une autre base.**
Pour fonctionner, l'étape 1 ci-dessous doit d'abord être transformée en import de données sans passer par une base annexe.

Cette population s'effectue suivant trois techniques distinctes :
 1. Via le seeder, depuis des tables d'une base existante (les tables auteurs et tables communes)
 2. Via le seeded, depuis des tables json générées localement et stockées dans le répertoire storage
 3. Puis en dernier lieu via une commande (BdfiAddVariantsCommand) d'ajout des variantes de titres, qui elle-même utilise un fichier json généré localement et également archivé dans le répertoire storage


php artisan migrate:refresh --seed

php artisan make:command BdfiAddVariantsCommand

