<?php

namespace Bulckens\AppTools;

class UserAuth {
  
  protected $permission;

  public function __construct( $permission ) {
    $this->permission = $permission;
  }


  // Ensure authenticated session
  public function __invoke( $req, $res, $next ) {
    // get current uri and format
    $uri    = $req->getUri()->getPath();
    $format = pathinfo( $uri, PATHINFO_EXTENSION );

    // initialize output container
    $output = new Output( $format );

    // test if user is logged in 
    $logged_in = User::loggedIn();

    // if credentials are provided, start a login session
    // note: currently only possible in dev and test environments for testing purposes
    if ( App::env([ 'dev', 'test' ]) && ! $logged_in ) {
      if ( $credentials = $req->getParam( 'credentials' ) )
        $logged_in = User::login( $credentials['email'], $credentials['password'] );
    }

    // fail when not being logged in
    if ( ! $logged_in ) {
      $output->add([ 'error' => 'login.required' ])->status( 401 );

    } else {
      // get user
      $user = User::get();

      // test permissions
      if ( ! $user->hasAnyAccess( $this->permission ) )
        $output->add([
          'error'   => 'permission.not_granted'
        , 'details' => [ 'requirement' => $this->permission ]
        ])->status( 401 );
    }

    // passes
    if ( $output->ok() )
      return $next( $req, $res );

    // error
    return $res->withHeader( 'Content-type', $output->mime() )
               ->withStatus( $output->status() )
               ->write( $output->render() );
  }

}