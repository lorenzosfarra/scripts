<?php
/**
 * Author: Lorenzo Sfarra <lorenzosfarra@gmail.com>
 * Website: http://lorenzosfarra.com/
 *
 * Script to add a customer attribute.
 * It's very simple and does not support everything (example: select).
 * Please provide a better authentication / authorization system!!
 * NOTE: clean and check the $_POST parameters before adding them to your DB
 */
/***** AUTHORIZATION INFO: edit this! *****/
$username = "user";
$password = "pwd";
/**** STOP EDIT *****/

// GET OR POST?
$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
  if (!isset($_GET['username']) || !isset($_GET['password'])) {
    echo "You're not authorized to check this URL.";
    exit;
  }
  
  if (!(($_GET['username'] == $username) && ($_GET['password'] == $password))) {
    echo "Wrong username and/or password";
    exit;
  }
}

// SET UP MAGENTO
define('MAGENTO', realpath(dirname(__FILE__)));
require_once MAGENTO . '/app/Mage.php';
Mage::app();

// SET UP THE INSTALLER
$installer = new Mage_Customer_Model_Entity_Setup('core_setup');
$installer->startSetup();
$vCustomerEntityType = $installer->getEntityTypeId('customer');
$vCustAttributeSetId = $installer->getDefaultAttributeSetId($vCustomerEntityType);
$vCustAttributeGroupId = $installer->getDefaultAttributeGroupId($vCustomerEntityType, $vCustAttributeSetId);

/**
 * GET? Show the form.
 */
if ($method === "GET") { ?>
<!DOCTYPE html>
<html>
  <head>
    <title>Add a customer attribute :: Magento</title>
    <!-- JQuery tooltips -->
    <script src="http://cdn.jquerytools.org/1.2.6/full/jquery.tools.min.js"></script>
    <!-- Style-->
    <link rel="stylesheet" href="//yui.yahooapis.com/pure/0.6.0/pure-min.css">
    <style>
      form {
        padding: 1em;
        border-width: 1px 1px;
        border-style: solid;
        border-color: #EEE;
        background: #FAFAFA none repeat scroll 0% 0%;
        color: #333;
        margin: 10px;
      }
      /* simple css-based tooltip */
      .tooltip {
        background-color: #000;
        border: 1px solid #fff;
        padding: 10px 15px;
        width: auto;
        display: none;
        color: #fff;
        text-align: left;
        font-size: 12px;
      
        /* outline radius for mozilla/firefox only */
        -moz-box-shadow:0 0 10px #000;
        -webkit-box-shadow:0 0 10px #000;
      }
      
      #status {
        padding: 12px;
        color: #000000;
      }

      .success {
        background: rgb(28, 184, 65); /* this is a green */
      }

      .error {
        background: rgb(202, 60, 60); /* this is a maroon */
      }

      .warning {
        background: rgb(223, 117, 20); /* this is an orange */
      }

      .info {
        background: rgb(66, 184, 221); /* this is a light blue */
      }
    </style>
  </head>
  <body>
    <h1>Add a customer attribute to your Magento</h1>
    <div id="status"></div>
    <form class="pure-form pure-form-aligned" id="mainform" action="/add_customer_attribute.php" method="post">
    <fieldset>
        <legend>Add your attribute details here</legend>
        <div class="pure-control-group tooltips">
          <label for="name">Name</label>
          <input id="name" type="text" placeholder="Name" name="name" title="The name. IMPORTANT: no spaces here." required>
        </div>
        <div class="pure-control-group tooltips">
          <label for="label">Label</label>
          <input id="label" type="text" placeholder="Label" name="label" title="Backend label (Human-friendly title)." required>
        </div>
        <div class="pure-control-group tooltips">
          <label for="default">Default value</label>
          <input id="default" type="text" placeholder="Default value" name="default" title="Default value." required>
        </div>
        <div style="clear: both;">&nbsp;</div>
        <div class="pure-control-group">
          <label for="type">Type</label>
          <select id="type" name="type">
            <option value="int">Int</option>
            <option value="varchar">Varchar</option>
            <option value="text">Text</option>
          </select>
          <label for="input">Input Type</label>
          <select id="input" name="input">
            <option value="textarea">TextArea</option>
            <option value="text">Text</option>
          </select>
        </div>
        <div class="pure-control-group">
          <label for="visible">Visible?</label>
          <select id="visible" name="visible">
            <option value="yes">Yes</option>
            <option value="no">No</option>
          </select>
          <label for="visibleonfront">Visible on front?</label>
          <select id="visibleonfront" name="visibleonfront">
            <option value="yes">Yes</option>
            <option value="no">No</option>
          </select>
        </div>
        <div class="pure-control-group">
          <label for="required">Required?</label>
          <select id="required" name="required">
            <option value="yes">Yes</option>
            <option value="no">No</option>
          </select>
        </div>
        <div class="pure-control-group">
          <label for="global">Global?</label>
          <select id="global" name="global">
            <option value="yes">Yes</option>
            <option value="no">No</option>
          </select>
        </div>
        <div class="pure-control-group">
          <label for="userdefined">User defined?</label>
          <select id="userdefined" name="userdefined">
            <option value="yes">Yes</option>
            <option value="no">No</option>
          </select>
        </div>
        <div class="pure-controls">
          <button type="submit" class="pure-button pure-button-primary">Add field</button>
        </div>
    </fieldset>
    </form>
    <script>
      var msgs = {
        "submit": "Adding the customer attribute...",
        "added" : "Customer attribute added successfully!",
        "failed": "There was an error adding the customer's attribute, check your server logs for details",
      };
      $(document).ready(function() {
        /* Tooltip to guide users */
        $("#mainform .tooltips :input").tooltip({
          position: "center right",
          offset: [-2, 10],
          effect: "fade",
          opacity: 0.7
        });
        // Attach a submit handler to the form
        $("#mainform").submit(function(event) {
          // Stop form from submitting normally
          event.preventDefault();
          // Show the action
          $("#status").text(msgs.submit).addClass('info').show();
          // Submit the form
          var jqxhr = $.post($(this).attr('action'), $(this).serialize());
          jqxhr.done(function() {
            // SUCCESS
            $("#status").text(msgs.added).addClass('success');
          });
          jqxhr.fail(function() {
            // FAILED
            $("#status").text(msgs.failed).addClass('warning');
          });
          jqxhr.always(function() {
            // ALWAYS
            $("#status").removeClass('info');
            // Hide the message after 3 seconds
            setTimeout(function() {
              $("#status").fadeOut('slow');
            }, 3000);
          });
        });
      });
    </script>
  </body>
</html>
<?php

} else {
  $installer->addAttribute('customer', $_POST['name'], array(
        'label' => $_POST['label'],
        'input' => $_POST['input'],
        'type'  => $_POST['type'],
        'forms' => array('adminhtml_customer'),
        'default' => $_POST['default'],
        'visible' => ($_POST['visible'] === "yes"),
        'global'  => ($_POST['global'] === "yes"),
        'visible_on_front' => ($_POST['visibleonfront'] === "yes"),
        'required' => ($_POST['required'] === "yes"),
        'user_defined' => ($_POST['userdefined'] === "yes"),
  ));

  $installer->addAttributeToGroup($vCustomerEntityType, $vCustAttributeSetId, $vCustAttributeGroupId, $_POST['name'], 0);

  $oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', $_POST['name']);
  $oAttribute->setData('used_in_forms', array('adminhtml_customer'));
  $oAttribute->save();
  $installer->endSetup();
}

?>
