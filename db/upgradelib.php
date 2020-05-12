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

function qtype_sc_convert_question_attempts($version) {
    global $DB;

    $questionids = $DB->get_fieldset_select('question', 'id', "qtype = 'sc'");
    if (!$questionids) {
        return;
    }

    list($qsql, $params) = $DB->get_in_or_equal($questionids, SQL_PARAMS_NAMED, 'qid');
    $attemptsql = "SELECT id
                    FROM {question_attempts} qa
                    WHERE qa.questionid " . $qsql;

    $attemptids = $DB->get_fieldset_sql($attemptsql, $params);

    foreach ($attemptids as $attemptid) {

        $attempt = $DB->get_record('question_attempts', array('id' => $attemptid));
        $numberofrows = $DB->get_field('qtype_sc_options', 'numberofrows', array('questionid' => $attempt->questionid));
        $steps = $DB->get_records('question_attempt_steps', array('questionattemptid' => $attemptid));

        if ($version == 2018032003) {
            $transaction = $DB->start_delegated_transaction();
            foreach ($steps as $step) {
                qtype_sc_convert_attempt_step_data_2018032003($numberofrows, $step->id);
            }
            $transaction->allow_commit();
        }

        if ($version == 2020051200) {
            $transaction = $DB->start_delegated_transaction();
            foreach ($steps as $step) {
                qtype_sc_convert_attempt_step_data_2020051200($step->id);
            }
            $transaction->allow_commit();
        }
    }
}

function qtype_sc_is_order_or_finish_step(array $stepdatarows) {
    foreach ($stepdatarows as $stepdata) {
        if ($stepdata->name == '_order' || $stepdata->name == '-finish') {
            return true;
        }
    }
    return false;
}

function qtype_sc_convert_attempt_step_data_2018032003($numberofrows, $attemptstepid) {
    global $DB;

    $stepdatarows = $DB->get_records('question_attempt_step_data', array('attemptstepid' => $attemptstepid));
    if (qtype_sc_is_order_or_finish_step($stepdatarows)) {
        return;
    }

    $chosenoption = -1;
    $chosendistractors = array();

    foreach ($stepdatarows as $stepdata) {
        if ($stepdata->name == 'option') {
            $chosenoption = $stepdata->value;
            $DB->delete_records('question_attempt_step_data', array('id' => $stepdata->id));
        }
        if (substr($stepdata->name, 0, 10) == 'distractor' && $stepdata->value == 1) {
            $number = (int) substr($stepdata->name, 10 , 1);
            $chosendistractors[$number] = 1;
            $DB->delete_records('question_attempt_step_data', array('id' => $stepdata->id));
        }
    }

    for ($i = 0; $i < $numberofrows; $i++) {
        $newoptiondata = new stdClass();
        $newoptiondata->attemptstepid = $attemptstepid;
        $newoptiondata->name = 'option' . $i;
        $newoptiondata->value = 0;
        if ($i == $chosenoption) {
            $newoptiondata->value = 1;
        }
        $DB->insert_record('question_attempt_step_data', $newoptiondata);

        $newdistdata = new stdClass();
        $newdistdata->attemptstepid = $attemptstepid;
        $newdistdata->name = 'distractor' . $i;
        $newdistdata->value = 0;
        if (array_key_exists($i, $chosendistractors)) {
            $newdistdata->value = 1;
        }
        $DB->insert_record('question_attempt_step_data', $newdistdata);
    }
}

function qtype_sc_convert_attempt_step_data_2020051200($attemptstepid) {
    global $DB;

    @set_time_limit(0);
    @ini_set('memory_limit', '3072M');

    $stepdatarows = $DB->get_records('question_attempt_step_data', array('attemptstepid' => $attemptstepid));
    if (qtype_sc_is_order_or_finish_step($stepdatarows)) {
        return;
    }

    $optionrow = $selected = null;
    $isconverted = false;

    foreach ($stepdatarows as $stepdata) {

        if ($stepdata->name == 'option') {
            $isconverted = true;
            continue;
        }

        $optionrow = &preg_split("/option/", $stepdata->name)[1];
        if (!isset($optionrow)) {
            continue;
        }

        if ($stepdata->value == 1 && isset($optionrow)) {
            $selected = $optionrow;
        }
        $DB->delete_records('question_attempt_step_data', array('id' => $stepdata->id));
    }

    if (!$isconverted) {
        if (!isset($selected)) {
            $selected = -1;
        }

        $newdistdata = new stdClass();
        $newdistdata->attemptstepid = $attemptstepid;
        $newdistdata->name = 'option';
        $newdistdata->value = $selected;
        $DB->insert_record('question_attempt_step_data', $newdistdata);
    }
}