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

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/lib/questionlib.php');
require_once($CFG->dirroot . '/question/type/sc/lib.php');
require_once('migration_lib.php');

$courseid = optional_param('courseid', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$includesubcategories = optional_param('includesubcategories', 0, PARAM_INT);
$all = optional_param('all', 0, PARAM_INT);
$dryrun = optional_param('dryrun', 1, PARAM_INT);

require_login();

if (!is_siteadmin()) {
    echo 'You are not a Website Administrator!';
    die();
}


$starttime = time();
$fs = get_file_storage();

$sql = "SELECT q.*
        FROM {question} q,
             {qtype_multichoice_options} mco
        WHERE q.qtype = 'multichoice'
          AND q.parent = 0
          AND mco.questionid = q.id
          AND mco.single = 1
        ";
$params = array();

if (!$all && (!($courseid > 0 || $categoryid > 0))) {
    echo "<br/><font color='red'>You should specify either the 'courseid'
    or the 'categoryid' parameter! Or set the parameter 'all' to 1.</font><br/>\n";
    echo "I'm not doing anything without restrictions!\n";
    die();
}

if ($courseid > 0) {
    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
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
$sql .= " ORDER BY category ASC";
$questions = $DB->get_records_sql($sql, $params);

echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>';
echo 'Migrating ' . count($questions) . " Multichoice questions... <br/>\n";

if ($dryrun) {
    echo "***********************************************************<br/>\n";
    echo "*   Dry run: No changes to the database will be made! *<br/>\n";
    echo "***********************************************************<br/>\n";
}

$counter = 0;
$notmigrated = array();
foreach ($questions as $question) {
    set_time_limit(60);

    $transaction = $DB->start_delegated_transaction();

    $oldquestionid = $question->id;

    // Retrieve the answers.
    $answers = $DB->get_records('question_answers', array('question' => $oldquestionid));
    sort($answers);

    if ($dryrun) {
        echo '--------------------------------------------------------------------------------' .
            "<br/>\n";
        if (!in_array(count($answers), [2, 3, 4, 5])) {
            echo 'Question: "' . $question->name . '" with ID ' . $question->id .
                " would NOT be migrated! It has the wrong number of options!<br/>\n";
            $notmigrated[] = $question;
        } else {
            echo 'Question: "' . $question->name . '" with ID ' . $question->id .
                " would be migrated! It has " . count($answers) . " answers.<br/>\n";
            $counter++;
        }
        echo shorten_text($question->questiontext, 100, false, '...');
        $transaction->allow_commit();
        continue;
    } else {
        echo '--------------------------------------------------------------------------------' .
            "<br/>\n";
        echo 'Multichoice Question: "' . $question->name . "\"<br/>\n";
    }

    // If the MC question has got too manu options or responses, we ignore it.
    if (!in_array(count($answers), [2, 3, 4, 5])) {
        echo "&nbsp;&nbsp; Question has the wrong number of options! Question is not migrated.<br/>\n";
        $notmigrated[] = $question;
        $transaction->allow_commit();
        continue;
    }

    // Create a new sc question in the same category.
    unset($question->id);
    $question->parent = 0;
    $question->qtype = 'sc';
    $question->name = $question->name . ' (SC)';
    $question->stamp = make_unique_id_code();
    $question->version = make_unique_id_code();
    $question->timecreated = time();
    $question->timemodified = time();
    $question->modifiedby = $USER->id;
    $question->createdby = $USER->id;
    // Get the new question ID.
    $question->id = $DB->insert_record('question', $question);
    $counter++;
    echo 'New SC Question: "' . $question->name . '" with ID ' . $question->id . "<br/>\n";


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

    $correctrow = null;
    $optionnumber = 1;
    $rowcount = 1;
    $rows = array();
    foreach ($answers as $answer) {
        if ($answer->fraction == 1.0 && !$correctrow) {
            $correctrow = $rowcount;
        }
        // Create a new sc row.
        $scrow = new stdClass();
        $scrow->questionid = $question->id;
        $scrow->number = $rowcount++;
        $scrow->optiontext = $answer->answer;
        $scrow->optiontextformat = $answer->answerformat;
        $scrow->optionfeedback = $answer->feedback;
        $scrow->optionfeedbackformat = $answer->feedbackformat;
        $scrow->id = $DB->insert_record('qtype_sc_rows', $scrow);
        $rows[] = $scrow;

        // Copy images in the answer text.
        $filenames = mig_sc_get_image_filenames($answer->answer);
        foreach ($filenames as $filename) {
            $file = $fs->get_file($contextid, 'question', 'answer', $answer->id, '/', $filename);
            if ($file) {
                $newfile = new stdClass();
                $newfile->component = 'qtype_sc';
                $newfile->filearea = 'optiontext';
                $newfile->itemid = $scrow->id;
                if (!$fs->get_file($contextid, $newfile->component, $newfile->filearea, $newfile->itemid, '/', $filename)) {
                    $fs->create_file_from_storedfile($newfile, $file);
                }
            }
        }

        // Copy images in the answer feedback.
        $filenames = mig_sc_get_image_filenames($answer->feedback);
        foreach ($filenames as $filename) {
            $file = $fs->get_file($contextid, 'question', 'answerfeedback', $answer->id, '/', $filename);

            $newfile = new stdClass();
            $newfile->component = 'qtype_sc';
            $newfile->filearea = 'feedbacktext';
            $newfile->itemid = $scrow->id;
            if (!$fs->get_file($contextid, $newfile->component, $newfile->filearea, $newfile->itemid, '/', $filename)) {
                $fs->create_file_from_storedfile($newfile, $file);
            }
        }
    }

    $mcoptions = $DB->get_record('qtype_multichoice_options', array('questionid' => $oldquestionid));

    // SC question options.
    $scoptions = new stdClass();
    $scoptions->questionid = $question->id;
    $scoptions->scoringmethod = 'sconezero';
    $scoptions->shuffleanswers = $mcoptions->shuffleanswers;
    $scoptions->answernumbering = $mcoptions->answernumbering;
    $scoptions->numberofrows = count($answers);
    if ($correctrow) {
        $scoptions->correctrow = $correctrow;
    }
    $scoptions->id = $DB->insert_record('qtype_sc_options', $scoptions);


    $transaction->allow_commit();
}
echo '--------------------------------------------------------------------------------' . "<br/>\n";

$endtime = time();
$used = $endtime - $starttime;
$mins = round($used / 60);
$used = ($used - ($mins * 60));

echo "<br/>\n Done with " . $counter . " new questions \n<br/>";
echo 'Time needed: ' . $mins . ' mins and ' . $used . " secs.<br/>\n<br/>\n";

echo "Questions that were not migrated:<br/>\n";
echo " ID &nbsp;&nbsp; ,  Question Name<br/>\n";
echo "----------------------------------------<br/>\n";
foreach ($notmigrated as $question) {
    echo $question->id . ' : ' . $question->name . "<br/>\n";
}
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