<?php

namespace Bulckens\AppTools;

use Desarrolla2\Cache\File;
use Desarrolla2\Cache\Predis;
use Desarrolla2\Cache\NotCache;
use Desarrolla2\Cache\Memcached;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Traits\Configurable;
use Predis\Client;
use Psr\SimpleCache\CacheInterface;
use Desarrolla2\Cache\Exception\InvalidArgumentException;

class Cache implements CacheInterface {

  use Configurable;

  protected $cache;
  protected $ttl;
  protected $prefix;

  public function __construct() {
    // set default ttl
    $this->ttl = $this->config( 'ttl', 60 * 60 * 24 * 30 );

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
  public function set( $key, $value, $ttl = null ) {
    // get ttl
    $ttl = is_numeric( $ttl ) ? $ttl : $this->ttl;

    // store cache
    try {
      $status = $this->cache->set( $this->prefix( $key ), $value, $ttl );
    } catch( InvalidArgumentException $e ) {
      $status = false;
    }
    return $status;
  }


  // Read item
  public function get( $key, $default = null ) {
    try {
      $value = $this->cache->get( $this->prefix( $key ), $default );
    } catch( InvalidArgumentException $e ) {
      $value = $default;
    }
    return $value;
  }


  // Delete item
  public function delete( $key ) {
    try {
      $status = $this->cache->delete( $this->prefix( $key ) );
    } catch( InvalidArgumentException $e ) {
      $status = false;
    }

    return $status;
  }


  // Test if item is present
  public function has( $key ) {
    return !! $this->cache->has( $this->prefix( $key ));
  }


  public function clear() {
    return $this->cache->clear();
  }


  public function getMultiple( $keys, $default = null ) {
    try {
      $value = $this->cache->getMultiple( $keys, $default );
    } catch( InvalidArgumentException $e ) {
      $value = [];
    }
    return $value;
  }


  public function setMultiple( $keyvalues, $ttl = null ) {
    $ttl = is_numeric( $ttl ) ? $ttl : $this->ttl;
    try {
      $status = $this->cache->setMultiple( $keyvalues, $ttl );
    } catch( InvalidArgumentException $e ) {
      $status = false;
    }
    return $status;
  }


  public function deleteMultiple( $keys ) {
    try {
      $status = $this->cache->deleteMultiple( $keys );
    } catch( InvalidArgumentException $e ) {
      $status = false;
    }
    return $status;
  }

  // Prefix given key
  protected function prefix( $key ) {
    // get key parts
    $parts = array_filter([ $this->prefix, $key ]);

    // interpolate environment
    return str_replace( '{{env}}', App::env(), implode( '.', $parts ) );
  }

}
