<?php

namespace Bulckens\AppTools;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Connectors\ConnectionFactory;

class Database {

  // Initialize database connection
  public function __construct( $config ) {
    try {
      // bootstrap Eloquent ORM
      $container = new Container();
      $container->singleton(
        'Illuminate\Contracts\Debug\ExceptionHandler'
      , 'Stencils\Base\DatabaseExceptionHandler'
      );

      $factory    = new ConnectionFactory( $container );
      $connection = $factory->make([
        'driver'    => 'mysql'
      , 'host'      => $config->get( 'host' )
      , 'database'  => $config->get( 'name' )
      , 'username'  => $config->get( 'user' )
      , 'password'  => $config->get( 'password' )
      , 'charset'   => $config->get( 'charset' )
      , 'collation' => $config->get( 'collate' )
      , 'prefix'    => ''
      ]);
      
      $resolver = new ConnectionResolver();
      $resolver->addConnection( 'default', $connection );
      $resolver->setDefaultConnection( 'default' );

      // initialize connection
      Model::setConnectionResolver( $resolver );

    } catch ( Exception $e ) {
      
      if ( Notifier::active() )
        Notifier::error( $e );

      die( "Caught exception: {$e->getMessage()}\n" );
    }
  }

}