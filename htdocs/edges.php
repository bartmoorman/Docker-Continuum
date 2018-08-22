<?php
require_once('inc/continuum.class.php');
$continuum = new Continuum(true, true, true, false);
?>
<!DOCTYPE html>
<html lang='en'>
  <head>
    <title>Continuum - Edges</title>
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
            <th>Edge ID</th>
            <th>Edge Name</th>
            <th>URL</th>
          </tr>
        </thead>
        <tbody>
<?php
foreach ($continuum->getObjects('edges') as $edge) {
  $tableClass = $edge['disabled'] ? 'text-warning' : 'table-default';
  echo "          <tr class='{$tableClass}'>" . PHP_EOL;
  echo "            <td><button type='button' class='btn btn-sm btn-outline-info id-details' data-edge_id='{$edge['edge_id']}'>Details</button></td>" . PHP_EOL;
  echo "            <td>{$edge['edge_id']}</td>" . PHP_EOL;
  echo "            <td>{$edge['name']}</td>" . PHP_EOL;
  echo "            <td>{$edge['url']}</td>" . PHP_EOL;
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
                  <label>Edge Name <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <input class='form-control' id='name' type='text' name='name' required>
                </div>
              </div>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>URL <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <input class='form-control' id='url' type='text' name='url' required>
                </div>
              </div>
              <div class='form-row'>
                <div class='form-group col'>
                  <label>API Key <sup class='text-danger' data-toggle='tooltip' title='Required'>*</sup></label>
                  <input class='form-control' id='api_key' type='text' name='api_key' minlength='40' maxlength='40' pattern='[A-Za-z0-9]{40}' required>
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
          $('h5.modal-title').text('Add Edge');
          $('form').removeData('edge_id').data('func', 'createEdge').trigger('reset');
          $('button.id-modify').addClass('d-none').removeData('edge_id');
          $('button.id-submit').removeClass('btn-info').addClass('btn-success').text('Add');
          $('div.id-modal').modal('toggle');
        });

        $('button.id-details').click(function() {
          $('h5.modal-title').text('Edge Details');
          $('form').removeData('edge_id').data('func', 'updateEdge').trigger('reset');
          $('button.id-modify').removeClass('d-none').removeData('edge_id');
          $('button.id-submit').removeClass('btn-success').addClass('btn-info').text('Save');
          $.get('src/action.php', {"func": "getObjectDetails", "type": "edge", "value": $(this).data('edge_id')})
            .done(function(data) {
              if (data.success) {
                edge = data.data;
                $('form').data('edge_id', edge.edge_id);
                $('#name').val(edge.name);
                $('#url').val(edge.url);
                $('#api_key').val(edge.api_key);
                $('button.id-modify.id-volatile').data('action', edge.disabled ? 'enable' : 'disable').text(edge.disabled ? 'Enable' : 'Disable');
                $('button.id-modify').data('edge_id', edge.edge_id);
                $('div.id-modal').modal('toggle');
              }
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
              console.log(`getObjectDetails failed: ${jqxhr.status} (${jqxhr.statusText}), ${textStatus}, ${errorThrown}`);
            });
        });

        $('button.id-modify').click(function() {
          if (confirm(`Want to ${$(this).data('action').toUpperCase()} edge ${$(this).data('edge_id')}?`)) {
            $.get('src/action.php', {"func": "modifyObject", "action": $(this).data('action'), "type": "edge_id", "value": $(this).data('edge_id')})
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
          $.post('src/action.php', {"func": $(this).data('func'), "edge_id": $(this).data('edge_id'), "name": $('#name').val(), "url": $('#url').val(), "api_key": $('#api_key').val()})
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
