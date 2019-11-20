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

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/question/type/sc/lib.php');

    // Introductory explanation that all the settings are defaults for the edit_sc_form.
    $settings->add(new admin_setting_heading('configintro', '', get_string('configintro', 'qtype_sc')));

    // Scoring methods.
    $options = array('sconezero' => get_string('scoringsconezero', 'qtype_sc'),
        'aprime' => get_string('scoringaprime', 'qtype_sc'),
        'subpoints' => get_string('scoringsubpoints', 'qtype_sc')
    );

    $settings->add(new admin_setting_configselect('qtype_sc/scoringmethod',
        get_string('scoringmethod', 'qtype_sc'),
        get_string('scoringmethod_help', 'qtype_sc'), 'sconezero', $options));

    // Show Scoring Method in quizes.
    $settings->add(new admin_setting_configcheckbox('qtype_sc/showscoringmethod',
        get_string('showscoringmethod', 'qtype_sc'),
        get_string('showscoringmethod_help', 'qtype_sc'), 0));

    // Shuffle options.
    $settings->add(new admin_setting_configcheckbox('qtype_sc/shuffleanswers',
        get_string('shuffleanswers', 'qtype_sc'),
        get_string('shuffleanswers_help', 'qtype_sc'), 1));
}
