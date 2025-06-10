<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * 未認証時のリダイレクト先を返す
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // 管理者がアクセスするすべてのURLパターンを網羅
            if (
                $request->is('admin/*') || $request->is('stamp_correction_request/*')
            ) {
                return route('admin.login'); // 管理者用ログインページへ
            }

            // それ以外は一般ユーザー用ログインページへ
            return route('login');
        }
    }
}
