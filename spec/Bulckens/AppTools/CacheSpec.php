<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\Cache;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Bulckens\AppTools\App;

class CacheSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev' );
    $app->run();
  }

  function letGo() {
    $this->delete( 'monkey' );
    $this->delete( 'monkey.loves' );
    $this->delete( 'monkey.hates' );
  }


  // Set method
  function it_stores_a_given_value() {
    $this->get( 'monkey' )->shouldBe( null );
    $this->set( 'monkey', 'banana' );
    $this->get( 'monkey' )->shouldBe( 'banana' );
  }

  function it_returns_itself_after_storing_a_given_value() {
    $this->set( 'monkey', 'banana' )->shouldBe( $this );
  }


  // Get method
  function it_returns_a_stored_value() {
    $this->set( 'monkey', 'banana' );
    $this->get( 'monkey' )->shouldBe( 'banana' );
  }


  // Delete method
  function it_deletes_a_given_key() {
    $this->set( 'monkey.loves', 'banana' );
    $this->delete( 'monkey.loves' );
    $this->get( 'monkey.loves' )->shouldBe( null );
  }

  function it_returns_itself_after_deleting_a_given_key() {
    $this->delete( 'monkey.loves' )->shouldBe( $this );
  }


  // Has method
  function it_checks_the_existence_of_a_given_key() {
    $this->has( 'monkey.hates' )->shouldBe( false );
    $this->set( 'monkey.hates', 'pickles' );
    $this->has( 'monkey.hates' )->shouldBe( true );
  }

    
  // Config method
  function it_returns_the_config_instance_without_an_argument() {
    $this->config()->shouldHaveType( 'Bulckens\AppTools\Config' );
  }

  function it_returns_the_the_value_for_a_given_key() {
    $this->config( 'engine' )->shouldBe( 'redis' );
  }

  function it_returns_a_given_default_value_if_key_is_not_existing() {
    $this->config( 'pater', 'nostrum' )->shouldBe( 'nostrum' );
  }


  // File method
  function it_builds_config_file_name_from_class() {
    $this->configFile()->shouldBe( 'cache.yml' );
  }

  function it_defines_a_custom_config_file() {
    $this->configFile( 'cache_custom.yml' );
    $this->configFile()->shouldBe( 'cache_custom.yml' );
    $this->config( 'engine' )->shouldBe( 'custom' );
  }

  function it_unsets_the_custom_config_file_with_null_given() {
    $this->configFile( 'cache_custom.yml' );
    $this->configFile()->shouldBe( 'cache_custom.yml' );
    $this->configFile( null );
    $this->configFile()->shouldBe( 'cache.yml' );
  }

  function it_returns_itself_after_defining_a_custom_config_file() {
    $this->configFile( 'cache_custom.yml' )->shouldBe( $this );
  }

}
