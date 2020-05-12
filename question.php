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

class qtype_sc_question extends question_graded_automatically_with_countback {

    public $rows;

    public $scoringmethod;

    public $shuffleanswers;

    public $numberofrows;

    public $order = null;

    public $editedquestion;

    public $correctrow;

    /**
     * (non-PHPdoc).
     *
     * @see question_definition::start_attempt()
     */
    public function start_attempt(question_attempt_step $step, $variant) {
        $this->order = array_keys($this->rows);
        if ($this->shuffleanswers) {
            shuffle($this->order);
        }
        $step->set_qt_var('_order', implode(',', $this->order));
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_definition::apply_attempt_state()
     */
    public function apply_attempt_state(question_attempt_step $step) {
        $this->order = explode(',', $step->get_qt_var('_order'));

        for ($i = 0; $i < count($this->order); $i++) {
            if (isset($this->rows[$this->order[$i]])) {
                continue;
            }

            $a = new stdClass();
            $a->id = 0;
            $a->questionid = $this->id;
            $a->number = -1;
            $a->optiontext = html_writer::span(get_string('deletedchoice', 'qtype_sc'), 'notifyproblem');
            $a->optiontextformat = FORMAT_HTML;
            $a->optionfeedback = "";
            $a->optionfeedbackformat = FORMAT_HTML;
            $this->rows[$this->order[$i]] = $a;
        }
        parent::apply_attempt_state($step);
    }

    /**
     *
     * @param question_attempt $qa
     * @return array
     */
    public function get_order(question_attempt $qa) {
        $this->init_order($qa);
        return $this->order;
    }

    /**
     * Initialises the order (if it is not set yet) by decoding the question attempt variable '_order'.
     *
     * @param question_attempt $qa
     */
    protected function init_order(question_attempt $qa) {
        if (is_null($this->order)) {
            $this->order = explode(',', $qa->get_step(0)->get_qt_var('_order'));
        }
    }

    /**
     * Returns the name field name for distractor buttons.
     *
     * @param int $key
     * @return string
     */
    public function distractorfield($key) {
        return 'distractor' . $key;
    }

    /**
     * @param array $response
     * @param int $key
     * @return bool
     */
    public function is_option_selected($response, $key) {
        return array_key_exists('option', $response) && $response['option'] == $key;
    }

    /**
     * @param array $response
     * @param int $key
     * @return bool
     */
    public function is_distractor_selected($response, $key) {
        $distractorfield = $this->distractorfield($key);
        return array_key_exists($distractorfield, $response) && $response[$distractorfield];
    }

    /**
     * Returns the last response in a question attempt.
     * @param question_attempt $qa
     * @return array|mixed
     */
    public function get_response(question_attempt $qa) {
        return $qa->get_last_qt_data();
    }

    /**
     * Returns true if an option was chosen, false otherwise.
     *
     * @param array $response response
     * @return bool
     */
    public function is_complete_response(array $response) {
        return array_key_exists('option', $response) && $response['option'] !== '-1';
    }

    /**
     * Returns true if an option was chosen or, in case of aprime and subpoints, if at least one distractor was marked.
     *
     * @see question_graded_automatically::is_gradable_response()
     */
    public function is_gradable_response(array $response) {

        if ($this->is_complete_response($response)) {
            return true;
        }

        if ($this->scoringmethod == 'aprime' || $this->scoringmethod == 'subpoints') {
            return $this->any_distractor_chosen($response);
        }
        return false;
    }

    /**
     * In situations where is_gradable_response() returns false, this method should generate a description of what the problem is.
     *
     * @param array response
     * @return string the message.
     */
    public function get_validation_error(array $response) {

        $isgradable = $this->is_gradable_response($response);
        if ($isgradable) {
            return '';
        }
        return get_string('oneradiobutton', 'qtype_sc');
    }

    /**
     *
     * @param array $response
     * @return int the number of choices that were selected. in this response.
     */
    public function get_num_selected_choices(array $response) {

        $numselected = 0;
        foreach ($response as $key => $value) {
            if (!empty($value) && $key[0] != '_') {
                $numselected += 1;
            }
        }
        return $numselected;
    }

    /**
     * Produce a plain text summary of a response.
     *
     * @param array $response
     * @return string a plain text summary of that response, that could be used in reports.
     */
    public function summarise_response(array $response) {

        $result = array();

        foreach ($this->order as $key => $rowid) {
            if (array_key_exists('option', $response) && $response['option'] == $key) {
                $row = $this->rows[$rowid];
                $result[] = $this->html_to_text($row->optiontext, $row->optiontextformat);
            }
        }
        foreach ($this->order as $key => $rowid) {
            $field = $this->distractorfield($key);
            if (array_key_exists($field, $response) && $response[$field]) {
                $row = $this->rows[$rowid];
                $result[] = $this->html_to_text($row->optiontext, $row->optiontextformat) . ' ' .
                    get_string('iscrossedout', 'qtype_sc');
            }
        }

        return implode('; ', $result);
    }

    /**
     * Returns true if at least one distractor was marked in a response.
     *
     * @param array $response
     * @return bool
     */
    public function any_distractor_chosen(array $response) {

        foreach ($this->order as $key => $rowid) {
            $field = $this->distractorfield($key);
            if (array_key_exists($field, $response) && $response[$field] == 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * (non-PHPdoc).
     *
     * @param array $response
     * @see question_with_responses::classify_response()
     */
    public function classify_response(array $response) {

        if (!$this->is_complete_response($response)) {
            return array($this->id => question_classified_response::no_response());
        }

        list($partialcredit, $state) = $this->grade_response($response);

        foreach ($this->order as $key => $rowid) {
            if (array_key_exists('option', $response) && ($response['option'] == $key)) {

                $row = $this->rows[$rowid];
                if ($row->number == $this->correctrow) {
                    $partialcredit = 1.0;
                } else {
                    $partialcredit = 0; // Due to non-linear math.
                }

                return array($this->id => new question_classified_response(
                    $rowid . '1',
                    question_utils::to_plain_text($row->optiontext, $row->optiontextformat),
                    $partialcredit));
            }
        }
    }

    /**
     * Use by many of the behaviours to determine whether the student's response has changed.
     * This is normally used to determine that a new set of responses can safely be discarded.
     *
     * @param array $prevresponse the responses previously recorded for this question,
     *        as returned by {@link question_attempt_step::get_qt_data()}
     * @param array $newresponse the new responses, in the same format.
     * @return bool whether the two sets of responses are the same - that is
     *         whether the new set of responses can safely be discarded.
     */
    public function is_same_response(array $prevresponse, array $newresponse) {

        if (!question_utils::arrays_same_at_key($prevresponse, $newresponse, 'option')) {
            return false;
        }

        foreach ($this->order as $key => $rowid) {
            $distractorfield = $this->distractorfield($key);
            if (!question_utils::arrays_same_at_key($prevresponse, $newresponse, $distractorfield)) {
                return false;
            }
        }

        return true;
    }

    /**
     * What data would need to be submitted to get this question correct.
     * If there is more than one correct answer, this method should just return one possibility.
     *
     * @return array parameter name => value.
     */
    public function get_correct_response() {
        $result = array();

        foreach ($this->order as $key => $rowid) {
            $row = $this->rows[$rowid];
            if ($row->number == $this->correctrow) {
                $result['option'] = $key;
            }
        }
        return $result;
    }

    /**
     * Returns an instance of the grading class according to the scoringmethod of the question.
     *
     * @return The grading object.
     */
    public function grading() {
        global $CFG;

        $type = $this->scoringmethod;
        $gradingclass = 'qtype_sc_grading_' . $type;

        require_once($CFG->dirroot . '/question/type/sc/grading/' . $gradingclass . '.class.php');

        return new $gradingclass();
    }

    /**
     * Grade a response to the question, returning a fraction between
     * get_min_fraction() and 1.0, and the corresponding {@link question_state}
     * right, partial or wrong.
     *
     * @param array $response responses, as returned by
     *        {@link question_attempt_step::get_qt_data()}.
     * @return array (number, integer) the fraction, and the state.
     */
    public function grade_response(array $response) {
        $grade = $this->grading()->grade_question($this, $response);
        $state = question_state::graded_state_for_fraction($grade);

        return array($grade, $state);
    }

    /**
     * What data may be included in the form submission when a student submits this question in its current state?
     *
     * This information is used in calls to optional_param. The parameter name
     * has {@link question_attempt::get_field_prefix()} automatically prepended.
     *
     * @return array|string variable name => PARAM_... constant, or, as a special case
     *         that should only be used in unavoidable, the constant question_attempt::USE_RAW_DATA
     *         meaning take all the raw submitted data belonging to this question.
     */
    public function get_expected_data() {
        $result = array();

        $result["qtype_sc_changed_value"] = PARAM_INT;
        $result['option'] = PARAM_INT;

        foreach ($this->order as $key => $notused) {
            $distractorfield = $this->distractorfield($key);
            $result[$distractorfield] = PARAM_BOOL;
        }
        return $result;
    }

    /**
     * Makes HTML text (e.g. option or feedback texts) suitable for inline presentation in renderer.php.
     *
     * @param string html The HTML code.
     * @return string the purified HTML code without paragraph elements and line breaks.
     */
    public function make_html_inline($html) {
        $html = preg_replace('~\s*<p>\s*~u', '', $html);
        $html = preg_replace('~\s*</p>\s*~u', '<br />', $html);
        $html = preg_replace('~(<br\s*/?>)+$~u', '', $html);

        return trim($html);
    }

    /**
     * Convert some part of the question text to plain text.
     * This might be used,
     * for example, by get_response_summary().
     *
     * @param string $text The HTML to reduce to plain text.
     * @param int $format the FORMAT_... constant.
     * @return string the equivalent plain text.
     */
    public function html_to_text($text, $format) {
        return question_utils::to_plain_text($text, $format);
    }

    /**
     * Computes the final grade when "Multiple Attempts" or "Hints" are enabled
     *
     * @param array $responses Contains the user responses. 1st dimension = attempt, 2nd dimension = answers
     * @param int $totaltries Not needed
     */
    public function compute_final_grade($responses, $totaltries) {
        $lastresponse = count($responses) - 1;
        $numpoints = isset($responses[$lastresponse]) ? $this->grading()->grade_question($this, $responses[$lastresponse]) : 0;
        return max(0, $numpoints - max(0, $lastresponse) * $this->penalty);
    }

    /**
     * Disable those hint settings that we don't want when the student has selected
     * more choices than the number of right choices.
     * This avoids giving the game away.
     *
     * @param question_hint_with_parts $hint a hint.
     */
    protected function disable_hint_settings_when_too_many_selected(question_hint_with_parts $hint) {
        $hint->clearwrong = false;
    }

    public function get_hint($hintnumber, question_attempt $qa) {

        $hint = parent::get_hint($hintnumber, $qa);
        if (is_null($hint)) {
            return $hint;
        }

        if ($this->get_num_selected_choices($qa->get_last_qt_data()) > 1) {
            $hint = clone ($hint);
            $this->disable_hint_settings_when_too_many_selected($hint);
        }
        return $hint;
    }

    /**
     * (non-PHPdoc)
     *
     * @see question_definition::check_file_access()
     */
    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'qtype_sc' && $filearea == 'optiontext') {
            return true;
        } else if ($component == 'qtype_sc' && $filearea == 'feedbacktext') {
            return true;
        } else if ($component == 'question'
                    && in_array($filearea, array('correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'))) {
            if ($this->editedquestion == 1) {
                return true;
            } else {
                return $this->check_combined_feedback_file_access($qa, $options, $filearea);
            }
        } else if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);
        } else {
            return parent::check_file_access($qa, $options, $component, $filearea, $args,
                    $forcedownload);
        }
    }
}