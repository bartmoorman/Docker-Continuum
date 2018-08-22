<?php
require_once('inc/continuum.class.php');
$continuum = new Continuum(true, true, true, false);
?>
<!DOCTYPE html>
<html lang='en'>
  <head>
    <title>Continuum - Monitors</title>
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
    <div class='container'>
      <table class='table table-striped table-hover table-sm'>
        <thead>
          <tr>
            <th><button type='button' class='btn btn-sm btn-outline-success id-add'>Add</button></th>
            <th>Monitor ID</th>
            <th>Monitor Name</th>
            <th>URL</th>
            <th>Edges</th>
            <th>Interval</th>
            <th>Timeout</th>
          </tr>
        </thead>
        <tbody>
<?php
foreach ($continuum->getObjects('monitors') as $monitor) {
  $tableClass = $monitor['disabled'] ? 'text-warning' : 'table-default';
  echo "          <tr class='{$tableClass}'>" . PHP_EOL;
  echo "            <td><button type='button' class='btn btn-sm btn-outline-info id-details' data-monitor_id='{$monitor['monitor_id']}'>Details</button></td>" . PHP_EOL;
  echo "            <td>{$monitor['monitor_id']}</td>" . PHP_EOL;
  echo "            <td>{$monitor['name']}</td>" . PHP_EOL;
  echo "            <td>{$monitor['url']}</td>" . PHP_EOL;
  echo "            <td>{$monitor['edges']}</td>" . PHP_EOL;
  echo "            <td>{$monitor['interval']}m</td>" . PHP_EOL;
  echo "            <td>{$monitor['timeout']}s</td>" . PHP_EOL;
  echo "          </tr>" . PHP_EOL;
}
?>
        </tbody>
      </table>
    </div>
    <div class='modal fade id-modal'>
      <div class='modal-dialog'>
        <div class='modal-content'>
          <form>
            <div class='modal-header'>
              <h5 class='modal-title'></h5>
            </div>
            <div class='modal-body'>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>Monitor Name <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <input class='form-control' id='name' type='text' name='name' required>
                </div>
              </div>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>URL <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <input class='form-control' id='url' type='text' name='url' required>
                </div>
                <div class='form-group col'>
                  <label>Edges <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <select class='form-control id-edges' id='edges' name='edges' required>
<?php
for ($i = 1; $i <= $continuum->getObjectCount('edges'); $i++) {
  echo "                    <option value='{$i}'>{$i}</option>" . PHP_EOL;
}
?>
                  </select>
                </div>
              </div>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>Interval <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <div class='input-group'>
                    <input class='form-control id-interval' id='interval' type='number' name='interval' min='1' max='60' step='1' required>
                    <div class='input-group-append'>
                      <span class='input-group-text'>min</span>
                    </div>
                  </div>
                </div>
                <div class='form-group col'>
                  <label>Timeout <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <div class='input-group'>
                    <input class='form-control id-timeout' id='timeout' type='number' name='timeout' min='0.1' max='5.0' step='0.1' required>
                    <div class='input-group-append'>
                      <span class='input-group-text'>sec</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>Allow Redirects <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <select class='form-control' id='allow_redirects' name='allow_redirects' required>
                    <option value='true'>true</option>
                    <option value='false'>false</option>
                  </select>
                </div>
                <div class='form-group col'>
                  <label>Verify SSL <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <select class='form-control' id='verify' name='verify' required>
                    <option value='true'>true</option>
                    <option value='false'>false</option>
                  </select>
                </div>
              </div>
            </div>
            <div class='modal-footer'>
              <button type='button' class='btn btn-outline-warning id-modify id-volatile'></button>
              <button type='button' class='btn btn-outline-danger mr-auto id-modify' data-action='delete'>Delete</button>
              <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
              <button type='submit' class='btn id-submit'></button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script src='//code.jquery.com/jquery-3.3.1.min.js' integrity='sha384-tsQFqpEReu7ZLhBV2VZlAu7zcOV+rXbYlF2cqB8txI/8aZajjp4Bqd+V6D5IgvKT' crossorigin='anonymous'></script>
    <script src='//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js' integrity='sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49' crossorigin='anonymous'></script>
    <script src='//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js' integrity='sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T' crossorigin='anonymous'></script>
    <script>
      $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();

        $('button.id-add').click(function() {
          $('h5.modal-title').text('Add Monitor');
          $('form').removeData('monitor_id').data('func', 'createMonitor').trigger('reset');
          $('select.id-edges').val(2);
          $('input.id-interval').val(5);
          $('input.id-timeout').val(1.0);
          $('button.id-modify').addClass('d-none').removeData('monitor_id');
          $('button.id-submit').removeClass('btn-info').addClass('btn-success').text('Add');
          $('div.id-modal').modal('toggle');
        });

        $('button.id-details').click(function() {
          $('h5.modal-title').text('Monitor Details');
          $('form').removeData('monitor_id').data('func', 'updateMonitor').trigger('reset');
          $('button.id-modify').removeClass('d-none').removeData('monitor_id');
          $('button.id-submit').removeClass('btn-success').addClass('btn-info').text('Save');
          $.get('src/action.php', {"func": "getObjectDetails", "type": "monitor", "value": $(this).data('monitor_id')})
            .done(function(data) {
              if (data.success) {
                monitor = data.data;
                $('form').data('monitor_id', monitor.monitor_id);
                $('#name').val(monitor.name);
                $('#url').val(monitor.url);
                $('#edges').val(monitor.edges);
                $('#interval').val(monitor.interval);
                $('#timeout').val(monitor.timeout);
                $('#allow_redirects').val(monitor.allow_redirects);
                $('#verify').val(monitor.verify);
                $('button.id-modify.id-volatile').data('action', monitor.disabled ? 'enable' : 'disable').text(monitor.disabled ? 'Enable' : 'Disable');
                $('button.id-modify').data('monitor_id', monitor.monitor_id);
                $('div.id-modal').modal('toggle');
              }
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
              console.log(`getObjectDetails failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
            });
        });

        $('button.id-modify').click(function() {
          if (confirm(`Want to ${$(this).data('action').toUpperCase()} monitor ${$(this).data('monitor_id')}?`)) {
            $.get('src/action.php', {"func": "modifyObject", "action": $(this).data('action'), "type": "monitor_id", "value": $(this).data('monitor_id')})
              .done(function(data) {
                if (data.success) {
                  location.reload();
                }
              })
              .fail(function(jqxhr, textStatus, errorThrown) {
                console.log(`modifyObject failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
              });
          }
        });

        $('form').submit(function(e) {
          e.preventDefault();
          $.post('src/action.php', {"func": $(this).data('func'), "monitor_id": $(this).data('monitor_id'), "name": $('#name').val(), "url": $('#url').val(), "edges": $('#edges').val(), "interval": $('#interval').val(), "timeout": $('#timeout').val(), "allow_redirects": $('#allow_redirects').val(), "verify": $('#verify').val()})
            .done(function(data) {
              if (data.success) {
                location.reload();
              }
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
              console.log(`${$(this).data('func')} failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
            });
        });

        $('button.id-nav').click(function() {
          location.href=$(this).data('href');
        });
      });
    </script>
  </body>
</html>
