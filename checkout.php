<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * squareup enrolments Checkout page.
 *
 * @package    enrol_squareup
 * @copyright  2021 Brain station 23 ltd.
 * @author     Brain station 23 ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use enrol_squareup\squareup_helper;

require_once ('../../config.php');

global $DB;

$config         = get_config('enrol_squareup');
$cost           = required_param('amount', PARAM_FLOAT);
$currency       = required_param('currency_code', PARAM_RAW);
$email          = required_param('email', PARAM_RAW);
$courseid       = required_param('course_id', PARAM_INT);
$userid         = required_param('userid', PARAM_INT);
$instanceid     = required_param('instance_id', PARAM_INT);
$fullname       = required_param('name', PARAM_RAW);
$number         = required_param('mobile', PARAM_INT);
$address        = required_param('address', PARAM_RAW);
$coursename     = required_param('coursename', PARAM_RAW);


$apiKey = $config->apikey;
$apiurl = $config->apiurl;

$squareup_helper = new squareup_helper($apiKey, $apiurl, $currency, $cost, $email, $courseid, $instanceid);
$checkout = $squareup_helper->checkout_helper();
$checkout = json_decode($checkout);
$timeupdated = time();

$SQL = "INSERT INTO {enrol_squareup_log}
        (courseid, coursename, userid, instanceid, currency,
        cost, payment_status, name, address, mobile, email, timeupdated)
        VALUES
        ($courseid, '$coursename', $userid,$instanceid, '$currency',
            $cost, 'pending','$fullname', '$address',$number,'$email',$timeupdated)";

$DB->execute($SQL);

header('Location: ' . $checkout->url);
