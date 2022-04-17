<?php

namespace App\Http\Controllers;

use App\Helpers\MailHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function me()
    {
        return auth()->user();
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        if (User::where('email', $request->email)->first() != null) {
            return response()->json(['message' => __('user.email_exists')], 422);
        }
        $user->update($request->all());
        return response()->json(['success' => true]);
    }

    public function confirmEmail(Request $request)
    {

        $user = auth()->user();
        $user->email_verified_code = strval(rand(100000, 999999));
        $user->email_verified_expire = now()->addMinutes(10);
        $user->save();

        MailHelper::ConfirmEmail($user->email_verified_code, $user->email);

        return response()->json(['success' => true]);
    }

    public function confirmEmailCode(Request $request)
    {

        $user = auth()->user();

        if (now() > $user->email_verified_expire) {
            return response()->json(['message' => __('user.email_expire')], 422);
        }
        if ($user->email_verified_code != $request->code) {
            return response()->json(['message' => __('user.email_code_wrong')], 422);
        }


        $user->email_verified_at = now();
        $user->save();

        return response()->json(['success' => true]);

    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        if (!(Hash::check($request->current_password, $user->password))) {
            return response()->json(['message' => __('user.current_password_wrong')], 422);
        }
        $user->password = bcrypt($request->password);
        $user->save();
        return response()->json(['success' => true]);
    }

}
