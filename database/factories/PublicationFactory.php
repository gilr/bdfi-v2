<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\GenreStat;
use App\Enums\PublicationSupport;
use App\Enums\PublicationContent;
use App\Enums\PublicationFormat;
use App\Enums\GenreAppartenance;
use App\Enums\AudienceTarget;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Publication>
 */
class PublicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'status' => 'annonce',
            'name' => $this->faker->sentence(4),
            'type' => $this->faker->randomElement(PublicationContent::class),
            'support' => $this->faker->randomElement(PublicationSupport::class),
            'approximate_parution' => "2024-" . $this->faker->randomElement(['05', '06', '07', '08', '09', '10', '11', '12']) . "-00",
            'is_genre' => $this->faker->randomElement(GenreAppartenance::class),
            'genre_stat' => $this->faker->randomElement(GenreStat::class),
            'target_audience' => $this->faker->randomElement(AudienceTarget::class),
            'isbn' => $this->faker->isbn13(),
            'is_verified' => false,
            'private' => $this->faker->text(),
            'format' => PublicationFormat::INCONNU->value,

            'created_at'   => today(),
            'updated_at'   => today(),
            'deleted_at'   => NULL,

            'created_by'   => 1,
            'updated_by'   => 1,
            'deleted_by'   => NULL
        ];
    }
}
