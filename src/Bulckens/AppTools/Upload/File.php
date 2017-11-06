<?php

namespace Bulckens\AppTools\Upload;

use Exception;
use Aws\S3\S3Client;
use Illuminate\Support\Str;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Upload;
use Bulckens\AppTools\Interfaces\UploadInterface;
use Bulckens\AppTools\Helpers\UploadableHelper;

class File extends Upload implements UploadInterface {

  protected $stored = true;

  public function __construct( $source, $options = [] ) {
    // detect and prepare source
    if ( ! is_array( $source ) ) {
      throw new FileSourceNotAcceptableException( "Array expected as source but got " . gettype( $source ) );
    }

    // initialize parent
    parent::__construct( $source, $options );

    // test completeness of given source
    if ( ! isset( $this->source['name'] ) || ! isset( $this->source['mime'] ) || ! isset( $this->source['size'] ) ) {
      throw new FileSourceIncompleteException( "Expected source to contain 'name', 'mime' and 'size' keys but it doesn't" );
    }
  }


  // Get the file mime type
  public function mime() {
    return $this->source['mime'];
  }


  // Test if source is an image
  public function isImage() {
    if ( is_null( $this->image_dimensions ) ) {
      if ( is_numeric( $w = $this->meta( 'width' ) ) && is_numeric( $h = $this->meta( 'height' ) ) ) {
        $this->image_dimensions = [ $w, $h, 'width' => $w, 'height' => $h ];
      }
    }

    return !! $this->image_dimensions;
  }


  // Get the file size
  public function size() {
    return $this->source['size'];
  }


  // Parse given meta data
  public function meta( $key = null ) {
    if ( empty( $this->meta ) && isset( $this->source['meta'] ) ) {
      $this->meta = json_decode( $this->source['meta'], true );
    }

    if ( is_null( $key ) ) {
      return $this->meta;
    } elseif ( isset( $this->meta[$key] ) ) {
      return $this->meta[$key];
    }
  }


  // Get/set and interpolate dir
  public function dir( $dir = null ) {
    // interpolated getter
    if ( is_null( $dir ) && isset( $this->source['interpolations'] ) ) {
      return UploadableHelper::dir( parent::dir(), $this->source['interpolations'] );
    }

    // norma getter and setter
    return parent::dir( $dir );
  }

}


// Exceptions
class FileSourceNotAcceptableException extends Exception {}
class FileSourceIncompleteException extends Exception {}
