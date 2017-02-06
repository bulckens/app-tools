<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\Database;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DatabaseSpec extends ObjectBehavior {

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