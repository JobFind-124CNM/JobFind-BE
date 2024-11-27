<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
        $this->middleware('auth:api', ['except' => ['login', 'socialLogin', 'register', 
                                                    'verifyOtp', 'resendOtp', 'socialCallback',
                                                    'forgotPassword']]);
    }

    public function register(Request $request)
    {
        try {
            $user = $this->authService->register($request->all());

            return response()->json([
                'status' => true,
                'message' => 'User registered. Please check your email for verification OTP.',
                'user_id' => $user->id
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $user = $this->authService->verifyOtp(
                $request->input('user_id'), 
                $request->input('otp_code')
            );

            return response()->json([
                'status' => true,
                'message' => 'Email verified successfully',
                'user' => $user
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->only(['email', 'password']);

            $token = $this->authService->login($credentials);

            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    public function resendOtp(Request $request)
    {
        try {
            $user = $this->authService->resendOtp($request->input('email'));

            return response()->json([
                'status' => true,
                'message' => 'New OTP sent to your email',
                'user_id' => $user->id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function logout(Request $request)
    {
        try {
            auth()->logout();

            return response()->json([
                'status' => true,
                'message' => 'Logged out successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function me()
    {
        $user = auth()->user();
        $user->load('roles');

        return response()->json([
            'status' => true,
            'data' => $user
        ]);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function socialLogin(Request $request) {
        $loginType = $request->query('login_type');

        if ($loginType) {
            $url = $this->authService->generateLoginUrl($loginType);

            return response()->json([
                'status' => true,
                'data' => [
                    'url' => $url
                ]
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid login type'
            ], 400);
        }
    }

    public function socialCallback(Request $request) {
        try {
            $loginType = $request->input('login_type');
            $code = $request->input('code');

            if (!$loginType || !$code) {
                throw new \Exception('Invalid login type or code');
            }

            $token = $this->authService->handleSocialCallback($loginType, $code);

            return $this->respondWithToken($token);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function forgotPassword(Request $request) {
        $email = $request->input('email');

        $user = User::findOrFail($userId);

        $token = $this->$authService->generateTokenChangePassword();

        $this->$authService->sendEmailVerification($token, $user);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}