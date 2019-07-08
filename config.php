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
 * Electronic Learning Blue Print
 *
 * ELBP is a moodle block plugin, which provides one singular place for all of a student's key academic information to be stored and viewed, such as attendance, targets, tutorials,
 * reports, qualification progress, etc... as well as unlimited custom sections.
 *
 * @package     block_elbp
 * @subpackage  block_elbp_timetable
 * @copyright   2017-onwards Conn Warwicker
 * @author      Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_elbp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Originally developed at Bedford College, now maintained by Conn Warwicker
 *
 */

require_once '../../config.php';
require_once $CFG->dirroot . '/blocks/elbp/lib.php';

$ELBP = ELBP\ELBP::instantiate( array("load_plugins" => false) );
$DBC = new ELBP\DB();

$view = optional_param('view', 'main', PARAM_ALPHA);

$access = $ELBP->getCoursePermissions(1);
if (!$access['god']){
    print_error( get_string('invalidaccess', 'block_elbp') );
}

// Need to be logged in to view this page
require_login();

try {
    $TT = \ELBP\Plugins\Plugin::instaniate("elbp_timetable");
} catch (\ELBP\ELBPException $e) {
    echo $e->getException();
    exit;
}


$TPL = new \ELBP\Template();
$MSGS['errors'] = array();
$MSGS['success'] = '';

// Submitted
if (!empty($_POST))
{
    if ($TT->saveConfig($_POST)){
        $MSGS['success'] = get_string('settingsupdated', 'block_elbp');
        $TPL->set("MSGS", $MSGS);
    }
}

// Set up PAGE
$PAGE->set_context( context_course::instance(1) );
$PAGE->set_url($CFG->wwwroot . '/blocks/elbp_timetable/config.php');
$PAGE->set_title( get_string('timetableconfig', 'block_elbp_timetable') );
$PAGE->set_heading( get_string('timetableconfig', 'block_elbp_timetable') );
$PAGE->set_cacheable(true);
$ELBP->loadJavascript();
$ELBP->loadCSS();

// If course is set, put that into breadcrumb
$PAGE->navbar->add( get_string('timetableconfig', 'block_elbp_timetable'), $CFG->wwwroot . '/blocks/elbp_timetable/config.php', navigation_node::TYPE_CUSTOM);

echo $OUTPUT->header();

$TPL->set("TT", $TT);
$TPL->set("view", $view);
$TPL->set("MSGS", $MSGS);

switch($view)
{
    case 'data':

        // Create directory for template csvs
        $TT->createDataDirectory('templates');

        $reload = (bool)optional_param('reload', 0, PARAM_INT);

        // If template csv doesn't exist, create it, otherwise get the file path
        $importFile = $TT->createTemplateImportCsv($reload);
        $TPL->set("importFile", $importFile);

        // If example csv doesn't exist, create it, otherwise get the file path
        $exampleFile = $TT->createExampleImportCsv($reload);
        $TPL->set("exampleFile", $exampleFile);

    break;

    case 'mis':

        $core = $TT->getMainMIS();
        if ($core){
            $conn = new \ELBP\MISConnection($core->id);
            $TPL->set("conn", $conn);
        }

    break;

}


try {
    $TPL->load( $CFG->dirroot . '/blocks/elbp_timetable/tpl/config.html' );
    $TPL->display();
} catch (\ELBP\ELBPException $e){
    echo $e->getException();
}

echo $OUTPUT->footer();