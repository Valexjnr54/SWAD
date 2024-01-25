<?php

namespace App\Http\Controllers\api\agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmEmail;

class AgentAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','confirmEmail']]);
    }

    public function register(Request $request)
    {
        $this->validate($request,[
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'agency_name' => 'required|string|max:255',
            'license_number' => 'required|string|max:255',
            'office_address' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if($request->hasFile('profile_photo')){
            $file = $request->file('profile_photo');
            $folder = 'Swad_Holdings/images/agent_profile';
            $uploadedFile = cloudinary()->upload($file->getRealPath(), [
                'folder' => $folder
            ]);
            $fileNameToStore = $uploadedFile->getSecurePath();
        }else{
            $fileNameToStore = 'https://res.cloudinary.com/dx2gbcwhp/image/upload/v1695134288/Swad_Holdings/images/agent_profile/noimage_radbzf.png';
        }

        $user = new User;
        $user->fullname = $request->input('fullname');
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->agency_name = $request->input('agency_name');
        $user->license_number = $request->input('license_number');
        $user->office_address = $request->input('office_address');
        $user->profile_photo = $fileNameToStore;
        $user->password = bcrypt($request->input('password'));
        $user->save();

        // Generate JWT token for the user
        $token = JWTAuth::fromUser($user);
        Mail::to($user->email)->send(new ConfirmEmail($user));
        return response()->json([
            'message' => 'Registration successful',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['errors' =>'Invalid Email / Password'], 401);
        }
        $agentDetail = User::where(['email' => $request->input('email')])->first();
        $email_status = $agentDetail->email_verified_status;
        if ($email_status == 0) {
            return response()->json([
                'status' => 'Bad Request',
                'message' => 'E-mail Confirmation Required'
            ],400);
        }
        return $this->createToken($token);
    }

    public function createToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

    public function profile()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json([
            'message' => 'User Logged out successful'
        ]);
    }

    public function confirmEmail(Request $request)
    {
        $id = isset($_GET['id']) ? $_GET['id'] : '';
        if (!$id) {
           return response()->json([
            'status' => 'Request Failed',
            'message' => 'Invaild URL provided',
           ],404);
        } else {
            $agentDetail = User::where(['id' => $id])->first();
            if ($agentDetail) {
                $agentEmail = $agentDetail->email;
                $agentEmailConfirm = $agentDetail->email_verified_status;
                if ($agentEmailConfirm == 1) {
                    return response()->json([
                        'status' => 'Bad Request',
                        'message' => 'Agent Email Already confirmed',
                    ],400);
                } else {
                    $update_confirmation = User::where(['id' => $id])->update(['email_verified_status'=>1]);
                    $email_confirmation_date = User::where(['id' => $id])->update(['email_verified_at'=>now()]);
                    if ($update_confirmation && $email_confirmation_date) {
                        return response()->json([
                            'status' => 'Successful',
                            'message' => 'Email Confirmation Successful',
                            'url' => 'login Url'
                        ],200);
                    }
                }
            } else {
                return response()->json([
                    'status' => 'Request Failed',
                    'message' => 'Agent does not exist',
                ],404);
            }

        }

    }
}
