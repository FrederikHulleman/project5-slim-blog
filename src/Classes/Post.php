<?php

namespace Project5SlimBlog;
use Illuminate\Database\Eloquent\Model as Model;

class Post extends Model {

   public $timestamps = false;
   //for exception handling testing purpose
   //protected $table = 'my_users';
   protected $fillable = ['title','body','date'];

   public function comments()
    {
        return $this->hasMany('Project5SlimBlog\Comment');
    }

    public function delete()
    {
      $this->comments()->delete();
      parent::delete();
    }

}

?>
