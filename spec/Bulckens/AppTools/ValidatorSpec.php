<?php

namespace spec\Bulckens\AppTools;

use Bulckens\Helpers\StringHelper;
use Bulckens\AppTools\App;
use Bulckens\AppTools\Upload\Tmp;
use Bulckens\AppTools\Validator;
use Bulckens\AppTests\TestModel;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ValidatorSpec extends ObjectBehavior {

  function let() {
    $app = new App( 'dev' );
    $app->run();
    $this->beConstructedWith([ 'email' => [ 'required' => true ] ]);
  }

  function letGo() {
    TestModel::truncate();
    exec( sprintf( 'rm -rf %s', escapeshellarg( App::root( 'dev/upload/tmp' ) ) ) );
    exec( sprintf( 'rm -rf %s', escapeshellarg( App::root( 'dev/upload/test' ) ) ) );
  }


  // Data method
  function it_sets_and_returns_arbitrary_data_for_validation() {
    $this->data([ 'more' => 'info' ]);
    $this->data()->shouldHaveKeyWithValue( 'more', 'info' );
  }

  function it_returns_data_as_an_array() {
    $this->data()->shouldBeArray();
  }

  function it_sets_data_and_returns_itself() {
    $this->data([ 'more' => 'info' ])->shouldBe( $this );
  }


  // Model method
  function it_sets_and_returns_a_model() {
    $model = new TestModel();
    $this->model( $model );
    $this->model()->shouldBe( $model );
  }

  function it_sets_a_model_by_class_name() {
    $model = new TestModel();
    $model->save();
    $this->model( 'Bulckens\AppTests\TestModel', $model->id );
    $this->model()->shouldHaveType( 'Bulckens\AppTests\TestModel' );
    $this->model()->id->shouldBe( $model->id );
  }

  function it_returns_itself_after_setting_a_model() {
    $model = new TestModel();
    $this->model( $model )->shouldBe( $this );
  }


  // Messages method
  function it_sets_an_array_of_base_error_messages() {
    $this->messages([ 'base' => [ 'required' => 'eb lasustov' ] ]);
    $this->passes();
    $this->errorMessages( 'email' )->shouldContain( 'eb lasustov' );
  }

  function it_sets_an_array_of_specific_error_messages() {
    $this->messages([ 'specific' => [ 'email' => [ 'required' => 'sters stres' ] ] ]);
    $this->passes();
    $this->errorMessages( 'email' )->shouldContain( 'sters stres' );
  }

  function it_returns_itself_after_setting_messages() {
    $this->messages([ 'base' => [ 'required' => 'eb lasustov' ] ])->shouldBe( $this );
  }


  // Passes method
  function it_passes_when_no_errors_are_present() {
    $this->data([ 'email' => 'fl@ming.go' ]);
    $this->passes()->shouldBe( true );
  }

  function it_does_not_pass_when_errors_are_present() {
    $this->passes()->shouldBe( false );
  }


  // Fails method
  function it_does_not_fail_when_no_errors_are_present() {
    $this->data([ 'email' => 'fl@ming.go' ]);
    $this->fails()->shouldBe( false );
  }

  function it_fails_when_errors_are_present() {
    $this->fails()->shouldBe( true );
  }


  // Errors method
  function it_returns_an_array_with_errors() {
    $this->errors()->shouldBeArray();
  }

  function it_returns_null_when_no_errors_are_present_for_a_given_key() {
    $this->data([ 'email' => 'fl@ming.go' ]);
    $this->passes();
    $this->errors( 'email' )->shouldBe( null );
  }

  function it_returns_an_array_of_errors_on_a_given_key() {
    $this->passes();
    $this->errors( 'email' )->shouldHaveKey( 'required' );
  }


  // ErrorMessages method
  function it_returns_error_messages() {
    $this->passes();
    $this->errorMessages()->shouldHaveKey( 'email' );
    $this->errorMessages()['email']->shouldContain( 'is required' );
  }

  function it_returns_error_messages_on_a_given_attribute() {
    $this->passes();
    $this->errorMessages( 'email' )->shouldContain( 'is required' );
  }


  // Prefix method
  function it_applies_a_prefix_to_the_reference_keys_for_a_errors() {
    $this->prefix( 'subset' );
    $this->passes();
    $errors = $this->errors();
    $errors->shouldHaveKey( 'subset.email' );
  }

  function it_returns_the_prefix() {
    $this->prefix( 'subset' );
    $this->prefix()->shouldBe( 'subset' );
  }

  function it_applies_a_prefix_to_the_reference_keys_for_a_specific_error() {
    $this->prefix( 'subset' );
    $this->passes();
    $this->errors( 'subset.email' )->shouldContain( 'is required' );
  }

  function it_applies_a_prefix_to_the_reference_keys_for_error_messages() {
    $this->prefix( 'subset' );
    $this->passes();
    $errors = $this->errorMessages();
    $errors->shouldHaveKey( 'subset.email' );
  }

  function it_applies_a_prefix_to_the_reference_keys_for_a_specific_error_message() {
    $this->prefix( 'subset' );
    $this->passes();
    $this->errorMessages( 'subset.email' )->shouldContain( 'is required' );
  }

  function it_returns_itself_after_setting_the_prefix() {
    $this->prefix( 'egoism' )->shouldBe( $this );
  }


  // Value required
  function it_ensures_a_value_is_required() {
    $this->beConstructedWith([ 'email' => [ 'required' => true ] ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'email' )->shouldContain( 'is required' );
  }

  function it_ensures_a_required_value_is_given() {
    $this->beConstructedWith([ 'email' => [ 'required' => true ] ]);
    $this->data([ 'email' => 'li@la.lo' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'email' )->shouldHaveCount( 0 );
  }

  function it_ensures_a_value_is_required_on_create() {
    $model = new TestModel();
    $this->beConstructedWith([ 'email' => [ 'required' => 'create' ] ]);
    $this->model( $model );
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'email' )->shouldContain( 'is required' );
  }

  function it_ensures_a_value_is_required_on_update() {
    $model = new TestModel();
    $model->save();
    $this->beConstructedWith([ 'email' => [ 'required' => 'update' ] ]);
    $this->model( $model );
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'email' )->shouldContain( 'is required' );
  }

  function it_ensures_a_value_is_required_when_it_is_blank() {
    $this->beConstructedWith([ 'email' => [ 'required' => true ] ]);
    $this->data([ 'email' => '' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'email' )->shouldHaveCount( 1 );
  }

  function it_allows_not_to_be_required_explicitly() {
    $this->beConstructedWith([ 'email' => [ 'required' => false ] ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'email' )->shouldHaveCount( 0 );
  }

  function it_requires_an_upload() {
    $upload = new Tmp([ 'name' => 'w.jpg' , 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]);

    $this->beConstructedWith([ 'image' => [ 'required' => true ] ]);
    $this->data([ 'image' => $upload ]);
    $this->passes()->shouldBe( true );
  }

  function it_fails_when_an_upload_is_required_but_none_is_given() {
    $this->beConstructedWith([ 'image' => [ 'required' => true ] ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'image' )->shouldContain( 'is required' );
  }


  // Value forbidden
  function it_ensures_a_value_is_forbidden() {
    $this->beConstructedWith([ 'phish' => [ 'forbidden' => true ] ]);
    $this->data([ 'phish' => 'DROP DATABASE testDB;' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'phish' )->shouldContain( 'is not allowed' );
  }

  function it_ensures_a_forbidden_value_is_not_given() {
    $this->beConstructedWith([ 'phish' => [ 'forbidden' => true ] ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'phish' )->shouldHaveCount( 0 );
  }


  // Value unchangeable
  function it_ensures_a_value_is_unchangeable() {
    $model = new TestModel();
    $model->name = 'foof';
    $model->save();
    $this->beConstructedWith([ 'name' => [ 'unchangeable' => true ] ]);
    $this->data([ 'name' => 'offo' ]);
    $this->model( $model );
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'can not be changed' );
  }

  function it_allows_a_value_to_be_changed_with_new_models() {
    $model = new TestModel();
    $model->name = 'foof';
    $this->beConstructedWith([ 'name' => [ 'unchangeable' => true ] ]);
    $this->data([ 'name' => 'offo' ]);
    $this->model( $model );
    $this->passes()->shouldBe( true );
  }

  function it_allows_the_unchangeable_value_to_be_blank() {
    $model = new TestModel();
    $model->save();
    $this->beConstructedWith([ 'name' => [ 'unchangeable' => true ] ]);
    $this->data([ 'name' => '' ]);
    $this->model( $model );
    $this->passes()->shouldBe( true );
  }


  // Value min
  function it_ensures_the_minimum_length_of_a_value() {
    $this->beConstructedWith([ 'email' => [ 'min' => 200 ] ]);
    $this->data([ 'email' => 'a@b.c' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'email' )->shouldContain( 'should be longer than 200 characters' );
  }

  function it_ensures_the_value_has_a_minimum_length() {
    $this->beConstructedWith([ 'email' => [ 'min' => 20 ] ]);
    $this->data([ 'email' => 'aaaaaaaaa@bbbbbbbbb.ccccccccc' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'email' )->shouldHaveCount( 0 );
  }

  function it_ensures_the_value_has_a_minimum_length_with_an_empty_string() {
    $this->beConstructedWith([ 'email' => [ 'min' => 20 ] ]);
    $this->data([ 'email' => '' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'email' )->shouldContain( 'should be longer than 20 characters' );
  }

  function it_allows_absense_of_data_while_testing_the_minimum_length() {
    $this->beConstructedWith([ 'email' => [ 'min' => 20 ] ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'email' )->shouldHaveCount( 0 );
  }


  // Value max
  function it_ensures_the_maximum_length_of_a_value() {
    $this->beConstructedWith([ 'email' => [ 'max' => 10 ] ]);
    $this->data([ 'email' => 'aaaaaaaaa@bbbbbbbbb.ccccccccc' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'email' )->shouldContain( 'should be shorter than 10 characters' );
  }

  function it_ensures_the_value_has_a_maximum_length() {
    $this->beConstructedWith([ 'email' => [ 'max' => 1000 ] ]);
    $this->data([ 'email' => 'aaaaaaaaa@bbbbbbbbb.ccccccccc' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'email' )->shouldHaveCount( 0 );
  }

  function it_allows_absense_of_data_while_testing_for_the_maximum_length() {
    $this->beConstructedWith([ 'email' => [ 'max' => 1000 ] ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'email' )->shouldHaveCount( 0 );
  }


  // Value match
  function it_ensures_a_regular_expression_matches_a_value() {
    $this->beConstructedWith([ 'key' => [ 'match' => '/^[a-z]{4}[0-9]{4}$/' ] ]);
    $this->data([ 'key' => 'K4Iuk4525khgi0' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'key' )->shouldContain( 'does not have the right format' );
  }

  function it_ensures_the_format_of_a_value_to_match_a_regular_expression() {
    $this->beConstructedWith([ 'key' => [ 'match' => '/^[a-z]{4}[0-9]{4}$/' ] ]);
    $this->data([ 'key' => 'jsoe5913' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'key' )->shouldHaveCount( 0 );
  }

  function it_ensures_an_email_regex_matches_a_value() {
    $this->beConstructedWith([ 'email' => [ 'match' => 'email' ] ]);
    $this->data([ 'email' => 'kandukinast' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'email' )->shouldContain( 'is not an email address' );
  }

  function it_ensures_the_format_of_a_value_to_match_an_email() {
    $this->beConstructedWith([ 'email' => [ 'match' => 'email' ] ]);
    $this->data([ 'email' => 'a.b+x@bido-na.fra' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'email' )->shouldHaveCount( 0 );
  }

  function it_ensures_an_email_address_is_inside_a_value() {
    $this->beConstructedWith([ 'email' => [ 'match' => 'email_address' ] ]);
    $this->data([ 'email' => 'Fra stu li ma pra.' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'email' )->shouldContain( 'does not contain an email address' );
  }

  function it_ensures_the_format_of_a_value_to_contain_an_email_address() {
    $this->beConstructedWith([ 'email' => [ 'match' => 'email_address' ] ]);
    $this->data([ 'email' => 'Malba si na: a.b+x@bido-na.fra' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'email' )->shouldHaveCount( 0 );
  }

  function it_ensures_a_lowercase_alphanumeric_regex_matches_a_value() {
    $this->beConstructedWith([ 'name' => [ 'match' => 'alpha' ] ]);
    $this->data([ 'name' => 'KalKamaMa' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'should be letters-only and all lowercase' );
  }

  function it_ensures_the_value_is_lowercase_alpha() {
    $this->beConstructedWith([ 'name' => [ 'match' => 'alpha' ] ]);
    $this->data([ 'name' => 'himanasupi' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }

  function it_ensures_a_uppercase_alpha_regex_matches_a_value() {
    $this->beConstructedWith([ 'name' => [ 'match' => 'ALPHA' ] ]);
    $this->data([ 'name' => 'glimastrapot' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'should be letters-only and all uppercase' );
  }

  function it_ensures_the_value_is_uppercase_alpha() {
    $this->beConstructedWith([ 'name' => [ 'match' => 'ALPHA' ] ]);
    $this->data([ 'name' => 'FUMENLAPROT' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }

  function it_ensures_an_alpha_regex_matches_a_value() {
    $this->beConstructedWith([ 'name' => [ 'match' => 'Alpha' ] ]);
    $this->data([ 'name' => 'H4J5m3j3' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'should be letters-only' );
  }

  function it_ensures_the_value_is_alpha() {
    $this->beConstructedWith([ 'name' => [ 'match' => 'Alpha' ] ]);
    $this->data([ 'name' => 'FUMENLAProt' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }

  function it_ensures_a_lowercase_alpha_regex_matches_a_value() {
    $this->beConstructedWith([ 'name' => [ 'match' => 'alphanumeric' ] ]);
    $this->data([ 'name' => 'KalKamaMa' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'should be alphanumeric and all lowercase' );
  }

  function it_ensures_the_value_is_lowercase_alphanumeric() {
    $this->beConstructedWith([ 'name' => [ 'match' => 'alphanumeric' ] ]);
    $this->data([ 'name' => 'himanasupi' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }

  function it_ensures_a_uppercase_alphanumeric_regex_matches_a_value() {
    $this->beConstructedWith([ 'name' => [ 'match' => 'ALPHANUMERIC' ] ]);
    $this->data([ 'name' => 'glimastrapot' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'should be alphanumeric and all uppercase' );
  }

  function it_ensures_the_value_is_uppercase_alphanumeric() {
    $this->beConstructedWith([ 'name' => [ 'match' => 'ALPHANUMERIC' ] ]);
    $this->data([ 'name' => 'FUMENLAPROT' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }

  function it_ensures_an_alphanumeric_regex_matches_a_value() {
    $this->beConstructedWith([ 'name' => [ 'match' => 'Alphanumeric' ] ]);
    $this->data([ 'name' => 'H4J5m3j3---asdf' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'should be alphanumeric' );
  }

  function it_ensures_the_value_is_alphanumeric() {
    $this->beConstructedWith([ 'name' => [ 'match' => 'Alphanumeric' ] ]);
    $this->data([ 'name' => 'FUMENLAProt' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }

  function it_allows_absense_of_data_when_matching_a_value_against_a_regex() {
    $this->beConstructedWith([ 'name' => [ 'match' => '/^1?23?$/' ] ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }


  // Value confirmation
  function it_ensures_the_confirmation_of_a_value() {
    $this->beConstructedWith([ 'name' => [ 'confirmation' => true ] ]);
    $this->data([ 'name' => 'Flubert', 'name_confirmation' => 'Trebulf' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'confirmation does not match' );
  }

  function it_ensures_the_confirmation_of_a_value_without_the_confirmation() {
    $this->beConstructedWith([ 'name' => [ 'confirmation' => true ] ]);
    $this->data([ 'name' => 'Flubert' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'confirmation does not match' );
  }

  function it_ensures_a_value_is_confirmed() {
    $this->beConstructedWith([ 'name' => [ 'confirmation' => true ] ]);
    $this->data([ 'name' => 'Flubert', 'name_confirmation' => 'Flubert' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }

  function it_ensures_a_value_is_confirmed_with_a_custom_column_name() {
    $this->beConstructedWith([ 'name' => [ 'confirmation' => 'nombre' ] ]);
    $this->data([ 'name' => 'Flubert', 'nombre' => 'Flubert' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }

  function it_allows_absence_of_data_when_testing_the_confirmation_value() {
    $this->beConstructedWith([ 'name' => [ 'confirmation' => true ] ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }

  function it_does_not_allow_absence_of_the_confirmation_value_when_the_confirmable_value_is_given() {
    $this->beConstructedWith([ 'name' => [ 'confirmation' => true ] ]);
    $this->data([ 'name' => 'Flubert' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'confirmation does not match' );
  }


  // Exact value
  function it_ensures_the_exact_match_of_a_value() {
    $this->beConstructedWith([ 'name' => [ 'exactly' => 'famosa' ] ]);
    $this->data([ 'name' => 'samofa' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'is not the expected value' );
  }

  function it_ensures_the_value_is_matched_exactly() {
    $this->beConstructedWith([ 'name' => [ 'exactly' => 'famosa' ] ]);
    $this->data([ 'name' => 'famosa' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }

  function it_allows_absense_of_data_when_matching_exactly() {
    $this->beConstructedWith([ 'name' => [ 'exactly' => 'famosa' ] ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }


  // Value in
  function it_ensures_the_presence_of_a_value_in_an_array() {
    $this->beConstructedWith([ 'name' => [ 'in' => [ 'aap', 'noot', 'mies' ] ] ]);
    $this->data([ 'name' => 'wim' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'could not be found in the list' );
  }

  function it_ensures_a_value_is_inside_an_array() {
    $this->beConstructedWith([ 'name' => [ 'in' => [ 'aap', 'noot', 'mies' ] ] ]);
    $this->data([ 'name' => 'aap' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }

  function it_allows_absense_of_data_when_testing_the_presence_in_an_array() {
    $this->beConstructedWith([ 'name' => [ 'in' => [ 'aap', 'noot', 'mies' ] ] ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }


  // Value not in
  function it_ensures_the_presence_of_a_value_is_not_in_an_array() {
    $this->beConstructedWith([ 'name' => [ 'not_in' => [ 'aap', 'noot', 'mies' ] ] ]);
    $this->data([ 'name' => 'aap' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'is not an allowed value' );
  }

  function it_ensures_a_value_is_not_inside_an_array() {
    $this->beConstructedWith([ 'name' => [ 'not_in' => [ 'aap', 'noot', 'mies' ] ] ]);
    $this->data([ 'name' => 'wim' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }

  function it_allows_absense_of_data_when_testing_the_absence_in_an_array() {
    $this->beConstructedWith([ 'name' => [ 'not_in' => [ 'aap', 'noot', 'mies' ] ] ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }


  // Value numeric
  function it_fails_when_a_given_value_is_not_numeric() {
    $this->beConstructedWith([ 'age' => [ 'numeric' => true ] ]);
    $this->data([ 'age' => 'I am 38.7' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'age' )->shouldContain( 'is not the right numeric value' );
  }

  function it_ensures_a_value_is_numeric() {
    $this->beConstructedWith([ 'age' => [ 'numeric' => true ] ]);
    $this->data([ 'age' => '38.7' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'age' )->shouldHaveCount( 0 );
  }

  function it_fails_when_a_given_value_is_not_even() {
    $this->beConstructedWith([ 'age' => [ 'numeric' => 'even' ] ]);
    $this->data([ 'age' => '37' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'age' )->shouldContain( 'is not a an even number' );
  }

  function it_ensures_a_value_is_even() {
    $this->beConstructedWith([ 'age' => [ 'numeric' => 'even' ] ]);
    $this->data([ 'age' => '38' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'age' )->shouldHaveCount( 0 );
  }

  function it_fails_when_a_given_value_is_not_odd() {
    $this->beConstructedWith([ 'age' => [ 'numeric' => 'odd' ] ]);
    $this->data([ 'age' => '38' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'age' )->shouldContain( 'is not a an odd number' );
  }

  function it_ensures_a_value_is_odd() {
    $this->beConstructedWith([ 'age' => [ 'numeric' => 'odd' ] ]);
    $this->data([ 'age' => '37' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'age' )->shouldHaveCount( 0 );
  }

  function it_fails_when_a_given_value_is_not_an_integer() {
    $this->beConstructedWith([ 'age' => [ 'numeric' => 'integer' ] ]);
    $this->data([ 'age' => '3.141592653589' ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'age' )->shouldContain( 'is not a an integer' );
  }

  function it_ensures_a_value_is_an_integer() {
    $this->beConstructedWith([ 'age' => [ 'numeric' => 'integer' ] ]);
    $this->data([ 'age' => '37' ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'age' )->shouldHaveCount( 0 );
  }

  function it_allows_absense_of_data_when_testing_for_a_numeric_value() {
    $this->beConstructedWith([ 'age' => [ 'numeric' => true ] ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'age' )->shouldHaveCount( 0 );
  }


  // Between
  function it_ensures_a_numeric_value_between_two_limits() {
    $this->beConstructedWith([ 'tolerance' => [ 'between' => [-2, 5] ] ]);
    $this->data([ 'tolerance' => 3.5 ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'tolerance' )->shouldHaveCount( 0 );
  }

  function it_fails_to_validate_a_numer_outside_of_the_given_range() {
    $this->beConstructedWith([ 'tolerance' => [ 'between' => [-2, 5] ] ]);
    $this->data([ 'tolerance' => 9 ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'tolerance' )->shouldContain( 'should be between -2 and 5' );
  }


  // Value uniqueness
  function it_fails_when_a_value_is_not_unique() {
    $model = new TestModel();
    $model->name = 'foof';
    $model->save();
    $model = new TestModel();
    $this->beConstructedWith([ 'name' => [ 'unique' => true ] ]);
    $this->data([ 'name' => 'foof' ]);
    $this->model( $model );
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'is already taken' );
  }

  function it_fails_when_a_value_is_not_unique_within_a_scope() {
    $model = new TestModel();
    $model->name  = 'foof';
    $model->group = 'werk';
    $model->save();
    $model = new TestModel();
    $model->name  = 'foof';
    $model->group = 'werk';
    $this->beConstructedWith([ 'name' => [ 'unique' => [ 'scope' => 'group' ] ] ]);
    $this->data([ 'name' => 'foof', 'group' => 'werk' ]);
    $this->model( $model );
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'is already taken' );
  }

  function it_fails_when_a_value_is_not_unique_within_multiple_scopes() {
    $model = new TestModel();
    $model->name  = 'foof';
    $model->group = 'werk';
    $model->parent_id = 1;
    $model->save();
    $model = new TestModel();
    $model->name  = 'foof';
    $model->group = 'werk';
    $model->parent_id = 1;
    $this->beConstructedWith([ 'name' => [ 'unique' => [ 'scope' => [ 'parent_id', 'group' ] ] ] ]);
    $this->data([ 'name' => 'foof', 'group' => 'werk', 'parent_id' => 1 ]);
    $this->model( $model );
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'name' )->shouldContain( 'is already taken' );
  }

  function it_ensures_a_value_is_unique() {
    $model = new TestModel();
    $model->name = 'roof';
    $model->save();
    $model = new TestModel();
    $this->beConstructedWith([ 'name' => [ 'unique' => true ] ]);
    $this->data([ 'name' => 'foof' ]);
    $this->model( $model );
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }

  function it_ensures_a_value_is_unique_within_a_scope() {
    $model = new TestModel();
    $model->name  = 'roof';
    $model->group = 'werk';
    $model->save();
    $model = new TestModel();
    $model->name  = 'loof';
    $model->group = 'werk';
    $this->beConstructedWith([ 'name' => [ 'unique' => [ 'scope' => 'group' ] ] ]);
    $this->data([ 'name' => 'loof', 'group' => 'werk' ]);
    $this->model( $model );
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }

  function it_ensures_a_value_is_unique_within_multiple_scopes() {
    $model = new TestModel();
    $model->name  = 'roof';
    $model->group = 'werk';
    $model->parent_id = 1;
    $model->save();
    $model = new TestModel();
    $model->name  = 'roof';
    $model->group = 'werk';
    $model->parent_id = 2;
    $this->beConstructedWith([ 'name' => [ 'unique' => [ 'scope' => [ 'parent_id', 'group' ] ] ] ]);
    $this->data([ 'name' => 'roof', 'group' => 'werk', 'parent_id' => 2 ]);
    $this->model( $model );
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }

  function it_allows_absense_of_data_while_testing_for_uniqueness() {
    $model = new TestModel();
    $model->save();
    $model = new TestModel();
    $this->beConstructedWith([ 'name' => [ 'unique' => true ] ]);
    $this->model( $model );
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'name' )->shouldHaveCount( 0 );
  }


  // File weight
  function it_ensures_a_file_is_not_too_heavy() {
    $upload = new Tmp([ 'name' => 'w.jpg' , 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]);

    $this->beConstructedWith([ 'image' => [ 'weight' => '10 MB' ] ]);
    $this->data([ 'image' => $upload ]);
    $this->passes()->shouldBe( true );
  }

  function it_ensures_a_file_is_not_too_light() {
    $upload = new Tmp([ 'name' => 'w.jpg' , 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]);

    $this->beConstructedWith([ 'image' => [ 'weight' => [ 'min' => '1KB' ] ] ]);
    $this->data([ 'image' => $upload ]);
    $this->passes()->shouldBe( true );
  }

  function it_fails_when_a_file_is_too_heavy() {
    $upload = new Tmp([ 'name' => 'w.jpg' , 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]);

    $this->beConstructedWith([ 'image' => [ 'weight' => [ 'max' => '1 KB' ] ] ]);
    $this->data([ 'image' => $upload ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'image' )->shouldContain( 'should be no bigger than 1 KB' );
  }

  function it_fails_when_a_file_is_too_light() {
    $upload = new Tmp([ 'name' => 'w.jpg' , 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]);

    $this->beConstructedWith([ 'image' => [ 'weight' => [ 'min' => '1TB' ] ] ]);
    $this->data([ 'image' => $upload ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'image' )->shouldContain( 'should be at least 1TB' );
  }

  function it_fails_to_ensure_the_weight_of_a_file_if_the_given_value_is_not_an_upload() {
    $this->beConstructedWith([ 'image' => [ 'weight' => [ 'min' => '10 PB' ] ] ]);
    $this->data([ 'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ] ]);
    $this->shouldThrow( 'Bulckens\AppTools\ValidatorFileDataNotAnUploadInstanceException' )->duringPasses();
  }

  function it_passes_when_a_file_width_is_required_but_no_file_is_given() {
    $this->beConstructedWith([ 'image' => [ 'weight' => '1000 TB' ] ]);
    $this->passes()->shouldBe( true );
  }


  // File dimensions
  function it_ensures_an_image_is_not_too_large() {
    $upload = new Tmp([ 'name' => 'w.jpg' , 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]);

    $this->beConstructedWith([ 'image' => [ 'dimensions' => '1204x1024' ] ]);
    $this->data([ 'image' => $upload ]);
    $this->passes()->shouldBe( true );
  }

  function it_ensures_an_image_is_not_too_small() {
    $upload = new Tmp([ 'name' => 'w.jpg' , 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]);

    $this->beConstructedWith([ 'image' => [ 'dimensions' => [ 'min' => '128x128' ] ] ]);
    $this->data([ 'image' => $upload ]);
    $this->passes()->shouldBe( true );
  }

  function it_fails_when_an_image_is_too_large() {
    $upload = new Tmp([ 'name' => 'w.jpg' , 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]);

    $this->beConstructedWith([ 'image' => [ 'dimensions' => [ 'max' => '32x32' ] ] ]);
    $this->data([ 'image' => $upload ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'image' )->shouldContain( 'should not be wider than 32px' );
    $this->errorMessages( 'image' )->shouldContain( 'should not be higher than 32px' );
  }

  function it_fails_when_an_image_is_too_wide() {
    $upload = new Tmp([ 'name' => 'w.jpg' , 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]);

    $this->beConstructedWith([ 'image' => [ 'dimensions' => [ 'max' => '32x' ] ] ]);
    $this->data([ 'image' => $upload ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'image' )->shouldContain( 'should not be wider than 32px' );
    $this->errorMessages( 'image' )->shouldHaveCount( 1 );
  }

  function it_fails_when_an_image_is_too_high() {
    $upload = new Tmp([ 'name' => 'w.jpg' , 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]);

    $this->beConstructedWith([ 'image' => [ 'dimensions' => [ 'max' => 'x32' ] ] ]);
    $this->data([ 'image' => $upload ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'image' )->shouldContain( 'should not be higher than 32px' );
    $this->errorMessages( 'image' )->shouldHaveCount( 1 );
  }

  function it_fails_when_an_image_is_too_small() {
    $upload = new Tmp([ 'name' => 'w.jpg' , 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]);

    $this->beConstructedWith([ 'image' => [ 'dimensions' => [ 'min' => '5120x5120' ] ] ]);
    $this->data([ 'image' => $upload ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'image' )->shouldContain( 'should be at least 5120px wide' );
    $this->errorMessages( 'image' )->shouldContain( 'should be at least 5120px high' );
  }

  function it_fails_to_ensure_the_dimensions_of_an_image_if_the_given_value_is_not_an_upload() {
    $this->beConstructedWith([ 'image' => [ 'dimensions' => [ 'min' => '100x100' ] ] ]);
    $this->data([ 'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ] ]);
    $this->shouldThrow( 'Bulckens\AppTools\ValidatorFileDataNotAnUploadInstanceException' )->duringPasses();
  }

  function it_passes_when_specific_dimensions_are_required_but_no_file_is_given() {
    $this->beConstructedWith([ 'image' => [ 'dimensions' => [ 'min' => '5120x5120' ] ] ]);
    $this->passes()->shouldBe( true );
  }


  // File mime type
  function it_ensures_a_file_has_the_allowed_mime_type() {
    $upload = new Tmp([ 'name' => 'w.jpg' , 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]);

    $this->beConstructedWith([ 'image' => [ 'mime' => 'image/jpeg' ] ]);
    $this->data([ 'image' => $upload ]);
    $this->passes()->shouldBe( true );
  }

  function it_ensures_a_file_has_one_of_the_allowed_mime_types() {
    $upload = new Tmp([ 'name' => 'w.jpg' , 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]);

    $this->beConstructedWith([ 'image' => [ 'mime' => [ 'image/jpeg', 'image/png', 'image/gif' ] ] ]);
    $this->data([ 'image' => $upload ]);
    $this->passes()->shouldBe( true );
  }

  function it_fails_if_a_file_has_the_wrong_mime_type() {
    $upload = new Tmp([ 'name' => 'w.jpg' , 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ]);

    $this->beConstructedWith([ 'image' => [ 'mime' => 'image/png' ] ]);
    $this->data([ 'image' => $upload ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'image' )->shouldContain( 'is not an accepted file type' );
  }

  function it_fails_to_ensure_the_mime_type_of_a_file_if_the_given_value_is_not_an_upload() {
    $this->beConstructedWith([ 'image' => [ 'mime' => [ 'image/jpeg' ] ] ]);
    $this->data([ 'image' => [ 'name' => 'w.jpg', 'tmp_name' => self::setupTmpFile(), 'error' => UPLOAD_ERR_OK ] ]);
    $this->shouldThrow( 'Bulckens\AppTools\ValidatorFileDataNotAnUploadInstanceException' )->duringPasses();
  }

  function it_passes_when_a_mime_type_is_required_but_no_file_is_given() {
    $this->beConstructedWith([ 'image' => [ 'mime' => 'image/png' ] ]);
    $this->passes()->shouldBe( true );
  }


  // Custom validation
  function it_fails_when_a_custom_closure_returns_false() {
    $this->beConstructedWith([ 'faduba' => [ 'custom' => function() { return false; } ] ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'faduba' )->shouldContain( 'is invalid' );
  }

  function it_ensures_a_value_is_acceptable_using_a_custom_closure() {
    $this->beConstructedWith([ 'faduba' => [ 'custom' => function() { return true; } ] ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'faduba' )->shouldHaveCount( 0 );
  }

  function it_ensures_a_value_is_acceptable_using_a_method_name() {
    $this->beConstructedWith([ 'truthy' => [ 'custom' => 'Bulckens\AppTests\TestValidatorHelper::truthy' ] ]);
    $this->passes()->shouldBe( true );
    $this->errorMessages( 'truthy' )->shouldHaveCount( 0 );
  }

  function it_ensures_a_value_is_unacceptable_using_a_method_name() {
    $this->beConstructedWith([ 'falsy' => [ 'custom' => 'Bulckens\AppTests\TestValidatorHelper::falsy' ] ]);
    $this->passes()->shouldBe( false );
    $this->errorMessages( 'falsy' )->shouldContain( 'is invalid' );
  }


  // Helpers
  protected static function setupTmpFile() {
    if ( ! file_exists( $tmp = App::root( 'dev/upload/tmp' ) ) ) {
      mkdir( $tmp, 0777, true );
    }
    if ( ! file_exists( $dir = App::root( 'dev/upload/test' ) ) ) {
      mkdir( $dir, 0777, true );
    }

    $source = App::root( 'dev/upload/w.jpg' );
    $random = StringHelper::generate( 32 );
    $tmp_name = "$tmp/$random";

    copy( $source, $tmp_name );

    return $tmp_name;
  }


}
