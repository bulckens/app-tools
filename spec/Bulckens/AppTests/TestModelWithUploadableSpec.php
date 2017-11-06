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
    global $_SERVER;
    $_SERVER['HTTP_HOST'] = 'localhost';

    $app = new App( 'dev' );
    $app->run();
  }

  function letGo() {
    exec( sprintf( 'rm -rf %s', escapeshellarg( App::root( 'dev/upload/tmp' ) ) ) );
    exec( sprintf( 'rm -rf %s', escapeshellarg( App::root( 'dev/upload/test' ) ) ) );
    TestModelWithUploadable::truncate();
  }


  // Magic __set method
  function it_sets_the_database_attributes_from_a_given_associative_array() {
    $tmp_name = self::setupTmpFile();
    
    $this->image = [ 'name' => 'w.jpg', 'tmp_name' => $tmp_name, 'error' => UPLOAD_ERR_OK ];
    $this->image->name()->shouldBe( 'w.original.jpg' );
    $this->image->size()->shouldBe( filesize( $tmp_name ) );
    $this->image->mime()->shouldBe( 'image/jpeg' );
    $this->image->meta()->shouldBeArray();
    $this->image->meta()->shouldHaveKeyWithValue( 'width', 320 );
    $this->image->meta()->shouldHaveKeyWithValue( 'height', 320 );
  }

  function it_fails_if_the_given_associative_array_is_incomplete() {
    $this->shouldThrow( 'Bulckens\AppTools\Upload\TmpSourceIncompleteException' )->during__set( 'image', [
      'name' => 'w.jpg'
    ]);
  }

  function it_fails_if_the_given_associative_array_contains_a_reference_to_a_non_existant_file() {
    $this->shouldThrow( 'Bulckens\AppTools\Upload\TmpNameNotFoundException' )->during__set( 'image', [
      'name' => 'w.jpg'
    , 'tmp_name' => '/I/am/lost/or/so/I/think.jpg'
    , 'error' => UPLOAD_ERR_OK
    ]);
  }


  // Magic __get method
  function it_gets_an_uploaded_thumb() {
    $this->beConstructedWith([
      'thumb' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    , 'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->save();

    $this->thumb->shouldHaveType( 'Bulckens\AppTools\Upload\File' );
    $this->thumb->name()->shouldBe( 'w-original.jpg' );
    $this->thumb->path()->shouldEndWith( '/test_model_with_uploadables/1/thumbs/w-original.jpg' );
    $this->thumb->dir()->shouldEndWith( '/test_model_with_uploadables/1/thumbs' );
    $this->thumb->url()->shouldEndWith( 'https://zow-v5-test.s3-eu-central-1.amazonaws.com/test_model_with_uploadables/1/thumbs/w-original.jpg' );
    $this->thumb->mime()->shouldBe( 'image/jpeg' );
    $this->thumb->size()->shouldBe( 18338 );
    $this->thumb->weight()->shouldBe( '17.91 KB' );
    $this->thumb->tiny_name->shouldBe( 'w-tiny.jpg' );
    $this->thumb->tiny_file->shouldBe( '/test_model_with_uploadables/1/thumbs/w-tiny.jpg' );
    $this->thumb->tiny_url->shouldEndWith( 'https://zow-v5-test.s3-eu-central-1.amazonaws.com/test_model_with_uploadables/1/thumbs/w-tiny.jpg' );
    $this->thumb->small_name->shouldBe( 'w-small.jpg' );
    $this->thumb->small_file->shouldBe( '/test_model_with_uploadables/1/thumbs/w-small.jpg' );
    $this->thumb->small_url->shouldEndWith( 'https://zow-v5-test.s3-eu-central-1.amazonaws.com/test_model_with_uploadables/1/thumbs/w-small.jpg' );
    $this->thumb->medium_name->shouldBe( 'w-medium.jpg' );
    $this->thumb->medium_file->shouldBe( '/test_model_with_uploadables/1/thumbs/w-medium.jpg' );
    $this->thumb->medium_url->shouldEndWith( 'https://zow-v5-test.s3-eu-central-1.amazonaws.com/test_model_with_uploadables/1/thumbs/w-medium.jpg' );
    $this->thumb->large_name->shouldBe( 'w-large.jpg' );
    $this->thumb->large_file->shouldBe( '/test_model_with_uploadables/1/thumbs/w-large.jpg' );
    $this->thumb->large_url->shouldEndWith( 'https://zow-v5-test.s3-eu-central-1.amazonaws.com/test_model_with_uploadables/1/thumbs/w-large.jpg' );
    $this->thumb->original_name->shouldBe( $this->thumb->name() );
    $this->thumb->original_file->shouldBe( $this->thumb->file() );
    $this->thumb->original_url->shouldEndWith( $this->thumb->url() );
  }

  function it_gets_an_uploaded_image() {
    $this->beConstructedWith([
      'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->save();

    $this->image->shouldHaveType( 'Bulckens\AppTools\Upload\File' );
    $this->image->name()->shouldBe( 'w.original.jpg' );
    $this->image->path()->shouldEndWith( '/test_models/000/000/001/image/w.original.jpg' );
    $this->image->dir()->shouldEndWith( '/test_models/000/000/001/image' );
    $this->image->url()->shouldEndWith( 'https://localhost/test_models/000/000/001/image/w.original.jpg' );
    $this->image->mime()->shouldBe( 'image/jpeg' );
    $this->image->size()->shouldBe( 18338 );
    $this->image->weight()->shouldBe( '17.91 KB' );
  }

  function it_gets_an_uploaded_pdf() {
    $this->beConstructedWith([
      'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    , 'pdf' => [ 'name' => 'not-an-image.pdf', 'tmp_name' => self::setupTmpFile( 'not-an-image.pdf' ), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->save();

    $this->pdf->shouldHaveType( 'Bulckens\AppTools\Upload\File' );
    $this->pdf->name()->shouldBe( 'not-an-image.pdf' );
    $this->pdf->path()->shouldEndWith( '/test_model_with_uploadables/1/pdfs/not-an-image.pdf' );
    $this->pdf->dir()->shouldEndWith( '/test_model_with_uploadables/1/pdfs' );
    $this->pdf->url()->shouldEndWith( 'https://zow-v5-test-alternative.s3-eu-west-1.amazonaws.com/test_model_with_uploadables/1/pdfs/not-an-image.pdf' );
    $this->pdf->mime()->shouldBe( 'application/pdf' );
    $this->pdf->size()->shouldBe( 7512 );
    $this->pdf->weight()->shouldBe( '7.34 KB' );
    $this->pdf->original_name->shouldBe( null );
    $this->pdf->original_file->shouldBe( null );
    $this->pdf->original_url->shouldBe( null );
  }

  function it_returns_nothing_if_the_file_has_not_yet_been_uploaded() {
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


  // StoreUploads method
  function it_stores_the_uploads() {
    $this->beConstructedWith([
      'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]
    ]);
    $this->save()->shouldBe( true );
    $this->image->url()->shouldStartWith( 'https://localhost/test_models/000/000/001/image/w.original.jpg' );
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
