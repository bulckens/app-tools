<?php

namespace spec\Bulckens\AppTools;

use Exception;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Notifier;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NotifierSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev' );
    $app->run();
  }


  // Data method
  function it_sets_and_gets_arbitrary_data_for_notifications() {
    $this->data([ 'more' => 'info' ]);
    $this->data()->shouldHaveKeyWithValue( 'more', 'info' );
  }

  function it_returns_data_as_an_array() {
    $this->data()->shouldBeArray();
  }

  function it_sets_data_and_returns_itself() {
    $this->data([ 'more' => 'info' ])->shouldBe( $this );
  }
  

  
  // Config method
  function it_returns_the_config_instance_without_an_argument() {
    $this->config()->shouldHaveType( 'Bulckens\AppTools\Config' );
  }

  function it_returns_the_the_value_for_a_given_key() {
    $this->config( 'level' )->shouldBe( 'DEBUG' );
  }

  function it_returns_a_given_default_value_if_key_is_not_existing() {
    $this->config( 'pater', 'nostrum' )->shouldBe( 'nostrum' );
  }


  // File method
  function it_builds_config_file_name_from_class() {
    $this->configFile()->shouldBe( 'notifier.yml' );
  }

  function it_defines_a_custom_config_file() {
    $this->configFile( 'notifier_custom.yml' );
    $this->configFile()->shouldBe( 'notifier_custom.yml' );
    $this->config( 'level' )->shouldBe( 'custom' );
  }

  function it_unsets_the_custom_config_file_with_null_given() {
    $this->configFile( 'notifier_custom.yml' );
    $this->configFile()->shouldBe( 'notifier_custom.yml' );
    $this->configFile( null );
    $this->configFile()->shouldBe( 'notifier.yml' );
  }

  function it_returns_itself_after_defining_a_custom_config_file() {
    $this->configFile( 'notifier_custom.yml' )->shouldBe( $this );
  }


  // Log method
  function it_logs_a_message() {
    $this->shouldNotThrow( 'Bulckens\AppTools\NotifierMissingMethodException' )->duringLog( 'My message' );
  }

  function it_logs_a_message_with_given_type_info() {
    $this->shouldNotThrow( 'Bulckens\AppTools\NotifierMissingMethodException' )->duringLog( 'My message', 'info' );
  }

  function it_logs_a_message_with_given_type_high() {
    $this->shouldNotThrow( 'Bulckens\AppTools\NotifierMissingMethodException' )->duringLog( 'My message', 'high' );
  }

  function it_logs_a_message_with_given_type_warn() {
    $this->shouldNotThrow( 'Bulckens\AppTools\NotifierMissingMethodException' )->duringLog( 'My message', 'warn' );
  }

  function it_logs_a_message_with_given_type_error() {
    $this->shouldNotThrow( 'Bulckens\AppTools\NotifierMissingMethodException' )->duringLog( 'My message', 'error' );
  }

  function it_fails_with_a_missing_method_for_given_type() {
    $this->shouldThrow( 'Bulckens\AppTools\NotifierMissingMethodException' )->duringLog( 'My message', 'lalala' );
  }


  // Info method
  function it_logs_an_info_message() {
    $this->info( 'Hellø' )->shouldBe( null );
  }


  // High method
  function it_logs_a_high_message() {
    $this->high( 'Hi' )->shouldBe( null );
  }


  // Warn method
  function it_logs_a_warning_message() {
    $this->warn( 'Upgepasst' )->shouldBe( null );
  }


  // Error method
  function it_logs_an_error_message() {
    $this->error( 'Eurr' )->shouldBe( null );
  }

  function it_logs_an_error_message_with_custom_data_and_theme() {
    $this->configFile( 'notifier_full_theme.yml' );
    $this->data([ 'custom' => [ 'data' => 'in an array' ] ]);
    $this->error( 'Full theme' )->shouldBe( null );
  }

}










