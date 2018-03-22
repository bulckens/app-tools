<?php

namespace Bulckens\AppTools;

use Resque;
use Resque_Job_Status;

class Worker {

  use Traits\Configurable;

  // Start the workers
  public function start( $queue = '*' ) {
    if ( ! $this->working()) {
      // gather settings
      $pid_file = $this->pidFile();
      $pid_dir = dirname( $pid_file );
      $prefix = $this->config( 'namespace', 'app_tools:worker' );
      $env = App::env();
      $count = $this->config( 'workers', 5 );
      $resque = App::root( 'vendor/bin/resque' );

      // make sure pid directory exists
      if ( ! file_exists( $pid_dir )) {
        exec( "mkdir -p $pid_dir" );
      }

      // execute command
      $command = "PIDFILE='$pid_file' PREFIX='$prefix:$env' QUEUE='$queue' COUNT=$count $resque";
      exec( "$command </dev/null &>/dev/null &" );

      // wait half a second for the process to be started
      usleep( 5e5 );
    }

    return $this->pid();
  }


  // Stop the workers
  public function stop() {
    if ( $pid = $this->pid()) {
      exec( "kill $pid" );

      if ( file_exists( $file = $this->pidFile())) {
        unlink( $file );
      }

      return $pid;
    }
  }


  // Restart the workers
  public function restart() {
    $this->stop();
    return $this->start();
  }


  // Test if workers are active
  public function working() {
    return !! $this->pid();
  }


  // Get the pid file path
  public function pidFile() {
    return App::root( 'pids/worker.pid' );
  }


  // Get process ID
  public function pid() {
    if ( file_exists( $file = $this->pidFile())) {
      return file_get_contents( $file );
    }
  }


  // Add job to worker
  public function job( $queue, $class, $args ) {
    // ensure argumens as array
    if ( ! is_array( $args )) $args = [];

    // add environment to arguments
    $args['app_env'] = App::env();

    return Resque::enqueue( $queue, $class, $args );
  }


  // Release worker from a job
  public function release( $queue, $job = null ) {
    // make sure job is an array
    if ( is_string( $job )) $job = [ $job ];

    return Resque::dequeue( $queue, $job );
  }


  // Get the status for a given job id
  public function status( $job_id ) {
    $status = new Resque_Job_Status( $job_id );

    switch ( $value = $status->get()) {
      case Resque_Job_Status::STATUS_WAITING:  return 'waiting';  break;
      case Resque_Job_Status::STATUS_RUNNING:  return 'running';  break;
      case Resque_Job_Status::STATUS_FAILED:   return 'failed';   break;
      case Resque_Job_Status::STATUS_COMPLETE: return 'complete'; break;
      default:
        return $value;
      break;
    }
  }

}
