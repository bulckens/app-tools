<?php

namespace Bulckens\AppTools\Traits;

use Exception;

trait Tokenish {

  protected $secret;
  protected $stamp;
  protected $stampless;

  // Return converted secret
  public function secret() {
    return $this->secret;
  }


  // Return stamp
  public function stamp() {
    return $this->stamp;
  }


  // Hash given array with parameters and current timestamp
  public function hash( array $parts ) {
  	return hash( 'sha256', implode( '---', $parts ) ) . dechex( $this->stamp );
  }


  // Validate a token against the current given parameters
  public function validate( $token ) {
    // use the token's stamp if none was explicitly given
    if ( $this->stampless )
      $this->stamp = self::timestamp( $token );

    return $token === $this->get();
  }


  // Verify presence of given secret
  public function verify( $secret ) {
  	if ( ! $this->secret )
  	  throw new TokenishSecretMissingException( "Secret could not be found for $secret" );

  	return $this;
  }


  // Convert to string
  public function __toString() {
    return $this->get();
  }


  // Parse timestamp from given token
  public static function timestamp( $token ) {
    return hexdec( substr( $token, 64, 11 ) );
  }


}

// Exceptions
class TokenishSecretMissingException extends Exception {}
