<?php

namespace spec\Bulckens\AppTests;

use Exception;
use Bulckens\Helpers\StringHelper;
use Bulckens\AppTools\App;
use Bulckens\AppTests\TestModelWithUploadable;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestModelWithUploadableSpec extends ObjectBehavior {

  // TIP: you'll need this: getWrappedObject

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
    
    $this->image = [ 'name' => 'w.jpg', 'tmp_name' => $tmp_name, 'error' => UPLOAD_ERR_OK ];
    $this->image_name->shouldBe( 'w.original.jpg' );
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


  // Magic __get method
  function it_gets_an_upload_object() {
    $tmp_name = self::setupTmpFile();

    $this->image = [ 'name' => 'w.jpg', 'tmp_name' => $tmp_name, 'error' => UPLOAD_ERR_OK ];
    $this->image->shouldHaveType( 'Bulckens\AppTools\Upload' );
    $this->image->name()->shouldBe( 'w.original.jpg' );
    $this->image->tmpName()->shouldBe( $tmp_name );
    $this->image->error()->shouldBe( UPLOAD_ERR_OK );
  }

  function it_returns_nothing_if_the_attribute_has_not_been_set() {
    $this->image->shouldBe( null );
  }


  // Validations (thumb)
  function it_validates_the_correct_weight_of_the_thumb() {
    $this->beConstructedWith([
      'thumb' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    , 'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->isValid()->shouldBe( true );
  }

  function it_is_invalid_if_the_thumb_is_too_light() {
    $this->beConstructedWith([
      'thumb' => [ 'name' => 'micro.jpeg', 'tmp_name' => self::setupTmpFile( 'micro.jpeg' ), 'error' => UPLOAD_ERR_OK ]
    , 'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->isValid()->shouldBe( false );
    $this->errorsOn( 'thumb' )->shouldHaveKeyWithValue( 'weight_min', 'should be at least 10 KB' );
  }

  function it_is_invalid_if_the_thumb_is_too_heavy() {
    $this->beConstructedWith([
      'thumb' => [ 'name' => 'wood.jpg', 'tmp_name' => self::setupTmpFile( 'wood.jpg' ), 'error' => UPLOAD_ERR_OK ]
    , 'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->isValid()->shouldBe( false );
    $this->errorsOn( 'thumb' )->shouldHaveKeyWithValue( 'weight_max', 'should be no bigger than 512 KB' );
  }

  function it_validates_the_correct_dimensions_of_the_thumb() {
    $this->beConstructedWith([
      'thumb' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    , 'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->isValid()->shouldBe( true );
  }

  function it_is_invalid_if_the_thumb_is_too_small() {
    $this->beConstructedWith([
      'thumb' => [ 'name' => 'micro.jpeg', 'tmp_name' => self::setupTmpFile( 'micro.jpeg' ), 'error' => UPLOAD_ERR_OK ]
    , 'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->isValid()->shouldBe( false );
    $this->errorsOn( 'thumb' )->shouldHaveKeyWithValue( 'width_min', 'should be at least 128px wide' );
    $this->errorsOn( 'thumb' )->shouldHaveKeyWithValue( 'height_min', 'should be at least 128px high' );
  }

  function it_is_invalid_if_the_thumb_is_too_large() {
    $this->beConstructedWith([
      'thumb' => [ 'name' => 'wood.jpg', 'tmp_name' => self::setupTmpFile( 'wood.jpg' ), 'error' => UPLOAD_ERR_OK ]
    , 'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->isValid()->shouldBe( false );
    $this->errorsOn( 'thumb' )->shouldHaveKeyWithValue( 'width_max', 'should not be wider than 1024px' );
    $this->errorsOn( 'thumb' )->shouldNotHaveKey( 'height_max' );
  }

  function it_validates_the_correct_mime_type_of_the_thumb() {
    $this->beConstructedWith([
      'thumb' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    , 'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->isValid()->shouldBe( true );
  }

  function it_is_invalid_if_anything_other_than_an_image_file_is_given() {
    $this->beConstructedWith([
      'thumb' => [ 'name' => 'not-an-image.txt', 'tmp_name' => self::setupTmpFile( 'not-an-image.txt' ), 'error' => UPLOAD_ERR_OK ]
    , 'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->isValid()->shouldBe( false );
    $this->errorsOn( 'thumb' )->shouldHaveKeyWithValue( 'mime', 'is not an accepted file type' );
  }

  function it_does_not_validate_the_requirement_of_the_thumb() {
    $this->beConstructedWith([
      'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->isValid()->shouldBe( true );
  }


  // Validations (image)
  function it_validates_the_correct_weight_of_the_image() {
    $this->beConstructedWith([
      'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->isValid()->shouldBe( true );
  }

  function it_is_invalid_if_the_image_is_too_heavy() {
    $this->beConstructedWith([
      'image' => [ 'name' => 'wood.jpg', 'tmp_name' => self::setupTmpFile( 'wood.jpg' ), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->isValid()->shouldBe( false );
    $this->errorsOn( 'image' )->shouldHaveKeyWithValue( 'weight_max', 'should be no bigger than 512 KB' );
  }

  function it_is_invalid_if_the_image_is_not_given() {
    $this->isValid()->shouldBe( false );
    $this->errorsOn( 'image' )->shouldHaveKeyWithValue( 'required', 'is required' );
  }


  // Validations (pdf)
  function it_validates_the_correct_mime_type_of_the_pdf() {
    $this->beConstructedWith([
      'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    , 'pdf' => [ 'name' => 'not-an-image.pdf', 'tmp_name' => self::setupTmpFile( 'not-an-image.pdf' ), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->isValid()->shouldBe( true );
  }

  function it_is_invalid_if_anything_other_than_a_pdf_file_is_given() {
    $this->beConstructedWith([
      'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    , 'pdf' => [ 'name' => 'not-an-image.txt', 'tmp_name' => self::setupTmpFile( 'not-an-image.txt' ), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->isValid()->shouldBe( false );
    $this->errorsOn( 'pdf' )->shouldHaveKeyWithValue( 'mime', 'is not an accepted file type' );
  }


  // Fill method
  function it_returns_itself_after_filling() {
    $this->beConstructedWith();
    $this->fill([
      'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    , 'pdf' => [ 'name' => 'not-an-image.txt', 'tmp_name' => self::setupTmpFile( 'not-an-image.txt' ), 'error' => UPLOAD_ERR_OK ]
    ])->shouldBe( $this );
  }


  // Helpers
  protected static function setupTmpFile( $file = 'w.jpg' ) {
    if ( ! file_exists( $tmp = App::root( 'dev/upload/tmp' ) ) ) {
      mkdir( $tmp, 0777, true );
    }
    if ( ! file_exists( $dir = App::root( 'dev/upload/test' ) ) ) {
      mkdir( $dir, 0777, true );
    }

    $source = App::root( "dev/upload/$file" );
    $random = StringHelper::generate( 32 );
    $tmp_name = "$tmp/$random";

    copy( $source, $tmp_name );

    return $tmp_name;
  }

}
