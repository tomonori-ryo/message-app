<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $blockedUsers = $user->blockedUsers()->get();
        
        return view('profile.edit', [
            'user' => $user,
            'blockedUsers' => $blockedUsers,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's avatar.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();
        $disk = env('CLOUDINARY_CLOUD_NAME') ? 'cloudinary' : 'public';

        // 古いアバターを削除
        if ($user->avatar) {
            Storage::disk($disk)->delete($user->avatar);
        }

        // 新しいアバターを保存
        $avatarPath = $request->file('avatar')->store('avatars', $disk);
        
        // Cloudinaryを使用している場合、パブリックURLを取得
        if ($disk === 'cloudinary') {
            $user->avatar = Storage::disk($disk)->url($avatarPath);
        } else {
            $user->avatar = $avatarPath;
        }
        
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'avatar-updated');
    }

    /**
     * Delete the user's avatar.
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
        $disk = env('CLOUDINARY_CLOUD_NAME') ? 'cloudinary' : 'public';

        if ($user->avatar) {
            // Cloudinaryの場合はURLが保存されているので、パスを抽出して削除
            if ($disk === 'cloudinary') {
                // Cloudinary URLからpublic_idを抽出
                $publicId = $this->extractPublicIdFromUrl($user->avatar);
                if ($publicId) {
                    Storage::disk($disk)->delete($publicId);
                }
            } else {
                Storage::disk($disk)->delete($user->avatar);
            }
            $user->avatar = null;
            $user->save();
        }

        return Redirect::route('profile.edit')->with('status', 'avatar-deleted');
    }

    /**
     * Extract public_id from Cloudinary URL
     */
    private function extractPublicIdFromUrl($url): ?string
    {
        // Cloudinary URL形式: https://res.cloudinary.com/{cloud_name}/image/upload/v{version}/{public_id}.{extension}
        if (preg_match('/\/image\/upload\/v\d+\/(.+)\.(jpg|jpeg|png|gif|webp)/i', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Get image URL (handles both Cloudinary URLs and local storage paths)
     */
    private function getImageUrl($imagePath): string
    {
        // Cloudinary URLの場合はそのまま返す
        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
            return $imagePath;
        }
        
        // パスの場合はStorage::url()を使用
        return Storage::url($imagePath);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
