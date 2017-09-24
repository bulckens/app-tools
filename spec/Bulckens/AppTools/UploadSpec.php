<?php

namespace spec\Bulckens\AppTools;

use Exception;
use Bulckens\Helpers\StringHelper;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Upload;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UploadSpec extends ObjectBehavior {
  
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
  function it_stores_the_key() {
    $this->key()->shouldBe( 'image' );
  }

  function it_stores_the_file_name() {
    $this->name()->shouldBe( 'w.jpg' );
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

  function it_sanitizes_the_name() {
    $this->name( ' Some crazy ? 123 value with años to go!!!' );
    $this->name()->shouldStartWith( 'some-crazy-123-value-with-anos-to-go.jpg' );
  }

  function it_allows_the_name_to_be_passed_without_an_extension() {
    $this->name( 'without' );
    $this->name()->shouldBe( 'without.jpg' );
  }

  function it_allows_the_name_to_be_passed_with_an_extension() {
    $this->name( 'with.gif' );
    $this->name()->shouldBe( 'with.jpg' );
  }

  function it_returns_a_file_with_lowercase_extension_when_sanitization_is_enabled_by_default() {
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
    $this->name()->shouldEndWith( 'DOG FØØD CONTAINER≈.JPG' );
  }


  // TmpName method
  function it_returns_the_tmp_name() {
    $root = str_replace( '/', '\/', App::root( 'dev/upload/tmp/[A-Za-z0-9]{32}' ) );
    $this->tmpName()->shouldMatch( "/$root/" );
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


  // Mime method
  function it_returns_the_mime_type() {
    $this->mime()->shouldBe( 'image/jpeg' );
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
    $this->shouldThrow( 'Bulckens\AppTools\UploadStorageNotConfiguredException' )->duringStorage( 'falumba' );
  }


  // Exists method
  function it_tests_the_existance_of_the_given_key_in_the_files_array() {
    $this->exists()->shouldBe( true );
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
    $this->shouldThrow( 'Bulckens\AppTools\UploadStorageTypeUnknownException' )->duringStore();
  }

  function it_fails_when_store_is_called_twice() {
    $this->store()->shouldBe( true );
    $this->shouldThrow( 'Bulckens\AppTools\UploadAlreadyStoredException' )->duringStore();
  }

  function it_fails_when_no_access_key_and_or_secret_are_configured() {
    $this->beConstructedWith( 'image', [
      'config' => 'upload_credentialless.yml'
    ]);
    $this->shouldThrow( 'Bulckens\AppTools\UploadS3CredentialsNotDefinedException' )->duringStore();
  }
  
  function it_fails_when_no_region_is_defined() {
    $this->beConstructedWith( 'image', [
      'config' => 'upload_regionless.yml'
    ]);
    $this->shouldThrow( 'Bulckens\AppTools\UploadS3RegionNotDefinedException' )->duringStore();
  }

  function it_fails_when_no_bucket_is_defined() {
    $this->beConstructedWith( 'image', [
      'config' => 'upload_bucketless.yml'
    ]);
    $this->shouldThrow( 'Bulckens\AppTools\UploadS3BucketNotDefinedException' )->duringStore();
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

  function it_returns_the_full_destination_file_path_with_a_custom_name() {
    $this->name( 'stored' );
    $this
      ->file()
      ->shouldBe( App::root( 'dev/upload/test/stored.jpg' ) );
  }

  function it_returns_the_full_destination_file_path_with_an_additional_sub_path() {
    $this->name( 'substored' );
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

  function it_returns_the_public_s3_path_to_the_file() {
    $this->beConstructedWith( 'image', [
      'storage' => 's3'
    ]);
    $this->path()->shouldBe( '/w.jpg' );
  }


  // Url method
  function it_returns_the_url_of_the_file() {
    $this->url()->shouldBe( 'https://localhost/dev/upload/test/w.jpg' );
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
    $this->url( 'http:' )->shouldBe( 'http://superserver.com/custom/upload/test/w.jpg' );
  }

  function it_returns_the_s3_url_of_the_file() {
    $this->beConstructedWith( 'image', [
      'storage' => 's3'
    ]);
    $this->url()->shouldBe( 'https://zow-v5-test.s3-eu-central-1.amazonaws.com/w.jpg' ); 
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