<?php

namespace Bulckens\AppTools\Notifier;

use Monolog\Logger;
use Monolog\Handler\SwiftMailerHandler;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

class Notification {

  public function __construct( $exception ) {
    // gather variables
    $title      = $exception->getMessage();
    $env        = Notifier::env();
    $smtp       = Notifier::config( 'smtp' );
    $port       = Notifier::config( 'port' );
    $from       = Notifier::config( 'from' );
    $email      = Notifier::config( 'email' );
    $subject    = Notifier::config( 'subject' );
    $username   = Notifier::config( 'username' );
    $password   = Notifier::config( 'password' );
    $encryption = Notifier::config( 'encryption' );

    // prepare mailer
    $transport = Swift_SmtpTransport::newInstance( $smtp, $port )
                  ->setUsername( $username )
                  ->setPassword( $password );

    if ( ! empty( $encryption ) )
      $transport = $transport->setEncryption( $encryption );

    $mailer = Swift_Mailer::newInstance( $transport );

    // prepare message
    $html = View::error( $exception );

    $message = Swift_Message::newInstance( "$subject [$env] $title" )
                ->setFrom([ $from ])
                ->setTo([ $email ])
                ->setBody( $html, 'text/html' );
    
    // initialize notifier
    $handler  = new SwiftMailerHandler( $mailer, $message );
    $notifier = new Logger( 'notifier_mailer' );
    $handler->setFormatter( new MonologPrintRLineFormatter );
    $notifier->pushHandler( $handler );

    // send mail
    $notifier->error( $html );
  }

}