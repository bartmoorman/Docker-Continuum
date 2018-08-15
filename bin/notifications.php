#!/usr/bin/php
<?php
require_once('/var/www/html/inc/continuum.class.php');
$continuum = new Continuum(false, false, false, false);

while (true) {
  $messages = [];
  while (msg_receive($continuum->queueConn, 0, $msgtype, $continuum->queueSize, $message, true, MSG_IPC_NOWAIT)) {
    $messages[] = $message;
  }
  $continuum->sendNotifications($messages);
  sleep(5);
}
?>
