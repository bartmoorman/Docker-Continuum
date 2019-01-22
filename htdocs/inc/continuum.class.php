<?php
class Continuum {
  private $dbFile = '/config/continuum.db';
  private $dbConn;
  public $memcacheConn;
  private $queueKey = 6082;
  public $queueSize = 512;
  public $queueConn;
  private $pushoverAppToken;
  public $pageLimit = 20;

  public function __construct($requireConfigured = true, $requireValidSession = true, $requireAdmin = true, $requireIndex = false) {
    session_start([
      'save_path' => '/config/sessions',
      'name' => '_sess_continuum',
      'gc_maxlifetime' => 60 * 60 * 24 * 7,
      'cookie_lifetime' => 60 * 60 * 24 * 7,
      'cookie_secure' => true,
      'cookie_httponly' => true,
      'use_strict_mode' => true
    ]);

    if (is_writable($this->dbFile)) {
      $this->connectDb();
    } elseif (is_writable(dirname($this->dbFile))) {
      $this->connectDb();
      $this->initDb();
    }

    $this->connectMemcache();

    $this->connectQueue();

    if ($this->isConfigured()) {
      if ($this->isValidSession()) {
        if (($requireAdmin && !$this->isAdmin()) || $requireIndex) {
          header('Location: index.php');
          exit;
        }
      } elseif ($requireValidSession) {
        header('Location: login.php');
        exit;
      }
    } elseif ($requireConfigured) {
      header('Location: setup.php');
      exit;
    }

    $this->pushoverAppToken = getenv('PUSHOVER_APP_TOKEN');
  }

  private function connectDb() {
    if ($this->dbConn = new SQLite3($this->dbFile)) {
      $this->dbConn->busyTimeout(500);
      $this->dbConn->exec('PRAGMA journal_mode = WAL');
      return true;
    }
    return false;
  }

  private function initDb() {
    $query = <<<EOQ
CREATE TABLE IF NOT EXISTS `config` (
  `config_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `key` TEXT NOT NULL UNIQUE,
  `value` TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `username` TEXT NOT NULL UNIQUE,
  `password` TEXT NOT NULL,
  `first_name` TEXT NOT NULL,
  `last_name` TEXT,
  `pushover_user` TEXT,
  `pushover_token` TEXT,
  `pushover_priority` INTEGER DEFAULT 0,
  `pushover_retry` INTEGER DEFAULT 60,
  `pushover_expire` INTEGER DEFAULT 3600,
  `pushover_sound` TEXT,
  `role` TEXT NOT NULL,
  `disabled` INTEGER NOT NULL DEFAULT 0
);
CREATE TABLE IF NOT EXISTS `events` (
  `event_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `date` INTEGER DEFAULT (STRFTIME('%s', 'now')),
  `user_id` INTEGER,
  `action` TEXT,
  `message` BLOB,
  `remote_addr` INTEGER
);
CREATE TABLE IF NOT EXISTS `edges` (
  `edge_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL,
  `url` TEXT NOT NULL,
  `api_key` TEXT NOT NULL,
  `disabled` INTEGER NOT NULL DEFAULT 0
);
CREATE TABLE IF NOT EXISTS `monitors` (
  `monitor_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL,
  `url` TEXT NOT NULL,
  `edges` INTEGER NOT NULL DEFAULT 2,
  `interval` INTEGER NOT NULL DEFAULT 5,
  `timeout` NUMERIC NOT NULL DEFAULT 1.0,
  `allow_redirects` INTEGER NOT NULL DEFAULT 1,
  `verify` INTEGER NOT NULL DEFAULT 1,
  `disabled` INTEGER NOT NULL DEFAULT 0
);
CREATE TABLE IF NOT EXISTS `readings` (
  `reading_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `date` INTEGER DEFAULT (STRFTIME('%s', 'now')),
  `edge_id` INTEGER NOT NULL,
  `monitor_id` INTEGER NOT NULL,
  `total_seconds` NUMERIC,
  `status_code` INTEGER,
  `reason` TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS `apps` (
  `app_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL,
  `token` TEXT NOT NULL UNIQUE,
  `begin` INTEGER,
  `end` INTEGER,
  `disabled` INTEGER NOT NULL DEFAULT 0
);
CREATE TABLE IF NOT EXISTS `calls` (
  `call_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `date` INTEGER DEFAULT (STRFTIME('%s', 'now')),
  `app_id` INTEGER NOT NULL,
  `action` TEXT,
  `message` BLOB,
  `remote_addr` INTEGER
);
EOQ;
    if ($this->dbConn->exec($query)) {
      return true;
    }
    return false;
  }

  private function connectMemcache() {
    if ($this->memcacheConn = new Memcached()) {
      $this->memcacheConn->addServer('localhost', null);
      return true;
    }
    return false;
  }

  private function connectQueue() {
    if ($this->queueConn = msg_get_queue($this->queueKey)) {
      return true;
    }
    return false;
  }

  public function isConfigured() {
    if ($this->getObjectCount('users')) {
      return true;
    }
    return false;
  }

  public function isValidSession() {
    if (array_key_exists('authenticated', $_SESSION) && $this->isValidObject('user_id', $_SESSION['user_id'])) {
      return true;
    }
    return false;
  }

  public function isAdmin() {
    $user_id = $_SESSION['user_id'];
    $query = <<<EOQ
SELECT COUNT(*)
FROM `users`
WHERE `user_id` = '{$user_id}'
AND `role` = 'admin';
EOQ;
    if ($this->dbConn->querySingle($query)) {
      return true;
    }
    return false;
  }

  public function isValidCredentials($username, $password) {
    $username = $this->dbConn->escapeString($username);
    $query = <<<EOQ
SELECT `password`
FROM `users`
WHERE `username` = '{$username}'
EOQ;
    if (password_verify($password, $this->dbConn->querySingle($query))) {
      return true;
    }
    return false;
  }

  public function isValidObject($type, $value) {
    $type = $this->dbConn->escapeString($type);
    $value = $this->dbConn->escapeString($value);
    switch ($type) {
      case 'username':
      case 'user_id':
        $table = 'users';
        break;
      case 'edge_id':
        $table = 'edges';
        break;
      case 'monitor_id':
        $table = 'monitors';
        break;
      case 'token':
      case 'app_id':
        $table = 'apps';
        break;
    }
    $query = <<<EOQ
SELECT COUNT(*)
FROM `{$table}`
WHERE `{$type}` = '{$value}'
AND NOT `disabled`;
EOQ;
    if ($this->dbConn->querySingle($query)) {
      return true;
    }
    return false;
  }

  public function resolveObject($type, $value) {
    $type = $this->dbConn->escapeString($type);
    $value = $this->dbConn->escapeString($value);
    switch ($type) {
      case 'token':
        $column = 'app_id';
        $table = 'apps';
        break;
    }
    $query = <<<EOQ
SELECT `{$column}`
FROM `{$table}`
WHERE `{$type}` = '{$value}';
EOQ;
    if ($object_id = $this->dbConn->querySingle($query)) {
      return $object_id;
    }
    return false;
  }

  public function authenticateSession($username, $password) {
    if ($this->isValidCredentials($username, $password)) {
      $username = $this->dbConn->escapeString($username);
      $query = <<<EOQ
SELECT `user_id`
FROM `users`
WHERE `username` = '{$username}';
EOQ;
      if ($user_id = $this->dbConn->querySingle($query)) {
        $_SESSION['authenticated'] = true;
        $_SESSION['user_id'] = $user_id;
        return true;
      }
    }
    return false;
  }

  public function deauthenticateSession() {
    if (session_destroy()) {
      return true;
    }
    return false;
  }

  public function createUser($username, $password, $first_name, $last_name = null, $pushover_user = null, $pushover_token = null, $pushover_priority = null, $pushover_retry = null, $pushover_expire = null, $pushover_sound = null, $role) {
    $username = $this->dbConn->escapeString($username);
    $query = <<<EOQ
SELECT COUNT(*)
FROM `users`
WHERE `username` = '{$username}';
EOQ;
    if (!$this->dbConn->querySingle($query)) {
      $password = password_hash($password, PASSWORD_DEFAULT);
      $first_name = $this->dbConn->escapeString($first_name);
      $last_name = $this->dbConn->escapeString($last_name);
      $pushover_user = $this->dbConn->escapeString($pushover_user);
      $pushover_token = $this->dbConn->escapeString($pushover_token);
      $pushover_priority = $this->dbConn->escapeString($pushover_priority);
      $pushover_retry = $this->dbConn->escapeString($pushover_retry);
      $pushover_expire = $this->dbConn->escapeString($pushover_expire);
      $pushover_sound = $this->dbConn->escapeString($pushover_sound);
      $role = $this->dbConn->escapeString($role);
      $query = <<<EOQ
INSERT
INTO `users` (`username`, `password`, `first_name`, `last_name`, `pushover_user`, `pushover_token`, `pushover_priority`, `pushover_retry`, `pushover_expire`, `pushover_sound`, `role`)
VALUES ('{$username}', '{$password}', '{$first_name}', '{$last_name}', '{$pushover_user}', '{$pushover_token}', '{$pushover_priority}', '{$pushover_retry}', '{$pushover_expire}', '{$pushover_sound}', '{$role}');
EOQ;
      if ($this->dbConn->exec($query)) {
        return true;
      }
    }
    return false;
  }

  public function createEdge($name, $url, $api_key) {
    $url = $this->dbConn->escapeString($url);
    $query = <<<EOQ
SELECT COUNT(*)
FROM `edges`
WHERE `url` = '{$url}';
EOQ;
    if (!$this->dbConn->querySingle($query)) {
      $name = $this->dbConn->escapeString($name);
      $api_key = $this->dbConn->escapeString($api_key);
      $query = <<<EOQ
INSERT
INTO `edges` (`name`, `url`, `api_key`)
VALUES ('{$name}', '{$url}', '{$api_key}');
EOQ;
      if ($this->dbConn->exec($query)) {
        return true;
      }
    }
    return false;
  }

  public function createMonitor($name, $url, $edges, $interval, $timeout, $allow_redirects, $verify) {
    $url = $this->dbConn->escapeString($url);
    $query = <<<EOQ
SELECT COUNT(*)
FROM `monitors`
WHERE `url` = '{$url}';
EOQ;
    if (!$this->dbConn->querySingle($query)) {
      $name = $this->dbConn->escapeString($name);
      $edges = $this->dbConn->escapeString($edges);
      $interval = $this->dbConn->escapeString($interval);
      $timeout = $this->dbConn->escapeString($timeout);
      $allow_redirects = $this->dbConn->escapeString($allow_redirects);
      $verify = $this->dbConn->escapeString($verify);
      $query = <<<EOQ
INSERT
INTO `monitors` (`name`, `url`, `edges`, `interval`, `timeout`, `allow_redirects`, `verify`)
VALUES ('{$name}', '{$url}', '{$edges}', '{$interval}', '{$timeout}', '{$allow_redirects}', '{$verify}');
EOQ;
      if ($this->dbConn->exec($query)) {
        return true;
      }
    }
    return false;
  }

  public function createApp($name, $token = null, $begin = null, $end = null) {
    $token = !$token ? bin2hex(random_bytes(8)) : $this->dbConn->escapeString($token);
    $query = <<<EOQ
SELECT COUNT(*)
FROM `apps`
WHERE `token` = '{$token}';
EOQ;
    if (!$this->dbConn->querySingle($query)) {
      $name = $this->dbConn->escapeString($name);
      $begin = $this->dbConn->escapeString($begin);
      $end = $this->dbConn->escapeString($end);
      $query = <<<EOQ
INSERT
INTO `apps` (`name`, `token`, `begin`, `end`)
VALUES ('{$name}', '{$token}', STRFTIME('%s','{$begin}',) STRFTIME('%s','{$end}'));
EOQ;
      if ($this->dbConn->exec($query)) {
        return true;
      }
    }
    return false;
  }

  public function updateUser($user_id, $username, $password = null, $first_name, $last_name = null, $pushover_user = null, $pushover_token = null, $pushover_priority = null, $pushover_retry = null, $pushover_expire = null, $pushover_sound = null, $role) {
    $user_id = $this->dbConn->escapeString($user_id);
    $username = $this->dbConn->escapeString($username);
    $query = <<<EOQ
SELECT COUNT(*)
FROM `users`
WHERE `user_id` != '{$user_id}'
AND `username` = '{$username}';
EOQ;
    if (!$this->dbConn->querySingle($query)) {
      $passwordQuery = null;
      if (!empty($password)) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $passwordQuery = <<<EOQ
  `password` = '{$password}',
EOQ;
      }
      $first_name = $this->dbConn->escapeString($first_name);
      $last_name = $this->dbConn->escapeString($last_name);
      $pushover_user = $this->dbConn->escapeString($pushover_user);
      $pushover_token = $this->dbConn->escapeString($pushover_token);
      $pushover_priority = $this->dbConn->escapeString($pushover_priority);
      $pushover_retry = $this->dbConn->escapeString($pushover_retry);
      $pushover_expire = $this->dbConn->escapeString($pushover_expire);
      $pushover_sound = $this->dbConn->escapeString($pushover_sound);
      $role = $this->dbConn->escapeString($role);
      $query = <<<EOQ
UPDATE `users`
SET
  `username` = '{$username}',
{$passwordQuery}
  `first_name` = '{$first_name}',
  `last_name` = '{$last_name}',
  `pushover_user` = '{$pushover_user}',
  `pushover_token` = '{$pushover_token}',
  `pushover_priority` = '{$pushover_priority}',
  `pushover_retry` = '{$pushover_retry}',
  `pushover_expire` = '{$pushover_expire}',
  `pushover_sound` = '{$pushover_sound}',
  `role` = '{$role}'
WHERE `user_id` = '{$user_id}';
EOQ;
      if ($this->dbConn->exec($query)) {
        return true;
      }
    }
    return false;
  }

  public function updateEdge($edge_id, $name, $url, $api_key) {
    $edge_id = $this->dbConn->escapeString($edge_id);
    $url = $this->dbConn->escapeString($url);
    $query = <<<EOQ
SELECT COUNT(*)
FROM `edges`
WHERE `edge_id` != '{$edge_id}'
AND `url` = '{$url}';
EOQ;
    if (!$this->dbConn->querySingle($query)) {
      $name = $this->dbConn->escapeString($name);
      $api_key = $this->dbConn->escapeString($api_key);
      $query = <<<EOQ
UPDATE `edges`
SET
  `name` = '{$name}',
  `url` = '{$url}',
  `api_key` = '{$api_key}'
WHERE `edge_id` = '{$edge_id}';
EOQ;
      if ($this->dbConn->exec($query)) {
        return true;
      }
    }
    return false;
  }

  public function updateMonitor($monitor_id, $name, $url, $edges, $interval, $timeout, $allow_redirects, $verify) {
    $monitor_id = $this->dbConn->escapeString($monitor_id);
    $url = $this->dbConn->escapeString($url);
    $query = <<<EOQ
SELECT COUNT(*)
FROM `monitors`
WHERE `monitor_id` != '{$monitor_id}'
AND `url` = '{$url}';
EOQ;
    if (!$this->dbConn->querySingle($query)) {
      $name = $this->dbConn->escapeString($name);
      $edges = $this->dbConn->escapeString($edges);
      $interval = $this->dbConn->escapeString($interval);
      $timeout = $this->dbConn->escapeString($timeout);
      $allow_redirects = $this->dbConn->escapeString($allow_redirects);
      $verify = $this->dbConn->escapeString($verify);
      $query = <<<EOQ
UPDATE `monitors`
SET
  `name` = '{$name}',
  `url` = '{$url}',
  `edges` = '{$edges}',
  `interval` = '{$interval}',
  `timeout` = '{$timeout}',
  `allow_redirects` = '{$allow_redirects}',
  `verify` = '{$verify}'
WHERE `monitor_id` = '{$monitor_id}';
EOQ;
      if ($this->dbConn->exec($query)) {
        return true;
      }
    }
    return false;
  }

  public function updateApp($app_id, $name, $token, $begin, $end) {
    $app_id = $this->dbConn->escapeString($app_id);
    $token = $this->dbConn->escapeString($token);
    $query = <<<EOQ
SELECT COUNT(*)
FROM `apps`
WHERE `app_id` != '{$app_id}'
AND `token` = '{$token}';
EOQ;
    if (!$this->dbConn->querySingle($query)) {
      $name = $this->dbConn->escapeString($name);
      $begin = $this->dbConn->escapeString($begin);
      $end = $this->dbConn->escapeString($end);
      $query = <<<EOQ
UPDATE `apps`
SET
  `name` = '{$name}',
  `token` = '{$token}',
  `begin` = STRFTIME('%s', '{$begin}'),
  `end` = STRFTIME('%s', '{$end}')
WHERE `app_id` = '{$app_id}';
EOQ;
      if ($this->dbConn->exec($query)) {
        return true;
      }
    }
    return false;
  }

  public function modifyObject($action, $type, $value, $extra_type = null, $extra_value = null) {
    $type = $this->dbConn->escapeString($type);
    $value = $this->dbConn->escapeString($value);
    $extra_type = $this->dbConn->escapeString($extra_type);
    $extra_value = $this->dbConn->escapeString($extra_value);
    switch ($type) {
      case 'username':
      case 'user_id':
        $table = 'users';
        $extra_table = 'events';
        break;
      case 'edge_id':
        $table = 'edges';
        $extra_table = 'readings';
        break;
      case 'monitor_id':
        $table = 'monitors';
        $extra_table = 'readings';
        break;
      case 'token':
      case 'app_id':
        $table = 'apps';
        $extra_table = 'calls';
        break;
    }
    switch ($action) {
      case 'enable':
        $query = <<<EOQ
UPDATE `{$table}`
SET `disabled` = '0'
WHERE `{$type}` = '{$value}';
EOQ;
        break;
      case 'disable':
        $query = <<<EOQ
UPDATE `{$table}`
SET `disabled` = '1'
WHERE `{$type}` = '{$value}';
EOQ;
        break;
      case 'delete':
        $query = <<<EOQ
DELETE
FROM `{$table}`
WHERE `{$type}` = '{$value}';
DELETE
FROM `{$extra_table}`
WHERE `{$type}` = '{$value}';
EOQ;
        break;
    }
    if ($this->dbConn->exec($query)) {
      return true;
    }
    return false;
  }

  public function getObjects($type) {
    switch ($type) {
      case 'users':
        $query = <<<EOQ
SELECT `user_id`, `username`, `first_name`, `last_name`, `pushover_user`, `pushover_token`, `pushover_priority`, `pushover_retry`, `pushover_expire`, `pushover_sound`, `role`, `disabled`
FROM `users`
ORDER BY `last_name`, `first_name`;
EOQ;
        break;
      case 'edges':
        $query = <<<EOQ
SELECT `edge_id`, `name`, `url`, `api_key`, `disabled`
FROM `edges`
ORDER BY `name`;
EOQ;
        break;
      case 'monitors':
        $query = <<<EOQ
SELECT `monitor_id`, `name`, `url`, `edges`, `interval`, `timeout`, `allow_redirects`, `verify`, `disabled`
FROM `monitors`
ORDER BY `name`;
EOQ;
        break;
      case 'apps':
        $query = <<<EOQ
SELECT `app_id`, `name`, `token`, `begin`, `end`, `disabled`
FROM `apps`
ORDER BY `name`;
EOQ;
        break;
    }
    if ($objects = $this->dbConn->query($query)) {
      $output = [];
      while ($object = $objects->fetchArray(SQLITE3_ASSOC)) {
        $output[] = $object;
      }
      return $output;
    }
    return false;
  }

  public function getObjectDetails($type, $value) {
    $value = $this->dbConn->escapeString($value);
    switch ($type) {
      case 'user':
        $query = <<<EOQ
SELECT `user_id`, `username`, `first_name`, `last_name`, `pushover_user`, `pushover_token`, `pushover_priority`, `pushover_retry`, `pushover_expire`, `pushover_sound`, `role`, `disabled`
FROM `users`
WHERE `user_id` = '{$value}';
EOQ;
        break;
      case 'edge':
        $query = <<<EOQ
SELECT `edge_id`, `name`, `url`, `api_key`, `disabled`
FROM `edges`
WHERE `edge_id` = '{$value}';
EOQ;
        break;
      case 'monitor':
        $query = <<<EOQ
SELECT `monitor_id`, `name`, `url`, `edges`, `interval`, `timeout`, `allow_redirects`, `verify`, `disabled`
FROM `monitors`
WHERE `monitor_id` = '{$value}';
EOQ;
        break;
      case 'app':
        $query = <<<EOQ
SELECT `app_id`, `name`, `token`, STRFTIME('%Y-%m-%dT%H:%M', `begin`, 'unixepoch') AS `begin`, STRFTIME('%Y-%m-%dT%H:%M', `end`, 'unixepoch') AS `end`, `disabled`
FROM `apps`
WHERE `app_id` = '{$value}';
EOQ;
        break;

    }
    if ($object = $this->dbConn->querySingle($query, true)) {
      return $object;
    }
    return false;
  }

  public function getObjectCount($type) {
    $type = $this->dbConn->escapeString($type);
    $query = <<<EOQ
SELECT COUNT(*)
FROM `{$type}`;
EOQ;
    if ($count = $this->dbConn->querySingle($query)) {
      return $count;
    }
    return false;
  }

  public function putEvent($user_id, $action, $message = []) {
    $user_id = $this->dbConn->escapeString($user_id);
    $action = $this->dbConn->escapeString($action);
    $message = $this->dbConn->escapeString(json_encode($message));
    $remote_addr = ip2long(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
    $query = <<<EOQ
INSERT
INTO `events` (`user_id`, `action`, `message`, `remote_addr`)
VALUES ('{$user_id}', '{$action}', '{$message}', '{$remote_addr}');
EOQ;
    if ($this->dbConn->exec($query)) {
      return true;
    }
    return false;
  }

  public function putCall($token, $action, $message = []) {
    $app_id = $this->resolveObject('token', $token);
    $action = $this->dbConn->escapeString($action);
    $message = $this->dbConn->escapeString(json_encode($message));
    $remote_addr = ip2long(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
    $query = <<<EOQ
INSERT
INTO `calls` (`app_id`, `action`, `message`, `remote_addr`)
VALUES ('{$app_id}', '{$action}', '{$message}', '{$remote_addr}');
EOQ;
    if ($this->dbConn->exec($query)) {
      return true;
    }
    return false;
  }

  public function getEvents($page = 1) {
    $start = ($page - 1) * $this->pageLimit;
    $query = <<<EOQ
SELECT `event_id`, STRFTIME('%s', `date`, 'unixepoch') AS `date`, `user_id`, `first_name`, `last_name`, `action`, `message`, `remote_addr`, `disabled`
FROM `events`
LEFT JOIN `users` USING (`user_id`)
ORDER BY `date` DESC
LIMIT {$start}, {$this->pageLimit};
EOQ;
    if ($events = $this->dbConn->query($query)) {
      $output = [];
      while ($event = $events->fetchArray(SQLITE3_ASSOC)) {
        $output[] = $event;
      }
      return $output;
    }
    return false;
  }

  public function putReading($edge_id, $monitor_id, $total_seconds = null, $status_code = null, $reason) {
    $edge_id = $this->dbConn->escapeString($edge_id);
    $monitor_id = $this->dbConn->escapeString($monitor_id);
    $total_seconds = $this->dbConn->escapeString($total_seconds);
    $status_code = $this->dbConn->escapeString($status_code);
    $reason = $this->dbConn->escapeString($reason);
    $query = <<<EOQ
INSERT
INTO `readings` (`edge_id`, `monitor_id`, `total_seconds`, `status_code`, `reason`)
VALUES ('{$edge_id}', '{$monitor_id}', '{$total_seconds}', '{$status_code}', '{$reason}')
EOQ;
    if ($this->dbConn->exec($query)) {
      return true;
    }
    return false;
  }

  public function getReadings($monitor_id, $hours, $type) {
    $monitor_id = $this->dbConn->escapeString($monitor_id);
    $hours = $this->dbConn->escapeString($hours);
    switch ($type) {
      case 0:
        $query = <<<EOQ
SELECT STRFTIME('%Y-%m-%dT%H:%M', (`date` / ({$hours} * 60)) * ({$hours} * 60), 'unixepoch', 'localtime') AS `date`, ROUND(AVG(`total_seconds`) * 1000, 2) AS `total_milliseconds`
FROM `readings`
WHERE `monitor_id` = '{$monitor_id}'
AND `date` > STRFTIME('%s', 'now', '-{$hours} hours')
AND `total_seconds`
GROUP BY DATETIME((`date` / ({$hours} * 60)) * ({$hours} * 60), 'unixepoch')
ORDER BY `date`;
EOQ;
        break;
      case 1:
        $query = <<<EOQ
SELECT `edge_id`, `name`, STRFTIME('%Y-%m-%dT%H:%M', (`date` / ({$hours} * 60)) * ({$hours} * 60), 'unixepoch', 'localtime') AS `date`, ROUND(AVG(`total_seconds`) * 1000, 2) AS `total_milliseconds`
FROM `readings`
LEFT JOIN `edges` USING (`edge_id`)
WHERE `monitor_id` = '{$monitor_id}'
AND `date` > STRFTIME('%s', 'now', '-{$hours} hours')
GROUP BY `edge_id`, DATETIME((`date` / ({$hours} * 60)) * ({$hours} * 60), 'unixepoch')
ORDER BY `date`;
EOQ;
        break;
    }
    if ($readings = $this->dbConn->query($query)) {
      $output = [];
      while ($reading = $readings->fetchArray(SQLITE3_ASSOC)) {
        if (array_key_exists('edge_id', $reading)) {
          $output['edges'][$reading['edge_id']] = $reading['name'];
          $output['edgeData'][$reading['edge_id']][] = ['x' => $reading['date'], 'y' => $reading['total_milliseconds']];
        } else {
          $output[] = ['x' => $reading['date'], 'y' => $reading['total_milliseconds']];
        }
      }
      return $output;
    }
    return false;
  }

  public function getSounds() {
    if ($result = $this->memcacheConn->get('pushoverSounds')) {
      return json_decode($result)->sounds;
    } else {
      $ch = curl_init("https://api.pushover.net/1/sounds.json?token={$this->pushoverAppToken}");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      if (($result = curl_exec($ch)) !== false && curl_getinfo($ch, CURLINFO_RESPONSE_CODE) == 200) {
        $this->memcacheConn->set('pushoverSounds', $result, 60 * 60 * 24);
        return json_decode($result)->sounds;
      }
    }
    return false;
  }

  public function getRandomEdges($limit = 2) {
    $limit = $this->dbConn->escapeString($limit);
    $query = <<<EOQ
SELECT `edge_id`, `name`, `url`, `api_key`, `disabled`
FROM `edges`
WHERE NOT `disabled`
ORDER BY RANDOM()
LIMIT {$limit};
EOQ;
    if ($edges = $this->dbConn->query($query)) {
      $output = [];
      while ($edge = $edges->fetchArray(SQLITE3_ASSOC)) {
        $output[] = $edge;
      }
      return $output;
    }
    return false;
  }

  public function sendNotifications($messages = []) {
    $query = <<<EOQ
SELECT `user_id`, `first_name`, `last_name`, `pushover_user`, `pushover_token`, `pushover_priority`, `pushover_retry`, `pushover_expire`, `pushover_sound`
FROM `users`
WHERE LENGTH(`pushover_user`) AND LENGTH(`pushover_token`)
AND NOT `disabled`;
EOQ;
    if ($messages && $users = $this->dbConn->query($query)) {
      $ch = curl_init('https://api.pushover.net/1/messages.json');
      while ($user = $users->fetchArray(SQLITE3_ASSOC)) {
        $user_name = !empty($user['last_name']) ? sprintf('%2$s, %1$s', $user['first_name'], $user['last_name']) : $user['first_name'];
        foreach ($messages as $message) {
          curl_setopt($ch, CURLOPT_POSTFIELDS, ['user' => $user['pushover_user'], 'token' => $user['pushover_token'], 'message' => $message, 'priority' => $user['pushover_priority'], 'retry' => $user['pushover_retry'], 'expire' => $user['pushover_expire'], 'sound' => $user['pushover_sound']]);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          if (curl_exec($ch) !== false && curl_getinfo($ch, CURLINFO_RESPONSE_CODE) == 200) {
            $status = 'successful';
          } else {
            $status = 'failed';
          }
          echo date('Y-m-d H:i:s') . " - notification to {$user_name} (user_id: {$user['user_id']}) {$status}: {$message}" . PHP_EOL;
        }
      }
      curl_close($ch);
      return true;
    }
    return false;
  }
}
