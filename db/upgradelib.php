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
* @package qtype_sc
* @author        Jürgen Zimmer (juergen.zimmer@edaktik.at)
* @author        Andreas Hruska (andreas.hruska@edaktik.at)
* @copyright     2018 eDaktik GmbH {@link http://www.edaktik.at}
* @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
defined('MOODLE_INTERNAL') || die();

function qtype_sc_convert_question_attempts() {
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
        $transaction = $DB->start_delegated_transaction();

        $attempt = $DB->get_record('question_attempts', array('id' => $attemptid));
        $numberofrows = $DB->get_field('qtype_sc_options', 'numberofrows', array('questionid' => $attempt->questionid));

        $steps = $DB->get_records('question_attempt_steps', array('questionattemptid' => $attemptid));

        foreach ($steps as $step) {
            $stepdatarows = $DB->get_records('question_attempt_step_data', array('attemptstepid' => $step->id));

            if (qtype_sc_is_order_or_finish_step($stepdatarows)) {
                continue;
            }
            qtype_sc_convert_attempt_step_data($numberofrows, $stepdatarows, $step->id);
        }
        $transaction->allow_commit();
    }
}

function qtype_sc_is_order_or_finish_step(array $stepdatarows) {
      foreach ($stepdatarows as $stepdata) {
          if ($stepdata->name == '_order') {
              return true;
          }
          if ($stepdata->name == '-finish') {
              return true;
          }
      }
      return false;
}

function qtype_sc_convert_attempt_step_data($numberofrows, array $stepdatarows, $attemptstepid) {
    global $DB;

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