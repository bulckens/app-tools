<?php

namespace spec\Bulckens\AppTools\Upload;

use Exception;
use Bulckens\Helpers\StringHelper;
use Bulckens\AppTests\TestModel;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Upload\File;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileSpec extends ObjectBehavior {

  protected static $model;
  protected static $test_file;
  
  function let() {
    global $_SERVER;

    $_SERVER['HTTP_HOST'] = 'localhost';

    $app = new App( 'dev' );
    $app->run();

    $this->beConstructedWith([
      'name' => 'w.jpg'
    , 'mime' => 'image/jpeg'
    , 'size' => 18338
    , 'meta' => '{"width":320,"height":320}'
    ]);

    self::$test_file = [
      'name' => 'w.js'
    , 'mime' => 'image/jpeg'
    , 'size' => 18338
    , 'meta' => '{"width":320,"height":320}'
    , 'interpolations' => [
        'object' => TestModel::create()
      , 'name' => 'image'
      ]
    ];
  }

  function letGo() {
    exec( sprintf( 'rm -rf %s', escapeshellarg( App::root( 'dev/upload/tmp' ) ) ) );
    exec( sprintf( 'rm -rf %s', escapeshellarg( App::root( 'dev/upload/test' ) ) ) );
    TestModel::truncate();
  }


  // Initialization
  function it_stores_the_upload() {
    $this->source()->shouldBeArray();
  }

  function it_stores_the_file_name() {
    $this->name()->shouldBe( 'w.jpg' );
  }

  function it_can_be_constructed_without_the_meta_key() {
    $this
      ->shouldNotThrow( 'Bulckens\AppTools\Upload\FileSourceIncompleteException' )
      ->during__construct([ 'name' => 'not-an-image.txt', 'mime' => 'text/plain', 'size' => 533 ]);
  }

  function it_fails_if_the_given_source_is_not_an_array() {
    $this
      ->shouldThrow( 'Bulckens\AppTools\Upload\FileSourceNotAcceptableException' )
      ->during__construct( 'image' );
  }

  function it_fails_if_the_given_source_is_incomplete() {
    $this
      ->shouldThrow( 'Bulckens\AppTools\Upload\FileSourceIncompleteException' )
      ->during__construct([ 'name' => 'w.jpg' ]);
  }


  // Basename
  function it_returns_the_name_without_extension() {
    $this->basename()->shouldBe( 'w' );
  }


  // Name method
  function it_returns_the_name() {
    $this->name()->shouldBe( 'w.jpg' );
  }

  function it_returns_the_file_name_formatted_as_configured_locally() {
    $this->beConstructedWith( self::$test_file, [
      'config' => 'upload_formatted.yml'
    , 'name' => '{{ style }}.{{ name }}'
    , 'styles' => [ 'medium' => '1280x1280#' ]
    ]);
    $this->name( 'medium' )->shouldEndWith( 'medium.w.jpg' );
  }

  function it_returns_the_file_name_formatted_as_configured_locally_with_width_and_height() {
    $this->beConstructedWith( self::$test_file, [
      'config' => 'upload_formatted.yml'
    , 'name' => '{{ style }}-{{ width }}x{{ height }}.{{ name }}'
    , 'styles' => [ 'medium' => '1280x1280#' ]
    ]);
    $this->name( 'medium' )->shouldEndWith( 'medium-320x320.w.jpg' );
  }

  function it_returns_the_file_name_formatted_as_configured_locally_with_a_default_style_label() {
    $this->beConstructedWith( self::$test_file, [
      'config' => 'upload_formatted.yml'
    , 'name' => 'image-{{ style }}.{{ name }}'
    ]);
    $this->name()->shouldEndWith( 'image-original.w.jpg' );
  }

  function it_uses_the_default_name_format_if_styles_are_defined_but_no_format_is_given() {
    $this->beConstructedWith( self::$test_file, [
      'styles' => [
        'mini' => '10x10!'
      , 'original' => '1024x1024>'
      ]
    ]);
    $this->name( 'mini' )->shouldEndWith( 'w-mini.jpg' );
    $this->name( 'original' )->shouldEndWith( 'w-original.jpg' );
  }

  function it_uses_the_original_style_when_no_style_is_given_but_styles_are_defined() {
    $this->beConstructedWith( self::$test_file, [
      'styles' => [
        'mini' => '10x10!'
      , 'original' => '1024x1024>'
      ]
    ]);
    $this->name()->shouldEndWith( 'w-original.jpg' );
  }


  // Ext method
  function it_returns_the_file_extension() {
    $this->ext()->shouldBe( 'jpg' );
  }


  // Dimensions method
  function it_returns_the_dimensions_of_an_image() {
    $this->beConstructedWith([
      'name' => 'm.jpg'
    , 'mime' => 'image/jpeg'
    , 'size' => 103648
    , 'meta' => '{"width":746,"height":1080}'
    ]);

    $dimensions = $this->dimensions();
    $dimensions->shouldBeArray();
    $dimensions[0]->shouldBe( 746 );
    $dimensions[1]->shouldBe( 1080 );
    $dimensions['width']->shouldBe( 746 );
    $dimensions['height']->shouldBe( 1080 );
  }


  // Width method
  function it_returns_the_width_of_an_image() {
    $this->beConstructedWith([
      'name' => 'm.jpg'
    , 'mime' => 'image/jpeg'
    , 'size' => 103648
    , 'meta' => '{"width":746,"height":1080}'
    ]);
    $this->width()->shouldBe( 746 );
  }


  // Height method
  function it_returns_the_height_of_an_image() {
    $this->beConstructedWith([
      'name' => 'm.jpg'
    , 'mime' => 'image/jpeg'
    , 'size' => 103648
    , 'meta' => '{"width":746,"height":1080}'
    ]);
    $this->height()->shouldBe( 1080 );
  }


  // IsImage method
  function it_tests_positive_if_a_file_is_an_image() {
    $this->shouldBeImage();
  }

  function it_tests_negative_if_a_file_is_not_an_image() {
    $this->beConstructedWith([
      'name' => 'not-an-image.txt'
    , 'mime' => 'text/plain'
    , 'size' => 533
    , 'meta' => '[]'
    ]);
    $this->shouldNotBeImage();
  }


  // Mime method
  function it_returns_the_mime_type() {
    $this->mime()->shouldBe( 'image/jpeg' );
  }


  // Meta method
  function it_parses_json_meta_data() {
    $meta = $this->meta();
    $meta->shouldBeArray();
    $meta->shouldHaveKeyWithValue( 'width', 320 );
    $meta->shouldHaveKeyWithValue( 'height', 320 );
  }

  function it_returns_a_given_meta_key() {
    $this->meta( 'width' )->shouldBe( 320 );
    $this->meta( 'height' )->shouldBe( 320 );
  }

  function it_returns_nothing_if_a_given_key_could_not_be_found() {
    $this->meta( 'mastaba' )->shouldBeNull();
  }

  function it_returns_an_empty_array_if_no_meta_data_was_given() {
    $this->beConstructedWith([
      'name' => 'not-an-image.txt'
    , 'mime' => 'text/plain'
    , 'size' => 533
    , 'meta' => '[]'
    ]);
    $this->meta()->shouldBeArray();
    $this->meta()->shouldBeEmpty();
  }

  function it_returns_an_empty_array_if_no_meta_key_was_given() {
    $this->beConstructedWith([
      'name' => 'not-an-image.txt'
    , 'mime' => 'text/plain'
    , 'size' => 533
    ]);
    $this->meta()->shouldBeArray();
    $this->meta()->shouldBeEmpty();
  }


  // Size method
  function it_returns_the_size() {
    $this->size()->shouldBe( 18338 );
  }


  // Weight method (human readable size)
  function it_returns_the_weight() {
    $this->weight()->shouldBe( '17.91 KB' );
  }


  // File method
  function it_returns_the_full_destination_file_path() {
    $this
      ->file()
      ->shouldStartWith( App::root( 'dev/upload/test/w.jpg' ) );
  }

  function it_returns_the_full_destination_file_path_with_a_style() {
    $this->beConstructedWith([
      'name' => 'w.js'
    , 'mime' => 'image/jpeg'
    , 'size' => 18338
    , 'meta' => '{"width":320,"height":320}'
    ],
    [ 'styles' => [
        'micro' => '2x2#'
      , 'original' => '1280x1280>'
      ]
    ]);

    $this
      ->file( 'micro' )
      ->shouldStartWith( App::root( 'dev/upload/test/w-micro.jpg' ) );
  }

  function it_returns_the_absolute_path_for_filesystem_storage() {
    $this->dir( 'will/power' )->file()->shouldBe( App::root( 'will/power/w.jpg' ) );
  }

  function it_returns_the_absolute_path_for_filesystem_streamed_storage() {
    $this->beConstructedWith([
      'name' => 'w.js'
    , 'mime' => 'image/jpeg'
    , 'size' => 18338
    , 'meta' => '{"width":320,"height":320}'
    ],
    [ 'storage' => 'external'
    ]);
    $this->dir( 'will/power' )->file()->shouldBe( "http://server.local/safe/to/store/will/power/w.jpg" );
  }

  function it_returns_the_relative_path_for_s3_storage() {
    $this->beConstructedWith([
      'name' => 'w.js'
    , 'mime' => 'image/jpeg'
    , 'size' => 18338
    , 'meta' => '{"width":320,"height":320}'
    ],
    [ 'storage' => 's3'
    ]);
    $this->dir( 'will/power' )->file()->shouldBe( '/will/power/w.jpg' );
  }


  // Dir method
  function it_returns_the_dir() {
    $this->dir( 'halla/23/malla' );
    $this->dir()->shouldBe( '/halla/23/malla' );
  }

  function it_sets_the_dir_of_the_file() {
    $this->dir()->shouldBe( '/dev/upload/test' );
    $this->dir( 'some/sub/directory' );
    $this->dir()->shouldBe( '/some/sub/directory' );
  }

  function it_strips_any_trailing_slashes() {
    $this->dir( '/some/sub/directory/' );
    $this->dir()->shouldBe( '/some/sub/directory' );
  }

  function it_returns_itself_after_setting_the_path_of_the_file() {
    $this->dir( 'some/sub/directory' )->shouldBe( $this );
  }


  // Path method
  function it_returns_the_public_path_to_the_file() {
    $this->beConstructedWith( self::$test_file, [ 'storage' => 's3' ]);
    $this->path()->shouldBe( '/test_models/1/images/w.jpg' );
  }

  function it_returns_the_public_path_to_the_file_for_a_given_style() {
    $this->beConstructedWith( self::$test_file, [ 'styles' => [ 'mini' => '256x256#' ] ]);
    $this->path( 'mini' )->shouldEndWith( '/dev/upload/test/w-mini.jpg' );
  }

  function it_returns_the_public_s3_path_to_the_file() {
    $this->beConstructedWith( self::$test_file, [ 'storage' => 's3' ]);
    $this->path()->shouldBe( '/test_models/1/images/w.jpg' );
  }

  function it_returns_the_public_s3_path_to_the_file_for_a_given_style() {
    $this->beConstructedWith( self::$test_file,
    [ 'storage' => 's3'
    , 'styles' => [
        'tiny' => '128x128#'
      ]
    ]);
    $this->path( 'tiny' )->shouldBe( '/test_models/1/images/w-tiny.jpg' );
  }


  // Url method
  function it_returns_the_url_of_the_file() {
    $this->url()->shouldBe( 'https://localhost/dev/upload/test/w.jpg' );
  }

  function it_returns_the_url_of_the_file_in_a_given_style() {
    $this->beConstructedWith( self::$test_file,
    [ 'styles' => [
        'frop' => '21x21#'
      ]
    ]);
    $this->url( 'frop' )->shouldBe( 'https://localhost/dev/upload/test/w-frop.jpg' );
  }

  function it_returns_the_url_of_the_file_in_the_original_style_by_default() {
    $this->beConstructedWith(
      self::$test_file
    , [ 'styles' => [
          'frop' => '21x21#'
        , 'original' => '300x300'
        ]
      ]
    );
    $this->url()->shouldEndWith( 'https://localhost/dev/upload/test/w-original.jpg' );
  }

  function it_returns_the_url_of_the_file_with_a_cofigured_host() {
    $this->beConstructedWith( self::$test_file, [ 'config' => 'upload_with_host.yml' ]);

    $this->url()->shouldBe( 'https://superserver.com/custom/upload/test/w.jpg' );
  }

  function it_returns_the_url_of_the_file_with_a_cofigured_host_and_a_custom_protocol() {
    $this->beConstructedWith( self::$test_file, [ 'config' => 'upload_with_host.yml' ]);

    $this->url( 'original', [ 'protocol' => 'http:' ] )->shouldBe( 'http://superserver.com/custom/upload/test/w.jpg' );
  }

  function it_returns_the_s3_url_of_the_file() {
    $this->beConstructedWith( self::$test_file, [ 'storage' => 's3' ]);
    $this->url()->shouldBe( 'https://zow-v5-test.s3-eu-central-1.amazonaws.com/test_models/1/images/w.jpg' ); 
  }


  // NameFormat method
  function it_returns_the_default_name_format() {
    $this->nameFormat()->shouldStartWith( '{{ basename }}.{{ ext }}' );
  }

  function it_returns_the_default_name_format_if_styles_are_defined() {
    $this->beConstructedWith( self::$test_file, [
      'styles' => [
        'frop' => '21x21#'
      , 'original' => '300x300'
      ]
    ]);
    $this->nameFormat()->shouldStartWith( '{{ basename }}-{{ style }}.{{ ext }}' );
  }

  function it_returns_the_name_format_defined_in_the_config_file() {
    $this->beConstructedWith( self::$test_file, [
      'config' => 'upload_formatted.yml'
    ]);
    $this->nameFormat()->shouldStartWith( '{{ basename }}-{{ style }}.{{ ext }}' );
  }

  function it_returns_the_name_format_provided_in_the_uploadable_configuration() {
    $this->beConstructedWith( self::$test_file, [
      'styles' => [
        'frop' => '21x21#'
      , 'original' => '300x300'
      ]
    , 'name' => '{{ basename }}-{{ style }}-image.{{ ext }}'
    ]);
    $this->name( 'frop' )->shouldStartWith( 'w-frop-image.jpg' );
  }


  // Magic __get method
  function it_gets_the_name_file_and_url_of_a_style() {
    $this->beConstructedWith( self::$test_file, [
      'styles' => [
        'mini' => '10x10!'
      , 'original' => '1024x1024>'
      ]
    ]);

    $this->original_name->shouldStartWith( 'w-original.jpg' );
    $this->original_file->shouldStartWith( App::root( 'dev/upload/test/w-original.jpg' ) );
    $this->original_url->shouldStartWith( 'https://localhost/dev/upload/test/w-original.jpg' );
    $this->mini_name->shouldStartWith( 'w-mini.jpg' );
    $this->mini_file->shouldStartWith( App::root( 'dev/upload/test/w-mini.jpg' ) );
    $this->mini_url->shouldStartWith( 'https://localhost/dev/upload/test/w-mini.jpg' );
  }


  // DirFormat method
  function it_returns_the_default_dir_format() {
    $this->beConstructedWith( self::$test_file, [
      'config' => 'upload_dirless.yml'
    ]);
    $this->dirFormat()->shouldStartWith( '{{ model }}/{{ id }}/{{ name }}' );
  }

  function it_returns_the_dir_format_defined_in_the_config_file() {
    $this->beConstructedWith( self::$test_file, [
      'config' => 'upload_formatted.yml'
    ]);
    $this->dirFormat()->shouldStartWith( 'model/{{ model }}/id/{{ id }}/name/{{ name }}' );
  }

  function it_returns_the_dir_format_provided_in_the_uploadable_configuration() {
    $this->dirFormat([
      'dir' => 'uploads/{{ name }}/{{ model }}/{{ id }}'
    ])->shouldStartWith( 'uploads/{{ name }}/{{ model }}/{{ id }}' );
  }
  
}
