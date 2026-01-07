<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{
    /**
     * 友達の表示名を更新
     */
    public function updateDisplayName(Request $request, $friendId)
    {
        $request->validate([
            'display_name' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        
        // 友達関係が存在することを確認
        $friendExists = DB::table('friends')
            ->where('user_id', $user->id)
            ->where('friend_id', $friendId)
            ->exists();

        if (!$friendExists) {
            abort(403, 'このユーザーは友達ではありません');
        }

        // 表示名を更新
        DB::table('friends')
            ->where('user_id', $user->id)
            ->where('friend_id', $friendId)
            ->update(['display_name' => $request->input('display_name')]);

        return response()->json([
            'success' => true,
            'display_name' => $request->input('display_name'),
        ]);
    }
}
