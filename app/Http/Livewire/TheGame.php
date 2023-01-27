<?php

namespace App\Http\Livewire;

use App\Models\Game;
use App\Models\Guess;
use App\Models\Point;
use App\Models\User;
use App\Models\Word;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TheGame extends Component
{
    public $length;
    public $gameId;
    public $word;
    public $opponentName;

    public $guessesCount;
    public $guessesArray;

    protected $listeners = ['loser', 'winner'];

    public function loser()
    {
        $game = Game::whereId($this->gameId)->first();
        $game->winner_id = $game->user_id;
        $game->degree = (9 - $game->length);
        $game->save();

        $point = Point::whereUser_id($game->user_id);
        if($point->exists())
        {
            $point = $point->first();
            $previous = $point->point;
            $new = $game->degree;
            $total = $previous + $new;
            $point->point = $total;
            $point->save();
        }
        else {
            $point = new Point;
            $point->user_id = $game->user_id;
            $point->point = $game->degree;;
            $point->save();

        }
        return redirect('/the-game/'.$this->gameId);
    }


    public function winner()
    {
        $game = Game::whereId($this->gameId)->first();
        $game->winner_id = $game->opponent_id;
        $game->degree = ($game->length - Guess::whereGame_id($this->gameId)->count() + 1) * 5;
        $game->save();

        $point = Point::whereUser_id($game->winner_id);
        if($point->exists()){
            $point = $point->first();
            $previous = $point->point;
            $new = $game->degree;
            $total = $previous + $new;
            $point->point = $total;
            $point->save();
        }
        else {
            $point = new Point;
            $point->user_id = $game->winner_id;
            $point->point = $game->degree;;
            $point->save();
        }
        return redirect('/the-game/'.$this->gameId);
    }

    public function mount($gameId)
    {
        $game = Game::whereId($gameId)->where('opponent_id', Auth::id());
        if ($game->exists()) {
            $game = $game->first();
            $guesses = $game->guesses()->get();
            foreach ($guesses as $guess) {
                $this->guessesArray[] = $guess->word->name;
            }
            $this->guessesCount = $guesses->count();

            $this->gameId = $gameId;
            $this->length = $game->length;
            $this->opponentName = User::find($game->user_id)->name;
        } else {
            session()->flash('message', 'Bu oyunu görme yetkiniz yok.');
            return redirect()->to('/create-game');
        }

    }

    public function render()
    {
        $game = Game::find($this->gameId);
        $game->seen = 1;
        $game->save();
        return view('livewire.the-game');
    }
}
