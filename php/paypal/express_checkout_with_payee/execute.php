<?php
/**
 * Author: Lorenzo Sfarra <lorenzosfarra@gmail.com>
 * Website: http://lorenzosfarra.com/
 *
 * Simple script to create an instance of the Process class
 * and perform the execute() action.
 **/

require_once __DIR__ . "/Process.php";
$execute = new \Process();
$execute->execute();
