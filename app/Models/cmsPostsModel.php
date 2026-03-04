<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class cmsPostsModel extends Model
{
    protected $table = 'cms_posts';
    protected $primaryKey = 'post_id';
    protected $fillable = [
        'post_id',
        'user_id',
        'display_name',
        'title',
        'contents',
        'file_path',
        'file_name'
    ];
    public $timestamps = true;
}
