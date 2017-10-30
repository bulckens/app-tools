<?php

namespace Bulckens\AppTools\Traits;

use Illuminate\Database\Eloquent\MassAssignmentException;
use Bulckens\AppTools\Upload;

trait Uploadable {

  protected $uploadable;
  protected $upload_queue = [];


  // Set the upload attributes
  public function __set( $name, $value ) {
    // test presence of uploadable
    if ( isset( $this->uploadable[$name] ) ) {
      // prepare upload instance with given settings
      $this->upload_queue[$name] = new Upload( $value, $this->uploadable[$name] );

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
    return parent::__set( $name, $value );
  }


  // Get the upload attribute
  public function __get( $name ) {
    if ( isset( $this->upload_queue[$name] ) ) {
      return $this->upload_queue[$name];
    }

    // contiute with parent getter
    return parent::__get( $name );
  }


  // Fill attributes
  public function fill( array $attributes ) {
    // original code form Eloquent for correct mass assignment
    $totallyGuarded = $this->totallyGuarded();

    foreach ( $this->fillableFromArray( $attributes ) as $name => $value ) {
      $name = $this->removeTableFromKey( $name );

      if ( $this->isFillable( $name ) && isset( $this->uploadable[$name] ) ) {
        // use magic setter
        $this->$name = $value;

        // remove upload attribute
        unset( $attributes[$name] );
        
      } elseif ( $totallyGuarded ) {
        throw new MassAssignmentException( $name );
      }
    }

    parent::fill( $attributes );
  }

}