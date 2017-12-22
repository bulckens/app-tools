<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\App;
use Bulckens\AppTools\I18n;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class I18nSpec extends ObjectBehavior {
  
  function let() {
    $app = new App( 'dev' );
    $app->run();
  }

  function letGo() {
    $this->purgeCache();
  }

  // Initialization
  function it_fails_when_the_i18n_directory_is_not_defined() {
    $this
      ->shouldThrow( 'Bulckens\AppTools\I18nDirMissingException' )
      ->during__Construct([ 'config' => 'i18n_missing_dir.yml' ]);
  }


  // Translate method
  function it_translates() {
    $this->t( 'beast' )->shouldBe( 'beast' );
  }

  function it_translates_a_nested_key() {
    $this->t( 'animals.monkey' )->shouldBe( 'monkey' );
  }

  function it_translates_a_nested_key_with_a_custom_locale() {
    $this->locale( 'es' )->t( 'animals.monkey' )->shouldBe( 'mono' );
  }

  function it_translates_from_a_nested_locale_file() {
    $this->locale( 'nl' )->t( 'flowers.rose' )->shouldBe( 'Roos' );
  }

  function it_returns_an_array_if_the_end_of_the_scope_has_not_been_reached() {
    $t = $this->t( 'animals' );
    $t->shouldBeArray();
    $t->shouldHaveKeyWithValue( 'monkey', 'monkey' );
    $t->shouldHaveKeyWithValue( 'cat', 'cat' );
    $t->shouldHaveKeyWithValue( 'dog', 'dog' );
  }

  function it_defaults_to_a_given_value_if_no_translation_could_be_found() {
    $this->t( 'belle', 'schone' )->shouldBe( 'schone' );
  }

  function it_forces_the_key_to_be_returned_if_no_value_could_be_found() {
    $this->t( 'belle.benado', null, true )->shouldBe( 'belle.benado' );
  }


  // Config method
  function it_returns_the_config_instance_without_an_argument() {
    $this->config()->shouldHaveType( 'Bulckens\AppTools\Config' );
  }

  function it_returns_the_the_value_for_a_given_key() {
    $this->config( 'default' )->shouldBe( 'en' );
  }

  function it_returns_a_given_default_value_if_key_is_not_existing() {
    $this->config( 'pater', 'nostrum' )->shouldBe( 'nostrum' );
  }


  // Locale method
  function it_returns_the_locale() {
    $this->locale()->shouldBe( 'en' );
  }

  function it_sets_the_locale() {
    $this->locale( 'nl' );
    $this->locale()->shouldBe( 'nl' );
  }

  function it_returns_itself_after_setting_the_locale() {
    $this->locale( 'es' )->shouldBe( $this );
  }


  // ConfigFile method
  function it_builds_config_file_name_from_class() {
    $this->configFile()->shouldBe( 'i18n.yml' );
  }

  function it_defines_a_custom_config_file() {
    $this->configFile( 'i18n_custom.yml' );
    $this->configFile()->shouldBe( 'i18n_custom.yml' );
    $this->config( 'default' )->shouldBe( 'hk' );
  }

  function it_unsets_the_custom_config_file_with_null_given() {
    $this->configFile( 'i18n_custom.yml' );
    $this->configFile()->shouldBe( 'i18n_custom.yml' );
    $this->configFile( null );
    $this->configFile()->shouldBe( 'i18n.yml' );
  }

  function it_returns_itself_after_defining_a_custom_config_file() {
    $this->configFile( 'i18n_custom.yml' )->shouldBe( $this );
  }


  // CacheKey method
  function it_returns_a_cache_key_for_the_default_locale() {
    $this->cacheKey()->shouldStartWith( 'bulckens.dev.app_tools.i18n.en' );
  }

  function it_returns_a_cache_key_for_the_custom_locale() {
    $this->locale( 'fr' );
    $this->cacheKey()->shouldStartWith( 'bulckens.dev.app_tools.i18n.fr' );
  }


  // CacheId method
  function it_returns_the_default_locale_as_cache_id() {
    $this->cacheId()->shouldBe( 'en' );
  }

  function it_returns_the_given_locale_as_cache_id() {
    $this->locale( 'nl' );
    $this->cacheId()->shouldBe( 'nl' );
  }


  // Cached method
  function it_is_negative_when_no_cache_has_been_created() {
    $this->purgeCache();
    $this->cached()->shouldBe( false );
  }

  function it_is_positive_when_cache_has_been_created() {
    $this->t( 'beast' );
    $this->cached()->shouldBe( true );
  }

  function it_is_always_negative_if_cacheing_is_disabled() {
    $this->configFile( 'i18n_no_cache.yml' );
    $this->purgeCache();
    $this->locale( 'nl' );
    $this->cached()->shouldBe( false );
  }


  // Dir method
  function it_returns_the_locale_dir() {
    $this->dir()->shouldBe( App::root( 'dev/i18n' ) );
  }

  function it_returns_the_custom_locale_dir() {
    $this->configFile( 'i18n_custom.yml' );
    $this->dir()->shouldBe( App::root( 'dev/i18n/plants' ) );
  }

}
