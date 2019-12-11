<?php

namespace Project5SlimBlog;
use Illuminate\Database\Eloquent\Model as Model;

class Post extends Model {

  //no created & updated timestamps in this model
  public $timestamps = false;
  //the only allowable columns to be updated
  protected $fillable = ['title','body','date','slug'];

   /**
     * Get the comments that belongs to the post.
     * default order by date desc
     */
    public function comments()
    {
        return $this->hasMany('Project5SlimBlog\Comment')->orderBy('date','desc');
    }

    /**
      * Get the tags that belongs to the post.
      * default order by name asc
      */
    public function tags()
    {
        return $this->belongsToMany('Project5SlimBlog\Tag')->orderBy('name','asc');
    }

    /**
      * When a post is deleted, also delete the linked comments and detach the linked tags
      */
    public function delete()
    {
      $this->comments()->delete();
      $this->tags()->detach();
      parent::delete();
    }

    /**
      * When a Post is created or updated, make sure the title is converted to a slug, and made unique, if necessary with the addition of it's ID
      */
    public function setSlugAttribute($value)
    {
        $slug = $this->slugify($value);

        //verify how many other posts have the same slug
        $count = $this->where('slug','like', $slug . '%')->where('id','<>',$this->attributes['id'])->count();

        //if other posts have the same slug, add the ID to the slug
        if(!empty($count) && $count > 0) {
          $slug = $slug . '-' . $this->attributes['id'];
        }

        $this->attributes['slug'] = $slug;
    }

    //convert a random text into a slug
    public function slugify($text)
    {

      // replace non letter or digits by -
      $text = preg_replace('~[^\pL\d]+~u', '-', $text);

      // transliterate
      $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

      // remove unwanted characters
      $text = preg_replace('~[^-\w]+~', '', $text);

      // trim
      $text = trim($text, '-');

      // remove duplicate -
      $text = preg_replace('~-+~', '-', $text);

      // lowercase
      $text = strtolower($text);

      if (empty($text)) {
        return 'n-a';
      }

      return $text;
    }

}

?>
