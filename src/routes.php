<?php
// Routes
use Project5SlimBlog\Post;
use Project5SlimBlog\Comment;

$app->map(['GET','POST'],'/new', function ($request, $response, $args) {

  if($request->getMethod() == "POST") {
    $args = array_merge($args, $request->getParsedBody());
    $args = filter_var_array($args,FILTER_SANITIZE_STRING);

    $log = json_encode(["title: ".$args['title']]);
    if(!empty($args['title']) && !empty($args['body'])) {
      $args['date'] = date('Y-m-d H:i:s');

      try {
        $post = Post::create($args);
        $this->logger->notice("New post | SUCCESSFUL | $log");
        //to avoid resubmitting values:
        $url = $this->router->pathFor('posts-list');
        return $response->withStatus(302)->withHeader('Location',$url);
      } catch(\Exception $e){
          $args['msg_content'] = 'Something went wrong adding the new post. Try again later.';
          $args['msg_type'] = 'error';
          $this->logger->notice("New post | UNSUCCESSFUL | " . $e->getMessage());
      }
    }
    else {
      $args['msg_content'] = "All fields are required.";
      $args['msg_type'] = 'error';
      $this->logger->notice("New post | UNSUCCESSFUL | all fields required");
    }
  }

  $nameKey = $this->csrf->getTokenNameKey();
  $valueKey = $this->csrf->getTokenValueKey();
  $csrf = [
    $nameKey => $request->getAttribute($nameKey),
    $valueKey => $request->getAttribute($valueKey)
  ];

  return $this->view->render($response, 'post_form.twig', [
   'csrf' => $csrf,
   'args' => $args
  ]);
})->setName('new-post');

$app->map(['GET','POST'],'/edit/{id}', function ($request, $response, $args) {
  $id = (int)$args['id'];

  if($request->getMethod() == "POST") {
    $args = array_merge($args, $request->getParsedBody());
    $args = filter_var_array($args,FILTER_SANITIZE_STRING);

    $log = json_encode(["title: ".$args['title']]);
    if(!empty($args['title']) && !empty($args['body'])) {
      $args['date'] = date('Y-m-d H:i:s');
      unset($args['id']);
      unset($args['csrf_name']);
      unset($args['csrf_value']);

      try {
        $post = Post::where('id',$id)->update($args);
        $this->logger->notice("Edit post: $id | SUCCESSFUL | $log");
        //to avoid resubmitting values:
        $url = $this->router->pathFor('post-detail',['id' => $id]);
        return $response->withStatus(302)->withHeader('Location',$url);
      } catch(\Exception $e){
          $args['msg_content'] = 'Something went wrong updating the post. Try again later.';
          $args['msg_type'] = 'error';
          $this->logger->notice("Edit post: $id | UNSUCCESSFUL | " . $e->getMessage());
      }
    }
    else {
      $args['msg_content'] = "All fields are required.";
      $args['msg_type'] = 'error';
      $this->logger->notice("New post | UNSUCCESSFUL | all fields required");
    }
  }
  else {
    try {
      $post = Post::find($id);
      $args = array_merge($args, $post->toArray());
      $this->logger->info("Edit post: $id | VIEW | SUCCESSFUL");
    } catch(\Exception $e){
        $args['msg_content'] = 'Something went wrong retrieving the post details. Try again later.';
        $args['msg_type'] = 'error';
        $this->logger->notice("Edit post: $id | VIEW | UNSUCCESSFUL | " . $e->getMessage());
    }
  }

  $nameKey = $this->csrf->getTokenNameKey();
  $valueKey = $this->csrf->getTokenValueKey();
  $csrf = [
    $nameKey => $request->getAttribute($nameKey),
    $valueKey => $request->getAttribute($valueKey)
  ];

  return $this->view->render($response, 'post_form.twig', [
   'csrf' => $csrf,
   'args' => $args
  ]);
})->setName('edit-post');

$app->map(['GET','POST'],'/post/{id}', function ($request, $response, $args) {
  $id = (int)$args['id'];

  //when a new comment was submitted:
  if($request->getMethod() == "POST") {
    $args = array_merge($args, $request->getParsedBody());
    $args = filter_var_array($args,FILTER_SANITIZE_STRING);

    $log = json_encode(["id: $id","name: ".$args['name']]);
    if(!empty($args['name']) && !empty($args['body'])) {
      $args['date'] = date('Y-m-d H:i:s');

      try {
        $comment = new Comment($args);
        $post = Post::find($id);
        $post->comments()->save($comment);
        $this->logger->notice("New comment | SUCCESSFUL | $log");
        //to avoid resubmitting values:
        $url = $this->router->pathFor('post-detail',['id' => $id]);
        return $response->withStatus(302)->withHeader('Location',$url);
      } catch(\Exception $e){
          $args['msg_content'] = 'Something went wrong adding the new comment. Try again later.';
          $args['msg_type'] = 'error';
          $this->logger->notice("New comment: $id | UNSUCCESSFUL | " . $e->getMessage());
      }
    }
    else {
      $args['msg_content'] = "All fields are required.";
      $args['msg_type'] = 'error';
      $this->logger->notice("New comment | UNSUCCESSFUL | all fields required");
    }
  }

  //$post = $this->db->table('posts')->find($id);
  //$comments = $this->db->table('comments')
  //  ->where('id',$id)
  //  ->orderBy('date', 'desc')
  //  ->get();
  try {
    $post = Post::find($id);
    $comments = Comment::where('post_id',$id)->orderBy('date','desc')->get();
    $this->logger->info("View post: $id | SUCCESSFUL");
  } catch(\Exception $e){
      $args['msg_content'] = 'Something went wrong retrieving the post and/or comments. Try again later.';
      $args['msg_type'] = 'error';
      $this->logger->notice("View post: $id | UNSUCCESSFUL | " . $e->getMessage());
  }

  $nameKey = $this->csrf->getTokenNameKey();
  $valueKey = $this->csrf->getTokenValueKey();
  $csrf = [
    $nameKey => $request->getAttribute($nameKey),
    $valueKey => $request->getAttribute($valueKey)
  ];

  return $this->view->render($response, 'detail.twig', [
   'post' => $post,
   'comments' => $comments,
   'csrf' => $csrf,
   'args' => $args
  ]);
})->setName('post-detail');

$app->get('/[{posts}]', function ($request, $response, $args) {
    //$posts = $this->db->table('posts')->get();
    try {
      $posts = Post::orderBy('date','desc')->get();
      $this->logger->info("View posts list | SUCCESSFUL");
    } catch(\Exception $e){
       $args['msg_content'] = 'Something went wrong retrieving the posts. Try again later.';
       $args['msg_type'] = 'error';
       $this->logger->notice("View posts list | UNSUCCESSFUL | " . $e->getMessage());
    }
    return $this->view->render($response, 'blog.twig', [
      'posts' => $posts,
      'args' => $args
    ]);
})->setName('posts-list');
