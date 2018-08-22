#!/usr/bin/php
<?php
require_once('/var/www/html/inc/continuum.class.php');
$continuum = new Continuum(false, false, false, false);

$pids = [];
while (true) {
  $continuum->memcacheConn->set('lastRun', time());
  foreach ($continuum->getObjects('endpoints') as $endpoint) {
    if ($endpoint['disabled']) continue;
    $ch = curl_init($endpoint['url']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-API-Key: {$endpoint['api_key']}"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    foreach ($continuum->getObjects('monitors') as $monitor) {
      if ($monitor['disabled']) continue;
      if (!$continuum->memcacheConn->get(sprintf('endpoint%u-monitor%u', $endpoint['endpoint_id'], $monitor['monitor_id']))) {
        switch ($pid = pcntl_fork()) {
          case -1:
            die('could not fork');
            break;
          case 0:
            $continuum->memcacheConn->set(sprintf('endpoint%u-monitor%u', $endpoint['endpoint_id'], $monitor['monitor_id']), time(), 60 * $monitor['interval']);
            $payload = ['url' => $monitor['url'], 'timeout' => $monitor['timeout'], 'allow_redirects' => (bool) $monitor['allow_redirects'], 'verify' => (bool) $monitor['verify']];
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            if (($result = curl_exec($ch)) !== false && curl_getinfo($ch, CURLINFO_RESPONSE_CODE) == 200) {
              $json = json_decode($result, true);
              $continuum->putReading($endpoint['endpoint_id'], $monitor['monitor_id'], array_key_exists('total_seconds', $json) ? $json['total_seconds'] : null, array_key_exists('status_code', $json) ? $json['status_code'] : null, $json['reason']);
            }
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
