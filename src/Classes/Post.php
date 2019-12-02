<?php

namespace Project5SlimBlog;

Class Post {

  private $post_id,$title,$date,$body;
  private $comments = []; //links to related comment objects

  public function __construct($data = [])
  {
      if (!empty($data)) {
          $this->setValues($data);
      }
  }

  /**
   * Calls individual methods to set values for object properties.
   * @param array $data Data to set from user or database
   */
  public function setValues($data = []) {
      if (isset($data['post_id'])) {
          $this->setId($data['post_id']);
      }
      if (isset($data['title'])) {
          $this->setTitle($data['title']);
      }
      if (isset($data['date'])) {
          $this->setDate($data['date']);
      }
      if (isset($data['body'])) {
          $this->setBody($data['body']);
      }

  }

  /**
   * Gets the local property $id
   * @return int
   */
  public function getId()
  {
      return $this->post_id;
  }

  /**
   * Cleans up and sets the local property $id
   * @param int $value Data may be from database or user
   */
  public function setId($value)
  {
      $this->post_id = trim(filter_var($value, FILTER_SANITIZE_NUMBER_INT));
  }

  /**
   * Gets the local property $title
   * @return string
   */
  public function getTitle()
  {
      return $this->title;
  }

  /**
   * Cleans up and sets the local property $title
   * @param string $value to set property
   */
  public function setTitle($value)
  {
      $this->title = trim(filter_var($value, FILTER_SANITIZE_STRING));
  }

  /**
  * Gets the local property $date in raw format
  * @return string
  */
 public function getRawDate()
 {
     return $this->date;
 }

 /**
 * Gets the local property $date in the right format for display to user
 * @return string
 */
public function getFormattedDate()
{
    return date('F j, Y | H:i:s',strtotime($this->date));
}


  /**
   * Cleans up and sets the local property $date
   * @param string $value to set property
   */
  public function setDate($value)
  {
      $this->date = trim(filter_var($value, FILTER_SANITIZE_STRING));
  }

  /**
  * Gets the local property $body
  * @return string
  */
 public function getBody()
 {
     return $this->body;
 }

  /**
   * Cleans up and sets the local property $body
   * @param string $value to set property
   */
  public function setBody($value)
  {
      $this->body = trim(filter_var($value, FILTER_SANITIZE_STRING));
  }

  public function addComment($comment)
  {
    $this->comments[] = $comment;
  }

  public function getComments()
  {
    return $this->comments;
  }

  /**
   * Convert the current object to an associative array of parameters
   * @return array of object parameters
   */
  public function toArray()
  {
      $vars = get_object_vars($this);
      unset($vars['comments']);
      return $vars;
  }

}

?>
