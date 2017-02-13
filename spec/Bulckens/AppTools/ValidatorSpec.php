<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\Validator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ValidatorSpec extends ObjectBehavior {
  
  function let() {
    $this->beConstructedWith([ 'required' => true ]);
  }


  // Data method
  function it_sets_and_gets_arbitrary_data_for_validation() {
    $this->data([ 'more' => 'info' ]);
    $this->data()->shouldHaveKeyWithValue( 'more', 'info' );
  }

  function it_returns_data_as_an_array() {
    $this->data()->shouldBeArray();
  }

  function it_sets_data_and_returns_itself() {
    $this->data([ 'more' => 'info' ])->shouldBe( $this );
  }

}