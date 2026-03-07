<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\cmsPostsModel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class cmsPostsController extends Controller
{
    public function home(){
        return view('maintenance');
    }
    public function getPosts(Request $request)
    {
        $posts = cmsPostsModel::orderBy('created_at', 'desc')->get();
        return view('home', compact('posts'));
    }

    public function getPostbyID($id)
    {
        $post = cmsPostsModel::where('post_id', $id)->first();
        return $post;
    }

    public function createPost(Request $request)
    {
        // dd($request->all());
        // $request->validate([
        //     'post_file' => 'file|image|max:10240'
        // ]);
        $post = new cmsPostsModel();
        $post->user_id = Auth::check() ? Auth::id() : 1999;
        $post->display_name = $request->input('display_name');
        $post->title = $request->input('post_title');
        $post->contents = $request->input('post_contents');

        if ($request->hasFile('post_file') && $request->file('post_file')->isValid()) {

            $request->validate([
                'post_file' => [
                    \Illuminate\Validation\Rules\File::types(['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])
                        ->max(10240)
                ]
            ]);

            $compressor = new ImageManager(new Driver());
            $file = $request->file('post_file');
            $img = $compressor->read($file);
            $img->scaleDown(width: 1920);
            $compressedImg = $img->toJpeg(75);

            try {
                // 1. Generate a unique file name with a .jpg extension
                $fileName = 'posts/' . Str::random(40) . '.jpg';

                // 2. Use put() instead of putFile() and cast the image to a string
                Storage::disk('r2')->put($fileName, (string) $compressedImg);

                // 3. Save the exact generated string and URL
                $post->file_name = $fileName;
                $post->file_path = Storage::disk('r2')->url($fileName);
            } catch (\Exception $e) {
                dd([
                    'Message' => $e->getMessage(),
                    'Endpoint' => config('filesystems.disks.r2.endpoint'),
                    'Bucket' => config('filesystems.disks.r2.bucket')
                ]);
            }
        }

        $post->save();

        return redirect('/')
            ->with('success', 'Post Created');
    }

    public function deletePost(Request $request)
    {
        $post = cmsPostsModel::where('post_id', $request->input('id'))->first();
        if ($post) {
            if ($post->file_name) {
                Storage::disk('r2')->delete($post->file_name);
            }
            $post->delete();
        }
        return redirect('/')
            ->with('success', 'Post Deleted');
    }
}