<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTools\App;
use Bulckens\AppTests\TestModel;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestModelSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev' );
    $app->run();
  }


  // Children relation
  function it_has_many_children() {
    $this->children()->shouldHaveType( 'Illuminate\Database\Eloquent\Relations\HasMany' );
  }

  // Parent relation
  function it_belongs_to_a_parent() {
    $this->parent()->shouldHaveType( 'Illuminate\Database\Eloquent\Relations\BelongsTo' );
  }
  
}
