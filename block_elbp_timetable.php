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

require_once 'elbp_timetable.class.php';

class block_elbp_timetable extends block_base
{
    
    var $TT;
    var $ELBP;
    var $DBC;
    var $blockwww;
    var $blocklang;
    
    public function init()
    {
        global $CFG, $USER;
        $this->title = get_string('timetable', 'block_elbp_timetable');   
        $this->blockwww = $CFG->wwwroot . '/blocks/elbp_timetable/';
        $this->blocklang = get_string_manager()->load_component_strings('block_elbp_timetable', $CFG->lang, true);
        
    }
    
    public function get_content()
    {
        
        global $SITE, $CFG, $COURSE, $USER;
        
        if (!$USER->id){
            return false;
        }
        
        $this->blockwww = $CFG->wwwroot . '/blocks/elbp_timetable/';
        
        try
        {
            $this->TT = \ELBP\Plugins\Plugin::instaniate("elbp_timetable", "/blocks/elbp_timetable/");
            $this->TT->connect();
            $this->TT->loadStudent($USER->id, true);
            $this->ELBP = ELBP\ELBP::instantiate( array("load_plugins" => false) );
            $this->DBC = new ELBP\DB();
        }
        catch (\ELBP\ELBPException $e){
            echo $e->getException();
            exit;
        }
        
        $access = $this->ELBP->getCoursePermissions($COURSE->id);
        
        $this->content = new \stdClass();
        $this->content->footer = '';
        
        // Timetable can't work without it being enabled, as it calls normal methods on object
        if (!$this->TT || !$this->TT->isEnabled()){
            return $this->content;
        }
        
        $dayNumberFormat = $this->TT->getSetting('mis_day_number_format');
        
        if ($dayNumberFormat)
        {
        
            $this->content->text = '';

            $this->content->text .= '<p class="elbp_centre"><small><a href="'.$this->blockwww.'full.php">'.$this->blocklang['showfulltimetable'].'</a></small></p>';

            // Today
            $this->content->text .= '<strong>'.$this->blocklang['today'].'</strong><br>';
            
            $today = date($dayNumberFormat);

            $classes = $this->TT->getClassesByDay( $today );

            foreach( (array)$classes as $class)
            {
                $link = ($class->getCourseID()) ? "<a href='{$CFG->wwwroot}/course/view.php?id={$class->getCourseID()}' target='_blank'>".$class->getDescription()."</a>" : $class->getDescription();
                $this->content->text .= "<span title='".$class->getTextInfo()."'><u>".$class->getStartTime()."-".$class->getEndTime().":</u> {$link}<br></span>";
            }

            $this->content->text .= '<br>';        

            // Tomorrow
            $this->content->text .= '<strong>'.$this->blocklang['tomorrow'].'</strong><br>';

            // Increment day number
            $tommorow = $today + 1;
            
            if ($dayNumberFormat == "N" && $tommorow > 7) $tommorow = 1;
            elseif ($dayNumberFormat == "w" && $tommorow > 6) $tommorow = 0;
            
            $classes = $this->TT->getClassesByDay( $tommorow );

            foreach( (array)$classes as $class)
            {
                $link = ($class->getCourseID()) ? "<a href='{$CFG->wwwroot}/course/view.php?id={$class->getCourseID()}' target='_blank'>".$class->getDescription()."</a>" : $class->getDescription();
                $this->content->text .= "<span title='".$class->getTextInfo()."'><u>".$class->getStartTime()."-".$class->getEndTime().":</u> {$link}<br></span>";
            }
        
        }
        else
        {
            $this->content->text = get_string('err:daynumformat', 'block_elbp_timetable');
        }
        
        if ($access['god']){
            $this->content->text .= '<br><ul class="list"><li><a href="'.$this->blockwww.'config.php"><img src="'.$this->blockwww.'pix/cog.png" alt="Img" class="icon" /> '.get_string('config', 'block_elbp_timetable').'</a></li></ul>';
        }
        
        return $this->content;
        
    }
    
}