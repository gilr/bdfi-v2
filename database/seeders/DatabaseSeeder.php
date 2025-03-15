<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Enums\UserRole;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        DB::table('users')->insert([
            'id'       => '1',
            'name'     => 'legacy1',
            'role'     => UserRole::SYSADMIN->value,
            'email'    => 'legacy1@host.net',
            'password' => Hash::make('password'),
            'format_date'  => 'clair',
            'created_at'   => today(),
            'updated_at'   => today(),
            'deleted_at'   => NULL,
            'created_by'   => 1,
            'updated_by'   => 1,
            'deleted_by'   => NULL
        ]);
        DB::table('users')->insert([
            'id'       => '2',
            'name'     => 'legacy2',
            'role'     => UserRole::SYSADMIN->value,
            'email'    => 'legacy2@host.net',
            'password' => Hash::make('password'),
            'format_date'  => 'clair',
            'created_at'   => today(),
            'updated_at'   => today(),
            'deleted_at'   => NULL,
            'created_by'   => 1,
            'updated_by'   => 1,
            'deleted_by'   => NULL
        ]);
        DB::table('users')->insert([
            'id'       => '3',
            'name'     => 'legacy3',
            'role'     => UserRole::ADMIN->value,
            'email'    => 'legacy3@hort.net',
            'password' => Hash::make('password'),
            'format_date'  => 'clair',
            'created_at'   => today(),
            'updated_at'   => today(),
            'deleted_at'   => NULL,
            'created_by'   => 1,
            'updated_by'   => 1,
            'deleted_by'   => NULL
        ]);
        DB::table('users')->insert([
            'id'       => '99',
            'name'     => 'legacy4',
            'role'     => UserRole::MEMBER->value,
            'email'    => 'legacy4@host.net',
            'password' => Hash::make('password'),
            'format_date'  => 'clair',
            'created_at'   => today(),
            'updated_at'   => today(),
            'deleted_at'   => NULL,
            'created_by'   => 1,
            'updated_by'   => 1,
            'deleted_by'   => NULL
        ]);
        DB::table('users')->insert([
            'id'       => '10',
            'name'     => 'sysadmin',
            'role'     => UserRole::SYSADMIN->value,
            'email'    => 'sysadmin@bdfi.net',
            'password' => Hash::make('password'),
            'format_date'  => 'clair',
            'created_at'   => today(),
            'updated_at'   => today(),
            'deleted_at'   => NULL,
            'created_by'   => 1,
            'updated_by'   => 1,
            'deleted_by'   => NULL
        ]);
        DB::table('users')->insert([
            'id'       => '11',
            'name'     => 'admin',
            'role'     => UserRole::ADMIN->value,
            'email'    => 'admin@bdfi.net',
            'password' => Hash::make('password'),
            'format_date'  => 'clair',
            'created_at'   => today(),
            'updated_at'   => today(),
            'deleted_at'   => NULL,
            'created_by'   => 1,
            'updated_by'   => 1,
            'deleted_by'   => NULL
        ]);
        DB::table('users')->insert([
            'id'       => '12',
            'name'     => 'admin2',
            'role'     => UserRole::ADMIN->value,
            'email'    => 'admin2@bdfi.net',
            'password' => Hash::make('password'),
            'format_date'  => 'abr',
            'created_at'   => today(),
            'updated_at'   => today(),
            'deleted_at'   => NULL,
            'created_by'   => 1,
            'updated_by'   => 1,
            'deleted_by'   => NULL
        ]);
        DB::table('users')->insert([
            'id'       => '13',
            'name'     => 'admin3',
            'role'     => UserRole::ADMIN->value,
            'email'    => 'admin3@bdfi.net',
            'password' => Hash::make('password'),
            'format_date'  => 'fru',
            'created_at'   => today(),
            'updated_at'   => today(),
            'deleted_at'   => NULL,
            'created_by'   => 1,
            'updated_by'   => 1,
            'deleted_by'   => NULL
        ]);
        DB::table('users')->insert([
            'id'       => '14',
            'name'     => 'editor',
            'role'     => UserRole::MEMBER->value,
            'email'    => 'editor@bdfi.net',
            'password' => Hash::make('password'),
            'format_date'  => 'fr',
             'created_at'   => today(),
            'updated_at'   => today(),
            'deleted_at'   => NULL,
            'created_by'   => 1,
            'updated_by'   => 1,
            'deleted_by'   => NULL
        ]);
        DB::table('users')->insert([
            'id'       => '15',
            'name'     => 'editor2',
            'role'     => UserRole::MEMBER->value,
            'email'    => 'editor2@bdfi.net',
            'password' => Hash::make('password'),
            'format_date'  => 'db',
            'created_at'   => today(),
            'updated_at'   => today(),
            'deleted_at'   => NULL,
            'created_by'   => 1,
            'updated_by'   => 1,
            'deleted_by'   => NULL
        ]);
        DB::table('users')->insert([
            'id'       => '16',
            'name'     => 'editor3',
            'role'     => UserRole::MEMBER->value,
            'email'    => 'editor3@bdfi.net',
            'password' => Hash::make('password'),
            'format_date'  => 'clair',
            'created_at'   => today(),
            'updated_at'   => today(),
            'deleted_at'   => NULL,
            'created_by'   => 1,
            'updated_by'   => 1,
            'deleted_by'   => NULL
        ]);
        DB::table('users')->insert([
            'id'       => '17',
            'name'     => 'proposant',
            'role'     => UserRole::PROPONENT->value,
            'email'    => 'proposant@bdfi.net',
            'password' => Hash::make('password'),
            'format_date'  => 'clair',
            'created_at'   => today(),
            'updated_at'   => today(),
            'deleted_at'   => NULL,
            'created_by'   => 1,
            'updated_by'   => 1,
            'deleted_by'   => NULL
        ]);
        DB::table('users')->insert([
            'id'       => '18',
            'name'     => 'visitor',
            'role'     => UserRole::GUEST->value,
            'email'    => 'visitor@bdfi.net',
            'password' => Hash::make('password'),
            'format_date'  => 'clair',
            'created_at'   => today(),
            'updated_at'   => today(),
            'deleted_at'   => NULL,
            'created_by'   => 1,
            'updated_by'   => 1,
            'deleted_by'   => NULL
        ]);
        DB::table('users')->insert([
            'id'       => '19',
            'name'     => 'user',
            'role'     => UserRole::USER->value,
            'email'    => 'user@bdfi.net',
            'password' => Hash::make('password'),
            'format_date'  => 'clair',
            'created_at'   => today(),
            'updated_at'   => today(),
            'deleted_at'   => NULL,
            'created_by'   => 1,
            'updated_by'   => 1,
            'deleted_by'   => NULL
        ]);

        $this->call([
            CountrySeeder::class,
            WebsiteTypeSeeder::class,
            RelationshipTypeSeeder::class,

            AnnouncementSeeder::class,
            EventSeeder::class,
            StatSeeder::class,

            AuthorSeeder::class,

            WebsiteSeeder::class,
            SignatureSeeder::class,
            RelationshipSeeder::class,
/* */
            AwardSeeder::class,
            AwardCategorySeeder::class,
            AwardWinnerSeeder::class,
/*  */
            CycleSeeder::class,

            PublisherSeeder::class,
            CollectionSeeder::class,
            PublicationSeeder::class,
            ReprintSeeder::class,
            CollectionPublicationSeeder::class,
            AuthorPublicationSeeder::class,

            TitleSeeder::class,
            AuthorTitleSeeder::class,

            TableOfContentSeeder::class, // = PublicationTitle

            CycleTitleSeeder::class,

            ArticleSeeder::class,
            DocumentSeeder::class,

            IllustratorSeeder::class,
            TranslatorSeeder::class,

        ]);
    }
}
