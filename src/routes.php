<?php
// Routes
use Project5SlimBlog\PostMapper;
use Project5SlimBlog\CommentMapper;

$app->get('/post/{id}', function ($request, $response, $args) {

    $post_id = (int)$args['id'];
    $this->logger->info("Post: $post_id");
    $mapper = new PostMapper($this->db);
    $mapper->selectPosts($post_id);
    return $this->view->render($response, 'detail.twig', [
     'post' => $mapper->posts[0]
    ]);
})->setName('post-detail');

$app->get('/[{posts}]', function ($request, $response, $args) {
    $this->logger->info("Posts list");
    $mapper = new PostMapper($this->db);
    $mapper->selectPosts();
    return $this->view->render($response, 'blog.twig', [
      'posts' => $mapper->posts
    ]);
})->setName('posts-list');

// $app->get('/[{name}]', function ($request, $response, $args) {
//     // Sample log message
//     $this->logger->info("Slim-Skeleton '/' route");
//
//     // Render index view
//     return $this->renderer->render($response, 'index.phtml', $args);
// });
