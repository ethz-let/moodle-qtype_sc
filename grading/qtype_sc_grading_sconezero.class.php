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

require_once($CFG->dirroot . '/question/type/sc/grading/qtype_sc_grading.class.php');

class qtype_sc_grading_sconezero extends qtype_sc_grading {

    const TYPE = 'sconezero';

    /**
     *
     * {@inheritDoc}
     * @see qtype_sc_grading::get_name()
     */
    public function get_name() {
        return self::TYPE;
    }

    /**
     *
     * {@inheritDoc}
     * @see qtype_sc_grading::get_title()
     */
    public function get_title() {
        return get_string('scoring' . self::TYPE, 'qtype_sc');
    }

    /**
     *
     * {@inheritDoc}
     * @see qtype_sc_grading::grade_question()
     */
    public function grade_question(qtype_sc_question $question, array $response) {
        if ($this->marked_wrong_distractor($question, $response)) {
            return 0.0;
        }

        if ($this->chose_correct_answer($question, $response)) {
            return 1.0;
        }

        return 0.0;
    }
}
