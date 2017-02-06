<?php

namespace Bulckens\AppTests;

class TestHandler {

  // 500
  public static function error( $c ) {
    return function ( $req, $res, $exception ) use ($c) {
      return $c['response']
        ->withStatus( 500 )
        ->withHeader( 'Content-Type', 'text/plain' )
        ->write( 'Something went wrong!' );
    };
  }
  
  // 404
  public static function notFound( $c ) {
    return function ( $req, $res ) use ( $c ) {
      return $c['response']
        ->withStatus( 404 )
        ->withHeader( 'Content-type', 'text/plain' )
        ->write( 'Not found!' );
    };
  }
  
  // 405
  public static function notAllowed( $c ) {
    return function ( $req, $res, $methods ) use ($c) {
      return $c['response']
        ->withStatus(405)
        ->withHeader( 'Allow', implode( ', ', $methods ) )
        ->withHeader( 'Content-type', 'text/plain' )
        ->write( 'Method must be one of: ' . implode( ', ', $methods ) );
    };
  }

}