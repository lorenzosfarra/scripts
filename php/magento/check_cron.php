<?php
/**
 * Author: Lorenzo Sfarra <lorenzosfarra@gmail.com>
 * Website: http://lorenzosfarra.com/
 *
 * This PHP script shows the status of Magento cron jobs.
 * You can order ASC/DESC by clicking on a column, or filter by using
 * the "Search" input field.
 *
 * It's mainly based on a great script that you can find at this URL[1].
 * Please protect this script with a better auth system!
 * For now, just change the $username and $password variables and pass
 * them as GET params username&password.
 *
 * You can use it for free without limitations.
 * [1] http://blog.nexcess.net/2010/10/03/finding-the-status-of-magento-cron-jobs-tasks/
 */

/***** AUTHORIZATION INFO: edit this! *****/
$username = "wow.user";
$password = "wow.pwd";
/**** STOP EDIT *****/

if (!isset($_GET['username']) || !isset($_GET['password'])) {
  echo "You're not authorized to check this URL.";
  exit;
}

if (!(($_GET['username'] == $username) && ($_GET['password'] == $password))) {
  echo "Wrong username and/or password";
  exit;
}
  
// Parse magento's local.xml to get db info, if local.xml is found
if (file_exists('app/etc/local.xml')) {
  $xml = simplexml_load_file('app/etc/local.xml');
  
  // Store the configuration in a more convenient format, a PHP array
  $confs = Array(
    "tblprefix" =>  $xml->global->resources->db->table_prefix,
    "dbhost" =>  $xml->global->resources->default_setup->connection->host,
    "dbuser" =>  $xml->global->resources->default_setup->connection->username,
    "dbpass" =>  $xml->global->resources->default_setup->connection->password,
    "dbname" =>  $xml->global->resources->default_setup->connection->dbname
  );
  
} else {
  exit('Failed to open app/etc/local.xml');
}
  
// DB Interaction
$conn = mysqli_connect($confs['dbhost'], $confs['dbuser'],
            $confs['dbpass'], $confs['dbname']) or die ("Error connecting to the DB.");

// Fields and labels
$fields_labels = Array(
  'schedule_id' => 'ID', 
  'job_code' => 'job code',
  'status' => 'status',
  'messages' => 'messages',
  'created_at' => 'created at',
  'scheduled_at' => 'scheduled at',
  'executed_at' => 'executed at',
  'finished_at' => 'finished at'
);
// Prepare and execute the query 
$sql  = "SELECT " . implode(',', array_keys($fields_labels)) .
        " FROM " . $confs['tblprefix'] . "cron_schedule";
$result = mysqli_query($conn, $sql) or die (mysql_error());
?>  

<!DOCTYPE html>
<html>
  <head>
    <title>Crons list</title>
    <!-- Style -->
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.4/css/jquery.dataTables.css">
    <style>
      #cronstable {
        border-spacing: 0px;
        width: 100%;
      }

      th::first-letter {
        text-transform: capitalize;
      }
    </style>
    <!-- JS -->
    <script src="//code.jquery.com/jquery-1.11.1.min.js"> </script>
    <script src="//cdn.datatables.net/1.10.4/js/jquery.dataTables.min.js"> </script>
  </head>
  <body>
  <table id="cronstable" class="display">
    <thead>
      <tr>
        <?php foreach ($fields_labels as $field => $label) :?>
          <th><?php echo $label; ?></th>
        <?php endforeach; ?>
      </tr>
      </thead>
      <tbody>
    
      <?php while ($row = mysqli_fetch_array($result)) :?>
        <tr>
          <?php foreach ($fields_labels as $field => $label) :?>
            <td><?php echo $row[$field]; ?></td>
          <?php endforeach; ?>
        </tr>
      <?php endwhile; ?>

      </tbody>
    </table>

    <script type="text/javascript">
      $(document).ready(function() {
        $('#cronstable').dataTable({
          "order": [[ 5, "desc" ]]
        });
      });
    </script>
  </body>
</html>
  
<?php mysqli_close($conn); ?>
