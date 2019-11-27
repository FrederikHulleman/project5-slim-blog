<?php

namespace Project5SlimBlog;
use PDO;

class PostMapper
{
  private $db;
  public $posts = [];

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function selectPosts($post_id = null)
  {
    $sql = $where = $order = "";
    $sql = "SELECT * FROM posts";

    if(!empty($post_id)) {
      $where = " WHERE id = :post_id";
    } else {
      $order = " ORDER BY date DESC";
    }

    try {
      $results = $this->db->prepare($sql . $where . $order);
      if(!empty($post_id)) {
        $results->bindParam(':post_id',$post_id,PDO::PARAM_INT);
      }
      $results->execute();
    }
    catch (Exception $e) {
      echo "Bad query: " . $e->getMessage();
      exit;
    }
    foreach ($results->fetchAll(PDO::FETCH_ASSOC) as $data) {
      $this->addPost($data);
    }
    return $results->rowCount();
    //return $posts;
  }

  public function addPost($data = null)
  {
    $post = new Post($data);
    $this->posts[] = $post;
    return $post;
  }

}
