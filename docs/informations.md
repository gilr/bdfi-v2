[Accueil documentation](welcome.md)

**Attention: la population de la base ne peut pas fonctionner actuellement** (voir explications plus bas)

# Informations techniques

Le site se compose :
 - d'une partie site web public fondée sur le framework Laravel et le framework CSS Tailwind
 - d'une gestion de compte utilisateur basée sur Livewire
 - d'un premier niveau d'administation basé sur Livewire
 - d'un second niveau d'administration "tables" basé sur le Panel Builder Filament

 Le site web proprement dit contiendra une dizaine de zones construites sur un modèle commun ou similaire (auteurs, ouvrages, retirages, textes, cycles, éditeurs, collections, salons, et prix dans une moindre mesure), une zone programmes, et deux zones plus informatives ('site' et 'chiffres'). Le forum restera une entité à part. Le site se veut responsive (y compris la gestion du menu), et peut légérement se personnaliser grâce au compte utilisateur (menu avec ou sans icones, nombre de résultats par page, format des dates).

## Installation

 composer install

 php artisan key:generate

 npm install

 npm run dev

## Population de la base

Les données insérées sont en grande partie des données réelles, mais limitées à celles qui ont subi ont longue étape de vérification (soit environ 10% à 15% des données existantes du site actuel), ainsi que des données de test (les comptes user et admin, et une poignée d'ouvrages "à paraître").

**!!! Actuellement, le seed ne peut pas fonctionner en dehors de mon propre poste local, car l'import se base partiellement sur une connexion avec une autre base de données.**
Pour fonctionner après un clone, l'étape 1 ci-dessous devra d'abord être transformée en import de données sans utilisation directe de la base pré-existante.

Cette population s'effectue aujourd'hui selon trois techniques distinctes :
 1. Via le seeder, depuis des tables d'une autre base existante (les tables auteurs et tables communes)
 2. Via le seeder, depuis des tables json générées localement et stockées dans le répertoire storage/app
 3. Puis en dernier lieu via une commande (BdfiAddVariantsCommand) d'ajout des variantes de titres, qui elle-même utilise un fichier json généré localement et également archivé dans le répertoire storage/app


php artisan migrate:refresh --seed

php artisan make:command BdfiAddVariantsCommand

