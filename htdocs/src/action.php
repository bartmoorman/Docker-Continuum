<?php
require_once('../inc/continuum.class.php');
$continuum = new Continuum(false, false, false, false);

$output = $logFields = ['success' => null, 'message' => null];
$log = [];
$putEvent = true;

switch ($_REQUEST['func']) {
  case 'authenticateSession':
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
      $output['success'] = $continuum->authenticateSession($_POST['username'], $_POST['password']);
      $log['username'] = $_POST['username'];
      usleep(rand(750000, 1000000));
    } else {
      header('HTTP/1.1 400 Bad Request');
      $output['success'] = false;
      $output['message'] = 'Missing arguments';
    }
    break;
  case 'createUser':
    if (!$continuum->isConfigured() || ($continuum->isValidSession() && $continuum->isAdmin())) {
      if (!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['first_name']) && !empty($_POST['role'])) {
        $last_name = !empty($_POST['last_name']) ? $_POST['last_name'] : null;
        $pushover_user = !empty($_POST['pushover_user']) ? $_POST['pushover_user'] : null;
        $pushover_token = !empty($_POST['pushover_token']) ? $_POST['pushover_token'] : null;
        $pushover_priority = isset($_POST['pushover_priority']) ? $_POST['pushover_priority'] : null;
        $pushover_retry = isset($_POST['pushover_retry']) ? $_POST['pushover_retry'] : null;
        $pushover_expire = isset($_POST['pushover_expire']) ? $_POST['pushover_expire'] : null;
        $pushover_sound = !empty($_POST['pushover_sound']) ? $_POST['pushover_sound'] : null;
        $begin = !empty($_POST['begin']) ? $_POST['begin'] : null;
        $end = !empty($_POST['end']) ? $_POST['end'] : null;
        $output['success'] = $continuum->createUser($_POST['username'], $_POST['password'], $_POST['first_name'], $last_name, $pushover_user, $pushover_token, $pushover_priority, $pushover_retry, $pushover_expire, $pushover_sound, $_POST['role'], $begin, $end);
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
      if (!empty($_POST['name']) && !empty($_POST['url']) && !empty($_POST['api_key'])) {
        $color = !empty($_POST['color']) ? $_POST['color'] : null;
        $output['success'] = $continuum->createEdge($_POST['name'], $color, $_POST['url'], $_POST['api_key']);
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
  case 'createMonitor':
    if ($continuum->isValidSession() && $continuum->isAdmin()) {
      if (!empty($_POST['name']) && !empty($_POST['url']) && !empty($_POST['method']) && !empty($_POST['edges']) && !empty($_POST['interval']) && !empty($_POST['timeout']) && isset($_POST['allow_redirects']) && isset($_POST['verify'])) {
        $output['success'] = $continuum->createMonitor($_POST['name'], $_POST['url'], $_POST['method'], $_POST['edges'], $_POST['interval'], $_POST['timeout'], $_POST['allow_redirects'], $_POST['verify']);
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
  case 'createApp':
    if ($continuum->isValidSession() && $continuum->isAdmin()) {
      if (!empty($_POST['name'])) {
        $token = isset($_POST['token']) ? $_POST['token'] : null;
        $begin = !empty($_POST['begin']) ? $_POST['begin'] : null;
        $end = !empty($_POST['end']) ? $_POST['end'] : null;
        $output['success'] = $continuum->createApp($_POST['name'], $token, $begin, $end);
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
      if (!empty($_POST['user_id']) && !empty($_POST['username']) && !empty($_POST['first_name']) && !empty($_POST['role'])) {
        $password = !empty($_POST['password']) ? $_POST['password'] : null;
        $last_name = !empty($_POST['last_name']) ? $_POST['last_name'] : null;
        $pushover_user = !empty($_POST['pushover_user']) ? $_POST['pushover_user'] : null;
        $pushover_token = !empty($_POST['pushover_token']) ? $_POST['pushover_token'] : null;
        $pushover_priority = isset($_POST['pushover_priority']) ? $_POST['pushover_priority'] : null;
        $pushover_retry = isset($_POST['pushover_retry']) ? $_POST['pushover_retry'] : null;
        $pushover_expire = isset($_POST['pushover_expire']) ? $_POST['pushover_expire'] : null;
        $pushover_sound = !empty($_POST['pushover_sound']) ? $_POST['pushover_sound'] : null;
        $begin = !empty($_POST['begin']) ? $_POST['begin'] : null;
        $end = !empty($_POST['end']) ? $_POST['end'] : null;
        $output['success'] = $continuum->updateUser($_POST['user_id'], $_POST['username'], $password, $_POST['first_name'], $last_name, $pushover_user, $pushover_token, $pushover_priority, $pushover_retry, $pushover_expire, $pushover_sound, $_POST['role'], $begin, $end);
        $log['user_id'] = $_POST['user_id'];
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
      if (!empty($_POST['edge_id']) && !empty($_POST['name']) && !empty($_POST['url']) && !empty($_POST['api_key'])) {
        $color = !empty($_POST['color']) ? $_POST['color'] : null;
        $output['success'] = $continuum->updateEdge($_POST['edge_id'], $_POST['name'], $color, $_POST['url'], $_POST['api_key']);
        $log['edge_id'] = $_POST['edge_id'];
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
      if (!empty($_POST['monitor_id']) && !empty($_POST['name']) && !empty($_POST['url']) && !empty($_POST['method']) && !empty($_POST['edges']) && !empty($_POST['interval']) && !empty($_POST['timeout']) && isset($_POST['allow_redirects']) && isset($_POST['verify'])) {
        $output['success'] = $continuum->updateMonitor($_POST['monitor_id'], $_POST['name'], $_POST['url'], $_POST['method'], $_POST['edges'], $_POST['interval'], $_POST['timeout'], $_POST['allow_redirects'], $_POST['verify']);
        $log['monitor_id'] = $_POST['monitor_id'];
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
      if (!empty($_POST['app_id']) && !empty($_POST['name']) && !empty($_POST['token'])) {
        $begin = !empty($_POST['begin']) ? $_POST['begin'] : null;
        $end = !empty($_POST['end']) ? $_POST['end'] : null;
        $output['success'] = $continuum->updateApp($_POST['app_id'], $_POST['name'], $_POST['token'], $begin, $end);
        $log['app_id'] = $_POST['app_id'];
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
      if (!empty($_POST['action']) && !empty($_POST['type']) && !empty($_POST['value'])) {
        $output['success'] = $continuum->modifyObject($_POST['action'], $_POST['type'], $_POST['value']);
        $log['action'] = $_POST['action'];
        $log['type'] = $_POST['type'];
        $log['value'] = $_POST['value'];
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
