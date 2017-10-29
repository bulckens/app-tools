<?php

namespace Bulckens\AppTools;

use Exception;
use Aws\S3\S3Client;
use Illuminate\Support\Str;
use Bulckens\Helpers\TimeHelper;
use Bulckens\Helpers\MimeHelper;
use Bulckens\Helpers\MemoryHelper;
use Bulckens\AppTools\Traits\Configurable;

class Upload {

  use Configurable;

  protected $source;
  protected $dir;
  protected $name;
  protected $size;
  protected $mime;
  protected $stamp;
  protected $is_upload;
  protected $image_dimensions;
  protected $stored = false;
  protected $storage = 'default';


  public function __construct( $source, $options = [] ) {
    global $_FILES;
    
    // set different config file
    if ( isset( $options['config'] ) ) {
      $this->configFile( $options['config'] );
    }

    // set different storage option
    if ( isset( $options['storage'] ) ) {
      $this->storage = $options['storage'];
    }

    // if set to true, move_uploaded_file() will be used
    if ( isset( $options['is_upload'] ) && is_bool( $options['is_upload'] ) ) {
      $this->is_upload = $options['is_upload'];
    } else {
      $this->is_upload = ! App::env( 'dev' );
    }

    // store upload
    if ( is_string( $source ) ) {
      if ( ! isset( $_FILES[$source] ) ) {
        throw new UploadKeyNotFoundException( "The '{$source}' upload could not be found" );
      }

      $this->source = $_FILES[$source];

    } elseif ( is_array( $source ) ) {
      $this->source = $source;

    } else {
      throw new UploadSourceNotAccptableException( "String or array expected but got " . gettype( $source ) );
    }

    // test completeness of given source
    if ( ! isset( $this->source['name'] ) || ! isset( $this->source['tmp_name'] ) || ! isset( $this->source['error'] ) ) {
      throw new UploadSourceIncompleteException( "Expected source to contain a 'name', 'tmp_name' and 'error' keys but it doesn't" );

    // test existance of source file
    } elseif ( ! file_exists( $this->source['tmp_name'] ) ) {
      throw new UploadTmpNameNotFoundException( "The file '{$this->source['tmp_name']}' could not be found" );
    }

    // store and create stamp
    $this->stamp = TimeHelper::ms();
  }


  // Get the original upload parameters
  public function upload() {
    return $this->source;
  }


  // Get/set the file name
  public function name( $name = null ) {
    // act as setter
    if ( $name ) {
      $this->name = $name;

      return $this;
    }

    // get file name alone
    $name = preg_replace( '/\.[a-zA-Z0-9]{1,8}$/', '', $this->name ?: $this->source['name'] );

    // sanitize if required (on by default)
    if ( $this->config( 'sanitize', true ) ) {
      $name = Str::slug( $name );
    }

    return "$name." . $this->ext();
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


  // Get the temporary name
  public function tmpName() {
    return $this->source['tmp_name'];
  }


  // Get the error type
  public function error() {
    return $this->source['error'];
  }


  // Get the file size
  public function size() {
    if ( ! isset( $this->size ) ) {
      $this->size = filesize( $this->tmpName() );
    }

    return $this->size;
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


  // Test if source is an image
  public function isImage() {
    if ( is_null( $this->image_dimensions ) ) {
      if ( $d = getimagesize( $this->tmpName() ) ) {
        $this->image_dimensions = [ $d[0], $d[1], 'width' => $d[0], 'height' => $d[1] ];
      }
    }

    return !! $this->image_dimensions;
  }


  // Get the file mime type
  public function mime() {
    if ( ! isset( $this->mime ) ) {
      $this->mime = mime_content_type( $this->tmpName() );
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


  // Store the file at its configured destination
  public function store() {
    // get absolute file path
    $file = $this->file();

    // do not allow store to be called twice
    if ( $this->stored ) {
      throw new UploadAlreadyStoredException( "The file '$file' has already been stored" );
    }

    // store file according to config
    switch ( $type = $this->config( "storage.$this->storage.type" ) ) {
      case 'filesystem':
        // detect local file storage (not stream)
        if ( ! ( $is_stream = strpos( $file, '://' ) > 0 ) ) {
          // make sure sub dir exists
          if ( ! file_exists( $dir = dirname( $file ) ) ) {
            if ( ! mkdir( $dir, 0777, true ) ) {
              throw new UploadUnableToCreateDirectoryException( "Unable to create dir '$dir'" );
            }
          }

          // make sure target dir is ritable
          if ( ! is_writable( $dir ) ) {
            throw new UploadDirectoryNotWritableException( "The target dir '$dir' is not writable" );
          }

          // detect valid uploaded file
          if ( $this->is_upload && ! is_uploaded_file( $this->tmpName() ) ) {
            throw new UploadFileNotValidException( "The given file '$this->tmpName()' is not valid" );
          }
        }

        // store file
        if ( $this->is_upload ) {
          $stored = move_uploaded_file( $this->tmpName(), $file );
        } elseif ( $is_stream ) {
          $stored = copy( $this->tmpName(), $file );
        } else {
          $stored = rename( $this->tmpName(), $file );
        }

        // fail if not stored
        if ( ! $stored ) {
          throw new UploadFileNotMovableException( "The uploaded file '$this->tmpName()' could not be moved to '$file'" );
        }

        // fail if unable to delete source file
        if ( $is_stream && ! unlink( $this->tmpName() ) ) {
          throw new UploadFileNotDeletableException( "The uploaded file '{$this->tmpName()}' could not be deleted" );
        }

        // get url
        $url = str_replace( App::root(), '/', $file );

      break;
      case 's3':
        // get region
        if ( ! ( $region = $this->config( "storage.$this->storage.region" ) ) ) {
          throw new UploadS3RegionNotDefinedException( 'No S3 region is defined (e.g. eu-west-1)' );
        }

        // get credentials
        $access = $this->config( "storage.$this->storage.access" );
        $secret = $this->config( "storage.$this->storage.secret" );

        if ( $access && $secret ) {
          $client = new S3Client([
            'version' => 'latest'
          , 'region' => $region
          , 'credentials' => [
              'key' => $access
            , 'secret' => $secret
            ]
          ]);
        } else {
          throw new UploadS3CredentialsNotDefinedException( 'No S3 access key and/or secret are defined' );
        }

        // get bucket or fail
        if ( ! ( $bucket = $this->config( "storage.$this->storage.bucket" ) ) ) {
          throw new UploadS3BucketNotDefinedException( 'No S3 bucket is defined to store the file' );
        }

        // upload file
        $result = $client->putObject([
          'Bucket' => $bucket
        , 'Key'    => preg_replace( '/^\//', '', $this->file() )
        , 'Body'   => fopen( $this->tmpName(), 'r' )
        , 'ACL'    => 'public-read'
        ]);

        // fail if unable to delete source file
        if ( ! unlink( $this->tmpName() ) ) {
          throw new UploadFileNotDeletableException( "The uploaded file '{$this->tmpName()}' could not be deleted" );
        }

      break;
      default:
        throw new UploadStorageTypeUnknownException( "Storage type '$type' has not been implemented" );
      break;
    }

    // mark as stored
    return $this->stored = true;
  }


  // Get/set subdirectory
  public function dir( $dir = null ) {
    // act as getter
    if ( is_null( $dir ) ) return $this->dir;

    // continue as setter
    $this->dir = preg_replace( '/\A\/|\/\z/', '', $dir );

    return $this;
  }


  // Get full file path
  public function file() {
    // get path
    $file = $this->path();

    // add root if required
    if ( $this->config( "storage.$this->storage.type" ) == 'filesystem' && ! strpos( $file, '://' ) ) {
      $file = App::root( $file );
    }

    // add a time stamp if the file already exists
    if ( file_exists( $file ) ) {
      $file = preg_replace( '/(\.[a-zA-Z0-9]{1,8})$/', ".$this->stamp$1", $file );
    }

    return $file;
  }

  
  // Get public path
  public function path() {
    // get configured dir
    $dir = $this->config( "storage.$this->storage.dir", '' );

    // add subdir
    if ( $this->dir() ) $dir .= ( empty( $dir ) ? '' : '/') . $this->dir();

    // add name
    $name = '/' . $this->name();
    
    // add leading slash i required
    if ( ! strpos( $dir, '://' ) ) $dir = "/$dir";

    return $dir == '/' ? $name : $dir . $name;
  }


  // Get public url
  public function url( $protocol = 'https:' ) {
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

    return "$protocol//$host" . $this->path();
  }

}


// Exceptions
class UploadUnableToCreateDirectoryException extends Exception {}
class UploadS3CredentialsNotDefinedException extends Exception {}
class UploadStorageNotConfiguredException extends Exception {}
class UploadSourceNotAccptableException extends Exception {}
class UploadStorageTypeUnknownException extends Exception {}
class UploadS3BucketNotDefinedException extends Exception {}
class UploadS3RegionNotDefinedException extends Exception {}
class UploadSourceIncompleteException extends Exception {}
class UploadFileNotDeletableException extends Exception {}
class UploadTmpNameNotFoundException extends Exception {}
class UploadAlreadyStoredException extends Exception {}
class UploadFileNotValidException extends Exception {}
class UploadKeyNotFoundException extends Exception {}

