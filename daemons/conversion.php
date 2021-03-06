<?php

ini_set('display_errors', 'On');
error_reporting(-1);
ini_set('log_errors', 'On');
ini_set('error_log', __DIR__ . '/conversion.errors');

require_once dirname(__DIR__) . '/config.php';
require MODEL_DIRECTORY . '/Conversion.php';
require MODEL_DIRECTORY . '/Logs.php';

global $database;

$settings = new Settings($database);

if (count($argv) >=2 ) {
  foreach ($argv as $key => $argument) {
    if (strstr($argument, '=')) {
      $crumbs = array_filter(explode('=', $argument));
      if (isset($crumbs[0]) && isset($crumbs[1])) {
          $variable = $crumbs[0];
          $value = $crumbs[1];
          $$variable = $value;
      }
    }
  }

  $base = $directory . '/' . $filename;
  $path = TEMPORARY_DIRECTORY . '/' . $base . '.' . $extension;
  $log = LOGS_DIRECTORY . '/' . $base . '.log';
  $logs = new Logs($log);
  $conversion = new Conversion($filename, $directory, $path, $logs);
  $results = $conversion->process();

  $status = 'successful';
  if ($results['details']) {
    $duration = $results['details']['duration'];
    $qualities = $results['details']['possibleQualities'];
  }

  if ($results['files']) {
    foreach ($results['files'] as $key => $file) {
      if ($file['status'] != 'success') {
        $status = $file['status'];
        break;
      }
    }
  }

  $fields = array('duration' => $duration, 'status' => $status, 'qualities' => $qualities);

  $videos = new Videos($database);
  $videos->setFields($fields, $filename, 'filename');

  @unlink($path);
}