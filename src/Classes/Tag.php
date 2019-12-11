<?php

namespace Project5SlimBlog;
use Illuminate\Database\Eloquent\Model as Model;

class Tag extends Model {

  //no created & updated timestamps in this model
  public $timestamps = false;
  //the only allowable columns to be updated
  protected $fillable = ['name'];

  /**
   * The posts that belong to the tag.
   * default order by date desc
   */
  public function posts()
  {
      return $this->belongsToMany('Project5SlimBlog\Post')->orderBy('date','desc');
  }

  //if a tag is deleted, make sure the linked posts are detached
  public function delete()
  {
    $this->posts()->detach();
    parent::delete();
  }



}



?>
