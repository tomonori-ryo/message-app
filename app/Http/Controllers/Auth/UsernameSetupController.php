<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsernameSetupController extends Controller
{
    /**
     * アカウント名設定画面を表示
     */
    public function show(): View|RedirectResponse
    {
        $user = Auth::user();
        
        // 既にusernameが設定されている場合はダッシュボードにリダイレクト
        if ($user->username) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.username-setup');
    }

    /**
     * アカウント名を設定
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        // 既にusernameが設定されている場合はダッシュボードにリダイレクト
        if ($user->username) {
            return redirect()->route('dashboard');
        }
        
        $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('users')->ignore($user->id),
            ],
        ], [
            'username.regex' => 'アカウント名は英数字とアンダースコアのみ使用できます',
            'username.unique' => 'このアカウント名は既に使用されています',
        ]);

        $user->username = $request->username;
        $user->save();

        return redirect()->route('dashboard')->with('status', 'アカウント名を設定しました');
    }
}
