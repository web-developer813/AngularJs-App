<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 6/28/2016
 * Time: 11:39 PM
 */

require_once '../common.php';
require_once '../configs/config.php';

$response = array();
$response['error'] = false;

function setErrorResponse($message) {
    global $response;

    $response['error'] = true;
    $response['data'] = $message;
}

function setSuccessResponse($message) {
    global $response;

    $response['error'] = false;
    $response['data'] = $message;
}

if (isset($_GET['csh'])) {
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

    foreach($_GET as $key => $value) {
        $_GET[$key] = $mysqli->real_escape_string($value);
    }

    if (!isset($_GET['newStatus'])) {
        // So we didn't selected status to change to

        $query = "SELECT `id` FROM `{$tablePrefix}appointment`
              WHERE `csh` = '{$_GET['csh']}' AND `appointmentDate` > NOW() + INTERVAL 2 HOUR - INTERVAL 3 DAY";

        $result = $mysqli->query($query);
        if(!$result) {
            setErrorResponse($mysqli->error . "\nQuery: {$query}");
        } else {
            if ($result->num_rows !== 1) {
                setErrorResponse("The link you followed is broken or old.");
            }
        }
    } else {
        $newStatus = $_GET['newStatus'];
        $newDate = isset($_GET['newDate']) && $newStatus == 3 ? $_GET['newDate'] : '';

        $newDateQueryPart = $newDate == '' ? '' : " , `appointmentDate` = '{$newDate}' ";
        $query = "UPDATE `{$tablePrefix}appointment` SET `status` = {$newStatus} {$newDateQueryPart}
        WHERE `csh` = '{$_GET['csh']}' AND `appointmentDate` > NOW() + INTERVAL 2 HOUR - INTERVAL 3 DAY";
        $result = $mysqli->query($query);

        if(!$result) {
            setErrorResponse($mysqli->error . "\nQuery: {$query}");
        } else {
            if ($mysqli->affected_rows !== 1) {
                setErrorResponse("Something went wrong. Please contact web-master.");
            } else {
                setSuccessResponse("You've successfully updated appointment status.");
            }
        }

    }

} else {
    setErrorResponse("The link you followed is broken.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Appointment Manager Portal 2.0</title>

    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-datetimepicker.min.css" rel="stylesheet">

    <script src="../js/jquery-2.2.4.min.js"></script>
    <script src="../js/moment.min.js"></script>
    <script src="../js/bootstrap-datetimepicker.min.js"></script>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style>
        html {
            height: 100%;
        }
        body {
            display: table;
            width: 100%;
            height: 100%;
        }
        #mainWrapper {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        img {
            max-with: 100%;
        }
    </style>
</head>
<body>
<div id="mainWrapper">
    <img src="../images/logo.png" alt="Logo"><br><br>

    <?php
     if ($response['error']) {
        printf("<img src='../images/error.png' alt=''><br><p style='color: #bf3d27'>%s</p>", $response['data']);
     } else {
         if (isset($_GET['newStatus'])) {
             printf("<img src='../images/thumbs-up.png' alt=''><br><p style='color: #009c26'>%s</p>", $response['data']);
         } else {
             ?>

             <form action="" method="get">
                 <input type="hidden" name="csh" value="<?= $_GET['csh']; ?>">

                 <div class="form-group">
                     <label>Please select new status
                         <select name="newStatus" class="form-control">
                             <?php
                             foreach ($statuses as $index => $status) {
                                 printf('<option value="%s">%s</option>', $index, $status);
                             }
                             ?>
                         </select>
                     </label>
                 </div>
                 <div class="form-group newDateGroup" style="display:none">
                     <label>Please select new date</label>
                         <div class="input-group date" id="datetimepicker1" style="max-width: 240px; margin: 0 auto">
                             <input type="text" class="form-control" name="newDate_displayOnly">
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                         </div>

                     <input type="hidden" name="newDate">
                 </div>


                 <button type="submit" class="btn btn-primary">Change</button>
             </form>
            <?php
         }

     }
    ?>


</div>
<script>
    $(document).ready(function() {
        var rescheduleStatusID = 3;

        $('#datetimepicker1').datetimepicker({format: 'MMM D YYYY h:mm A'});

        $('#datetimepicker1').on('dp.change', function(e) {
            $('input[name=newDate]').val(moment(e.date, ["MMM D YYYY h:mm A"]).format("YYYY-MM-DD HH:mm:ss"));
        });

        $("select[name=newStatus]").on('change', function() {
            $(".newDateGroup").hide();

            if ($(this).val() == rescheduleStatusID) {
                $(".newDateGroup").show();
            }
        })
    });
</script>
</body>
</html>
