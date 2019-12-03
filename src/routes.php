<?php
// Routes
use Project5SlimBlog\Post;
use Project5SlimBlog\Comment;

$app->get('/[{posts}]', function ($request, $response, $args) {
    $this->logger->info("Posts list");
    $post_model = new Post();
    $posts = $post_model->all();
    //$posts = $this->db->table('posts')->get();
    // $post_mapper = new PostMapper($this->db);
    // $post_mapper->selectPosts();
    var_dump($posts);
    return $this->view->render($response, 'blog.twig', [
      'posts' => $posts
    ]);
})->setName('posts-list');

/*
$app->map(['GET','POST'],'/post/new', function ($request, $response, $args) {

  if($request->getMethod() == "POST") {

    $args = array_merge($args, $request->getParsedBody());
    $args = filter_var_array($args,FILTER_SANITIZE_STRING);

    $log = json_encode(["title: ".$args['title']]);
    if(!empty($args['title']) && !empty($args['body'])) {
      $post_mapper = new PostMapper($this->db);

      if($post_mapper->insert($args)) {
          $this->logger->notice("New post: SUCCESFUL | $log");
          //to avoid resubmitting values:
          $url = $this->router->pathFor('posts-list');
          return $response->withStatus(302)->withHeader('Location',$url);
        } else {
          $args['error'] = $post_mapper->getAlert()[0]['message'];
          $this->logger->notice("New post: UNSUCCESFUL | $log");
        }
      }
      else {
        $args['error'] = "all fields required";
        $this->logger->notice("New post: UNSUCCESFUL | $log");
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

$app->map(['GET','POST'],'/post/edit/{post_id}', function ($request, $response, $args) {
  $post_id = (int)$args['post_id'];

  $post_mapper = new PostMapper($this->db);
  $post_mapper->selectPosts($post_id);

  if($request->getMethod() == "POST") {
    $args = array_merge($args, $request->getParsedBody());
    $args = filter_var_array($args,FILTER_SANITIZE_STRING);

    $log = json_encode(["post_id: $post_id","title: ".$args['title']]);
    if(!empty($args['title']) && !empty($args['body'])) {
      if($count = $post_mapper->update($args)) {
          $this->logger->notice("Update post: SUCCESFUL | $log");
          //to avoid resubmitting values:
          $url = $this->router->pathFor('post-detail',['post_id' => $post_id]);
          return $response->withStatus(302)->withHeader('Location',$url);
        } else {
          $args['error'] = $post_mapper->getAlert()[0]['message'];
          $this->logger->notice("Update post: UNSUCCESFUL | $log");
        }
      }
      else {
        $args['error'] = "all fields required";
        $this->logger->notice("Update post: UNSUCCESFUL | $log");
      }
  }
  else {
    $args = array_merge($args, $post_mapper->posts[0]->toArray());
    $this->logger->info("Edit post: $post_id");
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

// GET /post/post_id
$app->get('/post/{post_id}', function ($request, $response, $args) {
    $post_id = (int)$args['post_id'];
    $post = $this->db->table('posts')->find($post_id);
    var_dump($post);

    return $this->view->render($response, 'detail.twig', [
     'post' => $post
     //'comments' => $post_mapper->posts[0]->getComments(),
     //'csrf' => $csrf,
     //'args' => $args
    ]);
  })->setName('post-detail');


/*
$app->map(['GET','POST'],'/post/{post_id}', function ($request, $response, $args) {
  $post_id = (int)$args['post_id'];

  $post_mapper = new PostMapper($this->db);
  $post_mapper->selectPosts($post_id);
  $comment_mapper = new CommentMapper($this->db,$post_mapper->posts[0]);

  if($request->getMethod() == "POST") {
    $args = array_merge($args, $request->getParsedBody());
    $args = filter_var_array($args,FILTER_SANITIZE_STRING);

    $log = json_encode(["post_id: $post_id","name: ".$args['name'],"body: ".$args['body']]);
    if(!empty($args['name']) && !empty($args['body'])) {
      if($comment_mapper->insert($args)) {
          $this->logger->notice("New comment: SUCCESFUL | $log");
          //to avoid resubmitting values:
          $url = $this->router->pathFor('post-detail',['post_id' => $post_id]);
          return $response->withStatus(302)->withHeader('Location',$url);
        } else {
          $args['error'] = $comment_mapper->getAlert()[0]['message'];
          $this->logger->notice("New comment: UNSUCCESFUL | $log");
        }
      }
      else {
        $args['error'] = "all fields required";
        $this->logger->notice("New comment: UNSUCCESFUL | $log");
      }
  }
  else {
    $this->logger->info("View post: $post_id");
  }

  $nameKey = $this->csrf->getTokenNameKey();
  $valueKey = $this->csrf->getTokenValueKey();
  $csrf = [
    $nameKey => $request->getAttribute($nameKey),
    $valueKey => $request->getAttribute($valueKey)
  ];

  $comment_mapper->selectComments();

  return $this->view->render($response, 'detail.twig', [
   'post' => $post_mapper->posts[0],
   'comments' => $post_mapper->posts[0]->getComments(),
   'csrf' => $csrf,
   'args' => $args
  ]);
})->setName('post-detail');
*/
