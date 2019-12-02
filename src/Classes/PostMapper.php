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
      $where = " WHERE post_id = :post_id";
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

  /**
     * Insert listing
     * @param array $data User Data
     * @return bool If post inserted true/false
     */
    public function insert($data)
    {
      //post property date set to now
      $data['date'] = date('Y-m-d H:i:s');
      //filter out non comment properties
      $data = array_filter($this->addPost($data)->toArray());

      $sql = "INSERT INTO posts("
          . implode(', ', array_keys($data))
          . ") VALUES(:"
          . implode(', :', array_keys($data))
          . ")";

      $statement = $this->db->prepare($sql);
      $statement->execute($data);
      if ($statement->rowCount() > 0) {
          $this->setAlert(
              'success',
              '<strong>Add post successful!</strong> ' . $data['title']
          );
          return true;
      } else {
          $this->setAlert('danger', 'Unable to add post');
          return false;
      }
    }


    /**
     * Update listing
     * @param array $data User Data
     * @return integer Indicates the number of records updated
     */
    public function update($data)
    {
      //post property date set to now
      $data['date'] = date('Y-m-d H:i:s');
      //filter out non post properties
      $data = $this->addPost($data)->toArray();

      $sql = 'UPDATE posts SET ';
      foreach (array_keys($data) as $key) {
          if ($key != 'post_id' && $key != 'comments') {
              $sql .= " $key = :$key, ";
          }
      }
      $sql = substr($sql, 0, -2);
      $sql .= ' WHERE post_id = :post_id';

      try {
          $statement = $this->db->prepare($sql);
          foreach (array_keys($data) as $key) {
              if ($key != 'post_id' && $key != 'comments') {
                  $results->bindValue(':$key',$data[$key],PDO::PARAM_STR);
              }
          }
          $results->bindValue(':post_id',$data['post_id'],PDO::PARAM_INT);
          $statement->execute($data);
      } catch (Exception $e) {
          $this->setAlert('danger',$e->getMessage());
      }
      $count = $statement->rowCount();

      if ($count > 0) {
          $this->setAlert(
              'success',
              '<strong>Update post successful!</strong> ' . $data['title']
          );
      } else {
          $this->setAlert('danger', 'Unable to update post');
      }
      return $count;
    }

    /**
     * Delete a single post
     * @param integer $id ID of the single post to remove
     * @return bool true/false
     */
    public function delete($post_id)
    {
        $sql = "DELETE FROM posts WHERE post_id=?";
        try {
            $statement = $this->db->prepare($sql);
            $statement->bindValue(1, $post_id, PDO::PARAM_INT);
            $statement->execute();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        if ($statement->rowCount() > 0) {
            $this->setAlert(
                'danger',
                'Post Deleted'
            );
            return true;
        } else {
            $this->setAlert(
                'danger',
                '<strong>Unable to remove post</strong>'
            );
            return false;
        }
    }

  /**
     * Set alerts to show user
     * @param string $type Options: primary/success/info/warning/danger
     * @param string $msg  Message to display
     * @return null sets super global $_SESSION
     */
    public function setAlert($type, $msg)
    {
        $_SESSION['alerts'][] = ['type' => $type, 'message' => $msg];
    }

    /**
     * Get alerts to show user
     * @return array
     */
    public function getAlert()
    {
        if (!isset($_SESSION['alerts'])) {
            return [];
        }
        $alerts = $_SESSION['alerts'];
        unset($_SESSION['alerts']);
        return $alerts;
    }

}
