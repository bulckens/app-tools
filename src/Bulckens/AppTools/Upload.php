<?php

namespace Bulckens\AppTools;

use Exception;
use Bulckens\Helpers\MemoryHelper;
use Bulckens\AppTools\Traits\Configurable;

class Upload {

  use Configurable;

  protected $key;
  protected $name;
  protected $tmp_name;
  protected $error;
  protected $size;
  protected $mime;
  protected $storage = 'default';


  public function __construct( $key, $storage = 'default' ) {
    global $_FILES;

    // store key and storage
    $this->key = $key;
    $this->storage = $storage;

    // store aditional data
    if ( $this->exists() ) {
      // set given upload values
      $this->name = $_FILES[$key]['name'];
      $this->tmp_name = $_FILES[$key]['tmp_name'];
      $this->error = $_FILES[$key]['error'];
    }
  }


  // Get the key
  public function key() {
    return $this->key;
  }


  // Get/set the file name
  public function name( $name = null ) {
    // act as getter
    if ( is_null( $name ) ) return $this->name;

    // continue as setter
    $this->name = "$name." . pathinfo( $this->name, PATHINFO_EXTENSION );

    return $this;
  }


  // Get the temporary name
  public function tmpName() {
    return $this->tmp_name;
  }


  // Get the error type
  public function error() {
    return $this->error;
  }


  // Get the file size
  public function size() {
    if ( ! isset( $this->size ) ) {
      $this->size = filesize( $this->tmp_name );
    }

    return $this->size;
  }


  // Get the human readable file size
  public function weight() {
    return MemoryHelper::humanize( $this->size() );
  }


  // Get the file mime type
  public function mime() {
    if ( ! isset( $this->mime ) ) {
      $this->mime = mime_content_type( $this->tmp_name );
    }

    return $this->mime;
  }


  // Get/set the storage destination
  public function storage( $storage = null ) {
    // act as setter
    if ( is_string( $storage ) && $this->config( "storage.$storage" ) ) {
      $this->storage = $storage;

      return $this;
    }

    // continue as getter
    if ( is_null( $storage ) && $this->config( "storage.$this->storage" ) ) {
      return $this->storage;
    }

    // fail
    $storage = $storage ?: $this->storage;

    throw new UploadStorageNotConfiguredException( "No '$storage' storage is defined" );
  }


  // Test existace of key in files array
  public function exists() {
    global $_FILES;

    if ( ! isset( $_FILES[$this->key] ) ) {
      throw new UploadKeyNotFoundException( "The '{$this->key}' file could not be found" );
    }

    if ( ! file_exists( $_FILES[$this->key]['tmp_name'] ) ) {
      throw new UploadTmpNameNotFoundException( "The file {$this->tmp_name} could not be found" );
    }

    return true;
  }

}


// Exceptions
class UploadStorageNotConfiguredException extends Exception {}
class UploadTmpNameNotFoundException extends Exception {}
class UploadKeyNotFoundException extends Exception {}
