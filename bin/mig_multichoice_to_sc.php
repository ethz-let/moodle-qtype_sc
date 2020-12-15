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

@set_time_limit(0);
@ini_set('memory_limit', '3072M');

// General Page Setup.
echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' .
    '<style>body{font-family: "Courier New", Courier, monospace; font-size: 12px; background: #ebebeb; color: #5a5a5a;}</style>' .
    '</head>';
echo "=========================================================================================<br/>\n";
echo "M I G R A T I O N ::  Multichoice to SC<br/>\n";
echo "=========================================================================================<br/>\n";

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

// Parameter Information.
echo "-----------------------------------------------------------------------------------------<br/><br/>\n\n";
echo ($dryrun == 1 ? "[<font style='color:#228d00;'>ON </font>] " : "[<font color='red'>OFF</font>] ") .
    "Dryrun: " . ($dryrun == 1 ? "NO changes to the database will be made!" : "Migration is being processed") . "<br/>\n";
echo ($includesubcategories == 1 ? "[<font style='color:#228d00;'>ON </font>] " : "[<font color='red'>OFF</font>] ") .
    "IncludeSubcategories<br/><br/>\n\n";
echo "-----------------------------------------------------------------------------------------<br/>\n";
echo "=========================================================================================<br/>\n";

$counter = 0;
$notmigrated = array();

foreach ($questions as $question) {
    set_time_limit(60);

    $oldquestionid = $question->id;
    $oldquestionname = $question->name;

    // Retrieve the answers.
    $answers = $DB->get_records('question_answers', array('question' => $oldquestionid), ' id ASC ');

    // If the MC question has got too manu options or responses, we ignore it.
    if (!in_array(count($answers), [2, 3, 4, 5])) {
        echo "&nbsp;&nbsp; Question has the wrong number of options! Question is not migrated.<br/>\n";
        $notmigrated[] = $question;
        continue;
    }

    // Pretesting files

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

    foreach ($answers as $answer) {

        // Test images in the answer text.
        $testresult = test_files(
            $fs,
            $contextid,
            $answer->id,
            trim($answer->answer),
            'answer',
            'question'
        );

        $success = $success && $testresult[0];
        $status .= $testresult[1];

        // Test images in the answer feedback.
        $testresult = test_files(
            $fs,
            $contextid,
            $answer->id,
            trim($answer->feedback),
            'answerfeedback',
            'question'
        );

        $success = $success && $testresult[0];
        $status .= $testresult[1];
    }


    if ($dryrun == 0 && $success) {
        try {

            unset($transaction);
            $transaction = $DB->start_delegated_transaction();

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
                'generalfeedback'
            );

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
                $scrow->optiontext = trim($answer->answer);
                $scrow->optiontextformat = $answer->answerformat;
                $scrow->optionfeedback = $answer->feedback;
                $scrow->optionfeedbackformat = $answer->feedbackformat;
                $scrow->id = $DB->insert_record('qtype_sc_rows', $scrow);
                $rows[] = $scrow;

                // Copy images in the answer text.
                copy_files(
                    $fs,
                    $contextid,
                    $answer->id,
                    $scrow->id,
                    trim($answer->answer),
                    'answer',
                    'qtype_sc',
                    'optiontext'
                );

                // Copy images in the answer feedback.
                copy_files(
                    $fs,
                    $contextid,
                    $answer->id,
                    $scrow->id,
                    trim($answer->feedback),
                    'answerfeedback',
                    'qtype_sc',
                    'feedbacktext'
                );

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

            // Copy tags.
            $tags = $DB->get_records_sql(
                "SELECT * FROM {tag_instance} WHERE itemid = :itemid",
                array('itemid' => $oldquestionid));

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

function get_image_filenames($text) {
    $result = array();
    $strings = preg_split("/<img|<source/i", $text);
    foreach ($strings as $string) {
        $matches = array();
        if (preg_match('!@@PLUGINFILE@@/(.+)!u', $string, $matches) && count($matches) > 0) {
            $filename = mb_substr($matches[1], 0, mb_strpos($matches[1], '"'));
            $filename = urldecode($filename);
            $result[] = $filename;
        }
    }
    return $result;
}

// Copying files from one question to another.
function copy_files($fs, $contextid, $oldid, $newid, $text, $type, $component, $filearea) {
    $filenames = get_image_filenames($text);
    foreach ($filenames as $filename) {

        $parsed_filename_url = parse_url($filename)["path"];
        if (isset($parsed_filename_url)) {
            $filename = $parsed_filename_url;
        }

        $file = $fs->get_file($contextid, 'question', $type, $oldid, '/', $filename);
        if ($file) {
            $newfile = new stdClass();
            $newfile->component = $component;
            $newfile->filearea = $filearea;
            $newfile->itemid = $newid;
            if (!$fs->get_file($contextid, $newfile->component, $newfile->filearea, $newfile->itemid, '/', $filename)) {
                $fs->create_file_from_storedfile($newfile, $file);
            }
        }
    }
}

// Testing files
function test_files($fs, $contextid, $oldid, $text, $type, $olcdomponent) {

    $success = 1;
    $message = "";

    $filenames = get_image_filenames($text);
    foreach ($filenames as $filename) {

        $parsed_filename_url = parse_url($filename)["path"];
        if (isset($parsed_filename_url)) {
            $filename = $parsed_filename_url;
        }

        $file = $fs->get_file($contextid, $olcdomponent, $type, $oldid, '/', $filename);
        if (!$file) {
            $success = 0;
            $message .= "- File <font color='red'>$filename</font> not found in <u>$type</u><br>";
        }
    }

    return ["0" => $success, "1" => $message];
}