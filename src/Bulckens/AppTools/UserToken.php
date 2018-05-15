<?php

namespace Bulckens\AppTools;

use Exception;
use Bulckens\Helpers\TimeHelper;

class UserToken {

  use Traits\Configurable;
  use Traits\Tokenish;

  protected $code;

  public function __construct( $code, $secret, $stamp = null ) {
    $this->configFile( 'user.yml' );

    // store code
    $this->code = $code;

    // store or generate timestamp
    $this->stampless = is_null( $stamp );
    $this->stamp = $stamp ?: TimeHelper::ms();

    // get secret
    if ( $this->config( 'secrets' ) )
      $this->secret = $this->config( "secrets.$secret" );

    // make sure the given secret exists
    $this->verify( $secret );
  }


  // Get generated token
  public function get() {
    $parts = [ $this->secret, $this->stamp, $this->code ];
    return $this->hash( $parts ) . strrev( $this->code );
  }


  // Return given code
  public function code() {
    return $this->code;
  }


  // Parse code from given token
  public static function persistenceCode( $token ) {
    if ( $match = self::match( $token ) )
      return strrev( $match[3] );
  }


  // Match a given value
  public static function match( $token ) {
    if ( preg_match( '/^([a-z0-9]{64})([a-z0-9]{11})([A-Za-z0-9]{32})$/', $token, $matches ) )
      return $matches;
  }

}
