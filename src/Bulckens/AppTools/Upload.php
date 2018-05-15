<?php

namespace Bulckens\AppTools;

use Exception;
use Illuminate\Support\Str;
use Bulckens\Helpers\MimeHelper;
use Bulckens\Helpers\MemoryHelper;
use Bulckens\AppTools\Traits\Configurable;

abstract class Upload {

  use Configurable;

  protected $dir;
  protected $root;
  protected $name;
  protected $size;
  protected $mime;
  protected $styles;
  protected $source;
  protected $name_format;
  protected $image_dimensions;
  protected $meta = [];
  protected $storage = 'default';

  public function __construct( $source, $options = [] ) {
    // store source
    $this->source = $source;

    // set different config file
    $this->configFile( isset( $options['config'] ) ? $options['config'] : 'upload.yml' );

    // set different storage option
    if ( isset( $options['storage'] ) ) {
      $this->storage = $options['storage'];
    }

    // define styles
    if ( isset( $options['styles'] ) ) {
      $this->styles = $options['styles'];
    }

    // store dir
    $this->dir = $this->dirFormat( $options );

    // define name format
    $this->name_format = $this->nameFormat( $options );
  }


  // Get style file data
  public function __get( $name ) {
    if ( is_array( $this->styles ) ) {
      if ( preg_match( '/(.*)_(name|file|url)$/', $name, $matches ) ) {
        $style = $matches[1];
        $field = $matches[2];
        
        if ( isset( $this->styles[$style] ) ) {
          return $this->$field( $style );
        }
      }
    }
  }


  // Get the original source parameters
  public function source() {
    return $this->source;
  }


  // Get the file name
  public function name( $style = null ) {
    // get name format
    $name = $this->name_format;

    // insert local method values
    foreach ( [ 'basename', 'ext', 'width', 'height' ] as $method ) {
      $name = preg_replace( "/\{\{\s?$method\s?\}\}/", $this->$method(), $name );
    }

    // insert full name
    $name = preg_replace( '/\{\{\s?name\s?\}\}/', $this->basename() . '.' . $this->ext(), $name );

    // insert style
    $name = preg_replace( '/\{\{\s?style\s?\}\}/', $style ?: 'original', $name );

    return $name;
  }


  // Get the base name
  public function basename() {
    // get file name alone
    $name = preg_replace( '/\.[a-zA-Z0-9]{1,8}$/', '', $this->name ?: $this->source['name'] );

    // sanitize if required (on by default)
    if ( $this->config( 'sanitize', true ) ) {
      $name = Str::slug( $name );
    }

    return $name;
  }


  // Get (real) extension based on mime type
  public function ext() {
    // get extension from mime type
    $ext = MimeHelper::ext( $this->mime() );

    // fall back on given extension in case the given mime type is unknown
    if ( is_null( $ext ) ) {
      $ext = pathinfo( $this->source['name'], PATHINFO_EXTENSION );  
    }

    return strtolower( $ext );
  }


  // Get the human readable file size
  public function weight() {
    return MemoryHelper::humanize( $this->size() );
  }


  // Get dimensions of source (image)
  public function dimensions() {
    if ( $this->isImage() ) {
      return $this->image_dimensions;
    }
  }


  // Get width of source (image)
  public function width() {
    if ( $this->isImage() ) {
      return $this->image_dimensions['width'];
    }
  }


  // Get height of source (image)
  public function height() {
    if ( $this->isImage() ) {
      return $this->image_dimensions['height'];
    }
  }


  // Get/set root directory
  public function root( $root = null ) {
    if ( empty( func_get_args() ) ) {
      if ( empty( $this->root ) ) {
        if ( $this->config( "storage.$this->storage.type" ) == 'filesystem' ) {
          // get root from config
          $root = $this->config( "storage.$this->storage.root" );

          // default to app root
          if ( empty( $root ) ) $root = App::root();
        }
      } else {
        $root = $this->root;
      }

      // make sure there is a trailing slash
      return preg_replace( '/\/\z/', '', $root );
    }

    // continue as setter
    $this->root = $root;

    return $this;
  }


  // Get/set subdirectory
  public function dir( $dir = null ) {
    // act as getter
    if ( is_null( $dir ) ) {
      return preg_replace( '/\/\z/', '', str_replace( '//', '/', "/$this->dir" ) );
    }

    // continue as setter
    $this->dir = $dir;

    return $this;
  }


  // Get public path
  public function path( $style = null ) {
    return str_replace( '//', '/', $this->dir() . '/' . $this->name( $style ) );
  }


  // Get full file path
  public function file( $style = null ) {
    // get path
    $file = $this->root() . $this->path( $style );

    // // add a time stamp if the file already exists
    // if ( file_exists( $file ) ) {
    //   $file = preg_replace( '/(\.[a-zA-Z0-9]{1,8})$/', ".$this->stamp$1", $file );
    // }

    return $file;
  }


  // Get public url
  public function url( $style = null, $options = [] ) {
    // ensure default options
    $options = array_replace([
      'protocol' => 'https:'
    ], $options );

    switch ( $this->config( "storage.$this->storage.type" ) ) {
      case 's3':
        // get region and bucket
        $region = $this->config( "storage.$this->storage.region" );
        $bucket = $this->config( "storage.$this->storage.bucket" );

        $host = "$bucket.s3-$region.amazonaws.com";
      break;
      case 'filesystem':
        // get configured host with fallback to current host
        $host = $this->config( "storage.$this->storage.host", $_SERVER['HTTP_HOST'] );
      break;
    } 

    // make sure host has no trailing slash
    $host = preg_replace( '/\/\z/', '', $host );
    
    return $options['protocol'] . "//$host" . $this->path( $style );
  }


  // Get name format
  public function nameFormat( $options = [] ) {
    if ( isset( $options['name'] ) ) {
      return $options['name'];

    } elseif ( $name = $this->config( "storage.$this->storage.name" ) ) {
      return $name;

    } elseif ( is_array( $this->styles ) ) {
      return '{{ basename }}-{{ style }}.{{ ext }}';

    } else {
      return '{{ basename }}.{{ ext }}';
    }
  }


  // Get the dir format
  public function dirFormat( $options = [] ) {
    if ( isset( $options['dir'] ) ) {
      return $options['dir'];

    } elseif ( $dir = $this->config( "storage.$this->storage.dir" ) ) {
      return $dir;

    } else {
      return '{{ model }}/{{ id }}/{{ name }}';
    }
  }

}


// Exceptions
class UploadUnableToCreateDirectoryException extends Exception {}
class UploadS3CredentialsNotDefinedException extends Exception {}
class UploadStorageNotConfiguredException extends Exception {}
class UploadImageMagickNotFoundException extends Exception {}
class UploadSourceNotAcceptableException extends Exception {}
class UploadStorageTypeUnknownException extends Exception {}
class UploadS3BucketNotDefinedException extends Exception {}
class UploadS3RegionNotDefinedException extends Exception {}
class UploadSourceIncompleteException extends Exception {}
class UploadFileNotDeletableException extends Exception {}
class UploadTmpNameNotFoundException extends Exception {}
class UploadStyleNotValidException extends Exception {}
class UploadAlreadyStoredException extends Exception {}
class UploadFileNotValidException extends Exception {}
class UploadKeyNotFoundException extends Exception {}

