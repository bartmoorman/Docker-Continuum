<?php
require_once('inc/continuum.class.php');
$continuum = new Continuum(false, true, false, true);
?>
<!DOCTYPE html>
<html lang='en'>
  <head>
    <title>Continuum - Setup</title>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
    <link rel='stylesheet' href='//stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css' integrity='sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T' crossorigin='anonymous'>
    <link rel='stylesheet' href='//stackpath.bootstrapcdn.com/bootswatch/4.3.1/darkly/bootstrap.min.css' integrity='sha384-w+8Gqjk9Cuo6XH9HKHG5t5I1VR4YBNdPt/29vwgfZR485eoEJZ8rJRbm3TR32P6k' crossorigin='anonymous'>
    <link rel='stylesheet' href='//use.fontawesome.com/releases/v5.8.1/css/all.css' integrity='sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf' crossorigin='anonymous'>
  </head>
  <body>
    <div class='modal fade'>
      <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content'>
          <form>
            <div class='modal-header'>
              <h5 class='modal-title'>Continuum Setup</h5>
            </div>
            <div class='modal-body'>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>Username <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <input class='form-control' id='username' type='text' name='username' pattern='[A-Za-z0-9]+' autofocus required>
                </div>
                <div class='form-group col'>
                  <label>Password <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <input class='form-control' id='password' type='password' name='password' minlength='6' required>
                </div>
              </div>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>First Name <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <input class='form-control' id='first_name' type='text' name='first_name' required>
                </div>
                <div class='form-group col'>
                  <label>Last Name</label>
                  <input class='form-control' id='last_name' type='text' name='last_name'>
                </div>
              </div>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>Role <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <input class='form-control' id='role' type='text' name='role' value='admin' readonly required>
                </div>
              </div>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>Pushover User Key</label>
                  <input class='form-control' id='pushover_user' type='text' name='pushover_user' minlegth='30' maxlength='30' pattern='[A-Za-z0-9]{30}'>
                </div>
                <div class='form-group col'>
                  <label>Pushover App. Token</label>
                  <input class='form-control' id='pushover_token' type='text' name='pushover_token' minlegth='30' maxlength='30' pattern='[A-Za-z0-9]{30}'>
                </div>
              </div>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>Pushover Sound <sup><a target='_blank' href='https://pushover.net/api#sounds'>Listen</a></sup></label>
                  <select class='form-control' id='pushover_sound' name='pushover_sound'>
                    <option value=''>User Default</option>
<?php
foreach ($continuum->getSounds() as $value => $text) {
  echo "                    <option value='{$value}'>{$text}</option>" . PHP_EOL;
}
?>
                  </select>
                </div>
                <div class='form-group col'>
                  <label>Pushover Priority</label>
                  <select class='form-control id-pushover_priority' id='pushover_priority' name='pushover_priority'>
<?php
for ($priority = -2; $priority <= 2; $priority++) {
  echo "                    <option value='{$priority}'>{$priority}</option>" . PHP_EOL;
}
?>
                  </select>
                </div>
              </div>
              <div class='form-row id-required'>
                <div class='form-group col'>
                  <label>Pushover Retry <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <input class='form-control id-pushover_retry' id='pushover_retry' type='number' name='pushover_retry' min='30' required>
                </div>
                <div class='form-group col'>
                  <label>Pushover Expire <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <input class='form-control id-pushover_expire' id='pushover_expire' type='number' name='pushover_expire' max='10800' required>
                </div>
              </div>
            </div>
            <div class='modal-footer'>
              <button type='submit' class='btn btn-info'>Setup</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script src='//code.jquery.com/jquery-3.3.1.min.js' integrity='sha384-tsQFqpEReu7ZLhBV2VZlAu7zcOV+rXbYlF2cqB8txI/8aZajjp4Bqd+V6D5IgvKT' crossorigin='anonymous'></script>
    <script src='//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js' integrity='sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1' crossorigin='anonymous'></script>
    <script src='//stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js' integrity='sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM' crossorigin='anonymous'></script>
    <script>
      $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();

        $('select.id-pushover_priority').val(0);
        $('div.id-required').addClass('d-none');
        $('input.id-pushover_retry').val(60);
        $('input.id-pushover_expire').val(3600);

        $('div.modal').modal({backdrop: false, keyboard: false});

        $('select.id-pushover_priority').change(function() {
          $('div.id-required').toggleClass('d-none', $(this).val() != 2 ? true : false);
        });

        $('form').submit(function(e) {
          e.preventDefault();
          $.post('src/action.php', {"func": "createUser", "username": $('#username').val(), "password": $('#password').val(), "first_name": $('#first_name').val(), "last_name": $('#last_name').val(), "pushover_user": $('#pushover_user').val(), "pushover_token": $('#pushover_token').val(), "pushover_priority": $('#pushover_priority').val(), "pushover_retry": $('#pushover_retry').val(), "pushover_expire": $('#pushover_expire').val(), "pushover_sound": $('#pushover_sound').val(), "role": $('#role').val()})
            .done(function(data) {
              if (data.success) {
                location.href = '<?php echo dirname($_SERVER['PHP_SELF']) ?>';
              }
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
              console.log(`createUser failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
            });
        });
      });
    </script>
  </body>
</html>
