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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * qtype_sc editing form.
 *
 * @package qtype_sc
 * @author ETH Zurich (moodle@id.ethz.ch)
 * @author Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @author Jürgen Zimmer (juergen.zimmer@edaktik.at)
 * @author Andreas Hruska (andreas.hruska@edaktik.at)
 * @copyright 2018 ETHZ {@link http://ethz.ch/}
 * @copyright 2017 eDaktik GmbH {@link http://www.edaktik.at}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot . '/question/type/edit_question_form.php');
require_once ($CFG->dirroot . '/question/type/sc/lib.php');
require_once ($CFG->dirroot . '/question/engine/bank.php');
require_once ($CFG->dirroot . '/question/type/multichoice/questiontype.php');

/**
 * qtype_sc editing form definition.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_sc_edit_form extends question_edit_form {

    /** @var int numberofrows */
    private $numberofrows;

    /** @var string messages */
    private $messages;

    /** @var int lastnumberofrows */
    private $lastnumberofrows;

    /**
     * (non-PHPdoc).
     *
     * @see myquestion_edit_form::qtype()
     */
    public function qtype() {
        return 'sc';
    }

    /**
     * Build the form definition.
     * This adds all the form fields that the default question type supports.
     * If your question type does not support all these fields, then you can
     * override this method and remove the ones you don't want with $mform->removeElement().
     */
    protected function definition() {
        global $DB, $PAGE;

        $mform = $this->_form;

        // Standard fields at the start of the form.
        $mform->addElement('header', 'generalheader', get_string("general", 'form'));

        if (!isset($this->question->id)) {
            if (!empty($this->question->formoptions->mustbeusable)) {
                $contexts = $this->contexts->having_add_and_use();
            } else {
                $contexts = $this->contexts->having_cap('moodle/question:add');
            }

            // Adding question.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'), array('contexts' => $contexts));
        } else if (!($this->question->formoptions->canmove || $this->question->formoptions->cansaveasnew)) {
            // Editing question with no permission to move from category.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'),
                            array('contexts' => array($this->categorycontext)));
            $mform->addElement('hidden', 'usecurrentcat', 1);
            $mform->setType('usecurrentcat', PARAM_BOOL);
            $mform->setConstant('usecurrentcat', 1);
        } else {
            // Editing question with permission to move from category or save as new q.
            $currentgrp = array();
            $currentgrp[0] = $mform->createElement('questioncategory', 'category', get_string('categorycurrent', 'question'),
                                                array('contexts' => array($this->categorycontext)));
            // Validate if the question is being duplicated.
            $beingcopied = false;
            if (isset($this->question->beingcopied)) {
                $beingcopied = $this->question->beingcopied;
            }
            if (($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew) && ($beingcopied)) {
                // Not move only form.
                $currentgrp[1] = $mform->createElement('checkbox', 'usecurrentcat', '',
                                                    get_string('categorycurrentuse', 'question'));
                $mform->setDefault('usecurrentcat', 1);
            }
            $currentgrp[0]->freeze();
            $currentgrp[0]->setPersistantFreeze(false);
            $mform->addGroup($currentgrp, 'currentgrp', get_string('categorycurrent', 'question'), null, false);

            if (($beingcopied)) {
                $mform->addElement('questioncategory', 'categorymoveto', get_string('categorymoveto', 'question'),
                                array('contexts' => array($this->categorycontext)));
                if ($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew) {
                    // Not move only form.
                    $mform->disabledIf('categorymoveto', 'usecurrentcat', 'checked');
                }
            }
        }

        if (class_exists('qbank_editquestion\\editquestion_helper') && !empty($this->question->id) && !$this->question->beingcopied) {
            // Add extra information from plugins when editing a question (e.g.: Authors, version control and usage).
            $functionname = 'edit_form_display';
            $questiondata = [];
            $plugins = get_plugin_list_with_function('qbank', $functionname);
            foreach ($plugins as $componentname => $plugin) {
                $element = new StdClass();
                $element->pluginhtml = component_callback($componentname, $functionname, [$this->question]);
                $questiondata['editelements'][] = $element;
            }
            $mform->addElement('static', 'versioninfo', get_string('versioninfo', 'qbank_editquestion'),
                            $PAGE->get_renderer('qbank_editquestion')->render_question_info($questiondata));
        }

        $mform->addElement('text', 'name', get_string('tasktitle', 'qtype_sc'), array('size' => 50, 'maxlength' => 255));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('float', 'defaultmark', get_string('maxpoints', 'qtype_sc'), array('size' => 7));
        $mform->setDefault('defaultmark', $this->get_default_value('defaultmark', 1));
        $mform->addRule('defaultmark', null, 'required', null, 'client');

        $mform->addElement('editor', 'questiontext', get_string('stem', 'qtype_sc'), array('rows' => 15), $this->editoroptions);
        $mform->setType('questiontext', PARAM_RAW);
        $mform->addRule('questiontext', null, 'required', null, 'client');
        $mform->setDefault('questiontext', array('text' => get_string('enterstemhere', 'qtype_sc')));

        if (class_exists('qbank_editquestion\\editquestion_helper')) {
            $mform->addElement('select', 'status', get_string('status', 'qbank_editquestion'),
                            \qbank_editquestion\editquestion_helper::get_question_status_list());
        }
        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'), array('rows' => 10),
                        $this->editoroptions);
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'question');

        $mform->addElement('text', 'idnumber', get_string('idnumber', 'question'), 'maxlength="100"  size="10"');
        $mform->addHelpButton('idnumber', 'idnumber', 'question');
        $mform->setType('idnumber', PARAM_RAW);

        // Any questiontype specific fields.
        $this->definition_inner($mform);
        $this->add_interactive_settings();

        if (core_tag_tag::is_enabled('core_question', 'question') &&
             \core\plugininfo\qbank::is_plugin_enabled('qbank_tagquestion')) {
            $this->add_tag_fields($mform);
        }

        if (!empty($this->customfieldpluginenabled) && $this->customfieldpluginenabled) {
            // Add custom fields to the form.
            $this->customfieldhandler = qbank_customfields\customfield\question_handler::create();
            $this->customfieldhandler->set_parent_context($this->categorycontext); // For question handler only.
            $this->customfieldhandler->instance_form_definition($mform, empty($this->question->id) ? 0 : $this->question->id);
        }

        $this->add_hidden_fields();

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);

        $mform->addElement('hidden', 'makecopy');
        $mform->setType('makecopy', PARAM_INT);

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'updatebutton', get_string('savechangesandcontinueediting', 'question'));
        if ($this->can_preview()) {
            if (class_exists('qbank_editquestion\\editquestion_helper')) {
                if (\core\plugininfo\qbank::is_plugin_enabled('qbank_previewquestion')) {
                    $previewlink = $PAGE->get_renderer('qbank_previewquestion')->question_preview_link($this->question->id,
                                                                                                    $this->context, true);
                    $buttonarray[] = $mform->createElement('static', 'previewlink', '', $previewlink);
                }
            } else {
                $previewlink = $PAGE->get_renderer('core_question')->question_preview_link($this->question->id, $this->context, true);
                $buttonarray[] = $mform->createElement('static', 'previewlink', '', $previewlink);
            }
        }

        $mform->addGroup($buttonarray, 'updatebuttonar', '', array(' '), false);
        $mform->closeHeaderBefore('updatebuttonar');

        $this->add_action_buttons(true, get_string('savechanges'));

        if ((!empty($this->question->id)) && (!($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew))) {
            $mform->hardFreezeAllVisibleExcept(array('categorymoveto', 'buttonar', 'currentgrp'));
        }
    }

    /**
     * Adds question-type specific form fields.
     *
     * @param object $mform
     *        the form being built.
     */
    protected function definition_inner($mform) {
        global $PAGE;

        $scconfig = get_config('qtype_sc');

        $this->editoroptions['changeformat'] = 1;
        $mform->addElement('select', 'answernumbering', get_string('answernumbering', 'qtype_sc'),
                        qtype_multichoice::get_numbering_styles());
        $mform->setDefault('answernumbering', 'answernumberingnone');

        if (isset($this->question->options->numberofrows) && $this->question->options->numberofrows > 0) {
            $this->numberofrows = $this->question->options->numberofrows;
        } else {
            $this->numberofrows = QTYPE_SC_NUMBER_OF_OPTIONS;
        }

        $this->lastnumberofrows = $this->numberofrows;

        $PAGE->requires->js_call_amd('qtype_sc/question_edit', 'init');

        // Add number of rows setting.
        $availableanumanswers = array(2 => 2, 3 => 3, 4 => 4, 5 => 5);
        $mform->addElement('select', 'numberofrows', get_string('numberofrows', 'qtype_sc'), $availableanumanswers, array());
        $mform->setDefault('numberofrows', $this->numberofrows);
        $mform->addHelpButton('numberofrows', 'numberofrows', 'qtype_sc');

        // Keep state of number of rows.
        $mform->addElement('hidden', 'lastnumberofrows');
        $mform->setType('lastnumberofrows', PARAM_INT);
        $mform->setDefault('lastnumberofrows', $this->lastnumberofrows);
        $mform->addElement('header', 'scoringmethodheader', get_string('scoringmethod', 'qtype_sc'));
        $mform->setExpanded('scoringmethodheader', true);

        // Add the scoring method radio buttons.
        $attributes = array();
        $scoringbuttons = array();
        $scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '', get_string('scoringsconezero', 'qtype_sc'),
                                                'sconezero', $attributes);
        $scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '', get_string('scoringaprime', 'qtype_sc'), 'aprime',
                                                $attributes);
        $scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '', get_string('scoringsubpoints', 'qtype_sc'),
                                                'subpoints', $attributes);

        $mform->addGroup($scoringbuttons, 'radiogroupscoring', get_string('scoringmethod', 'qtype_sc'), array(' <br/> '), false);
        $mform->addHelpButton('radiogroupscoring', 'scoringmethod', 'qtype_sc');
        $mform->setDefault('scoringmethod', 'sconezero');

        // Add the shuffleanswers checkbox.
        $mform->addElement('advcheckbox', 'shuffleanswers', get_string('shuffleanswers', 'qtype_sc'), null, null, array(0, 1));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_sc');
        $mform->setDefault('shuffleanswers', $scconfig->shuffleanswers);
        $mform->addElement('header', 'optionsandfeedbackheader', get_string('optionsandfeedback', 'qtype_sc'));
        $mform->setExpanded('optionsandfeedbackheader');

        // Add an option text editor, response radio buttons and a feedback editor for each option.
        for ($i = 1; $i <= 5; ++$i) {
            // Add the option editor.
            $mform->addElement('html', '<div class="optionbox" id="optionbox_response_' . $i . '">');
            $mform->addElement('html', '<div class="optionandresponses">');
            $mform->addElement('html', '<div class="optiontext">');
            $mform->addElement('html', '<label class="optiontitle">' . get_string('optionno', 'qtype_sc', $i) . '</label>');
            $mform->addElement('editor', 'option_' . $i, '', array('rows' => 2.5), $this->editoroptions);
            $mform->setDefault('option_' . $i, array('text' => get_string('enteroptionhere', 'qtype_sc')));
            $mform->setType('option_' . $i, PARAM_RAW);
            $mform->addElement('html', '</div>');

            // Add the radio for correctness.
            $mform->addElement('html', '<div class="responses">');
            $mform->addElement('radio', 'correctrow', '', get_string('correct', 'qtype_sc'), $i, $attributes);
            $mform->setDefault('correctrow', 0);
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '<br /><br />');

            // Add the feedback text editor in a new line.
            $mform->addElement('html', '<div class="feedbacktext">');
            $mform->addElement('html', '<label class="feedbacktitle">' . get_string('feedbackforoption', 'qtype_sc') . '</label>');
            $mform->addElement('editor', 'feedback_' . $i, '', array('rows' => 1.5, 'placeholder' => ''), $this->editoroptions);
            $mform->setType('feedback_' . $i, PARAM_RAW);
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '</div>');
        }
    }

    /**
     * Create the form elements required by one hint.
     *
     * @param bool $withclearwrong
     * @param bool $withshownumpartscorrect
     * @return array form field elements for one hint.
     */
    protected function get_hint_fields($withclearwrong = false, $withshownumpartscorrect = false) {
        list($repeated, $repeatedoptions) = parent::get_hint_fields(false, false);
        return array($repeated, $repeatedoptions);
    }

    /**
     * JS Call function
     */
    public function js_call() {
        global $PAGE;

        foreach (array_keys(get_string_manager()->load_component_strings('qtype_sc', current_language())) as $string) {
            $PAGE->requires->string_for_js($string, 'qtype_sc');
        }
    }

    /**
     * Perform an preprocessing needed on the data passed to set_data()
     * before it is used to initialise the form.
     *
     * @param object $question
     * @return object $question
     */
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_hints($question, true, true);

        if (isset($question->options)) {
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->scoringmethod = $question->options->scoringmethod;
            $question->rows = $question->options->rows;
            $question->numberofrows = $question->options->numberofrows;
            $question->answernumbering = $question->options->answernumbering;
            $question->correctrow = $question->options->correctrow;
        }

        if (isset($this->question->id)) {
            $key = 1;
            foreach ($question->options->rows as $row) {
                // Restore all images in the option text.
                $draftid = file_get_submitted_draft_itemid('option_' . $key);

                $question->{'option_' . $key}['text'] = file_prepare_draft_area($draftid, $this->context->id, 'qtype_sc',
                                                                            'optiontext', !empty($row->id) ? (int)$row->id : null,
                                                                            $this->fileoptions, $row->optiontext);

                $question->{'option_' . $key}['itemid'] = $draftid;

                // Now do the same for the feedback text.
                $draftid = file_get_submitted_draft_itemid('feedback_' . $key);

                $question->{'feedback_' . $key}['text'] = file_prepare_draft_area($draftid, $this->context->id, 'qtype_sc',
                                                                                'feedbacktext',
                                                                                !empty($row->id) ? (int)$row->id : null,
                                                                                $this->fileoptions, $row->optionfeedback);

                $question->{'feedback_' . $key}['itemid'] = $draftid;

                ++$key;
            }
        }

        $this->js_call();

        return $question;
    }

    /**
     * Cleans the optiontext from newlines, whitespaces, tabs and UTF-8 non breaking whitespaces.
     *
     * @param string $optiontext
     * @return string
     */
    private function clean_option_text($optiontext) {
        $optiontext = preg_replace("/[\r\n]+/i", '', $optiontext);
        $optiontext = preg_replace("/[\s\t]+/i", '', $optiontext);
        $optiontext = trim($optiontext, "\xC2\xA0\n");
        return $optiontext;
    }

    /**
     * Validates the form.
     *
     * @param array $data
     * @param array $files
     * @return array
     * @throws coding_exception
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        // If the questionname is empty.
        // or if the variable is missing.
        if (!property_exists((object)$data, 'name') || empty($data['name'])) {
            $errors['name'] = get_string('mustsupplyname', 'qtype_sc');
        }

        // If the variable numberofrows does not exist.
        // If the variable is empty or if the value of.
        // the variable exceeds 5 or is smaller than 2.
        if (!property_exists((object)$data, 'numberofrows') || empty($data['numberofrows']) || $data['numberofrows'] > 5 ||
             $data['numberofrows'] < 2) {
            $errors['numberofrows'] = get_string('mustsupplyvalue', 'qtype_sc');
        }

        // If one of the optiontexts if empty.
        if (property_exists((object)$data, 'numberofrows') || !empty($data['numberofrows'])) {
            for ($i = 1; $i <= $data['numberofrows']; ++$i) {
                if (property_exists((object)$data, 'option_' . $i)) {
                    $optiontext = $this->clean_option_text($data['option_' . $i]['text']);
                    if (empty($optiontext)) {
                        $errors['option_' . $i] = get_string('mustsupplyvalue', 'qtype_sc');
                    }
                }
            }
        }

        // If the correctrow value is missing.
        // or if the correctrow is greater than.
        // the number of rows (out of range).
        if (property_exists((object)$data, 'numberofrows') || !empty($data['numberofrows'])) {
            if (!property_exists((object)$data, 'correctrow') || !$data['correctrow'] || $data['correctrow'] > $data['numberofrows']) {
                $errors['correctrow'] = get_string('mustchoosecorrectoption', 'qtype_sc');
            }
        } 
        // Category.
        if (empty($data['category'])) {
            // User has provided an invalid category.
            $errors['category'] = get_string('required');
        }

        // Default mark.
        if (array_key_exists('defaultmark', $data) && $data['defaultmark'] < 0) {
            $errors['defaultmark'] = get_string('defaultmarkmustbepositive', 'question');
        }

        // Can only have one idnumber per category.
        if (strpos($data['category'], ',') !== false) {
            list($category, $categorycontextid) = explode(',', $data['category']);
        } else {
            $category = $data['category'];
        }
        if (isset($data['idnumber']) && ((string) $data['idnumber'] !== '')) {
            if (empty($data['usecurrentcat']) && !empty($data['categorymoveto'])) {
                $categoryinfo = $data['categorymoveto'];
            } else {
                $categoryinfo = $data['category'];
            }
            list($categoryid, $notused) = explode(',', $categoryinfo);
            $conditions = 'questioncategoryid = ? AND idnumber = ?';
            $params = [$categoryid, $data['idnumber']];
            if (!empty($this->question->id)) {
                // Get the question bank entry id to not check the idnumber for the same bank entry.
                $sql = "SELECT DISTINCT qbe.id
                          FROM {question_versions} qv
                          JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                         WHERE qv.questionid = ?";
                $bankentry = $DB->get_record_sql($sql, ['id' => $this->question->id]);
                $conditions .= ' AND id <> ?';
                $params[] = $bankentry->id;
            }

            if ($DB->record_exists_select('question_bank_entries', $conditions, $params)) {
                $errors['idnumber'] = get_string('idnumbertaken', 'error');
            }
        }

        if ($this->customfieldpluginenabled) {
            // Add the custom field validation.
            $errors  = array_merge($errors, $this->customfieldhandler->instance_form_validation($data, $files));
        }

        return $errors;
    }
}
