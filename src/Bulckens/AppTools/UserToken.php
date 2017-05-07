<?php

namespace Bulckens\AppTools;

use Exception;

class UserToken {

  use Traits\Configurable;

  protected $code;
  protected $secret;

  public function __construct( $code, $secret ) {
    $this->file( 'user.yml' );

    // store code
    $this->code = $code;

    // get secret
    if ( $this->config( 'secrets' ) )
      $this->secret = $this->config( "secrets.$secret" );

    // fail if no secret could be found
    if ( ! $this->secret )
      throw new TokenSecretMissingException( "Secret could not be found for $secret" ); 
  }


  // Get generated token
  public function get() {
    $parts = [ $this->secret, $this->code ];
    return hash( 'sha256', implode( '---', $parts ) ) . strrev( $this->code );
  }


  // Validate a token agains the current given parameters
  public function validate( $token ) {
    return $token === $this->get();
  }


  // Return converted secret
  public function secret() {
    return $this->secret;
  }


  // Return given code
  public function code() {
    return $this->code;
  }


  // Convert to string
  public function __toString() {
    return $this->get();
  }


  // Parse code from given token
  public static function persistenceCode( $token ) {
    if ( $match = self::match( $token ) )
      return strrev( $match[2] );
  }


  // Match a given value
  public static function match( $token ) {
    if ( preg_match( '/^([a-z0-9]{64})([A-Za-z0-9]{32})$/', $token, $matches ) )
      return $matches;
  }

}


// Exceptions
class TokenSecretMissingException extends Exception {}