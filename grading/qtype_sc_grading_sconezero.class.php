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
 * qtype_sc grading class for sconezero scoringmethod
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

require_once($CFG->dirroot . '/question/type/sc/grading/qtype_sc_grading.class.php');

/**
 * Provides grading functionality for sconezero scoring metod
 *
 * @package     qtype_sc
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_sc_grading_sconezero extends qtype_sc_grading {

    /** @var string TYPE */
    const TYPE = 'sconezero';

    /**
     * Returns the scoringmethod name.
     * @return string
     */
    public function get_name() {
        return self::TYPE;
    }

    /**
     * Returns the scoringmethod title.
     * @return string
     */
    public function get_title() {
        return get_string('scoring' . self::TYPE, 'qtype_sc');
    }

    /**
     * Returns the question's grade for a given response.
     * @param qtype_sc_question $question
     * @param array $response
     * @return float
     */
    public function grade_question(qtype_sc_question $question, array $response) {
        if ($this->chose_correct_answer($question, $response)) {
            return 1.0;
        }

        return 0.0;
    }
}
