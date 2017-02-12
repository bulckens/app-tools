<?php

namespace spec\Bulckens\AppTools\Notifier;

use Bulckens\AppTools\Notifier\Error;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErrorSpec extends ObjectBehavior {

  function let() {
    $this->beConstructedWith( 'Aiai', debug_backtrace() );
  }

  // function it_is_initializable() {
  //   $this->shouldHaveType(Error::class);
  // }

}
