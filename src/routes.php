<?php
// Routes
use Project5SlimBlog\Post;
use Project5SlimBlog\Comment;

/*-----------------------------------------------------------------------------------------------
Available routes:
  1. new post
  2. edit posts
  3. delete post
  4. show post details & its comments. And also handle the insert of new comments
  5. show full post list
-----------------------------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------------------------
1. ROUTE FOR NEW POST
-----------------------------------------------------------------------------------------------*/
$app->map(['GET','POST'],'/new', function ($request, $response, $args) {

  if($request->getMethod() == "POST") {
    $args = array_merge($args, $request->getParsedBody());
    $args = filter_var_array($args,FILTER_SANITIZE_STRING);

    $log = json_encode(["title: ".$args['title']]);
    if(!empty($args['title']) && !empty($args['body'])) {
      $args['date'] = date('Y-m-d H:i:s');
      unset($args['csrf_name']);
      unset($args['csrf_value']);
      try {
        //$args['slug'] = Post::slugify($args['title']);
        $post = new Post($args);
        $post->save();
        $post->slug = $args['title'];
        $post->save();

        $_SESSION['message']['content'] = 'Successfully added new Post "'.$args['title'].'"';
        $_SESSION['message']['type'] = 'success';
        $this->logger->notice("New post | SUCCESSFUL | $log");
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

  $nameKey = $this->csrf->getTokenNameKey();
  $valueKey = $this->csrf->getTokenValueKey();
  $csrf = [
    $nameKey => $request->getAttribute($nameKey),
    $valueKey => $request->getAttribute($valueKey)
  ];

  $message = array();
  if(!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
  }
  return $this->view->render($response, 'post_form.twig', [
   'csrf' => $csrf,
   'args' => $args,
   'message' => $message
  ]);
})->setName('new');

/*-----------------------------------------------------------------------------------------------
2. ROUTE FOR EDIT POST
-----------------------------------------------------------------------------------------------*/
$app->map(['GET','POST'],'/edit/{id}', function ($request, $response, $args) {
  $id = (int)$args['id'];

  if($request->getMethod() == "POST") {
    $args = array_merge($args, $request->getParsedBody());
    $args = filter_var_array($args,FILTER_SANITIZE_STRING);

    $log = json_encode(["title: ".$args['title']]);
    if(!empty($args['title']) && !empty($args['body'])) {
      $args['date'] = date('Y-m-d H:i:s');
      $args['slug'] = $args['title'];
      unset($args['id']);
      unset($args['csrf_name']);
      unset($args['csrf_value']);

      try {
        $post = Post::find($id);

        foreach($args as $key=>$value) {
          $post->$key = $value;
        }
        $post->save();
        $slug =  $post->slug;

        $_SESSION['message']['content'] = 'Successfully updated Post "'.$args['title'].'"';
        $_SESSION['message']['type'] = 'success';
        $this->logger->notice("Edit post: $id | SUCCESSFUL | $log");
        //to avoid resubmitting values:
        $url = $this->router->pathFor('post-detail',['slug' => $slug]);
        return $response->withStatus(302)->withHeader('Location',$url);
      } catch(\Exception $e){
          $_SESSION['message']['content'] = 'Something went wrong updating the post. Try again later.';
          $_SESSION['message']['type'] = 'error';
          $this->logger->notice("Edit post: $id | UNSUCCESSFUL | " . $e->getMessage());
      }
    }
    else {
      $_SESSION['message']['content'] = "All fields are required.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("New post | UNSUCCESSFUL | all fields required");
    }
  }
  else {
    try {
      $post = Post::find($id);
      $args = array_merge($args, $post->toArray());
      $this->logger->info("Edit post: $id | VIEW | SUCCESSFUL");
    } catch(\Exception $e){
        $_SESSION['message']['content'] = 'Something went wrong retrieving the post details. Try again later.';
        $_SESSION['message']['type'] = 'error';
        $this->logger->notice("Edit post: $id | VIEW | UNSUCCESSFUL | " . $e->getMessage());
    }
  }

  $nameKey = $this->csrf->getTokenNameKey();
  $valueKey = $this->csrf->getTokenValueKey();
  $csrf = [
    $nameKey => $request->getAttribute($nameKey),
    $valueKey => $request->getAttribute($valueKey)
  ];

  $message = array();
  if(!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
  }
  return $this->view->render($response, 'post_form.twig', [
   'csrf' => $csrf,
   'args' => $args,
   'message' => $message
  ]);
})->setName('edit');

/*-----------------------------------------------------------------------------------------------
3. ROUTE FOR DELETE POST
-----------------------------------------------------------------------------------------------*/
$app->post('/delete', function ($request, $response, $args) {
  if($request->getMethod() == "POST") {
    $args = $request->getParsedBody();
    $args = filter_var_array($args,FILTER_SANITIZE_NUMBER_INT);
    $id = (int)$args['delete'];

    if (!empty($id)) {
      try {
        $post = Post::find($id);
        $title = $post->title;
        $slug = $post->slug;
        $post = Post::find($id)->delete();
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
  } else {
    $this->logger->notice("Delete post: $id | UNSUCCESSFUL | Delete post only allowed via Post Method");
  }
  $_SESSION['message']['content'] = 'Something went wrong deleting the post. Try again later.';
  $_SESSION['message']['type'] = 'error';
  $url = $this->router->pathFor('post-detail',['slug' => $slug]);
  return $response->withStatus(302)->withHeader('Location',$url);
})->setName('delete');

/*-----------------------------------------------------------------------------------------------
4. ROUTE FOR DISPLAY POST DETAILS, ITS COMMENTS AND MANAGE INSERT OF NEW COMMENTS
-----------------------------------------------------------------------------------------------*/
$app->map(['GET','POST'],'/post/{slug}', function ($request, $response, $args) {
  $slug = (string)$args['slug'];
  //when a new comment was submitted:
  if($request->getMethod() == "POST") {
    $args = array_merge($args, $request->getParsedBody());
    $args = filter_var_array($args,FILTER_SANITIZE_STRING);
    $id = (int)$args['id'];

    $log = json_encode(["id: $id","name: ".$args['name']]);
    if(!empty($args['name']) && !empty($args['body'])) {
      $args['date'] = date('Y-m-d H:i:s');

      try {
        $comment = new Comment($args);
        $post = Post::find($id);
        $post->comments()->save($comment);
        $_SESSION['message']['content'] = 'Successfully added comment';
        $_SESSION['message']['type'] = 'success';
        $this->logger->notice("New comment | SUCCESSFUL | $log");
        //to avoid resubmitting values:
        $url = $this->router->pathFor('post-detail',['slug' => $slug]);
        return $response->withStatus(302)->withHeader('Location',$url);
      } catch(\Exception $e){
          $_SESSION['message']['content'] = 'Something went wrong adding the new comment. Try again later.';
          $_SESSION['message']['type'] = 'error';
          $this->logger->notice("New comment: $id | UNSUCCESSFUL | " . $e->getMessage());
      }
    }
    else {
      $_SESSION['message']['content'] = "All fields are required.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("New comment | UNSUCCESSFUL | all fields required");
    }
  }

  try {
    $post = Post::where('slug',$slug)->first();
    $comments = Post::find($id)->comments()->orderBy('date','desc')->get();
    //var_dump($post->title);
    //var_dump(Post::slugify($post->title));
    $this->logger->info("View post: $id | SUCCESSFUL");
  } catch(\Exception $e){
      $_SESSION['message']['content'] = 'Something went wrong retrieving the post and/or comments. Try again later.';
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("View post: $id | UNSUCCESSFUL | " . $e->getMessage());
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

  $message = array();
  if(!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
  }
  return $this->view->render($response, 'detail.twig', [
   'post' => $post,
   'comments' => $comments,
   'csrf_comment' => $csrf_comment,
   'csrf_delete' => $csrf_delete,
   'args' => $args,
   'message' => $message
  ]);
})->setName('post-detail');

/*-----------------------------------------------------------------------------------------------
5. ROUTE FOR POSTS LIST
-----------------------------------------------------------------------------------------------*/
$app->get('/[{posts}]', function ($request, $response, $args) {
    try {
      $posts = Post::orderBy('date','desc')->get();
      $this->logger->info("View posts list | SUCCESSFUL");
    } catch(\Exception $e){
       $_SESSION['message']['content'] = 'Something went wrong retrieving the posts. Try again later.';
       $_SESSION['message']['type'] = 'error';
       $this->logger->notice("View posts list | UNSUCCESSFUL | " . $e->getMessage());
    }

    $message = array();
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
