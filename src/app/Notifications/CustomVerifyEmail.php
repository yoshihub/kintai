<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Cache;

class CustomVerifyEmail extends VerifyEmail
{
    /**
     * メールメッセージを構築
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $verificationCode = $this->getOrGenerateVerificationCode($notifiable->id);

        return (new MailMessage)
            ->subject('メールアドレスの認証について')
            ->line('ご登録いただき、ありがとうございます。')
            ->line('メールアドレスの認証を完了するために、以下のいずれかの方法をお選びください。')
            ->line('')
            ->line('【方法1】下記のボタンをクリックしてください。')
            ->action('メールアドレスを認証する', $verificationUrl)
            ->line('')
            ->line('【方法2】ウェブサイトで認証コードを入力してください。')
            ->line('認証コード: ' . $verificationCode)
            ->line('※認証コードの有効期限は5分間です。')
            ->line('')
            ->line('このメールに心当たりがない場合は、このメールを無視してください。');
    }

    /**
     * 認証コードを取得または生成
     */
    private function getOrGenerateVerificationCode($userId)
    {
        $code = Cache::get("email_verification_code_{$userId}");

        if (!$code) {
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put("email_verification_code_{$userId}", $code, now()->addMinutes(5));
        }

        return $code;
    }
}
