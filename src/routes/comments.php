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

  //filter settings for all args from POST & GET
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
  //combine args from GET with args from POST
  $args = array_merge($args, $request->getParsedBody());
  //apply filters from array to all args
  $args = filter_var_array($args,$filters);
  //apply trim to all elements in the args array
  $args = array_map('trim',$args);

  $id = $args['id'];
  $slug = $args['slug'];

  if(!empty($id)) {

    if(!empty($args['name']) && !empty($args['body'])) {
      //set date to now!
      $args['date'] = date('Y-m-d H:i:s');

      try {
        //select post to which this new comment should be linked
        $post = Post::findorfail($id);
        //create new comment model
        $comment = new Comment();
        //make sure only the 'fillable' args for a comment remain
        $comment_args = array_intersect_key($args,array_flip($comment->getFillable()));

        //set all properties for the new comment
        foreach($comment_args as $key=>$value) {
          $comment->$key = $value;
        }
        //save the new comment as a relation to the existing post
        $post->comments()->save($comment);

        //logging & messaging to the user
        $_SESSION['message']['content'] = 'Successfully added comment';
        $_SESSION['message']['type'] = 'success';
        $this->logger->notice("New comment post $id | SUCCESSFUL");

      } catch(\Exception $e){
          //logging & messaging to the user
          $_SESSION['message']['content'] = 'Something went wrong adding the new comment. Try again later.';
          $_SESSION['message']['type'] = 'error';
          $this->logger->notice("New comment post $id | UNSUCCESSFUL | " . $e->getMessage());
      }
    }
    else {
      //logging & messaging to the user
      $_SESSION['message']['content'] = "All comment fields are required.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("New comment post $id | UNSUCCESSFUL | all fields required");

      //store the values in a session variable, so these value can be shown in the comment form. So the user does not have to refill everything
      $_SESSION['comment']['name'] = $args['name'];
      $_SESSION['comment']['body'] = $args['body'];
    }
  }
  else {
    //logging & messaging to the user
    $_SESSION['message']['content'] = "Something went wrong adding the new comment. Try again later.";
    $_SESSION['message']['type'] = 'error';
    $this->logger->notice("New comment | UNSUCCESSFUL | No valid post_id");
  }

  //determine the right redirect, depending on whether or not a slug is available
  if(!empty($slug)) {
    $url = $this->router->pathFor('post-detail',['slug' => $slug]);
  } else {
    $url = $this->router->pathFor('posts-list');
  }
  return $response->withStatus(302)->withHeader('Location',$url);

})->setName('new-comment');
