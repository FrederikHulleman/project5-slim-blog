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
  //initialize variables for the templates and the 'key elements' for this route to succeed
  $csrf = $full_tags_list = $message = array();

  //check whether the user posted data
  if($request->getMethod() == "POST") {
    //filter settings for all args from POST & GET
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
    //combine args from GET with args from POST
    $args = array_merge($args, $request->getParsedBody());
    //apply filters from array to all args
    $args = filter_var_array($args,$filters);

    //apply trim to all elements in the args array, also for args which are an array themselves
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
      //set date to now!
      $args['date'] = date('Y-m-d H:i:s');

      try {
        $post = new Post();
        //make sure only the 'fillable' args remain
        $post_args = array_intersect_key($args,array_flip($post->getFillable()));
        //set all properties
        foreach($post_args as $key=>$value) {
          $post->$key = $value;
        }
        $post->save();
        //after the 1st safe the slug is set, so that the post has an ID
        $post->slug = $args['title'];
        $post->save();
        //remove all linked tags and then, link all new selected tags
        $post->tags()->detach();
        $post->tags()->attach($args['tags']);

        //logging & messaging to the user
        $_SESSION['message']['content'] = 'Successfully added new Post "'.$args['title'].'"';
        $_SESSION['message']['type'] = 'success';
        $log = json_encode(["id: $post->id","title: ".$post->title]);
        $this->logger->notice("New post id: $post->id | SUCCESSFUL | $log");
        //determine the right redirect
        $url = $this->router->pathFor('posts-list');
        return $response->withStatus(302)->withHeader('Location',$url);
      } catch(\Exception $e){
          //logging & messaging to the user
          $_SESSION['message']['content'] = 'Something went wrong adding the new post. Try again later.';
          $_SESSION['message']['type'] = 'error';
          $this->logger->notice("New post | UNSUCCESSFUL | " . $e->getMessage());
      }
    }
    else {
      //logging & messaging to the user
      $_SESSION['message']['content'] = "All fields are required.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("New post | UNSUCCESSFUL | all fields required");
    }
  }

  //independent whether req method is GET or POST:
  try {
    //retrieve all tags for add form
    $full_tags_list = Tag::orderBy('name','asc')->get();
  } catch(\Exception $e){
      //logging & messaging to the user
      $_SESSION['message']['content'] = 'Something went wrong retrieving the tags. No tags can be selected. Try again later.';
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("New post | VIEW | UNSUCCESSFUL | " . $e->getMessage());
  }

  //csrf settings for form
  $nameKey = $this->csrf->getTokenNameKey();
  $valueKey = $this->csrf->getTokenValueKey();
  $csrf = [
    $nameKey => $request->getAttribute($nameKey),
    $valueKey => $request->getAttribute($valueKey)
  ];

  //if a message in the session is set, it has to be diplayed
  //therefore it's copied to the $message variable and passed to the template
  //and the session variable is cleaned up, to avoid repetition of the same message
  if(!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
  }

  //pass all necessary variables to the template:
  // - csrf settings
  // - all available tags
  // - the arguments from GET and POST
  // - optionally a message to show to the user
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
  //initialize variables for the templates and the 'key elements' for this route to succeed
  $csrf = $full_tags_list = $message = array();
  $id = "";

  //check whether the user posted data
  if($request->getMethod() == "POST") {
    //filter settings for all args from POST & GET
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

    //combine args from GET with args from POST
    $args = array_merge($args, $request->getParsedBody());
    //apply filters from array to all args
    $args = filter_var_array($args,$filters);

    //apply trim to all elements in the args array, also for args which are an array themselves
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
        //set date to now
        $args['date'] = date('Y-m-d H:i:s');
        //input for slug is the title, so it can be converted to a proper slug
        $args['slug'] = $args['title'];

        try {
          //search post which the user wants to edit
          $post = Post::findorfail($id);
          //make sure only the 'fillable' args remain
          $post_args = array_intersect_key($args,array_flip($post->getFillable()));
          //set all properties
          foreach($post_args as $key=>$value) {
            $post->$key = $value;
          }
          $post->save();
          //remove all linked tags and then, link all new selected tags
          $post->tags()->detach();
          $post->tags()->attach($args['tags']);

          //logging & messaging to the user
          $_SESSION['message']['content'] = 'Successfully updated Post "'.$args['title'].'"';
          $_SESSION['message']['type'] = 'success';
          $log = json_encode(["id: $post->id","title: ".$post->title]);
          $this->logger->notice("Edit post: $id | SUCCESSFUL | $log");
          //determine the right redirect
          $url = $this->router->pathFor('post-detail',['slug' => $post->slug]);
          return $response->withStatus(302)->withHeader('Location',$url);
        } catch(\Exception $e){
            //logging & messaging to the user
            //techical error; redirect to post list
            $_SESSION['message']['content'] = 'Something went wrong updating the post. Try again later.';
            $_SESSION['message']['type'] = 'error';
            $this->logger->notice("Edit post: $id | UNSUCCESSFUL | " . $e->getMessage());
            //determine the right redirect
            $url = $this->router->pathFor('posts-list');
            return $response->withStatus(302)->withHeader('Location',$url);
        }
      }
      else {
        //logging & messaging to the user
        //functional error; no redirect
        $_SESSION['message']['content'] = "All fields are required.";
        $_SESSION['message']['type'] = 'error';
        $this->logger->notice("Edit post | UNSUCCESSFUL | all fields required");
      }
    }
    else {
      //logging & messaging to the user
      //techical error; redirect to post list
      $_SESSION['message']['content'] = "Something went wrong updating the post. Try again later.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("Edit post | UNSUCCESSFUL | No valid ID");
      //determine the right redirect
      $url = $this->router->pathFor('posts-list');
      return $response->withStatus(302)->withHeader('Location',$url);
    }
  }
  else {
    //if req method is not POST, but e.g. GET when the page is originally requested to edit the post
    $id = trim(filter_var($args['id'],FILTER_SANITIZE_NUMBER_INT));

    if(!empty($id)) {
      try {
        //search post which the user wants to edit
        $post = Post::findorfail($id);
        //retrieve all associated tags of the post
        $tags = $post->tags;
        //combine the GET args with the properties of the post in 1 array, so they are pre filled in the edit form
        $args = array_merge($args, $post->toArray());
        //add the associated tags to the args array, so they are pre selected in the edit form
        foreach ($tags as $tag) {
          $args['tags'][] = $tag->id;
        }
        //logging
        $this->logger->info("Edit post: $id | VIEW | SUCCESSFUL");
      } catch(\Exception $e){
          //logging & messaging to the user
          //techical error; redirect to post list
          $_SESSION['message']['content'] = 'Something went wrong retrieving the post details. Try again later.';
          $_SESSION['message']['type'] = 'error';
          $this->logger->notice("Edit post: $id | VIEW | UNSUCCESSFUL | " . $e->getMessage());
          //determine the right redirect
          $url = $this->router->pathFor('posts-list');
          return $response->withStatus(302)->withHeader('Location',$url);
      }
    }
    else {
      //logging & messaging to the user
      //techical error; redirect to post list
      $_SESSION['message']['content'] = "Something went wrong retrieving the post details. Try again later.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("Edit post | VIEW | UNSUCCESSFUL | No valid ID");
      //determine the right redirect
      $url = $this->router->pathFor('posts-list');
      return $response->withStatus(302)->withHeader('Location',$url);
    }
  }

  //independent whether req method is GET or POST:
  try {
    //retrieve full list of tags for the edit form
    $full_tags_list = Tag::orderBy('name','asc')->get();
  } catch(\Exception $e){
      //logging & messaging to the user
      $_SESSION['message']['content'] = 'Something went wrong retrieving the tags. No tags can be selected. Try again later.';
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("New post | VIEW | UNSUCCESSFUL | no tags could be retrieved | " . $e->getMessage());
  }

  //csrf settings for form
  $nameKey = $this->csrf->getTokenNameKey();
  $valueKey = $this->csrf->getTokenValueKey();
  $csrf = [
    $nameKey => $request->getAttribute($nameKey),
    $valueKey => $request->getAttribute($valueKey)
  ];

  //if a message in the session is set, it has to be diplayed
  //therefore it's copied to the $message variable and passed to the template
  //and the session variable is cleaned up, to avoid repetition of the same message
  if(!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
  }

  //pass all necessary variables to the template:
  // - csrf settings
  // - all available tags
  // - the arguments from GET and POST
  // - optionally a message to show to the user
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
  //initialize variables for the templates and the 'key elements' for this route to succeed
  $id = "";

  //get POST args
  $args = $request->getParsedBody();
  //filter settings for all args from POST & GET
  $id = trim(filter_var($args['delete'],FILTER_SANITIZE_NUMBER_INT));

  if (!empty($id)) {
    try {
      //select the post the user wants to delete
      $post = Post::findorfail($id);
      $title = $post->title;
      $slug = $post->slug;
      $post->delete();

      //logging & messaging to the user
      $_SESSION['message']['content'] = 'Successfully deleted Post "'.$title.'"';
      $_SESSION['message']['type'] = 'success';
      $this->logger->info("Delete post: $id | SUCCESSFUL");
      //determine the right redirect
      $url = $this->router->pathFor('posts-list');
      return $response->withStatus(302)->withHeader('Location',$url);
    } catch(\Exception $e){
       //logging
       $this->logger->notice("Delete post: $id | UNSUCCESSFUL | " . $e->getMessage());
    }
  } else {
    //logging
    $this->logger->notice("Delete post: $id | UNSUCCESSFUL | No valid ID");
  }
  //messaging to the user
  $_SESSION['message']['content'] = 'Something went wrong deleting the post. Try again later.';
  $_SESSION['message']['type'] = 'error';
  //determine the right redirect
  $url = $this->router->pathFor('posts-list');
  return $response->withStatus(302)->withHeader('Location',$url);
})->setName('delete-post');

/*-----------------------------------------------------------------------------------------------
4. ROUTE FOR DISPLAY POST DETAILS, ITS COMMENTS
-----------------------------------------------------------------------------------------------*/
$app->get('/post/[{slug}]', function ($request, $response, $args) {
  //initialize variables for the templates and the 'key elements' for this route to succeed
  $post = $csrf_comment = $csrf_delete = $message = array();
  $slug = "";

  //filter settings for all args from POST & GET
  $filters = array(
      'slug'    => array(
                              'filter' => FILTER_SANITIZE_STRING,
                              'flags'  => FILTER_FLAG_NO_ENCODE_QUOTES,
                            )
  );
  //apply filters from array to all args
  $args = filter_var_array($args,$filters);
  //apply filters from array to all args
  $args = array_map('trim',$args);

  //save the slug from the GET, for redirect purposes
  $slug = $args['slug'];

  if(!empty($slug)) {

    //in case not all comment fields were submitted, the values which were stored are available from the comment/new route in the session
    if(!empty($_SESSION['comment']['name'])) {
      $args['name'] = $_SESSION['comment']['name'];
      unset($_SESSION['comment']['name']);
    }
    if(!empty($_SESSION['comment']['body'])) {
      $args['body'] = $_SESSION['comment']['body'];
      unset($_SESSION['comment']['body']);
    }

    try {
      //select the post based on the navigated slug
      $post = Post::where('slug',$slug)->firstorfail();
      //logging
      $this->logger->info("View post: $post->id | SUCCESSFUL");
    } catch(\Exception $e){
        //logging & messaging to the user
        //techical error; redirect to post list
        $_SESSION['message']['content'] = 'Something went wrong retrieving the post and/or comments. Try again later.';
        $_SESSION['message']['type'] = 'error';
        $this->logger->notice("View post: $slug | UNSUCCESSFUL | " . $e->getMessage());
        //determine the right redirect
        $url = $this->router->pathFor('posts-list');
        return $response->withStatus(302)->withHeader('Location',$url);
    }

    //csrf settings for comment form
    $nameKey_comment = $this->csrf->getTokenNameKey();
    $valueKey_comment = $this->csrf->getTokenValueKey();
    $csrf_comment = [
      $nameKey_comment => $request->getAttribute($nameKey_comment),
      $valueKey_comment => $request->getAttribute($valueKey_comment)
    ];

    //csrf settings for delete post form
    $nameKey_delete = $this->csrf->getTokenNameKey();
    $valueKey_delete = $this->csrf->getTokenValueKey();
    $csrf_delete = [
      $nameKey_delete => $request->getAttribute($nameKey_delete),
      $valueKey_delete => $request->getAttribute($valueKey_delete)
    ];

    //if a message in the session is set, it has to be diplayed
    //therefore it's copied to the $message variable and passed to the template
    //and the session variable is cleaned up, to avoid repetition of the same message
    if(!empty($_SESSION['message'])) {
      $message = $_SESSION['message'];
      unset($_SESSION['message']);
    }

    //pass all necessary variables to the template:
    // - the details of the selected post
    // - csrf settings for comment form
    // - csrf settings for delete form
    // - the arguments from GET and POST
    // - optionally a message to show to the user
    return $this->view->render($response, 'detail.twig', [
     'post' => $post,
     'csrf_comment' => $csrf_comment,
     'csrf_delete' => $csrf_delete,
     'args' => $args,
     'message' => $message
    ]);
  }
  else {
    //logging & messaging to the user
    //techical error; redirect to post list
    $_SESSION['message']['content'] = "Something went wrong retrieving the post details. Try again later.";
    $_SESSION['message']['type'] = 'error';
    $this->logger->notice("View post | VIEW | UNSUCCESSFUL | No valid Slug $slug");
    //determine the right redirect
    $url = $this->router->pathFor('posts-list');
    return $response->withStatus(302)->withHeader('Location',$url);
  }
})->setName('post-detail');

/*-----------------------------------------------------------------------------------------------
5. ROUTE FOR POSTS LIST, OPTIONALLY  FILTERED BY TAG
-----------------------------------------------------------------------------------------------*/
$app->get('/[posts[/[{tag}]]]', function ($request, $response, $args) {
  //initialize variables for the templates and the 'key elements' for this route to succeed
  $posts = $message = array();
  $tag = "";

  //filter settings for all args from POST & GET
  $filters = array(
      'tag'    => array(
                              'filter' => FILTER_SANITIZE_STRING,
                              'flags'  => FILTER_FLAG_NO_ENCODE_QUOTES,
                            )
  );
  //apply filters from array to all args
  $args = filter_var_array($args,$filters);
  //apply trim to all elements in the args array
  $args = array_map('trim',$args);

  $tag = $args['tag'];

  if(!empty($tag)) {
    try {
      //count the number of tags based on the name in the GET args
      $query = "lower(name) = lower('".$tag."')";
      $count_tags = Tag::whereRaw($query)->count();

      //if more than 0 tags were found
      if (!empty($count_tags) && $count_tags > 0) {
        //count the number of related posts of the tag
        $count_posts = Tag::whereRaw($query)->first()->posts()->count();

        //if more than 0 posts were found
        if (!empty($count_posts) && $count_posts > 0) {
          //retrieve the related posts
          $posts = Tag::whereRaw($query)->first()->posts()->get();
          //logging
          $this->logger->info("View posts list with tag \"$tag\" | SUCCESSFUL");

          //pass all necessary variables to the template:
          // - the properties of the selected posts
          // - the arguments from GET and POST
          // - optionally a message to show to the user
          return $this->view->render($response, 'blog.twig', [
            'posts' => $posts,
            'args' => $args,
            'message' => $message
          ]);
        }
        else {
          //logging & messaging to the user
          //tag exists, but no related posts
          $_SESSION['message']['content'] = "No posts are linked with tag \"$tag\". All posts are shown.";
          $_SESSION['message']['type'] = 'notice';
          $this->logger->notice("View posts list with tag \"$tag\" | NOTICE | Tag exits, but without linked posts; all posts are shown");
        }
      }
      else {
        //logging & messaging to the user
        //tag doesn't exist
        $_SESSION['message']['content'] = "Tag \"$tag\" does not exist. All posts are shown.";
        $_SESSION['message']['type'] = 'error';
        $this->logger->notice("View posts list with tag \"$tag\" | UNSUCCESSFUL | Tag doesn't exit; all posts are shown");
      }
    }
    catch(\Exception $e){
      //logging & messaging to the user
      $_SESSION['message']['content'] = "Something went wrong retrieving the posts with tag $tag. All posts are shown.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("View posts list with tag \"$tag\" | UNSUCCESSFUL | " . $e->getMessage());
    }
    //determine the right redirect, if based on the tag no posts could be retrieved
    $url = $this->router->pathFor('posts-list');
    return $response->withStatus(302)->withHeader('Location',$url);
  }

  //if no tag was submitted:
  try {
    //retireve all posts
    $posts = Post::orderBy('date','desc')->get();
    //logging
    $this->logger->info("View posts list | SUCCESSFUL");
  }
  catch(\Exception $e){
     //logging & messaging to the user
     $_SESSION['message']['content'] = 'Something went wrong retrieving the posts. Try again later.';
     $_SESSION['message']['type'] = 'error';
     $this->logger->notice("View posts list | UNSUCCESSFUL | " . $e->getMessage());
  }

  //if a message in the session is set, it has to be diplayed
  //therefore it's copied to the $message variable and passed to the template
  //and the session variable is cleaned up, to avoid repetition of the same message
  if(!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
  }

  //pass all necessary variables to the template:
  // - the properties of the selected posts
  // - the arguments from GET and POST
  // - optionally a message to show to the user
  return $this->view->render($response, 'blog.twig', [
    'posts' => $posts,
    'args' => $args,
    'message' => $message
  ]);

})->setName('posts-list');
