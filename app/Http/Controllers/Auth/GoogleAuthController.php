<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Google認証にリダイレクト
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Google認証のコールバックを処理
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // 既存のユーザーを検索（google_idまたはemailで）
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($user) {
                // 既存ユーザーの場合、google_idを更新（まだ設定されていない場合）
                if (!$user->google_id) {
                    $user->google_id = $googleUser->getId();
                    $user->save();
                }
                
                // アバターが設定されていない場合、Googleのアバターを設定
                if (!$user->avatar && $googleUser->getAvatar()) {
                    $user->avatar = $googleUser->getAvatar();
                    $user->save();
                }
            } else {
                // 新規ユーザーを作成
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(32)), // ランダムなパスワードを設定
                    'avatar' => $googleUser->getAvatar(),
                    // usernameは後で設定してもらう
                ]);
            }

            // ログイン
            Auth::login($user, true);

            // usernameが設定されていない場合は設定画面にリダイレクト
            if (!$user->username) {
                return redirect()->route('username.setup');
            }

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google認証に失敗しました: ' . $e->getMessage());
        }
    }
}
