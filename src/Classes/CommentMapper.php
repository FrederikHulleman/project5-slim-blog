<?php

namespace Project5SlimBlog;
use PDO;

class CommentMapper
{
  private $db;
  //public $comments = [];
  public $post;

  public function __construct($db,$post)
  {
    $this->db = $db;
    $this->post = $post;
  }

  public function selectComments()
  {
    $sql = $where = $order = "";
    $sql = "SELECT * FROM comments";
    $where = " WHERE post_id = :post_id";
    $order = " ORDER BY date DESC";

    try {
      $results = $this->db->prepare($sql . $where . $order);
      $results->bindParam(':post_id',$this->post-getId(),PDO::PARAM_INT);
      $results->execute();
    }
    catch (Exception $e) {
      echo "Bad query: " . $e->getMessage();
      exit;
    }
    foreach ($results->fetchAll(PDO::FETCH_ASSOC) as $data) {
      $this->addComment($data);
    }
    return $results->rowCount();
    //return $posts;
  }

  public function addComment($data = null)
  {
    $comment = new Comment($data);
    $this->post->addComment($comment);
    //$this->comments[] = $comment;
    return $comment;
  }

}
