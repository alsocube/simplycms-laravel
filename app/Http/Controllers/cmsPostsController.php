<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\cmsPostsModel;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        $post = new cmsPostsModel();
        $post->user_id = Auth::check() ? Auth::id() : 1999;
        $post->display_name = $request->input('display_name');
        $post->title = $request->input('post_title');
        $post->contents = $request->input('post_contents');

        if ($request->hasFile('post_file')) {

            $request->validate([
                'post_file' => [
                    File::types(['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])
                        ->max(4608)
                ]
            ]);

            $file = $request->file('post_file');

            try {
                $path = Storage::disk('supabase')->putFile('', $file);
                $post->file_name = $path;
                $post->file_path = "https://czvnfiithalbnojeucgz.supabase.co/storage/v1/object/public/posts/{$path}";
            } catch (\Exception $e) {
                // This will now output the SPECIFIC error from Supabase
                dd([
                    'Message' => $e->getMessage(),
                    'Endpoint' => config('filesystems.disks.supabase.endpoint'),
                    'Bucket' => config('filesystems.disks.supabase.bucket')
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
            Storage::disk('supabase')->delete($post->file_name);
            $post->delete();
        }
        return redirect('/')
            ->with('success', 'Post Deleted');
    }
}