<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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
            $url = "https://api.cloudflare.com/client/v4/accounts/{$accountId}/r2/buckets/{$bucketName}/usage";

            $r2Usage = Http::withToken($apiToken)->get($url);
            $storageSize = 'Unknown';
            
            if ($r2Usage->successful()) {
                $bytes = $r2Usage->json('result.payloadSize', 0);
                $storageSize = $this->formatBytes($bytes);
            }
            // dd([
            //     // 'HTTP Status Code' => $response->status(),
            //     'Raw Body' => $r2Usage->body(),
            //     // 'Posts' => $postsJSON
            // ]);
            return view('admin', compact('posts', 'storageSize'));
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

    private function formatBytes($bytes, $precision = 2) 
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
