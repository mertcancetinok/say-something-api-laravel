<?php

namespace App\Http\Controllers;

use App\Helpers\MailHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

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
        if (User::where('email', $request->email)->where("id",'!=',$user->id)->first() != null) {
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

    public function updateProfilePicture(Request $request){

        $validator = Validator::make($request->all(), [
            'base64Image' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $user = auth()->user();

        $name = time().'.' . explode('/', explode(':', substr($request->base64Image, 0, strpos($request->base64Image, ';')))[1])[1];

        Image::make($request->base64Image)->save(public_path('user-images/').$name);

        $user->profile_photo = env('APP_CDN') . '/user-images/' . $name;

        $user->save();

        return response()->json(['success' => true,'user' => auth()->user()]);

    }

    public function banUser(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        if ($user == null) {
            return response()->json(['message' => __('user.user_not_found')], 422);
        }
        $user->is_active = false;
        $user->save();

        return response()->json(['success' => true]);
    }

}
