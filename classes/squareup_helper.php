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
 * Various helper methods for interacting with the squareup checkout.
 *
 * @package    enrol_squareup
 * @copyright  2022 Brain station 23 ltd.
 * @author     Brain station 23 ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_squareup;

class squareup_helper {

    public function __construct(string $apiKey, string $apiurl, string $currency, float $cost, string $email, int $courseid, int $instanceid) {
        $this->apiKey = $apiKey;
        $this->apiurl = $apiurl;
        $this->currency = $currency;
        $this->cost = $cost;
        $this->email = $email;
        $this->courseid = $courseid;
        $this->instanceid = $instanceid;
    }

    public function checkout_helper () {
        global $CFG, $USER;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->apiurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, "amount=". $this->cost ."&currency=".$this->currency."&email=".$this->email."&name=".$USER->username."&redirect_url=".$CFG->wwwroot.'/enrol/squareup/success.php?data='.$this->courseid.'_'.$this->instanceid.'_'.$this->currency.'_'.$this->cost);

        $headers = array();
        $headers[] = 'X-Business-Api-Key: '. $this->apiKey;
        $headers[] = 'X-Requested-With: XMLHttpRequest';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return $result;
    }
}
