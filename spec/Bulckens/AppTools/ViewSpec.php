<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\View;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ViewSpec extends ObjectBehavior {

  function letGo() {
    $this->file( null );
  }


  // Initialization
  function it_fails_when_no_view_root_is_defined() {
    $this->file( 'view_fail.yml' );
    $this->shouldThrow( 'Bulckens\AppTools\ViewRootNotDefinedException' )->during__Construct();
  }


  // Render method
  function it_renders_a_given_view() {
    $this->render( 'my/valentine.html.twig' )->shouldContain( 'You\'ve could' );
    $this->render( 'my/valentine.html.twig' )->shouldContain( '<strong>right</strong>' );
  }

  function it_includes_information_about_the_app_environment() {
    $this->render( 'app.html.twig' )->shouldContain( 'env: <i>test</i>' );
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

  
  // Config method
  function it_returns_the_config_instance_without_an_argument() {
    $this->config()->shouldHaveType( 'Bulckens\AppTools\Config' );
  }

  function it_returns_the_the_value_for_a_given_key() {
    $this->config( 'debug' )->shouldBe( true );
  }


  // File method
  function it_builds_config_file_name_from_class() {
    $this->file()->shouldBe( 'view.yml' );
  }

  function it_defines_a_custom_config_file() {
    $this->file( 'view_custom.yml' );
    $this->file()->shouldBe( 'view_custom.yml' );
    $this->config( 'debug' )->shouldBe( false );
  }

  function it_unsets_the_custom_config_file_with_null_given() {
    $this->file( 'view_custom.yml' );
    $this->file()->shouldBe( 'view_custom.yml' );
    $this->file( null );
    $this->file()->shouldBe( 'view.yml' );
  }

  function it_returns_itself_after_defining_a_custom_config_file() {
    $this->file( 'view_custom.yml' )->shouldBe( $this );
  }

}
