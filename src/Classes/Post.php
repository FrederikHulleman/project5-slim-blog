<?php

namespace Project5SlimBlog;
use Illuminate\Database\Eloquent\Model as Model;

class Post extends Model {

   public $timestamps = false;
   //for exception handling testing purpose
   //protected $table = 'my_users';
   protected $fillable = ['title','body','date','slug'];
   //private $slug;

   public function comments()
    {
        return $this->hasMany('Project5SlimBlog\Comment');
    }

    public function delete()
    {
      $this->comments()->delete();
      parent::delete();
    }

    public function setSlugAttribute($value)
    {
        $slug = $this->slugify($value);

        $count = $this->where('slug','like', $slug . '%')->where('id','<>',$this->attributes['id'])->count();

        if ($count > 0) {
          $slug = $slug . '-' . $this->attributes['id'];
        }
        var_dump($count);
        var_dump($slug);

        $this->attributes['slug'] = $slug;
    }


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
