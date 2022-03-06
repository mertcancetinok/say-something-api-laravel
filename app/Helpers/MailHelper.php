<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Mail;

class MailHelper
{

    public static function PasswordResetMail($code, $email)
    {
        $array = [
            'code' => $code,
            'email' => $email,
        ];

        mail::send('mail.password-reset-mail', $array, function ($message) use ($email) {
            $message->subject("Şifre Yenileme - Sözümün Eri");
            $message->to($email);
        });
    }

}
