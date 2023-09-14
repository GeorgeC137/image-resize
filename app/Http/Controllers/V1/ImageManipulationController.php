<?php

namespace App\Http\Controllers\V1;

use App\Models\Album;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use App\Models\ImageManipulation;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use App\Http\Requests\ResizeImageRequest;
use App\Http\Resources\V1\ImageManipulationResource;
use Illuminate\Http\Request;

class ImageManipulationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return ImageManipulationResource::collection(ImageManipulation::where('user_id', $user->id)->paginate());
    }

    public function byAlbum(Album $album, Request $request)
    {
        $user = $request->user();

        if ($user->id !== $album->user_id) {
            return response('Unauthorized!!!', 403);
        }

        $where = [
            'album_id' => $album->id,
            'user_id' => $user->id
        ];

        return ImageManipulationResource::collection(ImageManipulation::where($where)->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function resize(ResizeImageRequest $request)
    {
        $all = $request->all();

        // Uploaded File or String (url)
        $image = $all['image'];
        unset($all['image']);

        $data = [
            'type' => ImageManipulation::TYPE_RESIZE,
            'data' => json_encode($all),
            'user_id' => $request->user()->id,
        ];

        if (isset($all['album_id'])) {
            $album = Album::find($all['album_id']);

            $user = $request->user();

            if ($user->id !== $album->user_id) {
                return response('Unauthorized!!!', 403);
            }

            $data['album_id'] = $all['album_id'];
        }

        // Create image directory
        $dir = 'images/'.Str::random().'/';
        $absolutePath =  public_path($dir);

        File::makeDirectory($absolutePath);

        // Work on saving image
        // path = images/randomstring/test.jpg
        // path = images/randomstring/test-resized.jpg
        if ($image instanceof UploadedFile) {
            $data['name'] = $image->getClientOriginalName();
            // test.jpg -> test-resized.jpg
            $filename = pathinfo($data['name'], PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $originalPath = $absolutePath . $data['name'];

            $image->move($absolutePath, $data['name']);
        } else {
            $data['name'] = pathinfo($image, PATHINFO_BASENAME);
            $filename = pathinfo($image, PATHINFO_FILENAME);
            $extension = pathinfo($image, PATHINFO_EXTENSION);
            $originalPath = $absolutePath.$data['name'];

            copy($image, $originalPath);
        }

        $data['path'] = $dir.$data['name'];

        // Working on resizing image
        $w = $all['w'];
        $h = $all['h'] ?? false;

        list($width, $height, $image) = $this->getImageWidthAndHeight($w, $h, $originalPath);

        $resizedFilename = $filename.'-resized.'.$extension;

        $image->resize($width, $height)->save($absolutePath.$resizedFilename);
        $data['output_path'] = $dir.$resizedFilename;

        $imageManipulation = ImageManipulation::create($data);

        return new ImageManipulationResource($imageManipulation);
    }

    /**
     * Display the specified resource.
     */
    public function show(ImageManipulation $image, Request $request)
    {
        $user = $request->user();

        if ($user->id !== $image->user_id) {
            return response('Unauthorized!!!', 403);
        }

        return new ImageManipulationResource($image);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ImageManipulation $id, Request $request)
    {
        $user = $request->user();

        if ($user->id !== $id->user_id) {
            return response('Unauthorized!!!', 403);
        }

        $id->delete();

        return response('Image Deleted Successfully', 200);
    }

    private function getImageWidthAndHeight($w, $h, string $originalPath)
    {
        // 1000 - 50% = 500px (w is %)
        $image = Image::make($originalPath);
        $originalWidth = $image->width();
        $originalHeight = $image->height();

        // Check if passed width($w) ends with %
        if (str_ends_with($w, '%')) {
            $ratioW = (float)str_replace('%', '', $w);
            $ratioH = $h ? (float)str_replace('%', '', $h) : $ratioW;

            // Calculate value in pixels
            $newWidth = $originalWidth * $ratioW /  100;
            $newHeight = $originalHeight * $ratioH /  100;
        } else {
            // w is number not %
            $newWidth = (float)$w;
            /**
                * $originalWidth -> $newWidth
                * $originalHeight -> $newHeight
                * $newHeight = $originalHeight * $newWidth/$originalWidth
            */
            $newHeight = $h ? (float)$h : $originalHeight * $newWidth/$originalWidth;
        }

        return [
            $newHeight,
            $newWidth,
            $image
        ];
    }
}
