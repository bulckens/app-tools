<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTools\App;
use Bulckens\AppTests\TestModelWithNestedAssociations;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestModelWithNestedAssociationsSpec extends ObjectBehavior {
  
  function let() {
    $app = new App( 'dev' );
    $app->run();
  }

  function letGo() {
    TestModelWithNestedAssociations::truncate();
  }


  // Children relation
  function it_has_many_children() {
    $this->children()->shouldHaveType( 'Illuminate\Database\Eloquent\Relations\HasMany' );
  }

  // Spouse relation
  function it_has_one_sibling() {
    $this->sibling()->shouldHaveType( 'Illuminate\Database\Eloquent\Relations\HasOne' );
  }


  // Nested associations setter
  function it_fails_when_an_unregistered_nested_association_is_provided() {
    $this->shouldThrow( 'Bulckens\AppTools\Traits\NestedAssociationsNotAllowedException' )->during__construct([
      'name' => 'I am valid'
    , 'nested_associations' => [
        'parents' => [
          [ 'group' => 'beast' ]
        ]
      ]
    ]);
  }


  // Validation of nested asociations
  function it_is_invalid_if_multiple_nested_associations_is_invalid() {
    $this->beConstructedWith([
      'name' => 'I am valid'
    , 'nested_associations' => [
        'children' => [
          [ 'group' => 'beast' ]
        , [ 'group' => 'feast' ]
        ]
      ]
    ]);
    $this->isValid()->shouldBe( false );
  }

  function it_is_invalid_if_a_single_nested_association_is_invalid() {
    $this->beConstructedWith([
      'name' => 'I am valid'
    , 'nested_associations' => [
        'sibling' => [ 'group' => 'beast' ]
      ]
    ]);
    $this->isValid()->shouldBe( false );
  }


  // Creating relations
  function it_attaches_many_child_associations_to_the_parent() {
    $this->beConstructedWith([
      'name' => 'I have many children'
    , 'nested_associations' => [
        'children' => [
          [ 'name' => 'beast' ]
        , [ 'name' => 'chick' ]
        , [ 'name' => 'plumb' ]
        ]
      ]
    ]);
    $this->save();
    $children = $this->children()->get();
    $children->count()->shouldBe( 3 );
    $children->get( 0 )->name->shouldBe( 'beast' );
    $children->get( 1 )->name->shouldBe( 'chick' );
    $children->get( 2 )->name->shouldBe( 'plumb' );
  }

  function it_attaches_a_sibling_association_to_the_parent() {
    $this->beConstructedWith([
      'name' => 'I have one sibling'
    , 'nested_associations' => [
        'sibling' => [ 'name' => 'darling' ]
      ]
    ]);
    $this->save();
    $this->sibling->name->shouldBe( 'darling' );
  }


  // Updating relations
  function it_updates_many_child_associations() {
    $this->beConstructedWith([
      'name' => 'I have many children'
    , 'nested_associations' => [
        'children' => [
          [ 'name' => 'bee' ]
        , [ 'name' => 'dee' ]
        , [ 'name' => 'lee' ]
        ]
      ]
    ]);
    $this->save();

    $this->fill([
      'nested_associations' => [
        'children' => [
          [ 'id' => $this->children->get( 0 )->id, 'name' => 'baa' ]
        , [ 'id' => $this->children->get( 1 )->id, 'name' => 'daa' ]
        , [ 'id' => $this->children->get( 2 )->id, 'name' => 'laa' ]
        ]
      ]
    ]);
    $this->save();

    $this->children->count()->shouldBe( 3 );
    $this->children->get( 0 )->name->shouldBe( 'baa' );
    $this->children->get( 1 )->name->shouldBe( 'daa' );
    $this->children->get( 2 )->name->shouldBe( 'laa' );
  }

  function it_updates_itself_and_a_few_child_associations() {
    $this->beConstructedWith([
      'name' => 'I have many children'
    , 'nested_associations' => [
        'children' => [
          [ 'name' => 'bee' ]
        , [ 'name' => 'dee' ]
        , [ 'name' => 'lee' ]
        ]
      ]
    ]);
    $this->save();

    $this->fill([
      'name' => 'I updated myself and a few children'
    , 'nested_associations' => [
        'children' => [
          [ 'id' => $this->children->get( 0 )->id, 'name' => 'boo' ]
        , [ 'id' => $this->children->get( 2 )->id, 'name' => 'loo' ]
        ]
      ]
    ]);
    $this->save();

    $this->children->count()->shouldBe( 3 );
    $this->children->get( 0 )->name->shouldBe( 'boo' );
    $this->children->get( 1 )->name->shouldBe( 'dee' );
    $this->children->get( 2 )->name->shouldBe( 'loo' );
  }

  function it_updates_and_creates_child_associations() {
    $this->beConstructedWith([
      'name' => 'I have many children'
    , 'nested_associations' => [
        'children' => [
          [ 'name' => 'bii' ]
        , [ 'name' => 'dii' ]
        , [ 'name' => 'lii' ]
        ]
      ]
    ]);
    $this->save();

    $this->fill([
      'nested_associations' => [
        'children' => [
          [ 'id' => $this->children->get( 0 )->id, 'name' => 'buu' ]
        , [ 'id' => $this->children->get( 2 )->id, 'name' => 'luu' ]
        , [ 'name' => 'rii' ]
        ]
      ]
    ]);
    $this->save();

    $this->children->count()->shouldBe( 4 );
    $this->children->get( 0 )->name->shouldBe( 'buu' );
    $this->children->get( 1 )->name->shouldBe( 'dii' );
    $this->children->get( 2 )->name->shouldBe( 'luu' );
    $this->children->get( 3 )->name->shouldBe( 'rii' );
  }

  function it_creates_updates_and_deletes_child_associations() {
    $this->beConstructedWith([
      'name' => 'I have many children'
    , 'nested_associations' => [
        'children' => [
          [ 'name' => 'biin' ]
        , [ 'name' => 'diin' ]
        , [ 'name' => 'liin' ]
        ]
      ]
    ]);
    $this->save();

    $this->fill([
      'nested_associations' => [
        'children' => [
          [ 'id' => $this->children->get( 0 )->id, 'name' => 'buun' ]
        , [ 'id' => $this->children->get( 1 )->id, '_delete' => '1' ]
        , [ 'name' => 'riin' ]
        ]
      ]
    ]);
    $this->save();

    $this->children->count()->shouldBe( 3 );
    $this->children->get( 0 )->name->shouldBe( 'buun' );
    $this->children->get( 1 )->name->shouldBe( 'liin' );
    $this->children->get( 2 )->name->shouldBe( 'riin' );
  }

  function it_fails_to_update_a_child_with_a_non_existant_id() {
    $this->shouldThrow( 'Bulckens\AppTools\Traits\NestedAssociationRecordNotFoundException' )->duringCreate([
      'name' => 'I fail to have children'
    , 'nested_associations' => [
        'children' => [
          [ 'name' => 'bqqn' ]
        , [ 'name' => 'dqqn', 'id' => 123 ]
        ]
      ]
    ]);
  }

  function it_fails_to_update_a_child_from_another_parent() {
    $other = new TestModelWithNestedAssociations([
      'name' => 'I am another parent'
    , 'nested_associations' => [
        'children' => [
          [ 'name' => 'lalalaland' ]
        ]
      ]
    ]);
    $other->save();

    $this->shouldThrow( 'Bulckens\AppTools\Traits\NestedAssociationRecordNotFoundException' )->duringCreate([
      'name' => 'I fail to have children'
    , 'nested_associations' => [
        'children' => [
          [ 'name' => 'bqqn' ]
        , [ 'name' => 'dqqn', 'id' => $other->children->get( 0 )->id ]
        ]
      ]
    ]);
  }

  function it_updates_a_sibling_association() {
    $this->beConstructedWith([
      'name' => 'I have one sibling'
    , 'nested_associations' => [
        'sibling' => [ 'name' => 'darling' ]
      ]
    ]);
    $this->save();

    $this->fill([
      'name' => 'I updated one sibling'
    , 'nested_associations' => [
        'sibling' => [ 'name' => 'devil' ]
      ]
    ]);
    $this->save();
    
    $this->sibling->name->shouldBe( 'devil' );
  }

  function it_maintains_the_original_sibling_after_updating() {
    $this->beConstructedWith([
      'name' => 'I have one sibling'
    , 'nested_associations' => [
        'sibling' => [ 'name' => 'honey' ]
      ]
    ]);
    $this->save();

    $id = $this->sibling->id;

    $this->fill([
      'name' => 'I updated one sibling'
    , 'nested_associations' => [
        'sibling' => [ 'name' => 'butter' ]
      ]
    ]);
    $this->save();

    $this->sibling->id->shouldBe( $id );
  }

  function it_deletes_the_sibling() {
    $this->beConstructedWith([
      'name' => 'I have one sibling'
    , 'nested_associations' => [
        'sibling' => [ 'name' => 'mortal' ]
      ]
    ]);
    $this->save();

    $this->fill([
      'name' => 'I deleted my only sibling'
    , 'nested_associations' => [
        'sibling' => [ '_delete' => 1 ]
      ]
    ]);
    $this->save();

    $this->sibling->shouldBe( null );
  }

  function it_creates_children_and_grandchildren() {
    $this->beConstructedWith([
      'name' => 'I have grandchildren'
    , 'nested_associations' => [
        'children' => [
          [ 'name' => 'I have children'
          , 'nested_associations' => [
              'children' => [
                [ 'name' => 'I am a grandchild' ]
              ]
            ]
          ]
        ]
      ]
    ]);
    $this->save();

    $this->children->get( 0 )->name->shouldBe( 'I have children' );
    $this->children->get( 0 )->children->get( 0 )->name->shouldBe( 'I am a grandchild' );
  }

  function it_creates_polymorphic_associations() {
    $this->beConstructedWith([
      'name' => 'I have friends'
    , 'nested_associations' => [
        'friends' => [
          [ 'name' => 'I am a friend' ]
        , [ 'name' => 'I am another friend' ]
        ]
      ]
    ]);
    $this->save();
    
    $this->friends->get( 0 )->name->shouldBe( 'I am a friend' );
    $this->friends->get( 1 )->name->shouldBe( 'I am another friend' );
  }

  function it_creates_associations_with_the_given_index() {
    $this->beConstructedWith([
      'name' => 'I have nieces in a specific sort order'
    , 'nested_associations' => [
        'nieces' => [
          [ 'name' => 'I am niece one' ]
        , [ 'name' => 'I am niece two' ]
        , [ 'name' => 'I am niece three' ]
        , [ 'name' => 'I am niece four' ]
        , [ 'name' => 'I am niece five' ]
        ]
      ]
    ]);
    $this->save();

    $this->nieces->get( 0 )->name->shouldBe( 'I am niece one' );
    $this->nieces->get( 0 )->position->shouldBe( 0 );
    $this->nieces->get( 1 )->name->shouldBe( 'I am niece two' );
    $this->nieces->get( 1 )->position->shouldBe( 1 );
    $this->nieces->get( 2 )->name->shouldBe( 'I am niece three' );
    $this->nieces->get( 2 )->position->shouldBe( 2 );
    $this->nieces->get( 3 )->name->shouldBe( 'I am niece four' );
    $this->nieces->get( 3 )->position->shouldBe( 3 );
    $this->nieces->get( 4 )->name->shouldBe( 'I am niece five' );
    $this->nieces->get( 4 )->position->shouldBe( 4 );
  }

  function it_updates_associations_with_the_given_index() {
    $this->beConstructedWith([
      'name' => 'I have nieces in a specific sort order'
    , 'nested_associations' => [
        'nieces' => [
          [ 'name' => 'I am niece one' ]
        , [ 'name' => 'I am niece two' ]
        , [ 'name' => 'I am niece three' ]
        , [ 'name' => 'I am niece four' ]
        , [ 'name' => 'I am niece five' ]
        ]
      ]
    ]);
    $this->save();

    $this->fill([
      'nested_associations' => [
        'nieces' => [
          [ 'id' => $this->nieces->get( 4 )->id ]
        , [ 'id' => $this->nieces->get( 3 )->id ]
        , [ 'id' => $this->nieces->get( 2 )->id ]
        , [ 'id' => $this->nieces->get( 1 )->id ]
        , [ 'id' => $this->nieces->get( 0 )->id ]
        ]
      ]
    ]);
    $this->save();

    $this->nieces->get( 0 )->name->shouldBe( 'I am niece five' );
    $this->nieces->get( 0 )->position->shouldBe( 0 );
    $this->nieces->get( 1 )->name->shouldBe( 'I am niece four' );
    $this->nieces->get( 1 )->position->shouldBe( 1 );
    $this->nieces->get( 2 )->name->shouldBe( 'I am niece three' );
    $this->nieces->get( 2 )->position->shouldBe( 2 );
    $this->nieces->get( 3 )->name->shouldBe( 'I am niece two' );
    $this->nieces->get( 3 )->position->shouldBe( 3 );
    $this->nieces->get( 4 )->name->shouldBe( 'I am niece one' );
    $this->nieces->get( 4 )->position->shouldBe( 4 );
  }

  function it_does_not_overwrite_given_order_values() {
    $this->beConstructedWith([
      'name' => 'I have nieces in a specific sort order'
    , 'nested_associations' => [
        'nieces' => [
          [ 'name' => 'I am niece one',   'position' => 4 ]
        , [ 'name' => 'I am niece two',   'position' => 1 ]
        , [ 'name' => 'I am niece three', 'position' => 3 ]
        , [ 'name' => 'I am niece four',  'position' => 2 ]
        , [ 'name' => 'I am niece five',  'position' => 0 ]
        ]
      ]
    ]);
    $this->save();

    $this->nieces->get( 0 )->name->shouldBe( 'I am niece five' );
    $this->nieces->get( 1 )->name->shouldBe( 'I am niece two' );
    $this->nieces->get( 2 )->name->shouldBe( 'I am niece four' );
    $this->nieces->get( 3 )->name->shouldBe( 'I am niece three' );
    $this->nieces->get( 4 )->name->shouldBe( 'I am niece one' );
  }


  // SaveWithNestedAssociations method
  function it_saves_itself_and_the_nested_associations() {
    $this->beConstructedWith([
      'name' => 'I save it all'
    , 'nested_associations' => [
        'children' => [
          [ 'name' => 'been' ]
        , [ 'name' => 'deen' ]
        ]
      ]
    ]);

    $this->save();
    $this->name->shouldBe( 'I save it all' );
    $this->children->count()->shouldBe( 2 );
    $this->children->get( 0 )->name->shouldBe( 'been' );
    $this->children->get( 1 )->name->shouldBe( 'deen' );
  }

  function it_returns_the_original_save_value_after_saving_itself_and_the_nested_associations() {
    $this->beConstructedWith([
      'name' => 'I save it all'
    , 'nested_associations' => [
        'children' => [
          [ 'name' => 'been' ]
        , [ 'name' => 'deen' ]
        ]
      ]
    ]);
    $this->save()->shouldBe( true );
  }



  // Deep validation messages
  function it_creates_validation_errors_for_the_instance() {
    $this->beConstructedWith([
      'group' => 'I am nameless'
    , 'nested_associations' => [
        'children' => [
          [ 'name' => 'been' ]
        , [ 'name' => 'deen' ]
        ]
      ]
    ]);

    $this->isValid();
    $errors = $this->errors();
    $errors->shouldHaveKey( 'name' );
    $errors['name']->shouldHaveKeyWithValue( 'required', 'is required' );
  }

  function it_creates_validation_errors_for_the_children() {
    $this->beConstructedWith([
      'name' => 'Most of my children are just wrong'
    , 'nested_associations' => [
        'children' => [
          [ 'group' => 'been' ]
        , [ 'name'  => 'leen' ]
        , [ 'group' => 'deen' ]
        ]
      ]
    ]);

    $this->isValid();
    $errors = $this->errors();
    $errors->shouldHaveKey( 'children' );    
    $errors['children']->shouldHaveCount( 2 );
    $errors['children'][0]->shouldHaveKey( 'name' );
    $errors['children'][0]['name']->shouldHaveKeyWithValue( 'required', 'is required' );
    $errors['children'][0]->shouldHaveKey( '_index' );
    $errors['children'][0]['_index']->shouldBe( 0 );
    $errors['children'][1]->shouldHaveKey( 'name' );
    $errors['children'][1]['name']->shouldHaveKeyWithValue( 'required', 'is required' );
    $errors['children'][1]->shouldHaveKey( '_index' );
    $errors['children'][1]['_index']->shouldBe( 2 );
  }

  function it_creates_validation_errors_for_the_grandchildren() {
    $this->beConstructedWith([
      'name' => 'My children are ok, my grandchildren are not'
    , 'nested_associations' => [
        'children' => [
          [ 'name' => 'leen' ]
        , [ 'name' => 'zeen'
          , 'nested_associations' => [
              'children' => [
                [ 'name'  => 'lano' ]
              , [ 'name'  => 'zano' ]
              , [ 'group' => 'bano' ]
              , [ 'name'  => 'hano' ]
              , [ 'group' => 'dano' ]
              ]
            ]
          ]
        ]
      ]
    ]);

    $this->isValid();
    $errors = $this->errors();
    $errors->shouldHaveKey( 'children' );    
    $errors['children']->shouldHaveCount( 1 );
    $errors['children'][0]->shouldHaveKey( 'children' );
    $errors['children'][0]['children']->shouldHaveCount( 2 );
    $errors['children'][0]['children'][0]->shouldHaveKey( 'name' );
    $errors['children'][0]['children'][0]['name']->shouldHaveKeyWithValue( 'required', 'is required' );
    $errors['children'][0]['children'][0]->shouldHaveKey( '_index' );
    $errors['children'][0]['children'][0]['_index']->shouldBe( 2 );
    $errors['children'][0]['children'][1]->shouldHaveKey( 'name' );
    $errors['children'][0]['children'][1]['name']->shouldHaveKeyWithValue( 'required', 'is required' );
    $errors['children'][0]['children'][1]->shouldHaveKey( '_index' );
    $errors['children'][0]['children'][1]['_index']->shouldBe( 4 );
  }

}




