<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\App;
use Bulckens\AppTools\Upload;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UploadSpec extends ObjectBehavior {
  
  function let() {
    global $_FILES;

    $app = new App( 'dev' );
    $app->run();

    $_FILES['image'] = [
      'name' => 'w.jpg'
    , 'tmp_name' => App::root( 'dev/upload/w.jpg' )
    , 'error' => UPLOAD_ERR_OK
    ];

    $this->beConstructedWith( 'image' );
  }

  function letGo() {
    
  }


  // Initialization
  function it_stores_the_key() {
    $this->key()->shouldBe( 'image' );
  }

  function it_stores_the_file_name() {
    $this->name()->shouldBe( 'w.jpg' );
  }

  function it_stores_the_tmp_name() {
    $this->tmpName()->shouldBe( App::root( 'dev/upload/w.jpg' ) );
  }

  function it_stores_the_error() {
    $this->error()->shouldBe( UPLOAD_ERR_OK );
  }

  function it_uses_the_default_storage_destination() {
    $this->storage()->shouldBe( 'default' );
  }

  function it_uses_the_available_storage_location_if_only_one_is_configured() {
    $this->configFile( 'upload_single.yml' );
    $this->storage()->shouldBe( 'default' );
  }

  function it_uses_the_given_storage_destination() {
    $this->beConstructedWith( 'image', 's3' );
    $this->storage()->shouldBe( 's3' );
  }

  function it_fails_if_the_given_key_is_not_present_in_the_files_array() {
    $this
      ->shouldThrow( 'Bulckens\AppTools\UploadKeyNotFoundException' )
      ->during__construct( 'void' );
  }

  function it_fails_if_the_tmp_name_does_not_exist() {
    $_FILES['image']['tmp_name'] = App::root( 'undefined/unrelated/w.jpg' );

    $this
      ->shouldThrow( 'Bulckens\AppTools\UploadTmpNameNotFoundException' )
      ->during__construct( 'image' );
  }

  
  // Config method
  function it_returns_the_config_instance_without_an_argument() {
    $this->config()->shouldHaveType( 'Bulckens\AppTools\Config' );
  }

  function it_returns_the_the_value_for_a_given_key() {
    $this->config( 'storage' )->shouldBeArray();
  }

  function it_returns_a_given_default_value_if_key_is_not_existing() {
    $this->config( 'pater', 'nostrum' )->shouldBe( 'nostrum' );
  }


  // File method
  function it_builds_config_file_name_from_class() {
    $this->configFile()->shouldBe( 'upload.yml' );
  }

  function it_defines_a_custom_config_file() {
    $this->configFile( 'upload_custom.yml' );
    $this->configFile()->shouldBe( 'upload_custom.yml' );
    $this->config( 'storage.default.dir' )->shouldBe( '/tmp/bulckens/app_tools/custom' );
  }

  function it_unsets_the_custom_config_file_with_null_given() {
    $this->configFile( 'upload_custom.yml' );
    $this->configFile()->shouldBe( 'upload_custom.yml' );
    $this->configFile( null );
    $this->configFile()->shouldBe( 'upload.yml' );
  }

  function it_returns_itself_after_defining_a_custom_config_file() {
    $this->configFile( 'user_custom.yml' )->shouldBe( $this );
  }


  // Key method
  function it_returns_the_key() {
    $this->key()->shouldBe( 'image' );
  }


  // Name method
  function it_returns_the_name() {
    $this->name()->shouldBe( 'w.jpg' );
  }

  function it_sets_the_name() {
    $this->name( 'wout' );
    $this->name()->shouldBe( 'wout.jpg' );
  }

  function it_returns_itself_after_setting_the_name() {
    $this->name( 'wout' )->shouldBe( $this );
  }


  // TmpName method
  function it_returns_the_tmp_name() {
    $this->tmpName()->shouldBe( App::root( 'dev/upload/w.jpg' ) );
  }


  // Error method
  function it_returns_the_error() {
    $this->error()->shouldBe( UPLOAD_ERR_OK );
  }


  // Size method
  function it_returns_the_size() {
    $this->size()->shouldBe( 118603 );
  }


  // Weight method (human readable size)
  function it_returns_the_weight() {
    $this->weight()->shouldBe( '115.82 KB' );
  }


  // Mime method
  function it_returns_the_mime_type() {
    $this->mime()->shouldBe( 'image/jpeg' );
  }


  // Storage method
  function it_returns_the_storage() {
    $this->beConstructedWith( 'image', 's3' );
    $this->storage()->shouldBe( 's3' );
  }

  function it_sets_the_storage() {
    $this->storage( 's3' );
    $this->storage()->shouldBe( 's3' );
  }

  function it_returns_itself_after_setting_the_storage() {
    $this->storage( 's3' )->shouldBe( $this );
  }

  function it_fails_when_the_given_storage_destination_is_not_configured() {
    $this->shouldThrow( 'Bulckens\AppTools\UploadStorageNotConfiguredException' )->duringStorage( 'falumba' );
  }


  // Exists method
  function it_tests_the_existance_of_the_given_key_in_the_files_array() {
    $this->exists()->shouldBe( true );
  }


  // Store method
  function it_stores_the_file_locally() {

  }

  function it_stores_the_file_locally_into_a_given_directory() {
    
  }

  function it_stores_the_file_on_s3() {

  }

  function it_stores_the_file_on_s3_into_a_given_directory() {
    
  }


  // Multiple static method
  function it_allows_multiple_files_to_be_uploaded() {

  }

}
