<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ResponseCode;
use App\Helpers\CodeHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegistrationRequest;
use App\Http\Requests\Api\V1\Auth\UpdatePasswordRequest;
use App\Http\Requests\Api\V1\Auth\UpdateProfileImageRequest;
use App\Http\Requests\Api\V1\Auth\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(RegistrationRequest $request)
    {
        $user = User::create([
            'code' => CodeHelper::generateUserCode(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->balance()->create(['amount' => 0]);

        return ResponseHelper::generate(
            responseCode: ResponseCode::Ok,
            message: 'Registrasi berhasil, silahkan login',

        );
    }

    public function login(LoginRequest $request)
    {
        $email = $request->email;
        $password = $request->password;

        $user = User::where('email', $email)->first();

        if (empty($user)) {
            return ResponseHelper::generate(responseCode: ResponseCode::InvalidUser);
        }

        if (!Hash::check($password, $user->password)) {
            return ResponseHelper::generate(ResponseCode::LoginFailed);
        }

        $token = auth('jwt')->login($user);

        return ResponseHelper::generate(
            responseCode: ResponseCode::Ok,
            message: 'Login Sukses',
            data: [
                'access_token' => $token,
            ]
        );
    }

    public function logout()
    {
        auth('jwt')->logout();

        return ResponseHelper::generate(
            responseCode: ResponseCode::Ok,
            message: 'Logout Sukses'
        );
    }

    public function getProfile()
    {
        try {
            $user = auth('jwt')->user();

            if (!$user) {
                return ResponseHelper::generate(
                    responseCode: ResponseCode::Unauthorized,
                    message: 'Token tidak valid atau sudah kadaluarsa.'
                );
            }

            return ResponseHelper::generate(
                responseCode: ResponseCode::Ok,
                message: 'Data profil berhasil diambil',
                data: [
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'profile_image' => $user->profile_image ?? null
                ]
            );
        } catch (Throwable $th) {
            return ResponseHelper::generate(
                responseCode: ResponseCode::GeneralError,
                message: 'Terjadi kesalahan saat mengambil profil'
            );
        }
    }

    public function updateProfileImage(UpdateProfileImageRequest $request)
    {
        try {
            $user = auth('jwt')->user();

            $file = $request->file('image');

            if ($user->profile_image) {
                $oldFilePath = str_replace(url('storage') . '/', '', $user->profile_image);
                Storage::disk('public')->delete($oldFilePath);
            }

            $fileName = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/profile_images', $fileName);

            $user->profile_image = url('storage/profile_images/' . $fileName);
            $user->save();

            return ResponseHelper::generate(
                responseCode: ResponseCode::Ok,
                message: 'Update profile image berhasil',
                data: [
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'profile_image' => $user->profile_image,
                ]
            );
        } catch (Throwable $th) {
            return ResponseHelper::generate(
                responseCode: ResponseCode::GeneralError,
                message: 'Terjadi kesalahan saat memperbarui foto profil.' . $th->getMessage()
            );
        }
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $user = auth('jwt')->user();

            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
            ]);

            return ResponseHelper::generate(
                responseCode: ResponseCode::Ok,
                message: 'Update profile berhasil',
                data: [
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'profile_image' => $user->profile_image ?? null
                ]
            );
        } catch (Throwable $th) {
            Log::error('Update profile error: ' . $th->getMessage());

            return ResponseHelper::generate(
                responseCode: ResponseCode::GeneralError,
                message: 'Terjadi kesalahan saat memperbarui profil'
            );
        }
    }

    public function getBalance()
    {
        $user = auth('jwt')->user();

        return response()->json([
            'message' => 'Get Balance Berhasil',
            'data' => [
                'balance' => $user->balance->amount
            ]
        ]);
    }
}
