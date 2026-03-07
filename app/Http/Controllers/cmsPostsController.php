<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\cmsPostsModel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
        try {
            $validator = Validator::make($request->all(), [
                'display_name' => 'required|string|max:25',
                'post_title' => 'required|string|max:255',
                'post_file' => 'nullable|image|max:4096' // 4MB safe for serverless
            ], [
                'display_name.required' => 'Display Name is required',
                'display_name.max' => 'Display Name must be less than 25 characters',
                'post_title.required' => 'Title is required',
                'post_title.max' => 'Title must be less than 255 characters',
                'post_file.image' => 'Currently we only support img',
                'post_file.max' => 'The image size must not exceed 4MB',
            ]);

            if ($validator->fails()) {
                return redirect('/')
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $post = new cmsPostsModel();
                $post->user_id = Auth::check() ? Auth::id() : 1999;
                $post->display_name = $request->input('display_name');
                $post->title = $request->input('post_title');
                $post->contents = $request->input('post_contents');
        
                if ($request->hasFile('post_file')) {
                    $file = $request->file('post_file');
    
                    $path = $file->storeAs(
                        'posts',
                        Str::random(40) . '.' . $file->extension(),
                        'r2'
                    );
    
                    $post->file_name = $path;
                    $post->file_path = Storage::disk('r2')->url($path);
                }
        
                $post->save();
        
                return redirect('/')
                    ->with('success', 'Post Created');
            }
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
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
