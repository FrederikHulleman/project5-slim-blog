<?php

namespace Project5SlimBlog;
use PDO;

class CommentMapper
{
  private $db;
  public $comments = [];

  public function __construct($db)
  {
    $this->db = $db;
  }
}
