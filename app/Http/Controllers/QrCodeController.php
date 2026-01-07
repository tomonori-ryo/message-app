<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    /**
     * 自分のQRコードを表示
     */
    public function show()
    {
        $user = Auth::user();
        $qrData = route('friends.add-by-qr', ['user_id' => $user->id]);
        
        return view('qr.show', [
            'user' => $user,
            'qrData' => $qrData,
        ]);
    }

    /**
     * QRコードをスキャンして友達を追加
     */
    public function scan()
    {
        return view('qr.scan');
    }

    /**
     * QRコードから友達を追加
     */
    public function addByQr(Request $request, $userId)
    {
        $currentUser = Auth::user();
        
        // 自分自身を追加しようとしている場合は拒否
        if ($currentUser->id == $userId) {
            return redirect()->route('dashboard')->with('error', '自分自身を友達に追加することはできません');
        }
        
        // ユーザーが存在するか確認
        $friend = \App\Models\User::findOrFail($userId);
        
        // 既に友達かどうか確認
        $isAlreadyFriend = $currentUser->friends()->where('friend_id', $userId)->exists();
        
        if ($isAlreadyFriend) {
            return redirect()->route('dashboard')->with('info', '既に友達です');
        }
        
        // 友達を追加（双方向）
        $currentUser->friends()->syncWithoutDetaching([$userId]);
        $friend->friends()->syncWithoutDetaching([$currentUser->id]);
        
        return redirect()->route('dashboard')->with('success', $friend->name . 'さんを友達に追加しました');
    }
}
