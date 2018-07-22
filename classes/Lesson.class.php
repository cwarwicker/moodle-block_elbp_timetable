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

namespace ELBP\Plugins\Timetable;

/**
 * 
 */
class Lesson {
    
    private $id;
    private $userid;
    private $course;
    private $description;
    private $startdate;
    private $enddate;
    private $starttime;
    private $endtime;
    private $daynumber;
    private $staff;
    private $room;
    
    private $user;
    private $timetable;
        
    /**
     * Construct lesson object
     * @global type $CFG
     * @global type $DB
     * @param type $timetable
     * @param type $id
     */
    public function __construct($timetable, $id) {
        
        global $CFG, $DB, $MSGS;
                
        // Using MIS connection
        if ($timetable->isUsingMIS())
        {
            
            $obj = $id;
            
            if (is_array($obj)){
                $obj = (object)$obj;
            }
                                                
            // If we are using an MIS connection, the $id will actually be an object. It will be a record from the external DB
            
            // Set properties from that object
            $idField = $timetable->getPluginConnection()->getFieldMap("id");
            $this->id = $obj->$idField;
            
            $userField = $timetable->getPluginConnection()->getFieldMap("username");
            $userNameOrId = $timetable->getMisSetting('mis_username_or_idnumber');
            $user = $DB->get_record("user", array($userNameOrId => $obj->$userField));
            if ($user){
                $this->userid = $user->id;
                $this->user = $user;
            }
            
            $courseField = $timetable->getPluginConnection()->getFieldMap("course");
            $this->course = $obj->$courseField;
            
            // Check if we're trying to link timetable slots to actual Moodle courses
            if ($linkBy = $timetable->getSetting("link_course_by")){
                
                // Check if course exists with given shortnanme/fullname/idnumber/whatever
                $course = $DB->get_record("course", array($linkBy=>$this->course));

                // if it does, add an COURSE_ID property so we can use it later
                if ($course) $this->COURSE_ID = $course->id;
                
            }
            
            
            $descField = $timetable->getPluginConnection()->getFieldAliasOrMap("lesson");
            $this->description = $obj->$descField;
            
            $startDateField = $timetable->getPluginConnection()->getFieldAliasOrMap("startdate");
            $this->startdate = $obj->$startDateField;
            
            $endDateField = $timetable->getPluginConnection()->getFieldAliasOrMap("enddate");
            $this->enddate = $obj->$endDateField;
            
            $startTimeField = $timetable->getPluginConnection()->getFieldAliasOrMap("starttime");
            $this->starttime = $obj->$startTimeField;
            
            $endTimeField = $timetable->getPluginConnection()->getFieldAliasOrMap("endtime");
            $this->endtime = $obj->$endTimeField;
            
            $dayNumField = $timetable->getPluginConnection()->getFieldAliasOrMap("daynum");
            $this->daynumber = (int)$obj->$dayNumField;
            
            $staffField = $timetable->getPluginConnection()->getFieldAliasOrMap("staff");
            $this->staff = $obj->$staffField;
            
            $roomField = $timetable->getPluginConnection()->getFieldAliasOrMap("room");
            $this->room = $obj->$roomField;
                        
            
        }
        else
        {
            
            // Get record from moodle timetable table
            $check = $DB->get_record("lbp_timetable", array("id"=>$id));
            
            // if record exists set properties
            if ($check)
            {
                
                // Set properties of object
                foreach($check as $property => $value)
                {
                    $this->$property = $value;
                }
                
                // Check if we're trying to link timetable slots to actual Moodle courses
                $linkBy = $timetable->getSetting("link_course_by");
                
                if ($linkBy){
                    
                    // Check if course exists with given shortnanme/fullname/idnumber/whatever
                    $course = $DB->get_record("course", array($linkBy=>$this->course));
                    
                    // if it does, add an COURSE_ID property so we can use it later
                    if ($course){
                        $this->COURSE_ID = $course->id;
                    }
                }

            }
            
        }
        
        // Check formats of dates and times
        if (!preg_match("/\d{2}:\d{2}/", $this->starttime)){
            $MSGS['queryerrors'] = get_string('err:invalidformat:starttime', 'block_elbp_timetable');
        }

        if (!preg_match("/\d{2}:\d{2}/", $this->endtime)){
            $MSGS['queryerrors'] = get_string('err:invalidformat:endtime', 'block_elbp_timetable');
        }

        if (!preg_match("/\d{2}\-\d{2}\-\d{4}/", $this->startdate)){
            $MSGS['queryerrors'] = get_string('err:invalidformat:startdate', 'block_elbp_timetable');
        }

        if (!preg_match("/\d{2}\-\d{2}\-\d{4}/", $this->enddate)){
            $MSGS['queryerrors'] = get_string('err:invalidformat:enddate', 'block_elbp_timetable');
        }
        
        $this->timetable = $timetable;
        
    }
    
    /**
     * Get lesson id
     * @return type
     */
    public function getID(){
        return $this->id;
    }
    
    /**
     * Get the ID of the Moodle course if there is one set
     * @return type
     */
    public function getCourseID(){
        return (isset($this->COURSE_ID)) ? $this->COURSE_ID : false;
    }
    
    /**
     * Get the description of the lesson - e.g. it's title
     */
    public function getDescription(){
        return $this->description;
    }
    
    /**
     * Get the lesson's start time
     * @return type
     */
    public function getStartTime(){
        return $this->starttime;
    }
    
    /**
     * Get the lesson's end time
     * @return type
     */
    public function getEndTime(){
        return $this->endtime;
    }
    
    /**
     * Get the lesson's start date in HR format
     * @return type
     */
    public function getStartDate(){
        $date = $this->startdate;
        
        if ($this->timetable->isUsingMIS()){
            $format = $this->timetable->getMisSetting('dateformat');
            $day = \DateTime::createFromFormat($format, $date);
        } else {
            $day = \DateTime::createFromFormat("Ymd", $date);
        }
        
        return $day->format("jS F Y");
    }
    
    /**
     * Get the lesson's end date, in HR format
     * @return type
     */
    public function getEndDate(){
        $date = $this->enddate;
        
        if ($this->timetable->isUsingMIS()){
            $format = $this->timetable->getMisSetting('dateformat');
            $day = \DateTime::createFromFormat($format, $date);
        } else {
            $day = \DateTime::createFromFormat("Ymd", $date);
        }
        
        return $day->format("jS F Y");
    }
    
    /**
     * Get the lesson's day number
     * @return type
     */
    public function getDayNumber(){
        return $this->daynumber;
    }
    
    /**
     * Get the lesson's staff
     * @return type
     */
    public function getStaff(){
        return $this->staff;
    }
    
    /**
     * Get the lesson's room
     * @return type
     */
    public function getRoom(){
        return $this->room;
    }
    
    /**
     * Build the lesson info for a title attribute
     */
    public function getTextInfo(){
        $output = "";
        $output .= elbp_html($this->description)."\n";
        $output .= $this->starttime." - ".$this->endtime."\n";
        if (!empty($this->staff)) $output .= elbp_html($this->staff) . "\n";
        if (!empty($this->room))  $output .= get_string('room', 'block_elbp_timetable') . ": " . elbp_html($this->room);
        return $output;
    }
    
    /**
     * Get info display for popup
     */
    public function getPopupInfo()
    {
        
        $output = "";
        
        $title = elbp_html($this->description);
        
        $output .= "<h2>{$title}</h2>";
        $output .= "{$this->starttime} - {$this->endtime}";
        $output .= "<br>";
        if (!empty($this->staff)) $output .= get_string('teachingstaff', 'block_elbp_timetable') . ": " . elbp_html($this->staff) . "<br>";
        if (!empty($this->room))  $output .= get_string('room', 'block_elbp_timetable') . ": " . elbp_html($this->room) . "<br>";
        $output .= "<br>";
        $output .= "<div class='day_number' style='top:auto;bottom:0px;right:20px;'>".get_string('courseruns', 'block_elbp_timetable').": {$this->getStartDate()} - {$this->getEndDate()}</div>";
        
        return $output;
        
    }
    
    /**
     * Get the tooltip content for the lesson
     * @return string
     */
    public function getTooltipContent()
    {
        
        $output = "";
        
        $output .= $this->starttime . "-" . $this->endtime;
        $output .= " &nbsp;&nbsp; ";
        $output .= "<b>".elbp_html($this->description)."</b>";
        $output .= "<br>";
        
        return $output;
        
    }
    
    /**
     * All lesson content for display if echoing object
     * @return string
     */
    public function __toString() {
        
        $output = "";
        $output .= "ID: " . $this->id . "\n";
        $output .= "UserID: " . $this->userid . "\n";
        $output .= "User Full Name: " . fullname($this->user) . "\n";
        $output .= "Course: {$this->course}\n"; 
        $output .= "Description: {$this->description}\n"; 
        $output .= "Start Date: {$this->startdate}\n"; 
        $output .= "End Date: {$this->enddate}\n"; 
        $output .= "Start Time: {$this->starttime}\n"; 
        $output .= "End Time: {$this->endtime}\n"; 
        $output .= "Day Number: {$this->daynumber}\n"; 
        $output .= "Staff: {$this->staff}\n"; 
        $output .= "Room: {$this->room}\n"; 
        $output .= "\n----------\n";
        return $output;
        
    }
    
    /**
     * Sort array of lessons by start time
     * @param type $lessons
     * @return type
     */
    public static function sortLessons(&$lessons){
        
        usort($lessons, function($a, $b){
            return ($a->getStartTime() > $b->getStartTime());
        });
        
        return $lessons;
        
    }
    
    
    
}