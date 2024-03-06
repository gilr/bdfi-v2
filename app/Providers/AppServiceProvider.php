<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Enums\TitleType;
use Illuminate\Support\Number;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Number::useLocale('fr');

        // TODO si on peut trouver mieux (secure ?)
        Model::unguard();

        Collection::macro('Novels', function () {
            // TBD TODO
            // ->where('is_serial', 0)
            // Serial non filtré car ensuite on ne prends que les parents
            return $this->whereIn('type', [TitleType::ROMAN, TitleType::FIXUP, TitleType::NOVELLA])
                        ->sortBy('copyright');
        });
        Collection::macro('Collections', function () {
            return $this->whereIn('type', [TitleType::RECUEIL, TitleType::ANTHO, TitleType::OMNIBUS, TitleType::CHRONIQUES])
                        ->sortBy('copyright');
        });
        Collection::macro('Shorts', function () {
            return $this->whereIn('type', [TitleType::NOUVELLE, TitleType::SHORTSHORT])
                        ->sortBy('copyright');
        });
        Collection::macro('Others', function () {
            return $this->whereIn('type', [TitleType::POEME, TitleType::CHANSON, TitleType::THEATRE, TitleType::SCENARIO, TitleType::RADIO])
                        ->sortBy('copyright');
        });
        Collection::macro('NonFictions', function () {
            return $this->whereIn('type', [TitleType::LETTRE, TitleType::PREFACE, TitleType::POSTFACE, TitleType::BIBLIO, TitleType::BIO, TitleType::ESSAI, TitleType::GUIDE, TitleType::ARTICLE, TitleType::LIVREJEU, TitleType::JEU])
                        ->sortBy('copyright');
        });
    }
}
