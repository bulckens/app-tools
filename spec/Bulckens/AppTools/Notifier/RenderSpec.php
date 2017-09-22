<?php

namespace spec\Bulckens\AppTools\Notifier;

use Exception;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Config;
use Bulckens\AppTools\Notifier\Render;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RenderSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev' );
    $app->run();
    $this->beConstructedWith( new Exception( 'Fal che ou' ), new Config( 'dev' ) );
  }
  

  // Cli method
  function it_renders_the_given_exception_for_cli_with_given_subject() {
    $this->cli( 'Manastaba' )->shouldContain( 'Manastaba [dev]' );
  }

  function it_renders_custom_data_for_cli() {
    $text = $this->cli( 'AbFab', [ 'absolutely' => 'fabulous' ]);
    $text->shouldContain( 'ADDITIONAL DATA' );
    $text->shouldContain( 'absolutely' );
    $text->shouldContain( 'fabulous' );
  }


  // Html method
  function it_renders_the_given_exception_as_html_with_given_subject() {
    $this->html( 'Manastaba' )->shouldContain( 'Manastaba [dev]</h2>' );
  }

  function it_renders_custom_data_as_html() {
    $html = $this->html( 'AbFab', [ 'absolutely' => 'fabulous' ]);
    $html->shouldContain( '<h2 style="margin-bottom:5px;font-weight:normal;color:#b2b2b2;">ADDITIONAL DATA</h2>' );
    $html->shouldContain( 'absolutely' );
    $html->shouldContain( 'fabulous' );
  }

  function it_renders_with_default_theme_colors() {
    $html = $this->html( 'Default theme' );
    $html->shouldContain( 'background-color:#f2f2f2;" link=' );
    $html->shouldContain( 'div style="background-color:#ffffff;' );
    $html->shouldContain( 'link="#0db0ff" alink="#0db0ff" vlink="#0db0ff"' );
    $html->shouldContain( 'span style="color:#4d4d4d;"' );
    $html->shouldContain( 'text-transform:uppercase;color:#b2b2b2;"' );
    $html->shouldContain( 'span style="color:#4ab0b7;"' );
    $html->shouldContain( 'span style="color:#75bf78;"' );
    $html->shouldContain( 'span style="color:#b8cd3f;"' );
  }

  function it_renders_with_custom_theme_colors() {
    $notifier = App::get()->notifier();
    $notifier->configFile( 'notifier_full_theme.yml' );
    $this->beConstructedWith( new Exception( 'Full theme' ), $notifier->config() );

    $html = $this->html( 'Full theme' );
    $html->shouldContain( 'background-color:#aaaaaa;" link=' );
    $html->shouldContain( 'div style="background-color:#bbbbbb;' );
    $html->shouldContain( 'link="#cccccc" alink="#cccccc" vlink="#cccccc"' );
    $html->shouldContain( 'span style="color:#dddddd;"' );
    $html->shouldContain( 'text-transform:uppercase;color:#eeeeee;"' );
    $html->shouldContain( 'span style="color:#ffffff;"' );
    $html->shouldContain( 'span style="color:#999999;"' );
    $html->shouldContain( 'span style="color:#888888;"' );
  }


  // Theme method
  function it_returns_the_default_theme_when_no_custom_theme_is_configured() {
    $notifier = App::get()->notifier();
    $notifier->configFile( 'notifier_themeless.yml' );
    $this->beConstructedWith( new Exception( 'Partial theme' ), $notifier->config() );

    $theme = $this->theme();
    $theme['body']->shouldBe( '#f2f2f2' );
    $theme['item']->shouldBe( '#ffffff' );
    $theme['link']->shouldBe( '#0db0ff' );
    $theme['text']->shouldBe( '#4d4d4d' );
    $theme['title']->shouldBe( '#b2b2b2' );
    $theme['class']->shouldBe( '#4ab0b7' );
    $theme['function']->shouldBe( '#75bf78' );
    $theme['line']->shouldBe( '#b8cd3f' );
  }

  function it_uses_the_default_theme_values_as_fallback() {
    $notifier = App::get()->notifier();
    $notifier->configFile( 'notifier_partial_theme.yml' );
    $this->beConstructedWith( new Exception( 'Partial theme' ), $notifier->config() );

    $theme = $this->theme();
    $theme['body']->shouldBe( '#123456' );
    $theme['item']->shouldBe( '#ffffff' );
    $theme['link']->shouldBe( '#654321' );
    $theme['text']->shouldBe( '#4d4d4d' );
    $theme['title']->shouldBe( '#b2b2b2' );
    $theme['class']->shouldBe( '#4ab0b7' );
    $theme['function']->shouldBe( '#75bf78' );
    $theme['line']->shouldBe( '#b8cd3f' );
  }

  function it_uses_the_complete_custom_theme() {
    $notifier = App::get()->notifier();
    $notifier->configFile( 'notifier_full_theme.yml' );
    $this->beConstructedWith( new Exception( 'Full theme' ), $notifier->config() );

    $theme = $this->theme();
    $theme['body']->shouldBe( '#aaaaaa' );
    $theme['item']->shouldBe( '#bbbbbb' );
    $theme['link']->shouldBe( '#cccccc' );
    $theme['text']->shouldBe( '#dddddd' );
    $theme['title']->shouldBe( '#eeeeee' );
    $theme['class']->shouldBe( '#ffffff' );
    $theme['function']->shouldBe( '#999999' );
    $theme['line']->shouldBe( '#888888' );
  }

}
