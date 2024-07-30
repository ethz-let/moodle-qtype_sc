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
 * Unit tests for qtype_sc definition class.
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

namespace qtype_sc;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');

/**
 * Unit tests for qtype_sc question definition class.
 *
 * @copyright   2018 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group       qtype_sc
 */
final class question_test extends \advanced_testcase {

    /**
     * Makes a qtype_sc question.
     * @return qtype_sc
     */
    public function make_a_sc_question() {
        \question_bank::load_question_definition_classes('sc');
        $sc = new \qtype_sc_question();
        \test_question_maker::initialise_a_question($sc);
        $sc->qtype = \question_bank::get_qtype('sc');
        $sc->name = 'SC001';
        $sc->idnumber = 6;
        $sc->questiontext = 'the correct row is row 1';
        $sc->generalfeedback = 'You should do this and that';
        $sc->answernumbering = 'abc';
        $sc->scoringmethod = "sconezero";
        $sc->options = new \stdClass();
        $sc->shuffleanswers = 0;
        $sc->numberofrows = 3;
        $sc->correctrow = 1;
        $sc->rows = [
            1 => (object) [
                'id' => 1,
                'questionid' => 6,
                'number' => 1,
                'optiontext' => 'option text 1',
                'optiontextformat' => 1,
                'optionfeedback' => 'feedback text 1',
                'optionfeedbackformat' => 1,
            ],
            2 => (object) [
                'id' => 2,
                'questionid' => 6,
                'number' => 2,
                'optiontext' => 'option text 2',
                'optiontextformat' => 1,
                'optionfeedback' => 'feedback text 2',
                'optionfeedbackformat' => 1,
            ],
            3 => (object) [
                'id' => 3,
                'questionid' => 6,
                'number' => 3,
                'optiontext' => 'option text 3',
                'optiontextformat' => 1,
                'optionfeedback' => 'feedback text 3',
                'optionfeedbackformat' => 1,
            ],
        ];
        $sc->hints = [
            0 => (object) [
                "id" => 0,
                "hint" => "This is the 1st hint",
                "hintformat" => 1,
                "options" => 0,
                "shownumcorrect" => 0,
                "clearwrong" => 0,
            ],
            1  => (object) [
                "id" => 1,
                "hint" => "This is the 2nd hint",
                "hintformat" => 1,
                "options" => 0,
                "shownumcorrect" => 0,
                "clearwrong" => 0,
            ],
        ];
        return $sc;
    }

    /**
     * Test get_expected_data
     *
     * @covers ::get_expected_data
     */
    public function test_get_expected_data(): void {
        $question = $this->make_a_sc_question();
        $question->order = array_keys($question->rows);
        $this->assertEquals(
            [
                'option' => PARAM_INT,
                'distractor0' => PARAM_BOOL,
                'distractor1' => PARAM_BOOL,
                'distractor2' => PARAM_BOOL,
                'qtype_sc_changed_value' => PARAM_INT, ],
            $question->get_expected_data());
    }

    /**
     * Test is_complete_response
     *
     * @covers ::is_complete_response
     */
    public function test_is_complete_response(): void {
        $question = $this->make_a_sc_question();
        $question->order = array_keys($question->rows);
        $this->assertFalse($question->is_complete_response(
            [])
        );
        $this->assertFalse($question->is_complete_response(
            [
                'option' => '-1', ])
        );
        $this->assertFalse($question->is_complete_response(
            [
                'distractor0' => '0', ])
        );
        $this->assertTrue($question->is_complete_response(
            [
                'option' => '0', ])
        );
        $this->assertTrue($question->is_complete_response(
            [
                'option' => '0',
                'distractor0' => '0', ])
        );

        $this->assertTrue($question->is_complete_response(
            [
                'option' => '1',
                'distractor0' => '1',
                'distractor1' => '0',
                'distractor2' => '1', ])
        );
    }

    /**
     * Test is_gradable_response
     *
     * @covers ::is_gradable_response
     */
    public function test_is_gradable_response_sconezero(): void {
        // Sconezero.
        $question = $this->make_a_sc_question();
        $question->order = array_keys($question->rows);
        $this->assertFalse($question->is_gradable_response(
            [])
        );
        $this->assertFalse($question->is_gradable_response(
            [
                'option' => '-1', ])
        );
        $this->assertFalse($question->is_gradable_response(
            [
                'distractor0' => '1', ])
        );
        $this->assertTrue($question->is_gradable_response(
            [
                'option' => '1', ])
        );
        $this->assertTrue($question->is_gradable_response(
            [
                'option' => '1',
                'distractor0' => '1', ])
        );
        $this->assertTrue($question->is_gradable_response(
            [
                'option' => '1',
                'distractor0' => '0',
                'distractor1' => '0',
                'distractor2' => '0', ])
        );
    }

    /**
     * Test is_gradable_response (aprime)
     *
     * @covers ::is_gradable_response
     */
    public function test_is_gradable_response_aprime(): void {
        $question = $this->make_a_sc_question();
        $question->scoringmethod = 'aprime';
        $question->order = array_keys($question->rows);
        $this->assertFalse($question->is_gradable_response(
            [])
        );
        $this->assertFalse($question->is_gradable_response(
            [
                'option' => '-1', ])
        );
        $this->assertTrue($question->is_gradable_response(
            [
                'distractor0' => '1', ])
        );
        $this->assertTrue($question->is_gradable_response(
            [
                'option' => '1', ])
        );
        $this->assertTrue($question->is_gradable_response(
            [
                'option' => '1',
                'distractor0' => '1', ])
        );
        $this->assertTrue($question->is_gradable_response(
            [
                'option' => '1',
                'distractor0' => '0',
                'distractor1' => '0',
                'distractor2' => '0', ])
        );
    }

    /**
     * Test is_gradable_response (subpoints)
     *
     * @covers ::is_gradable_response
     */
    public function test_is_gradable_response_subpoints(): void {
        $question = $this->make_a_sc_question();
        $question->scoringmethod = 'subpoints';
        $question->order = array_keys($question->rows);
        $this->assertFalse($question->is_gradable_response(
            [])
        );
        $this->assertFalse($question->is_gradable_response(
            [
                'option' => '-1', ])
        );
        $this->assertTrue($question->is_gradable_response(
            [
                'distractor0' => '1', ])
        );
        $this->assertTrue($question->is_gradable_response(
            [
                'option' => '1', ])
        );
        $this->assertTrue($question->is_gradable_response(
            [
                'option' => '1',
                'distractor0' => '1', ])
        );
        $this->assertTrue($question->is_gradable_response(
            [
                'option' => '1',
                'distractor0' => '0',
                'distractor1' => '0',
                'distractor2' => '0', ])
        );
    }

    /**
     * Test get_order
     *
     * @covers ::get_order
     */
    public function test_get_order(): void {
        $question = $this->make_a_sc_question();
        $question->shuffleanswers = 1;
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals( $question->order, $question->get_order(\test_question_maker::get_a_qa($question)));
        unset($question);
        $question = $this->make_a_sc_question();
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals( [0 => 1, 1 => 2, 2 => 3], $question->get_order(\test_question_maker::get_a_qa($question)));
    }

    /**
     * Test is_same_response
     *
     * @covers ::is_same_response
     */
    public function test_is_same_response(): void {
        $question = $this->make_a_sc_question();
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertTrue($question->is_same_response(
            [],
            []));
        $this->assertFalse($question->is_same_response(
            [],
            ['option' => '1']));
        $this->assertTrue($question->is_same_response(
            ['option' => '1'],
            ['option' => '1']));
        $this->assertFalse($question->is_same_response(
            ['option' => '1'],
            ['option' => '2']));
        $this->assertFalse($question->is_same_response(
            [],
            ['option' => '1']));
        $this->assertTrue($question->is_same_response(
            ['option' => '0', 'distractor1' => '1'],
            ['option' => '0', 'distractor1' => '1']));
        $this->assertFalse($question->is_same_response(
            ['option' => '0'],
            ['option' => '0', 'distractor1' => '1']));
        $this->assertFalse($question->is_same_response(
            ['option' => '0', 'distractor1' => '1'],
            ['option' => '0', 'distractor2' => '1']));
    }

    /**
     * Test grading
     *
     * @covers ::grading
     */
    public function test_grading(): void {
        $question = $this->make_a_sc_question();
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals(['option' => 0],
            $question->get_correct_response());
    }

    /**
     * Test summarise_response
     *
     * @covers ::summarise_response
     */
    public function test_summarise_response(): void {
        $question = $this->make_a_sc_question();
        $question->start_attempt(new \question_attempt_step(), 1);
        $summary = $question->summarise_response(['option' => 0],
        \test_question_maker::get_a_qa($question));
        $this->assertEquals('option text 1', $summary);
    }

    /**
     * Test classify_response
     *
     * @covers ::classify_response
     */
    public function test_classify_response(): void {
        $question = $this->make_a_sc_question();
        $question->start_attempt(new \question_attempt_step(), 1);

        $this->assertEquals(['0' => new \question_classified_response(11, 'option text 1', 1.0)],
            $question->classify_response(['option' => '0']));

        $this->assertNotEquals(['0' => new \question_classified_response(11, 'option text 1', 0.0)],
            $question->classify_response(['option' => '0']));

        $this->assertEquals(['0' => new \question_classified_response(21, 'option text 2', 0.0)],
            $question->classify_response(['option' => '1']));

        $this->assertEquals(['0' => new \question_classified_response(31, 'option text 3', 0.0)],
            $question->classify_response(['option' => '2']));

        $this->assertEquals(['0' => \question_classified_response::no_response()],
            $question->classify_response(['option' => '-1']));
    }

    /**
     * Test make_html_inline
     *
     * @covers ::make_html_inline
     */
    public function test_make_html_inline(): void {
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

    /**
     * Test get_hint
     *
     * @covers ::get_hint
     */
    public function test_get_hint(): void {
        $question = $this->make_a_sc_question();
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals('This is the 1st hint', $question->get_hint(0, \test_question_maker::get_a_qa($question))->hint);
        $this->assertEquals('This is the 2nd hint', $question->get_hint(1, \test_question_maker::get_a_qa($question))->hint);
    }

    /**
     * Test compute_final_grade (szonezero)
     *
     * @covers ::compute_final_grade
     */
    public function test_compute_final_grade_sconezero(): void {
        $question = $this->make_a_sc_question();
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals('1.0', $question->compute_final_grade([
            0 => ['option' => '0'], ],
            1));
        $this->assertEquals('0.0', $question->compute_final_grade([
            0 => ['option' => '1'], ],
            1));
        $this->assertEquals('0.0', $question->compute_final_grade([
            0 => ['distractor1' => '1', 'distractor2' => '1'], ],
            1));
        $this->assertEquals('0.66666669999999995', $question->compute_final_grade([
            0 => [],
            1 => ['option' => '0'], ],
            1));
        $this->assertEquals('0.3333334', $question->compute_final_grade([
            0 => [],
            1 => [],
            2 => ['option' => '0'], ],
            1));
        $this->assertEquals('0.0', $question->compute_final_grade([
            0 => [],
            1 => [],
            2 => [],
            3 => [],
            4 => ['option' => '0'], ],
            1));
    }

    /**
     * Test compute_final_grade_mtfonezero (aprime)
     *
     * @covers ::compute_final_grade
     */
    public function test_compute_final_grade_aprime(): void {
        $question = $this->make_a_sc_question();
        $question->scoringmethod = 'aprime';
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals('1.0', $question->compute_final_grade([
            0 => ['option' => '0'], ],
            1));
        $this->assertEquals('0.66666669999999995', $question->compute_final_grade([
            0 => [],
            1 => ['option' => '0'], ],
            1));
        $this->assertEquals('0.3333334', $question->compute_final_grade([
            0 => [],
            1 => [],
            2 => ['option' => '0'], ],
            1));
        $this->assertEquals('0.0', $question->compute_final_grade([
            0 => ['option' => '1'], ],
            1));
        $this->assertEquals('0.5', $question->compute_final_grade([
            0 => ['distractor2' => '1'], ],
            1));
        $this->assertEquals('0.1666667', $question->compute_final_grade([
            0 => [],
            1 => ['distractor2' => '1'], ],
            1));
        $this->assertEquals('0.0', $question->compute_final_grade([
            0 => [],
            1 => [],
            2 => ['distractor2' => '1'], ],
            1));
    }

    /**
     * Test compute_final_grade_mtfonezero (subpoints)
     *
     * @covers ::compute_final_grade
     */
    public function test_compute_final_grade_subpoints(): void {
        $question = $this->make_a_sc_question();
        $question->scoringmethod = 'subpoints';
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals('1.0', $question->compute_final_grade([
            0 => ['option' => '0'], ],
            1));
        $this->assertEquals('0.66666669999999995', $question->compute_final_grade([
            0 => [],
            1 => ['option' => '0'], ],
            1));
        $this->assertEquals('0.3333334', $question->compute_final_grade([
            0 => [],
            1 => [],
            2 => ['option' => '0'], ],
            1));
        $this->assertEquals('0.0', $question->compute_final_grade([
            0 => ['option' => '1'], ],
            1));
        $this->assertEquals('0.5', $question->compute_final_grade([
            0 => ['distractor2' => '1'], ],
            1));
        $this->assertEquals('0.1666667', $question->compute_final_grade([
            0 => [],
            1 => ['distractor2' => '1'], ],
            1));
        $this->assertEquals('0.0', $question->compute_final_grade([
            0 => [],
            1 => [],
            2 => ['distractor2' => '1'], ],
            1));
    }

    /**
     * Test grade_response (sconezero)
     *
     * @covers ::grade_response
     */
    public function test_grade_response_sconezero(): void {
        $question = $this->make_a_sc_question();
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals(
            "1.0", $question->grade_response(['option' => '0'])[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(['option' => '1'])[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(['option' => '2'])[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(['option' => '-1'])[0]);
    }

    /**
     * Test grade_response (aprime)
     *
     * @covers ::grade_response
     */
    public function test_grade_response_aprime(): void {
        $question = $this->make_a_sc_question();
        $question->scoringmethod = 'aprime';
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals(
            "1.0", $question->grade_response(['option' => '0'])[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(['option' => '1'])[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(['option' => '2'])[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(['option' => '-1'])[0]);
        $this->assertEquals(
            "1.0", $question->grade_response(['distractor1' => '1', 'distractor2' => '1'])[0]);
        $this->assertEquals(
            "0.5", $question->grade_response(['distractor2' => '1'])[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(['distractor0' => '1'])[0]);
        $this->assertEquals(
            "1.0", $question->grade_response(['option' => '0', 'distractor1' => '1', 'distractor2' => '1'])[0]);
    }

    /**
     * Test grade_response (subpoints)
     *
     * @covers ::grade_response
     */
    public function test_grade_response_subpoints(): void {
        $question = $this->make_a_sc_question();
        $question->scoringmethod = 'subpoints';
        $question->start_attempt(new \question_attempt_step(), 1);
        $this->assertEquals(
            "1.0", $question->grade_response(['option' => '0'])[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(['option' => '1'])[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(['option' => '2'])[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(['option' => '-1'])[0]);
        $this->assertEquals(
            "1.0", $question->grade_response(['distractor1' => '1', 'distractor2' => '1'])[0]);
        $this->assertEquals(
            "0.5", $question->grade_response(['distractor2' => '1'])[0]);
        $this->assertEquals(
            "0.0", $question->grade_response(['distractor0' => '1'])[0]);
        $this->assertEquals(
            "1.0", $question->grade_response(['option' => '0', 'distractor1' => '1', 'distractor2' => '1'])[0]);
    }
}
