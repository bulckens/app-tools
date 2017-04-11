<?php

namespace spec\Bulckens\AppTools;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Bulckens\AppTools\Database;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Bulckens\AppTools\App;

class DatabaseSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev' );
    $app->run();
  }


  // Initialize
  function it_sets_the_main_connection_resolver_on_the_main_model() {
    $this->resolver()->shouldBe( Eloquent::getConnectionResolver() );
  }


  // Resolver method
  function it_builds_a_connection_resolver() {
    $this->resolver()->shouldHaveType( 'Illuminate\Database\ConnectionResolver' );
  }

  function it_builds_a_named_connection_resolver() {
    $this->resolver( 'febreze' )->shouldNotBe( $this->resolver() );
  }

  function it_builds_a_resolver_only_once() {
    $resolver = $this->resolver( 'frenzy' );
    $this->resolver( 'frenzy' )->shouldBe( $resolver );
  }


  // Config method
  function it_returns_the_config_instance_without_an_argument() {
    $this->config()->shouldHaveType( 'Bulckens\AppTools\Config' );
  }

  function it_returns_the_the_value_for_a_given_key() {
    $this->config( 'host' )->shouldBe( 'test' );
  }

  function it_returns_a_given_default_value_if_key_is_not_existing() {
    $this->config( 'pater', 'nostrum' )->shouldBe( 'nostrum' );
  }


  // File method
  function it_builds_config_file_name_from_class() {
    $this->file()->shouldBe( 'database.yml' );
  }

  function it_defines_a_custom_config_file() {
    $this->file( 'database_custom.yml' );
    $this->file()->shouldBe( 'database_custom.yml' );
    $this->config( 'host' )->shouldBe( 'custom' );
  }

  function it_unsets_the_custom_config_file_with_null_given() {
    $this->file( 'database_custom.yml' );
    $this->file()->shouldBe( 'database_custom.yml' );
    $this->file( null );
    $this->file()->shouldBe( 'database.yml' );
  }

  function it_returns_itself_after_defining_a_custom_config_file() {
    $this->file( 'database_custom.yml' )->shouldBe( $this );
  }

}