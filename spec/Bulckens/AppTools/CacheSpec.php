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

  function it_returns_true_storing_a_given_value_was_successful() {
    $this->set( 'monkey', 'banana' )->shouldBe( true );
  }


  // Get method
  function it_returns_a_stored_value() {
    $this->set( 'monkey', 'banana' );
    $this->get( 'monkey' )->shouldBe( 'banana' );
  }

  function it_returns_the_default_value_when_get_fails() {
    $this->set( 'monkey.loves', 'banana' );
    $this->get( 'monkey.hates', 'apples' )->shouldBe( 'apples' );
  }

  // Delete method
  function it_deletes_a_given_key() {
    $this->set( 'monkey.loves', 'banana' );
    $this->delete( 'monkey.loves' );
    $this->get( 'monkey.loves' )->shouldBe( null );
  }

  function it_returns_true_after_deleting_a_given_key() {
    $this->delete( 'monkey.loves' )->shouldBe( true );
  }

  function it_returns_false_when_deletion_fails() {
    $this->delete( 'monkey:loves' )->shouldBe( false );
  }


  // Has method
  function it_checks_the_existence_of_a_given_key() {
    $this->has( 'monkey.hates' )->shouldBe( false );
    $this->set( 'monkey.hates', 'pickles' );
    $this->has( 'monkey.hates' )->shouldBe( true );
  }

  // Clear method
  function it_clears_all_the_existing_keys_and_values() {
    $this->set( 'monkey.loves', 'banana' );
    $this->set( 'monkey.hates', 'apple' );
    $this->clear()->shouldBe( true );
    $this->has( 'monkey.loves' )->shouldBe( false );
    $this->has( 'monkey.hates' )->shouldBe( false );
  }

  // GetMultiple method
  function it_gets_multiple_key_value_pairs_when_given_multiple_keys() {
    $this->set( 'monkey.loves', 'banana' );
    $actual = $this->getMultiple( ['monkey.loves', 'monkey.hates'], 'apples' );
    $actual->shouldHaveKeyWithValue( 'monkey.loves', 'banana' );
    $actual->shouldHaveKeyWithValue( 'monkey.hates', 'apples' );
  }

  function it_returns_empty_array_when_getting_multiple_keys_fails() {
    $this->getMultiple( ['a:b'] )->shouldBe( [] );
  }

  // SetMultiple method
  function it_can_set_multiple_key_value_pairs_at_once() {
    $this->setMultiple( [
      'monkey.loves' => 'banana'
    , 'monkey.hates' => 'apples'
    ] )->shouldBe( true );
    $this->get( 'monkey.loves' )->shouldBe( 'banana' );
    $this->get( 'monkey.hates' )->shouldBe( 'apples' );
  }

  function it_returns_false_when_setting_multiple_key_value_pairs_fails() {
    $this->setMultiple( [
      'monkey:loves' => 'banana'
    ] )->shouldBe( false );
  }


  // DeleteMultiple method
  function it_can_delete_multiple_keys_at_once() {
    $this->set( 'monkey.loves', 'banana' );
    $this->set( 'monkey.hates', 'apple' );
    $this->deleteMultiple( ['monkey.loves', 'monkey.hates'] )->shouldBe( true );
    $this->has( 'monkey.loves' )->shouldBe( false );
    $this->has( 'monkey.hates' )->shouldBe( false );
  }

  function it_returns_false_when_deleting_multiple_keys_fails() {
    $this->deleteMultiple( ['monkey:loves'] )->shouldBe( false );
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
