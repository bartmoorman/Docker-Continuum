<?php
require_once('inc/continuum.class.php');
$continuum = new Continuum(true, false, false, true);
?>
<!DOCTYPE html>
<html lang='en'>
  <head>
    <title>Continuum - Login</title>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
    <link rel='stylesheet' href='//stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css' integrity='sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T' crossorigin='anonymous'>
    <link rel='stylesheet' href='//stackpath.bootstrapcdn.com/bootswatch/4.3.1/darkly/bootstrap.min.css' integrity='sha384-w+8Gqjk9Cuo6XH9HKHG5t5I1VR4YBNdPt/29vwgfZR485eoEJZ8rJRbm3TR32P6k' crossorigin='anonymous'>
    <link rel='stylesheet' href='//use.fontawesome.com/releases/v5.8.1/css/all.css' integrity='sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf' crossorigin='anonymous'>
  </head>
  <body>
    <div class='modal fade'>
      <div class='modal-dialog modal-sm modal-dialog-centered'>
        <div class='modal-content'>
          <form>
            <div class='modal-body'>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>Username</label>
                  <input class='form-control form-control-lg' id='username' type='text' name='username' autofocus required>
                </div>
              </div>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>Password</label>
                  <input class='form-control form-control-lg' id='password' type='password' name='password' required>
                </div>
              </div>
            </div>
            <div class='modal-footer'>
              <button type='submit' class='btn btn-lg btn-info btn-block id-login'>Log in</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script src='//code.jquery.com/jquery-3.3.1.min.js' integrity='sha384-tsQFqpEReu7ZLhBV2VZlAu7zcOV+rXbYlF2cqB8txI/8aZajjp4Bqd+V6D5IgvKT' crossorigin='anonymous'></script>
    <script src='//code.jquery.com/ui/1.12.1/jquery-ui.min.js' integrity='sha384-Dziy8F2VlJQLMShA6FHWNul/veM9bCkRUaLqr199K94ntO5QUrLJBEbYegdSkkqX' crossorigin='anonymous'></script>
    <script src='//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js' integrity='sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1' crossorigin='anonymous'></script>
    <script src='//stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js' integrity='sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM' crossorigin='anonymous'></script>
    <script>
      $(document).ready(function() {
        $('div.modal').modal({backdrop: false, keyboard: false});

        $('form').submit(function(e) {
          e.preventDefault();
          $('button.id-login').prop('disabled', true);
          $.post('src/action.php', {"func": "authenticateSession", "username": $('#username').val(), "password": $('#password').val()})
            .done(function(data) {
              if (data.success) {
                location.href = '<?php echo dirname($_SERVER['PHP_SELF']) ?>';
              } else {
                $('div.modal').effect('shake');
              }
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
              console.log(`authenticateSession failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
            })
            .always(function() {
              $('button.id-login').prop('disabled', false);
            });
        });
      });
    </script>
  </body>
</html>
