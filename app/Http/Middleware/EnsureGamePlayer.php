<?php
namespace App\Http\Middleware;use Closure;use Illuminate\Http\Request;use App\Models\GameRoom;use Illuminate\Support\Facades\Auth;
class EnsureGamePlayer{public function handle(Request $r,Closure $next){$room=$r->route('room');if(!$room instanceof GameRoom)$room=GameRoom::findOrFail($room);if(!$room->players()->where('user_id',Auth::id())->exists())abort(403);return $next($r);}}

