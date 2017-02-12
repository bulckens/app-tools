<?php

namespace spec\Bulckens\AppTools\Notifier;

use Exception;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Notifier\Notification;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NotificationSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev' );
    $this->beConstructedWith( new Exception( 'Do you care?' ), $app->notifier() );
  }

  function it_is_initializable() {
    $this->shouldHaveType( Notification::class );
  }

}
