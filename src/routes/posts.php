<?php
// Routes
use Project5SlimBlog\Post;
use Project5SlimBlog\Comment;
use Project5SlimBlog\Tag;
/*-----------------------------------------------------------------------------------------------
Available routes:
  1. new post
  2. edit posts
  3. delete post
  4. show post details & its comments
  5. show full post list, optionally filtered by tag
-----------------------------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------------------------
1. ROUTE FOR NEW POST
-----------------------------------------------------------------------------------------------*/
$app->map(['GET','POST'],'/post/new', function ($request, $response, $args) {
  $csrf = $full_tags_list = $message = array();

  if($request->getMethod() == "POST") {
    $filters = array(
        'title'   => array(
                                'filter' => FILTER_SANITIZE_STRING,
                                'flags'  => FILTER_FLAG_NO_ENCODE_QUOTES,
                               ),
        'body'    => array(
                                'filter' => FILTER_SANITIZE_STRING,
                                'flags'  => FILTER_FLAG_NO_ENCODE_QUOTES,
                               ),
        'tags'     => array(
                            'filter' => FILTER_SANITIZE_NUMBER_INT,
                            'flags'  => FILTER_FORCE_ARRAY,
                           )
    );

    $args = array_merge($args, $request->getParsedBody());
    $args = filter_var_array($args,$filters);

    foreach($args as $key=>$value) {
      if(!is_array($value)) {
        $args[$key] = trim($value);
      }
      else {
        foreach($value as $key2 => $value2) {
          $args[$key][$key2] = trim($value2);
        }
      }
    }

    if(!empty($args['title']) && !empty($args['body'])) {
      $args['date'] = date('Y-m-d H:i:s');

      try {
        $post = new Post();
        $post_args = array_intersect_key($args,array_flip($post->getFillable()));

        foreach($post_args as $key=>$value) {
          $post->$key = $value;
        }
        $post->save();
        //after the 1st safe the slug is set, so that the post has an ID
        $post->slug = $args['title'];
        $post->save();
        $post->tags()->detach();
        $post->tags()->attach($args['tags']);

        $_SESSION['message']['content'] = 'Successfully added new Post "'.$args['title'].'"';
        $_SESSION['message']['type'] = 'success';
        $log = json_encode(["id: $post->id","title: ".$post->title]);
        $this->logger->notice("New post id: $post->id | SUCCESSFUL | $log");
        //to avoid resubmitting values:
        $url = $this->router->pathFor('posts-list');
        return $response->withStatus(302)->withHeader('Location',$url);
      } catch(\Exception $e){
          $_SESSION['message']['content'] = 'Something went wrong adding the new post. Try again later.';
          $_SESSION['message']['type'] = 'error';
          $this->logger->notice("New post | UNSUCCESSFUL | " . $e->getMessage());
      }
    }
    else {
      $_SESSION['message']['content'] = "All fields are required.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("New post | UNSUCCESSFUL | all fields required");
    }
  }

  //independent whether req method is GET or POST:

  try {
    $full_tags_list = Tag::orderBy('name','asc')->get();
  } catch(\Exception $e){
      $_SESSION['message']['content'] = 'Something went wrong retrieving the tags. No tags can be selected. Try again later.';
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("New post | VIEW | UNSUCCESSFUL | " . $e->getMessage());
  }

  $nameKey = $this->csrf->getTokenNameKey();
  $valueKey = $this->csrf->getTokenValueKey();
  $csrf = [
    $nameKey => $request->getAttribute($nameKey),
    $valueKey => $request->getAttribute($valueKey)
  ];

  if(!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
  }

  return $this->view->render($response, 'post_form.twig', [
   'csrf' => $csrf,
   'tags' => $full_tags_list,
   'args' => $args,
   'message' => $message
  ]);
})->setName('new-post');

/*-----------------------------------------------------------------------------------------------
2. ROUTE FOR EDIT POST
-----------------------------------------------------------------------------------------------*/
$app->map(['GET','POST'],'/post/edit/[{id}]', function ($request, $response, $args) {
  $csrf = $full_tags_list = $message = array();
  $id = "";

  if($request->getMethod() == "POST") {
    $filters = array(
        'title'   => array(
                                'filter' => FILTER_SANITIZE_STRING,
                                'flags'  => FILTER_FLAG_NO_ENCODE_QUOTES,
                               ),
        'body'    => array(
                                'filter' => FILTER_SANITIZE_STRING,
                                'flags'  => FILTER_FLAG_NO_ENCODE_QUOTES,
                               ),
        'tags'     => array(
                            'filter' => FILTER_SANITIZE_NUMBER_INT,
                            'flags'  => FILTER_FORCE_ARRAY,
                          ),
        'id'    => array(
                                'filter' => FILTER_SANITIZE_NUMBER_INT
                              )

    );

    $args = array_merge($args, $request->getParsedBody());
    $args = filter_var_array($args,$filters);

    foreach($args as $key=>$value) {
      if(!is_array($value)) {
        $args[$key] = trim($value);
      }
      else {
        foreach($value as $key2 => $value2) {
          $args[$key][$key2] = trim($value2);
        }
      }
    }

    $id = (int)$args['id'];

    if(!empty($id)) {

      if(!empty($args['title']) && !empty($args['body'])) {
        $args['date'] = date('Y-m-d H:i:s');
        $args['slug'] = $args['title'];

        try {
          $post = Post::findorfail($id);

          $post_args = array_intersect_key($args,array_flip($post->getFillable()));

          foreach($post_args as $key=>$value) {
            $post->$key = $value;
          }
          $post->save();
          $post->tags()->detach();
          $post->tags()->attach($args['tags']);

          $_SESSION['message']['content'] = 'Successfully updated Post "'.$args['title'].'"';
          $_SESSION['message']['type'] = 'success';
          $log = json_encode(["id: $post->id","title: ".$post->title]);
          $this->logger->notice("Edit post: $id | SUCCESSFUL | $log");
          //to avoid resubmitting values:
          $url = $this->router->pathFor('post-detail',['slug' => $post->slug]);
          return $response->withStatus(302)->withHeader('Location',$url);
        } catch(\Exception $e){
          //techical error; redirect to post list
            $_SESSION['message']['content'] = 'Something went wrong updating the post. Try again later.';
            $_SESSION['message']['type'] = 'error';
            $this->logger->notice("Edit post: $id | UNSUCCESSFUL | " . $e->getMessage());
            $url = $this->router->pathFor('posts-list');
            return $response->withStatus(302)->withHeader('Location',$url);
        }
      }
      else {
        //functional error; no redirect
        $_SESSION['message']['content'] = "All fields are required.";
        $_SESSION['message']['type'] = 'error';
        $this->logger->notice("Edit post | UNSUCCESSFUL | all fields required");
      }
    }
    else {
      //techical error; redirect to post list
      $_SESSION['message']['content'] = "Something went wrong updating the post. Try again later.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("Edit post | UNSUCCESSFUL | No valid ID");
      $url = $this->router->pathFor('posts-list');
      return $response->withStatus(302)->withHeader('Location',$url);
    }
  }
  else {
    //if req method = GET
    $id = trim(filter_var($args['id'],FILTER_SANITIZE_NUMBER_INT));

    if(!empty($id)) {
      try {
        $post = Post::findorfail($id);
        $tags = $post->tags;
        $args = array_merge($args, $post->toArray());
        foreach ($tags as $tag) {
          $args['tags'][] = $tag->id;
        }
        $this->logger->info("Edit post: $id | VIEW | SUCCESSFUL");
      } catch(\Exception $e){
          //techical error; redirect to post list
          $_SESSION['message']['content'] = 'Something went wrong retrieving the post details. Try again later.';
          $_SESSION['message']['type'] = 'error';
          $this->logger->notice("Edit post: $id | VIEW | UNSUCCESSFUL | " . $e->getMessage());
          $url = $this->router->pathFor('posts-list');
          return $response->withStatus(302)->withHeader('Location',$url);
      }
    }
    else {
      //techical error; redirect to post list
      $_SESSION['message']['content'] = "Something went wrong retrieving the post details. Try again later.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("Edit post | VIEW | UNSUCCESSFUL | No valid ID");
      $url = $this->router->pathFor('posts-list');
      return $response->withStatus(302)->withHeader('Location',$url);
    }
  }

  //independent whether req method is GET or POST:
  try {
    $full_tags_list = Tag::orderBy('name','asc')->get();
  } catch(\Exception $e){
      $_SESSION['message']['content'] = 'Something went wrong retrieving the tags. No tags can be selected. Try again later.';
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("New post | VIEW | UNSUCCESSFUL | no tags could be retrieved | " . $e->getMessage());
  }

  $nameKey = $this->csrf->getTokenNameKey();
  $valueKey = $this->csrf->getTokenValueKey();
  $csrf = [
    $nameKey => $request->getAttribute($nameKey),
    $valueKey => $request->getAttribute($valueKey)
  ];

  if(!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
  }
  return $this->view->render($response, 'post_form.twig', [
   'csrf' => $csrf,
   'tags' => $full_tags_list,
   'args' => $args,
   'message' => $message
  ]);
})->setName('edit-post');

/*-----------------------------------------------------------------------------------------------
3. ROUTE FOR DELETE POST
-----------------------------------------------------------------------------------------------*/
$app->post('/post/delete', function ($request, $response, $args) {
  $id = "";

  $args = $request->getParsedBody();
  $id = trim(filter_var($args['delete'],FILTER_SANITIZE_NUMBER_INT));

  if (!empty($id)) {
    try {
      $post = Post::findorfail($id);
      $title = $post->title;
      $slug = $post->slug;
      $post->delete();

      $_SESSION['message']['content'] = 'Successfully deleted Post "'.$title.'"';
      $_SESSION['message']['type'] = 'success';
      $this->logger->info("Delete post: $id | SUCCESSFUL");
      //to avoid resubmitting values:
      $url = $this->router->pathFor('posts-list');
      return $response->withStatus(302)->withHeader('Location',$url);
    } catch(\Exception $e){
       $this->logger->notice("Delete post: $id | UNSUCCESSFUL | " . $e->getMessage());
    }
  } else {
    $this->logger->notice("Delete post: $id | UNSUCCESSFUL | No valid ID");
  }

  $_SESSION['message']['content'] = 'Something went wrong deleting the post. Try again later.';
  $_SESSION['message']['type'] = 'error';
  $url = $this->router->pathFor('posts-list');
  return $response->withStatus(302)->withHeader('Location',$url);
})->setName('delete-post');

/*-----------------------------------------------------------------------------------------------
4. ROUTE FOR DISPLAY POST DETAILS, ITS COMMENTS
-----------------------------------------------------------------------------------------------*/
$app->get('/post/[{slug}]', function ($request, $response, $args) {
  $post = $csrf_comment = $csrf_delete = $message = array();
  $slug = "";

  $filters = array(
      'slug'    => array(
                              'filter' => FILTER_SANITIZE_STRING,
                              'flags'  => FILTER_FLAG_NO_ENCODE_QUOTES,
                            )
  );
  $args = filter_var_array($args,$filters);
  $args = array_map('trim',$args);

  $slug = $args['slug'];

  if(!empty($slug)) {

    //in case not all comment fields were submitted:
    if(!empty($_SESSION['comment']['name'])) {
      $args['name'] = $_SESSION['comment']['name'];
      unset($_SESSION['comment']['name']);
    }
    if(!empty($_SESSION['comment']['body'])) {
      $args['body'] = $_SESSION['comment']['body'];
      unset($_SESSION['comment']['body']);
    }

    try {
      $post = Post::where('slug',$slug)->firstorfail();
      $this->logger->info("View post: $post->id | SUCCESSFUL");
    } catch(\Exception $e){
      //techical error; redirect to post list
        $_SESSION['message']['content'] = 'Something went wrong retrieving the post and/or comments. Try again later.';
        $_SESSION['message']['type'] = 'error';
        $this->logger->notice("View post: $slug | UNSUCCESSFUL | " . $e->getMessage());
        $url = $this->router->pathFor('posts-list');
        return $response->withStatus(302)->withHeader('Location',$url);
    }

    $nameKey_comment = $this->csrf->getTokenNameKey();
    $valueKey_comment = $this->csrf->getTokenValueKey();
    $csrf_comment = [
      $nameKey_comment => $request->getAttribute($nameKey_comment),
      $valueKey_comment => $request->getAttribute($valueKey_comment)
    ];

    $nameKey_delete = $this->csrf->getTokenNameKey();
    $valueKey_delete = $this->csrf->getTokenValueKey();
    $csrf_delete = [
      $nameKey_delete => $request->getAttribute($nameKey_delete),
      $valueKey_delete => $request->getAttribute($valueKey_delete)
    ];

    if(!empty($_SESSION['message'])) {
      $message = $_SESSION['message'];
      unset($_SESSION['message']);
    }
    return $this->view->render($response, 'detail.twig', [
     'post' => $post,
     'csrf_comment' => $csrf_comment,
     'csrf_delete' => $csrf_delete,
     'args' => $args,
     'message' => $message
    ]);
  }
  else {
    //techical error; redirect to post list
    $_SESSION['message']['content'] = "Something went wrong retrieving the post details. Try again later.";
    $_SESSION['message']['type'] = 'error';
    $this->logger->notice("View post | VIEW | UNSUCCESSFUL | No valid Slug $slug");
    $url = $this->router->pathFor('posts-list');
    return $response->withStatus(302)->withHeader('Location',$url);
  }
})->setName('post-detail');

/*-----------------------------------------------------------------------------------------------
5. ROUTE FOR POSTS LIST, OPTIONALLY  FILTERED BY TAG
-----------------------------------------------------------------------------------------------*/
$app->get('/[posts[/[{tag}]]]', function ($request, $response, $args) {
  $posts = $message = array();
  $tag = "";

  $filters = array(
      'tag'    => array(
                              'filter' => FILTER_SANITIZE_STRING,
                              'flags'  => FILTER_FLAG_NO_ENCODE_QUOTES,
                            )
  );
  $args = filter_var_array($args,$filters);
  $args = array_map('trim',$args);

  $tag = $args['tag'];

  if(!empty($tag)) {
    try {
      $query = "lower(name) = lower('".$tag."')";
      $count_tags = Tag::whereRaw($query)->count();

      if (!empty($count_tags) && $count_tags > 0) {

        $count_posts = Tag::whereRaw($query)->first()->posts()->count();

        if (!empty($count_posts) && $count_posts > 0) {
          $posts = Tag::whereRaw($query)->first()->posts()->get();
          $this->logger->info("View posts list with tag \"$tag\" | SUCCESSFUL");
          return $this->view->render($response, 'blog.twig', [
            'posts' => $posts,
            'args' => $args,
            'message' => $message
          ]);
        }
        else {
          //tag exists, but no related posts
          $_SESSION['message']['content'] = "No posts are linked with tag \"$tag\". All posts are shown.";
          $_SESSION['message']['type'] = 'notice';
          $this->logger->notice("View posts list with tag \"$tag\" | NOTICE | Tag exits, but without linked posts; all posts are shown");
        }
      }
      else {
        //tag doesn't exist
        $_SESSION['message']['content'] = "Tag \"$tag\" does not exist. All posts are shown.";
        $_SESSION['message']['type'] = 'error';
        $this->logger->notice("View posts list with tag \"$tag\" | UNSUCCESSFUL | Tag doesn't exit; all posts are shown");
      }
    }
    catch(\Exception $e){
      $_SESSION['message']['content'] = "Something went wrong retrieving the posts with tag $tag. All posts are shown.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("View posts list with tag \"$tag\" | UNSUCCESSFUL | " . $e->getMessage());
    }
    $url = $this->router->pathFor('posts-list');
    return $response->withStatus(302)->withHeader('Location',$url);
  }

  try {
    $posts = Post::orderBy('date','desc')->get();
    $this->logger->info("View posts list | SUCCESSFUL");
  }
  catch(\Exception $e){
     $_SESSION['message']['content'] = 'Something went wrong retrieving the posts. Try again later.';
     $_SESSION['message']['type'] = 'error';
     $this->logger->notice("View posts list | UNSUCCESSFUL | " . $e->getMessage());
  }

  if(!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
  }
  return $this->view->render($response, 'blog.twig', [
    'posts' => $posts,
    'args' => $args,
    'message' => $message
  ]);

})->setName('posts-list');
