<?php

namespace Project5SlimBlog;

Class Post {

  private $id,$title,$date,$body;

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
     * Cleans up and sets the local property $id
     * @param int $value Data may be from database or user
     */
    public function setId($value)
    {
        $this->id = trim(filter_var($value, FILTER_SANITIZE_NUMBER_INT));
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
     * Cleans up and sets the local property $date
     * @param string $value to set property
     */
    public function setDate($value)
    {
        $this->date = trim(filter_var($value, FILTER_SANITIZE_STRING));
    }

    /**
     * Cleans up and sets the local property $body
     * @param string $value to set property
     */
    public function setBody($value)
    {
        $this->body = trim(filter_var($value, FILTER_SANITIZE_STRING));
    }

}

?>
