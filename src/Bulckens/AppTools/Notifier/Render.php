<?php

namespace Bulckens\AppTools\Notifier;

use Bulckens\Helpers\ArrayHelper;
use Bulckens\CliTools\Style;
use Bulckens\AppTools\View;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Notifier;

class Render {

  protected $view;
  protected $exception;
  protected $theme;
  protected $default_theme = [
    'body'     => '#f2f2f2'
  , 'item'     => '#ffffff'
  , 'link'     => '#0db0ff'
  , 'text'     => '#4d4d4d'
  , 'title'    => '#b2b2b2'
  , 'class'    => '#4ab0b7'
  , 'function' => '#75bf78'
  , 'line'     => '#b8cd3f'
  ];

  public function __construct( $exception, $notifier ) {
    $this->exception = $exception;

    // initialize new view
    $this->view = new View( false );
    $dir = $notifier ? $notifier->config()->get( 'views' ) : null;
    $this->view->root( $dir ?: ( __DIR__ . '/Views' ) );

    // store theme
    $theme = $notifier->config()->get( 'theme' );
    $this->theme = $theme ?: [];
  }


  // Render as html error
  public function html( $subject, $data = [] ) {
    return $this->view->render( 'exception.html.twig', [
      'error'   => $this->exception->getMessage()
    , 'env'     => App::env()
    , 'subject' => $subject
    , 'trace'   => $this->exception->getTrace()
    , 'theme'   => $this->theme()
    , 'server'  => empty( $_SERVER )  ? null : ArrayHelper::pretty( $_SERVER )
    , 'session' => empty( $_SESSION ) ? null : ArrayHelper::pretty( $_SESSION )
    , 'post'    => empty( $_POST )    ? null : ArrayHelper::pretty( $_POST )
    , 'get'     => empty( $_GET )     ? null : ArrayHelper::pretty( $_GET )
    , 'data'    => empty( $data )     ? null : ArrayHelper::pretty( $data )
    ]);
  }


  // Theme color values with default
  public function theme() {
    return array_replace( $this->default_theme, $this->theme );
  }


  // Render as cli error
  public function cli( $subject, $data = [] ) {
    // header
    $message  = Style::end( Style::grey( "$subject [" . App::env() . "]" ) );
    $message .= Style::end( Style::red( $this->exception->getMessage() ) );
    $message .= Style::end( '  ' );

    // occurrences
    $message .= Style::end( Style::grey( 'OCCURENCES' ) );
    $message .= Style::end( '  ' );

    foreach ( $this->exception->getTrace() as $key => $value ) {
      if ( isset( $value['file'] ) ) {
        if ( isset( $value['class'] ) ) {
          $message .= Style::green( $value['class'] );
          $message .= Style::grey( $value['type'] );
          $message .= Style::end( Style::purple( $value['function'] ) );
        }
        
        $message .= Style::yellow( $value['file'] );
        $message .= Style::grey( ': ' );
        $message .= Style::end( Style::purple( $value['line'] ) );
        $message .= Style::end( '  ' );
      }
    }

    // additional data
    if ( ! empty( $data ) ) {
      $message .= Style::end( Style::grey( 'ADDITIONAL DATA' ) );
      $message .= Style::end( '  ' );

      foreach ( $data as $key => $value ) {
        $message .= Style::yellow( $key );
        $message .= Style::end( Style::grey( ': ' ), false );
        $message .= Style::end( $value );
        $message .= Style::end( '  ' );
      }
    }


    return $message;
  }


}
