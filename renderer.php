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
 * @copyright     2017 eDaktik GmbH {@link http://www.edaktik.at}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/outputcomponents.php');


/**
 * Subclass for generating the bits of output specific to sc questions.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_sc_renderer extends qtype_renderer {

    public function head_code(question_attempt $qa) {
        global $PAGE;

        parent::head_code($qa);
    }

    /**
     * Generate the display of the formulation part of the question.
     * This is the area that contains the question text (stem), and the
     * controls for students to input their answers.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     *
     * @return string HTML fragment.
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $displayoptions) {
        global $PAGE;

        $question = $qa->get_question();
        $order = $question->get_order($qa);
        $response = $question->get_response($qa);

        $isreadonly = $displayoptions->readonly;

        $optionhighlighting = true;
        if ($question->scoringmethod == 'sconezero') {
            $optionhighlighting = false;
        }

        if (!$isreadonly) {
            $PAGE->requires->js_call_amd('qtype_sc/question_behaviour', 'init', array($optionhighlighting, $question->id));
        }

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa),
        array('class' => 'qtext'
        ));

        $table = new html_table();
        $table->id = 'questiontable' . $question->id;
        $table->attributes['class'] = 'generaltable sc';

        // Add empty header for correctness if needed.
        if ($displayoptions->correctness) {
            $table->head[] = '';
        }
        // Add empty header for feedback if needed.
        if ($displayoptions->feedback) {
            $table->head[] = '';
        }

        $correctnessclass = '';
        if ($displayoptions->correctness) {
            $correctnessclass = ' correctness';
        }

        foreach ($order as $key => $rowid) {
            $row = $question->rows[$rowid];

            // Has a selection been made for this option?
            $isselected = $question->is_row_selected($response, $key);

            // Holds the data for one table row.
            $rowdata = array();

            // Add radio button for option choice.
            $optionfield = $question->optionfield($key);
            $optioninputname = $qa->get_field_prefix() . $optionfield;

            $radio = $this->optioncheckbox($question->id, $optioninputname, $key, $isselected, $isreadonly, $optionhighlighting);

            // Radio button: add correctness icon with radio button if needed.
            if ($displayoptions->correctness) {
                $radio .= $this->get_correctness_image($question, $key, $row, $response);
            }

            $cell = new html_table_cell($radio);
            $cell->attributes['class'] = 'scoptionbutton' . $correctnessclass;
            $rowdata[] = $cell;

            // Pre-comput the distractorfield value.
            $distractorfield = $question->distractorfield($key);
            $distractorinputname = $qa->get_field_prefix() . $distractorfield;
            $distractorischecked = false;
            if (array_key_exists($distractorfield, $response) && $response[$distractorfield]) {
                $distractorischecked = true;
            }

            // Add the formated option text to the table.
            $rowtext = $question->make_html_inline(
                    $this->number_in_style($key, $question->answernumbering) .
                    $question->format_text($row->optiontext, $row->optiontextformat, $qa,
                            'qtype_sc', 'optiontext', $row->id));

            $optiontextclass = 'optiontext';
            if ($distractorischecked) {
                $optiontextclass = 'optiontext linethrough';
            }
            $cell = new html_table_cell('<span id="q' . $question->id . '_optiontext' . $key . '" class="' .
                $optiontextclass . '">' . $rowtext . '</span>');
            $cell->attributes['class'] = 'scoptiontext';
            $rowdata[] = $cell;

            // Add button for distractor choice.
            $button = $this->distractorcheckbox($question->id, $distractorinputname, $key, $distractorischecked,
                $isreadonly, $optionhighlighting);

            $cell = new html_table_cell($button);
            $cell->attributes['class'] = 'scdistractorbutton';
            $rowdata[] = $cell;

            // For correctness we have to grade the option...
            if ($displayoptions->correctness) {
                $feedbackimage = $this->get_answer_correctness_image($question, $key, $row, $response, $isselected,
                    $distractorischecked);
                $cell = new html_table_cell($feedbackimage);
                $cell->attributes['class'] = 'sccorrectness';
                $rowdata[] = $cell;
            }

            // Add the feedback to the table, if it is visible.
            if ($displayoptions->feedback && empty($displayoptions->suppresschoicefeedback) &&
                      trim($row->optionfeedback)) {
                if ($isselected) {
                    $cell = new html_table_cell(
                            html_writer::tag('div',
                                    $question->make_html_inline(
                                            $question->format_text($row->optionfeedback,
                                                    $row->optionfeedbackformat, $qa, 'qtype_sc',
                                                    'feedbacktext', $rowid)),
                                    array('class' => 'scspecificfeedback')));
                } else {
                    $cell = new html_table_cell();
                }
                $cell->attributes['class'] = 'scspecificfeedbackcell';
                $rowdata[] = $cell;
            }
            $rowclass = 'optionrow' . $key;

            $table->data[] = $rowdata;
            $table->rowclasses[] = $rowclass;
        }

        $result .= html_writer::table($table, true);

        $changedvalue = $qa->get_qt_field_name('qtype_sc_changed_value');
        $result .= "<input type='hidden' id='qtype_sc_changed_value_" . $question->id . "' name='" . $changedvalue . "'/>";
        $result .= "<input type='hidden' id='qtype_sc_scoring_method_" . $question->id . "' value='" . $question->scoringmethod . "'/>";

        if (!empty(get_config('qtype_sc')->showscoringmethod)) {
            $result .= $this->showscoringmethod($question);
        }

        return $result;
    }

    /**
     * Returns a string containing the rendererd question's scoring method.
     * Appends an info icon containing information about the scoring method.
     * @param qtype_sc_question $question
     * @return string
     */
    private function showscoringmethod($question) {
        global $OUTPUT;

        $result = '';

        if (get_string_manager()->string_exists('scoring' . $question->scoringmethod, 'qtype_sc')) {
            $outputscoringmethod = get_string('scoring' . $question->scoringmethod, 'qtype_sc');
        } else {
            $outputscoringmethod = $question->scoringmethod;
        }

        if (get_string_manager()->string_exists('scoring' . $question->scoringmethod . '_help', 'qtype_sc')) {
            $result .= html_writer::tag('div',
                '<br>'. get_string('scoringmethod', 'qtype_sc'). ': <b>' . ucfirst($outputscoringmethod) . '</b>' .
                $OUTPUT->help_icon('scoring' . $question->scoringmethod, 'qtype_sc'),
                array('id' => 'scoringmethodinfo_q' . $question->id));
        }
        return $result;
    }

    /**
     * Returns the image shown in the first column indicating whether an option is correct or not.
     * @param qtype_sc_question $question
     * @param unknown $row
     * @param array $response
     * @return string
     */
    private function get_correctness_image(qtype_sc_question $question, $key, $row, array $response) {
        if ($question->scoringmethod == 'sconezero') {
            return $this->get_correctness_image_sconezero($question, $key, $row, $response);
        } else {
            return $this->get_correctness_image_aprime_subpoints($question, $key, $row, $response);
        }
    }

    private function get_correctness_image_sconezero(qtype_sc_question $question, $key, $row, array $response) {
        $optionfield = $question->optionfield($key);
        if (array_key_exists($optionfield, $response) && $response[$optionfield] &&
            $row->number == $question->correctrow) {
                    return '<span class="scgreyingout">' . $this->feedback_image(0.5) . '</span>';
        }
        return '';
    }


    private function get_correctness_image_aprime_subpoints(qtype_sc_question $question, $key, $row, array $response) {
        $optionfield = $question->optionfield($key);
        if ($row->number == $question->correctrow) {
            return '<span class="scgreyingout">' . $this->feedback_image(0.5) . '</span>';
        }
        return '';
    }

    /**
     * Returns the image shown in the last column indicating the correctness of an answer given by the student.
     * @param qtype_sc_question $question
     * @param unknown $row
     * @param array $response
     * @return string
     */
    private function get_answer_correctness_image(qtype_sc_question $question, $key, $row, array $response, $isselected,
                                                  $distractorischecked) {
        list($questiongrade, $state) = $question->grade_response($response);
        if ($question->scoringmethod == 'sconezero') {
            return $this->get_answer_correctness_image_sconezero($question, $key, $row, $response, $questiongrade, $isselected);
        } else {
            return $this->get_answer_correctness_image_aprime_subpoints($question, $key, $row, $response, $questiongrade,
                $isselected, $distractorischecked);
        }
    }

    private function get_answer_correctness_image_sconezero(qtype_sc_question $question, $key, $row, $response,
                                                            $questiongrade, $isselected) {
        if ($isselected) {
            return '<span class="scgreyingout">' . $this->feedback_image($questiongrade) . '</span>';
        }
        return '';
    }


    private function get_answer_correctness_image_aprime_subpoints(qtype_sc_question $question, $key, $row, $response,
                                                                   $questiongrade, $isselected, $distractorischecked) {
        if ($isselected) {
            return '<span class="scgreyingout">' . $this->feedback_image($questiongrade) . '</span>';
        }
        // If no option was chosen but the distractors where all chosen correctly (grade > 0.0), then
        // display the corresponding image.
        $optionfield = $question->optionfield($key);
        if (!array_key_exists($optionfield, $response) || !$response[$optionfield]) {
            // If all distractors chosen correctly.
            if ($questiongrade > 0.0) {
                if ($distractorischecked) {
                    return '<span class="scgreyingout">' . $this->feedback_image(0.5) . '</span>';
                }
            } else {
                // Correct option was chosen as distractor.
                if ($row->number == $question->correctrow) {
                    return '<span class="scgreyingout">' . $this->feedback_image(0.0) . '</span>';
                }
            }
        }
        return '';
    }

    /**
     * Returns the HTML representation of a radio button with the given attributes.
     *
     * @param unknown $name
     * @param unknown $value
     * @param unknown $checked
     * @param unknown $readonly
     *
     * @return string
     */
    protected static function optioncheckbox($questionid, $name, $value, $checked, $readonlybool, $optionhighlighting) {
        $output = '';
        $readonly = $readonlybool ? 'readonly="readonly" disabled="disabled"' : '';

        if ($checked) {
            $radio1checked = 'checked="checked"';
            $radio2checked = '';
        } else {
            $radio1checked = '';
            $radio2checked = 'checked="checked"';
        }

        $inputid = 'q' . $questionid . '_optionbutton' . $value;

        if (!$readonlybool) {

            $output .= '<input type ="radio" ' .
                        'name="' . $name . '" ' .
                        'id="' . $inputid . '_hid" ' .
                        'class="optioncheckbox hidden active"' .
                        'data-questionid="' . $questionid . '" '.
                        'data-number="' . $value . '" ' .
                        $radio2checked .
                        'value="0" />';

            $output .= '<input type ="radio" ' .
                        'name="' . $name . '" ' .
                        'id="' . $inputid . '" ' .
                        'class="optioncheckbox active"' .
                        'data-questionid="' . $questionid . '" '.
                        'data-number="' . $value . '" ' .
                        $radio1checked .
                        'value="1" />';
        } else {

            $output .= '<input type ="radio" ' .
                        'name="' . $name . '" ' .
                        'id="' . $inputid . '" ' .
                        'class="optioncheckbox"' .
                        'data-questionid="' . $questionid . '" '.
                        'data-number="' . $value . '" ' .
                        $radio1checked .
                        $readonly .
                        'value="1" />';
        }

        $labeltitle = '';
        if ($readonlybool) {
            if ($checked) {
                $labeltitle = get_string('markedascorrect', 'qtype_sc');
            }
        } else {
            $labeltitle = get_string('markascorrect', 'qtype_sc');
        }
        $output .= '<label for="' . $inputid . '" title="' . $labeltitle . '"></label>';

        return $output;
    }

    /**
     * Returns the HTML representation of a radio button with the given attributes.
     *
     * @param unknown $name
     * @param unknown $value
     * @param unknown $checked
     * @param unknown $readonly
     *
     * @return string
     */
    protected static function distractorcheckbox($questionid, $name, $value, $checked, $readonlybool, $optionhighlighting) {
        global $OUTPUT;

        $output = '';
        $readonly = $readonlybool ? 'readonly="readonly" disabled="disabled"' : '';

        if ($checked) {
            $radio1checked = 'checked="checked"';
            $radio2checked = '';
        } else {
            $radio1checked = '';
            $radio2checked = 'checked="checked"';
        }

        $inputid = 'q' . $questionid . '_distractor' . $value;

        if (!$readonlybool) {

            $output .= '<input type ="radio" ' .
                        'name="' . $name . '" ' .
                        'id="' . $inputid . '_hid" ' .
                        'class="distractorcheckbox hidden active"' .
                        'data-questionid="' . $questionid . '" '.
                        'data-number="' . $value . '" ' .
                        $radio2checked .
                        'value="0" />';

            $output .= '<input type ="radio" ' .
                        'name="' . $name . '" ' .
                        'id="' . $inputid . '" ' .
                        'class="distractorcheckbox xg active"' .
                        'data-questionid="' . $questionid . '" '.
                        'data-number="' . $value . '" ' .
                        $radio1checked .
                        'value="1" />';
        } else {
            $output .= '<input type ="radio" ' .
            'name="' . $name . '" ' .
            'id="' . $inputid . '" ' .
            'class="distractorcheckbox"' .
            'data-questionid="' . $questionid . '" '.
            'data-number="' . $value . '" ' .
            $radio1checked .
            $readonly .
            'value="1" />';
        }

        $labeltitle = '';
        if ($readonlybool) {
            if ($checked) {
                $labeltitle = get_string('markedasdistractor', 'qtype_sc');
            }
        } else {
            $labeltitle = get_string('markasdistractor', 'qtype_sc');
        }
        $output .= '<label for="' . $inputid . '" title="' . $labeltitle . '"></label>';

        return $output;
    }


    /**
     * The prompt for the user to answer a question.
     *
     * @return Ambigous <string, lang_string, unknown, mixed>
     */
    protected function prompt() {
        return get_string('selectone', 'qtype_sc');
    }

    /**
     * (non-PHPdoc).
     *
     * @see qtype_renderer::correct_response()
     */
    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();

        $result = array();
        $response = '';
        $correctresponse = $question->get_correct_response(true);

        foreach ($question->order as $key => $rowid) {
            $row = $question->rows[$rowid];
            $correctrow = $question->correctrow;

            if ($row->number == $correctrow) {
                $result[] = ' ' .
                     $question->make_html_inline(
                            $question->format_text($row->optiontext, $row->optiontextformat, $qa,
                                    'qtype_sc', 'optiontext', $rowid)) .
                                    ': ' . get_string('correct', 'qtype_sc');
            } else {
                $result[] = ' ' .
                        $question->make_html_inline(
                                $question->format_text($row->optiontext, $row->optiontextformat, $qa,
                                        'qtype_sc', 'optiontext', $rowid)) .
                                        ': ' . get_string('incorrect', 'qtype_sc');
            }
        }
        if (!empty($result)) {
            $response = '<ul style="list-style-type: none;"><li>';
            $response .= implode('</li><li>', $result);
            $response .= '</li></ul>';
        }

        return $response;
    }

    protected function number_html($qnum) {
        return '<span class="answernumber">' . $qnum . '.</span>';
    }

    /**
     * @param int $num The number, starting at 0.
     * @param string $style The style to render the number in. One of the
     * options returned by {@link qtype_multichoice:;get_numbering_styles()}.
     * @return string the number $num in the requested style.
     */
    protected function number_in_style($num, $style) {
        switch($style) {
            case 'abc':
                $number = chr(ord('a') + $num);
                break;
            case 'ABCD':
                $number = chr(ord('A') + $num);
                break;
            case '123':
                $number = $num + 1;
                break;
            case 'iii':
                $number = question_utils::int_to_roman($num + 1);
                break;
            case 'IIII':
                $number = strtoupper(question_utils::int_to_roman($num + 1));
                break;
            case 'none':
                return '';
            default:
                return 'ERR';
        }
        return $this->number_html($number);
    }

}
