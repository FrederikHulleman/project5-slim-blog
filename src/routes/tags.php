<?php
// Routes
use Project5SlimBlog\Post;
use Project5SlimBlog\Comment;
use Project5SlimBlog\Tag;
/*-----------------------------------------------------------------------------------------------
Available routes:
  1. new tag
  2. edit tag
  3. delete tag
  4. tag list
-----------------------------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------------------------
1. ROUTE FOR NEW TAG
-----------------------------------------------------------------------------------------------*/
$app->post('/tag/new', function ($request, $response, $args) {
  //filter settings for all args from POST & GET
  $filters = array(
      'name'   => array(
                              'filter' => FILTER_SANITIZE_STRING,
                              'flags'  => FILTER_FLAG_NO_ENCODE_QUOTES,
                             )
  );
  //combine args from GET with args from POST
  $args = array_merge($args, $request->getParsedBody());
  //apply filters from array to all args
  $args = filter_var_array($args,$filters);
  //apply trim to all elements in the args array
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
      //validate whether there is a tag with the same name
      $query = "lower(name) = lower('".$args['name']."')";
      if (Tag::whereraw($query)->count() == 0) {
        //if the tag name doesn't exists yet
        $tag = new Tag();
        //make sure only the 'fillable' args for a comment remain
        $tag_args = array_intersect_key($args,array_flip($tag->getFillable()));

        //set all properties for the new comment
        foreach($tag_args as $key=>$value) {
          $tag->$key = $value;
        }
        $tag->save();
        //logging & messaging to the user
        $log = json_encode(["id: $tag->id","name: ".$tag->name]);
        $_SESSION['message']['content'] = 'Successfully added tag';
        $_SESSION['message']['type'] = 'success';
        $this->logger->notice("New tag | SUCCESSFUL | $log");
      } else {
        //logging & messaging to the user
        $_SESSION['message']['content'] = "Tag name \"".$args['name']."\" already exists";
        $_SESSION['message']['type'] = 'error';
        $this->logger->notice("New tag | UNSUCCESSFUL | Tag name \"". $args['name']  ."\" already exists");
      }
    } catch(\Exception $e){
        //logging & messaging to the user
        $_SESSION['message']['content'] = 'Something went wrong adding the new tag. Try again later.';
        $_SESSION['message']['type'] = 'error';
        $this->logger->notice("New tag | UNSUCCESSFUL | " . $e->getMessage());
    }
  }
  else {
    //logging & messaging to the user
    $_SESSION['message']['content'] = "Tag name field is required.";
    $_SESSION['message']['type'] = 'error';
    $this->logger->notice("New tag | UNSUCCESSFUL | Tag name field required");
  }
  //determine the right redirect
  $url = $this->router->pathFor('tags-list');
  return $response->withStatus(302)->withHeader('Location',$url);

})->setName('new-tag');

/*-----------------------------------------------------------------------------------------------
2. ROUTE FOR EDIT TAG
    - when a technical error occurs, the user is redirected to the tag list page
-----------------------------------------------------------------------------------------------*/
$app->map(['GET','POST'],'/tag/edit/[{id}]', function ($request, $response, $args) {
  //initialize variables for the templates and the 'key elements' for this route to succeed
  $csrf = $message = array();
  $id = "";

  if($request->getMethod() == "POST") {
    //filter settings for all args from POST & GET
    $filters = array(
        'name'   => array(
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
          //validate whether there is a tag with the same name
          $query = "lower(name) = lower('".$args['name']."') AND id <> $id";
          if (Tag::whereraw($query)->count() == 0) {
            //if the tag name doesn't exist yet:
            $tag = Tag::findorfail($id);
            //make sure only the 'fillable' args for a comment remain
            $tag_args = array_intersect_key($args,array_flip($tag->getFillable()));

            //set all properties for the new comment
            foreach($tag_args as $key=>$value) {
              $tag->$key = $value;
            }
            $tag->save();
            //logging & messaging to the user
            $log = json_encode(["id: $tag->id","name: ".$tag->name]);
            $_SESSION['message']['content'] = 'Successfully updated Tag "'.$args['name'].'"';
            $_SESSION['message']['type'] = 'success';
            $this->logger->notice("Edit tag: $id | SUCCESSFUL | $log");
            //determine the right redirect
            $url = $this->router->pathFor('tags-list');
            return $response->withStatus(302)->withHeader('Location',$url);
          } else {
            //logging & messaging to the user
            //functional error; no redirect
            $_SESSION['message']['content'] = "Tag name \"".$args['name']."\" already exists";
            $_SESSION['message']['type'] = 'error';
            $this->logger->notice("Edit tag: $id | UNSUCCESSFUL | Tag name \"". $args['name']  ."\" already exists");
          }

        } catch(\Exception $e){
            //logging & messaging to the user
            //techical error; redirect to tag list
            $_SESSION['message']['content'] = 'Something went wrong updating the tag. Try again later.';
            $_SESSION['message']['type'] = 'error';
            $this->logger->notice("Edit tag: $id | UNSUCCESSFUL | " . $e->getMessage());
            //determine the right redirect
            $url = $this->router->pathFor('tags-list');
            return $response->withStatus(302)->withHeader('Location',$url);
        }
      }
      else {
        //logging & messaging to the user
        //functional error; no redirect
        $_SESSION['message']['content'] = "Tag name field is required.";
        $_SESSION['message']['type'] = 'error';
        $this->logger->notice("Edit tag: $id | UNSUCCESSFUL | Tag name field is required");
      }
    }
    else {
      //logging & messaging to the user
      //techical error; redirect to tag list
      $_SESSION['message']['content'] = "Something went wrong adding the new tag. Try again later.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("Edit comment | UNSUCCESSFUL | No valid ID");
      //determine the right redirect
      $url = $this->router->pathFor('tags-list');
      return $response->withStatus(302)->withHeader('Location',$url);
    }
  }
  //if request method = GET and the edit form is being requested with the right ID
  else {
    //apply filters from array to all args
    $id = trim(filter_var($args['id'],FILTER_SANITIZE_NUMBER_INT));

    if(!empty($id)) {

      try {
        //select the tag which the user wants to edit
        $tag = Tag::findorfail($id);
        $args['name'] = $tag->name;
        //loging
        $this->logger->info("Edit tag: $id | VIEW | SUCCESSFUL");
      } catch(\Exception $e){
          //logging & messaging to the user
          //techical error; redirect to tag list
          $_SESSION['message']['content'] = 'Something went wrong retrieving the tag name. Try again later.';
          $_SESSION['message']['type'] = 'error';
          $this->logger->notice("Edit tag: $id | VIEW | UNSUCCESSFUL | " . $e->getMessage());
          //determine the right redirect
          $url = $this->router->pathFor('tags-list');
          return $response->withStatus(302)->withHeader('Location',$url);
      }
    }
    else {
      //logging & messaging to the user
      //techical error; redirect to tag list
      $_SESSION['message']['content'] = "Something went wrong retrieving the tag name. Try again later.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("Edit tag | VIEW | UNSUCCESSFUL | No valid ID");
      //determine the right redirect
      $url = $this->router->pathFor('tags-list');
      return $response->withStatus(302)->withHeader('Location',$url);
    }
  }
  //csrf settings for form
  $nameKey = $this->csrf->getTokenNameKey();
  $valueKey = $this->csrf->getTokenValueKey();
  $csrf = [
    $nameKey => $request->getAttribute($nameKey),
    $valueKey => $request->getAttribute($valueKey)
  ];

  //if a message in the session is set, it has to be diplayed
  //therefore it's copied to the $message variable and passed to the template
  //and the session variable is cleaned up, to avoid repetition of the same message
  if(!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
  }

  //pass all necessary variables to the template:
  // - csrf settings
  // - the arguments from GET and POST
  // - optionally a message to show to the user
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
  //initialize variables for the templates and the 'key elements' for this route to succeed
  $id = "";

  //get POST args
  $args = $request->getParsedBody();
  //filter settings for all args from POST & GET
  $id = trim(filter_var($args['delete'],FILTER_SANITIZE_NUMBER_INT));

  if (!empty($id)) {
    try {
      //select the tag which should be removed
      $tag = Tag::findorfail($id);
      $name = $tag->name;
      $tag->delete();

      //messaging to the user
      $_SESSION['message']['content'] = 'Successfully deleted Tag "'.$name.'"';
      $_SESSION['message']['type'] = 'success';
      $this->logger->info("Delete tag: $id | SUCCESSFUL");
      //determine the right redirect
      $url = $this->router->pathFor('tags-list');
      return $response->withStatus(302)->withHeader('Location',$url);
    } catch(\Exception $e){
      //logging
       $this->logger->notice("Delete tag: $id | UNSUCCESSFUL | " . $e->getMessage());
    }
  } else {
    //logging
    $this->logger->notice("Delete tag | UNSUCCESSFUL | No valid ID");
  }

  //messaging to the user
  $_SESSION['message']['content'] = 'Something went wrong deleting the tag. Try again later.';
  $_SESSION['message']['type'] = 'error';
  //determine the right redirect
  $url = $this->router->pathFor('tags-list');
  return $response->withStatus(302)->withHeader('Location',$url);
})->setName('delete-tag');

/*-----------------------------------------------------------------------------------------------
4. Tag list
-----------------------------------------------------------------------------------------------*/
$app->get('/tags', function ($request, $response, $args) {
    //initialize variables for the templates and the 'key elements' for this route to succeed
    $full_tags_list = $csrf = $message = array();

    try {
      //select & order the full tag list
      $full_tags_list = Tag::orderBy('name','asc')->get();
      //logging
      $this->logger->info("View tags list | SUCCESSFUL");
    } catch(\Exception $e){
        //logging & messaging to the user
       $_SESSION['message']['content'] = 'Something went wrong retrieving the tags. Try again later.';
       $_SESSION['message']['type'] = 'error';
       $this->logger->notice("View tags list | UNSUCCESSFUL | " . $e->getMessage());
    }

    //csrf settings for form
    $nameKey = $this->csrf->getTokenNameKey();
    $valueKey = $this->csrf->getTokenValueKey();
    $csrf = [
      $nameKey => $request->getAttribute($nameKey),
      $valueKey => $request->getAttribute($valueKey)
    ];

    //if a message in the session is set, it has to be diplayed
    //therefore it's copied to the $message variable and passed to the template
    //and the session variable is cleaned up, to avoid repetition of the same message
    if(!empty($_SESSION['message'])) {
      $message = $_SESSION['message'];
      unset($_SESSION['message']);
    }

    //pass all necessary variables to the template:
    // - all available tags
    // - csrf settings
    // - the arguments from GET and POST
    // - optionally a message to show to the user
    return $this->view->render($response, 'manage_tags.twig', [
      'tags' => $full_tags_list,
      'csrf' => $csrf,
      'args' => $args,
      'message' => $message
    ]);
})->setName('tags-list');
