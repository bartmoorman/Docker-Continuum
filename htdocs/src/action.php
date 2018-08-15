<?php
require_once('../inc/continuum.class.php');
$continuum = new Continuum(false, false, false, false);

$output = $logFields = ['success' => null, 'message' => null];
$log = [];
$putEvent = true;

switch ($_REQUEST['func']) {
  case 'authenticateSession':
    if (!empty($_REQUEST['username']) && !empty($_REQUEST['password'])) {
      $output['success'] = $continuum->authenticateSession($_REQUEST['username'], $_REQUEST['password']);
      $log['username'] = $_REQUEST['username'];
    } else {
      header('HTTP/1.1 400 Bad Request');
      $output['success'] = false;
      $output['message'] = 'Missing arguments';
    }
    break;
  case 'createUser':
    if (!$continuum->isConfigured() || ($continuum->isValidSession() && $continuum->isAdmin())) {
      if (!empty($_REQUEST['username']) && !empty($_REQUEST['password']) && !empty($_REQUEST['first_name']) && !empty($_REQUEST['role'])) {
        $last_name = !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : null;
        $pushover_user = !empty($_REQUEST['pushover_user']) ? $_REQUEST['pushover_user'] : null;
        $pushover_token = !empty($_REQUEST['pushover_token']) ? $_REQUEST['pushover_token'] : null;
        $pushover_priority = isset($_REQUEST['pushover_priority']) ? $_REQUEST['pushover_priority'] : null;
        $pushover_retry = isset($_REQUEST['pushover_retry']) ? $_REQUEST['pushover_retry'] : null;
        $pushover_expire = isset($_REQUEST['pushover_expire']) ? $_REQUEST['pushover_expire'] : null;
        $pushover_sound = !empty($_REQUEST['pushover_sound']) ? $_REQUEST['pushover_sound'] : null;
        $output['success'] = $continuum->createUser($_REQUEST['username'], $_REQUEST['password'], $_REQUEST['first_name'], $last_name, $pushover_user, $pushover_token, $pushover_priority, $pushover_retry, $pushover_expire, $pushover_sound, $_REQUEST['role']);
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      header('HTTP/1.1 403 Forbidden');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'createEndpoint':
    if ($continuum->isValidSession() && $continuum->isAdmin()) {
      if (!empty($_REQUEST['name']) && !empty($_REQUEST['url']) && !empty($_REQUEST['api_key'])) {
        $output['success'] = $continuum->createEndpoint($_REQUEST['name'], $_REQUEST['url'], $_REQUEST['api_key']);
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'No name supplied';
      }
    } else {
      header('HTTP/1.1 403 Forbidden');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'updateUser':
    if ($continuum->isValidSession() && $continuum->isAdmin()) {
      if (!empty($_REQUEST['user_id']) && !empty($_REQUEST['username']) && !empty($_REQUEST['first_name']) && !empty($_REQUEST['role'])) {
        $password = !empty($_REQUEST['password']) ? $_REQUEST['password'] : null;
        $last_name = !empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : null;
        $pushover_user = !empty($_REQUEST['pushover_user']) ? $_REQUEST['pushover_user'] : null;
        $pushover_token = !empty($_REQUEST['pushover_token']) ? $_REQUEST['pushover_token'] : null;
        $pushover_priority = isset($_REQUEST['pushover_priority']) ? $_REQUEST['pushover_priority'] : null;
        $pushover_retry = isset($_REQUEST['pushover_retry']) ? $_REQUEST['pushover_retry'] : null;
        $pushover_expire = isset($_REQUEST['pushover_expire']) ? $_REQUEST['pushover_expire'] : null;
        $pushover_sound = !empty($_REQUEST['pushover_sound']) ? $_REQUEST['pushover_sound'] : null;
        $output['success'] = $continuum->updateUser($_REQUEST['user_id'], $_REQUEST['username'], $password, $_REQUEST['first_name'], $last_name, $pushover_user, $pushover_token, $pushover_priority, $pushover_retry, $pushover_expire, $pushover_sound, $_REQUEST['role']);
        $log['user_id'] = $_REQUEST['user_id'];
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      header('HTTP/1.1 403 Forbidden');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'updateEndpoint':
    if ($continuum->isValidSession() && $continuum->isAdmin()) {
      if (!empty($_REQUEST['endpoint_id']) && !empty($_REQUEST['name']) && !empty($_REQUEST['url']) && !empty($_REQUEST['api_key'])) {
        $output['success'] = $continuum->updateEndpoint($_REQUEST['endpoint_id'], $_REQUEST['name'], $_REQUEST['url'], $_REQUEST['api_key']);
        $log['endpoint_id'] = $_REQUEST['endpoint_id'];
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      header('HTTP/1.1 403 Forbidden');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'modifyObject':
    if ($continuum->isValidSession() && $continuum->isAdmin()) {
      if (!empty($_REQUEST['action']) && !empty($_REQUEST['type']) && !empty($_REQUEST['value'])) {
        $output['success'] = $continuum->modifyObject($_REQUEST['action'], $_REQUEST['type'], $_REQUEST['value']);
        $log['action'] = $_REQUEST['action'];
        $log['type'] = $_REQUEST['type'];
        $log['value'] = $_REQUEST['value'];
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      header('HTTP/1.1 403 Forbidden');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
  case 'getObjectDetails':
    if ($continuum->isValidSession() && $continuum->isAdmin()) {
      if (!empty($_REQUEST['type']) && !empty($_REQUEST['value'])) {
        if ($output['data'] = $continuum->getObjectDetails($_REQUEST['type'], $_REQUEST['value'])) {
          $output['success'] = true;
          $putEvent = false;
        } else {
          $output['success'] = false;
          $log['type'] = $_REQUEST['type'];
          $log['value'] = $_REQUEST['value'];
        }
      } else {
        header('HTTP/1.1 400 Bad Request');
        $output['success'] = false;
        $output['message'] = 'Missing arguments';
      }
    } else {
      header('HTTP/1.1 403 Forbidden');
      $output['success'] = false;
      $output['message'] = 'Unauthorized';
    }
    break;
}

if ($putEvent) {
  $continuum->putEvent($_REQUEST['func'], array_merge(array_intersect_key($output, $logFields), $log));
}

header('Content-Type: application/json');
echo json_encode($output);
?>
