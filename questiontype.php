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

global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/type/sc/lib.php');

/**
 * Question hint for sc.
 *
 * An extension of {@link question_hint} for questions like match and multiple
 * choice with multile answers, where there are options for whether to show the
 * number of parts right at each stage, and to reset the wrong parts.
 *
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_hint_sc extends question_hint_with_parts {

    public $statewhichincorrect;

    /**
     * Constructor.
     * @param int the hint id from the database.
     * @param string $hint The hint text
     * @param int the corresponding text FORMAT_... type.
     * @param bool $shownumcorrect whether the number of right parts should be shown
     * @param bool $clearwrong whether the wrong parts should be reset.
     */
    public function __construct($id, $hint, $hintformat, $shownumcorrect,
                                                            $clearwrong, $statewhichincorrect) {
        parent::__construct($id, $hint, $hintformat, $shownumcorrect, $clearwrong);
        $this->statewhichincorrect = $statewhichincorrect;
    }

    /**
     * Create a basic hint from a row loaded from the question_hints table in the database.
     * @param object $row with property options as well as hint, shownumcorrect and clearwrong set.
     * @return question_hint_sc
     */
    public static function load_from_record($row) {
        return new question_hint_sc($row->id, $row->hint, $row->hintformat,
                $row->shownumcorrect, $row->clearwrong, $row->options);
    }

    public function adjust_display_options(question_display_options $options) {
        parent::adjust_display_options($options);
        $options->statewhichincorrect = $this->statewhichincorrect;
    }
}

/**
 * The sc question type.
 */
class qtype_sc extends question_type {

    /**
     * Sets the default options for the question.
     *
     * (non-PHPdoc)
     *
     * @see question_type::set_default_options()
     */
    public function set_default_options($question) {
        $scconfig = get_config('qtype_sc');

        if (!isset($question->options)) {
            $question->options = new stdClass();
        }
        if (!isset($question->options->numberofrows)) {
            $question->options->numberofrows = QTYPE_SC_NUMBER_OF_OPTIONS;
        }
        if (!isset($question->options->shuffleanswers)) {
            $question->options->shuffleanswers = $scconfig->shuffleanswers;
        }
        if (!isset($question->options->scoringmethod)) {
            $question->options->scoringmethod = $scconfig->scoringmethod;
        }
        if (!isset($question->options->answernumbering)) {
            $question->options->answernumbering = 'none';
        }
        if (!isset($question->options->correctrow)) {
            $question->options->correctrow = 1;
        }
        if (!isset($question->options->rows)) {
            $rows = array();
            for ($i = 1; $i <= $question->options->numberofrows; $i++) {
                $row = new stdClass();
                $row->number = $i;
                $row->optiontext = '';
                $row->optiontextformat = FORMAT_HTML;
                $row->optionfeedback = '';
                $row->optionfeedbackformat = FORMAT_HTML;
                $rows[] = $row;
            }
            $question->options->rows = $rows;
        }

    }

    /**
     * Loads the question options and rows from the database.
     *
     * (non-PHPdoc)
     *
     * @see question_type::get_question_options()
     */
    public function get_question_options($question) {
        global $DB, $OUTPUT;

        parent::get_question_options($question);

        // Retrieve the question options.
        $question->options = $DB->get_record('qtype_sc_options',
            array('questionid' => $question->id
        ));
        // Retrieve the question rows (sc options).
        $question->options->rows = $DB->get_records('qtype_sc_rows',
            array('questionid' => $question->id
        ), 'number ASC', '*', 0, $question->options->numberofrows);

        foreach ($question->options->rows as $key => $row) {
            $question->{'option_' . $row->number}['text'] = $row->optiontext;
            $question->{'option_' . $row->number}['format'] = $row->optiontextformat;
            $question->{'feedback_' . $row->number}['text'] = $row->optionfeedback;
            $question->{'feedback_' . $row->number}['format'] = $row->optionfeedbackformat;
        }

        return true;
    }

    /**
     * Stores the question options in the database.
     *
     * (non-PHPdoc)
     *
     * @see question_type::save_question_options()
     */
    public function save_question_options($question) {
        global $DB;

        $context = $question->context;
        $result = new stdClass();

        // Get the old options.
        $options = $DB->get_record('qtype_sc_options', array('questionid' => $question->id));

        // If there are none, create a DB row.
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->scoringmethod = '';
            $options->shuffleanswers = '';
            $options->numberofrows = '';
            $options->correctrow = 0;
            $options->id = $DB->insert_record('qtype_sc_options', $options);
        }

        $options->scoringmethod = $question->scoringmethod;
        $options->shuffleanswers = $question->shuffleanswers;
        $options->numberofrows = $question->numberofrows;
        $options->answernumbering = $question->answernumbering;
        // Redmine 3587: Set default value for correctrow.
        if (property_exists($question, 'correctrow') && $question->correctrow) {
            $options->correctrow = $question->correctrow;
        } else {
            $options->correctrow = 0;
        }

        $DB->update_record('qtype_sc_options', $options);

        $this->save_hints($question, true);

        // Insert all the new rows.
        $oldrows = $DB->get_records('qtype_sc_rows',
        array('questionid' => $question->id
        ), 'number ASC');

        // Delete surplus rows/options from DB.
        $numberofobsolete = count($oldrows) - $question->numberofrows;
        if ($numberofobsolete > 0) {
            for ($k = 1; $k <= $numberofobsolete; $k++) {
                $obsoleterow = array_pop($oldrows);
                if ($obsoleterow->id) {
                    $DB->delete_records('qtype_sc_rows', array('id' => $obsoleterow->id));
                }
            }
        }

        for ($i = 1; $i <= $options->numberofrows; $i++) {
            $row = array_shift($oldrows);
            if (!$row) {
                $row = new stdClass();
                $row->questionid = $question->id;
                $row->number = $i;
                $row->optiontext = '';
                $row->optiontextformat = FORMAT_HTML;
                $row->optionfeedback = '';
                $row->optionfeedbackformat = FORMAT_HTML;

                $row->id = $DB->insert_record('qtype_sc_rows', $row);
            }

            // Also save images in optiontext and feedback.
            if (property_exists($question, 'option_' . $i)) {
                $optiondata = $question->{'option_' . $i};
                $row->optiontext = $this->import_or_save_files($optiondata, $context, 'qtype_sc',
                        'optiontext', $row->id);
            } else {
                $optiondata = array(
                        'text' => '',
                        'format' => FORMAT_HTML
                        );
                 $row->optiontext = '';
            }
            if ($optiondata['format']) {
                $row->optiontextformat = $optiondata['format'];
            }

            if (property_exists($question, 'feedback_' . $i)) {
                $optionfeedback = $question->{'feedback_' . $i};
                $row->optionfeedback = $this->import_or_save_files($optionfeedback, $context,
                        'qtype_sc', 'feedbacktext', $row->id);
            } else {
                $optionfeedback = array(
                                'text' => '',
                                'format' => FORMAT_HTML
                );
                $row->optionfeedback = '';
            }
            if ($optionfeedback['format']) {
                $row->optionfeedbackformat = $optionfeedback['format'];
            }

            $DB->update_record('qtype_sc_rows', $row);
        }
    }

    public function save_hints($formdata, $withparts = false) {
        global $DB;
        $context = $formdata->context;

        $oldhints = $DB->get_records('question_hints',
                array('questionid' => $formdata->id), 'id ASC');

        if (!empty($formdata->hint)) {
            $numhints = max(array_keys($formdata->hint)) + 1;
        } else {
            $numhints = 0;
        }

        if ($withparts) {
            if (!empty($formdata->hintclearwrong)) {
                $numclears = max(array_keys($formdata->hintclearwrong)) + 1;
            } else {
                $numclears = 0;
            }
            if (!empty($formdata->hintshownumcorrect)) {
                $numshows = max(array_keys($formdata->hintshownumcorrect)) + 1;
            } else {
                $numshows = 0;
            }
            $numhints = max($numhints, $numclears, $numshows);
        }

        for ($i = 0; $i < $numhints; $i += 1) {
            if (html_is_blank($formdata->hint[$i]['text'])) {
                $formdata->hint[$i]['text'] = '';
            }

            if ($withparts) {
                $clearwrong = !empty($formdata->hintclearwrong[$i]);
                $shownumcorrect = !empty($formdata->hintshownumcorrect[$i]);
                $statewhichincorrect = !empty($formdata->hintoptions[$i]);
            }

            if (empty($formdata->hint[$i]['text']) && empty($clearwrong) &&
                    empty($shownumcorrect) && empty($statewhichincorrect)) {
                continue;
            }

            // Update an existing hint if possible.
            $hint = array_shift($oldhints);
            if (!$hint) {
                $hint = new stdClass();
                $hint->questionid = $formdata->id;
                $hint->hint = '';
                $hint->id = $DB->insert_record('question_hints', $hint);
            }

            $hint->hint = $this->import_or_save_files($formdata->hint[$i],
                    $context, 'question', 'hint', $hint->id);
            $hint->hintformat = $formdata->hint[$i]['format'];
            if ($withparts) {
                $hint->clearwrong = $clearwrong;
                $hint->shownumcorrect = $shownumcorrect;
                $hint->options = $statewhichincorrect;
            }
            $DB->update_record('question_hints', $hint);
        }

        // Delete any remaining old hints.
        $fs = get_file_storage();
        foreach ($oldhints as $oldhint) {
            $fs->delete_area_files($context->id, 'question', 'hint', $oldhint->id);
            $DB->delete_records('question_hints', array('id' => $oldhint->id));
        }
    }

    protected function make_hint($hint) {
        return question_hint_sc::load_from_record($hint);
    }

    /**
     * Initialise the common question_definition fields.
     *
     * @param question_definition $question the question_definition we are creating.
     * @param object $questiondata the question data loaded from the database.
     */
    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);

        $question->shuffleanswers = $questiondata->options->shuffleanswers;
        $question->scoringmethod = $questiondata->options->scoringmethod;
        $question->answernumbering = $questiondata->options->answernumbering;
        $question->numberofrows = $questiondata->options->numberofrows;
        $question->correctrow = $questiondata->options->correctrow;
        $question->rows = $questiondata->options->rows;
    }

    /**
     * Custom method for deleting sc questions.
     *
     * (non-PHPdoc)
     *
     * @see question_type::delete_question()
     */
    public function delete_question($questionid, $contextid) {
        global $DB;
        $DB->delete_records('qtype_sc_options', array('questionid' => $questionid));
        $DB->delete_records('qtype_sc_rows', array('questionid' => $questionid));
        parent::delete_question($questionid, $contextid);
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_type::get_random_guess_score()
     */
    public function get_random_guess_score($questiondata) {
        $scoring = $questiondata->options->scoringmethod;
        if ($scoring == 'sconezero') {
            if ($questiondata->options && $questiondata->options->numberofrows &&
                $questiondata->options->numberofrows > 0) {
                    return 1.0 / (1.0 * $questiondata->options->numberofrows);
            } else {
                return 0.33;
            }
        } else if ($scoring == 'aprime') {
            if ($questiondata->options && $questiondata->options->numberofrows &&
                $questiondata->options->numberofrows > 0) {
                    return 1.0 / (1.0 * $questiondata->options->numberofrows);
            } else {
                return 0.4;
            }
        } else if ($scoring == 'subpoints') {
            if ($questiondata->options && $questiondata->options->numberofrows &&
                $questiondata->options->numberofrows > 0) {
                    return 1.0 / (1.0 * $questiondata->options->numberofrows);
            } else {
                return 0.5;
            }
        } else {
            return 0.00;
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see question_type::can_analyse_responses()
     */
    public function can_analyse_responses() {
        // This works in most cases.
        return true;
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_type::get_possible_responses()
     */
    public function get_possible_responses($questiondata) {
        $question = $this->make_question($questiondata);
        $choices = array();

        foreach ($question->rows as $rowid => $row) {

            if ($row->number == $question->correctrow) {
                $partialcredit = 1.0;
            } else {
                $partialcredit = 0; // Due to non-linear math.
            }

            $choices[$rowid . '1'] = new question_possible_response(
                question_utils::to_plain_text($row->optiontext, $row->optiontextformat), $partialcredit);
        }
        $choices[null] = question_possible_response::no_response();

        return array($questiondata->id => $choices);
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_type::move_files()
     */
    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_options_and_feedback($questionid, $oldcontextid, $newcontextid, true);
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_type::delete_files()
     */
    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_options_and_feedback($questionid, $contextid);
    }

    /**
     * Move all the files belonging to this question's options and feedbacks
     * when the question is moved from one context to another.
     *
     * @param int $questionid the question being moved.
     * @param int $oldcontextid the context it is moving from.
     * @param int $newcontextid the context it is moving to.
     * @param bool $answerstoo whether there is an 'answer' question area,
     *        as well as an 'answerfeedback' one. Default false.
     */
    protected function move_files_in_options_and_feedback($questionid, $oldcontextid, $newcontextid,
            $answerstoo = false) {
        global $DB;

        $fs = get_file_storage();

        $rowids = $DB->get_records_menu('qtype_sc_rows', array('questionid' => $questionid), 'id', 'id,1');

        foreach ($rowids as $rowid => $notused) {
            $fs->move_area_files_to_new_context($oldcontextid, $newcontextid, 'qtype_sc',
            'optiontext', $rowid);
            $fs->move_area_files_to_new_context($oldcontextid, $newcontextid, 'qtype_sc',
            'feedbacktext', $rowid);
        }
    }

    /**
     * Delete all the files belonging to this question's options and feedback.
     *
     *
     * @param unknown $questionid
     * @param unknown $contextid
     */
    protected function delete_files_in_options_and_feedback($questionid, $contextid) {
        global $DB;
        $fs = get_file_storage();

        $rowids = $DB->get_records_menu('qtype_sc_rows', array('questionid' => $questionid), 'id', 'id,1');

        foreach ($rowids as $rowid => $notused) {
            $fs->delete_area_files($contextid, 'qtype_sc', 'optiontext', $rowid);
            $fs->delete_area_files($contextid, 'qtype_sc', 'feedbacktext', $rowid);
        }
    }

    /**
     * Provide export functionality for xml format.
     *
     * @param question object the question object
     * @param format object the format object so that helper methods can be used
     * @param extra mixed any additional format specific data that may be passed by the format (see
     *        format code for info)
     *
     * @return string the data to append to the output buffer or false if error
     */
    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        $expout = '';
        $fs = get_file_storage();
        $contextid = $question->contextid;

        // First set the additional fields.
        $expout .= '    <scoringmethod>' . $format->writetext($question->options->scoringmethod) .
                 "</scoringmethod>\n";
        $expout .= '    <shuffleanswers>' . $format->get_single($question->options->shuffleanswers) .
                 "</shuffleanswers>\n";
        $expout .= '    <answernumbering>' . $format->writetext($question->options->answernumbering) .
                "</answernumbering>\n";
        $expout .= '    <numberofrows>' . $question->options->numberofrows . "</numberofrows>\n";
        $expout .= '    <correctrow>' . $question->options->correctrow . "</correctrow>\n";

        // Now we export the question rows (options).
        foreach ($question->options->rows as $row) {
            $number = $row->number;
            $expout .= "    <row number=\"$number\">\n";
            $textformat = $format->get_format($row->optiontextformat);
            $files = $fs->get_area_files($contextid, 'qtype_sc', 'optiontext', $row->id);
            $expout .= "      <optiontext format=\"$textformat\">\n" . '        ' .
                     $format->writetext($row->optiontext);
            $expout .= $format->write_files($files);
            $expout .= "      </optiontext>\n";

            $textformat = $format->get_format($row->optionfeedbackformat);
            $files = $fs->get_area_files($contextid, 'qtype_sc', 'feedbacktext', $row->id);
            $expout .= "      <feedbacktext format=\"$textformat\">\n" . '        ' .
                     $format->writetext($row->optionfeedback);
            $expout .= $format->write_files($files);
            $expout .= "      </feedbacktext>\n";
            $expout .= "    </row>\n";
        }

        return $expout;
    }

    /**
     * Provide import functionality for xml format.
     *
     * @param data mixed the segment of data containing the question
     * @param question object question object processed (so far) by standard import code
     * @param format object the format object so that helper methods can be used (in particular
     *        error())
     * @param extra mixed any additional format specific data that may be passed by the format (see
     *        format code for info)
     *
     * @return object question object suitable for save_options() call or false if cannot handle
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        // Check whether the question is for us.
        if (!isset($data['@']['type']) || $data['@']['type'] != 'sc') {
            return false;
        }

        $question = $format->import_headers($data);
        $question->qtype = 'sc';

        $question->scoringmethod = $format->getpath($data,
            array('#', 'scoringmethod', 0, '#', 'text', 0, '#'), 'sc');
        $question->shuffleanswers = $format->trans_single(
            $format->getpath($data, array('#', 'shuffleanswers', 0, '#'), 1));
        $question->answernumbering = $format->getpath($data,
            array('#', 'answernumbering', 0, '#', 'text', 0, '#'), 'sc');
        $question->numberofrows = $format->getpath($data,
            array('#', 'numberofrows', 0, '#'), QTYPE_SC_NUMBER_OF_OPTIONS);
        $question->correctrow = $format->getpath($data,
            array('#', 'correctrow', 0, '#'), 0);

        $rows = $data['#']['row'];
        $i = 1;
        foreach ($rows as $row) {
            $number = $format->getpath($row, array('@', 'number'), $i++);

            $question->{'option_' . $number} = array();
            $question->{'option_' . $number}['text'] = $format->getpath($row,
                array('#', 'optiontext', 0, '#', 'text', 0, '#'), '', true);
            $question->{'option_' . $number}['format'] = $format->trans_format(
                $format->getpath($row, array('#', 'optiontext', 0, '@', 'format'), FORMAT_HTML));

            $question->{'option_' . $number}['files'] = array();

            // Restore files in options (rows).
            $files = $format->getpath($row, array('#', 'optiontext', 0, '#', 'file'), array(), false);
            foreach ($files as $file) {
                $filesdata = new stdclass();
                $filesdata->content = $file['#'];
                $filesdata->encoding = $file['@']['encoding'];
                $filesdata->name = $file['@']['name'];
                $question->{'option_' . $number}['files'][] = $filesdata;
            }

            $question->{'feedback_' . $number} = array();
            $question->{'feedback_' . $number}['text'] = $format->getpath(
                $row, array('#', 'feedbacktext', 0, '#', 'text', 0, '#'), '', true);
            $question->{'feedback_' . $number}['format'] = $format->trans_format(
                $format->getpath($row, array('#', 'feedbacktext', 0, '@', 'format'), FORMAT_HTML));

            // Restore files in option feedback.
            $question->{'feedback_' . $number}['files'] = array();
            $files = $format->getpath($row, array('#', 'feedbacktext', 0, '#', 'file'), array(), false);

            foreach ($files as $file) {
                $filesdata = new stdclass();
                $filesdata->content = $file['#'];
                $filesdata->encoding = $file['@']['encoding'];
                $filesdata->name = $file['@']['name'];
                $question->{'feedback_' . $number}['files'][] = $filesdata;
            }
        }

        return $question;
    }
}
