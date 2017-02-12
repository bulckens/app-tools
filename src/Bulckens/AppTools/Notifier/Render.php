<?php

namespace Bulckens\AppTools\Notifier;

use Bulckens\Helpers\ArrayHelper;
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

  public function __construct( $exception, $config ) {
    $this->exception = $exception;

    // initialize new view
    $this->view = new View( false );
    $this->view->root( $config->get( 'views' ) ?: ( __DIR__ . '/Views' ) );

    // store theme
    $this->theme = $config->get( 'theme' ) ?: [];
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


}
