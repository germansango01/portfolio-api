<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Asegura que el cliente de acceso personal esté disponible
        if (DB::table('oauth_clients')->count() === 0)
        {
            DB::table('oauth_clients')->insert([
                'id' => '0198ca0b-8f4a-7184-8b81-cdfae97d410d',
                'name' => 'Laravel',
                'secret' => '$2y$12$jAsBMWa2FME7HUn4adu5AOLJpJl0lYr.Zcl3nMs12VcgwsRrbG7YS',
                'provider' => 'users',
                'redirect_uris' => '[]',
                'grant_types' => '["personal_access"]',
                'revoked' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

    }
}
