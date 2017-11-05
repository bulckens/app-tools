<?php

namespace spec\Bulckens\AppTools\Helpers;

use Illuminate\Support\Str;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Upload\File;
use Bulckens\AppTests\TestModel;
use Bulckens\AppTools\Helpers\UploadableHelper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UploadableHelperSpec extends ObjectBehavior {

  protected static $model;
  protected static $file;
  protected static $test_file = [
    'name' => 'w.jpg'
  , 'mime' => 'image/jpeg'
  , 'size' => 18338
  , 'meta' => '{"width":320,"height":320}'
  ];
  
  function let() {
    $app = new App( 'dev' );
    $app->run();

    self::$model = new TestModel();
    self::$model->save();

    self::$file = new File( self::$test_file );
  }

  function letGo() {
    TestModel::truncate();
  }

  // Dir method (static)
  function it_builds_a_default_upload_dir() {
    $file = new File( self::$test_file, [ 'config' => 'upload_dirless.yml' ]);

    $this::dir( self::$model, 'image', $file->dirFormat() )->shouldBe( 'test_models/1/images' );
  }

  function it_fails_if_a_given_field_is_not_recognized() {
    $this::shouldThrow( 'Bulckens\AppTools\Helpers\UploadableHelperDirFieldUnknownException' )
      ->duringDir( self::$model, 'image', self::$file->dirFormat([ 'dir' => '{{ class }}/{{ id }}/{{ name }}' ]) );
  }

  function it_fails_if_an_object_id_is_requested_but_it_is_not_there() {
    $this::shouldThrow( 'Bulckens\AppTools\Helpers\UploadableHelperObjectIdMissingException' )
      ->duringDir( new TestModel(), 'image', self::$file->dirFormat([ 'dir' => '{{ model }}/{{ id }}/{{ name }}' ]) );
  }

}
