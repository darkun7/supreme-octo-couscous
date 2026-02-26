<?php

use App\Models\GameCollection;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/switch-game/{id}', [AuthController::class, 'switchGame'])->name('switch-game')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    // Helper to get active game id
    $getActiveGameId = function () {
        $user = auth()->user();
        if (!$user) return null;
        $activeId = session('active_game_id');
        $games = $user->role === 'super_admin' ? \App\Models\Game::all() : $user->games;
        return $activeId ?? optional($games->first())->id;
    };

    // Dashboard
    Route::get('/', function () {
        return view('pages.dashboard');
    })->name('dashboard');

    // Collections Management
    Route::get('/collections', function () {
        return view('pages.collections');
    })->name('collections.index');

    Route::get('/collections/{slug}/fields', function ($slug) use ($getActiveGameId) {
        $collection = GameCollection::where('game_id', $getActiveGameId())->where('slug', $slug)->firstOrFail();
        return view('pages.collection-fields', compact('collection'));
    })->name('collections.fields');

    // Entries
    Route::get('/entries/{slug}', function ($slug) use ($getActiveGameId) {
        $collection = GameCollection::where('game_id', $getActiveGameId())->where('slug', $slug)->firstOrFail();
        // Redirect static collections to the static JSON editor
        if ($collection->type === 'static') {
            return redirect()->route('static.editor', $slug);
        }
        return view('pages.entries', compact('collection'));
    })->name('entries.index');

    // Static JSON Editor
    Route::get('/static/{slug}', function ($slug) use ($getActiveGameId) {
        GameCollection::where('game_id', $getActiveGameId())->where('slug', $slug)->where('type', 'static')->firstOrFail();
        return view('pages.static-editor', compact('slug'));
    })->name('static.editor');

    Route::get('/entries/{slug}/new', function ($slug) use ($getActiveGameId) {
        $collection = GameCollection::where('game_id', $getActiveGameId())->where('slug', $slug)->firstOrFail();
        $entryId = 'new';
        return view('pages.entry-editor', compact('collection', 'entryId'));
    })->name('entries.create');

    Route::get('/entries/{slug}/spreadsheet', function ($slug) use ($getActiveGameId) {
        // Ensuring collection exists for active game before rendering spreadsheet
        GameCollection::where('game_id', $getActiveGameId())->where('slug', $slug)->firstOrFail();
        return view('pages.spreadsheet', compact('slug'));
    })->name('entries.spreadsheet');

    Route::get('/entries/{slug}/{entry}', function ($slug, $entry) use ($getActiveGameId) {
        $collection = GameCollection::where('game_id', $getActiveGameId())->where('slug', $slug)->firstOrFail();
        $entryId = $entry;
        return view('pages.entry-editor', compact('collection', 'entryId'));
    })->name('entries.edit');
    // System Settings (Super Admin / Game Manager)
    Route::get('/settings/users', function () {
        return view('pages.users');
    })->name('settings.users');

    Route::get('/settings/games', function () {
        return view('pages.games');
    })->name('settings.games');
});
