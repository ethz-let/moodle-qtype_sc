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
 * qtype_sc question definition class.
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

/**
 * Represents a qtype_sc question.
 *
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_sc_question extends question_graded_automatically_with_countback {

    /** @var array rows */
    public $rows;
    /** @var string scoringmethod */
    public $scoringmethod;
    /** @var bool shuffleanswers */
    public $shuffleanswers;
    /** @var int numberofrows */
    public $numberofrows;
    /** @var array order */
    public $order = null;
    /** @var bool editedquestion */
    public $editedquestion;
    /** @var int correctrow */
    public $correctrow;
    /** @var stdClass options */
    public $options;
    /** @var string answernumbering */
    public $answernumbering;

    /**
     * (non-PHPdoc).
     * @see question_definition::start_attempt()
     * @param question_attempt_step $step
     * @param int $variant
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
     * @see question_definition::apply_attempt_state()
     * @param question_attempt_step $step
     */
    public function apply_attempt_state(question_attempt_step $step) {
        $this->order = explode(',', $step->get_qt_var('_order'));
        parent::apply_attempt_state($step);
    }
    /**
     * (non-PHPdoc).
     * @see question_definition::validate_can_regrade_with_other_version
     *
     * @param question_definition $otherversion
     * @return string|null
     * @throws coding_exception
     */
    public function validate_can_regrade_with_other_version(question_definition $otherversion): ?string {
        $basemessage = parent::validate_can_regrade_with_other_version($otherversion);
        if ($basemessage) {
            return $basemessage;
        }
        if (count($this->rows) != count($otherversion->rows)) {
            return get_string('numberchoicehaschanged', 'qtype_sc');
        }
        return null;
    }

    /**
     * (non-PHPdoc).
     * @see question_definition::update_attempt_state_data_for_new_version
     *
     * @param question_attempt_step $oldstep
     * @param question_definition $otherversion
     * @return array
     * @throws coding_exception
     */
    public function update_attempt_state_data_for_new_version(
                    question_attempt_step $oldstep, question_definition $otherversion) {

        $startdata = parent::update_attempt_state_data_for_new_version($oldstep, $otherversion);

        $mapping = array_combine(array_keys($otherversion->rows), array_keys($this->rows));

        $oldorder = explode(',', $oldstep->get_qt_var('_order'));
        $neworder = [];
        foreach ($oldorder as $oldid) {
            $neworder[] = $mapping[$oldid] ?? $oldid;
        }
        $startdata['_order'] = implode(',', $neworder);
        return $startdata;
    }
    /**
     * get the question order
     * @param question_attempt $qa
     * @return array
     */
    public function get_order(question_attempt $qa) {
        $this->init_order($qa);
        return $this->order;
    }

    /**
     * Initialises the order (if it is not set yet) by decoding the question attempt variable '_order'.
     * @param question_attempt $qa
     */
    protected function init_order(question_attempt $qa) {
        if (is_null($this->order)) {
            $this->order = explode(',', $qa->get_step(0)->get_qt_var('_order'));
        }
    }

    /**
     * Returns the name field name for distractor buttons.
     * @param int $key
     * @return string
     */
    public function distractorfield($key) {
        return 'distractor' . $key;
    }

    /**
     * Checks wether a specific option is selected.
     * @param array $response
     * @param int $key
     * @return bool
     */
    public function is_option_selected($response, $key) {
        return property_exists((object) $response, 'option') && $response['option'] == $key;
    }

    /**
     * Checks wether a specific distractor is selected.
     * @param array $response
     * @param int $key
     * @return bool
     */
    public function is_distractor_selected($response, $key) {
        $distractorfield = $this->distractorfield($key);
        return property_exists((object) $response, $distractorfield) && $response[$distractorfield];
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
     * Used by many of the behaviours, to work out whether the student's
     * response to the question is complete.
     * That is, whether the question attempt
     * should move to the COMPLETE or INCOMPLETE state.
     * @param array $response responses, as returned by
     *        {@see question_attempt_step::get_qt_data()}.
     * @return bool whether this response is a complete answer to this question.
     */
    public function is_complete_response(array $response) {
        return property_exists((object) $response, 'option') && $response['option'] !== '-1';
    }

    /**
     * Use by many of the behaviours to determine whether the student
     * has provided enough of an answer for the question to be graded automatically,
     * or whether it must be considered aborted.
     * @param array $response responses, as returned by
     *      {@see question_attempt_step::get_qt_data()}.
     * @return bool whether this response can be graded.
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
     * In situations where is_gradable_response() returns false, this method
     * should generate a description of what the problem is.
     * @param array $response
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
     * Get the number of selected options
     * @param array $response responses, as returned by
     *        {@see question_attempt_step::get_qt_data()}.
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
     * @param array $response
     * @return string
     */
    public function summarise_response(array $response) {

        $result = [];

        foreach ($this->order as $key => $rowid) {
            if (property_exists((object) $response, 'option') && $response['option'] == $key) {
                $row = $this->rows[$rowid];
                $result[] = $this->html_to_text($row->optiontext, $row->optiontextformat);
            }
        }
        foreach ($this->order as $key => $rowid) {
            $field = $this->distractorfield($key);
            if (property_exists((object) $response, $field) && $response[$field]) {
                $row = $this->rows[$rowid];
                $result[] = $this->html_to_text($row->optiontext, $row->optiontextformat) . ' ' .
                    get_string('iscrossedout', 'qtype_sc');
            }
        }

        return implode('; ', $result);
    }

    /**
     * Returns true if at least one distractor was marked in a response.
     * @param array $response
     * @return bool
     */
    public function any_distractor_chosen(array $response) {
        foreach ($this->order as $key => $rowid) {
            $field = $this->distractorfield($key);
            if (property_exists((object) $response, $field) && $response[$field] == 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Categorise the student's response according to the categories defined by get_possible_responses.
     * @param array $response a response, as might be passed to  grade_response().
     * @return array subpartid => question_classified_response objects.
     *      returns an empty array if no analysis is possible.
     */
    public function classify_response(array $response) {
        if (!$this->is_complete_response($response)) {
            return [$this->id => question_classified_response::no_response()];
        }

        list($partialcredit, $state) = $this->grade_response($response);

        foreach ($this->order as $key => $rowid) {
            if (property_exists((object) $response, 'option') && ($response['option'] == $key)) {

                $row = $this->rows[$rowid];
                if ($row->number == $this->correctrow) {
                    $partialcredit = 1.0;
                } else {
                    $partialcredit = 0; // Due to non-linear math.
                }

                return [$this->id => new question_classified_response(
                    $rowid . '1',
                    question_utils::to_plain_text($row->optiontext, $row->optiontextformat),
                    $partialcredit), ];
            }
        }
    }

    /**
     * Use by many of the behaviours to determine whether the student's
     * response has changed.
     * This is normally used to determine that a new set
     * of responses can safely be discarded.
     * @param array $prevresponse the responses previously recorded for this question,
     *        as returned by {@see question_attempt_step::get_qt_data()}
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
     * If there is more than one correct answer, this method should just
     * return one possibility
     * @return array
     */
    public function get_correct_response() {
        $result = [];

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
     * @return string The grading object.
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
     * get_min_fraction() and 1.0, and the corresponding {@see question_state}
     * right, partial or wrong.
     * @param array $response responses, as returned by
     *        {@see question_attempt_step::get_qt_data()}.
     * @return array (number, integer) the fraction, and the state.
     */
    public function grade_response(array $response) {
        $grade = $this->grading()->grade_question($this, $response);
        $state = question_state::graded_state_for_fraction($grade);

        return [$grade, $state];
    }

    /**
     * What data may be included in the form submission when a student submits
     * this question in its current state?
     * This information is used in calls to optional_param. The parameter name
     * has {@see question_attempt::get_field_prefix()} automatically prepended.
     * @return array|string variable name => PARAM_... constant, or, as a special case
     *         that should only be used in unavoidable, the constant question_attempt::USE_RAW_DATA
     *         meaning take all the raw submitted data belonging to this question.
     */
    public function get_expected_data() {
        $result = [];

        $result["qtype_sc_changed_value"] = PARAM_INT;
        $result['option'] = PARAM_INT;

        foreach ($this->order as $key => $notused) {
            $distractorfield = $this->distractorfield($key);
            $result[$distractorfield] = PARAM_BOOL;
        }
        return $result;
    }

    /**
     * Makes HTML text (e.g.
     * option or feedback texts) suitable for inline presentation in renderer.php.
     * @param string $html
     * @return string
     */
    public function make_html_inline($html) {
        $html = preg_replace('~\s*<p>\s*~u', '', $html);
        $html = preg_replace('/<p\b[^>]*>/', '', $html);
        $html = preg_replace('~\s*</p>\s*~u', '<br />', $html);
        $html = preg_replace('~(<br\s*/?>)+$~u', '', $html);
        $html = str_replace("&nbsp;", " ", $html);

        return trim($html);
    }

    /**
     * Convert some part of the question text to plain text.
     * This might be used, for example, by get_response_summary().
     * @param string $text The HTML to reduce to plain text.
     * @param int $format the FORMAT_... constant.
     * @return string the equivalent plain text.
     */
    public function html_to_text($text, $format) {
        return question_utils::to_plain_text($text, $format);
    }

    /**
     * Computes the final grade when "Multiple Attempts" or "Hints" are enabled
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
     * @param question_hint_with_parts $hint a hint.
     */
    protected function disable_hint_settings_when_too_many_selected(question_hint_with_parts $hint) {
        $hint->clearwrong = false;
    }

    /**
     * Get one of the question hints. The question_attempt is passed in case
     * the question type wants to do something complex. For example, the
     * multiple choice with multiple responses question type will turn off most
     * of the hint options if the student has selected too many opitions.
     * @param int $hintnumber Which hint to display. Indexed starting from 0
     * @param question_attempt $qa The question_attempt.
     */
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
     * Checks whether the users is allow to be served a particular file.
     * @param object $qa
     * @param object $options the options that control display of the question.
     * @param string $component the name of the component we are serving files for.
     * @param string $filearea the name of the file area.
     * @param array $args the remaining bits of the file path.
     * @param bool $forcedownload whether the user must be forced to download the file.
     * @return bool true if the user can access this file.
     */
    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'qtype_sc' && $filearea == 'optiontext') {
            return true;
        } else if ($component == 'qtype_sc' && $filearea == 'feedbacktext') {
            return true;
        } else if ($component == 'question'
                    && in_array($filearea, ['correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'])) {

            if ($this->editedquestion == 1) {
                return true;
            } else {
                return $this->check_combined_feedback_file_access($qa, $options, $filearea);
            }

        } else if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);
        } else {
            return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
        }
    }
}
