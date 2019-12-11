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
  $id = $slug = "";
  
  $filters = array(
      'name'   => array(
                              'filter' => FILTER_SANITIZE_STRING,
                              'flags'  => FILTER_FLAG_NO_ENCODE_QUOTES,
                             ),
      'body'    => array(
                              'filter' => FILTER_SANITIZE_STRING,
                              'flags'  => FILTER_FLAG_NO_ENCODE_QUOTES,
                            ),
      'slug'    => array(
                              'filter' => FILTER_SANITIZE_STRING,
                              'flags'  => FILTER_FLAG_NO_ENCODE_QUOTES,
                            ),
      'id'    => array(
                              'filter' => FILTER_SANITIZE_NUMBER_INT
                            )
  );
  $args = array_merge($args, $request->getParsedBody());
  $args = filter_var_array($args,$filters);
  $args = array_map('trim',$args);

  $id = $args['id'];
  $slug = $args['slug'];

  if(!empty($id)) {

    if(!empty($args['name']) && !empty($args['body'])) {
      $args['date'] = date('Y-m-d H:i:s');

      try {
        $comment = new Comment();
        $comment_args = array_intersect_key($args,array_flip($comment->getFillable()));

        foreach($comment_args as $key=>$value) {
          $comment->$key = $value;
        }

        $post = Post::findorfail($id);
        $post->comments()->save($comment);

        $_SESSION['message']['content'] = 'Successfully added comment';
        $_SESSION['message']['type'] = 'success';
        $this->logger->notice("New comment post $id | SUCCESSFUL");

      } catch(\Exception $e){
          $_SESSION['message']['content'] = 'Something went wrong adding the new comment. Try again later.';
          $_SESSION['message']['type'] = 'error';
          $this->logger->notice("New comment post $id | UNSUCCESSFUL | " . $e->getMessage());
      }
    }
    else {
      $_SESSION['message']['content'] = "All comment fields are required.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("New comment post $id | UNSUCCESSFUL | all fields required");
      $_SESSION['comment']['name'] = $args['name'];
      $_SESSION['comment']['body'] = $args['body'];
    }
  }
  else {
    $_SESSION['message']['content'] = "Something went wrong adding the new comment. Try again later.";
    $_SESSION['message']['type'] = 'error';
    $this->logger->notice("New comment | UNSUCCESSFUL | No valid post_id");
  }

  if(!empty($slug)) {
    $url = $this->router->pathFor('post-detail',['slug' => $slug]);
  } else {
    $url = $this->router->pathFor('posts-list');
  }
  return $response->withStatus(302)->withHeader('Location',$url);

})->setName('new-comment');
