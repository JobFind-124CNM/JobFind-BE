<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserVerification;
use App\Support\UserStatus;
use App\Support\Role;
use App\Mail\OtpVerificationMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthService
{
    protected $otpService;
    protected $roleService;

    public function __construct(OtpService $otpService, RoleService $roleService)
    {
        $this->otpService = $otpService;
        $this->roleService = $roleService;
    }

    public function register(array $data)
    {
        $this->validateRegistration($data);

        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => UserStatus::PENDING
        ]);

        $this->roleService->assignRoleToUser($user, Role::USER);

        $this->sendVerificationOtp($user);

        return $user;
    }

    private function validateRegistration(array $data)
    {
        $validator = validator($data, [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function sendVerificationOtp(User $user)
    {
        $verification = $this->otpService->generateOtp($user);

        $this->sendEmailVerification($verification->otp_code, $user);
    }

    private function sendEmailVerification($token, $user) {
        Mail::to($user->email)->send(new OtpVerificationMail($token));
    }

    public function verifyOtp(int $userId, string $otpCode)
    {
        $this->validateOtpVerification($userId, $otpCode);

        $user = User::findOrFail($userId);

        $verified = $this->otpService->verifyOtp($user, $otpCode);

        if (!$verified) {
            throw new \Exception('Invalid or expired OTP');
        }

        return $user;
    }

    private function validateOtpVerification(int $userId, string $otpCode)
    {
        $validator = validator([
            'user_id' => $userId,
            'otp_code' => $otpCode
        ], [
            'user_id' => 'required|exists:users,id',
            'otp_code' => 'required|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function login(array $credentials)
    {
        $this->validateLogin($credentials);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('Invalid credentials');
        }

        $this->checkUserStatus($user);

        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            throw new \Exception('Could not create token');
        }

        return $token;
    }

    private function validateLogin(array $credentials)
    {
        $validator = validator($credentials, [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function checkUserStatus(User $user)
    {
        switch ($user->status) {
            case UserStatus::PENDING:
                throw new \Exception('Please verify your email first');
            case UserStatus::INACTIVE:
                throw new \Exception('Your account is inactive');
            case UserStatus::SUSPENDED:
                throw new \Exception('Your account is suspended');
            case UserStatus::BANNED:
                throw new \Exception('Your account is banned');
        }
    }

    public function resendOtp(string $email)
    {
        $validator = validator(['email' => $email], [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = User::where('email', $email)->first();

        if ($user->status !== UserStatus::PENDING) {
            throw new \Exception('User is already verified');
        }

        // Send new OTP
        $this->sendVerificationOtp($user);

        return $user;
    }

    public function logout(User $user)
    {
        $user->tokens()->delete();
    }

    public function generateLoginUrl($loginType)
    {
        return Socialite::driver($loginType)
                    ->stateless()
                    ->redirect()
                    ->getTargetUrl();
    }

    public function handleSocialCallback($loginType, $code)
    {
        if ($loginType === 'google') {
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => config('services.google.redirect'),
                'grant_type' => 'authorization_code',
                'code' => $code,
            ]);

            if ($tokenResponse->failed()) {
                throw new \Exception('Failed to obtain access token from Google');
            }

            $accessToken = $tokenResponse->json()['access_token'];

            $userInfoResponse = Http::get('https://www.googleapis.com/oauth2/v3/userinfo', [
                'access_token' => $accessToken,
            ]);

            if ($userInfoResponse->failed()) {
                throw new \Exception('Failed to fetch user info from Google');
            }

            $userInfo = $userInfoResponse->json();
            return $this->processUser($userInfo, 'google');
        } elseif ($loginType === 'facebook') {
            $tokenResponse = Http::asForm()->post('https://graph.facebook.com/v12.0/oauth/access_token', [
                'client_id' => config('services.facebook.client_id'),
                'client_secret' => config('services.facebook.client_secret'),
                'redirect_uri' => config('services.facebook.redirect'),
                'code' => $code,
            ]);

            if ($tokenResponse->failed()) {
                throw new \Exception('Failed to obtain access token from Facebook');
            }

            $accessToken = $tokenResponse->json()['access_token'];

            $userInfoResponse = Http::get('https://graph.facebook.com/v12.0/me', [
                'fields' => 'id,name,email,picture.type(large)',
                'access_token' => $accessToken,
            ]);

            if ($userInfoResponse->failed()) {
                throw new \Exception('Failed to fetch user info from Facebook');
            }

            $userInfo = $userInfoResponse->json();
            return $this->processUser($userInfo, 'facebook');
        }

        throw new \Exception('Invalid login type');
    }


    private function processUser($socialiteUser, $platform)
    {
        $email = $socialiteUser['email'] ?? null;
        $name = $socialiteUser['name'] ?? 'Unknown';
        $platformId = $socialiteUser['id'] ?? null;
        $avatar = $socialiteUser['picture'] ?? null;

        $data = [
            'username' => $name,
            'avatar' => $avatar,
            'status' => UserStatus::ACTIVE,
            'password' => Hash::make('password'),
        ];

        if ($platform === 'google') {
            $platformId = $socialiteUser['sub'] ?? null;
            $data['google_id'] = $platformId;
        } elseif ($platform === 'facebook') {
            $avatar = $socialiteUser['picture']['data']['url'] ?? null;
            $data['avatar'] = $avatar;
            $data['facebook_id'] = $platformId;
        }

        $user = User::updateOrCreate(['email' => $email], $data);

        return JWTAuth::fromUser($user);
    }

    public function generateTokenChangePassword() {
        return "123";
    }
}