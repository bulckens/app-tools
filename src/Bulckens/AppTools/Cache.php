<?php

namespace Bulckens\AppTools;

// use Desarrolla2\Cache\Cache as Desarrolla2;
use Desarrolla2\Cache\File;
use Desarrolla2\Cache\Predis;
use Desarrolla2\Cache\NotCache;
use Desarrolla2\Cache\Memcached;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Traits\Configurable;
use Predis\Client;

class Cache {

  use Configurable;

  protected $cache;
  protected $lifespan;
  protected $prefix;
  protected $delimiter = '.';

  public function __construct() {
    // set default lifespan
    $this->lifespan = $this->config( 'lifespan', 60 * 60 * 24 * 30 );

    // set prefix
    $this->prefix = $this->config( 'prefix' );

    // initialize adaptor
    switch ( $this->config( 'engine' )) {
      case 'redis':
        $adapter = new Predis();
        $adapter->setOption( 'ttl', $this->lifespan );
      break;
      case 'file':
        $dir = App::root( $this->config( 'dir', 'tmp/cache' ));
        $adapter = new File( $dir );
        $adapter->setOption( 'ttl', $this->lifespan );
      break;
      case 'memcached':
        $adapter = new Memcached();
      break;
      default:
        $adapter = new NotCache();
      break;
    }

    // initialize cache
    $this->cache = $adapter;
  }


  // Create item
  public function set( $key, $value, $lifespan = null ) {
    // get lifespan
    $lifespan = is_numeric( $lifespan ) ? $lifespan : $this->lifespan;

    // store cache
    $this->cache->set( $this->prefix( $key ), $value, $lifespan );

    return $this;
  }


  // Read item
  public function get( $key ) {
    if ( $value = $this->cache->get( $this->prefix( $key ))) {
      return $value;
    }
  }


  // Delete item
  public function delete( $key ) {
    $this->cache->delete( $this->prefix( $key ));

    return $this;
  }


  // Test if item is present
  public function has( $key ) {
    return !! $this->cache->has( $this->prefix( $key ));
  }


  // Prefix given key
  protected function prefix( $key ) {
    // get key parts
    $parts = array_filter([ $this->prefix, $key ]);

    // interpolate environment
    $key = str_replace( '{{env}}', App::env(), implode( $this->delimiter, $parts ));

    return str_replace( '.', $this->delimiter, $key );
  }

}
