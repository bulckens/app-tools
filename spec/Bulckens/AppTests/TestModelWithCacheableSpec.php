<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTools\App;
use Bulckens\AppTests\TestModelWithCacheable;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestModelWithCacheableSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev' );
    $app->run();
    $this->purgeCache();
  }


  // Cache method
  function it_stores_a_given_value() {
    $this->cache()->shouldBe( null );
    $this->cache( 'falumba' );
    $this->cache()->shouldBe( 'falumba' );
  }

  function it_stores_a_given_value_with_a_lifespan() {
    $this->cache()->shouldBe( null );
    $this->cache( 'falumba', 1 );
    sleep( 2 );
    $this->cache()->shouldBe( null );
  }

  function it_returns_itself_after_storing_a_given_value() {
    $this->cache( 'falumba' )->shouldBe( $this );
  }

  function it_returns_a_stored_value() {
    $this->cache( 'mafasta' );
    $this->cache()->shouldBe( 'mafasta' );
  }


  // CacheKey method
  function it_generates_a_cache_key_including_the_class_name_and_id() {
    $this->id = 123;
    $this->cacheKey()->shouldBe( 'bulckens.app_tests.test_model_with_cacheable.123' );
  }


  // CacheId method
  function it_returns_the_id_as_a_cache_key() {
    $this->id = 312;
    $this->cacheId()->shouldBe( 312 );
  }


  // CacheScope method
  function it_returns_a_cache_scope_based_on_the_current_class_by_default() {
    $this->cacheScope()->shouldStartWith( 'bulckens.app_tests.test_model_with_cacheable' );
  }


  // Cached method
  function it_tests_positive_when_cached_already() {
    $this->cache( 'lantra' );
    $this->cached()->shouldBe( true );
  }

  function it_tests_negative_when_not_cached() {
    $this->cached()->shouldBe( false ); 
  }


  // PurgeCache method
  function it_pureges_the_stored_cache() {
    $this->cache( 'fickin' );
    $this->purgeCache();
    $this->cache()->shouldBe( null );
  }

  function it_returns_itself_after_purging_the_stored_cache() {
    $this->purgeCache()->shouldBe( $this );
  }

}
