<?php

namespace App\Http\Controllers\api\agent;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyVideo;
use Illuminate\Http\Request;

class PropertySalesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function allSoldProperty()
    {
        $properties = Property::where(['agent_id' => auth()->user()->id, 'is_taken' => true])->get();
        if ($properties->count() > 0) {
            return response()->json(['property'=>$properties],200);
        } else {
            return response()->json(['message' => 'No property(s) found'], 404);
        }
    }

    public function viewSingleSoldProperty()
    {
        $id = isset($_GET['property_id']) ? $_GET['property_id'] : '';
        if (!$id) {
            return response()->json([
                'status' => 'Request Failed',
                'message' => 'Invaild URL provided',
            ],404);
        } else{
            $prop = Property::where(['id' =>$id ,'agent_id' => auth()->user()->id,'is_taken' => true])->exists();

            if (!$prop) {
                return response()->json(['message' => 'Property not found / Property not sold yet'], 404);
            }

            $property = Property::where(['id' =>$id,'agent_id' => auth()->user()->id,'is_taken' => true])->first();
            $propertyImage = PropertyImage::where(['property_id' =>$id ,'agent_id' => auth()->user()->id])->get();
            $propertyVideo = PropertyVideo::where(['property_id' =>$id ,'agent_id' => auth()->user()->id])->get();
            
            return response()->json([
                'status' => 'Successful',
                'message' => 'Request was successful',
                'property' => $property,
                'property_images' => $propertyImage,
                'property_videos' => $propertyVideo
            ],200);
        }
    }
}
