<?php

namespace Bulckens\AppTools;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Connectors\ConnectionFactory;
use Bulckens\Notifier\Notifier;
use Bulckens\AppTraits\Configurable;

class Database {

  use Configurable;

  // Initialize database connection
  public function __construct() {
    try {
      // bootstrap Eloquent ORM
      $container = new Container();
      $container->singleton(
        'Illuminate\Contracts\Debug\ExceptionHandler'
      , 'Bulckens\AppTools\DatabaseExceptionHandler'
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
      
      $resolver = new ConnectionResolver();
      $resolver->addConnection( 'default', $connection );
      $resolver->setDefaultConnection( 'default' );

      // initialize connection
      Eloquent::setConnectionResolver( $resolver );

    } catch ( Exception $e ) {
      
      if ( Notifier::active() )
        Notifier::error( $e );
      else
        die( "Caught exception: {$e->getMessage()}\n" );
    }
  }

}


class DatabaseExceptionHandler implements ExceptionHandler {
  public function report( Exception $e ) {
    Notifier::error( $e );
  }

  public function render( $request, Exception $e ) {
    Notifier::error( $e );
  }

  public function renderForConsole( $output, Exception $e ) {
    throw $e;
  }
}