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
 * qtype_sc renderer classes.
 *
 * @package     qtype_sc
 * @author      ETH Zurich (moodle@id.ethz.ch)
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @author      Jürgen Zimmer (juergen.zimmer@edaktik.at)
 * @author      Andreas Hruska (andreas.hruska@edaktik.at)
 * @copyright   2018 ETHZ {@link http://ethz.ch/}
 * @copyright   2017 eDaktik GmbH {@link http://www.edaktik.at}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Subclass for generating the bits of output specific to qtype_sc questions.
 *
 * @copyright   2016 ETHZ {@link http://ethz.ch/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_sc_renderer extends qtype_renderer {

    /**
     * Generate the display of the formulation part of the question.
     * This is the area that contains the question text (stem), and the controls for students to
     * input their answers.
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $displayoptions controls what should and should not be displayed.
     * @return string HTML fragment.
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $displayoptions) {

        $question = $qa->get_question();
        $order = $question->get_order($qa);
        $response = $question->get_response($qa);

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa), array('class' => 'qtext'));
        $table = $this->createrows($question, $displayoptions, $qa, $response, $order);
        $result .= html_writer::table($table, true);

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div', $question->get_validation_error($qa->get_last_qt_data()),
                array('class' => 'validationerror'));
        }

        $changedvalue = $qa->get_qt_field_name('qtype_sc_changed_value');
        $result .= "<input type='hidden' id='qtype_sc_changed_value_" . $question->id .
            "' name='" . $changedvalue . "'/>";
        $result .= "<input type='hidden' id='qtype_sc_scoring_method_" . $question->id .
            "' value='" . $question->scoringmethod . "'/>";

        if (!empty(get_config('qtype_sc')->showscoringmethod)) {
            $result .= $this->showscoringmethod($question);
        }

        if (!$displayoptions->readonly) {
            $optionhighlighting = $question->scoringmethod == 'sconezero' ? false : true;
            $this->page->requires->js_call_amd('qtype_sc/question_behaviour', 'init', array($optionhighlighting, $question->id));
        } else {
            $this->page->requires->js_call_amd('qtype_sc/question_behaviour', 'initReadonly', array($question->id));
        }

        return $result;
    }

    /**
     * Returns the HTML representation of all question rows.
     * @param object $question
     * @param question_display_options $displayoptions
     * @param question_attempt $qa
     * @param array $response
     * @param array $order
     * @return string
     */
    protected function createrows($question, $displayoptions, $qa, $response, $order) {

        $table = new html_table();
        $table->id = 'questiontable' . $question->id;
        $table->attributes['class'] = 'generaltable sc';
        $order['-1'] = '-1';

        foreach ($order as $key => $rowid) {

            $rowdata = [];

            if ($key != '-1') {
                $row = $question->rows[$rowid];

                $option = [];
                $option['inputid'] = 'q' . $question->id . '_option' . $key;
                $option['selected'] = $question->is_option_selected($response, $key);
                $option['inputname'] = $qa->get_field_prefix() . 'option';
                $option['rowtext'] = $question->make_html_inline(
                    $this->number_in_style(
                        $key,
                        $question->answernumbering) .
                        $question->format_text(
                            $row->optiontext,
                            $row->optiontextformat,
                            $qa,
                            'qtype_sc',
                            'optiontext',
                            $row->id));

                $distractor = [];
                $distractor['inputid'] = 'q' . $question->id . '_distractor' . $key;
                $distractor['selected'] = $question->is_distractor_selected($response, $key);
                $distractor['name'] = $question->distractorfield($key);
                $distractor['inputname'] = $qa->get_field_prefix() . $distractor['name'];

                // Optionradio.

                $output = '';
                $label = '';
                if ($displayoptions->readonly && $option['selected']) {
                    $label = get_string('markedascorrect', 'qtype_sc');
                }
                if (!$displayoptions->readonly) {
                    $label = get_string('markascorrect', 'qtype_sc');
                }

                $feedbackimage = $displayoptions->correctness ? $this->get_correctness_image($question, $row, $response) : '';

                $inputattributes = [];
                $inputattributes['type'] = 'radio';
                $inputattributes['name'] = $option['inputname'];
                $inputattributes['id'] = $option['inputid'];
                $inputattributes['class'] = 'optionradio' . ($displayoptions->readonly ? '' : ' active ');
                $inputattributes['data-questionid'] = $question->id;
                $inputattributes['data-number'] = $key;
                $inputattributes['value'] = $key;
                $option['selected'] ? $inputattributes['checked'] = "checked" : '';
                $displayoptions->readonly ? $inputattributes['readonly'] = 'readonly' : '';
                $displayoptions->readonly ? $inputattributes['disabled'] = 'disabled' : '';

                $output .= html_writer::empty_tag('input', $inputattributes);

                $labelattributes = [];
                $labelattributes['class'] = 'w-100 ' . ($distractor['selected'] ? 'linethrough' : '');
                $labelattributes['title'] = $label;

                $output .= html_writer::label($feedbackimage . $option['rowtext'], $option['inputid'], true, $labelattributes);

                $cell = new html_table_cell($output);
                $cell->attributes['class'] = 'scoptionbutton';
                $rowdata[] = $cell;

                // Distractorcheckbox.

                $output = '';
                $label = '';
                if ($displayoptions->readonly && $distractor['selected']) {
                    $label = get_string('markedasdistractor', 'qtype_sc');
                }
                if (!$displayoptions->readonly) {
                    $label = get_string('markasdistractor', 'qtype_sc');
                }

                $output .= html_writer::empty_tag('input', array(
                    'type' => 'hidden',
                    'name' => $distractor['inputname'],
                    'value' => 0,
                ));

                $inputattributes = [];
                $inputattributes['type'] = 'checkbox';
                $inputattributes['name'] = $distractor['inputname'];
                $inputattributes['id'] = $distractor['inputid'];
                $inputattributes['class'] = 'distractorcheckbox' . ($displayoptions->readonly ? '' : ' active');
                $inputattributes['data-questionid'] = $question->id;
                $inputattributes['data-number'] = $key;
                $inputattributes['value'] = 1;
                $distractor['selected'] ? $inputattributes['checked'] = "checked" : '';
                $displayoptions->readonly ? $inputattributes['readonly'] = 'readonly' : '';
                $displayoptions->readonly ? $inputattributes['disabled'] = 'disabled' : '';

                $output .= html_writer::empty_tag('input', $inputattributes);

                $labelattributes = [];
                $labelattributes['title'] = $label;

                $output .= html_writer::label('S', $distractor['inputid'], true, $labelattributes);

                $cell = new html_table_cell($output);
                $cell->attributes['class'] = 'scdistractorbutton';
                $rowdata[] = $cell;

                // For correctness we have to grade the option...
                if ($displayoptions->correctness) {
                    $feedbackimage = $this->get_answer_correctness_image($question, $key, $row, $response, $option['selected'],
                    $distractor['selected']);
                    $cell = new html_table_cell($feedbackimage);
                    $cell->attributes['class'] = 'sccorrectness';
                    $rowdata[] = $cell;
                }

                // Add the feedback to the table, if it is visible.
                if ($displayoptions->feedback
                    && empty($displayoptions->suppresschoicefeedback)
                    && trim($row->optionfeedback)) {
                    if ($option['selected']) {
                        $cell = new html_table_cell(
                                    html_writer::tag('div',
                                        $question->make_html_inline(
                                            $question->format_text(
                                                $row->optionfeedback,
                                                $row->optionfeedbackformat,
                                                $qa,
                                                'qtype_sc',
                                                'feedbacktext',
                                                $rowid)),
                                        array('class' => 'scspecificfeedback')));
                    } else {
                        $cell = new html_table_cell();
                    }
                    $cell->attributes['class'] = 'scspecificfeedbackcell';
                    $rowdata[] = $cell;
                }
            } else if ($key == '-1' && !$displayoptions->readonly) {
                $option = [];
                $option['inputid'] = 'q' . $question->id . '_option' . $key;
                $option['selected'] = isset($response['option']) ? ($response['option'] == '-1' ? true : false) : true;
                $option['inputname'] = $qa->get_field_prefix() . 'option';
                $option['rowtext'] = get_string('clearchoice', 'qtype_sc');

                // Optionradio.
                $output = '';

                $label = get_string('markascorrect', 'qtype_sc');

                $inputattributes = [];
                $inputattributes['type'] = 'radio';
                $inputattributes['name'] = $option['inputname'];
                $inputattributes['id'] = $option['inputid'];
                $inputattributes['class'] = 'optionradio hidden' . ($displayoptions->readonly ? '' : ' active');
                $inputattributes['data-questionid'] = $question->id;
                $inputattributes['data-number'] = $key;
                $inputattributes['value'] = $key;
                $option['selected'] ? $inputattributes['checked'] = "checked" : '';
                $displayoptions->readonly ? $inputattributes['readonly'] = 'readonly' : '';
                $displayoptions->readonly ? $inputattributes['disabled'] = 'disabled' : '';

                $output .= html_writer::empty_tag('input', $inputattributes);

                $labelattributes = [];
                $labelattributes['title'] = $label;
                $labelattributes['class'] = 'btn btn-secondary';

                $output .= html_writer::label($option['rowtext'], $option['inputid'], true, $labelattributes);

                $cell = new html_table_cell($output);
                $cell->attributes['class'] = 'scoptionbutton';
                $rowdata[] = $cell;

                $cell = new html_table_cell();
                $rowdata[] = $cell;
            }

            $table->data[] = $rowdata;
            $table->rowclasses[] = 'optionrow' . $key;
        }
        return $table;
    }

    /**
     * Returns a string containing the rendererd question's scoring method.
     * Appends an info icon containing information about the scoring method.
     * @param qtype_mtf_question $question
     * @return string
     */
    private function showscoringmethod($question) {

        $result = '';

        if (get_string_manager()->string_exists('scoring' . $question->scoringmethod, 'qtype_sc')) {
            $outputscoringmethod = get_string('scoring' . $question->scoringmethod, 'qtype_sc');
        } else {
            $outputscoringmethod = $question->scoringmethod;
        }

        if (get_string_manager()->string_exists('scoring' . $question->scoringmethod . '_help', 'qtype_sc')) {
            $label = get_string('scoringmethod', 'qtype_sc') . ': <b>' . ucfirst($outputscoringmethod) . '</b>';
            $result .= html_writer::tag('div',
                '<br>' . $label . $this->output->help_icon('scoring' . $question->scoringmethod, 'qtype_sc'),
                array('id' => 'scoringmethodinfo_q' . $question->id,
                    'data-scoringmethodlabel' => $label,
                    'data-scoringmethod' => $question->scoringmethod));
        }
        return $result;
    }

    /**
     * Returns the image shown in the first column indicating whether an option is correct or not.
     * @param qtype_sc_question $question
     * @param object $row
     * @param array $response
     * @return string
     */
    private function get_correctness_image($question, $row, $response) {

        if ($row->number == $question->correctrow) {
            return html_writer::span($this->feedback_image(1.0), 'scgreyingout');
        } else if ($row->number != $question->correctrow) {
            return html_writer::span($this->feedback_image(0.0), 'scgreyingout');
        }
        return '';
    }

    /**
     * Returns the image shown in the last column indicating the correctness of an answer given by the student.
     * @param qtype_sc_question $question
     * @param int $key
     * @param object $row
     * @param array $response
     * @param bool $isselected
     * @param bool $distractorischecked
     * @return string
     */
    private function get_answer_correctness_image($question, $key, $row, $response, $isselected, $distractorischecked) {

        list($questiongrade, $state) = $question->grade_response($response);

        if ($question->scoringmethod == 'sconezero') {
            return $this->get_answer_correctness_image_sconezero(
                $question, $key, $row, $response, $questiongrade, $isselected
            );
        } else {
            return $this->get_answer_correctness_image_aprime_subpoints(
                $question, $key, $row, $response, $questiongrade, $isselected, $distractorischecked
            );
        }
    }

    /**
     * Returns the image shown in the last column indicating the correctness of an answer (szonezero) given by the student.
     * @param qtype_sc_question $question
     * @param int $key
     * @param object $row
     * @param array $response
     * @param bool $questiongrade
     * @param bool $isselected
     * @return string
     */
    private function get_answer_correctness_image_sconezero($question, $key, $row, $response, $questiongrade, $isselected) {

        if ($isselected) {
            return html_writer::span($this->feedback_image($questiongrade), 'scgreyingout');
        }
        return '';
    }

    /**
     * Returns the image shown in the last column indicating the correctness of an answer (subpoints) given by the student.
     * @param qtype_sc_question $question
     * @param int $key
     * @param object $row
     * @param array $response
     * @param bool $questiongrade
     * @param bool $isselected
     * @param bool $distractorischecked
     * @return string
     */
    private function get_answer_correctness_image_aprime_subpoints($question, $key, $row, $response, $questiongrade,
        $isselected, $distractorischecked) {

        if ($isselected) {
            return html_writer::span($this->feedback_image($questiongrade), 'scgreyingout');
        }

        if (!property_exists((object) $response, 'option') || $response['option'] == '-1') {
            if ($questiongrade > 0.0) {
                if ($distractorischecked) {
                    return html_writer::span($this->feedback_image(1.0), 'scgreyingout');
                }
            } else {
                if ($distractorischecked) {
                    if ($row->number == $question->correctrow) {
                        return html_writer::span($this->feedback_image(0.0), 'scgreyingout');
                    } else if ($row->number != $question->correctrow) {
                        return html_writer::span($this->feedback_image(1.0), 'scgreyingout');
                    }
                }
            }
        }
        return '';
    }

    /**
     * The prompt for the user to answer a question.
     * @return Ambigous <string, lang_string, unknown, mixed>
     */
    protected function prompt() {
        return get_string('selectone', 'qtype_sc');
    }

    /**
     * (non-PHPdoc).
     * @see qtype_renderer::correct_response()
     * @param question_attempt $qa
     * @return string
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
                        $question->format_text(
                            $row->optiontext,
                            $row->optiontextformat,
                            $qa,
                            'qtype_sc',
                            'optiontext', $rowid)) . ': ' . get_string('correct', 'qtype_sc');
            } else {
                $result[] = ' ' .
                    $question->make_html_inline(
                        $question->format_text(
                            $row->optiontext,
                            $row->optiontextformat,
                            $qa,
                            'qtype_sc',
                            'optiontext', $rowid)) . ': ' . get_string('incorrect', 'qtype_sc');
            }
        }
        if (!empty($result)) {
            $response = '<ul style="list-style-type: none;"><li>';
            $response .= implode('</li><li>', $result);
            $response .= '</li></ul>';
        }
        return $response;
    }

    /**
     * Returns Questionnumber html
     * @param string $qnum
     * @return string
     */
    protected function number_html($qnum) {
        return '<span class="answernumber">' . $qnum . '.</span>';
    }

    /**
     * Returns the number in the requested style.
     * @param int $num The number, starting at 0.
     * @param string $style The style to render the number in. One of the
     *      options returned by {@see qtype_mtf:get_numbering_styles()}.
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
