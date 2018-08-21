#!/usr/bin/php
<?php
require_once('/var/www/html/inc/continuum.class.php');
$continuum = new Continuum(false, false, false, false);

$pids = [];
while (true) {
  $continuum->memcacheConn->set('lastRun', time());
  foreach ($continuum->getObjects('endpoints') as $endpoint) {
    if ($endpoint['disabled']) continue;
    foreach ($continuum->getObjects('monitors') as $monitor) {
      if ($monitor['disabled']) continue;
      if (!$continuum->memcacheConn->get(sprintf('endpoint%u-monitor%u', $endpoint['endpoint_id'], $monitor['monitor_id']))) {
        switch ($pid = pcntl_fork()) {
          case -1:
            die('could not fork');
            break;
          case 0:
            $continuum->memcacheConn->set(sprintf('endpoint%u-monitor%u', $endpoint['endpoint_id'], $monitor['monitor_id']), time(), 60 * $monitor['interval']);
            sleep(rand(1,4));
            exit;
            break;
          default:
            $pids[] = $pid;
        }
      }
    }
  }
  foreach ($pids as $key => $cpid) {
    if (pcntl_waitpid($cpid, $status, WNOHANG)) unset($pids[$key]);
  }
  sleep(15);
}
?>
