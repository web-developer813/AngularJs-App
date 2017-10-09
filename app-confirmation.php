<?php

require_once 'common.php';
require_once 'configs/config.php';

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

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

foreach($_POST as $key => $value) {
    $_POST[$key] = $mysqli->real_escape_string($value);
}
foreach($_GET as $key => $value) {
    $_GET[$key] = $mysqli->real_escape_string($value);
}

function notifyAdmins() {
    global $tablePrefix, $mysqli, $declineReasons;

    $query = sprintf("SELECT `email` FROM `{$tablePrefix}user` WHERE `role` in (%s, %s)", ADMIN_ROLE_ID, SUPERADMIN_ROLE_ID);
    $result = $mysqli->query($query);

    if(!$result) {
        setErrorResponse($mysqli->error . "\nQuery: {$query}");
    } else {
        $from = "info@avincii.com";

        $headers = "MIME-Version: 1.0\r\n";
        $headers.= "From: $from\r\n";
        $headers.= "Content-Type: text/html;charset=utf-8\r\n";
        $headers.= "Reply-To: $from\r\n";
        $headers.= "X-Mailer: PHP/" . phpversion();


            $subject = "Appointment #{$_GET['id']} has been declined by salesman";
            $message = "Appointment #{$_GET['id']} has been declined by salesman<br>Reason: {$declineReasons[$_GET['decline-reason']]}";



        while ($row = $result->fetch_array()) {
            myMail($row[0], $subject, $message, $headers);
        }
    }
}


$response = array();
$response['error'] = false;


if(!in_array($_GET['accepted'], array(0, 1))) {
    setErrorResponse('The link you followed is broken. Please contact webmaster.');
} else {


    $query = "SELECT `id` FROM `{$tablePrefix}appointment`
              WHERE `id` = {$_GET['id']} AND `confirmationHash` = '{$_GET['hash']}' AND `accepted` IS NULL";

    $result = $mysqli->query($query);
    if(!$result) {
        setErrorResponse($mysqli->error . "\nQuery: {$query}");
    } else {
        if ($result->num_rows !== 1) {
            setErrorResponse('The link you followed is broken or old. Please contact webmaster.');
        } else {

            if ($_GET['accepted'] == 1 || ($_GET['accepted'] == 0 && isset($_GET['decline-reason']) )) {

                if (isset($_GET['decline-reason'])) {
                    $_GET['decline-reason'] = isset($declineReasons[$_GET['decline-reason']]) ? $_GET['decline-reason'] : 0;
                }


                $query = sprintf("UPDATE `{$tablePrefix}appointment` SET `accepted` = {$_GET['accepted']} %s
                                WHERE `id` = {$_GET['id']} AND `confirmationHash` = '{$_GET['hash']}' AND `accepted` IS NULL",
                    $_GET['accepted'] == 0 ? ", `declineReason` = {$_GET['decline-reason']} " : "");

                $result = $mysqli->query($query);
                if(!$result) {
                    setErrorResponse($mysqli->error . "\nQuery: {$query}");
                } else {
                    if ($_GET['accepted'] == 1) {
                        setSuccessResponse("You have successfully accepted the appointment.");
                    } else {
                        setSuccessResponse("You have successfully declined the appointment.");
                        notifyAdmins();
                    }


                }

            }

        }
    }


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

    <!-- Custom favicon-->
    <link rel="shortcut icon" href="http://avincii.com/wp-content/uploads/2015/09/favicon.png" />
    <!-- Retina/iOS favicon -->
    <link rel="apple-touch-icon-precomposed" href="http://avincii.com/wp-content/uploads/2015/09/favicon.png" />

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style>
        html, body {
            padding: 0;
            margin: 0;
        }
        html {
            height: 100%;
        }
        body {
            display: table;
            width: 100%;
            height: 100%;
            font-family: Arial;
        }
        body > div {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
    </style>
</head>
<body>
<div>
    <img src="images/logo.png" alt="Logo"><br><br>
    <?php
        if ($response['error']) {
            printf("<img src='images/error.png' alt=''><br><p style='color: #bf3d27'>%s</p>", $response['data']);
        } else {
            if ($_GET['accepted'] == 1) {
                printf("<img src='images/thumbs-up.png' alt=''><br><p style='color: #009c26'>%s</p>", $response['data']);
            } else {
                if (!isset($_GET['decline-reason'])) {
                    ?>


                    <img src='images/thumbs-down.png' alt=''><br>
                    <form action="" method="get">
                        <input type="hidden" name="hash" value="<?= $_GET['hash']; ?>">
                        <input type="hidden" name="id" value="<?= $_GET['id']; ?>">
                        <input type="hidden" name="accepted" value="<?= $_GET['accepted']; ?>">

                        <label>Please select the reason<br><br>
                            <select name="decline-reason">
                                <?php
                                    foreach ($declineReasons as $index => $reason) {
                                        printf('<option value="%s">%s</option>', $index, $reason);
                                    }
                                ?>
                            </select>
                        </label> <button type="submit">Decline</button>
                    </form>

                <?php
                } else {
                    printf("<img src='images/thumbs-down.png' alt=''><br><p style='color: #666'>%s</p>", $response['data']);
                }
            }
        }
    ?>
</div>
</body>
</html>
