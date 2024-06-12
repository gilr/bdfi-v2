<p align="center">
<h1>BDFI v2</h1>
</p>

* [Français](#french)
* [English](#english)

<a name="french"></a>
## A propos

Développement d'une nouvelle version d'un site web dédié à l'imaginaire (science-fiction, fantastique, fantasy...)
Le projet est développé sans support réel du multi-langue, sa vocation première atant d'être un site web francophone.
Le site web est un site associatif, non commercial, sans publicité.

Etant donné sa cible très spécifique, il n'est pas d'un grand intérêt pour des personnes hors de notre équipe, sauf éventuellement comme exemple d'un projet Laravel.

## Aide ?
Toute aide de personnes familières de l'éco-système Laravel et/ou passionnées par les littératures de l'imaginaire est la bienvenue !

## Documentation projet (en cours)

[Sommaire](docs/welcome.md)
[Informations](docs/informations.md)
[Tables de la zone 'Auteurs'](docs/auteurs.md)
[Tables de la zone 'Publications'](docs/publications.md)
[Tables de la zone 'Prix'](docs/prix.md)
[Tables de la zone 'Oeuvres'](docs/oeuvres.md)
[Tables de la zone 'Site'](docs/site.md)
[Tables communes](docs/communs.md)

## Basé sur Laravel

Laravel est un framework de développement web doté d'une syntaxe explicite et élégante. Laravel aide le développement en facilitant la plupart des tâches de base des projets web, tels que :

- [moteur de routage simple et rapide](https://laravel.com/docs/routing).
- [service de gestion d'injection de dépendances](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- [ORM](https://laravel.com/docs/eloquent) - mapping objet-relationnel - intuitif et expressif.
- Multi-format de base de données via les [schémas de migrations](https://laravel.com/docs/migrations).
- Gestion robuste [des tâches de fond](https://laravel.com/docs/queues).
- [Diffusion d'événements temps réel](https://laravel.com/docs/broadcasting).

Laravel est accessible, puissant, et fournit les outils nécessaires pour des applications robustes et performantes.

## ... Et Jetstream

Laravel Jetstream est un starter kit de Laravel. Outre qu'il offre de base l'ensemble des IHM de connection, inscription, vérification d'em-ail, authentication double facteur et la gestion des sessions (via [Laravel Sanctum](https://github.com/laravel/sanctum), il est également le point de départ des IHM d'administrations maison.

Le choix du stack sous-jacent a été [Livewire](https://jetstream.laravel.com/1.x/stacks/livewire.html), basé sur les mêmes template Blade que Laravel (au contraire de Inertia, basé sur jvue).

## ... Et Tailwind

[Tailwind CSS](https://tailwindcss.com) est un framework CSS moderne qui va à l'encontre totale des anciens principes et usages relatifs aux design de pages web (le "separation of concerns", la stricte séparation entre code HTML et feuilles de styles). Facile à utiliser, ultra-souple pour la mise au point, il se base sur l'idée que si réutilisation il doit y avoir, c'est la réutilisation de composants IHM communs, pas d'innombrables définitions de styles très ciblés.

## Packages et repositories utilisées

- **[debugbar](https://github.com/barryvdh/laravel-debugbar)** (outils de débug orienté PHP + Laravel)
- **[revisionable](https://packagist.org/packages/venturecraft/revisionable)** (extension permettant de stocker les modifications des tables)
- **[Laravel-Userstamps](https://github.com/WildsideUK/Laravel-Userstamps)** (extension qui ajoute les informations "créé par" et "mis à jour par", en plus des informations de base "créé le" et "mis à jour le")
- **[filament](https://filamentphp.com//)** (administration)

## Failles de sécurité

En cas de découverte de faille de sécurité, merci d'envoyer un e-mail au propriétaire github de ce projet.

## License

Le projet est totalement open-source.

<a name="english"></a>
## About

Development of the new version of a website dedicated to speculative fiction. 
The project won't be developed as a multi-language project, as its primary aim is to be a french-speaking website.
This website will be a non-commercial and non-profit project, without any advertising.

As it is developed for a specific scope, it should not be of great interests for people outside our small team, except maybe as an example of a laravel project.

## Help?
Any help or advice from people familiar with Laravel ecosystem and/or people who love the speculative fiction is welcome!

## Project documentation (in progress - in French)

[Content](docs/welcome.md)
[Information](docs/informations.md)
[Tables of the 'Authors' area](docs/auteurs.md)
[Tables of the 'Publications' area](docs/publications.md)
[Tables of the 'Awards' area](docs/prix.md)
[Tables of the 'Texts' area](docs/oeuvres.md)
[Tables of the 'Site' area](docs/site.md)
[Common tables](docs/communs.md)

## Based on Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## ... And Jetstream

Laravel Jetstream is a beautifully designed application scaffolding for Laravel. Jetstream provides the perfect starting point for your next Laravel application and includes login, registration, email verification, two-factor authentication, session management, API support via [Laravel Sanctum](https://github.com/laravel/sanctum), and optional team management.

The chosen scaffolding stack is [Livewire](https://jetstream.laravel.com/1.x/stacks/livewire.html), which is based on the Laravel Blade templates, rather than Inertia, based on jvue.

## ... And Tailwind
[Tailwind CSS](https://tailwindcss.com) is a modern CSS framework that goes against the old principles and rules for the definition of the style sheets of the web sites. Easy to understand, and of a great help for the development, it aim is to move towards the definition of some common HMI components, and never the definition of numerous styles classes as it was nearly recommended by the "separation of concerns" approach.

## Used packages & repositories

- **[debugbar](https://github.com/barryvdh/laravel-debugbar)**
- **[revisionable](https://packagist.org/packages/venturecraft/revisionable)**
- **[Laravel-Userstamps](https://github.com/WildsideUK/Laravel-Userstamps)**
- **[filament](https://filamentphp.com//)** (administration)

## Security Vulnerabilities

If you discover a security vulnerability within our project, please send an e-mail to the owner of the repository.

## License

This project is an open-sourced software.
