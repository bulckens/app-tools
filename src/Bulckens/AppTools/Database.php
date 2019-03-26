<?php

namespace Bulckens\AppTools;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Connectors\ConnectionFactory;
use Bulckens\AppTools\Traits\Configurable;

class Database {

  use Configurable;

  // Initialize database connection
  public function __construct() {
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
  }

}


class DatabaseExceptionHandler implements ExceptionHandler {

  // Report
  public function report( Exception $e ) {
    App::get()->notifier()->error( $e );
  }

  // Render
  public function render( $request, Exception $e ) {
    App::get()->notifier()->error( $e );
  }

  // Test reportablility
  public function shouldReport( Exception $e ) {
    return true;
  }

  // Console render 
  public function renderForConsole( $output, Exception $e ) {
    throw $e;
  }

}