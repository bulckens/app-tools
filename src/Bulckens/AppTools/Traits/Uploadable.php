<?php

namespace Bulckens\AppTools\Traits;

use Bulckens\AppTools\Upload;

trait Uploadable {

  protected $uploadable;
  protected $upload_queue = [];

  // Set the impload attributes
  public function __set( $name, $value ) {
    // test presence of uploadable
    if ( isset( $this->uploadable[$name] ) ) {
      // prepare upload instance
      $this->upload_queue[$name] = new Upload( $value );

      // prepare property names
      $upload_name = "{$name}_name";
      $upload_size = "{$name}_size";
      $upload_mime = "{$name}_mime";

      // store 
      $this->$upload_name = $this->upload_queue[$name]->name();
      $this->$upload_size = $this->upload_queue[$name]->size();
      $this->$upload_mime = $this->upload_queue[$name]->mime();
    }

    // contiute with parent setter
    parent::__set( $name, $value );
  }

}