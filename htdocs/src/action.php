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
        $begin = !empty($_REQUEST['begin']) ? $_REQUEST['begin'] : null;
        $end = !empty($_REQUEST['end']) ? $_REQUEST['end'] : null;
        $output['success'] = $continuum->createUser($_REQUEST['username'], $_REQUEST['password'], $_REQUEST['first_name'], $last_name, $pushover_user, $pushover_token, $pushover_priority, $pushover_retry, $pushover_expire, $pushover_sound, $_REQUEST['role'], $begin, $end);
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
  case 'createEdge':
    if ($continuum->isValidSession() && $continuum->isAdmin()) {
      if (!empty($_REQUEST['name']) && !empty($_REQUEST['url']) && !empty($_REQUEST['api_key'])) {
        $color = !empty($_REQUEST['color']) ? $_REQUEST['color'] : null;
        $output['success'] = $continuum->createEdge($_REQUEST['name'], $color, $_REQUEST['url'], $_REQUEST['api_key']);
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
  case 'createMonitor':
    if ($continuum->isValidSession() && $continuum->isAdmin()) {
      if (!empty($_REQUEST['name']) && !empty($_REQUEST['url']) && !empty($_REQUEST['method']) && !empty($_REQUEST['edges']) && !empty($_REQUEST['interval']) && !empty($_REQUEST['timeout']) && isset($_REQUEST['allow_redirects']) && isset($_REQUEST['verify'])) {
        $output['success'] = $continuum->createMonitor($_REQUEST['name'], $_REQUEST['url'], $_REQUEST['method'], $_REQUEST['edges'], $_REQUEST['interval'], $_REQUEST['timeout'], $_REQUEST['allow_redirects'], $_REQUEST['verify']);
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
  case 'createApp':
    if ($continuum->isValidSession() && $continuum->isAdmin()) {
      if (!empty($_REQUEST['name'])) {
        $token = isset($_REQUEST['token']) ? $_REQUEST['token'] : null;
        $begin = !empty($_REQUEST['begin']) ? $_REQUEST['begin'] : null;
        $end = !empty($_REQUEST['end']) ? $_REQUEST['end'] : null;
        $output['success'] = $continuum->createApp($_REQUEST['name'], $token, $begin, $end);
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
        $begin = !empty($_REQUEST['begin']) ? $_REQUEST['begin'] : null;
        $end = !empty($_REQUEST['end']) ? $_REQUEST['end'] : null;
        $output['success'] = $continuum->updateUser($_REQUEST['user_id'], $_REQUEST['username'], $password, $_REQUEST['first_name'], $last_name, $pushover_user, $pushover_token, $pushover_priority, $pushover_retry, $pushover_expire, $pushover_sound, $_REQUEST['role'], $begin, $end);
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
  case 'updateEdge':
    if ($continuum->isValidSession() && $continuum->isAdmin()) {
      if (!empty($_REQUEST['edge_id']) && !empty($_REQUEST['name']) && !empty($_REQUEST['url']) && !empty($_REQUEST['api_key'])) {
        $color = !empty($_REQUEST['color']) ? $_REQUEST['color'] : null;
        $output['success'] = $continuum->updateEdge($_REQUEST['edge_id'], $_REQUEST['name'], $color, $_REQUEST['url'], $_REQUEST['api_key']);
        $log['edge_id'] = $_REQUEST['edge_id'];
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
  case 'updateMonitor':
    if ($continuum->isValidSession() && $continuum->isAdmin()) {
      if (!empty($_REQUEST['monitor_id']) && !empty($_REQUEST['name']) && !empty($_REQUEST['url']) && !empty($_REQUEST['method']) && !empty($_REQUEST['edges']) && !empty($_REQUEST['interval']) && !empty($_REQUEST['timeout']) && isset($_REQUEST['allow_redirects']) && isset($_REQUEST['verify'])) {
        $output['success'] = $continuum->updateMonitor($_REQUEST['monitor_id'], $_REQUEST['name'], $_REQUEST['url'], $_REQUEST['method'], $_REQUEST['edges'], $_REQUEST['interval'], $_REQUEST['timeout'], $_REQUEST['allow_redirects'], $_REQUEST['verify']);
        $log['monitor_id'] = $_REQUEST['monitor_id'];
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
  case 'updateApp':
    if ($continuum->isValidSession() && $continuum->isAdmin()) {
      if (!empty($_REQUEST['app_id']) && !empty($_REQUEST['name']) && !empty($_REQUEST['token'])) {
        $begin = !empty($_REQUEST['begin']) ? $_REQUEST['begin'] : null;
        $end = !empty($_REQUEST['end']) ? $_REQUEST['end'] : null;
        $output['success'] = $continuum->updateApp($_REQUEST['app_id'], $_REQUEST['name'], $_REQUEST['token'], $begin, $end);
        $log['app_id'] = $_REQUEST['app_id'];
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
  case 'getReadings':
    if ($continuum->isValidSession() || (array_key_exists('token', $_REQUEST) && $continuum->isValidObject('token', $_REQUEST['token']))) {
      if (!empty($_REQUEST['monitor_id']) && !empty($_REQUEST['hours']) && isset($_REQUEST['type'])) {
        if ($output['data'] = $continuum->getReadings($_REQUEST['monitor_id'], $_REQUEST['hours'], $_REQUEST['type'])) {
          $output['success'] = true;
          $putEvent = false;
        } else {
          $output['success'] = false;
          $log['monitor_id'] = $_REQUEST['monitor_id'];
          $log['hours'] = $_REQUEST['hours'];
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
  $user_id = array_key_exists('authenticated', $_SESSION) ? $_SESSION['user_id'] : null;
  $continuum->putEvent($user_id, $_REQUEST['func'], array_merge(array_intersect_key($output, $logFields), $log));
}

header('Content-Type: application/json');
echo json_encode($output);
?>
