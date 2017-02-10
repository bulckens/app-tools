<?php

namespace spec\Bulckens\AppTools\Notifier;

use Bulckens\AppTools\Notifier\ErrorHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErrorHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ErrorHandler::class);
    }
}
