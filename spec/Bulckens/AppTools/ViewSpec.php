<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\App;
use Bulckens\AppTools\View;
use Bulckens\AppTools\View\UrlExtension;
use Bulckens\AppTools\View\StringExtension;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ViewSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev' );
    $app->run();
  }

  function letGo() {
    $this->configFile( 'view.yml' );
    $this->reset();
  }

  // Initialization
  function it_runs_on_initialization() {
    $this->configFile( 'non_existent.yml' );
    $this->shouldThrow( 'Bulckens\AppTools\ConfigFileMissingException' )->duringRun();
  }

  function it_can_be_initialized_without_calling_run() {
    $this->configFile( 'non_existent.yml' );
    $this->shouldNotThrow( 'Bulckens\AppTools\ConfigFileMissingException' )->during__Construct( false );
  }


  // Root method
  function it_returns_nothing_if_no_root_is_set() {
    $this->root()->shouldBe( null );
  }

  function it_sets_and_gets_a_view_root_directory() {
    $this->root( __DIR__ );
    $this->root()->shouldBe( __DIR__ );
  }

  function it_sets_the_root_and_returns_itself() {
    $this->root( __DIR__ )->shouldBe( $this );
  }


  // Run method
  function it_fails_when_no_view_root_is_defined() {
    $this->configFile( 'view_fail.yml' );
    $this->shouldThrow( 'Bulckens\AppTools\ViewRootNotDefinedException' )->duringRun();
  }

  function it_fails_not_with_a_custom_root_but_no_root_defened_in_the_config() {
    $this->configFile( 'view_fail.yml' )->root( __DIR__ );
    $this->shouldNotThrow( 'Bulckens\AppTools\ViewRootNotDefinedException' )->duringRun();
  }

  function it_returns_itself_after_initializing() {
    $this->run()->shouldBe( $this );
  }

  function it_fails_when_the_given_view_root_does_not_exist() {
    $this->configFile( 'view_fail.yml' )->root( '/get/out/of/here' );
    $this->shouldThrow( 'Bulckens\AppTools\ViewRootMissingException' )->duringRun();
  }


  // Render method
  function it_renders_a_given_view() {
    $this->render( 'my/valentine.html.twig' )->shouldContain( 'You\'ve could' );
    $this->render( 'my/valentine.html.twig' )->shouldContain( '<strong>right</strong>' );
  }

  function it_includes_information_about_the_app_environment() {
    $this->render( 'app.html.twig' )->shouldContain( 'env: <i>dev</i>' );
  }

  function it_includes_the_given_locales() {
    $this->render( 'app.html.twig', [ 'ru' => 'paul' ] )->shouldContain( 'ru:  <i>paul</i>' );
  }

  function it_renders_a_string_template_with_the_given_locales() {
    $this->render( 'My name is not {{ ru }}!', [ 'ru' => 'paul' ] )->shouldBe( 'My name is not paul!' );
  }


  // Functions method
  function it_adds_custom_functions_to_the_view_render_engine() {
    $this->functions([ 'hello' => function() { echo 'world'; } ]);
    $this->render( 'functions.html.twig' )->shouldContain( 'Hello <b>world</b>' );
  }

  function it_adds_custom_functions_to_the_text_render_engine() {
    $this->functions([ 'hello' => function() { echo 'world'; } ]);
    $this->render( 'Hello <b>{{ hello() }}</b>' )->shouldContain( 'Hello <b>world</b>' );
  }

  function it_fails_with_a_view_when_a_custom_function_is_not_defined() {
    $this->shouldThrow( 'Twig_Error_Syntax' )->duringRender( 'functions_fail.html.twig' );
  }

  function it_fails_with_a_string_when_a_custom_function_is_not_defined() {
    $this->shouldThrow( 'Twig_Error_Syntax' )->duringRender( 'Hello <b>{{ ho() }}</b>' );
  }


  // Filters method
  function it_adds_custom_filters_to_the_view_render_engine() {
    $this->filters([ 'emphasize' => function( $subject ) { return "<em>$subject</em>"; } ]);
    $this->render( 'filters.html.twig' )->shouldContain( 'Hello <em>world</em>' );
  }

  function it_adds_custom_filters_to_the_text_render_engine() {
    $this->filters([ 'italics' => function( $subject ) { return "<i>$subject</i>"; } ]);
    $this->render( "Hello {{ 'world'|italics }}" )->shouldContain( 'Hello <i>world</i>' );
  }

  function it_fails_with_a_view_when_a_custom_filter_is_not_defined() {
    $this->shouldThrow( 'Twig_Error_Syntax' )->duringRender( 'filters_fail.html.twig' );
  }

  function it_fails_with_a_string_when_a_custom_filter_is_not_defined() {
    $this->shouldThrow( 'Twig_Error_Syntax' )->duringRender( "Hello {{ 'world'|emphasize }}" );
  }


  // Extensions method
  function it_accepts_the_url_extension_to_the_view_render_engine() {
    $this->extension( new UrlExtension() );
    $this->render( 'extensions.html.twig' )->shouldContain( 'Given path: /' );
  }

  function it_accepts_the_url_extension_to_the_text_render_engine() {
    $this->extension( new UrlExtension() );
    $this->render( "Is root: {{ root_path( '/' ) }}" )->shouldContain( 'Is root: 1' );
  }

  function it_accepts_the_singular_extension_to_the_render_engines() {
    $this->extension( new StringExtension() );
    $this->render( "This {{ 'entities'|singular }}" )->shouldContain( 'This entity' );
  }

  function it_accepts_the_plural_extension_to_the_render_engines() {
    $this->extension( new StringExtension() );
    $this->render( "These {{ 'entity'|plural }}" )->shouldContain( 'These entities' );
  }

  function it_accepts_the_pluralize_extension_to_the_render_engines() {
    $this->extension( new StringExtension() );
    $this->render( "Subject: {{ 'entity'|pluralize(1) }}" )->shouldContain( 'Subject: entity' );
    $this->render( "Subject: {{ 'entity'|pluralize(10) }}" )->shouldContain( 'Subject: entities' );
  }


  // Config method
  function it_returns_the_config_instance_without_an_argument() {
    $this->config()->shouldHaveType( 'Bulckens\AppTools\Config' );
  }

  function it_returns_the_the_value_for_a_given_key() {
    $this->config( 'debug' )->shouldBe( true );
  }

  function it_returns_a_given_default_value_if_key_is_not_existing() {
    $this->config( 'true', false )->shouldBe( false );
  }


  // File method
  function it_builds_config_file_name_from_class() {
    $this->configFile()->shouldBe( 'view.yml' );
  }

  function it_defines_a_custom_config_file() {
    $this->configFile( 'view_custom.yml' );
    $this->configFile()->shouldBe( 'view_custom.yml' );
    $this->config( 'debug' )->shouldBe( false );
  }

  function it_unsets_the_custom_config_file_with_null_given() {
    $this->configFile( 'view_custom.yml' );
    $this->configFile()->shouldBe( 'view_custom.yml' );
    $this->configFile( null );
    $this->configFile()->shouldBe( 'view.yml' );
  }

  function it_returns_itself_after_defining_a_custom_config_file() {
    $this->configFile( 'view_custom.yml' )->shouldBe( $this );
  }


  // Reset method
  function it_returns_itself_after_resetting() {
    $this->reset()->shouldBe( $this );
  }

  function it_clears_the_custom_root() {
    $this->root( __DIR__ );
    $this->reset()->root()->shouldBe( null );
  }


  // Engine method
  function it_returns_the_render_engine() {
    $this->engine()->shouldHaveType( 'Twig_Environment' );
  }

}
