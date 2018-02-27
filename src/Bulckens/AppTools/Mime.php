<?php 

namespace Bulckens\AppTools;

use Exception;
use Bulckens\Helpers\MimeHelper;

class Mime {

  // Get mime output map
  public static function type( $format = null ) {
    if ( is_null( $format ) )
      return MimeHelper::map();

    if ( $mime = MimeHelper::get( $format ) ) return $mime;

    throw new MimeTypeMissingException( "Mime type for '$format' could not be found" ); 
  }

  // Comment text string based on format
  public static function comment( $string, $format ) {
    switch ( $format ) {
      case 'js':
      case 'css':
      case 'map':
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