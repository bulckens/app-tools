<?php

namespace Bulckens\AppTools;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Connectors\ConnectionFactory;
use Bulckens\AppTools\Traits\Configurable;

class Database {

  use Configurable;

  protected static $resolvers  = [];
  protected static $connection = 'app_tools_database';

  // Initialize database connection
  public function __construct() {
    Model::setConnectionResolver( $this->resolver() );
  }


  // build or get a resolver
  public function resolver( $name = null ) {
    // ensure connection name
    if ( is_null( $name) )
      $name = self::$connection ?: 'default';

    // build resolver if none is present
    if ( ! isset( self::$resolvers[$name] ) ) {
      // bootstrap Eloquent ORM
      $container = new Container();
      $container->singleton(
        'Illuminate\Contracts\Debug\ExceptionHandler'
      , 'Bulckens\AppTools\Notifier\DatabaseExceptionHandler'
      );

      $factory    = new ConnectionFactory( $container );
      $connection = $factory->make([
        'driver'    => 'mysql'
      , 'host'      => $this->config( 'host' )
      , 'database'  => $this->config( 'name' )
      , 'username'  => $this->config( 'user' )
      , 'password'  => $this->config( 'password' )
      , 'charset'   => $this->config( 'charset' )
      , 'collation' => $this->config( 'collate' )
      , 'prefix'    => ''
      ]);
      
      self::$resolvers[$name] = new ConnectionResolver();
      self::$resolvers[$name]->addConnection( $name, $connection );
    }
    
    return self::$resolvers[$name];
  }

}


class DatabaseExceptionHandler implements ExceptionHandler {

  // Report
  public function report( Exception $e ) {
    $this->notifier()->error( $e );
  }

  // Render
  public function render( $request, Exception $e ) {
    $this->notifier()->error( $e );
  }

  // Console render 
  public function renderForConsole( $output, Exception $e ) {
    throw $e;
  }

  // Notifier getter
  public function notifier() {
    return App::get()->notifier();
  }
}