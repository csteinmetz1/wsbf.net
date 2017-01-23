<?php

/**
 * @file schedule/show.php
 * @author Ben Shealy
 *
 * Get, update, or remove a show in the schedule.
 */
require_once("../auth/auth.php");
require_once("../connect.php");
require_once("functions.php");

/**
 * Validate a show.
 *
 * @param mysqli
 * @param show
 * @return true if show is valid, false otherwise
 */
function validate_show($mysqli, $show)
{
	if ( !is_numeric($show["dayID"])
	  || empty($show["start_time"])
	  || empty($show["end_time"])
	  || !is_numeric($show["show_typeID"])
	  || !is_array($show["hosts"]) ) {
		return false;
	}

	// end time should be after start time
	if ( $show["start_time"] != "23:00:00"
		&& $show["end_time"] <= $show["start_time"] ) {
		return false;
	}

	// show should have at least one host
	if ( count($show["hosts"]) < 1 ) {
		return false;
	}

	// show should not start at the same time as another active show
	$q = "SELECT s.scheduleID FROM `schedule` AS s "
		. "WHERE s.active=1 AND s.dayID='$show[dayID]' "
		. "AND s.start_time='$show[start_time]' "
		. "AND s.scheduleID != '$show[scheduleID]';";
	$result = exec_query($mysqli, $q);

	if ( $result->num_rows > 0 ) {
		return false;
	}

	return true;
}

/**
 * Add a show to the schedule.
 *
 * @param mysqli
 * @param show
 */
function add_show($mysqli, $show)
{
	// insert show
	$q = "INSERT INTO `schedule` SET "
		. "show_name = '$show[show_name]', "
		. "dayID = '$show[dayID]', "
		. "start_time = '$show[start_time]', "
		. "end_time = '$show[end_time]', "
		. "show_typeID = '$show[show_typeID]', "
		. "active = 1;";
	exec_query($mysqli, $q);

	// insert show hosts
	$scheduleID = $mysqli->insert_id;

	foreach ( $show["hosts"] as $h ) {
		$q = "INSERT INTO `schedule_hosts` SET "
			. "scheduleID = '$scheduleID', "
			. "username = '$h[username]';";
		exec_query($mysqli, $q);
	}
}

/**
 * Update a show in the schedule.
 *
 * @param mysqli
 * @param show
 */
function update_show($mysqli, $show)
{
	// update show
	$q = "UPDATE `schedule` SET "
		. "show_name = '$show[show_name]', "
		. "dayID = '$show[dayID]', "
		. "start_time = '$show[start_time]', "
		. "end_time = '$show[end_time]', "
		. "show_typeID = '$show[show_typeID]', "
		. "active = 1 "
		. "WHERE scheduleID = '$show[scheduleID]';";
	exec_query($mysqli, $q);

	// update show hosts
	$q = "DELETE FROM `schedule_hosts` WHERE scheduleID='$show[scheduleID]';";
	exec_query($mysqli, $q);

	foreach ( $show["hosts"] as $h ) {
		$q = "INSERT INTO `schedule_hosts` SET "
			. "scheduleID = '$show[scheduleID]', "
			. "username = '$h[username]';";
		exec_query($mysqli, $q);
	}
}

/**
 * Remove a show from the schedule.
 *
 * @param mysqli
 * @param scheduleID
 */
function remove_show($mysqli, $scheduleID)
{
	$q = "UPDATE `schedule` SET "
		. "active = 0 "
		. "WHERE scheduleID = '$scheduleID';";
	exec_query($mysqli, $q);
}

if ( $_SERVER["REQUEST_METHOD"] == "GET" ) {
	$mysqli = construct_connection();

	$scheduleID = $_GET["scheduleID"];

	if ( !is_numeric($scheduleID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	$show = get_schedule_show($mysqli, $scheduleID);
	$mysqli->close();

	header("Content-Type: application/json");
	exit(json_encode($show));
}
else if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
	authenticate();
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$show = json_decode(file_get_contents("php://input"), true);
	$show = escape_json($mysqli, $show);

	if ( !validate_show($mysqli, $show) ) {
		header("HTTP/1.1 404 Not Found");
		exit("Submitted show data is invalid.");
	}

	if ( isset($show["scheduleID"]) ) {
		update_show($mysqli, $show);
	}
	else {
		add_show($mysqli, $show);
	}

	$mysqli->close();

	exit;
}
else if ( $_SERVER["REQUEST_METHOD"] == "DELETE" ) {
	authenticate();
	$mysqli = construct_connection();

	if ( !check_senior_staff($mysqli) ) {
		header("HTTP/1.1 401 Unauthorized");
		exit;
	}

	$scheduleID = $_GET["scheduleID"];

	if ( !is_numeric($scheduleID) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}

	remove_show($mysqli, $scheduleID);
	$mysqli->close();

	exit;
}
?>
