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
 * Print the full timetable in the format of a one-day calendar
 *
 * @copyright   2011-2017 Bedford College, 2017 onwards Conn Warwicker
 * @package     block_elbp_timetable
 * @version     1.0
 * @author      Conn Warwicker <conn@cmrwarwicker.com>
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/elbp/lib.php');

$ELBP = block_elbp\ELBP::instantiate();
$DBC = new block_elbp\DB();

// Need to be logged in to view this page
require_login();

try {
    $TT = \block_elbp\Plugins\Plugin::instaniate("elbp_timetable");
    $TT->loadStudent($USER->id);
    $TT->connect();
    $TPL = new block_elbp\Template();
    $TPL->set("TT", $TT);
    $TPL->load($CFG->dirroot . '/blocks/elbp_timetable/tpl/print.html');
    $TPL->display();
} catch (\block_elbp\ELBPException $e) {
    echo $e->getException();
}