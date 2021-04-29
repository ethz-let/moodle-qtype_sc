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

abstract class qtype_sc_grading {

    /**
     * Returns the name of the grading method.
     */
    abstract public function get_name();

    /**
     * Returns the title of the grading method.
     */
    abstract public function get_title();

    /**
     * Returns the question's grade for a given response.
     *
     * @param unknown $question the question object
     * @param unknown $response the response given.
     */
    abstract public function grade_question(qtype_sc_question $question, array $response);

    /**
     * returns true if the student has marked the correct answer as a distractor, false otherwise.
     * @param qtype_sc_question $question
     * @param array $response
     * @return boolean
     */
    protected function marked_wrong_distractor(qtype_sc_question $question, array $response) {

        foreach ($question->order as $key => $rowid) {
            $distractorfield = $question->distractorfield($key);
            if (property_exists((object) $response, $distractorfield) && $response[$distractorfield] == 1) {
                $row = $question->rows[$rowid];
                if ($row->number == $question->correctrow) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * returns true if the student has actively chosen a wrong option via an option checkbox, false otherwise.
     * @param qtype_sc_question $question
     * @param array $response
     * @return boolean
     */
    protected function chose_wrong_answer(qtype_sc_question $question, array $response) {
        foreach ($question->order as $key => $rowid) {
            if (property_exists((object) $response, 'option') && $response['option'] == $key) {
                $row = $question->rows[$rowid];
                if ($row->number != $question->correctrow) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * returns true if the student has actively chosen the correct option via its option checkbox, false otherwise.
     * @param qtype_sc_question $question
     * @param array $response
     * @return boolean
     */
    protected function chose_correct_answer(qtype_sc_question $question, array $response) {
        foreach ($question->order as $key => $rowid) {
            if (property_exists((object) $response, 'option') && $response['option'] == $key) {
                $selectedrow = $question->rows[$rowid];
                if ($selectedrow->number == $question->correctrow) {
                    return true;
                }
            }
        }
        return false;
    }
}
