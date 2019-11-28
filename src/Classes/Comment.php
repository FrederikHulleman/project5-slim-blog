<?php

namespace Project5SlimBlog;

Class Comment {

  private $id,$name,$date,$body;

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
      if (isset($data['id'])) {
          $this->setId($data['id']);
      }
      if (isset($data['name'])) {
          $this->setName($data['name']);
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
      return $this->id;
  }

  /**
   * Cleans up and sets the local property $id
   * @param int $value Data may be from database or user
   */
  public function setId($value)
  {
      $this->id = trim(filter_var($value, FILTER_SANITIZE_NUMBER_INT));
  }

  /**
   * Gets the local property $name
   * @return string
   */
  public function getName()
  {
      return $this->name;
  }

  /**
   * Cleans up and sets the local property $name
   * @param string $value to set property
   */
  public function setName($value)
  {
      $this->name = trim(filter_var($value, FILTER_SANITIZE_STRING));
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

  /**
   * Convert the current object to an associative array of parameters
   * @return array of object parameters
   */
  public function toArray()
  {
      return get_object_vars($this);
  }

}

?>
