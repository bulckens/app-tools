<?php

namespace Bulckens\AppTools\Upload;

use Exception;
use Aws\S3\S3Client;
use Bulckens\Helpers\TimeHelper;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Upload;
use Bulckens\AppTools\Interfaces\UploadInterface;
use Bulckens\AppTools\Helpers\UploadableHelper;
use Bulckens\CliTools\System;

class Tmp extends Upload implements UploadInterface {

  protected $stamp;
  protected $magick;
  protected $convert;
  protected $is_upload;
  protected $stored = false;

  public function __construct( $source, $options = [] ) {
    global $_FILES;

    // detect and prepare source
    if ( is_string( $source ) ) {
      if ( ! isset( $_FILES[$source] ) ) {
        throw new TmpKeyNotFoundException( "The '{$source}' upload could not be found" );
      }

      $source = $_FILES[$source];

    } elseif ( ! is_array( $source ) ) {
      throw new TmpSourceNotAcceptableException( "String or array expected but got " . gettype( $source ) );
    }

    // initialize parent
    parent::__construct( $source, $options );

    // test completeness of given source
    if ( ! isset( $this->source['name'] ) || ! isset( $this->source['tmp_name'] ) || ! isset( $this->source['error'] ) ) {
      throw new TmpSourceIncompleteException( "Expected source to contain 'name', 'tmp_name' and 'error' keys but it doesn't" );

    // test existance of source file
    } elseif ( ! file_exists( $this->source['tmp_name'] ) ) {
      throw new TmpNameNotFoundException( "The file '{$this->source['tmp_name']}' could not be found" );
    }

    // if set to true, move_uploaded_file() will be used
    if ( isset( $options['is_upload'] ) && is_bool( $options['is_upload'] ) ) {
      $this->is_upload = $options['is_upload'];
    } else {
      $this->is_upload = ! App::env( 'dev' );
    }

    // define additional image magick command
    if ( isset( $options['convert'] ) ) {
      $this->convert = $options['convert'];
    }

    // define image magick command
    $os = System::os();
    $this->magick = $this->config( "magick.$os", '/usr/bin/convert' );

    // store and create stamp
    $this->stamp = TimeHelper::ms();
  }


  // Set a new file name
  public function rename( $name ) {
    $this->name = $name;

    return $this;
  }


  // Get the file mime type
  public function mime() {
    if ( ! isset( $this->mime ) ) {
      $this->mime = mime_content_type( $this->tmpName() );
    }

    return $this->mime;
  }


  // Get additional meta data for current
  public function meta() {
    if ( empty( $this->meta ) ) {
      if ( $this->isImage() ) {
        $this->meta = [
          'width' => $this->width()
        , 'height' => $this->height()
        ];
      }
    }

    return json_encode( $this->meta );
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


  // Get the file size
  public function size() {
    if ( ! isset( $this->size ) ) {
      $this->size = filesize( $this->tmpName() );
    }

    return $this->size;
  }


  // Get the temporary name and create the file if non-existant
  public function tmpName( $style = null ) {
    $tmp_name = $this->source['tmp_name'];

    // add style reference to tmp name
    if ( is_string( $style ) && isset( $this->styles[$style] ) && $style != 'original' ) {
      $tmp_name .= ".style-$style" ;

      // create file if non-existant
      if ( ! file_exists( $tmp_name ) ) {
        $this->createTmpStyle(
          $tmp_name
        , $this->styles[$style]
        , isset( $this->convert[$style] ) ? $this->convert[$style] : $this->convert
        );
      }
    }

    return $tmp_name;
  }


  // Get the error type
  public function error() {
    return $this->source['error'];
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

    throw new TmpStorageNotConfiguredException( "No '$storage' storage is defined" );
  }


  // Store the file at its configured destination
  public function store( $interpolations = [] ) {
    // convert dir to contain possible post-save an other parameters
    $this->dir = UploadableHelper::dir( $this->dir, $interpolations );

    // get absolute file path
    $file = $this->file();

    // do not allow store to be called twice
    if ( $this->stored ) {
      throw new TmpAlreadyStoredException( "The file '$file' has already been stored" );
    }

    // store file in every style
    if ( is_array( $this->styles ) ) {
      foreach ( $this->styles as $style => $resize ) {
        $this->storeStyle( $style );
      }
    } else {
      $this->storeStyle();
    }

    // mark as stored
    return $this->stored = true;
  }


  // Store a file in a given style
  protected function storeStyle( $style = null ) {
    // detect valid uploaded file
    if ( $this->is_upload && ! is_uploaded_file( $this->tmpName() ) ) {
      throw new TmpFileNotValidException( "The given file '$this->tmpName()' is not valid" );
    }

    // get file paths in the given style
    $file = $this->file( $style );

    // get tmp file for style
    $tmp_name = $this->tmpName( $style );

    // store file according to config
    switch ( $type = $this->config( "storage.$this->storage.type" ) ) {
      case 'filesystem':
        // detect local file storage (not stream)
        if ( ! ( $is_stream = strpos( $file, '://' ) > 0 ) ) {
          // make sure sub dir exists
          if ( ! file_exists( $dir = dirname( $file ) ) ) {
            if ( ! mkdir( $dir, 0777, true ) ) {
              throw new TmpUnableToCreateDirectoryException( "Unable to create dir '$dir'" );
            }
          }

          // make sure target dir is writable
          if ( ! is_writable( $dir ) ) {
            throw new TmpDirectoryNotWritableException( "The target dir '$dir' is not writable" );
          }
        }

        // store file
        if ( $this->is_upload && $tmp_name == $this->tmpName() ) {
          $stored = move_uploaded_file( $tmp_name, $file );
        } elseif ( $is_stream ) {
          $stored = copy( $tmp_name, $file );
        } else {
          $stored = rename( $tmp_name, $file );
        }

        // fail if not stored
        if ( ! $stored ) {
          throw new TmpFileNotMovableException( "The uploaded file '$tmp_name' could not be moved to '$file'" );
        }

        // fail if unable to delete source file
        if ( $is_stream && ! unlink( $tmp_name ) ) {
          throw new TmpFileNotDeletableException( "The uploaded file '$tmp_name' could not be deleted" );
        }

        // get url
        $url = str_replace( App::root(), '/', $file );

      break;
      case 's3':
        // get region
        if ( ! ( $region = $this->config( "storage.$this->storage.region" ) ) ) {
          throw new TmpS3RegionNotDefinedException( 'No S3 region is defined (e.g. eu-west-1)' );
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
          throw new TmpS3CredentialsNotDefinedException( 'No S3 access key and/or secret are defined' );
        }

        // get bucket or fail
        if ( ! ( $bucket = $this->config( "storage.$this->storage.bucket" ) ) ) {
          throw new TmpS3BucketNotDefinedException( 'No S3 bucket is defined to store the file' );
        }

        // upload file
        $result = $client->putObject([
          'Bucket' => $bucket
        , 'Key' => preg_replace( '/^\//', '', $file )
        , 'Body' => fopen( $tmp_name, 'r' )
        , 'ACL' => 'public-read'
        ]);

        // fail if unable to delete source file
        if ( ! unlink( $tmp_name ) ) {
          throw new TmpFileNotDeletableException( "The uploaded file '$tmp_name' could not be deleted" );
        }

      break;
      default:
        throw new TmpStorageTypeUnknownException( "Storage type '$type' has not been implemented" );
      break;
    }
  }


  // Create temporary style file
  protected function createTmpStyle( $tmp_name, $resize, $convert = null ) {
    // parse resize command
    preg_match( '/^(\d+)x(\d+)([#^>!]?)$/', $resize, $m );

    if ( isset( $m[1] ) && is_numeric( $m[1] ) && is_numeric( $m[2] ) ) {
      // output destination
      $devnull = '2>/dev/null';

      // test imagemagick path
      if ( empty( exec( "$this->magick $devnull" ) ) ) {
        throw new TmpImageMagickNotFoundException( "Expected to find ImageMagick at '$this->magick' but it's not there" );
      }

      // gather parameters
      list( $c, $w, $h, $f ) = $m;

      // interpret flags
      $f = in_array( $f, [ '>', '!', '^', '#', '' ] ) ? $f : '>';
      $f = in_array( $f, [ '>', '!', ] ) ? "\\$f" : $f;

      // build resize command
      $command = "$this->magick {$this->source['tmp_name']} -resize {$w}x{$h}";

      // crop or flag
      $command .= $f == '#' ? "^ -gravity center -crop {$w}x{$h}+0+0" : $f;

      // execute resize command
      exec( "$command $tmp_name $devnull" );

      // additional command
      if ( is_string( $convert ) ) {
        exec( "$this->magick $tmp_name $convert $tmp_name $devnull" );
      }

    } else {
      throw new TmpStyleNotValidException( "Unable to parse the style '$resize'" );
    }
  }

}


// Exceptions
class TmpUnableToCreateDirectoryException extends Exception {}
class TmpS3CredentialsNotDefinedException extends Exception {}
class TmpStorageNotConfiguredException extends Exception {}
class TmpImageMagickNotFoundException extends Exception {}
class TmpSourceNotAcceptableException extends Exception {}
class TmpStorageTypeUnknownException extends Exception {}
class TmpS3BucketNotDefinedException extends Exception {}
class TmpS3RegionNotDefinedException extends Exception {}
class TmpSourceIncompleteException extends Exception {}
class TmpFileNotDeletableException extends Exception {}
class TmpStyleNotValidException extends Exception {}
class TmpAlreadyStoredException extends Exception {}
class TmpNameNotFoundException extends Exception {}
class TmpFileNotValidException extends Exception {}
class TmpKeyNotFoundException extends Exception {}
