<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superAdmin = \App\Models\User::firstOrCreate(
            ['email' => 'dev@ninjasage.id'],
            [
                'name' => 'Ninja Sage',
                'username' => 'ninjasage',
                'password' => \Illuminate\Support\Facades\Hash::make('ijinmasuk'),
                'role' => 'super_admin',
            ]
        );

        $gameManager = \App\Models\User::firstOrCreate(
            ['email' => 'bot@ninjasage.id'],
            [
                'name' => 'Pet Bot Manager',
                'username' => 'petbot',
                'password' => \Illuminate\Support\Facades\Hash::make('ijinmasuk'),
                'role' => 'game_manager',
            ]
        );

        // Assign game_manager to Pet Bot if the game exists
        $petBotGame = \App\Models\Game::where('slug', 'pet-bot')->first();
        if ($petBotGame) {
            $gameManager->games()->syncWithoutDetaching([$petBotGame->id]);
        }
    }
}
