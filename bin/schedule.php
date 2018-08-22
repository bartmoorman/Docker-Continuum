#!/usr/bin/php
<?php
require_once('/var/www/html/inc/continuum.class.php');
$continuum = new Continuum(false, false, false, false);

$pids = [];
while (true) {
  $continuum->memcacheConn->set('lastRun', time());
  foreach ($continuum->getObjects('monitors') as $monitor) {
    if (!$monitor['disabled'] && !$continuum->memcacheConn->get(sprintf('lastRun-%u', $monitor['monitor_id']))) {
      $continuum->memcacheConn->set(sprintf('lastRun-%u', $monitor['monitor_id']), time(), 60 * $monitor['interval']);
      foreach ($continuum->getRandomEdges($monitor['edges']) as $edge) {
        switch ($pid = pcntl_fork()) {
          case -1:
            echo date('Y-m-d H:i:s') . " - could not fork (monitor_id: {$monitor['monitor_id']}, edge_id: {$edge['edge_id']})" . PHP_EOL;
            break;
          case 0:
            $ch = curl_init($edge['url']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-API-Key: {$edge['api_key']}"]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $monitor['url'], 'timeout' => $monitor['timeout'], 'allow_redirects' => (bool) $monitor['allow_redirects'], 'verify' => (bool) $monitor['verify']]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (($result = curl_exec($ch)) !== false && curl_getinfo($ch, CURLINFO_RESPONSE_CODE) == 200) {
              $json = json_decode($result, true);
              $continuum->putReading($edge['edge_id'], $monitor['monitor_id'], array_key_exists('total_seconds', $json) ? $json['total_seconds'] : null, array_key_exists('status_code', $json) ? $json['status_code'] : null, $json['reason']);
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
