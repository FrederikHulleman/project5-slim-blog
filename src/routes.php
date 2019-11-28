<?php
// Routes
use Project5SlimBlog\PostMapper;
use Project5SlimBlog\CommentMapper;

$app->get('/post/{id}', function ($request, $response, $args) {

    $post_id = (int)$args['id'];
    $this->logger->info("Post: $post_id");
    $post_mapper = new PostMapper($this->db);
    $post_mapper->selectPosts($post_id);

    $comment_mapper = new CommentMapper($this->db,$post_mapper->posts[0]);
    $comment_mapper->selectComments();

    return $this->view->render($response, 'detail.twig', [
     'post' => $post_mapper->posts[0],
     'comments' => $post_mapper->posts[0]->getComments()
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
