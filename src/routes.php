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
require __DIR__ . '/routes/tags.php';
require __DIR__ . '/routes/comments.php';
require __DIR__ . '/routes/posts.php';
