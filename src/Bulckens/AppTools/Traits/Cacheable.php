<?php

namespace Bulckens\AppTools\Traits;

use Exception;
use Illuminate\Support\Str;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Cache;

trait Cacheable {

  // Set/get cache
  public function cache( $value = null, $lifespan = null ) {
    // act as getter
    if ( is_null( $value ) ) {
      return $this->cacheModule()->get( $this->cacheKey() );
    }

    // act as setter
    $this->cacheModule()->set( $this->cacheKey(), $value, $lifespan );

    return $this;
  }


  // Get unique cache key
  public function cacheKey() {
    // get cache id
    $id = $this->cacheId();

    return $this->cacheScope() . ( $id ? ".$id" : '' );
  }


  // Get cache id (return nothing by default)
  public function cacheId() {}


  // Get the scope of the cache
  public function cacheScope() {
    return str_replace( '\_', '.', Str::snake( get_class() ) );
  }


  // Test existance in cache
  public function cached() {
    return $this->cacheModule()->has( $this->cacheKey() );
  }


  // Purge cache from cache store
  public function purgeCache() {
    $this->cacheModule()->delete( $this->cacheKey() );

    return $this;
  }


  // Get cache module
  protected function cacheModule() {
    if ( $cache = App::get()->cache() ) {
      return $cache;
    }

    throw new CacheableMissingCacheException( 'Missing cache module' );
  }

}


// Exceptions
class CacheableMissingCacheException extends Exception {}