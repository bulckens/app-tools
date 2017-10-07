<?php

namespace Bulckens\AppTools\Notifier;

use Monolog\Logger;
use Monolog\Handler\SwiftMailerHandler;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;
use Bulckens\AppTools\App;

class Notification {

  public function __construct( $exception, $notifier ) {
    // gather variables
    $env        = App::env();
    $config     = $notifier->config();
    $data       = $notifier->data();
    $title      = $exception->getMessage();
    $smtp       = $config->get( 'smtp' );
    $port       = $config->get( 'port' );
    $from       = $config->get( 'from' );
    $email      = $config->get( 'email' );
    $subject    = $config->get( 'subject' );
    $username   = $config->get( 'username' );
    $password   = $config->get( 'password' );
    $encryption = $config->get( 'encryption' );

    // prepare mailer
    $transport = Swift_SmtpTransport::newInstance( $smtp, $port )
                  ->setUsername( $username )
                  ->setPassword( $password );

    if ( ! empty( $encryption ) )
      $transport = $transport->setEncryption( $encryption );

    $mailer = Swift_Mailer::newInstance( $transport );

    // prepare message
    $render = new Render( $exception, $notifier );
    $html   = $render->html( $subject, $data );

    $message = Swift_Message::newInstance( "$subject [$env] $title" )
      ->setFrom([ $from ])
      ->setTo([ $email ])
      ->setBody( $html, 'text/html' );
    
    // initialize notifier
    $handler = new SwiftMailerHandler( $mailer, $message );
    $mailer  = new Logger( 'notifier_mailer' );
    $handler->setFormatter( new MonologPrintRLineFormatter );
    $mailer->pushHandler( $handler );

    // send mail
    $mailer->error( $html );
  }

}