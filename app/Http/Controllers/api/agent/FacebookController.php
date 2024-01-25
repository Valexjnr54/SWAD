<?php

namespace App\Http\Controllers\api\agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;

class FacebookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function getAuthUrl()
    {
        $url = Socialite::driver('facebook')->stateless()->redirect()->getTargetUrl();
        return response()->json([
            'status' => 'Successful',
            'redirect_url' => $url,
        ],200);
    }

    public function handleCallback(Request $request)
    {
        $facebookUser = Socialite::driver('facebook')->stateless()->user();

        $access_token = $facebookUser->token;
        $facebookUser_id = $facebookUser->id;

        // $facebookUser contains user information, including access token.

        // Implement your logic to save the user's access token and user ID here.

        return response()->json(['message' => 'Facebook authentication successful','access_token ' => $access_token,'facebookUser_id' => $facebookUser_id,'redirect_url' => 'http://localhost:8000/api/v1/agent/share/share-property?access_token='.$access_token.'&facebookUser_id='.$facebookUser_id]);
    }

    public function shareProperty(Request $request)
    {
        $accessToken = isset($_GET['access_token']) ? $_GET['access_token'] : '';
        if (!$accessToken) {
            return response()->json([
                'status' => 'Request Failed',
                'message' => 'Invaild / No Access Token provided',
            ],404);
        }

        $facebookUserId = isset($_GET['facebookUser_id']) ? $_GET['facebookUser_id'] : '';
        if (!$facebookUserId) {
            return response()->json([
                'status' => 'Request Failed',
                'message' => 'Invaild / No Facesbook User Id provided',
            ],404);
        }

        $this->validate($request,[
            'property_url' => 'required|string|max:255',
        ]);

        $content = $request->input('property_url');

        $response = Http::withToken($accessToken)
            ->post("https://graph.facebook.com/$facebookUserId/feed", [
                'message' => $content,
            ]);

        // Check the response for success or handle any errors.

        return response()->json(['message' => 'Property shared successfully on Facebook']);
    }
}
