<?php

namespace spec\Bulckens\AppTools;

use PhpSpec\Exception\Example\FailureException;
use Redis;
use Resque_Redis;
use Bulckens\AppTools\Worker;
use Bulckens\AppTools\App;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class WorkerSpec extends ObjectBehavior {

  function let() {
    new App( 'dev' );
  }

  function letGo() {
    $this->stop();
  }


  // Configurability
  function it_is_configurable() {
    $this->configFile()->shouldBe( 'worker.yml' );
  }


  // Initialization
  function it_sets_the_namespace_of_resque_redis() {
    $this->config( 'namespace' )->shouldBe( 'app_tools' );

    if (( $prefix = Resque_Redis::getPrefix()) != 'app_tools:dev:' ) {
      throw new FailureException( "Expected Redis prefix to be app_tools:dev: but got $prefix" );
    }
  }


  // Start method
  function it_starts_the_workers() {
    $this->start();
    $this->pid()->shouldBeNumeric();
  }

  function it_returns_the_pid_after_starting() {
    $this->start()->shouldBeNumeric();
  }

  function it_creates_a_pid_file() {
    $this->start()->shouldBeNumeric();

    if ( ! file_exists( App::root( 'pids/dev-worker.pid' ))) {
      throw new FailureException( 'Expected PID file to be created but it was not' );
    }
  }

  function it_does_not_start_a_process_twice() {
    $pid = $this->start();
    $this->start()->shouldBe( $pid );
  }


  // Stop method
  function it_stops_active_workers() {
    $this->start();
    $this->stop()->shouldBeNumeric();
    $this->working()->shouldBe( false );
  }

  function it_returns_the_pid_after_stopping() {
    $this->start();
    $this->stop()->shouldBeNumeric();
  }

  function it_removes_the_pid_file_after_stopping() {
    $this->start()->shouldBeNumeric();
    $this->stop()->shouldBeNumeric();

    if ( file_exists( App::root( 'pids/dev-worker.pid' ))) {
      throw new FailureException( 'Expected PID file to be removed but it was not' );
    }
  }


  // Restart method
  function it_restarts_the_workers() {
    $pid = $this->start();
    $this->restart()->shouldNotBe( $pid );
  }

  function it_returns_the_pid_after_restarting() {
    $this->restart()->shouldBeNumeric();
  }


  // Job method
  function it_returns_a_job_id_after_assigning_a_job() {
    $this->start();
    $job_id = $this->job( 'test', 'AppTools\AppTests\TestJob', [
      'vla' => 'Janssen'
    ]);
    $job_id->shouldMatch( '/^[a-z0-9]{32}$/' );
  }


  // Release method
  function it_releases_the_worker_from_all_jobs_in_a_queue() {
    $this->start();
    $job_id = $this->job( 'test', 'AppTools\AppTests\TestJob', [
      'vla' => 'Falumba'
    ]);
    $job_id = $this->job( 'test', 'AppTools\AppTests\TestJob', [
      'lva' => 'Mastaba'
    ]);
    $released = $this->release( 'test' );
    $released->shouldBeNumeric();
  }

  function it_releases_the_worker_from_a_given_job_in_a_queue() {
    $this->start();
    $job_id = $this->job( 'test', 'AppTools\AppTests\TestJob', [
      'vla' => 'Falumba'
    ]);
    $released = $this->release( 'test', 'AppTools\AppTests\TestJob' );
    $released->shouldBeNumeric();
  }

  function it_releases_the_worker_from_multiple_jobs_in_a_queue() {
    $this->start();
    $this->job( 'test', 'AppTools\AppTests\TestJob', [
      'vla' => 'Falumba'
    ]);
    $this->job( 'test', 'AppTools\AppTests\TestJob', [
      'alv' => 'Samonda'
    ]);
    $this->job( 'test', 'AppTools\AppTests\TestJobWack', [
      'lav' => 'Mastaba'
    ]);
    $released = $this->release( 'test', [
      'AppTools\AppTests\TestJob'
    , 'AppTools\AppTests\TestJobWack'
    ]);
    $released->shouldBeNumeric();
  }

  function it_releases_the_worker_from_a_specific_job_id() {
    $this->start();
    $job_id = $this->job( 'test', 'AppTools\AppTests\TestJob', [
      'vla' => 'Falumba'
    ]);
    $released = $this->release( 'test', [
      'AppTools\AppTests\TestJob' => $job_id
    ]);
    $released->shouldBeNumeric();
  }

  function it_releases_the_worker_from_a_specific_job_with_specific_arguments() {
    $this->start();
    $job_id = $this->job( 'test', 'AppTools\AppTests\TestJob', [
      'vla' => 'Falumba'
    ]);
    $released = $this->release( 'test', [
      'AppTools\AppTests\TestJob' => [ 'vla' => 'Falumba' ]
    ]);
    $released->shouldBeNumeric();
  }


  // Status method
  function it_returns_the_status_for_a_given_job_id() {
    $this->start();
    $job_id = $this->job( 'test', 'AppTools\AppTests\TestJob', [
      'vla' => 'Falumba'
    ]);
    // NOTE: this is an async process and returns false in the test environment
    $this->status( $job_id )->shouldBe( false );
  }


  // Working method
  function it_tests_if_the_workers_are_active() {
    $this->start()->shouldBeNumeric();
    $this->working()->shouldBe( true );
  }

  function it_tests_if_the_workers_are_inactive() {
    $this->working()->shouldBe( false );
  }


  // PidFile method
  function it_returns_the_full_path_to_the_pid_file() {
    $this->pidFile()->shouldEndWith( App::root( 'pids/dev-worker.pid' ));
  }


  // Pid method
  function it_returns_the_process_id_if_workers_are_active() {
    $this->start();
    $this->pid()->shouldBeNumeric();
  }

  function it_returns_nothing_if_workers_are_inactive() {
    $this->pid()->shouldBeNull();
  }

  function it_returns_nothing_if_there_is_a_pid_file_but_no_workers_are_active() {
    $pid = $this->start()->getWrappedObject();
    exec( "kill $pid" );
    usleep( 3.5e5 );
    $this->pid()->shouldBeNull();
  }


  // Count method
  function it_returns_the_number_of_instances_that_should_be_started() {
    return $this->count()->shouldBe( 5 );
  }


}
