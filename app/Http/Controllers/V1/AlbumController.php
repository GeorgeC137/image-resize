<?php

namespace App\Http\Controllers\V1;

use App\Models\Album;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlbumRequest;
use App\Http\Resources\V1\AlbumResource;
use App\Http\Requests\UpdateAlbumRequest;

class AlbumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return AlbumResource::collection(Album::where('user_id', $user->id)->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAlbumRequest $request)
    {
        $data = $request->all();
        $data['user_id'] = $request->user()->id;

        $album = Album::create($data);

        return new AlbumResource($album);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Album $album)
    {
        $user = $request->user();

        if ($user->id === $album->user_id) {
            return new AlbumResource($album);
        } else  {
            return response('Unauthorized!!!', 403);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAlbumRequest $request, Album $album)
    {
        $user = $request->user();

        if ($user->id === $album->user_id) {
            $album->update($request->all());

            return new AlbumResource($album);
        } else  {
            return response('Unauthorized!!!', 403);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Album $album)
    {
        $user = $request->user();

        if ($user->id === $album->user_id) {
            $album->delete();

            return response('Album Deleted Successfully', 200);
        } else {
            return response('Unauthorized!!!', 403);
        }
    }
}
