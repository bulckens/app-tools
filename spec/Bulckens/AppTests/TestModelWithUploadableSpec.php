<?php

namespace spec\Bulckens\AppTests;

use Exception;
use Bulckens\Helpers\StringHelper;
use Bulckens\AppTools\App;
use Bulckens\AppTests\TestModelWithUploadable;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestModelWithUploadableSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev' );
    $app->run();
  }

  function letGo() {
    exec( sprintf( 'rm -rf %s', escapeshellarg( App::root( 'dev/upload/tmp' ) ) ) );
    exec( sprintf( 'rm -rf %s', escapeshellarg( App::root( 'dev/upload/test' ) ) ) );
  }

  // Magic __set method
  function it_sets_the_database_attributes_from_a_given_associative_array() {
    $tmp_name = self::setupTmpFile();

    $this->image = [
      'name' => 'w.jpg'
    , 'tmp_name' => $tmp_name
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->image_name->shouldBe( 'w.jpg' );
    $this->image_size->shouldBe( filesize( $tmp_name ) );
    $this->image_mime->shouldBe( 'image/jpeg' );
  }

  function it_fails_if_the_given_associative_array_is_incomplete() {
    $this->shouldThrow( 'Bulckens\AppTools\UploadSourceIncompleteException' )->during__set( 'image', [
      'name' => 'w.jpg'
    ]);
  }

  function it_fails_if_the_given_associative_array_contains_a_reference_to_a_non_existant_file() {
    $this->shouldThrow( 'Bulckens\AppTools\UploadTmpNameNotFoundException' )->during__set( 'image', [
      'name' => 'w.jpg'
    , 'tmp_name' => '/I/am/lost/or/so/I/think.jpg'
    , 'error' => UPLOAD_ERR_OK
    ]);
  }


  // Helpers
  protected static function setupTmpFile() {
    if ( ! file_exists( $tmp = App::root( 'dev/upload/tmp' ) ) ) {
      mkdir( $tmp, 0777, true );
    }
    if ( ! file_exists( $dir = App::root( 'dev/upload/test' ) ) ) {
      mkdir( $dir, 0777, true );
    }

    $source = App::root( 'dev/upload/w.jpg' );
    $random = StringHelper::generate( 32 );
    $tmp_name = "$tmp/$random";

    copy( $source, $tmp_name );

    return $tmp_name;
  }

}
