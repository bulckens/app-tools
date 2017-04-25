<?php 

namespace Bulckens\AppTools;

use Exception;

class Mime {

  protected static $map  = [
    'css'  => 'text/css'
  , 'dump' => 'text/plain'
  , 'html' => 'text/html'
  , 'js'   => 'application/javascript'
  , 'json' => 'application/json'
  , 'txt'  => 'text/plain'
  , 'xml'  => 'application/xml'
  , 'yaml' => 'application/x-yaml'
  ];

  // Get mime output map
  public static function type( $format = null ) {
    if ( is_null( $format ) )
      return self::$map;

    if ( isset( self::$map[$format] ) )
      return self::$map[$format];
    else
      throw new MimeTypeMissingException( "Mime type for $format could not be found" ); 
  }

  // Comment text string based on format
  public static function comment( $string, $format ) {
    switch ( $format ) {
      case 'js':
      case 'css':
        return "/*\n$string\n*/";
      break;
      case 'html':
        return "<!--\n$string\n-->";
      break;
      default:
        return $string;
      break;
    }
  }

}


// Exceptions
class MimeTypeMissingException extends Exception {}