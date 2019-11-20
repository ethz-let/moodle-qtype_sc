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
  * @package    qtype_sc
  * @author     Amr Hourani (amr.hourani@id.ethz.ch)
  * @author     Martin Hanusch (martin.hanusch@let.ethz.ch)
  * @author     Jürgen Zimmer (juergen.zimmer@edaktik.at)
  * @author     Andreas Hruska (andreas.hruska@edaktik.at)
  * @copyright  2018 ETHZ {@link http://ethz.ch/}
  * @copyright  2017 eDaktik GmbH {@link http://www.edaktik.at}
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The default number of options. Options are called 'rows' in the database to distinguish from Moodle question options.
 */
define('QTYPE_SC_NUMBER_OF_OPTIONS', 3);
define('QTYPE_SC_MAX_NUMBER_OF_OPTIONS', 5);

/**
 * Checks file/image access for sc questions.
 *
 * @category files
 *
 * @param stdClass $course        course object
 * @param stdClass $cm            course module object
 * @param stdClass $context       context object
 * @param string   $filearea      file area
 * @param array    $args          extra arguments
 * @param bool     $forcedownload whether or not force download
 * @param array    $options       additional options affecting the file serving
 *
 * @return bool
 */
function qtype_sc_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload,
        array $options = array()) {
    global $CFG;
    require_once($CFG->libdir.'/questionlib.php');
    question_pluginfile($course, $context, 'qtype_sc', $filearea, $args, $forcedownload,
    $options);
}
