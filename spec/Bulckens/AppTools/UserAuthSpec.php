<?php

namespace spec\Bulckens\AppTools;

use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Cartalyst\Sentinel\Users\EloquentUser;
use Cartalyst\Sentinel\Reminders\EloquentReminder;
use Cartalyst\Sentinel\Activations\EloquentActivation;
use Cartalyst\Sentinel\Persistences\EloquentPersistence;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;
use Bulckens\AppTools\App;
use Bulckens\AppTools\User;
use Bulckens\AppTools\UserAuth;
use Bulckens\AppTools\UserToken;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserAuthSpec extends ObjectBehavior {
  
  protected $req;
  protected $res;
  protected $next;
  protected $credentials;
  protected $args = [
    'format' => 'json'
  ];

  function let() {
    // initialize app
    $app = new App( 'dev' );
    $app->run();

    // fake Slim environment
    $environment = Environment::mock([ 'REQUEST_URI' => '/fake.json' ]);
    $this->req = Request::createFromEnvironment( $environment );
    $this->res = new Response( 200 );
    $this->next = function() { return 'testful success'; };

    // login credentials
    $this->credentials = [ 'email' => 'w@w.w', 'password' => 'v4V4v00m' ];

    // initialize
    $this->beConstructedWith( 'test.permission' );
  }

  function letGo() {
    EloquentUser::truncate();
    EloquentReminder::truncate();
    EloquentActivation::truncate();
    EloquentPersistence::truncate();
    Sentinel::logout();
  }


  // Invoke method
  function it_defaults_to_html_for_the_format() {
    $this->beConstructedWith();

    $environment = Environment::mock([
      'REQUEST_URI'  => '/fake'
    ]);
    $this->req = Request::createFromEnvironment( $environment );

    $response = $this->__invoke( $this->req, $this->res, $this->next )->__toString();
    $response->shouldContain( '<!--' );
    $response->shouldContain( 'error: login.required' );
    $response->shouldEndWith( '-->' );
  }

  function it_requires_a_login() {
    $response = $this->__invoke( $this->req, $this->res, $this->next )->__toString();
    $response->shouldStartWith( 'HTTP/1.1 401 Unauthorized' );
    $response->shouldContain( '{"error":"login.required"}' );
  }

  function it_allows_logging_in_using_a_user_token() {
    $user = Sentinel::register( $this->credentials, true );
    $user->addPermission( 'test.permission' );
    $user->save();
    $user = User::login( $this->credentials['email'], $this->credentials['password'] );

    $code  = $user->persistences()->first()->code;
    $token = new UserToken( $code, 'generic' );

    $environment = Environment::mock([
      'REQUEST_URI'  => '/fake.json'
    , 'QUERY_STRING' => "user_token=$token"
    ]);
    $this->req = Request::createFromEnvironment( $environment );

    $response = $this->__invoke( $this->req, $this->res, $this->next );
    $response->shouldBe( 'testful success' );
  }

  function it_requires_the_correct_permissions() {
    Sentinel::register( $this->credentials, true );
    Sentinel::authenticate( $this->credentials );

    $response = $this->__invoke( $this->req, $this->res, $this->next )->__toString();
    $response->shouldStartWith( 'HTTP/1.1 401 Unauthorized' );
    $response->shouldContain( '{"error":"permission.not_granted","details"' ); 
  }

  function it_allows_access_with_the_correct_permission() {
    $user = Sentinel::register( $this->credentials, true );
    $user->addPermission( 'test.permission' );
    $user->save();
    Sentinel::authenticate( $this->credentials );

    $response = $this->__invoke( $this->req, $this->res, $this->next );
    $response->shouldBe( 'testful success' );
  }

  function it_allows_access_with_at_least_one_correct_permission() {
    $this->beConstructedWith([ 'test.permission', 'lala.land' ]);

    $user = Sentinel::register( $this->credentials, true );
    $user->addPermission( 'test.permission' );
    $user->save();
    Sentinel::authenticate( $this->credentials );

    $response = $this->__invoke( $this->req, $this->res, $this->next );
    $response->shouldBe( 'testful success' );
  }

  function it_allows_access_if_no_permission_is_defined() {
    $this->beConstructedWith();

    $user = Sentinel::register( $this->credentials, true );
    $user->save();
    Sentinel::authenticate( $this->credentials );

    $response = $this->__invoke( $this->req, $this->res, $this->next );
    $response->shouldBe( 'testful success' );
  }

}
