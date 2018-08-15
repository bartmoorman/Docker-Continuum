<?php
require_once('inc/continuum.class.php');
$continuum = new Continuum(true, true, false, false);

if ($continuum->deauthenticateSession()) {
  header('Location: login.php');
} else {
  header('Location: index.php');
}
?>
