<?php

namespace Bulckens\AppTools;

class UserAuth {
  
  protected $permission;
  protected $output;
  protected $user;

  public function __construct( $permission = null ) {
    $this->permission = $permission;
  }


  // Ensure authenticated session
  public function __invoke( $req, $res, $next ) {
    // get current uri and format
    $uri = $req->getUri()->getPath();
    $format = pathinfo( $uri, PATHINFO_EXTENSION );

    // initialize output container
    $this->output = new Output( empty( $format ) ? 'html' : $format );
    
    // fail when not being logged in
    if ( ! User::loggedIn( $req->getParam( 'user_token' ) ) ) {
      $this->output->add([ 'error' => 'login.required' ])->status( 401 );

    } else {
      // get user
      $this->user = User::get( $req->getParam( 'user_token' ) );

      // test permissions
      if ( ! is_null( $this->permission ) && ! $this->user->hasAnyAccess( $this->permission ) ) {
        $this->output->add([
          'error' => 'permission.not_granted'
        , 'details' => [ 'requirement' => $this->permission ]
        ])->status( 401 );
      }
    }

    // test custom validations
    $this->validate( $req, $res );

    // passes
    if ( $this->output->ok() ) {
      return $next( $req, $res );
    }

    // error
    return $this->error( $req, $res );
  }


  // Allow custom validations
  protected function validate( $req, $res ) {}


  // Render error output
  protected function error( $req, $res ) {
    return $res
      ->withHeader( 'Content-type', $this->output->mime() )
      ->withStatus( $this->output->status() )
      ->write( $this->output->render() );
  }

}