<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\Output;
use Bulckens\AppTools\App;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class OutputSpec extends ObjectBehavior {

  function let() {
    new App( 'dev' );
    $this->beConstructedWith( 'json' );
  }
  

  // Initializing
  function it_initializes_with_given_format() {
    $this->format()->shouldBe( 'json' );
  }

  function it_initializes_with_status_200() {
    $this->status()->shouldBe( 200 );
  }

  function it_initializes_with_empty_output() {
    $this->beConstructedWith( 'array' );
    $this->toArray()->shouldBeArray();
    $this->toArray()->shouldBe([]);
  }

  function it_initializes_with_empty_headers() {
    $this->headers()->shouldBeArray();
    $this->headers()->shouldBe([]);
  }

  function it_initializes_ok() {
    $this->ok()->shouldBe( true );
  }


  // Add method
  function it_accepts_an_array_for_add() {
    $this->add([ 'an' => 'array' ]);
  }

  function it_returns_itself_after_add() {
    $this->add([ 'an' => 'array' ])->shouldBe( $this );
  }

  function it_does_not_accept_anything_other_than_an_array_for_add() {
    $this->shouldThrow( 'Bulckens\AppTools\OutputArgumentInvalidException' )->duringAdd( 'string' );
    $this->shouldThrow( 'Bulckens\AppTools\OutputArgumentInvalidException' )->duringAdd( null );
    $this->shouldThrow( 'Bulckens\AppTools\OutputArgumentInvalidException' )->duringAdd( 123 );
    $this->shouldNotThrow( 'Bulckens\AppTools\OutputArgumentInvalidException' )->duringAdd([ 'I' => 'can' ]);
  }


  // Clear method
  function it_can_clear_output() {
    $this->beConstructedWith( 'array' );
    $this->add([ 'arbitrary' => 'data' ]);
    $this->toArray()->shouldHaveCount( 1 );
    $this->clear();
    $this->toArray()->shouldHaveCount( 0 );
  }

  function it_can_clear_headers() {
    $this->header( 'Hey', 'DIE!' );
    $this->headers()->shouldHaveCount( 1 );
    $this->clear();
    $this->headers()->shouldHaveCount( 0 );
  }

  function it_returns_itself_after_clear() {
    $this->clear()->shouldBe( $this );
  }


  // Header method
  function it_can_add_a_header() {
    $this->headers()->shouldHaveCount( 0 );
    $this->header( 'Pragma', 'public' );
    $this->headers()->shouldHaveCount( 1 );
  }

  function it_returns_itself_after_setting_header() {
    $this->header( 'Pragma', 'public' )->shouldBe( $this );
  }


  // Headers method
  function it_returns_an_array_of_headers() {
    $this->headers()->shouldBeArray();
  }

  function it_retreives_all_headers() {
    $this->headers()->shouldHaveCount( 0 );
    $this->header( 'Pragma', 'public' );
    $this->header( 'Cache-Control', 'maxage=3600' );
    $this->header( 'Expires', 'never' );
    $this->headers()->shouldHaveCount( 3 );
  }


  // Expires method
  function it_sets_required_expires_headers() {
    $this->headers()->shouldHaveCount( 0 );
    $this->expires( 3600 );
    $this->headers()->shouldHaveCount( 3 );
  }

  function it_returns_itself_after_setting_expiration() {
    $this->expires( 3600 )->shouldBe( $this );
  }

  function it_expires_without_an_argument() {
    $this->expires()->shouldBe( $this );
  }


  // Mime method
  function it_returns_mime_type_for_css_format() {
    $this->beConstructedWith( 'css' );
    $this->mime()->shouldBe( 'text/css' );
  }

  function it_returns_mime_type_for_css_map_format() {
    $this->beConstructedWith( 'map' );
    $this->mime()->shouldBe( 'application/json' );
  }

  function it_returns_mime_type_for_dump_format() {
    $this->beConstructedWith( 'dump' );
    $this->mime()->shouldBe( 'text/plain' );
  }

  function it_returns_mime_type_for_html_format() {
    $this->beConstructedWith( 'html' );
    $this->mime()->shouldBe( 'text/html' );
  }

  function it_returns_mime_type_for_js_format() {
    $this->beConstructedWith( 'js' );
    $this->mime()->shouldBe( 'application/javascript' );
  }

  function it_returns_mime_type_for_json_format() {
    $this->beConstructedWith( 'json' );
    $this->mime()->shouldBe( 'application/json' );
  }

  function it_returns_mime_type_for_txt_format() {
    $this->beConstructedWith( 'txt' );
    $this->mime()->shouldBe( 'text/plain' );
  }

  function it_returns_mime_type_for_xml_format() {
    $this->beConstructedWith( 'xml' );
    $this->mime()->shouldBe( 'application/xml' );
  }

  function it_returns_mime_type_for_yaml_format() {
    $this->beConstructedWith( 'yaml' );
    $this->mime()->shouldBe( 'application/x-yaml' );
  }


  // Status method
  function it_returns_the_default_status() {
    $this->status()->shouldBe( 200 );
  }

  function it_returns_the_given_status() {
    $this->status( 404 );
    $this->status()->shouldBe( 404 );
  }

  function it_sets_the_given_status() {
    $this->status()->shouldBe( 200 );
    $this->status( 500 );
    $this->status()->shouldBe( 500 );
  }

  function it_returns_itself_after_setting_status() {
    $this->status( 418 )->shouldBe( $this );
  }


  // OK method
  function it_is_ok_with_a_status_code_under_400() {
    $this->status( 200 )->ok()->shouldBe( true );
    $this->status( 302 )->ok()->shouldBe( true );
    $this->status( 308 )->ok()->shouldBe( true );
  }

  function it_is_not_ok_with_a_status_code_over_400() {
    $this->status( 400 )->ok()->shouldBe( false );
    $this->status( 404 )->ok()->shouldBe( false );
    $this->status( 500 )->ok()->shouldBe( false );
  }


  // toArray method
  function it_returns_the_output_array() {
    $this->toArray()->shouldBeArray();
    $this->add([ 'fab' => 'ulous' ]);
    $this->toArray()->shouldHaveKey( 'fab' );
  }


  // Is method
  function it_tests_positive_when_the_given_format_is_the_initialized_format() {
    $this->beConstructedWith( 'xml' );
    $this->is( 'xml' )->shouldBe( true );
  }

  function it_tests_negative_when_the_given_format_is_the_initialized_format() {
    $this->beConstructedWith( 'js' );
    $this->is( 'json' )->shouldBe( false );
  }


  // Path method
  function it_gets_the_path() {
    $this->path()->shouldBe( null );
  }

  function it_sets_the_given_path() {
    $this->path( '/floop' );
    $this->path()->shouldBe( '/floop' );
  }


  // Purify method
  function it_purifies_the_output_when_the_status_is_ok() {
    $this->beConstructedWith( 'array' );
    $this->add([
      'error'    => 'bad'
    , 'success'  => 'good'
    , 'details'  => [ 'unimportant' => 'right now' ]
    , 'resource' => 'which is not there'
    ]);
    $this->status( 200 )->purify();
    $this->render()->shouldHaveKey( 'success' );
    $this->render()->shouldHaveKey( 'resource' );
    $this->render()->shouldHaveKey( 'details' );
    $this->render()->shouldNotHaveKey( 'error' );
  }

  function it_ensures_the_output_has_a_success_key_when_none_given() {
    $this->beConstructedWith( 'array' );
    $this->add([
      'error'    => 'bad'
    , 'resource' => 'which is not there'
    ]);
    $this->status( 200 )->purify();
    $this->render()->shouldHaveKey( 'success' );
    $this->render()->shouldHaveKey( 'resource' );
    $this->render()->shouldNotHaveKey( 'error' );
  }

  function it_does_not_overwrite_the_success_key_if_already_defined() {
    $this->beConstructedWith( 'array' );
    $this->add([
      'success' => 'good.good.girl'
    ]);
    $this->status( 200 );
    $this->render()->shouldHaveKeyWithValue( 'success', 'good.good.girl' );
  }

  function it_purifies_the_output_when_the_status_is_not_ok() {
    $this->beConstructedWith( 'array' );
    $this->add([
      'error'    => 'bad'
    , 'success'  => 'good'
    , 'details'  => [ 'unimportant' => 'right now' ]
    , 'body'     => 'bestanding be beloved'
    , 'resource' => 'which is not there'
    ]);
    $this->status( 418 )->purify();
    $this->render()->shouldHaveKey( 'error' );
    $this->render()->shouldHaveKey( 'details' );
    $this->render()->shouldHaveKey( 'body' );
    $this->render()->shouldNotHaveKey( 'success' );
    $this->render()->shouldNotHaveKey( 'resource' );
  }

  function it_does_not_remove_keys_that_are_defined_as_pure() {
    $this->beConstructedWith( 'array' );
    $this->configFile( 'output.pure_keys.yml' );
    $this->add([
      'error'    => 'bad'
    , 'details'  => [ 'unimportant' => 'right now' ]
    , 'pure'     => 'I always need to be here'
    , 'sane'     => 'me too!'
    ]);
    $this->status( 418 )->purify();
    $this->render()->shouldHaveKey( 'error' );
    $this->render()->shouldHaveKey( 'details' );
    $this->render()->shouldHaveKey( 'pure' );
    $this->render()->shouldHaveKey( 'sane' );
  }

  function it_ensures_the_output_has_an_error_key_when_none_given() {
    $this->beConstructedWith( 'array' );
    $this->add([
      'success'  => 'good'
    , 'resource' => 'which is not there'
    ]);
    $this->status( 418 )->purify();
    $this->render()->shouldHaveKey( 'error' );
    $this->render()->shouldNotHaveKey( 'success' );
    $this->render()->shouldNotHaveKey( 'resource' );
  }

  function it_does_not_overwrite_the_error_key_if_already_defined() {
    $this->beConstructedWith( 'array' );
    $this->add([
      'error' => 'bad.bad.girl'
    ]);
    $this->status( 418 );
    $this->render()->shouldHaveKeyWithValue( 'error', 'bad.bad.girl' );
  }


  // Render method
  function it_renders_the_output_as_json() {
    $this->beConstructedWith( 'json' );
    $this->add([ 'candy' => [ 'ken' => 'pink' ] ]);
    $this->render()->shouldBe( '{"candy":{"ken":"pink"},"success":"status.200"}' );
  }

  function it_renders_the_output_as_yaml() {
    $this->beConstructedWith( 'yaml' );
    $this->add([ 'candy' => [ 'ken' => 'pink' ] ]);
    $this->render()->shouldBe( "candy:\n  ken: pink\nsuccess: status.200\n" );
  }

  function it_renders_the_output_as_xml() {
    $this->beConstructedWith( 'xml' );
    $this->add([ 'candy' => [ 'ken' => 'pink' ] ]);
    $this->render()->shouldBe( "<?xml version=\"1.0\"?>\n<root><candy><ken>pink</ken></candy><success>status.200</success></root>\n" );
  }

  function it_renders_the_output_as_dump() {
    $this->beConstructedWith( 'dump' );
    $this->add([ 'candy' => [ 'ken' => 'pink' ] ]);
    $this->render()->shouldStartWith( "Array\n(\n    [candy] => Array\n        (\n            [ken] => pink" );
  }

  function it_renders_the_output_as_array() {
    $this->beConstructedWith( 'array' );
    $this->add([ 'candy' => [ 'ken' => 'pink' ] ]);
    $this->render()->shouldBe( [ 'candy' => [ 'ken' => 'pink' ], 'success' => 'status.200' ] );
  }

  function it_renders_the_output_as_html() {
    $this->beConstructedWith( 'html' );
    $this->configFile( 'output.verbose.yml' );
    $this->add([ 'candy' => [ 'ken' => 'pink' ] ]);
    $this->render()->shouldStartWith( "<!--\nArray" );
  }

  function it_renders_the_output_as_txt() {
    $this->beConstructedWith( 'txt' );
    $this->configFile( 'output.verbose.yml' );
    $this->add([ 'candy' => [ 'ken' => 'pink' ] ]);
    $this->render()->shouldStartWith( "Array\n" );
  }

  function it_renders_the_output_as_css() {
    $this->beConstructedWith( 'css' );
    $this->configFile( 'output.verbose.yml' );
    $this->add([ 'candy' => [ 'ken' => 'pink' ] ]);
    $this->render()->shouldStartWith( "/*\nArray" );
  }

  function it_renders_the_output_as_css_map() {
    $this->beConstructedWith( 'map' );
    $this->configFile( 'output.verbose.yml' );
    $this->add([ 'body' => '{"source":"map"}' ]);
    $this->render()->shouldStartWith( '{"source":"map"}' );
  }

  function it_renders_the_output_as_js() {
    $this->beConstructedWith( 'js' );
    $this->configFile( 'output.verbose.yml' );
    $this->add([ 'candy' => [ 'ken' => 'pink' ] ]);
    $this->render()->shouldStartWith( "/*\nArray" );
  }

  function it_does_not_accept_any_other_formats() {
    $this->beConstructedWith( 'png' );
    $this->add([ 'candy' => [ 'ken' => 'pink' ] ]);
    $this->shouldThrow( 'Bulckens\AppTools\OutputFormatUnknownException' )->duringRender();
  }

  function it_uses_the_alternative_render_method() {
    $this->beConstructedWith( 'html' );
    $this->configFile( 'output.render.yml' );
    $this->add([ 'candy' => [ 'ken' => 'pink' ] ]);
    $this->render()->shouldBe( '<html><head><title></title></head><body>Rendered from the outside!</body></html>' );
  }

  function it_fails_if_the_defined_render_method_is_not_callable() {
    $this->beConstructedWith( 'html' );
    $this->configFile( 'output.render_fail.yml' );
    $this->add([ 'candy' => [ 'ken' => 'pink' ] ]);
    $this->shouldThrow( 'Bulckens\AppTools\OutputRenderMethodNotCallableException' )->duringRender();
  }

  function it_only_outputs_an_error_when_the_status_is_not_ok() {
    $this->add([ 'fine' => 'young canibals' ])->status( 418 );
    $this->render()->shouldBe( '{"error":"status.418"}' );
  }

}
