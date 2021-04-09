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
 * @package     qtype_sc
 * @author      Amr Hourani (amr.hourani@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @author      Jürgen Zimmer (juergen.zimmer@edaktik.at)
 * @author      Andreas Hruska (andreas.hruska@edaktik.at)
 * @copyright   2018 ETHZ {@link http://ethz.ch/}
 * @copyright   2017 eDaktik GmbH {@link http://www.edaktik.at}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/sc/tests/helper.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');

/**
 * @group qtype_sc
 */
class qtype_sc_walkthrough_test extends qbehaviour_walkthrough_test_base {

    protected function start_attempt_at_question($question, $preferredbehaviour,
        $maxmark = null, $variant = 1) {
        $this->quba->set_preferred_behaviour($preferredbehaviour);
        $this->slot = $this->quba->add_question($question, $maxmark);
        $this->quba->start_question($this->slot, $variant);
    }

    public function get_contains_sc_radio_expectation($index, $enabled = null, $checked = null) {
        return $this->get_contains_radio_expectation(array(
            'name' => $this->quba->get_field_prefix($this->slot) . "option",
            'value' => $index,
        ), $enabled, $checked);
    }

    public function make_a_sc_question() {
        question_bank::load_question_definition_classes('sc');
        $sc = new qtype_sc_question();
        test_question_maker::initialise_a_question($sc);
        $sc->qtype = question_bank::get_qtype('sc');
        $sc->name = 'SC001';
        $sc->idnumber = 6;
        $sc->questiontext = 'the correct row is row 1';
        $sc->generalfeedback = 'You should do this and that';
        $sc->answernumbering = 'abc';
        $sc->scoringmethod = "subpoints";
        $sc->options = new stdClass();
        $sc->shuffleanswers = 0;
        $sc->numberofrows = 3;
        $sc->correctrow = 1;
        $sc->rows = array(
            1 => (object) array(
                'id' => 1,
                'questionid' => 6,
                'number' => 1,
                'optiontext' => 'option text 1',
                'optiontextformat' => 1,
                'optionfeedback' => 'feedback text 1',
                'optionfeedbackformat' => 1
            ),
            2 => (object) array(
                'id' => 2,
                'questionid' => 6,
                'number' => 2,
                'optiontext' => 'option text 2',
                'optiontextformat' => 1,
                'optionfeedback' => 'feedback text 2',
                'optionfeedbackformat' => 1
            ),
            3 => (object) array(
                'id' => 3,
                'questionid' => 6,
                'number' => 3,
                'optiontext' => 'option text 3',
                'optiontextformat' => 1,
                'optionfeedback' => 'feedback text 3',
                'optionfeedbackformat' => 1
            )
        );
        return $sc;
    }

    public function test_deferredfeedback_feedback_sc() {
        $rightindex = 0;

        $sc = $this->make_a_sc_question();
        $this->start_attempt_at_question($sc, 'deferredfeedback', 1);
        $this->process_submission(
            array("option" => $rightindex, "distractor1" => 0, "distractor2" => 0)
        );
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_sc_radio_expectation($rightindex, true, true),
            $this->get_contains_sc_radio_expectation($rightindex + 1, true, false),
            $this->get_contains_sc_radio_expectation($rightindex + 2, true, false),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_does_not_contain_feedback_expectation());
        $this->quba->finish_all_questions();
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(1);
        $this->check_current_output(
            $this->get_contains_sc_radio_expectation($rightindex, false, true),
            $this->get_contains_sc_radio_expectation($rightindex + 1, false, false),
            $this->get_contains_sc_radio_expectation($rightindex + 2, false, false),
            $this->get_contains_correct_expectation(),
            new question_pattern_expectation('/name=\".*1_option\".*value=\"0\".*checked=\"checked\"/')
        );
    }
}