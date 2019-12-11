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
  //remove in front
  //make 1st character of each word uppercase
  $args['name'] = preg_replace(
                      "/[^\w-]/",'',
                          ucwords(
                            ltrim($args['name'],'#')
                          )
                        );
                        
  if(!empty($args['name'])) {
    try {
      if (Tag::where('name',$args['name'])->count() == 0) {
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

        $url = $this->router->pathFor('tags-list');
        return $response->withStatus(302)->withHeader('Location',$url);
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
-----------------------------------------------------------------------------------------------*/
$app->map(['GET','POST'],'/tag/edit/{id}', function ($request, $response, $args) {

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

    $args['name'] = ucwords(ltrim(trim($args['name']),'#'));

    $log = json_encode(["tag name: ".$args['name']]);
    if(!empty($args['name'])) {

      try {
        if (Tag::where('name',$args['name'])->where('id','<>',$id)->count() == 0) {
          $tag = Tag::find($id);
          $tag_args = array_intersect_key($args,array_flip($tag->getFillable()));

          foreach($tag_args as $key=>$value) {
            $tag->$key = $value;
          }
          $tag->save();

          $_SESSION['message']['content'] = 'Successfully updated Tag "'.$args['name'].'"';
          $_SESSION['message']['type'] = 'success';
          $this->logger->notice("Edit tag: $id | SUCCESSFUL | $log");
          //to avoid resubmitting values:
          $url = $this->router->pathFor('tags-list');
          return $response->withStatus(302)->withHeader('Location',$url);
        } else {
          $_SESSION['message']['content'] = "Tag name \"".$args['name']."\" already exists";
          $_SESSION['message']['type'] = 'error';
          $this->logger->notice("New tag | UNSUCCESSFUL | Tag name \"". $args['name']  ."\" already exists");
        }

      } catch(\Exception $e){
          $_SESSION['message']['content'] = 'Something went wrong updating the tag. Try again later.';
          $_SESSION['message']['type'] = 'error';
          $this->logger->notice("Edit tag: $id | UNSUCCESSFUL | " . $e->getMessage());
      }
    }
    else {
      $_SESSION['message']['content'] = "All fields are required.";
      $_SESSION['message']['type'] = 'error';
      $this->logger->notice("New tag | UNSUCCESSFUL | all fields required");
    }
  }
  else {
    $id = filter_var($args['id'],FILTER_SANITIZE_NUMBER_INT);

    try {
      $tag = Tag::find($id);
      $args['name'] = $tag->name;
      $this->logger->info("Edit tag: $id | VIEW | SUCCESSFUL");
    } catch(\Exception $e){
        $_SESSION['message']['content'] = 'Something went wrong retrieving the tag name. Try again later.';
        $_SESSION['message']['type'] = 'error';
        $this->logger->notice("Edit tag: $id | VIEW | UNSUCCESSFUL | " . $e->getMessage());
    }
  }

  $nameKey = $this->csrf->getTokenNameKey();
  $valueKey = $this->csrf->getTokenValueKey();
  $csrf = [
    $nameKey => $request->getAttribute($nameKey),
    $valueKey => $request->getAttribute($valueKey)
  ];

  $message = array();
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

  $args = $request->getParsedBody();
  $args = filter_var_array($args,FILTER_SANITIZE_NUMBER_INT);
  $id = (int)$args['delete'];

  if (!empty($id)) {
    try {
      $tag = Tag::find($id);
      $name = $tag->name;
      $tag = Tag::find($id)->delete();

      $_SESSION['message']['content'] = 'Successfully deleted Tag "'.$name.'"';
      $_SESSION['message']['type'] = 'success';
      $this->logger->info("Delete tag: $id | SUCCESSFUL");

      $url = $this->router->pathFor('tags-list');
      return $response->withStatus(302)->withHeader('Location',$url);
    } catch(\Exception $e){
       $this->logger->notice("Delete tag: $id | UNSUCCESSFUL | " . $e->getMessage());
    }
  } else {
    $this->logger->notice("Delete tag: $id | UNSUCCESSFUL | No valid ID");
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

    $message = array();
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
