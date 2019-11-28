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
      $results->bindValue(':post_id',$this->post->getId(),PDO::PARAM_INT);
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
  }

  public function addComment($data = null)
  {
    $comment = new Comment($data);
    $this->post->addComment($comment);
    //$this->comments[] = $comment;
    return $comment;
  }

  /**
     * Insert listing
     * @param array $data User Data
     * @return bool If listing inserted true/false
     */
    public function insert($data)
    {
      //comment property date set to now
      $data['date'] = date('Y-m-d H:i:s');
      //filter out non comment properties
      $data = array_filter($this->addComment($data)->toArray());
      //make sure comment is linked to the post, therefore post_id is added
      $data['post_id'] = $this->post->getId();

      $sql = "INSERT INTO comments("
            . implode(', ', array_keys($data))
            . ") VALUES(:"
            . implode(', :', array_keys($data))
            . ")";
        $statement = $this->db->prepare($sql);
        $statement->execute($data);
        if ($statement->rowCount() > 0) {
            $this->setAlert(
                'success',
                '<strong>Add comment successful!</strong>'
            );
            return true;
        } else {
            $this->setAlert('danger', 'Unable to update listing');
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
