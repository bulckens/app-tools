<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTools\App;
use Bulckens\AppTests\TestModelWithValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestModelWithValidatorSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev' );
    $app->run();
  }

  // Children relation
  function it_has_many_children() {
    $this->children()->shouldHaveType( 'Illuminate\Database\Eloquent\Relations\HasMany' );
  }
  
  
  // Rules method
  function it_is_valid_when_a_name_is_given() {
    $this->beConstructedWith([ 'name' => 'lovely' ]);
    $this->isValid()->shouldBe( true );
  }

  function it_is_invalid_when_no_name_is_given() {
    $this->beConstructedWith();
    $this->isValid()->shouldBe( false );
  }

}
