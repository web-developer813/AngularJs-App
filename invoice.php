<?php


require_once 'common.php';
require_once 'connector/functions.php';
require_once 'configs/config.php';

session_start();

if(isset($_SESSION['appointments.user'])) :

	if (!actionIsAllowedForTarget('get', 'invoice')) {
		die("Requested action (get, invoice) is not allowed for user role {$userRole}");
	}

	$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

	foreach($_POST as $key => $value) {
		$_POST[$key] = $mysqli->real_escape_string($value);
	}


/*
    $query = "SELECT f2.fee, a2.*, FROM (SELECT MAX(`date`) as maxDate, a.* FROM
    (SELECT a0.*, concat(u.name, ' ', u.lastName) as appointmentSetterName FROM `{$tablePrefix}appointment` a0
    LEFT JOIN `{$tablePrefix}user` u ON a0.appointmentSetter = u.id ) a

    LEFT JOIN `{$tablePrefix}user_fees` f
    ON a.appointmentSetter = f.userID AND a.appointmentDate > f.date
    WHERE a.id IN({$_POST['invoiceIDs']})
    GROUP BY a.id) a2

    LEFT JOIN `{$tablePrefix}user_fees` f2
    ON a2.appointmentSetter = f2.userID AND a2.maxDate = f2.date

    ORDER BY appointmentDate DESC";*/



    $query = "SELECT f2.fee, a2.*  FROM
    (SELECT MAX(`date`) as maxDate, f.`type`,  a.* FROM
    (SELECT a0.*, concat(u.name, ' ', u.lastName) as appointmentSetterName FROM `{$tablePrefix}appointment` a0
    LEFT JOIN `{$tablePrefix}user` u ON a0.appointmentSetter = u.id ) a

    LEFT JOIN `{$tablePrefix}user_fees` f
    ON a.appointmentSetter = f.userID AND a.appointmentDate > f.`date`
    WHERE a.id IN({$_POST['invoiceIDs']})
    GROUP BY a.id, f.`type`) a2

    JOIN `{$tablePrefix}user_fees` f2
    ON a2.appointmentSetter = f2.userID AND a2.maxDate = f2.`date` AND f2.`type` = a2.`type`

	WHERE f2.`type` = 1 and installation = 1 OR f2.`type` = 2 and installation != 1
    ORDER BY appointmentDate DESC";


	$response = array();

	$result = $mysqli->query($query);
	if(!$result) :
		$response['error'] = true;
		$response['data'] = $mysqli->error . "\nQuery: {$query}";
        echo $response['data'];
	else :
		
		$response['error'] = false;
		$response['data'] = array();
		$total = 0;
		while ($row = $result->fetch_assoc()) {
			$response['data'][] = $row;
			$total += $row['fee'];
		}

        $userDataQuery = "SELECT concat(name, ' ', lastName) as fullName, address, city, zip FROM `{$tablePrefix}user`
                          WHERE id={$response['data'][0]['appointmentSetter']}";

        $result = $mysqli->query($userDataQuery);
        if(!$result) {
            $response['error'] = true;
            $response['data'] = $mysqli->error . "\nQuery: {$query}";
            echo $response['data'];
        } else {
            $userData = $result->fetch_assoc();
        }
?>

<html>
<head>
    <meta http-equiv=Content-Type content="text/html; charset=windows-1251">
    <meta name=Generator content="Microsoft Word 15 (filtered)">
    <style>
        <!--
        /* Font Definitions */
        @font-face
        {font-family:Calibri;
            panose-1:2 15 5 2 2 2 4 3 2 4;}
        @font-face
        {font-family:Cambria;
            panose-1:2 4 5 3 5 4 6 3 2 4;}
        /* Style Definitions */
        p.MsoNormal, li.MsoNormal, div.MsoNormal
        {margin-top:2.0pt;
            margin-right:0in;
            margin-bottom:2.0pt;
            margin-left:0in;
            font-size:10.0pt;
            font-family:"Cambria",serif;
            color:#595959;}
        p.MsoHeader, li.MsoHeader, div.MsoHeader
        {mso-style-link:"Header Char";
            margin-top:2.0pt;
            margin-right:0in;
            margin-bottom:2.0pt;
            margin-left:0in;
            text-align:right;
            font-size:10.0pt;
            font-family:"Cambria",serif;
            color:#595959;}
        p.MsoFooter, li.MsoFooter, div.MsoFooter
        {mso-style-link:"Footer Char";
            margin-top:2.0pt;
            margin-right:5.05pt;
            margin-bottom:0in;
            margin-left:0in;
            margin-bottom:.0001pt;
            border:none;
            padding:0in;
            font-size:10.0pt;
            font-family:"Cambria",serif;
            color:#595959;}
        p.MsoTitle, li.MsoTitle, div.MsoTitle
        {mso-style-link:"Title Char";
            margin-top:6.0pt;
            margin-right:0in;
            margin-bottom:6.0pt;
            margin-left:0in;
            font-size:14.0pt;
            font-family:"Calibri",sans-serif;
            color:white;
            text-transform:uppercase;}
        p.MsoClosing, li.MsoClosing, div.MsoClosing
        {mso-style-link:"Closing Char";
            margin-top:30.0pt;
            margin-right:0in;
            margin-bottom:4.0pt;
            margin-left:0in;
            font-size:10.0pt;
            font-family:"Cambria",serif;
            color:#595959;}
        span.MsoPlaceholderText
        {color:gray;}
        p.MsoNoSpacing, li.MsoNoSpacing, div.MsoNoSpacing
        {mso-style-link:"No Spacing Char";
            margin:0in;
            margin-bottom:.0001pt;
            font-size:10.0pt;
            font-family:"Cambria",serif;
            color:#595959;}
        span.HeaderChar
        {mso-style-name:"Header Char";
            mso-style-link:Header;}
        span.FooterChar
        {mso-style-name:"Footer Char";
            mso-style-link:Footer;}
        span.NoSpacingChar
        {mso-style-name:"No Spacing Char";
            mso-style-link:"No Spacing";}
        span.TitleChar
        {mso-style-name:"Title Char";
            mso-style-link:Title;
            font-family:"Calibri",sans-serif;
            color:white;
            text-transform:uppercase;}
        span.ClosingChar
        {mso-style-name:"Closing Char";
            mso-style-link:Closing;}
        p.TableHeading, li.TableHeading, div.TableHeading
        {mso-style-name:"Table Heading";
            margin-top:2.0pt;
            margin-right:0in;
            margin-bottom:2.0pt;
            margin-left:0in;
            font-size:10.0pt;
            font-family:"Calibri",sans-serif;
            color:#7E97AD;
            text-transform:uppercase;}
        .MsoChpDefault
        {font-size:10.0pt;
            font-family:"Cambria",serif;
            color:#595959;}
        .MsoPapDefault
        {margin-top:2.0pt;
            margin-right:0in;
            margin-bottom:2.0pt;
            margin-left:0in;}
        /* Page Definitions */
        @page WordSection1
        {size:8.5in 11.0in;
            margin:1.9in .75in .75in .75in;}
        div.WordSection1
        {page:WordSection1;}
        -->
    </style>
</head>
<body lang=EN-US>
<div class=WordSection1>
<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0
       summary="Contact information" width="100%" style='width:100.0%;border-collapse:
				collapse'>
    <tr>
        <td width=343 valign=top style='width:257.4pt;padding:0in 5.4pt 0in 5.4pt'>
            <p class=MsoNormal>Avincii</p>
            <p class=MsoNormal>5851 Legacy Circle N0. 600, Plano TX 75024</p>
            <p class=MsoNormal><strong>Tel</strong> (866) 235-8057  <strong>Fax</strong>  (214)
                469-9444
            </p>
        </td>
        <td width=343 valign=top style='width:257.4pt;padding:0in 5.4pt 0in 5.4pt'>
            <p class=MsoHeader><img height=46
                                    src="images/logo.png"></p>
        </td>
    </tr>
</table>
<p class=MsoHeader>&nbsp;</p>
<p class=MsoNormal>&nbsp;</p>
<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
       style='width:100.0%;background:#7E97AD;border-collapse:collapse'>
    <tr>
        <td width="50%" style='width:50.0%;padding:0in 5.4pt 0in 5.4pt'>
            <p class=MsoTitle>Invoice</p>
        </td>
        <td width="50%" style='width:50.0%;padding:0in 5.4pt 0in 5.4pt'>
            <p class=MsoTitle align=right style='text-align:right'><?= date('F j, Y') ?></p>
        </td>
    </tr>
</table>
<p class=MsoNormal>&nbsp;</p>
<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0
       summary="Billing and shipping info table" width="44%" style='width:44.22%;
				border-collapse:collapse'>
    <tr style='height:16.7pt'>
        <td width="100%" valign=top style='width:100.0%;border:none;border-bottom:
						solid #7E97AD 1.0pt;padding:0in 5.4pt 0in 5.4pt;height:16.7pt'>
            <p class=TableHeading>Name</p>
        </td>
    </tr>
    <tr style='height:55.2pt'>
        <td width="100%" valign=top style='width:100.0%;border:none;padding:0in 5.4pt 0in 5.4pt;
						height:55.2pt'>
            <p class=MsoNormal>
                <?= $userData['fullName'] ?><br>
                <?= $userData['address'] ?><br>
                <?= $userData['city'] ?>, <?= $userData['zip'] ?>
            </p>
            <p class=MsoNormal>&nbsp;</p>
        </td>
    </tr>
</table>
<p class=MsoNormal>&nbsp;</p>
<table class=InvoiceTable border=1 cellspacing=0 cellpadding=0
       summary="Invoice table" width="100%" style='width:100.0%;border-collapse:collapse;
				border:none'>
<thead>
<tr>
    <td width="8%" style='width:8.92%;border:none;border-bottom:solid #D9D9D9 1.0pt;
							background:#7E97AD;padding:0in 5.4pt 0in 5.4pt'>
        <p class=MsoNormal style='margin-top:4.0pt;margin-right:0in;margin-bottom:
								4.0pt;margin-left:0in'><span style='font-family:"Calibri",sans-serif;
								color:white;text-transform:uppercase'>ID</span></p>
    </td>
    <td width="51%" style='width:51.08%;border:none;border-bottom:solid #D9D9D9 1.0pt;
							background:#7E97AD;padding:0in 5.4pt 0in 5.4pt'>
        <p class=MsoNormal style='margin-top:4.0pt;margin-right:0in;margin-bottom:
								4.0pt;margin-left:0in'><span style='font-family:"Calibri",sans-serif;
								color:white;text-transform:uppercase'>Description</span></p>
    </td>
    <td width="20%" style='width:20.0%;border:none;border-bottom:solid #D9D9D9 1.0pt;
							background:#7E97AD;padding:0in 5.4pt 0in 5.4pt'>
        <p class=MsoNormal align=right style='margin-top:4.0pt;margin-right:0in;
								margin-bottom:4.0pt;margin-left:0in;text-align:right'><span
                style='font-family:"Calibri",sans-serif;color:white;text-transform:uppercase'>Unit
								Price</span>
        </p>
    </td>
    <td width="20%" style='width:20.0%;border:none;border-bottom:solid #D9D9D9 1.0pt;
							background:#7E97AD;padding:0in 5.4pt 0in 5.4pt'>
        <p class=MsoNormal align=right style='margin-top:4.0pt;margin-right:0in;
								margin-bottom:4.0pt;margin-left:0in;text-align:right'><span
                style='font-family:"Calibri",sans-serif;color:white;text-transform:uppercase'>Total</span></p>
    </td>
</tr>
</thead>


<?php

    $rowTpl = "<tr>
        <td width='8%%' style='width:8.92%%;border:none;border-bottom:solid #D9D9D9 1.0pt;padding:0in 5.4pt 0in 5.4pt'>
            <p class=MsoNormal style='margin-top:4.0pt;margin-right:0in;margin-bottom:4.0pt;margin-left:0in'>
                %s
            </p>
        </td>
        <td width='51%%' style='width:51.08%%;border:none;border-bottom:solid #D9D9D9 1.0pt;padding:0in 5.4pt 0in 5.4pt'>
            <p class=MsoNormal style='margin-top:4.0pt;margin-right:0in;margin-bottom:4.0pt;margin-left:0in'>
                %s
            </p>
        </td>
        <td width='20%%' style='width:20.0%%;border:none;border-bottom:solid #D9D9D9 1.0pt;padding:0in 5.4pt 0in 5.4pt'>
            <p class=MsoNormal align=right style='margin-top:4.0pt;margin-right:0in;margin-bottom:4.0pt;margin-left:0in;text-align:right'>
                %s
            </p>
        </td>
        <td width='20%%' style='width:20.0%%;border:none;border-bottom:solid #D9D9D9 1.0pt;padding:0in 5.4pt 0in 5.4pt'>
            <p class=MsoNormal align=right style='margin-top:4.0pt;margin-right:0in;margin-bottom:4.0pt;margin-left:0in;text-align:right'>
                %s
            </p>
        </td>
    </tr>";

    foreach ($response['data'] as $row) {
        printf($rowTpl,
            $row['id'],
            "{$row['name']} {$row['lastName']}<br>{$row['address']}<br>{$row['city']}, {$row['zip']}",
            $row['fee'],
            $row['fee']
        );
    }
//echo "Total: $" . $total . ".00";
?>


</table>
<p class=MsoNoSpacing><span style='font-size:2.0pt'>&nbsp;</span></p>
<div align=right>
    <table class=InvoiceTable border=1 cellspacing=0 cellpadding=0
           summary="Invoice totals" width="50%" style='width:50.0%;border-collapse:collapse;
					border:none'>
        <tr>
            <td width="60%" style='width:60.0%;border:none;border-bottom:solid #D9D9D9 1.0pt;
							padding:0in 5.4pt 0in 5.4pt'>
                <p class=TableHeading align=right style='margin-top:4.0pt;margin-right:0in;
								margin-bottom:4.0pt;margin-left:0in;text-align:right'>Subtotal</p>
            </td>
            <td width="40%" style='width:40.0%;border:none;border-bottom:solid #D9D9D9 1.0pt;
							padding:0in 5.4pt 0in 5.4pt'>
                <p class=MsoNormal align=right style='margin-top:4.0pt;margin-right:0in;
								margin-bottom:4.0pt;margin-left:0in;text-align:right'><?=$total ?></p>
            </td>
        </tr>
        <tr>
            <td width="60%" style='width:60.0%;border:none;border-bottom:solid #D9D9D9 1.0pt;
							padding:0in 5.4pt 0in 5.4pt'>
                <p class=TableHeading align=right style='margin-top:4.0pt;margin-right:0in;
								margin-bottom:4.0pt;margin-left:0in;text-align:right'>Sales Tax</p>
            </td>
            <td width="40%" style='width:40.0%;border:none;border-bottom:solid #D9D9D9 1.0pt;
							padding:0in 5.4pt 0in 5.4pt'>
                <p class=MsoNormal align=right style='margin-top:4.0pt;margin-right:0in;
								margin-bottom:4.0pt;margin-left:0in;text-align:right'>NA</p>
            </td>
        </tr>
        <tr>
            <td width="60%" style='width:60.0%;border:none;border-bottom:solid #A6A6A6 1.0pt;
							padding:0in 5.4pt 0in 5.4pt'>
                <p class=TableHeading align=right style='margin-top:4.0pt;margin-right:0in;
								margin-bottom:4.0pt;margin-left:0in;text-align:right'>&nbsp;</p>
            </td>
            <td width="40%" style='width:40.0%;border:none;border-bottom:solid #A6A6A6 1.0pt;
							padding:0in 5.4pt 0in 5.4pt'>
                <p class=MsoNormal align=right style='margin-top:4.0pt;margin-right:0in;
								margin-bottom:4.0pt;margin-left:0in;text-align:right'>&nbsp;</p>
            </td>
        </tr>
        <tr>
            <td width="60%" style='width:60.0%;border:none;border-bottom:solid #A6A6A6 1.0pt;
							padding:0in 5.4pt 0in 5.4pt'>
                <p class=TableHeading align=right style='margin-top:4.0pt;margin-right:0in;
								margin-bottom:4.0pt;margin-left:0in;text-align:right'><strong>Total Due </strong></p>
            </td>
            <td width="40%" style='width:40.0%;border:none;border-bottom:solid #A6A6A6 1.0pt;
							padding:0in 5.4pt 0in 5.4pt'>
                <p class=MsoNormal align=right style='margin-top:4.0pt;margin-right:0in;
								margin-bottom:4.0pt;margin-left:0in;text-align:right'><strong><?=$total ?></strong></p>
            </td>
        </tr>
        <tr>
            <td width="100%" colspan=2 valign=bottom style='width:100.0%;border:none;
							padding:0in 5.4pt 0in 5.4pt'>
                <p class=MsoNormal style='margin-top:4.0pt;margin-right:0in;margin-bottom:
								4.0pt;margin-left:0in'>Thank you!</p>
            </td>
        </tr>
    </table>
</div>
<p class=MsoNormal>&nbsp;</p>
</div>
</body>
</html>

    <?php
    endif;
    $mysqli->close();
endif;
?>