<?php

namespace Bulckens\AppTools\Helpers;

use Exception;
use Illuminate\Support\Str;

class UploadableHelper {

  // Build upload dir from various parameters
  public static function dir( $object, $name, $format ) {
    // find required fields
    preg_match_all( '/\{\{\s?([A-Za-z0-9_]+)\s?\}\}/', $format, $fields );

    // insert data
    foreach ( $fields[1] as $field ) {
      switch ( $field ) {
        // get object id
        case 'id':
          if ( is_null( $value = $object->id ) ) {
            throw new UploadableHelperObjectIdMissingException( "Got a request to insert the object id but it's not there" );
          }
        break;

        // get plural model class name
        case 'model':
          $class = get_class( $object );
          $parts = explode( '\\', $class );
          $model = end( $parts );
          $value = Str::plural( Str::snake( $model ) );
        break;

        // get plural uploadable name
        case 'name':
          $value = Str::plural( $name );
        break;

        // get namespace
        
        default:
          throw new UploadableHelperDirFieldUnknownException( "Got '$field' which is not in the list of known fields" );
        break;
      }

      // replace data
      $format = preg_replace( "/\{\{\s?$field\s?\}\}/", $value, $format );
    }

    return $format;
  }

}


// Exceptions
class UploadableHelperDirFieldUnknownException extends Exception {}
class UploadableHelperObjectIdMissingException extends Exception {}