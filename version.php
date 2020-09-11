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
 * Version for Timetable
 *
 * @copyright   2011-2017 Bedford College, 2017 onwards Conn Warwicker
 * @package     block_elbp_timetable
 * @version     1.0
 * @author      Conn Warwicker <conn@cmrwarwicker.com>
 */

defined('MOODLE_INTERNAL') or die();

$plugin->version = 2020091100;
$plugin->requires = 2017111300;
$plugin->component = 'block_elbp_timetable';
$plugin->dependencies = array(
    'block_elbp' => 2020031900
);