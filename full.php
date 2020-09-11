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
 * Full timetable in the format of a one-day calendar
 *
 * @copyright   2011-2017 Bedford College, 2017 onwards Conn Warwicker
 * @package     block_elbp_timetable
 * @version     1.0
 * @author      Conn Warwicker <conn@cmrwarwicker.com>
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/elbp/lib.php');

$ELBP = block_elbp\ELBP::instantiate( array("load_plugins" => false) );
$DBC = new block_elbp\DB();

// Need to be logged in to view this page
require_login();

// Set up PAGE
$PAGE->set_context( context_course::instance(1) );
$PAGE->set_url($CFG->wwwroot . '/blocks/elbp_timetable/full.php');
$PAGE->set_title( get_string('fulltimetable', 'block_elbp_timetable') );
$PAGE->set_heading( get_string('fulltimetable', 'block_elbp_timetable') );
$PAGE->set_cacheable(true);
$ELBP->loadJavascript();
$ELBP->loadCSS();

// If course is set, put that into breadcrumb
$PAGE->navbar->add( get_string('fulltimetable', 'block_elbp_timetable'), $CFG->wwwroot . '/blocks/elbp_timetable/full.php', navigation_node::TYPE_CUSTOM);
$PAGE->navbar->add( fullname($USER), null, navigation_node::TYPE_CUSTOM);

echo $OUTPUT->header();

try {
    $TT = \block_elbp\Plugins\Plugin::instaniate("elbp_timetable");
    $TT->loadStudent($USER->id);
    $TT->connect();
    $TT->setAccess( array("user" => true) );
    $TT->buildCSS();
    $TT->buildFull( array("format" => true) );
} catch (\block_elbp\ELBPException $e) {
    echo $e->getException();
}


$TPL = new block_elbp\Template();
try {
    $TPL->load($CFG->dirroot . '/blocks/elbp/tpl/footer.html');
    $TPL->display();
} catch (\block_elbp\ELBPException $e) {
    echo $e->getException();
}

echo $OUTPUT->footer();