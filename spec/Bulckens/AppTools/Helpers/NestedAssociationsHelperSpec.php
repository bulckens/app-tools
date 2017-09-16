<?php

namespace spec\Bulckens\AppTools\Helpers;

use Bulckens\AppTools\App;
use Bulckens\AppTools\Helpers\NestedAssociationsHelper;
use Bulckens\AppTests\TestModelWithNestedAssociations;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NestedAssociationsHelperSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev' );
    $app->run();
  }

  // HasOne static method
  function it_detects_a_has_one_relationship() {
    $model = new TestModelWithNestedAssociations();
    $this::hasOne( $model->sibling() )->shouldBe( true );
  }

  function it_detects_a_morph_one_relationship() {
    $model = new TestModelWithNestedAssociations();
    $this::hasOne( $model->friend() )->shouldBe( true );
  }

  function it_rejects_another_relationhsip_than_one() {
    $model = new TestModelWithNestedAssociations();
    $this::hasOne( $model->children() )->shouldBe( false );
  }


  // HasOne static method
  function it_detects_a_has_many_relationship() {
    $model = new TestModelWithNestedAssociations();
    $this::hasMany( $model->children() )->shouldBe( true );
  }

  function it_detects_a_morph_many_relationship() {
    $model = new TestModelWithNestedAssociations();
    $this::hasMany( $model->friends() )->shouldBe( true );
  }

  function it_rejects_another_relationhsip_than_many() {
    $model = new TestModelWithNestedAssociations();
    $this::hasMany( $model->friend() )->shouldBe( false );
  }

}
