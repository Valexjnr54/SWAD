<?php

namespace App\Http\Controllers\api\agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyVideo;

class PropertyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function createProperty(Request $request)
    {
        $this->validate($request,[
            'property_type' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'price' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'property_status' => 'required|string|max:255',
            'display_image' => 'required|max:255',
            'property_image' => 'array',
            'property_video' => 'array',
        ]);

        $images = $request->file('property_image');
        $videos = $request->file('property_video');

        if($request->hasFile('display_image')){
            $file = $request->file('display_image');
            $folder = 'Swad_Holdings/images/property_display_image';
            $uploadedFile = cloudinary()->upload($file->getRealPath(), [
                'folder' => $folder
            ]);
            $fileNameToStore = $uploadedFile->getSecurePath();
        }else{
            $fileNameToStore = 'https://res.cloudinary.com/dx2gbcwhp/image/upload/v1695246635/Swad_Holdings/images/property_image/istockphoto-1147544807-612x612_wikbtu.jpg';
        }

        $property = new Property;
        $property->agent_id = auth()->user()->id;
        $property->property_type = $request->input('property_type');
        $property->location = $request->input('location');
        $property->price = $request->input('price');
        $property->description = $request->input('description');
        $property->display_image = $fileNameToStore;
        $property->property_status = $request->input('property_status');
        $property->save();

        if ($property) {
            $property_id = $property->id;

            if ($request->hasFile('property_image')) {
                foreach ($images as $image) {
                    $folder = 'Swad_Holdings/images/property_image';
                    $uploadedFile = cloudinary()->upload($image->getRealPath(), [
                        'folder' => $folder
                    ]);
                    $imageNameToStore = $uploadedFile->getSecurePath();

                    $property_image = new PropertyImage;
                    $property_image->agent_id = auth()->user()->id;
                    $property_image->property_id =$property_id;
                    $property_image->image_url = $imageNameToStore;
                    $property_image->save();
                }
            }

            if ($request->hasFile('property_video')) {
                foreach ($videos as $video) {
                    $folder = 'Swad_Holdings/videos/property_video';
                    $uploadedFile = cloudinary()->uploadVideo($video->getRealPath(), [
                        'folder' => $folder
                    ]);
                    $videoNameToStore = $uploadedFile->getSecurePath();

                    $property_video = new PropertyVideo;
                    $property_video->agent_id = auth()->user()->id;
                    $property_video->property_id =$property_id;
                    $property_video->video_url = $videoNameToStore;
                    $property_video->save();
                }
            }

            return response()->json([
                'status' => 'Successful',
                'message' => 'Property has been listed successfully'
            ],200);
        }else{
            return response()->json([
                'status' => 'Request Failed',
                'message' => 'Server Error'
            ],500);
        }
    }

    public function updateProperty(Request $request)
    {
        $id = isset($_GET['property_id']) ? $_GET['property_id'] : '';
        if (!$id) {
            return response()->json([
                'status' => 'Request Failed',
                'message' => 'Invaild URL provided',
            ],404);
        } else {
            $prop = Property::where(['id' =>$id])->exist();

            if (!$prop) {
                return response()->json(['message' => 'Property not found'], 404);
            }

            $this->validate($request,[
                'property_type' => 'required|string|max:255',
                'location' => 'required|string|max:255',
                'price' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'property_status' => 'required|string|max:255',
                'display_image' => 'nullable|max:255',
                'property_image' => 'array',
                'property_video' => 'array',
            ]);

            $images = $request->file('property_image');
            $videos = $request->file('property_video');

            if($request->hasFile('display_image')){
                $file = $request->file('display_image');
                $folder = 'Swad_Holdings/images/property_display_image';
                $uploadedFile = cloudinary()->upload($file->getRealPath(), [
                    'folder' => $folder
                ]);
                $fileNameToStore = $uploadedFile->getSecurePath();
            }

            $property = Property::find($id);
            $property->agent_id = auth()->user()->id;
            $property->property_type = $request->input('property_type');
            $property->location = $request->input('location');
            $property->price = $request->input('price');
            $property->description = $request->input('description');
            if ($request->hasFile('display_image')) {
                $property->display_image = $fileNameToStore;
            }
            $property->property_status = $request->input('property_status');
            $property->save();

            if ($property) {
                $property_id = $property->id;
    
                if ($request->hasFile('property_image')) {
                    foreach ($images as $image) {
                        $folder = 'Swad_Holdings/images/property_image';
                        $uploadedFile = cloudinary()->upload($image->getRealPath(), [
                            'folder' => $folder
                        ]);
                        $imageNameToStore = $uploadedFile->getSecurePath();
    
                        $property_image = PropertyImage::find($property_id);
                        $property_image->agent_id = auth()->user()->id;
                        $property_image->property_id =$property_id;
                        $property_image->image_url = $imageNameToStore;
                        $property_image->save();
                    }
                }
    
                if ($request->hasFile('property_video')) {
                    foreach ($videos as $video) {
                        $folder = 'Swad_Holdings/videos/property_video';
                        $uploadedFile = cloudinary()->uploadVideo($video->getRealPath(), [
                            'folder' => $folder
                        ]);
                        $videoNameToStore = $uploadedFile->getSecurePath();
    
                        $property_video = PropertyVideo::find($property_id);
                        $property_video->agent_id = auth()->user()->id;
                        $property_video->property_id =$property_id;
                        $property_video->video_url = $videoNameToStore;
                        $property_video->save();
                    }
                }
    
                return response()->json([
                    'status' => 'Successful',
                    'message' => 'Property has been updated successfully'
                ],200);
            }else{
                return response()->json([
                    'status' => 'Request Failed',
                    'message' => 'Server Error'
                ],500);
            }
            
        }
        
    }

    public function viewAllProperty()
    {
        $properties = Property::where(['agent_id' => auth()->user()->id])->get();
        if ($properties->count() > 0) {
            return response()->json(['property'=>$properties],200);
        } else {
            return response()->json(['message' => 'No property(s) found'], 404);
        }
    }

    public function viewSingleProperty()
    {
        $id = isset($_GET['property_id']) ? $_GET['property_id'] : '';
        if (!$id) {
            return response()->json([
                'status' => 'Request Failed',
                'message' => 'Invaild URL provided',
            ],404);
        } else{
            $prop = Property::where(['id' =>$id ,'agent_id' => auth()->user()->id])->exists();

            if (!$prop) {
                return response()->json(['message' => 'Property not found'], 404);
            }

            $property = Property::where(['id' =>$id,'agent_id' => auth()->user()->id])->first();
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

    public function deleteProperty()
    {
        $id = isset($_GET['property_id']) ? $_GET['property_id'] : '';
        if (!$id) {
            return response()->json([
                'status' => 'Request Failed',
                'message' => 'Invaild URL provided',
            ],404);
        } else{
            $prop = Property::where(['id' =>$id])->exists();

            if (!$prop) {
                return response()->json(['message' => 'Property not found'], 404);
            } else {
                $property = Property::find($id);
                $propertyImages = PropertyImage::where('property_id',$id)->get();
                $propertyVideos = PropertyVideo::where('property_id',$id)->get();
                foreach ($propertyImages as $propertyImage) {
                    $propertyImage->delete();
                }
                foreach ($propertyVideos as $propertyVideo) {
                    $propertyVideo->delete();
                }
                $property->delete();
            }
            
            return response()->json([
                'status' => 'Successful',
                'message' => 'Property was deleted successful',
            ],200);
        }
    }
}
