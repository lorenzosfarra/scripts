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
  
$tblprefix = $xml->global->resources->db->table_prefix;
//$dbhost = $xml->global->resources->default_setup->connection->host;
$dbhost = "localhost";
$dbuser = $xml->global->resources->default_setup->connection->username;
$dbpass = $xml->global->resources->default_setup->connection->password;
$dbname = $xml->global->resources->default_setup->connection->dbname;

}
  
else {
    exit('Failed to open app/etc/local.xml');
}
  
// DB Interaction
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die ('Error connecting to <a class="HelpLink" onclick="showHelpTip(event, hint_id_7); return false;" href="javascript:void(0)">mysql</a>');
  
$result = mysqli_query($conn, "SELECT * FROM " . $tblprefix . "cron_schedule") or die (mysql_error());
?>  
<html>
  <head>
    <title>Crons list</title>
    <!-- Style -->
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.4/css/jquery.dataTables.css">
    <!-- JS -->
    <script src="//code.jquery.com/jquery-1.11.1.min.js"> </script>
    <script src="//cdn.datatables.net/1.10.4/js/jquery.dataTables.min.js"> </script>
  </head>
  <body>
  <table id="cronstable" class="display" cellspacing="0" width="100%">
    <thead>
      <tr>
        <th>schedule_id</th>
        <th>job_code</th>
        <th>status</th>
        <th>messages</th>
        <th>created_at</th>
        <th>scheduled_at</th>
        <th>executed_at</th>
        <th>finished_at</th>
      </tr>
      </thead>
      <tbody>
    
      <?php while ($row = mysqli_fetch_array($result)) :?>
        <tr>
          <td><?php echo $row['schedule_id']; ?></td>
          <td><?php echo $row['job_code']; ?></td>
          <td><?php echo $row['status']; ?></td>
          <td><?php echo $row['messages']; ?></td>
          <td><?php echo $row['created_at']; ?></td>
          <td><?php echo $row['scheduled_at']; ?></td>
          <td><?php echo $row['executed_at']; ?></td>
          <td><?php echo $row['finished_at']; ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>

    <script type="text/javascript">
      $(document).ready(function() {
        $('#cronstable').dataTable( {
          "order": [[ 5, "desc" ]]
        });
      });
    </script>
  </body>
</html>
  
<?php mysqli_close($conn); ?>
