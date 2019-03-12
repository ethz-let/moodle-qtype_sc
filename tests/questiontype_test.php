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
 * Unit tests for the sc question definition classes.
 *
 * @package qtype_sc
 * @author Martin Hanusch martin.hanusch@let.ethz.ch
 * @copyright ETHz 2019 martin.hanusch@let.ethz.ch
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/sc/questiontype.php');
require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/sc/edit_sc_form.php');

/**
 * @group qtype_sc
 */
class qtype_sc_test extends advanced_testcase {

    protected $qtype;

    protected function setUp() {
        $this->qtype = new qtype_sc();
    }

    protected function tearDown() {
        $this->qtype = null;
    }

    public function test_name() {
        $this->assertEquals($this->qtype->name(), 'sc');
    }

    protected function get_test_question_data() {
        $qdata = new stdClass();
        $qdata->qtype = 'sc';
        $qdata->name = 'SC001';
        $qdata->id = 6;
        $qdata->idnumber = 6;
        $qdata->category = 1;
        $qdata->contextid = 1;
        $qdata->parent = 0;
        $qdata->createdby = 0;
        $qdata->modifiedby = 0;
        $qdata->length = 0;
        $qdata->hidden = 0;
        $qdata->timecreated = "1552376610";
        $qdata->timemodified = "1552376610";
        $qdata->stamp = "127.0.0.1+1552376610+76EZEc";
        $qdata->version = "127.0.0.1+155237661076EZEc";
        $qdata->defaultmark = 1;
        $qdata->penalty = 0.0000000;
        $qdata->questiontext = "Questiontext for Question 1";
        $qdata->questiontextformat = FORMAT_HTML;
        $qdata->generalfeedback = "This feedback is general";
        $qdata->generalfeedbackformat = FORMAT_HTML;
        $qdata->options = new stdClass();
        $qdata->options->questionid = 1;
        $qdata->options->scoringmethod = "subpoints";
        $qdata->options->shuffleanswers = 1;
        $qdata->options->answernumbering = 123;
        $qdata->options->numberofrows = 3;
        $qdata->options->correctrow = 1;
        $qdata->options->rows = array(
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
        return $qdata;
    }

    public function test_can_analyse_responses() {
        $this->assertTrue($this->qtype->can_analyse_responses());
    }

    public function test_get_random_guess_score_sc() {
        $question = $this->get_test_question_data();
        $this->assertEquals(0.33333333333333331, $this->qtype->get_random_guess_score($question), '', 0.0000001);
    }

    public function test_get_random_guess_score_sconzero() {
        $question = $this->get_test_question_data();
        $question->options->scoringmethod = "sconezero";
        $this->assertEquals(0.33333333333333331, $this->qtype->get_random_guess_score($question), '', 0.0000001);
    }

    public function test_get_random_guess_score_aprime() {
        $question = $this->get_test_question_data();
        $question->options->scoringmethod = "aprime";
        $this->assertEquals(0.33333333333333331, $this->qtype->get_random_guess_score($question), '', 0.0000001);
    }

    public function test_get_possible_responses() {
        $question = $this->get_test_question_data();
        $responses = $this->qtype->get_possible_responses($question);
        $this->assertEquals(array( 
                1 => array(11=>new question_possible_response('option text 1', 1)),
                2 => array(21=>new question_possible_response('option text 2', 0)),
                3 => array(31=>new question_possible_response('option text 3', 0)),
            ), $this->qtype->get_possible_responses($question));
    }

    public function get_question_saving_which() {
        return array(array('question_one'), array('question_two'));
    }

    /**
     * @dataProvider get_question_saving_which
     */
    public function test_question_saving_question_one($which) {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $questiondata = test_question_maker::get_question_data('sc', $which);
        $formdata = test_question_maker::get_question_form_data('sc', $which);
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category(array());
        $formdata->category = "{$cat->id},{$cat->contextid}";
        qtype_sc_edit_form::mock_submit((array)$formdata);
        $form = qtype_sc_test_helper::get_question_editing_form($cat, $questiondata);
        $this->assertTrue($form->is_validated());
        $fromform = $form->get_data();
        $returnedfromsave = $this->qtype->save_question($questiondata, $fromform);
        $actualquestionsdata = question_load_questions(array($returnedfromsave->id));
        $actualquestiondata = end($actualquestionsdata);

        foreach ($questiondata as $property => $value) {
            if (!in_array($property, array('id', 'version', 'timemodified', 'timecreated', 'options', 'hints', 'stamp'))) {
                $this->assertAttributeEquals($value, $property, $actualquestiondata);
            }
        }
        foreach ($questiondata->options as $optionname => $value) {
            if ($optionname != 'questionid' && $optionname != 'rows') {
                $this->assertAttributeEquals($value, $optionname, $actualquestiondata->options);
            }
        }
        foreach ($questiondata->hints as $hint) {
            $actualhint = array_shift($actualquestiondata->hints);
            foreach ($hint as $property => $value) {
                if (!in_array($property, array('id', 'questionid', 'options'))) {
                    $this->assertAttributeEquals($value, $property, $actualhint);
                }
            }
        }
        foreach ($questiondata->options->rows as $row) {
            $actualrow = array_shift($actualquestiondata->options->rows);
            foreach ($row as $rowproperty => $rowvalue) {
                if (!in_array($rowproperty, array('id', 'questionid'))) {
                    $this->assertAttributeEquals($rowvalue, $rowproperty, $actualrow);
                }
            }
        }    
    } 

    public function test_get_question_options() {
        global $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $questiondata = test_question_maker::get_question_data('sc', 'question_one');
        $formdata = test_question_maker::get_question_form_data('sc', 'question_two');
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category(array());
        $formdata->category = "{$cat->id},{$cat->contextid}";
        qtype_sc_edit_form::mock_submit((array)$formdata);
        $form = qtype_sc_test_helper::get_question_editing_form($cat, $questiondata);
        $this->assertTrue($form->is_validated());
        $fromform = $form->get_data();
        $returnedfromsave = $this->qtype->save_question($questiondata, $fromform);
        $question = $DB->get_record('question', ['id' => $returnedfromsave->id], '*', MUST_EXIST);
        $this->qtype->get_question_options($question);
        $this->assertDebuggingNotCalled();
        $options = $question->options;
        $this->assertEquals($question->id, $options->questionid);
    }
}