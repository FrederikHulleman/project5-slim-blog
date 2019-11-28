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
    if(!empty($args['name']) && !empty($args['body'])) {
        $log = json_encode(["post_id: $post_id","name: ".$args['name'],"body: ".$args['body']]);
        $this->logger->notice("New comment: $log");

        $comment_mapper->insert($args);
        
        foreach ($comment_mapper->getAlert() as $alert) {
          $this->logger->notice("Results: " . $alert['type'] . " | " . $alert['message']);
        }
        //to avoid resubmitting values:
        $url = $this->router->pathFor('post-detail',['post_id' => $post_id]);
        return $response->withStatus(302)->withHeader('Location',$url);
      }
      $args['error'] = "all fields required";

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

// $app->get('/[{name}]', function ($request, $response, $args) {
//     // Sample log message
//     $this->logger->info("Slim-Skeleton '/' route");
//
//     // Render index view
//     return $this->renderer->render($response, 'index.phtml', $args);
// });
