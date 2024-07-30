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
 * Migration script for migration to qtype_multichoice
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

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/lib/questionlib.php');
require_once($CFG->dirroot . '/question/type/sc/lib.php');

$courseid = optional_param('courseid', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$includesubcategories = optional_param('includesubcategories', 0, PARAM_INT);
$all = optional_param('all', 0, PARAM_INT);
$dryrun = optional_param('dryrun', 1, PARAM_INT);

@set_time_limit(0);
@ini_set('memory_limit', '3072M');

// General Page Setup.
echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' .
    '<style>body{font-family: "Courier New", Courier, monospace; font-size: 12px; background: #ebebeb; color: #5a5a5a;}</style>' .
    '</head>';
echo "=========================================================================================<br/>\n";
echo "M I G R A T I O N ::  Multichoice to SingleChoice (ETH) to Multichoice<br/>\n";
echo "=========================================================================================<br/>\n";

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
$params = [];

if (!$all && (!($courseid > 0 || $categoryid > 0))) {
    echo "<br/><font color='red'>You should specify either the 'courseid'
    or the 'categoryid' parameter! Or set the parameter 'all' to 1.</font><br/>\n";
    echo "I'm not doing anything without restrictions!\n";
    die();
}

if ($courseid > 0) {
    if (!$course = $DB->get_record('course', ['id' => $courseid,
    ])) {
        echo "<br/><font color='red'>Course with ID $courseid  not found...!</font><br/>\n";
        die();
    }
    $coursecontext = context_course::instance($courseid);
    $contextid = $coursecontext->id;

    $categories = $DB->get_records('question_categories',
               ['contextid' => $coursecontext->id]);
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
    if ($category = $DB->get_record('question_categories', ['id' => $categoryid])) {

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

        $contextid = $DB->get_field('question_categories', 'contextid', ['id' => $categoryid]);
    } else {
        echo "<br/><font color='red'>Question category with ID $categoryid  not found...!</font><br/>\n";
        echo "I'm not doing anything without restrictions!\n";
        die();
    }
}

$questions = $DB->get_records_sql($sql, $params);

echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>';
echo 'Migrating ' . count($questions) . " SingleChoice (ETH) questions... <br/>\n";

// Parameter Information.
echo "-----------------------------------------------------------------------------------------<br/><br/>\n\n";
echo ($dryrun == 1 ? "[<font style='color:#228d00;'>ON </font>] " : "[<font color='red'>OFF</font>] ") .
    "Dryrun: " . ($dryrun == 1 ? "NO changes to the database will be made!" : "Migration is being processed") . "<br/>\n";
echo ($includesubcategories == 1 ? "[<font style='color:#228d00;'>ON </font>] " : "[<font color='red'>OFF</font>] ") .
    "IncludeSubcategories<br/><br/>\n\n";
echo "-----------------------------------------------------------------------------------------<br/>\n";
echo "=========================================================================================<br/>\n";

$counter = 0;
$notmigrated = [];

foreach ($questions as $question) {
    set_time_limit(60);

    $oldquestionid = $question->id;
    $oldquestionname = $question->name;

    // Retrieve the answers.
    $rows = $DB->get_records('qtype_sc_rows', ['questionid' => $oldquestionid], ' id ASC ');

    // Get contextid from question category.
    $contextid = $DB->get_field('question_categories', 'contextid', ['id' => $question->category]);

    if (!isset($contextid) || $contextid == false) {
        echo "<br/>[<font color='red'>ERR</font>] No context id found for this question.";
        continue;
    }

    // Pretesting files.

    $success = 1;
    $status = "";

    // Test images in the questiontext to new itemid.
    $testresult = test_files(
        $fs,
        $contextid,
        $oldquestionid,
        $question->questiontext,
        'questiontext',
        'question'
    );

    $success = $success && $testresult[0];
    $status .= $testresult[1];

    // Test images in the general feedback to new itemid.
    $testresult = test_files(
        $fs,
        $contextid,
        $oldquestionid,
        $question->generalfeedback,
        'generalfeedback',
        'question'
    );

    $success = $success && $testresult[0];
    $status .= $testresult[1];

    foreach ($rows as $row) {

        // Test images in the optiontext to the new answer.
        $testresult = test_files(
            $fs,
            $contextid,
            $row->id,
            $row->optiontext,
            'optiontext',
            'qtype_sc'
        );

        $success = $success && $testresult[0];
        $status .= $testresult[1];

        // Test images in the answer feedback.
        $testresult = test_files(
            $fs,
            $contextid,
            $row->id,
            $row->optionfeedback,
            'feedbacktext',
            'qtype_sc'
        );

        $success = $success && $testresult[0];
        $status .= $testresult[1];
    }


    if ($dryrun == 0 && $success) {
        try {

            unset($transaction);
            $transaction = $DB->start_delegated_transaction();

            // Create a new MC question in the same category.
            unset($question->id);
            $question->parent = 0;
            $question->qtype = 'multichoice';
            $question->name = substr($question->name . " (MC)", 0, 255);
            $question->stamp = make_unique_id_code();
            $question->version = make_unique_id_code();
            $question->timecreated = time();
            $question->timemodified = time();
            $question->modifiedby = $USER->id;
            $question->createdby = $USER->id;
            $question->idnumber = null;
            $question->id = $DB->insert_record('question', $question);
            $counter++;

            // Copy images in the questiontext to new itemid.
            copy_files(
                $fs,
                $contextid,
                $oldquestionid,
                $question->id,
                $question->questiontext,
                'questiontext',
                'question',
                'question',
                'questiontext'
            );

            // Copy images in the general feedback to new itemid.
            copy_files(
                $fs,
                $contextid,
                $oldquestionid,
                $question->id,
                $question->generalfeedback,
                'generalfeedback',
                'question',
                'question',
                'generalfeedback'
            );

            $scoptions = $DB->get_record('qtype_sc_options', ['questionid' => $oldquestionid]);

            $correctrow = null;
            $optionnumber = 1;
            $rowcount = 1;
            $answers = [];

            foreach ($rows as $row) {
                // Create a new MC answer.
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = trim($row->optiontext);
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
                copy_files(
                    $fs,
                    $contextid,
                    $row->id,
                    $answer->id,
                    $row->optiontext,
                    'optiontext',
                    'qtype_sc',
                    'question',
                    'answer'
                );

                // Copy images in the answer feedback.
                copy_files(
                    $fs,
                    $contextid,
                    $row->id,
                    $answer->id,
                    $row->optionfeedback,
                    'feedbacktext',
                    'qtype_sc',
                    'question',
                    'answerfeedback'
                );
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

            // Copy tags.
            $tags = $DB->get_records_sql(
                "SELECT * FROM {tag_instance} WHERE itemid = :itemid",
                ['itemid' => $oldquestionid]);

            foreach ($tags as $tag) {
                $entry = new stdClass();
                $entry->tagid = $tag->tagid;
                $entry->component = $tag->component;
                $entry->itemtype = $tag->itemtype;
                $entry->itemid = $question->id;
                $entry->contextid = $tag->contextid;
                $entry->tiuserid = $tag->tiuserid;
                $entry->ordering = $tag->ordering;
                $entry->timecreated = $tag->timecreated;
                $entry->timemodified = $tag->timemodified;
                $DB->insert_record('tag_instance', $entry);
            }

            $transaction->allow_commit();
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
    }

    // Output: Question Migration Success.
    echo $success ? '[<font style="color:#228d00;">OK </font>]' : '[<font color="red">ERR</font>]';
    echo ' - question <i>"' . $oldquestionname . '"</i> ' .
    '(ID: <a href="' . $CFG->wwwroot . '/question/preview.php?id=' . $oldquestionid .
    '" target="_blank">' . $oldquestionid . '</a>) ';
    if ($dryrun == 0) {
        echo ($success) ? ' > <i>"' . $question->name . '"</i> ' .
        '(ID: <a href="' . $CFG->wwwroot . '/question/preview.php?id=' . $question->id .
        '" target="_blank">' . $question->id . '</a>)' : '';
    }
    if ($dryrun == 1) {
        echo ($success) ? " is migratable" : " is <u>not</u> migratable";
    }

    echo "<br/>$status\n";

}
echo '--------------------------------------------------------------------------------' . "<br/>\n";

$endtime = time();
$used = $endtime - $starttime;
$mins = round($used / 60);
$used = ($used - ($mins * 60));

echo "<br/>\n Done with " . $counter . " new questions \n<br/>";
echo 'Time needed: ' . $mins . ' mins and ' . $used . " secs.<br/>\n<br/>\n";

die();

/**
 * Getting all subcategories of a given category.
 * @param int $categoryid
 * @return array $subcategories
 */
function get_subcategories($categoryid) {
    global $DB;

    $subcategories = $DB->get_records('question_categories', ['parent' => $categoryid], 'id');

    foreach ($subcategories as $subcategory) {
        $subcategories = array_merge($subcategories, get_subcategories($subcategory->id));
    }

    return $subcategories;
}

/**
 * Extract the image filenames out of a certain text, e.g questiontext and returning the results
 * @param string $text
 * @return array
 */
function get_image_filenames($text) {
    $result = [];
    $strings = preg_split("/<img|<source/i", $text);
    foreach ($strings as $string) {
        $matches = [];
        if (preg_match('!@@PLUGINFILE@@/(.+)!u', $string, $matches) && count($matches) > 0) {
            $filename = mb_substr($matches[1], 0, mb_strpos($matches[1], '"'));
            $filename = urldecode($filename);
            $result[] = $filename;
        }
    }
    return $result;
}

/**
 * Copy files from one question to another.
 * @param object $fs
 * @param int $contextid
 * @param int $oldid
 * @param int $newid
 * @param string $text
 * @param string $type
 * @param string $olcdomponent
 * @param string $newcomponent
 * @param string $filearea
 */
function copy_files($fs, $contextid, $oldid, $newid, $text, $type, $olcdomponent, $newcomponent, $filearea) {
    $filenames = get_image_filenames($text);
    foreach ($filenames as $filename) {

        $parsedfilenameurl = parse_url($filename)["path"];
        if (isset($parsedfilenameurl)) {
            $filename = $parsedfilenameurl;
        }

        $file = $fs->get_file($contextid, $olcdomponent, $type, $oldid, '/', $filename);
        if ($file) {
            $newfile = new stdClass();
            $newfile->component = $newcomponent;
            $newfile->filearea = $filearea;
            $newfile->itemid = $newid;
            if (!$fs->get_file($contextid, $newfile->component, $newfile->filearea, $newfile->itemid, '/', $filename)) {
                $fs->create_file_from_storedfile($newfile, $file);
            }
        }
    }
}

/**
 * Check if files are actually existent
 * @param object $fs
 * @param int $contextid
 * @param int $oldid
 * @param string $text
 * @param string $type
 * @param string $olcdomponent
 * @return array
 */
function test_files($fs, $contextid, $oldid, $text, $type, $olcdomponent) {

    $success = 1;
    $message = "";

    $filenames = get_image_filenames($text);
    foreach ($filenames as $filename) {

        $parsedfilenameurl = parse_url($filename)["path"];
        if (isset($parsedfilenameurl)) {
            $filename = $parsedfilenameurl;
        }

        $file = $fs->get_file($contextid, $olcdomponent, $type, $oldid, '/', $filename);
        if (!$file) {
            $success = 0;
            $message .= "- File <font color='red'>$filename</font> not found in <u>$type</u><br>";
        }
    }

    return ["0" => $success, "1" => $message];
}
