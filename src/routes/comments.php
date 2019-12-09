<?php
// Routes
use Project5SlimBlog\Post;
use Project5SlimBlog\Comment;
use Project5SlimBlog\Tag;
/*-----------------------------------------------------------------------------------------------
Available routes:
  1. new comment
-----------------------------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------------------------
1. ROUTE FOR ADDING NEW COMMENTS TO A POST
-----------------------------------------------------------------------------------------------*/
$app->post('/post/{slug}/comment/new', function ($request, $response, $args) {
  $slug = (string)$args['slug'];
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
    $_SESSION['message']['content'] = "All comment fields are required.";
    $_SESSION['message']['type'] = 'error';
    $this->logger->notice("New comment | UNSUCCESSFUL | all fields required");
    $_SESSION['comment']['name'] = $args['name'];
    $_SESSION['comment']['body'] = $args['body'];
  }
  $url = $this->router->pathFor('post-detail',['slug' => $slug]);
  return $response->withStatus(302)->withHeader('Location',$url);

})->setName('new-comment');
