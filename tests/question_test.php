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
global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');

/**
 * @group qtype_sc
 */
class qtype_sc_question_test extends advanced_testcase {

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
        $sc->scoringmethod = "sconezero";
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
        $sc->hints = array(
            0 => (object) array(
                "id" => 0,
                "hint" => "This is the 1st hint",
                "hintformat" => 1,
                "options" => 0,
                "shownumcorrect" => 0,
                "clearwrong" => 0
            ),
            1  => (object) array(
                "id" => 1,
                "hint" => "This is the 2nd hint",
                "hintformat" => 1,
                "options" => 0,
                "shownumcorrect" => 0,
                "clearwrong" => 0
            )
        );
        return $sc;
    }

    public function test_get_expected_data() {
        $question = $this->make_a_sc_question();
        $question->order = array_keys($question->rows);
        $this->assertEquals(array('option0' => PARAM_INT, 'option1' => PARAM_INT, 'option2' => PARAM_INT,
        'distractor0' => PARAM_INT, 'distractor1' => PARAM_INT, 'distractor2' => PARAM_INT), $question->get_expected_data());
    }

    public function test_is_complete_response() {
        $question = $this->make_a_sc_question();
        $question->order = array_keys($question->rows);
        $this->assertFalse($question->is_complete_response(array()));
        $this->assertFalse($question->is_complete_response(array('option0' => '0')));
        $this->assertFalse($question->is_complete_response(array('option0' => '0', 'distractor0' => '0')));
        $this->assertTrue($question->is_complete_response(array('option0' => '1','option1' => '0','option2' => '0')));
        $this->assertTrue($question->is_complete_response(array('option0' => '1', 'distractor0' => '0', 'option1' => '0', 'distractor1' => '0', 'option2' => '0', 'distractor2' => '0')));
    }

    public function test_is_gradable_response_sconezero() {
        // Sconezero
        $question = $this->make_a_sc_question();
        $question->order = array_keys($question->rows);
        $this->assertFalse($question->is_gradable_response(array()));
        $this->assertTrue($question->is_gradable_response(array('option0' => '1')));
        $this->assertFalse($question->is_gradable_response(array('option0' => '0')));
        $this->assertTrue($question->is_gradable_response(array('option0' => '1','option1' => '0', 'option2' => '0')));
        $this->assertTrue($question->is_gradable_response(array('option0' => '1','option1' => '0', 'option2' => '0', 'distractor0' => '0', 'distractor1' => '0', 'distractor2' => '0',)));
    }

    public function test_is_gradable_response_aprime() {
        $question = $this->make_a_sc_question();
        $question->scoringmethod = 'aprime';
        $question->order = array_keys($question->rows);
        $this->assertFalse($question->is_gradable_response(array()));
        $this->assertTrue($question->is_gradable_response(array('option0' => '1')));
        $this->assertFalse($question->is_gradable_response(array('option0' => '0')));
        $this->assertTrue($question->is_gradable_response(array('option0' => '1','option1' => '0', 'option2' => '0')));
        $this->assertTrue($question->is_gradable_response(array('option0' => '1','option1' => '0', 'option2' => '0', 'distractor0' => '0', 'distractor1' => '0', 'distractor2' => '0',)));
    }

    public function test_is_gradable_response_subpoints() {
        $question = $this->make_a_sc_question();
        $question->scoringmethod = 'subpoints';
        $question->order = array_keys($question->rows);
        $this->assertFalse($question->is_gradable_response(array()));
        $this->assertTrue($question->is_gradable_response(array('option0' => '1')));
        $this->assertFalse($question->is_gradable_response(array('option0' => '0')));
        $this->assertTrue($question->is_gradable_response(array('option0' => '1','option1' => '0', 'option2' => '0')));
        $this->assertTrue($question->is_gradable_response(array('option0' => '1','option1' => '0', 'option2' => '0', 'distractor0' => '0', 'distractor1' => '0', 'distractor2' => '0',)));
    }

    public function test_get_order() {
        $question = $this->make_a_sc_question();
        $question->shuffleanswers = 1;
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals( $question->order, $question->get_order(test_question_maker::get_a_qa($question)));
        unset($question);
        $question = $this->make_a_sc_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals( array(0 => 1, 1 => 2, 2 => 3), $question->get_order(test_question_maker::get_a_qa($question)));
    }

    public function test_is_same_response() {
        $question = $this->make_a_sc_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertTrue($question->is_same_response(
            array(),
            array()));
        $this->assertFalse($question->is_same_response(
            array(),
            array('option0' => '1')));
        $this->assertTrue($question->is_same_response(
            array('option0' => '1'),
            array('option0' => '1')));
        $this->assertFalse($question->is_same_response(
            array('option0' => '1'),
            array('option1' => '1')));
        $this->assertFalse($question->is_same_response(
            array('option0' => '1'),
            array('option0' => '0')));
        $this->assertFalse($question->is_same_response(
            array('option0' => '1'),
            array('option1' => '1','option2' => '0')));
        $this->assertFalse($question->is_same_response(
            array('option0' => '1','option2' => '0'),
            array('option1' => '1','option2' => '1')));
        $this->assertTrue($question->is_same_response(
            array('option0' => '1', 'distractor0' => '0'),
            array('option0' => '1', 'distractor0' => '0')));
        $this->assertFalse($question->is_same_response(
            array('option0' => '1', 'distractor0' => '0'),
            array('option0' => '1', 'distractor0' => '1')));
    }

    public function test_grading() {
        $question = $this->make_a_sc_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals(array('option0' => 1, 'option1' => 0, 'option2' => 0), 
        $question->get_correct_response());
    }

    public function test_summarise_response() {
        $question = $this->make_a_sc_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $summary = $question->summarise_response(array('option0' => 1, 'option1' => 0, 'option2' => 0),
        test_question_maker::get_a_qa($question));
        $this->assertEquals('option text 1', $summary);
    }

    public function test_classify_response() {
        $question = $this->make_a_sc_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals(array('0' => new question_classified_response(11, 'option text 1', 1.0)),
        $question->classify_response(array('option0' => '1', 'option1' => '0', 'option2' => '0')));
        $this->assertNotEquals(array('0' => new question_classified_response(11, 'option text 2', 0.0)),
        $question->classify_response(array('option0' => '1', 'option1' => '0', 'option2' => '0')));
        $this->assertEquals(array('0' => new question_classified_response(21, 'option text 2', 0.0)),
        $question->classify_response(array('option0' => '0', 'option1' => '1', 'option2' => '0')));
        $this->assertEquals(array('0' =>  question_classified_response::no_response()),
        $question->classify_response(array('option0' => '0', 'option1' => '0', 'option2' => '0')));
    }

    public function test_make_html_inline() {
        $question = $this->make_a_sc_question();
        $this->assertEquals('Frog', $question->make_html_inline('<p>Frog</p>'));
        $this->assertEquals('Frog<br />Toad', $question->make_html_inline("<p>Frog</p>\n<p>Toad</p>"));
        $this->assertEquals('<img src="http://example.com/pic.png" alt="Graph" />',
        $question->make_html_inline('<p><img src="http://example.com/pic.png" alt="Graph" /></p>'));
        $this->assertEquals("Frog<br />XXX <img src='http://example.com/pic.png' alt='Graph' />",
        $question->make_html_inline(" <p> Frog </p> \n\r<p> XXX <img src='http://example.com/pic.png' alt='Graph' /> </p> "));
        $this->assertEquals('Frog', $question->make_html_inline('<p>Frog</p><p></p>'));
        $this->assertEquals('Frog<br />†', $question->make_html_inline('<p>Frog</p><p>†</p>'));
    }

    public function test_get_hint() {
        $question = $this->make_a_sc_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals('This is the 1st hint', $question->get_hint(0, test_question_maker::get_a_qa($question))->hint);
        $this->assertEquals('This is the 2nd hint', $question->get_hint(1, test_question_maker::get_a_qa($question))->hint);
    }

    public function test_compute_final_grade_sconezero() {
        $question = $this->make_a_sc_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals('1.0', $question->compute_final_grade(array(
            0 => array('option0' => '1','option1' => '0')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('option0' => '0','option1' => '1')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('distractor1' => '1','distractor2' => '1')),
            1));
        $this->assertEquals('0.66666669999999995', $question->compute_final_grade(array(
            0 => array(),
            1 => array('option0' => '1','option1' => '0')),
            1));
        $this->assertEquals('0.3333334', $question->compute_final_grade(array(
            0 => array(),
            1 => array(),
            2 => array('option0' => '1','option1' => '0')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array(),
            1 => array(),
            2 => array(),
            3 => array(),
            4 => array('option0' => '1','option1' => '0')),
            1));
    }

    public function test_compute_final_grade_aprime() {
        $question = $this->make_a_sc_question();
        $question->scoringmethod = 'aprime';
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals('1.0', $question->compute_final_grade(array(
            0 => array('option0' => '1','option1' => '0', 'option2' => '0')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('option0' => '0','option1' => '1', 'option2' => '0')),
            1));
        $this->assertEquals('0.5', $question->compute_final_grade(array(
            0 => array('distractor2' => '1')),
            1));
        $this->assertEquals('0.1666667', $question->compute_final_grade(array(
            0 => array(),
            1 => array('distractor2' => '1')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array(),
            1 => array(),
            2 => array('distractor2' => '1')),
            1));
    }

    public function test_compute_final_grade_subpoints() {
        $question = $this->make_a_sc_question();
        $question->scoringmethod = 'subpoints';
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals('1.0', $question->compute_final_grade(array(
            0 => array('option0' => '1','option1' => '0', 'option2' => '0')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array('option0' => '0','option1' => '1', 'option2' => '0')),
            1));
        $this->assertEquals('0.5', $question->compute_final_grade(array(
            0 => array('distractor2' => '1')),
            1));
        $this->assertEquals('0.1666667', $question->compute_final_grade(array(
            0 => array(),
            1 => array('distractor2' => '1')),
            1));
        $this->assertEquals('0.0', $question->compute_final_grade(array(
            0 => array(),
            1 => array(),
            2 => array('distractor2' => '1')),
            1));
    }

    public function test_grade_response_sconezero() {
        $question = $this->make_a_sc_question();
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals(
            "1.0", $question->grade_response(array('option0' => '1', 'option1' => '0', 'option2' => '0'))[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(array('option0' => '0', 'option1' => '1', 'option1' => '0'))[0]);
    }

    public function test_grade_response_aprime() {
        $question = $this->make_a_sc_question();
        $question->scoringmethod = 'aprime';
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals(
            "1.0", $question->grade_response(array('option0' => '1', 'option1' => '0', 'option2' => '0'))[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(array('option0' => '0', 'option1' => '1', 'option1' => '0'))[0]);
        $this->assertEquals(
            "0.5", $question->grade_response(array('distractor2' => '1'))[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(array('distractor0' => '1'))[0]);
    }

    public function test_grade_response_subpoints() {
        $question = $this->make_a_sc_question();
        $question->scoringmethod = 'subpoints';
        $question->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals(
            "1.0", $question->grade_response(array('option0' => '1', 'option1' => '0', 'option2' => '0'))[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(array('option0' => '0', 'option1' => '1', 'option1' => '0'))[0]);
        $this->assertEquals(
            "0.5", $question->grade_response(array('distractor2' => '1'))[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(array('distractor0' => '1'))[0]);
    }
}