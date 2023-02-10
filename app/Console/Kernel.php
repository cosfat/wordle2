<?php

namespace App\Console;

use App\Models\Challenge;
use App\Models\Game;
use App\Models\Today;
use App\Models\User;
use App\Models\Word;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Her hafta puanları sıfırla
        $schedule->call(function () {
            DB::table('points')->delete();
        })->weeklyOn(1, '08:00');

        // Todays Word
        $schedule->call(function () {
            $array = [4, 5, 6, 7];
            $length =  array_rand(array_flip($array), 1);
            $suggestQuery = DB::select(DB::raw("SELECT id, name, CHAR_LENGTH(name) AS 'chrlen' FROM words WHERE CHAR_LENGTH(name) = $length ORDER BY RAND() LIMIT 10"));
            foreach ($suggestQuery as $item) {
                if (Word::tdk($item->name)) {
                    $wordId = $item->id;
                    $today = new Today();
                    $today->word_id = $wordId;
                    $today->save();
                    break;
                }
            }

            $users = User::all();
            foreach ($users as $user) {
                $game = new Game();
                $game->opponent_id = $user->id;
                $game->word_id = $wordId;
                $game->today_id = $today->id;
                $game->user_id = 2;
                $game->length = $length;
                $game->save();
            }

            $exgames = Game::where('today_id', '!=', $today->id)->get();
            foreach ($exgames as $exgame) {
                $exgame->guesses()->delete();
                $exgame->delete();
            }
        })->everyFourHours();

        // 1 haftadır galibi olmayan klasik oyunları sil
        $schedule->call(function () {
            $games = Game::where('created_at', '<', Carbon::now()->subWeek())
                ->whereNull('winner_id')->get();
            foreach ($games as $game) {
                $game->chats()->where('game_type', 1)->delete();
                $game->delete();
            }
        })->hourly();

        // 1 haftadır galibi olmayan Rekabet oyunlarını sil
        $schedule->call(function () {
            $challenges = Challenge::where('created_at', '<', Carbon::now()->subWeek())->whereNull('winner_id')->get();
            foreach ($challenges as $challenge) {
                $challenge->chats()->where('game_type', 2)->delete();
                $challenge->chusers()->delete();
                $challenge->delete();
            }
        })->hourly();


        // 1 gündür tahmini olmayan klasik oyunları sil
        $schedule->call(function () {
            $games = Game::where('created_at', '<', Carbon::now()->subDay())
                ->where('guesscount', 0)->get();
            foreach ($games as $game) {
                $game->chats()->where('game_type', 1)->delete();
                $game->delete();
            }
        })->hourly();

        // 1 gündür tahmini olmayan Rekabet oyunlarını sil
        $schedule->call(function () {
            $challenges = Challenge::where('guesscount', 0)->where('created_at', '<', Carbon::now()->subDay())->get();
            foreach ($challenges as $challenge) {
                $challenge->chats()->where('game_type', 2)->delete();
                $challenge->chusers()->delete();
                $challenge->delete();
            }
        })->
        hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
