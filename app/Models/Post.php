<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $table = 'posts';
    protected $id = 'id';
    protected $fillable = ['title','is_active','created_by','category_id'];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\Models\User','created_by')->where('is_active',true);
    }

    public function comments()
    {
        return $this->hasMany('App\Models\PostComment','post_id')->orderBy('created_at','desc');
    }

    public function commentCount()
    {
            return $this->hasOne('App\Models\PostComment','post_id')->selectRaw('post_id, count(*) as count')->groupBy(['post_id','user_id']);
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category','category_id');
    }

    public function author(){
        return $this->belongsTo('App\Models\User','created_by');
    }

}
