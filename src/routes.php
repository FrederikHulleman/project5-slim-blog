<?php
// Routes
use Project5SlimBlog\PostMapper;
use Project5SlimBlog\CommentMapper;

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

  $comment_mapper->selectComments();

  return $this->view->render($response, 'detail.twig', [
   'post' => $post_mapper->posts[0],
   'comments' => $post_mapper->posts[0]->getComments(),
   'args' => $args
  ]);
})->setName('post-detail');

$app->get('/[{posts}]', function ($request, $response, $args) {
    $this->logger->info("Posts list");
    $post_mapper = new PostMapper($this->db);
    $post_mapper->selectPosts();

    return $this->view->render($response, 'blog.twig', [
      'posts' => $post_mapper->posts
    ]);
})->setName('posts-list');
