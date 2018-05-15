<?php

namespace Bulckens\AppTools\Helpers;

use Exception;
use Illuminate\Support\Str;
use Bulckens\Helpers\StringHelper;

class UploadableHelper {

  // Build upload dir from various parameters
  public static function dir( $format, $options = [] ) {
    // find required fields
    preg_match_all( '/\{\{\s?([A-Za-z0-9_]+)\s?\}\}/', $format, $fields );


    // pre-test presence of object
    if ( ! isset( $options['object'] ) && count( array_intersect( $fields[1], [ 'id', 'id_partition' ] ) ) > 0 ) {
      throw new UploadableHelperObjectMissingException( "Object details are required but no object is provided" );
    }


    // insert data
    foreach ( $fields[1] as $field ) {
      switch ( $field ) {
        // get object id
        case 'id':
          if ( is_null( $value = $options['object']->id ) ) {
            throw new UploadableHelperObjectIdMissingException( "Got a request to insert an object id but it's not there" );
          }
        break;

        // get partitioned object id
        case 'id_partition':
          if ( is_numeric( $options['object']->id ) ) {
            $value = StringHelper::partition( $options['object']->id, 3, '/', [ 'padding' => 9, 'with' => 0 ] );
            
          } else {
            throw new UploadableHelperObjectIdMissingException( "Got a request to insert a partitioned object id but no id could be found" );
          }
        break; 

        // get plural model class name
        case 'model':
          $class = get_class( $options['object'] );
          $parts = explode( '\\', $class );
          $model = end( $parts );
          $value = Str::plural( Str::snake( $model ) );
        break;

        // get plural uploadable name
        case 'name':
          if ( isset( $options['name'] ) ) {
            $value = Str::plural( $options['name'] );  
          
          } else {
            throw new UploadableHelperNameMissingException( 'A name is required bt not given' );
          }
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
class UploadableHelperNameMissingException extends Exception {}
class UploadableHelperObjectMissingException extends Exception {}
class UploadableHelperDirFieldUnknownException extends Exception {}
class UploadableHelperObjectIdMissingException extends Exception {}