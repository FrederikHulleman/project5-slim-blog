<?php

namespace Project5SlimBlog;
use Illuminate\Database\Eloquent\Model as Model;

class Comment extends Model {

   public $timestamps = false;
   protected $fillable = ['name','body','date','post_id'];


   /**
     * Get the posts that owns the comment.
     * default order by date desc 
     */
    public function posts()
    {
        return $this->belongsTo('Project5SlimBlog\Post')->orderBy('date','desc');
    }

}



?>
