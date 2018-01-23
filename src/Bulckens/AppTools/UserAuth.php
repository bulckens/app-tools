<?php

namespace Bulckens\AppTools;

class UserAuth {
  
  protected $permission;

  public function __construct( $permission = null ) {
    $this->permission = $permission;
  }


  // Ensure authenticated session
  public function __invoke( $req, $res, $next ) {
    // get current uri and format
    $uri = $req->getUri()->getPath();
    $format = pathinfo( $uri, PATHINFO_EXTENSION );

    // initialize output container
    $output = new Output( empty( $format ) ? 'html' : $format );
    
    // fail when not being logged in
    if ( ! User::loggedIn( $req->getParam( 'user_token' ) ) ) {
      $output->add([ 'error' => 'login.required' ])->status( 401 );

    } elseif ( ! is_null( $this->permission ) ) {
      // get user
      $user = User::get( $req->getParam( 'user_token' ) );

      // test permissions
      if ( ! $user->hasAnyAccess( $this->permission ) ) {
        $output->add([
          'error'   => 'permission.not_granted'
        , 'details' => [ 'requirement' => $this->permission ]
        ])->status( 401 );
      }
    }

    // passes
    if ( $output->ok() ) {
      return $next( $req, $res );
    }

    // error
    return $res->withHeader( 'Content-type', $output->mime() )
               ->withStatus( $output->status() )
               ->write( $output->render() );
  }

}