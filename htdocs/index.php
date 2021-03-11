<?php
require_once('inc/continuum.class.php');
$continuum = new Continuum(true, true, false, false);
?>
<!DOCTYPE html>
<html lang='en'>
  <head>
    <title><?php echo $continuum->appName ?> - Index</title>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
<?php require_once('include.css'); ?>
  </head>
  <body>
<?php require_once('header.php'); ?>
    <canvas id='chart'></canvas>
    <nav class='navbar text-center'>
      <select class='btn btn-sm btn-outline-success ml-auto mr-2 id-monitor_id' data-storage='monitor_id'>
        <option value='0'>-- Monitor --</option>
<?php
foreach ($continuum->getObjects('monitors') as $monitor) {
  echo "        <option value='{$monitor['monitor_id']}'>{$monitor['name']}</option>" . PHP_EOL;
}
?>
      </select>
      <select class='btn btn-sm btn-outline-success mr-2 id-hours' data-storage='hours'>
        <option value='0'>-- Period --</option>
<?php
$periods = [
  1 => '1 hour',
  3 => '3 hours',
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
      <select class='btn btn-sm btn-outline-success mr-auto id-type' data-storage='type'>
        <option value='0'>Summary</option>
        <option value='1'>Detailed</option>
      </select>
    </nav>
    <div class='container'>
    </div>
<?php require_once('include.js'); ?>
    <script src='//cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js' integrity='sha384-fYxN7HsDOBRo1wT/NSZ0LkoNlcXvpDpFy6WzB42LxuKAX7sBwgo7vuins+E1HCaw' crossorigin='anonymous'></script>
    <script src='//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js' integrity='sha384-QzN1ywg2QLsf72ZkgRHgjkB/cfI4Dqjg6RJYQUqH6Wm8qp/MvmEYn+2NBsLnhLkr' crossorigin='anonymous'></script>
    <script>
      $(document).ready(function() {
        var firstRun = true;
        var timer;
        var config = {
          type: 'line',
          options: {
            legend: {display: true, position: 'bottom'},
            scales: {
              xAxes: [{display: true, type: 'time'}],
              yAxes: [{
                display: true,
                position: 'left',
                scaleLabel: {display: true, labelString: 'Milliseconds'}
              }]
            }
          }
        };
        var chart = new Chart($('#chart'), config);

        function getReadings() {
          $.get('src/action.php', {"func": "getReadings", "monitor_id": $('select.id-monitor_id').val(), "hours": $('select.id-hours').val(), "type": $('select.id-type').val()})
            .done(function(data) {
              if (data.success) {
                if (data.data.edges) {
                  $.each(data.data.edges, function(key, value) {
                    if (firstRun) {
                      config.options.legend.display = true;
                      config.data.datasets[key - 1] = {
                        label: value.name,
                        backgroundColor: value.color + '4d',
                        borderColor: value.color,
                        borderWidth: 1,
                        pointRadius: 2,
                        fill: false,
                        data: data.data.edgeData[key]
                      };
                    } else {
                      config.data.datasets[key - 1].data = data.data.edgeData[key];
                    }
                  });
                } else {
                  if (firstRun) {
                    var r = Math.ceil(Math.random() * 255);
                    var g = Math.ceil(Math.random() * 255);
                    var b = Math.ceil(Math.random() * 255);
                    config.options.legend.display = false;
                    config.data.datasets[0] = {
                      backgroundColor: `rgba(${r}, ${g}, ${b}, 0.3)`,
                      borderColor: `rgb(${r}, ${g}, ${b})`,
                      borderWidth: 1,
                      pointRadius: 2,
                      fill: true,
                      data: data.data
                    };
                  } else {
                    config.data.datasets[0].data = data.data;
                  }
                }
                firstRun = false;
                chart.update();
              }
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
              if (jqxhr.status == 403) {
                location.reload();
              } else {
                console.log(`getReadings failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
              }
            })
            .always(function() {
              timer = setTimeout(getReadings, $('select.id-hours').val() * 1000);
            });
        };

        $.each(['monitor_id', 'hours', 'type'], function(key, value) {
          if (result = localStorage.getItem(value)) {
            if ($(`select.id-${value} option[value="${result}"]`).length) {
              $(`select.id-${value}`).val(result);
            }
          }
        });

        if ($('select.id-monitor_id').val() != 0 && $('select.id-hours').val() != 0) {
          getReadings();
        }

        $('select.id-monitor_id, select.id-hours, select.id-type').change(function() {
          clearTimeout(timer);
          localStorage.setItem($(this).data('storage'), $(this).val());
          config.data.datasets = [];
          firstRun = true;
          if ($('select.id-monitor_id').val() != 0 && $('select.id-hours').val() != 0) {
            getReadings();
          } else {
            chart.update();
          }

        });

        $('button.id-nav').click(function() {
          location.href=$(this).data('href');
        });
      });
    </script>
  </body>
</html>
