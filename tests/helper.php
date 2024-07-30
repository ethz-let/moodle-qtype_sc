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
 * Test helper code for qtype_sc.
 *
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
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');

/**
 * Test helper class for qtype_sc.
 *
 * @copyright   2018 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_sc_test_helper extends question_test_helper {

    /**
     * Get predefined questions
     * @return array
     */
    public function get_test_questions() {
        return ['question_one', 'question_two', 'question_three', 'question_four'];
    }

    /**
     * Get the question data, as it would be loaded by get_question_options.
     * @return object
     */
    public function get_sc_question_data_question_one() {
        global $USER;
        $qdata = new stdClass();
        $qdata->qtype = 'sc';
        $qdata->name = 'SC-Question-001';
        $qdata->idnumber = 4010;
        $qdata->category = 1;
        $qdata->contextid = 1;
        $qdata->parent = 0;
        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->length = 0;
        $qdata->hidden = 0;
        $qdata->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $qdata->timecreated = "1552376610";
        $qdata->timemodified = "1552376610";
        $qdata->stamp = "127.0.0.1+1552376610+76EZEc";
        $qdata->version = "127.0.0.1+155237661076EZEc";
        $qdata->defaultmark = 1;
        $qdata->penalty = 0.3333333;
        $qdata->questiontext = "Questiontext for Question 1";
        $qdata->questiontextformat = FORMAT_HTML;
        $qdata->generalfeedback = "This feedback is general";
        $qdata->generalfeedbackformat = FORMAT_HTML;
        $qdata->options = new stdClass();
        $qdata->options->questionid = 4010;
        $qdata->options->scoringmethod = "sconezero";
        $qdata->options->shuffleanswers = 0;
        $qdata->options->answernumbering = 'none';
        $qdata->options->numberofrows = 3;
        $qdata->options->correctrow = 1;
        $qdata->options->rows = [
            1 => (object) [
                'id' => 1,
                'questionid' => 4010,
                'number' => 1,
                'optiontext' => 'Option Text 1',
                'optiontextformat' => 1,
                'optionfeedback' => 'Feedback Text 1',
                'optionfeedbackformat' => 1,
            ],
            2 => (object) [
                'id' => 2,
                'questionid' => 4010,
                'number' => 2,
                'optiontext' => 'Option Text 2',
                'optiontextformat' => 1,
                'optionfeedback' => 'Feedback Text 2',
                'optionfeedbackformat' => 1,
            ],
            3 => (object) [
                'id' => 3,
                'questionid' => 4010,
                'number' => 3,
                'optiontext' => 'Option Text 3',
                'optiontextformat' => 1,
                'optionfeedback' => 'Feedback Text 3',
                'optionfeedbackformat' => 1,
            ],
        ];
        $qdata->hints = [
            0 => (object) [
                'id' => 0,
                'questionid' => 4010,
                'hint' => 'This is the 1st hint',
                'hintformat' => FORMAT_HTML,
                'shownumcorrect' => 0,
                'clearwrong' => 0,
                'options' => 0,
            ],
            1 => (object) [
                'id' => 1,
                'questionid' => 4010,
                'hint' => 'This is the 2nd hint',
                'hintformat' => FORMAT_HTML,
                'shownumcorrect' => 0,
                'clearwrong' => 0,
                'options' => 0,
            ],
        ];
        return $qdata;
    }

    /**
     * Get the question data, as it would be loaded by get_question_options.
     * @return object
     */
    public static function get_sc_question_form_data_question_one() {
        global $USER;
        $qdata = new stdClass();
        $qdata->id = 4010;
        $qdata->createdby = $USER->id;
        $qdata->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'sc';
        $qdata->name = 'SC-Question-001';
        $qdata->questiontext = [
            "text" => 'Questiontext for Question 1',
            'format' => FORMAT_HTML,
            'itemid' => 1,
        ];
        $qdata->generalfeedback = [
            "text" => 'This feedback is general',
            'format' => FORMAT_HTML,
            'itemid' => 2,
        ];
        $qdata->defaultmark = 1;
        $qdata->length = 1;
        $qdata->penalty = 0.3333333;
        $qdata->hidden = 0;
        $qdata->scoringmethod = 'sconezero';
        $qdata->shuffleanswers = 0;
        $qdata->answernumbering = 'none';
        $qdata->numberofrows = 3;
        $qdata->correctrow = 1;
        $qdata->option_1 = [
            'text' => 'Option Text 1',
            'format' => 1,
        ];
        $qdata->feedback_1 = [
            'text' => 'Feedback Text 1',
            'format' => 1,
        ];
        $qdata->option_2 = [
            'text' => 'Option Text 2',
            'format' => 1,
        ];
        $qdata->feedback_2 = [
            'text' => 'Feedback Text 2',
            'format' => 1,
        ];
        $qdata->option_3 = [
            'text' => 'Option Text 3',
            'format' => 1,
        ];
        $qdata->feedback_3 = [
            'text' => 'Feedback Text 3',
            'format' => 1,
        ];
        $qdata->hint = [
            0 => [
                'text' => 'This is the 1st hint',
                'format' => FORMAT_HTML,
            ],
            1  => [
                'text' => 'This is the 2nd hint',
                'format' => FORMAT_HTML,
            ],
        ];
        return $qdata;
    }

    /**
     * Get the question data, as it would be loaded by get_question_options.
     * @return object
     */
    public function get_sc_question_data_question_two() {
        global $USER;
        $qdata = new stdClass();
        $qdata->qtype = 'sc';
        $qdata->name = 'SC-Question-002';
        $qdata->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $qdata->idnumber = 4010;
        $qdata->category = 1;
        $qdata->contextid = 1;
        $qdata->parent = 0;
        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->length = 1;
        $qdata->hidden = 0;
        $qdata->timecreated = "1552376610";
        $qdata->timemodified = "1552376610";
        $qdata->stamp = "127.0.0.1+1552376610+76EZEc";
        $qdata->version = "127.0.0.1+155237661076EZEc";
        $qdata->defaultmark = 1;
        $qdata->penalty = 0.3333333;
        $qdata->questiontext = "Questiontext for Question 1";
        $qdata->questiontextformat = FORMAT_HTML;
        $qdata->generalfeedback = "This feedback is general";
        $qdata->generalfeedbackformat = FORMAT_HTML;
        $qdata->options = new stdClass();
        $qdata->options->questionid = 4010;
        $qdata->options->scoringmethod = "sconezero";
        $qdata->options->shuffleanswers = 1;
        $qdata->options->answernumbering = 'none';
        $qdata->options->numberofrows = 3;
        $qdata->options->correctrow = 1;
        $qdata->options->rows = [
            1 => (object) [
                'id' => 1,
                'questionid' => 4010,
                'number' => 1,
                'optiontext' => 'Option Text 1',
                'optiontextformat' => 1,
                'optionfeedback' => 'Feedback Text 1',
                'optionfeedbackformat' => 1,
            ],
            2 => (object) [
                'id' => 2,
                'questionid' => 4010,
                'number' => 2,
                'optiontext' => 'Option Text 2',
                'optiontextformat' => 1,
                'optionfeedback' => 'Feedback Text 2',
                'optionfeedbackformat' => 1,
            ],
            3 => (object) [
                'id' => 3,
                'questionid' => 4010,
                'number' => 3,
                'optiontext' => 'Option Text 3',
                'optiontextformat' => 1,
                'optionfeedback' => 'Feedback Text 3',
                'optionfeedbackformat' => 1,
            ],
        ];
        $qdata->hints = [
            0 => (object) [
                'id' => 0,
                'questionid' => 4010,
                'hint' => 'This is the 1st hint',
                'hintformat' => FORMAT_HTML,
                'shownumcorrect' => 0,
                'clearwrong' => 0,
                'options' => 0,
            ],
            1 => (object) [
                'id' => 1,
                'questionid' => 4010,
                'hint' => 'This is the 2nd hint',
                'hintformat' => FORMAT_HTML,
                'shownumcorrect' => 0,
                'clearwrong' => 0,
                'options' => 0,
            ],
        ];
        return $qdata;
    }

    /**
     * Get the question data, as it would be loaded by get_question_options.
     * @return object
     */
    public static function get_sc_question_form_data_question_two() {
        global $USER;
        $qdata = new stdClass();
        $qdata->id = 4011;
        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'sc';
        $qdata->name = 'SC-Question-002';
        $qdata->questiontext = [
            "text" => 'Questiontext for Question 1',
            'format' => FORMAT_HTML,
            'itemid' => 1,
        ];
        $qdata->generalfeedback = [
            "text" => 'This feedback is general',
            'format' => FORMAT_HTML,
            'itemid' => 2,
        ];
        $qdata->defaultmark = 1;
        $qdata->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $qdata->length = 1;
        $qdata->penalty = 0.3333333;
        $qdata->hidden = 0;
        $qdata->scoringmethod = 'sconezero';
        $qdata->shuffleanswers = 1;
        $qdata->answernumbering = 'none';
        $qdata->numberofrows = 3;
        $qdata->correctrow = 1;
        $qdata->option_1 = [
            'text' => 'Option Text 1',
            'format' => 1,
        ];
        $qdata->feedback_1 = [
            'text' => 'Feedback Text 1',
            'format' => 1,
        ];
        $qdata->option_2 = [
            'text' => 'Option Text 2',
            'format' => 1,
        ];
        $qdata->feedback_2 = [
            'text' => 'Feedback Text 2',
            'format' => 1,
        ];
        $qdata->option_3 = [
            'text' => 'Option Text 3',
            'format' => 1,
        ];
        $qdata->feedback_3 = [
            'text' => 'Feedback Text 3',
            'format' => 1,
        ];
        $qdata->hint = [
            0 => [
                'text' => 'This is the 1st hint',
                'format' => FORMAT_HTML,
            ],
            1  => [
                'text' => 'This is the 2nd hint',
                'format' => FORMAT_HTML,
            ],
        ];
        return $qdata;
    }

    /**
     * Get the question data, as it would be loaded by get_question_options.
     * @return object
     */
    public static function get_sc_question_form_data_question_three() {
        global $USER;
        $qdata = new stdClass();
        $qdata->id = 4011;
        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'sc';
        $qdata->name = 'SC-Question-003';
        $qdata->questiontext = [
            "text" => 'Questiontext for Question 1',
            'format' => FORMAT_HTML,
            'itemid' => 1,
        ];
        $qdata->generalfeedback = [
            "text" => 'This feedback is general',
            'format' => FORMAT_HTML,
            'itemid' => 2,
        ];
        $qdata->defaultmark = 1;
        $qdata->length = 1;
        $qdata->penalty = 0.3333333;
        $qdata->hidden = 0;
        $qdata->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $qdata->scoringmethod = 'sconezero';
        $qdata->shuffleanswers = 1;
        $qdata->answernumbering = 'none';
        $qdata->numberofrows = 5;
        $qdata->correctrow = 1;
        $qdata->option_1 = [
            'text' => 'Option Text 1',
            'format' => 1,
        ];
        $qdata->feedback_1 = [
            'text' => 'Feedback Text 1',
            'format' => 1,
        ];
        $qdata->option_2 = [
            'text' => 'Option Text 2',
            'format' => 1,
        ];
        $qdata->feedback_2 = [
            'text' => 'Feedback Text 2',
            'format' => 1,
        ];
        $qdata->option_3 = [
            'text' => 'Option Text 3',
            'format' => 1,
        ];
        $qdata->feedback_3 = [
            'text' => 'Feedback Text 3',
            'format' => 1,
        ];
        $qdata->option_4 = [
            'text' => 'Option Text 4',
            'format' => 1,
        ];
        $qdata->feedback_4 = [
            'text' => 'Feedback Text 4',
            'format' => 1,
        ];
        $qdata->option_5 = [
            'text' => 'Option Text 5',
            'format' => 1,
        ];
        $qdata->feedback_5 = [
            'text' => 'Feedback Text 5',
            'format' => 1,
        ];
        $qdata->hint = [
            0 => [
                'text' => 'This is the 1st hint',
                'format' => FORMAT_HTML,
            ],
            1  => [
                'text' => 'This is the 2nd hint',
                'format' => FORMAT_HTML,
            ],
        ];
        return $qdata;
    }

    /**
     * Get the question data, as it would be loaded by get_question_options.
     * @return object
     */
    public static function get_sc_question_form_data_question_four() {
        global $USER;
        $qdata = new stdClass();
        $qdata->id = 4011;
        $qdata->createdby = $USER->id;
        $qdata->modifiedby = $USER->id;
        $qdata->qtype = 'sc';
        $qdata->name = 'SC-Question-004';
        $qdata->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        $qdata->questiontext = [
            "text" => 'Questiontext for Question 1',
            'format' => FORMAT_HTML,
            'itemid' => 1,
        ];
        $qdata->generalfeedback = [
            "text" => 'This feedback is general',
            'format' => FORMAT_HTML,
            'itemid' => 2,
        ];
        $qdata->defaultmark = 1;
        $qdata->length = 1;
        $qdata->penalty = 0.3333333;
        $qdata->hidden = 0;
        $qdata->scoringmethod = 'subpoints';
        $qdata->shuffleanswers = 1;
        $qdata->answernumbering = 'none';
        $qdata->numberofrows = 5;
        $qdata->correctrow = 1;
        $qdata->option_1 = [
            'text' => 'Option Text 1',
            'format' => 1,
        ];
        $qdata->feedback_1 = [
            'text' => 'Feedback Text 1',
            'format' => 1,
        ];
        $qdata->option_2 = [
            'text' => 'Option Text 2',
            'format' => 1,
        ];
        $qdata->feedback_2 = [
            'text' => 'Feedback Text 2',
            'format' => 1,
        ];
        $qdata->option_3 = [
            'text' => 'Option Text 3',
            'format' => 1,
        ];
        $qdata->feedback_3 = [
            'text' => 'Feedback Text 3',
            'format' => 1,
        ];
        $qdata->option_4 = [
            'text' => 'Option Text 4',
            'format' => 1,
        ];
        $qdata->feedback_4 = [
            'text' => 'Feedback Text 4',
            'format' => 1,
        ];
        $qdata->option_5 = [
            'text' => 'Option Text 5',
            'format' => 1,
        ];
        $qdata->feedback_5 = [
            'text' => 'Feedback Text 5',
            'format' => 1,
        ];
        $qdata->hint = [
            0 => [
                'text' => 'This is the 1st hint',
                'format' => FORMAT_HTML,
            ],
            1  => [
                'text' => 'This is the 2nd hint',
                'format' => FORMAT_HTML,
            ],
        ];
        return $qdata;
    }
}
