<?php

namespace App\Http\Controllers\api\agent;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Thujohn\Twitter\Facades\Twitter;

class TwitterController extends Controller
{
    public function postTweet(Request $request)
    {
        $tweetText = $request->input('property_url');

        try {
            Twitter::postTweet(['status' => $tweetText]);
            return response()->json(['message' => 'Tweet posted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error posting tweet: ' . $e->getMessage()], 500);
        }
    }

    public function connect_twitter(Request $request)
    {
        $callback = url('/api/v1/agent/share/auth/twitter/callback');
        $_twitter_connect = new TwitterOAuth('U3tb3T2YeX7ij6lQ9CE3lY7dM', 'sEGD8Y6wCOhfITrzjdjZxkpNPgOe9JUqyLya7xQEH2ZGzjijML');
        $_access_token = $_twitter_connect->oauth('oauth/request_token',['oauth_callback' => $callback]);
        $_route = $_twitter_connect->url('oauth/authorize', ['oauth_token' => $_access_token['oauth_token']]);

        return response()->json([
            'redirect_route'=> $_route,
        ]);
    }

    public function twitter_cbk(Request $request)
    {
        $response = $request->all();

        $oauth_token = $response['oauth_token'];
        $oauth_verifier = $response['oauth_verifier'];

        $_twitter_connect = new TwitterOAuth('U3tb3T2YeX7ij6lQ9CE3lY7dM', 'sEGD8Y6wCOhfITrzjdjZxkpNPgOe9JUqyLya7xQEH2ZGzjijML', $oauth_token, $oauth_verifier);

        $token = $_twitter_connect->oauth('oauth/access_token',['oauth_verifier' => $oauth_verifier]);

        $oauth_token = $token['oauth_token'];
        $oauth_token_secret = $token['oauth_token_secret'];

        return response()->json([
            'oauth_token ' => $oauth_token,
            'oauth_token_secret' => $oauth_token_secret,
            'redirect_url' => 'http://localhost:8000/api/v1/agent/share/twitter-share-property?oauth_token='.$oauth_token.'&oauth_token_secret='.$oauth_token_secret
        ]);
    }

    public function shareProperty(Request $request)
    {
        $oauth_token = isset($_GET['oauth_token']) ? $_GET['oauth_token'] : '';
        if (!$oauth_token) {
            return response()->json([
                'status' => 'Request Failed',
                'message' => 'Invaild / No Access Token provided',
            ],404);
        }

        $oauth_token_secret = isset($_GET['oauth_token_secret']) ? $_GET['oauth_token_secret'] : '';
        if (!$oauth_token_secret) {
            return response()->json([
                'status' => 'Request Failed',
                'message' => 'Invaild / No Token Secret provided',
            ],404);
        }

        $this->validate($request,[
            'property_url' => 'required|string|max:255',
        ]);

        $content = $request->input('property_url');

        $push = new TwitterOAuth('U3tb3T2YeX7ij6lQ9CE3lY7dM', 'sEGD8Y6wCOhfITrzjdjZxkpNPgOe9JUqyLya7xQEH2ZGzjijML', $oauth_token, $oauth_token_secret);
        $push->setTimeouts(10,15);
        $push->post('statuses/update',['status' => $content]);

        return response()->json([
            'message' => 'Property Shared on Twitter',
        ]);
    }
}
