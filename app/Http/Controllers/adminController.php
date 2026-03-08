<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\cmsPostsModel;
use App\Http\Controllers\cmsPostsController;

class adminController extends Controller
{
    public function getAdmin(Request $request) 
    {
        if (Auth::check() && Auth::user()->access == "admin") {
            $posts = cmsPostsModel::orderBy('created_at', 'desc')
                ->get();
            
            $accountId = env('CLOUDFLARE_ACCOUNT_ID');
            $bucketName = env('CLOUDFLARE_R2_BUCKET');
            $apiToken = env('CLOUDFLARE_API_TOKEN');
            $url = "https://api.cloudflare.com/client/v4/accounts/{$accountId}/r2/buckets/{$bucketName}/usage?=null";

            $r2Usage = Http::withToken($apiToken)->get($url);
            $storageSize = 'Unknown';
            
            if ($r2Usage->successful()) {
                $bytes = $r2Usage->json('result.payloadSize', 0);
                $storageSize = round($bytes / 1000000, 2) . ' MB';
                $rawEnd = $r2Usage->json('result.end');
                $end = $rawEnd ? Carbon::parse($rawEnd)->format('M d, Y H:i') : 'N/A';
            }
            // dd([
            //     // 'HTTP Status Code' => $response->status(),
            //     'Raw Body' => $r2Usage->body(),
            //     // 'Posts' => $postsJSON
            // ]);
            return view('admin', compact('posts', 'storageSize', 'end'));
        }
        return redirect('/')->withErrors('Unauthorized access.');
    }

    public function r2Usage() {
        $accountId = env('CLOUDFLARE_ACCOUNT_ID');
        $bucketName = env('CLOUDFLARE_R2_BUCKET');
        $apiToken = env('CLOUDFLARE_API_TOKEN');
        $url = "https://api.cloudflare.com/client/v4/accounts/{$accountId}/r2/buckets/{$bucketName}/usage";
        $r2Usage = Http::withToken($apiToken)->get($url);
        if ($r2Usage->successful()) {
            return $r2Usage->json('result');
        }
    }
}
