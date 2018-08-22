<?php
require_once('inc/continuum.class.php');
$continuum = new Continuum(true, true, false, false);
?>
<!DOCTYPE html>
<html lang='en'>
  <head>
    <title>Continuum - Index</title>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
    <link rel='stylesheet' href='//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css' integrity='sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB' crossorigin='anonymous'>
    <link rel='stylesheet' href='//maxcdn.bootstrapcdn.com/bootswatch/4.1.1/darkly/bootstrap.min.css' integrity='sha384-ae362vOLHy2F1EfJtpMbNW0i9pNM1TP2l5O4VGYYiLJKsaejqVWibbP6BSf0UU5i' crossorigin='anonymous'>
    <link rel='stylesheet' href='//use.fontawesome.com/releases/v5.1.0/css/all.css' integrity='sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt' crossorigin='anonymous'>
  </head>
  <body>
<?php
include_once('header.php');
?>
    <canvas id='chart'></canvas>
    <nav class='navbar text-center'>
      <select class='btn btn-sm btn-outline-success ml-auto mr-2 id-monitor_id' data-storage='monitor_id'>
        <option value='0'>Monitor</option>
<?php
foreach ($continuum->getObjects('monitors') as $monitor) {
  echo "        <option value='{$monitor['monitor_id']}'>{$monitor['name']}</option>" . PHP_EOL;
}
?>
      </select>
      <select class='btn btn-sm btn-outline-success mr-2 id-endpoint_id' data-storage='endpoint_id'>
        <option value='0'>Endpoint</option>
<?php
foreach ($continuum->getObjects('endpoints') as $endpoint) {
  echo "        <option value='{$endpoint['endpoint_id']}'>{$endpoint['name']}</option>" . PHP_EOL;
}
?>
      </select>
      <select class='btn btn-sm btn-outline-success mr-auto id-hours' data-storage='hours'>
        <option value='0'>Period</option>
<?php
$periods = [
  6 => '6 hours',
  12 => '12 hours',
  24 => '1 day',
  24 * 7 => '1 week',
  24 * 7 * 2 => '2 weeks',
  24 * 30 => '1 month',
  24 * 30 * 3 => '3 months',
  24 * 30 * 6 => '6 months',
  24 * 30 * 9 => '9 months',
  24 * 365 => '1 year'
];
foreach ($periods as $hours => $period) {
  echo "        <option value='{$hours}'>{$period}</option>" . PHP_EOL;
}
?>
      </select>
    </nav>
    <div class='container'>
    </div>
    <script src='//code.jquery.com/jquery-3.3.1.min.js' integrity='sha384-tsQFqpEReu7ZLhBV2VZlAu7zcOV+rXbYlF2cqB8txI/8aZajjp4Bqd+V6D5IgvKT' crossorigin='anonymous'></script>
    <script src='//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js' integrity='sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49' crossorigin='anonymous'></script>
    <script src='//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js' integrity='sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T' crossorigin='anonymous'></script>
    <script src='//cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js' integrity='sha384-F13mJAeqdsVJS5kJv7MZ4PzYmJ+yXXZkt/gEnamJGTXZFzYgAcVtNg5wBDrRgLg9' crossorigin='anonymous'></script>
    <script src='//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js' integrity='sha384-0saKbDOWtYAw5aP4czPUm6ByY5JojfQ9Co6wDgkuM7Zn+anp+4Rj92oGK8cbV91S' crossorigin='anonymous'></script>
    <script>
      $(document).ready(function() {
        var timer;
        var config = {
          type: 'line'
        };
        var chart = new Chart($('#chart'), config);

        function getReadings() {
        };

        $.each(['monitor_id', 'endpoint_id', 'hours'], function(key, value) {
          if (result = localStorage.getItem(value)) {
            if ($(`select.id-${value} option[value="${result}"]`).length) {
              $(`select.id-${value}`).val(result);
            }
          }
        });

        $('select.id-monitor_id, select.id-endpoint_id, select.id-hours').change(function() {
          localStorage.setItem($(this).data('storage'), $(this).val());
        });

        $('button.id-nav').click(function() {
          location.href=$(this).data('href');
        });
      });
    </script>
  </body>
</html>
