<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemoController extends Controller
{
    /**
     * 【チャット画面用】最新のメモを1件取得してJSONで返す
     */
    public function latest()
    {
        $memo = Auth::user()->memos()->latest()->first();
        return response()->json($memo);
    }

    /**
     * 【チャット画面用】メモを保存・更新する
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        // 最新のメモを取得
        $memo = Auth::user()->memos()->latest()->first();

        // もし今日のメモがあれば更新、なければ新規作成などのロジックを入れることも可能
        // 今回はシンプルに毎回新規作成、あるいは要件に合わせて調整
        // ※前回のチャット画面のJSでは「新規作成」か「更新」かをIDで判断していましたが、
        //  ここではシンプルに新規作成として保存します。
        
        $memo = Auth::user()->memos()->create([
            'content' => $request->content,
            'target_user_id' => null, // チャットからのクイックメモは一旦誰宛でもない
        ]);

        return response()->json($memo);
    }
    
    /**
     * メモを更新する（ID指定あり）
     */
    public function update(Request $request, Memo $memo)
    {
        // 自分のメモでなければエラー
        if ($memo->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $memo->update(['content' => $request->content]);

        // JSONリクエストの場合はJSONを返す、それ以外はリダイレクト
        if ($request->expectsJson()) {
            return response()->json($memo);
        }

        return back()->with('success', 'メモを更新しました');
    }

    /**
     * 全てのメモ一覧を表示
     */
    public function index()
    {
        $memos = Auth::user()->memos()->latest()->get();
        return view('memos.index', compact('memos'));
    }

    /**
     * ★特定のユーザーについてのメモ一覧を表示
     */
    public function indexByUser(User $user)
    {
        // ログインユーザーが書いたメモの中で、target_user_id が選択したユーザーのIDまたはnullのものを取得
        // （チャットから作成されたメモも含める）
        $memos = Auth::user()->memos()
            ->where(function($query) use ($user) {
                $query->where('target_user_id', $user->id)
                      ->orWhereNull('target_user_id');
            })
            ->latest()
            ->get();

        return view('memos.index', compact('memos', 'user'));
    }

    /**
     * メモ編集画面を表示
     */
    public function edit(Memo $memo)
    {
        // 自分のメモでなければエラー
        if ($memo->user_id !== Auth::id()) {
            abort(403);
        }

        return view('memos.edit', compact('memo'));
    }

    /**
     * メモの削除
     */
    public function destroy(Memo $memo)
    {
        if ($memo->user_id !== Auth::id()) {
            abort(403);
        }
        
        $memo->delete();

        return back();
    }
}