<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

include "../../functions.php";
include "../../config.php";

include "./moduleFunctions.php";

//New PDO DB connection
$pdo = new Gibbon\sqlConnection();
$connection2 = $pdo->getConnection();

@session_start();

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$URL = $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Help Desk/" ;

if (isActionAccessible($guid, $connection2, "/modules/Help Desk/helpDesk_createIssue.php") == FALSE) {
    //Fail 0
    $URL .= "helpDesk_manage.php&return=error0" ;
    header("Location: {$URL}");
} else {
    $URL .= "helpDesk_createIssue.php";

    $data = array("gibbonPersonID" => $_SESSION[$guid]["gibbonPersonID"], "gibbonSchoolYearID" => $_SESSION[$guid]["gibbonSchoolYearID"], "date" => date("Y-m-d"), "createdByID" => $_SESSION[$guid]["gibbonPersonID"]);
    $formData = array("issueName" => true, "description"=>true, "category" => false, "priority" => false, "createFor" => false, "privacySetting" => true);

    foreach ($formData as $key => $value) {
        if (isset($_POST[$key]) && trim($_POST[$key])!=="") {
            if($key == "createFor") {
                $data["createdByID"] = $data["gibbonPersonID"];
                $data["gibbonPersonID"] = trim($_POST[$zkey]);
            } else {
                $data[$key] = trim($_POST[$key]);
            }
        } else if($value) {
            $URL .= "&return=error1";
            header("Location: {$URL}");
            exit();
        }
    }

    try {
        $sql = "INSERT INTO helpDeskIssue SET gibbonPersonID=:gibbonPersonID, issueName=:issueName, description=:description, category=:category, priority=:priority, gibbonSchoolYearID=:gibbonSchoolYearID, createdByID=:createdByID, privacySetting=:privacySetting, `date`=:date";
        $result = $connection2->prepare($sql);
        $result->execute($data);
        $issueID = $connection2->lastInsertId();
    } catch(PDOException $e) {
        $URL .="&return=error2";
        header("Location: {$URL}");
        exit();
    }

    $URL .="&return=success0&issueID=$issueID";
    header("Location: {$URL}");
    exit();
}
?>
