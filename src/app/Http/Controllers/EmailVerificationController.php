<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('throttle:6,1')->only('verifyCode', 'generateCode');
    }

    /**
     * 認証コード入力画面を表示
     */
    public function showVerificationForm()
    {
        $user = Auth::user();

        if ($user->email_verified_at !== null) {
            return redirect()->route('attendance.index');
        }

        // 既存のコードがあるかチェック（新しく生成しない）
        $cachedCode = Cache::get("email_verification_code_{$user->id}");

        // デバッグ情報（一時的）
        if (!$cachedCode) {
            session()->flash('debug_info', '認証コードが見つかりません。メール認証画面から再度お試しください。');
            return redirect()->route('verification.notice')
                ->with('resent', true);
        }

        return view('auth.verification-code');
    }

    /**
     * 認証コードを検証
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'verification_code' => ['required', 'string', 'size:6'],
        ], [
            'verification_code.required' => '認証コードを入力してください。',
            'verification_code.size' => '認証コードは6桁で入力してください。',
        ]);

        $user = Auth::user();

        if ($user->email_verified_at !== null) {
            return redirect()->route('attendance.index');
        }

        $cachedCode = Cache::get("email_verification_code_{$user->id}");

        // デバッグ情報（一時的）
        Log::info("認証コード検証", [
            'user_id' => $user->id,
            'input_code' => $request->verification_code,
            'cached_code' => $cachedCode,
            'cache_key' => "email_verification_code_{$user->id}"
        ]);

        if (!$cachedCode || $cachedCode !== $request->verification_code) {
            return back()->withErrors([
                'verification_code' => '認証コードが正しくありません。入力コード: ' . $request->verification_code . ' / 保存コード: ' . ($cachedCode ?? 'なし')
            ])->withInput();
        }

        // 認証を完了
        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        // キャッシュから認証コードを削除
        Cache::forget("email_verification_code_{$user->id}");

        return redirect()->route('attendance.index')
            ->with('verified', true);
    }

    /**
     * 認証コードを生成
     */
    private function generateVerificationCode($userId)
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // 5分間有効なコードをキャッシュに保存
        Cache::put("email_verification_code_{$userId}", $code, now()->addMinutes(5));

        return $code;
    }

    /**
     * 新しい認証コードを生成（必要に応じて）
     */
    public function generateCode()
    {
        $user = Auth::user();

        if ($user->email_verified_at !== null) {
            return redirect()->route('attendance.index');
        }

        $this->generateVerificationCode($user->id);

        return back()->with('code_generated', '新しい認証コードを生成しました。');
    }

    /**
     * 認証メールを再送信（Fortifyのverification.sendをオーバーライド）
     */
    public function resendNotification(Request $request)
    {
        $user = Auth::user();

        if ($user->email_verified_at !== null) {
            return redirect()->route('attendance.index');
        }

        // カスタム通知を送信（認証コード付き）
        $user->sendEmailVerificationNotification();

        return back()->with('resent', true);
    }
}
