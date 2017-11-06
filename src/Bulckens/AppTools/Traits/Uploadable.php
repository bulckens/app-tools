<?php

namespace Bulckens\AppTools\Traits;

use Illuminate\Database\Eloquent\MassAssignmentException;
use Bulckens\AppTools\Upload\Tmp;
use Bulckens\AppTools\Upload\File;

trait Uploadable {

  protected $uploadable;
  protected $upload_queue = [];


  // Set the upload attributes
  public function __set( $name, $value ) {
    // get uploadable
    $uploadable = $this->uploadable() ?: $this->uploadable;

    // test presence of uploadable
    if ( isset( $uploadable[$name] ) ) {
      // prepare upload tmp instance with given settings
      $tmp = new Tmp( $value, $uploadable[$name] );
      $this->upload_queue[$name] = $tmp;

      // prepare property names
      $upload_name = "{$name}_name";
      $upload_size = "{$name}_size";
      $upload_mime = "{$name}_mime";
      $upload_meta = "{$name}_meta";

      // store 
      $this->$upload_name = $tmp->basename() . '.' . $tmp->ext();
      $this->$upload_size = $tmp->size();
      $this->$upload_mime = $tmp->mime();

      if ( ! empty( $tmp->meta() ) ) {
        $this->$upload_meta = $tmp->meta();
      }

      return $this;

    } else {
      // with default setter
      return parent::__set( $name, $value );
    }
  }


  // Get the upload attribute
  public function __get( $name ) {
    // get uploadable
    $uploadable = $this->uploadable() ?: $this->uploadable;

    if ( isset( $uploadable[$name] ) ) {
      // prepare property names
      $name_field = "{$name}_name";
      $size_field = "{$name}_size";
      $mime_field = "{$name}_mime";
      $meta_field = "{$name}_meta";

      if ( count( array_filter([ $this->$name_field, $this->$size_field, $this->$mime_field ]) ) == 3 ) {
        // retrieve 
        $source = [
          'name' => $this->$name_field
        , 'size' => $this->$size_field
        , 'mime' => $this->$mime_field
        , 'meta' => $this->$meta_field
        , 'interpolations' => [
            'object' => $this
          , 'name' => $name
          ]
        ];

        // build file
        return new File( $source, $uploadable[$name] );
      }
    }

    // contiute with parent getter
    return parent::__get( $name );
  }


  // Fill attributes
  public function fill( array $attributes ) {
    // get uploadable
    $uploadable = $this->uploadable() ?: $this->uploadable;

    // NOTE: duplicated code form Eloquent for correct mass assignment
    $totallyGuarded = $this->totallyGuarded();

    foreach ( $this->fillableFromArray( $attributes ) as $name => $value ) {
      $name = $this->removeTableFromKey( $name );

      if ( $this->isFillable( $name ) && isset( $uploadable[$name] ) ) {
        // use magic setter
        $this->$name = $value;

        // remove upload attribute
        unset( $attributes[$name] );
        
      } elseif ( $totallyGuarded ) {
        throw new MassAssignmentException( $name );
      }
    }

    return parent::fill( $attributes );
  }


  // Store uploads
  protected function storeUploads() {
    foreach ( $this->upload_queue as $name => $upload ) {
      // store with dir path interpolations
      $upload->store([
        'object' => $this
      , 'name' => $name
      ]);
    }

    return $this;
  }


  // Uploadable placeholder function
  protected function uploadable() {}

}