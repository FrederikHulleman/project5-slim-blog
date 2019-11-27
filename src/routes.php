<?php
// Routes
use Project5SlimBlog\PostMapper;
use Project5SlimBlog\CommentMapper;

$app->get('/post/{id}', function ($request, $response, $args) {

    $post_id = (int)$args['id'];
    $this->logger->addInfo("Post: $post_id");
    $mapper = new PostMapper($this->db);
    $mapper->selectPosts($post_id);
    return $this->view->render($response, 'detail.twig', [
     'post' => $mapper->posts[0]
    ]);
    // foreach ($mapper->posts as $post) {
    //   $response->getBody()->write(var_export($mapper->posts[0], true));
    //
    // }
    // return $response;
})->setName('post-detail');

$app->get('/[{posts}]', function ($request, $response, $args) {
    $this->logger->addInfo("Posts list");
    $mapper = new PostMapper($this->db);
    $mapper->selectPosts();
    return $this->view->render($response, 'index.twig', [
      'posts' => $mapper->posts
    ]);
    // foreach ($mapper->posts as $post) {
    //   $response->getBody()->write(var_export($post, true));
    // }
    // return $response;
})->setName('posts-list');

// $app->get('/[{name}]', function ($request, $response, $args) {
//     // Sample log message
//     $this->logger->info("Slim-Skeleton '/' route");
//
//     // Render index view
//     return $this->renderer->render($response, 'index.phtml', $args);
// });
