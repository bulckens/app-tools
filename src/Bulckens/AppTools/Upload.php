<?php

namespace Bulckens\AppTools;

use Exception;
use Illuminate\Support\Str;
use Bulckens\Helpers\TimeHelper;
use Bulckens\Helpers\MemoryHelper;
use Bulckens\AppTools\Traits\Configurable;

class Upload {

  use Configurable;

  protected $key;
  protected $ext;
  protected $name;
  protected $tmp_name;
  protected $error;
  protected $size;
  protected $mime;
  protected $stamp;
  protected $storage = 'default';


  public function __construct( $key, $options = [] ) {
    global $_FILES;

    // set different config file
    if ( isset( $options['config'] ) ) {
      $this->configFile( $options['config'] );
    }

    // set different storage option
    if ( isset( $options['storage'] ) ) {
      $this->storage = $options['storage'];
    }

    // store key and create stamp
    $this->key = $key;
    $this->stamp = TimeHelper::ms();

    // store aditional data
    if ( $this->exists() ) {
      // set given upload values
      $this->ext = pathinfo( $_FILES[$key]['name'], PATHINFO_EXTENSION );
      $this->name( $_FILES[$key]['name'] );
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
    if ( is_null( $name ) ) return "$this->name.$this->ext";

    // continue as setter
    $this->name = preg_replace( '/\.[a-zA-Z0-9]{1,8}$/', '', $name );

    // sanitize if required
    if ( $this->config( 'sanitize', true ) ) {
      $this->ext = strtolower( $this->ext );
      $this->name = Str::slug( $this->name );
    }

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


  // Store the file at its configured destination
  public function store( $path = '' ) {
    switch ( $type = $this->config( "storage.$this->storage.type" ) ) {
      case 'local':
        // get absolute file path
        $file = $this->file( $path );

        // make sure sub folder exists
        if ( ! file_exists( dirname( $file ) ) ) {
          mkdir( dirname( $file ), 0777, true );
        }

        // move file
        if ( App::env( 'dev' ) ) {
          return rename( $this->tmp_name, $file );
        } else {
          return move_uploaded_file( $this->tmp_name, $file );
        }
      break;
      case 's3':

      break;
      default:
        throw new UploadStorageTypeUnknownException( "Storage type '$type' is not implemented" );
      break;
    }

    return true;
  }


  // Get full file path
  public function file( $path = null ) {
    // build the configured root
    $root = App::root( $this->config( "storage.$this->storage.dir" ) );

    // add the given path
    if ( is_string( $path ) ) {
      $root = preg_replace( '/\/$/', '', str_replace( '//', '/', "$root/$path" ) );
    }

    // build the absolute file name
    $file = "$root/" . $this->name();

    // add a time stamp if the file already exists
    if ( file_exists( $file ) ) {
      $file = preg_replace( '/(\.[a-zA-Z0-9]{1,8})$/', ".$this->stamp$1", $file );
    }

    return $file;
  }

}


// Exceptions
class UploadStorageNotConfiguredException extends Exception {}
class UploadStorageTypeUnknownException extends Exception {}
class UploadTmpNameNotFoundException extends Exception {}
class UploadKeyNotFoundException extends Exception {}

