<?php

namespace spec\Bulckens\AppTools\Notifier;

use Bulckens\AppTools\Notifier\Notification;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NotificationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Notification::class);
    }
}
