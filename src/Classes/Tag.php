<?php

namespace Project5SlimBlog;
use Illuminate\Database\Eloquent\Model as Model;

class Tag extends Model {

   public $timestamps = false;
   //for exception handling testing purposes
   //protected $table = 'my_users';
   protected $fillable = ['title'];

   /**
     * The posts that belong to the tag.
     */
    public function posts()
    {
        return $this->belongsToMany('Project5SlimBlog\Post');
    }
}



?>
