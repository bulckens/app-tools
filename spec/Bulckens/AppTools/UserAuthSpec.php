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
  function it_requires_a_login() {
    $response = $this->__invoke( $this->req, $this->res, $this->next )->__toString();
    $response->shouldStartWith( 'HTTP/1.1 401 Unauthorized' );
    $response->shouldContain( '{"error":"login.required"}' );
  }

  function it_allows_logging_in_using_credentials_in_test_and_dev_environments() {
    $environment = Environment::mock([
      'REQUEST_URI'  => '/fake.json'
    , 'QUERY_STRING' => "credentials[email]={$this->credentials['email']}&credentials[password]={$this->credentials['password']}"
    ]);
    $this->req = Request::createFromEnvironment( $environment );

    $user = Sentinel::register( $this->credentials, true );
    $user->addPermission( 'test.permission' );
    $user->save();

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

}
