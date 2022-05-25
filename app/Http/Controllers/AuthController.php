<?php

namespace App\Http\Controllers;

use App\Helpers\MailHelper;
use App\Models\User;
use App\Models\UserPasswordRecovery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'sendResetPasswordLink', 'confirmResetPasswordLink', 'resetPasswordSetNewPassword']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user && !$user->is_active) {
            return response()->json(['message' => 'User not found'], 403);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'device_token' => 'nullable',
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));
        return response()->json([
            'message' => __('auth.success_register'),
            'user' => $user
        ], 201);


    }

    public function sendResetPasswordLink(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => __('auth.user_not_found')], 404);
        }

        $passwordRecovery = UserPasswordRecovery::create([
            'user_id' => $user->id,
            'recovery_key' => strval(rand(100000, 999999)),
            'expire_at' => now()->addMinutes(10)
        ]);

        MailHelper::PasswordResetMail($passwordRecovery->recovery_key, $user->email);

        return response()->json(['message' => __('auth.reset_password_link_sent')], 200);

    }

    public function confirmResetPasswordLink(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'recovery_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }


        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => __('auth.user_not_found')], 404);
        }

        $passwordRecovery = UserPasswordRecovery::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$passwordRecovery) {
            return response()->json(['error' => __('auth.password_recovery_not_found')], 404);
        }

        if (now()->diffInMinutes($passwordRecovery->expire_at) > 10) {
            return response()->json(['error' => __('auth.password_recovery_link_expired')], 400);
        }

        if ($passwordRecovery->recovery_key != $request->recovery_key) {
            return response()->json(['error' => __('auth.password_recovery_key_not_match')], 400);
        }

        return response()->json(['message' => __('auth.password_recovery_key_match')], 200);

    }

    public function resetPasswordSetNewPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'recovery_key' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($this->confirmResetPasswordLink($request)->getStatusCode() != 200) {
            return $this->confirmResetPasswordLink($request);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = bcrypt($request->new_password);
        $user->save();

        return response()->json(['message' => __('auth.password_reset_set_new_password_success')], 200);

    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => __('auth.logout')]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
