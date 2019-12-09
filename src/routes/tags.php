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
  4. show post details & its comments. And also handle the insert of new comments
  5. show full post list, optionally filtered by tag
-----------------------------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------------------------
6. ROUTE FOR TAGS LIST
-----------------------------------------------------------------------------------------------*/
$app->get('/tags', function ($request, $response, $args) {
    try {
      $full_tags_list = Tag::all();
    } catch(\Exception $e){
       $_SESSION['message']['content'] = 'Something went wrong retrieving the tags. Try again later.';
       $_SESSION['message']['type'] = 'error';
       $this->logger->notice("View tags list | UNSUCCESSFUL | " . $e->getMessage());
    }

    $message = array();
    if(!empty($_SESSION['message'])) {
      $message = $_SESSION['message'];
      unset($_SESSION['message']);
    }
    return $this->view->render($response, 'tags.twig', [
      'tags' => $full_tags_list,
      'args' => $args,
      'message' => $message
    ]);
})->setName('tags-list');
