<?php

namespace Bulckens\AppTools;

use Desarrolla2\Cache\Cache as Desarrolla2;
use Desarrolla2\Cache\Adapter\File;
use Desarrolla2\Cache\Adapter\Predis;
use Desarrolla2\Cache\Adapter\NotCache;
use Desarrolla2\Cache\Adapter\Memcache;
use Bulckens\AppTools\Traits\Configurable;

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
    switch ( $this->config( 'engine' ) ) {
      case 'redis':
        $adapter = new Predis();
        $adapter->setOption( 'ttl', $this->lifespan );
        $this->delimiter = ':';
      break;
      case 'file':
        $dir = App::root( $this->config( 'dir', 'tmp/cache' ) );
        $adapter = new File( $dir );
        $adapter->setOption( 'ttl', $this->lifespan );
      break;
      case 'memcache':
        $adapter = new Memcache();
      break;
      default:
        $adapter = new NotCache();
      break;
    }

    // initialize cache
    $this->cache = new Desarrolla2( $adapter );
  }


  // Create item
  public function set( $key, $value, $lifespan = null ) {
    $this->cache->set( $this->prefix( $key ), $value, $lifespan ?: $this->lifespan );

    return $this;
  }


  // Read item
  public function get( $key ) {
    if ( $value = $this->cache->get( $this->prefix( $key ) ) ) {
      return $value;
    }
  }


  // Delete item
  public function delete( $key ) {
    $this->cache->delete( $this->prefix( $key ) );

    return $this;
  }


  // Test if item is present
  public function has( $key ) {
    return !! $this->cache->has( $this->prefix( $key ) );
  }


  // Prefix given key
  protected function prefix( $key ) {
    $parts = array_filter([ $this->prefix, $key ]);
    return str_replace( '.', $this->delimiter, implode( $this->delimiter, $parts ) );
  }

}
