<?php

namespace Project5SlimBlog;
use Illuminate\Database\Eloquent\Model as Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Post extends Model {

   use Sluggable;

   public $timestamps = false;
   //for exception handling testing purpose
   //protected $table = 'my_users';
   protected $fillable = ['title','body','date'];
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

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    // public static function getTitleAttribute($value)
    // {
    //   // replace non letter or digits by -
    //   $slug = preg_replace('~[^\pL\d]+~u', '-', $value);
    //
    //   // transliterate
    //   $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
    //
    //   // remove unwanted characters
    //   $slug = preg_replace('~[^-\w]+~', '', $slug);
    //
    //   // trim
    //   $slug = trim($slug, '-');
    //
    //   // remove duplicate -
    //   $slug = preg_replace('~-+~', '-', $slug);
    //
    //   // lowercase
    //   $slug = strtolower($slug);
    //
    //   if (empty($slug)) {
    //     return 'n-a';
    //   }
    //
    //   return $slug;
    // }

}

?>
