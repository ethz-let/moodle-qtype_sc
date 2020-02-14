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

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/lib/questionlib.php');
require_once($CFG->dirroot . '/question/type/sc/lib.php');
require_once('migration_lib.php');

$courseid = optional_param('courseid', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$includesubcategories = optional_param('includesubcategories', 0, PARAM_INT);
$all = optional_param('all', 0, PARAM_INT);
$dryrun = optional_param('dryrun', 0, PARAM_INT);

require_login();

if (!is_siteadmin()) {
    echo 'You are not a Website Administrator!';
    die();
}

$starttime = time();
$fs = get_file_storage();

$sql = "SELECT q.*
          FROM {question} q
         WHERE q.qtype = 'sc'
           AND q.parent = 0
        ";
$params = array();

if (!$all && (!($courseid > 0 || $categoryid > 0))) {
    echo "<br/><font color='red'>You should specify either the 'courseid'
    or the 'categoryid' parameter! Or set the parameter 'all' to 1.</font><br/>\n";
    echo "I'm not doing anything without restrictions!\n";
    die();
}

if ($courseid > 0) {
    if (!$course = $DB->get_record('course', array('id' => $courseid
    ))) {
        echo "<br/><font color='red'>Course with ID $courseid  not found...!</font><br/>\n";
        die();
    }
    $coursecontext = context_course::instance($courseid);
    $contextid = $coursecontext->id;

    $categories = $DB->get_records('question_categories',
               array('contextid' => $coursecontext->id));
    $catids = array_keys($categories);

    if (!empty($catids)) {
        list($csql, $params) = $DB->get_in_or_equal($catids, SQL_PARAMS_NAMED, 'catid');
        $sql .= " AND q.category $csql ";
    } else {
        echo "<br/><font color='red'>No question categories for course found... weird!</font><br/>\n";
        echo "I'm not doing anything without restrictions!\n";
        die();
    }
}

if ($categoryid > 0) {
    if ($category = $DB->get_record('question_categories', array('id' => $categoryid))) {

        echo 'Migration restricted to category "' . $category->name . "\".<br/>\n";

        $catids = [];

        if ($includesubcategories == 1) {
            $subcategories = get_subcategories($categoryid);
            $catids = array_column($subcategories, 'id');
            $catnames = array_column($subcategories, 'name');

            echo "Also migrating subcategories:<br>\n";
            echo implode(",<br>", $catnames) . "<br>\n\n";
        }

        $catids['catid' . count($catids)] = $categoryid;
        list($csql, $params) = $DB->get_in_or_equal($catids, SQL_PARAMS_NAMED, 'catid');

        $sql .= " AND q.category $csql ";

        $contextid = $DB->get_field('question_categories', 'contextid', array('id' => $categoryid));
    } else {
        echo "<br/><font color='red'>Question category with ID $categoryid  not found...!</font><br/>\n";
        echo "I'm not doing anything without restrictions!\n";
        die();
    }
}

$questions = $DB->get_records_sql($sql, $params);

echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>';
echo 'Migrating ' . count($questions) . " Multichoice questions... <br/>\n";

if ($dryrun) {
    echo "***********************************************************<br/>\n";
    echo "*   Dry run: No changes to the database will be made! *<br/>\n";
    echo "***********************************************************<br/>\n";
}

$counter = 0;
foreach ($questions as $question) {
    set_time_limit(60);

    $transaction = $DB->start_delegated_transaction();

    $oldquestionid = $question->id;

    // Retrieve the answers.
    $rows = $DB->get_records('qtype_sc_rows', array('questionid' => $oldquestionid));

    if ($dryrun) {
        echo '--------------------------------------------------------------------------------' .
                 "<br/>\n";
        echo 'SC Question: "' . $question->name . '" with ID ' . $question->id .
                     " would be migrated! It has " . count($rows) . " answers.<br/>\n";
        $counter++;
        echo shorten_text($question->questiontext, 100, false, '...');
        $transaction->allow_commit();
        continue;
    } else {
        echo '--------------------------------------------------------------------------------' .
                 "<br/>\n";
        echo 'SC Question: "' . $question->name . "\"<br/>\n";
    }

    // Create a new MC question in the same category.
    unset($question->id);
    $question->qtype = 'multichoice';
    $question->name = $question->name . ' (MC)';
    $question->timecreated = time();
    $question->timemodified = time();
    $question->modifiedby = $USER->id;
    $question->createdby = $USER->id;
    // Get the new question ID.
    $question->id = $DB->insert_record('question', $question);
    $counter++;
    echo 'New MC Question: "' . $question->name . '" with ID ' . $question->id . "<br/>\n";

    // Copy images in the questiontext to new itemid.
    $filenames = mig_sc_get_image_filenames($question->questiontext);
    foreach ($filenames as $filename) {
        $file = $fs->get_file($contextid, 'question', 'questiontext', $oldquestionid, '/', $filename);

        $newfile = new stdClass();
        $newfile->itemid = $question->id;
        if (!$fs->get_file($contextid, 'question', 'questiontext', $question->id, '/', $filename)) {
            $fs->create_file_from_storedfile($newfile, $file);
        }
    }

    // Copy images in the general feedback to new itemid.
    $filenames = mig_sc_get_image_filenames($question->generalfeedback);
    foreach ($filenames as $filename) {
        $file = $fs->get_file($contextid, 'question', 'generalfeedback', $oldquestionid, '/', $filename);

        $newfile = new stdClass();
        $newfile->itemid = $question->id;
        if (!$fs->get_file($contextid, 'question', 'generalfeedback', $question->id, '/', $filename)) {
            $fs->create_file_from_storedfile($newfile, $file);
        }
    }

    $scoptions = $DB->get_record('qtype_sc_options', array('questionid' => $oldquestionid));

    $correctrow = null;
    $optionnumber = 1;
    $rowcount = 1;
    $answers = array();
    foreach ($rows as $row) {
        // Create a new MC answer.
        $answer = new stdClass();
        $answer->question = $question->id;
        $answer->answer = $row->optiontext;
        $answer->answerformat = $row->optiontextformat;
        $answer->feedback = $row->optionfeedback;
        $answer->feedbackformat = $row->optionfeedbackformat;

        if ($row->number == $scoptions->correctrow) {
            $answer->fraction = 1.0;
        } else {
            $answer->fraction = 0.0;
        }
        $answer->id = $DB->insert_record('question_answers', $answer);
        $answers[] = $answer;
        // Copy images in the optiontext to the new answer.
        $filenames = mig_sc_get_image_filenames($row->optiontext);
        foreach ($filenames as $filename) {
            $file = $fs->get_file($contextid, 'qtype_sc', 'optiontext', $row->id, '/', $filename);
            if ($file) {
                $newfile = new stdClass();
                $newfile->component = 'question';
                $newfile->filearea = 'answer';
                $newfile->itemid = $answer->id;
                if (!$fs->get_file($contextid, $newfile->component, $newfile->filearea, $newfile->itemid, '/', $filename)) {
                    $fs->create_file_from_storedfile($newfile, $file);
                }
            }
        }

        // Copy images in the answer feedback.
        $filenames = mig_sc_get_image_filenames($row->optionfeedback);
        foreach ($filenames as $filename) {
            $file = $fs->get_file($contextid, 'qtype_sc', 'feedbacktext', $row->id, '/', $filename);

            if ($file) {
                $newfile = new stdClass();
                $newfile->component = 'question';
                $newfile->filearea = 'answerfeedback';
                $newfile->itemid = $answer->id;
                if (!$fs->get_file($contextid, $newfile->component,  $newfile->filearea, $newfile->itemid, '/', $filename)) {
                    $fs->create_file_from_storedfile($newfile, $file);
                }
            }
        }
    }

    // MC question options.
    $mcoptions = new stdClass();
    $mcoptions->questionid = $question->id;
    $mcoptions->layout = 0;
    $mcoptions->single = 1;
    $mcoptions->shuffleanswers = $scoptions->shuffleanswers;
    $mcoptions->answernumbering = $scoptions->answernumbering;
    $mcoptions->correctfeedback = '';
    $mcoptions->correctfeedbackformat = FORMAT_HTML;
    $mcoptions->partiallycorrectfeedback = '';
    $mcoptions->partiallycorrectfeedbackformat = FORMAT_HTML;
    $mcoptions->incorrectfeedback = '';
    $mcoptions->incorrectfeedbackformat = FORMAT_HTML;
    $mcoptions->shownumcorrect = 0;
    $mcoptions->id = $DB->insert_record('qtype_multichoice_options', $mcoptions);

    $transaction->allow_commit();
}
echo '--------------------------------------------------------------------------------' . "<br/>\n";

$endtime = time();
$used = $endtime - $starttime;
$mins = round($used / 60);
$used = ($used - ($mins * 60));

echo "<br/>\n Done with " . $counter . " new questions \n<br/>";
echo 'Time needed: ' . $mins . ' mins and ' . $used . " secs.<br/>\n<br/>\n";

die();

// Getting the subcategories of a certain category.
function get_subcategories($categoryid) {
    global $DB;

    $subcategories = $DB->get_records('question_categories', array('parent' => $categoryid), 'id');

    foreach ($subcategories as $subcategory) {
        $subcategories = array_merge($subcategories, get_subcategories($subcategory->id));
    }

    return $subcategories;
}
