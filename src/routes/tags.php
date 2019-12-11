<?php
// Routes
use Project5SlimBlog\Post;
use Project5SlimBlog\Comment;
use Project5SlimBlog\Tag;
/*-----------------------------------------------------------------------------------------------
Available routes:
  1. new tags
  3. delete tag
  4. tag list
-----------------------------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------------------------
1. ROUTE FOR NEW TAG
-----------------------------------------------------------------------------------------------*/
$app->post('/tag/new', function ($request, $response, $args) {
  $filters = array(
      'name'   => array(
                              'filter' => FILTER_SANITIZE_STRING,
                              'flags'  => FILTER_FLAG_NO_ENCODE_QUOTES,
                             )
  );
  $args = array_merge($args, $request->getParsedBody());
  $args = filter_var_array($args,$filters);
  $args = array_map('trim',$args);

  //filter out all characters which are not A-Za-z0-9, or _ or - and replace with nothing
  //remove # in front
  //make 1st character of each word uppercase
  $args['name'] = preg_replace(
                      "/[^\w-]/",'',
                          ucwords(
                            strtolower(
                              ltrim($args['name'],'#')
                            )
                          )
                        );

  if(!empty($args['name'])) {
    try {
      $query = "lower(name) = lower('".$args['name']."')";
      if (Tag::whereraw($query)->count() == 0) {
        $tag = new Tag();
        $tag_args = array_intersect_key($args,array_flip($tag->getFillable()));

        foreach($tag_args as $key=>$value) {
          $tag->$key = $value;
        }
        $tag->save();

        $log = json_encode(["id: $tag->id","name: ".$tag->name]);
        $_SESSION['message']['content'] = 'Successfully added tag';
        $_SESSION['message']['type'] = 'success';
        $this->logger->notice("New tag | SUCCESSFUL | $log");
      } else {
        $_SESSION['message']['content'] = "Tag name \"".$args['name']."\" already exists";
        $_SESSION['message']['type'] = 'error';
        $this->logger->notice("New tag | UNSUCCESSFUL | Tag name \"". $args['name']  ."\" already exists");
      }
    } catch(\Exception $e){
        $_SESSION['message']['content'] = 'Something went wrong adding the new tag. Try again later.';
        $_SESSION['message']['type'] = 'error';
        $this->logger->notice("New tag | UNSUCCESSFUL | " . $e->getMessage());
    }
  }
  else {
    $_SESSION['message']['content'] = "Tag name field is required.";
    $_SESSION['message']['type'] = 'error';
    $this->logger->notice("New tag | UNSUCCESSFUL | Tag name field required");
  }
  $url = $this->router->pathFor('tags-list');
  return $response->withStatus(302)->withHeader('Location',$url);

})->setName('new-tag');

/*-----------------------------------------------------------------------------------------------
2. ROUTE FOR EDIT TAG
    - when a technical error occurs, the user is redirected to the tag list page
-----------------------------------------------------------------------------------------------*/
$app->map(['GET','POST'],'/tag/edit/[{id}]', function ($request, $response, $args) {
  //initialize template variables
  $csrf = $message = array();
  $id = "";

  if($request->getMethod() == "POST") {
    $filters = array(
        'name'   => array(
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

    if(!empty($id)) {

      //filter out all characters which are not A-Za-z0-9, or _ or - and replace with nothing
      //remove # in front
      //make 1st character of each word uppercase
      $args['name'] = preg_replace(
                          "/[^\w-]/",'',
                              ucwords(
                                strtolower(
                                  ltrim($args['name'],'#')
                                )
                              )
                            );

      if(!empty($args['name'])) {

        try {
          $query = "lower(name) = lower('".$args['name']."') AND id <> $id";
          if (Tag::whereraw($query)->count() == 0) {
            $tag = Tag::findorfail($id);
            $tag_args = array_intersect_key($args,array_flip($tag->getFillable()));

            foreach($tag_args as $key=>$value) {
              $tag->$key = $value;
            }
            $tag->save();

            $log = json_encode(["id: $tag->id","name: ".$tag->name]);
            $_SESSION['message']['content'] = 'Successfully updated Tag "'.$args['name'].'"';
            $_SESSION['message']['type'] = 'success';
            $this->logger->notice("Edit tag: $id | SUCCESSFUL | $log");
            //redirect back to tag list
            $url = $this->router->pathFor('tags-list');
            return $response->withStatus(302)->withHeader('Location',$url);
          } else {
            //functional error; no redirect
            $_SESSION['message']['content'] = "Tag name \"".$args['name']."\" already exists";
            $_SESSION['message']['type'] = 'error';
            $this->logger->notice("Edit tag: $id | UNSUCCESSFUL | Tag name \"". $args['name']  ."\" already exists");
          }

        } catch(\Exception $e){
            //techical error; redirect to tag list
            $_SESSION['message']['content'] = 'Something went wrong updating the tag. Try again later.';
            $_SESSION['message']['type'] = 'error';
            $this->logger->notice("Edit tag: $id | UNSUCCESSFUL | " . $e->getMessage());
            $url = $this->router->pathFor('tags-list');
            return $response->withStatus(302)->withHeader('Location',$url);
        }
      }
      else {
        //functional error; no redirect
        $_SESSION['message']['content'] = "Tag name field is required.";
        $_SESSION['message']['type'] = 'error';
        $this->logger->notice("Edit tag: $id | UNSUCCESSFUL | Tag name field is required");
      }
    }
    else {
      //techical error; redirect to tag list
      $_SESSION['message']['content'] = "Something went wrong adding the new tag. Try again later.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("Edit comment | UNSUCCESSFUL | No valid ID");
      $url = $this->router->pathFor('tags-list');
      return $response->withStatus(302)->withHeader('Location',$url);
    }
  }
  //if request method = GET and the edit form is being requested with the right ID
  else {

    $id = trim(filter_var($args['id'],FILTER_SANITIZE_NUMBER_INT));

    if(!empty($id)) {

      try {
        $tag = Tag::findorfail($id);
        $args['name'] = $tag->name;
        $this->logger->info("Edit tag: $id | VIEW | SUCCESSFUL");
      } catch(\Exception $e){
        //techical error; redirect to tag list
          $_SESSION['message']['content'] = 'Something went wrong retrieving the tag name. Try again later.';
          $_SESSION['message']['type'] = 'error';
          $this->logger->notice("Edit tag: $id | VIEW | UNSUCCESSFUL | " . $e->getMessage());
          $url = $this->router->pathFor('tags-list');
          return $response->withStatus(302)->withHeader('Location',$url);
      }
    }
    else {
      //techical error; redirect to tag list
      $_SESSION['message']['content'] = "Something went wrong retrieving the tag name. Try again later.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("Edit tag | VIEW | UNSUCCESSFUL | No valid ID");
      $url = $this->router->pathFor('tags-list');
      return $response->withStatus(302)->withHeader('Location',$url);
    }
  }

  $nameKey = $this->csrf->getTokenNameKey();
  $valueKey = $this->csrf->getTokenValueKey();
  $csrf = [
    $nameKey => $request->getAttribute($nameKey),
    $valueKey => $request->getAttribute($valueKey)
  ];

  if(!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
  }
  return $this->view->render($response, 'manage_tags.twig', [
   'csrf' => $csrf,
   'args' => $args,
   'message' => $message
  ]);
})->setName('edit-tag');


/*-----------------------------------------------------------------------------------------------
3. ROUTE FOR DELETE TAG
-----------------------------------------------------------------------------------------------*/
$app->post('/tag/delete', function ($request, $response, $args) {
  $id = "";

  $args = $request->getParsedBody();
  $id = trim(filter_var($args['delete'],FILTER_SANITIZE_NUMBER_INT));

  if (!empty($id)) {
    try {
      $tag = Tag::findorfail($id);
      $name = $tag->name;
      $tag->delete();

      $_SESSION['message']['content'] = 'Successfully deleted Tag "'.$name.'"';
      $_SESSION['message']['type'] = 'success';
      $this->logger->info("Delete tag: $id | SUCCESSFUL");

      $url = $this->router->pathFor('tags-list');
      return $response->withStatus(302)->withHeader('Location',$url);
    } catch(\Exception $e){
       $this->logger->notice("Delete tag: $id | UNSUCCESSFUL | " . $e->getMessage());
    }
  } else {
    $this->logger->notice("Delete tag | UNSUCCESSFUL | No valid ID");
  }

  $_SESSION['message']['content'] = 'Something went wrong deleting the tag. Try again later.';
  $_SESSION['message']['type'] = 'error';
  $url = $this->router->pathFor('tags-list');
  return $response->withStatus(302)->withHeader('Location',$url);
})->setName('delete-tag');

/*-----------------------------------------------------------------------------------------------
4. Tag list
-----------------------------------------------------------------------------------------------*/
$app->get('/tags', function ($request, $response, $args) {
    $full_tags_list = $csrf = $message = array();

    try {
      $full_tags_list = Tag::orderBy('name','asc')->get();
      $this->logger->info("View tags list | SUCCESSFUL");
    } catch(\Exception $e){
       $_SESSION['message']['content'] = 'Something went wrong retrieving the tags. Try again later.';
       $_SESSION['message']['type'] = 'error';
       $this->logger->notice("View tags list | UNSUCCESSFUL | " . $e->getMessage());
    }

    $nameKey = $this->csrf->getTokenNameKey();
    $valueKey = $this->csrf->getTokenValueKey();
    $csrf = [
      $nameKey => $request->getAttribute($nameKey),
      $valueKey => $request->getAttribute($valueKey)
    ];

    if(!empty($_SESSION['message'])) {
      $message = $_SESSION['message'];
      unset($_SESSION['message']);
    }
    return $this->view->render($response, 'manage_tags.twig', [
      'tags' => $full_tags_list,
      'csrf' => $csrf,
      'args' => $args,
      'message' => $message
    ]);
})->setName('tags-list');
