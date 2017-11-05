<?php

namespace spec\Bulckens\AppTools\Upload;

use Exception;
use Bulckens\Helpers\StringHelper;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Upload\Tmp;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TmpSpec extends ObjectBehavior {
  
  function let() {
    global $_FILES;
    global $_SERVER;

    $_SERVER['HTTP_HOST'] = 'localhost';

    $app = new App( 'dev' );
    $app->run();

    $_FILES['image'] = [
      'name' => 'w.jpg'
    , 'tmp_name' => self::setupTmpFile()
    , 'error' => UPLOAD_ERR_OK
    ];

    $this->beConstructedWith( 'image' );
  }

  function letGo() {
    exec( sprintf( 'rm -rf %s', escapeshellarg( App::root( 'dev/upload/tmp' ) ) ) );
    exec( sprintf( 'rm -rf %s', escapeshellarg( App::root( 'dev/upload/test' ) ) ) );
  }


  // Initialization
  function it_stores_the_upload() {
    $this->source()->shouldBeArray();
  }

  function it_stores_the_file_name() {
    $this->name()->shouldBe( 'w.jpg' );
  }

  function it_accepts_an_upload_source() {
    $this->beConstructedWith([
      'name' => 'w.jpg'
    , 'tmp_name' => self::setupTmpFile()
    , 'error' => UPLOAD_ERR_OK
    ]);
    $this->name()->shouldBe( 'w.jpg' );
    $this->error()->shouldBe( UPLOAD_ERR_OK );
    $root = str_replace( '/', '\/', App::root( 'dev/upload/tmp/[A-Za-z0-9]{32}' ) );
    $this->tmpName()->shouldMatch( "/$root/" );
  }

  function it_stores_the_sanitizes_the_file_name() {
    $_FILES['image'] = [
      'name' => 'før Lasma ni ñogha$ !!!.jpg'
    , 'tmp_name' => self::setupTmpFile()
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'image' );
    $this->name()->shouldBe( 'for-lasma-ni-nogha.jpg' );
  }

  function it_stores_the_tmp_name() {
    $root = str_replace( '/', '\/', App::root( 'dev/upload/tmp/[A-Za-z0-9]{32}' ) );
    $this->tmpName()->shouldMatch( "/$root/" );
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
    $this->beConstructedWith( 'image', [ 'storage' =>  's3' ] );
    $this->storage()->shouldBe( 's3' );
  }

  function it_fails_if_the_given_key_is_not_present_in_the_files_array() {
    $this
      ->shouldThrow( 'Bulckens\AppTools\Upload\TmpKeyNotFoundException' )
      ->during__construct( 'void' );
  }

  function it_fails_if_the_tmp_name_does_not_exist() {
    $_FILES['image']['tmp_name'] = App::root( 'undefined/unrelated/w.jpg' );

    $this
      ->shouldThrow( 'Bulckens\AppTools\Upload\TmpNameNotFoundException' )
      ->during__construct( 'image' );
  }

  function it_fails_if_the_given_upload_is_not_a_string_or_an_array() {
    $this
      ->shouldThrow( 'Bulckens\AppTools\Upload\TmpSourceNotAcceptableException' )
      ->during__construct( 123 );
  }

  function it_fails_if_the_given_source_is_incomplete() {
    $this
      ->shouldThrow( 'Bulckens\AppTools\Upload\TmpSourceIncompleteException' )
      ->during__construct([ 'tmp_name' => '/some/tmp/name' ]);
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


  // Source method
  function it_returns_the_upload() {
    $upload = $this->source();
    $upload->shouldBeArray();
    $upload->shouldHaveKeyWithValue( 'name', 'w.jpg' );
    $upload->shouldHaveKeyWithValue( 'name', 'w.jpg' );
    $upload->shouldHaveKeyWithValue( 'error', UPLOAD_ERR_OK );
  }


  // Basename
  function it_returns_the_name_without_extension() {
    $this->basename()->shouldBe( 'w' );
  }


  // Name method
  function it_returns_the_name() {
    $this->name()->shouldBe( 'w.jpg' );
  }

  function it_sanitizes_the_name() {
    $_FILES['image'] = [
      'name' => ' Some crazy ? 123 value with años to go!!!'
    , 'tmp_name' => self::setupTmpFile()
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->name()->shouldStartWith( 'some-crazy-123-value-with-anos-to-go.jpg' );
  }

  function it_allows_the_name_to_be_passed_without_an_extension() {
    $_FILES['image'] = [
      'name' => 'without'
    , 'tmp_name' => self::setupTmpFile()
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->name()->shouldBe( 'without.jpg' );
  }

  function it_allows_the_name_to_be_passed_with_an_extension_even_if_it_is_not_the_right_one() {
    $_FILES['image'] = [
      'name' => 'with.gif'
    , 'tmp_name' => self::setupTmpFile()
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->name()->shouldBe( 'with.jpg' );
  }

  function it_returns_a_file_with_lowercase_name_when_sanitization_is_enabled_by_default() {
    $_FILES['image'] = [
      'name' => 'DOGFOOD_CONTAINER.JPG'
    , 'tmp_name' => self::setupTmpFile()
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'image' );
    $this->name()->shouldBe( 'dogfood-container.jpg' );
  }

  function it_returns_the_file_name_as_given_with_sanitization_disabled() {
    $_FILES['image'] = [
      'name' => 'DOG FØØD CONTAINER≈.JPG'
    , 'tmp_name' => self::setupTmpFile()
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'image', [ 'config' => 'upload_unsanitized.yml' ] );
    $this->name()->shouldEndWith( 'DOG FØØD CONTAINER≈.jpg' );
  }

  function it_returns_the_file_name_formatted_as_configured_globally() {
    $_FILES['image'] = [
      'name' => 'DOG FØØD CONTAINER≈.JPG'
    , 'tmp_name' => self::setupTmpFile()
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'image', [
      'config' => 'upload_formatted.yml'
    , 'styles' => [ 'large' => '2560x2560#' ]
    ]);
    $this->name( 'large' )->shouldEndWith( 'dog-food-container-large.jpg' );
  }

  function it_returns_the_file_name_formatted_as_configured_locally() {
    $_FILES['image'] = [
      'name' => 'DOG FØØD CONTAINER≈.JPG'
    , 'tmp_name' => self::setupTmpFile()
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'image', [
      'config' => 'upload_formatted.yml'
    , 'name' => '{{ style }}.{{ name }}'
    , 'styles' => [ 'medium' => '1280x1280#' ]
    ]);
    $this->name( 'medium' )->shouldEndWith( 'medium.dog-food-container.jpg' );
  }

  function it_returns_the_file_name_formatted_as_configured_locally_with_width_and_height() {
    $_FILES['image'] = [
      'name' => 'DOG FØØD CONTAINER≈.JPG'
    , 'tmp_name' => self::setupTmpFile()
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'image', [
      'config' => 'upload_formatted.yml'
    , 'name' => '{{ style }}-{{ width }}x{{ height }}.{{ name }}'
    , 'styles' => [ 'medium' => '1280x1280#' ]
    ]);
    $this->name( 'medium' )->shouldEndWith( 'medium-320x320.dog-food-container.jpg' );
  }

  function it_returns_the_file_name_formatted_as_configured_locally_with_a_default_style_label() {
    $_FILES['image'] = [
      'name' => 'DOG FØØD CONTAINER≈.JPG'
    , 'tmp_name' => self::setupTmpFile()
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'image', [
      'config' => 'upload_formatted.yml'
    , 'name' => 'image-{{ style }}.{{ name }}'
    ]);
    $this->name()->shouldEndWith( 'image-original.dog-food-container.jpg' );
  }

  function it_uses_the_default_name_format_if_styles_are_defined_but_no_format_is_given() {
    $_FILES['image'] = [
      'name' => 'DOG FØØD CONTAINER≈.JPG'
    , 'tmp_name' => self::setupTmpFile()
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'image', [
      'styles' => [
        'mini' => '10x10!'
      , 'original' => '1024x1024>'
      ]
    ]);
    $this->name( 'mini' )->shouldEndWith( 'dog-food-container-mini.jpg' );
    $this->name( 'original' )->shouldEndWith( 'dog-food-container-original.jpg' );
  }

  function it_uses_the_original_style_when_no_style_is_given_but_styles_are_defined() {
    $_FILES['image'] = [
      'name' => 'DOG FØØD CONTAINER≈.JPG'
    , 'tmp_name' => self::setupTmpFile()
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'image', [
      'styles' => [
        'mini' => '10x10!'
      , 'original' => '1024x1024>'
      ]
    ]);
    $this->name()->shouldEndWith( 'dog-food-container-original.jpg' );
  }


  // Rename method
  function it_sets_a_custom_name() {
    $this->rename( 'beast' );
    $this->name()->shouldEndWith( 'beast.jpg' );
  }

  function it_sets_a_custom_name_but_ignores_a_given_extension() {
    $this->rename( 'coolaid.gif' );
    $this->name()->shouldEndWith( 'coolaid.jpg' );
  }

  function it_returns_itself_after_setting_the_name() {
    $this->rename( 'coolaid.jpg' )->shouldBe( $this );
  }


  // Ext method
  function it_returns_the_file_extension() {
    $this->ext()->shouldBe( 'jpg' );
  }

  function it_returns_the_real_extension_even_if_none_is_given() {
    $_FILES['image'] = [
      'name' => 'DOGFOOD_CONTAINER'
    , 'tmp_name' => self::setupTmpFile()
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'image' );
    $this->ext()->shouldBe( 'jpg' );
  }

  function it_returns_the_real_extension_even_if_the_wrong_one_is_given() {
    $_FILES['image'] = [
      'name' => 'fake.jpg'
    , 'tmp_name' => self::setupTmpFile( 'fake.jpg' )
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'image' );
    $this->ext()->shouldBe( 'txt' );
  }


  // TmpName method
  function it_returns_the_tmp_name() {
    $root = str_replace( '/', '\/', App::root( 'dev/upload/tmp/[A-Za-z0-9]{32}' ) );
    $this->tmpName()->shouldMatch( "/$root/" );
  }

  function it_returns_the_tmp_name_for_a_style() {
    $this->beConstructedWith( 'image', [
      'styles' => [
        'medium' => '1024x1024#'
      ]
    ]);
    $root = str_replace( '/', '\/', App::root( 'dev/upload/tmp/[A-Za-z0-9]{32}\.style-medium' ) );
    $this->tmpName( 'medium' )->shouldMatch( "/$root/" );
  }

  function it_creates_the_tmp_file_for_a_style() {
    $this->beConstructedWith( 'image', [
      'styles' => [
        'mastaba' => '1024x1024#'
      ]
    ]);
    $tmp_name = $this->tmpName( 'mastaba' )->getWrappedObject();

    if ( ! file_exists( $tmp_name ) ) {
      throw new Exception( "File $tmp_name does not exist" );
    }
  }

  function it_converts_the_image_to_be_cropped_to_given_dimensions_in_the_style() {
    $_FILES['image'] = [ 'name' => 'm.jpg', 'tmp_name' => self::setupTmpFile( 'm.jpg' ), 'error' => UPLOAD_ERR_OK ];

    $this->beConstructedWith( 'image', ['styles' => [ 'small' => '128x128#' ] ]);
    $tmp_name = $this->tmpName( 'small' )->getWrappedObject();
    $size = getimagesize( $tmp_name );

    if ( $size[0] != 128 || $size[1] != 128 ) {
      throw new Exception( "Expected the file to be 128x128px wide but got {$size[0]}x{$size[1]}px" );
    }
  }

  function it_converts_the_image_to_fit_a_given_box_thereby_cropping_the_image() {
    $_FILES['image'] = [ 'name' => 'm.jpg', 'tmp_name' => self::setupTmpFile( 'm.jpg' ), 'error' => UPLOAD_ERR_OK ];

    $this->beConstructedWith( 'image', [ 'styles' => [ 'small' => '256x256' ] ]);
    $tmp_name = $this->tmpName( 'small' )->getWrappedObject();
    $size = getimagesize( $tmp_name );

    if ( $size[0] != 177 || $size[1] != 256 ) {
      throw new Exception( "Expected the file fit within 256x256px but got {$size[0]}x{$size[1]}px" );
    }
  }

  function it_converts_the_image_to_fit_a_given_box_and_scales_the_image_up_if_it_is_too_small_while_respecting_the_aspect_ratio() {
    $_FILES['image'] = [ 'name' => 'm.jpg', 'tmp_name' => self::setupTmpFile( 'm.jpg' ), 'error' => UPLOAD_ERR_OK ];

    $this->beConstructedWith( 'image', [ 'styles' => [ 'small' => '2560x2560' ] ]);
    $tmp_name = $this->tmpName( 'small' )->getWrappedObject();
    $size = getimagesize( $tmp_name );

    if ( $size[0] != 1768 || $size[1] != 2560 ) {
      throw new Exception( "Expected the file fit within 2560x2560px but got {$size[0]}x{$size[1]}px" );
    }
  }

  function it_converts_the_image_to_fit_a_given_box_without_scaling_the_image_up_if_it_is_too_small() {
    $_FILES['image'] = [ 'name' => 'm.jpg', 'tmp_name' => self::setupTmpFile( 'm.jpg' ), 'error' => UPLOAD_ERR_OK ];

    $this->beConstructedWith( 'image', [ 'styles' => [ 'small' => '2560x2560>' ] ]);
    $tmp_name = $this->tmpName( 'small' )->getWrappedObject();
    $size = getimagesize( $tmp_name );

    if ( $size[0] != 746 || $size[1] != 1080 ) {
      throw new Exception( "Expected the file fit within 2560x2560px without scaling it up but got {$size[0]}x{$size[1]}px" );
    }
  }

  function it_converts_the_image_to_fit_a_given_box_and_scales_the_image_up_if_it_is_too_small_while_not_respecting_the_aspect_ratio() {
    $_FILES['image'] = [ 'name' => 'm.jpg', 'tmp_name' => self::setupTmpFile( 'm.jpg' ), 'error' => UPLOAD_ERR_OK ];

    $this->beConstructedWith( 'image', [ 'styles' => [ 'small' => '2560x2560!' ] ]);
    $tmp_name = $this->tmpName( 'small' )->getWrappedObject();
    $size = getimagesize( $tmp_name );

    if ( $size[0] != 2560 || $size[1] != 2560 ) {
      throw new Exception( "Expected the image to be exactly 2560x2560px but got {$size[0]}x{$size[1]}px" );
    }
  }

  function it_converts_the_image_with_an_additional_image_magick_command() {
    // NOTE: this should be verified manually
    $this->beConstructedWith( 'image', [
      'styles' => [
        'small' => '230x230#'
      ]
    , 'convert' => '-colorspace Gray'
    ]);
    $this->tmpName( 'small' )->shouldBeString();
  }

  function it_converts_the_image_with_an_additional_image_magick_command_for_every_style() {
    // NOTE: this should be verified manually
    $this->beConstructedWith( 'image', [
      'styles' => [
        'small' => '240x240#'
      , 'medium' => '300x350#'
      , 'original' => '640x640>'
      ]
    , 'convert' => [
        'small' => '-colorspace Gray'
      , 'medium' => '\( +clone -sepia-tone 60% \) -average'
      ]
    ]);
    $this->tmpName( 'small' )->shouldBeString();
    $this->tmpName( 'medium' )->shouldBeString();
    $this->tmpName( 'original' )->shouldBeString();
  }

  function it_fails_if_the_resize_command_for_the_style_is_invalid() {
    $this->beConstructedWith( 'image', [
      'styles' => [
        'mini' => '128x 128#'
      ]
    ]);
    $this->shouldThrow( 'Bulckens\AppTools\Upload\TmpStyleNotValidException' )->duringTmpName( 'mini' );
  }

  function it_fails_if_the_resize_command_for_the_style_is_defines_no_value_at_all() {
    $this->beConstructedWith( 'image', [
      'styles' => [
        'mini' => 'x>'
      ]
    ]);
    $this->shouldThrow( 'Bulckens\AppTools\Upload\TmpStyleNotValidException' )->duringTmpName( 'mini' );
  }

  function it_fails_when_the_image_magick_command_could_not_be_found() {
    $this->beConstructedWith( 'image', [
      'styles' => [
        'massive' => '1001x1001#'
      ]
    , 'config' => 'upload_bad_magick.yml'
    ]);
    $this->shouldThrow( 'Bulckens\AppTools\Upload\TmpImageMagickNotFoundException' )->duringTmpName( 'massive' );
  }



  // Error method
  function it_returns_the_error() {
    $this->error()->shouldBe( UPLOAD_ERR_OK );
  }


  // Size method
  function it_returns_the_size() {
    $this->size()->shouldBe( 18338 );
  }


  // Weight method (human readable size)
  function it_returns_the_weight() {
    $this->weight()->shouldBe( '17.91 KB' );
  }


  // Dimensions method
  function it_returns_the_dimensions_of_an_image() {
    $_FILES['image'] = [
      'name' => 'm.jpg'
    , 'tmp_name' => self::setupTmpFile( 'm.jpg' )
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'image' );

    $dimensions = $this->dimensions();
    $dimensions->shouldBeArray();
    $dimensions[0]->shouldBe( 746 );
    $dimensions[1]->shouldBe( 1080 );
    $dimensions['width']->shouldBe( 746 );
    $dimensions['height']->shouldBe( 1080 );
  }


  // Width method
  function it_returns_the_width_of_an_image() {
    $_FILES['image'] = [
      'name' => 'm.jpg'
    , 'tmp_name' => self::setupTmpFile( 'm.jpg' )
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'image' );
    $this->width()->shouldBe( 746 );
  }



  // Height method
  function it_returns_the_height_of_an_image() {
    $_FILES['image'] = [
      'name' => 'm.jpg'
    , 'tmp_name' => self::setupTmpFile( 'm.jpg' )
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'image' );
    $this->height()->shouldBe( 1080 );
  }


  // IsImage method
  function it_tests_positive_if_a_file_is_an_image() {
    $this->shouldBeImage();
  }

  function it_tests_negative_if_a_file_is_not_an_image() {
    $_FILES['text'] = [
      'name' => 'not-an-image.txt'
    , 'tmp_name' => self::setupTmpFile( 'not-an-image.txt' )
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'text' );
    $this->shouldNotBeImage();
  }


  // Mime method
  function it_returns_the_mime_type() {
    $this->mime()->shouldBe( 'image/jpeg' );
  }


  // Meta method
  function it_returns_a_json_string() {
    $this->meta()->shouldBeJson();
  }

  function it_returns_a_json_string_with_image_dimensions() {
    $this->meta()->shouldHaveJsonKeyWithValue( 'width', 320 );
    $this->meta()->shouldHaveJsonKeyWithValue( 'height', 320 );
  }

  function it_returns_a_json_string_without_image_dimensions_for_non_image_formats() {
    $_FILES['text'] = [
      'name' => 'not-an-image.txt'
    , 'tmp_name' => self::setupTmpFile( 'not-an-image.txt' )
    , 'error' => UPLOAD_ERR_OK
    ];
    $this->beConstructedWith( 'text' );
    $this->meta()->shouldNotHaveJsonKey( 'width' );
    $this->meta()->shouldNotHaveJsonKey( 'height' );
  }


  // Storage method
  function it_returns_the_storage() {
    $this->beConstructedWith( 'image', [ 'storage' => 's3' ] );
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
    $this->shouldThrow( 'Bulckens\AppTools\Upload\TmpStorageNotConfiguredException' )->duringStorage( 'falumba' );
  }


  // Store method
  function it_stores_the_file_on_the_file_system() {
    $this->store()->shouldBe( true );
    $this->url()->shouldBe( 'https://localhost/dev/upload/test/w.jpg' );
  }

  function it_stores_the_file_on_the_file_system_into_a_given_directory() {
    $this->dir( 'mecaniq/arms' )->store()->shouldBe( true );
    $this->path()->shouldBe( '/dev/upload/test/mecaniq/arms/w.jpg' );
  }

  function it_stores_the_file_on_s3() {
    $this->beConstructedWith( 'image', [
      'storage' => 's3'
    ]);
    $this->store()->shouldBe( true );
    $this->url()->shouldBe( 'https://zow-v5-test.s3-eu-central-1.amazonaws.com/w.jpg' );
  }

  function it_stores_all_the_styles_on_s3() {
    $this->beConstructedWith( 'image', [
      'storage' => 's3'
    , 'styles' => [
        'mini' => '64x64#'
      , 'small' => '128x128#'
      , 'original' => '1028x1028>'
      ]
    ]);
    $this->dir( 'with_styles' )->store()->shouldBe( true );
    $this->url( 'original' )->shouldEndWith( 'https://zow-v5-test.s3-eu-central-1.amazonaws.com/with_styles/w-original.jpg' );
  }

  function it_stores_the_file_on_s3_into_a_given_directory() {
    $this->beConstructedWith( 'image', [
      'storage' => 's3'
    ]);
    $this->dir( 'some/other/dir' )->store()->shouldBe( true );
    $this->url()->shouldEndWith( 'https://zow-v5-test.s3-eu-central-1.amazonaws.com/some/other/dir/w.jpg' );
  }

  function it_stores_the_file_on_s3_into_a_given_bucket() {
    $this->beConstructedWith( 'image', [
      'storage' => 's3_ireland'
    ]);
    $this->store()->shouldBe( true );
    $this->url()->shouldEndWith( 'https://zow-v5-test-alternative.s3-eu-west-1.amazonaws.com/w.jpg' );
  }

  function it_fails_if_the_given_storage_type_does_not_exist() {
    $this->beConstructedWith( 'image', [ 'config' => 'upload_unknown.yml' ]);
    $this->shouldThrow( 'Bulckens\AppTools\Upload\TmpStorageTypeUnknownException' )->duringStore();
  }

  function it_fails_when_store_is_called_twice() {
    $this->store()->shouldBe( true );
    $this->shouldThrow( 'Bulckens\AppTools\Upload\TmpAlreadyStoredException' )->duringStore();
  }

  function it_fails_when_no_access_key_and_or_secret_are_configured() {
    $this->beConstructedWith( 'image', [
      'config' => 'upload_credentialless.yml'
    ]);
    $this->shouldThrow( 'Bulckens\AppTools\Upload\TmpS3CredentialsNotDefinedException' )->duringStore();
  }
  
  function it_fails_when_no_region_is_defined() {
    $this->beConstructedWith( 'image', [
      'config' => 'upload_regionless.yml'
    ]);
    $this->shouldThrow( 'Bulckens\AppTools\Upload\TmpS3RegionNotDefinedException' )->duringStore();
  }

  function it_fails_when_no_bucket_is_defined() {
    $this->beConstructedWith( 'image', [
      'config' => 'upload_bucketless.yml'
    ]);
    $this->shouldThrow( 'Bulckens\AppTools\Upload\TmpS3BucketNotDefinedException' )->duringStore();
  }

  function it_fails_when_the_bucket_does_not_exist() {
    $this->beConstructedWith( 'image', [
      'config' => 'upload_unknown.yml'
    , 'storage' => 's3'
    ]);
    $this->shouldThrow( 'Aws\S3\Exception\S3Exception' )->duringStore();
  }



  // File method
  function it_returns_the_full_destination_file_path() {
    $this
      ->file()
      ->shouldStartWith( App::root( 'dev/upload/test/w.jpg' ) );
  }

  function it_returns_the_full_destination_file_path_with_a_style() {
    $this->beConstructedWith( 'image', [
      'styles' => [
        'micro' => '2x2#'
      , 'original' => '1280x1280>'
      ]
    ]);

    $this
      ->file( 'micro' )
      ->shouldStartWith( App::root( 'dev/upload/test/w-micro.jpg' ) );
  }

  function it_returns_the_full_destination_file_path_with_a_custom_name() {
    $this->rename( 'stored' );
    $this
      ->file()
      ->shouldStartWith( App::root( 'dev/upload/test/stored.jpg' ) );
  }

  function it_returns_the_full_destination_file_path_with_an_additional_sub_path() {
    $this->rename( 'substored' );
    $this
      ->dir( 'picture/nicely' )
      ->file()
      ->shouldStartWith( App::root( 'dev/upload/test/picture/nicely/substored.jpg' ) );
  }

  function it_ensures_a_uniqe_file_name() {
    file_put_contents( App::root( 'dev/upload/test/w.jpg' ), '' );
    $this->file()->shouldMatch( '/w\.\d{13}\.jpg$/' );
  }

  function it_returns_the_absolute_path_for_filesystem_storage() {
    $this->dir( 'will/power' )->file()->shouldBe( App::root( 'dev/upload/test/will/power/w.jpg' ) );
  }

  function it_returns_the_absolute_path_for_filesystem_streamed_storage() {
    $this->beConstructedWith( 'image', [
      'storage' => 'external'
    ]);
    $this->dir( 'will/power' )->file()->shouldBe( "http://server.local/safe/to/store/will/power/w.jpg" );
  }

  function it_returns_the_relative_path_for_s3_storage() {
    $this->beConstructedWith( 'image', [
      'storage' => 's3'
    ]);
    $this->dir( 'will/power' )->file()->shouldBe( '/will/power/w.jpg' );
  }


  // Dir method
  function it_returns_the_dir() {
    $this->dir( 'halla/23/malla' );
    $this->dir()->shouldBe( 'halla/23/malla' );
  }

  function it_sets_the_dir_of_the_file() {
    $this->dir()->shouldBe( null );
    $this->dir( 'some/sub/directory' );
    $this->dir()->shouldBe( 'some/sub/directory' );
  }

  function it_strips_any_leading_and_trailing_slashes() {
    $this->dir( '/some/sub/directory/' );
    $this->dir()->shouldBe( 'some/sub/directory' );
  }

  function it_returns_itself_after_setting_the_path_of_the_file() {
    $this->dir( 'some/sub/directory' )->shouldBe( $this );
  }


  // Path method
  function it_returns_the_public_path_to_the_file() {
    $this->path()->shouldBe( '/dev/upload/test/w.jpg' );
  }

  function it_returns_the_public_path_to_the_file_for_a_given_style() {
    $this->beConstructedWith( 'image', [
      'styles' => [
        'mini' => '256x256#'
      ]
    ]);
    $this->path( 'mini' )->shouldEndWith( '/dev/upload/test/w-mini.jpg' );
  }

  function it_returns_the_public_s3_path_to_the_file() {
    $this->beConstructedWith( 'image', [
      'storage' => 's3'
    ]);
    $this->path()->shouldBe( '/w.jpg' );
  }

  function it_returns_the_public_s3_path_to_the_file_for_a_given_style() {
    $this->beConstructedWith( 'image', [
      'storage' => 's3'
    , 'styles' => [
        'tiny' => '128x128#'
      ]
    ]);
    $this->path( 'tiny' )->shouldBe( '/w-tiny.jpg' );
  }


  // Url method
  function it_returns_the_url_of_the_file() {
    $this->url()->shouldBe( 'https://localhost/dev/upload/test/w.jpg' );
  }

  function it_returns_the_url_of_the_file_in_a_given_style() {
    $this->beConstructedWith( 'image', [
      'styles' => [
        'frop' => '21x21#'
      ]
    ]);
    $this->url( 'frop' )->shouldBe( 'https://localhost/dev/upload/test/w-frop.jpg' );
  }

  function it_returns_the_url_of_the_file_in_the_original_style_by_default() {
    $this->beConstructedWith( 'image', [
      'styles' => [
        'frop' => '21x21#'
      , 'original' => '300x300'
      ]
    ]);
    $this->url()->shouldEndWith( 'https://localhost/dev/upload/test/w-original.jpg' );
  }

  function it_returns_the_url_of_the_file_with_a_cofigured_host() {
    $this->beConstructedWith( 'image', [
      'config' => 'upload_with_host.yml'
    ]);
    $this->url()->shouldBe( 'https://superserver.com/custom/upload/test/w.jpg' );
  }

  function it_returns_the_url_of_the_file_with_a_cofigured_host_and_a_custom_protocol() {
    $this->beConstructedWith( 'image', [
      'config' => 'upload_with_host.yml'
    ]);
    $this->url( 'original', [ 'protocol' => 'http:' ] )->shouldBe( 'http://superserver.com/custom/upload/test/w.jpg' );
  }

  function it_returns_the_s3_url_of_the_file() {
    $this->beConstructedWith( 'image', [
      'storage' => 's3'
    ]);
    $this->url()->shouldBe( 'https://zow-v5-test.s3-eu-central-1.amazonaws.com/w.jpg' ); 
  }



  // NameFormat method
  function it_returns_the_default_name_format() {
    $this->nameFormat()->shouldStartWith( '{{ basename }}.{{ ext }}' );
  }

  function it_returns_the_default_name_format_if_styles_are_defined() {
    $this->beConstructedWith( 'image', [
      'styles' => [
        'frop' => '21x21#'
      , 'original' => '300x300'
      ]
    ]);
    $this->nameFormat()->shouldStartWith( '{{ basename }}-{{ style }}.{{ ext }}' );
  }

  function it_returns_the_name_format_defined_in_the_config_file() {
    $this->beConstructedWith( 'image', [
      'config' => 'upload_formatted.yml'
    ]);
    $this->nameFormat()->shouldStartWith( '{{ basename }}-{{ style }}.{{ ext }}' );
  }

  function it_returns_the_name_format_provided_in_the_uploadable_configuration() {
    $this->beConstructedWith( 'image', [
      'styles' => [
        'frop' => '21x21#'
      , 'original' => '300x300'
      ]
    , 'name' => '{{ basename }}-{{ style }}-image.{{ ext }}'
    ]);
    $this->name( 'frop' )->shouldStartWith( 'w-frop-image.jpg' );
  }


  // DirFormat method
  function it_returns_the_default_dir_format() {
    $this->beConstructedWith( 'image', [
      'config' => 'upload_dirless.yml'
    ]);
    $this->dirFormat()->shouldStartWith( '{{ model }}/{{ id }}/{{ name }}' );
  }

  function it_returns_the_dir_format_defined_in_the_config_file() {
    $this->beConstructedWith( 'image', [
      'config' => 'upload_formatted.yml'
    ]);
    $this->dirFormat()->shouldStartWith( 'model/{{ model }}/id/{{ id }}/name/{{ name }}' );
  }

  function it_returns_the_dir_format_provided_in_the_uploadable_configuration() {
    $this->dirFormat([
      'dir' => 'uploads/{{ name }}/{{ model }}/{{ id }}'
    ])->shouldStartWith( 'uploads/{{ name }}/{{ model }}/{{ id }}' );
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
