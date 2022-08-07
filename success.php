<?php
//This file is part of Moodle - http://moodle.org/
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
 * squareup enrolments Success page.
 *
 * @package    enrol_squareup
 * @copyright  2022 Brain station 23 ltd.
 * @author     Brain station 23 ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

global $DB, $USER, $OUTPUT, $CFG, $PAGE;

$status = required_param('status', PARAM_TEXT);
$reference= required_param('reference', PARAM_RAW);
$parameters= required_param('data', PARAM_RAW);
$userid= $USER->id;

$str_arr = preg_split ("/\_/", $parameters);
$courseid = $str_arr[0];
$instanceid = $str_arr[1];
$currency = $str_arr[2];
$amount = $str_arr[3];

$config = get_config('enrol_squareup');

$prod_env = $config->productionenv;
$apiurl = $config->apiurl;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiurl.$reference);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

$headers = array();
$headers[] = 'X-Business-Api-Key:'.$config->apikey;
$headers[] = 'X-Requested-With: XMLHttpRequest';
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close($ch);

$result = json_decode($result);

if ($reference == $result->id && $result->payments[0]->status == 'succeeded' && $result->payments[0]->currency==strtolower($currency) && $result->payments[0]->amount==$amount && $USER->username == $result->payments[0]->buyer_name) {
    $condition = array();
    $condition['courseid'] = $courseid;
    $condition['instanceid'] = $instanceid;
    $condition['userid'] = $userid;

    if($DB->record_exists('enrol_squareup_log', $condition)) {
       $data = (object) array (
           'courseid' => $courseid,
           'userid' => $userid,
           'instanceid' => $instanceid,
           'currency' => $currency,
           'amount' => $amount,
           'status' => $result->payments[0]->status,
           'reference' => $reference,
           'payment_id' => $result->payments[0]->id,
           'username' => $result->payments[0]->buyer_name,
           'created_at' => $result->payments[0]->created_at,
           'updated_at' => $result->payments[0]->updated_at
       );

     if($DB->insert_record('enrol_squareup', $data)){

         $plugin = enrol_get_plugin('squareup');
         $plugininstance= $DB->get_record("enrol", array("id" => $instanceid, "enrol" => "squareup", "status" => 0));
         $plugin->enrol_user($plugininstance, $userid, $plugininstance->roleid);
         $url = new moodle_url('/course/view.php?id='. $courseid);
         redirect($url);
     }
    }
    else {
            $url = new moodle_url('/course/view.php?id='. $courseid);
            redirect($url);
    }
}