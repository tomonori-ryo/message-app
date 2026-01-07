<?php

namespace App\Http\Controllers;

use App\Models\FriendCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendCategoryController extends Controller
{
    /**
     * カテゴリーを作成
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $user = Auth::user();
        
        // 表示順序を取得（既存のカテゴリーの最大値 + 1）
        $maxOrder = FriendCategory::where('user_id', $user->id)->max('order') ?? 0;

        $category = FriendCategory::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'category' => $category,
        ]);
    }

    /**
     * カテゴリーを更新
     */
    public function update(Request $request, FriendCategory $category)
    {
        // 自分のカテゴリーのみ更新可能
        if ($category->user_id !== Auth::id()) {
            abort(403, 'このカテゴリーを編集する権限がありません');
        }

        $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $category->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'category' => $category,
        ]);
    }

    /**
     * カテゴリーを削除
     */
    public function destroy(FriendCategory $category)
    {
        // 自分のカテゴリーのみ削除可能
        if ($category->user_id !== Auth::id()) {
            abort(403, 'このカテゴリーを削除する権限がありません');
        }

        // カテゴリーに属する友達のcategory_idをnullに設定
        DB::table('friends')
            ->where('user_id', Auth::id())
            ->where('category_id', $category->id)
            ->update(['category_id' => null]);

        $category->delete();

        return response()->json(['success' => true]);
    }

    /**
     * 友達をカテゴリーに割り当て
     */
    public function assignFriend(Request $request)
    {
        $request->validate([
            'friend_id' => 'required|exists:users,id',
            'category_id' => 'nullable|exists:friend_categories,id',
        ]);

        $user = Auth::user();
        $friendId = $request->friend_id;
        $categoryId = $request->category_id;

        // カテゴリーが指定されている場合、そのカテゴリーが自分のものであることを確認
        if ($categoryId) {
            $category = FriendCategory::findOrFail($categoryId);
            if ($category->user_id !== $user->id) {
                abort(403, 'このカテゴリーを使用する権限がありません');
            }
        }

        // 友達関係が存在することを確認
        $friendExists = DB::table('friends')
            ->where('user_id', $user->id)
            ->where('friend_id', $friendId)
            ->exists();

        if (!$friendExists) {
            abort(403, 'このユーザーは友達ではありません');
        }

        // 友達のカテゴリーを更新
        DB::table('friends')
            ->where('user_id', $user->id)
            ->where('friend_id', $friendId)
            ->update(['category_id' => $categoryId]);

        return response()->json(['success' => true]);
    }
}
