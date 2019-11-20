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
  * @package    qtype_sc
  * @author     Amr Hourani (amr.hourani@id.ethz.ch)
  * @author     Martin Hanusch (martin.hanusch@let.ethz.ch)
  * @author     Jürgen Zimmer (juergen.zimmer@edaktik.at)
  * @author     Andreas Hruska (andreas.hruska@edaktik.at)
  * @copyright  2018 ETHZ {@link http://ethz.ch/}
  * @copyright  2017 eDaktik GmbH {@link http://www.edaktik.at}
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Restore plugin class that provides the necessary information
 * needed to restore one sc qtype plugin.
 */
class restore_qtype_sc_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level.
     */
    protected function define_question_plugin_structure() {
        $result = array();

        // We used get_recommended_name() so this works.
        $elename = 'sc';
        $elepath = $this->get_pathfor('/sc');
        $result[] = new restore_path_element($elename, $elepath);

        // We used get_recommended_name() so this works.
        $elename = 'row';
        $elepath = $this->get_pathfor('/rows/row');
        $result[] = new restore_path_element($elename, $elepath);

        return $result;
    }

    /**
     * Process the qtype/sc element.
     */
    public function process_sc($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        // Detect if the question is created or mapped.
        $oldquestionid = $this->get_old_parentid('question');
        $newquestionid = $this->get_new_parentid('question');

        $questioncreated = (bool) $this->get_mappingid('question_created', $oldquestionid);

        // If the question has been created by restore, we need to create its
        // qtype_sc_options too.
        if ($questioncreated) {
            // Adjust some columns.
            $data->questionid = $newquestionid;
            // Insert record.
            $newitemid = $DB->insert_record('qtype_sc_options', $data);
            // Create mapping (needed for decoding links).
            $this->set_mapping('qtype_sc_options', $oldid, $newitemid);
        }
    }

    /**
     * Detect if the question is created or mapped.
     *
     * @return bool
     */
    protected function is_question_created() {
        $oldquestionid = $this->get_old_parentid('question');
        $questioncreated = (bool) $this->get_mappingid('question_created', $oldquestionid);

        return $questioncreated;
    }


    /**
     * Process the qtype/sc/rows/row element.
     */
    public function process_row($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $oldquestionid = $this->get_old_parentid('question');
        $newquestionid = $this->get_new_parentid('question');

        if ($this->is_question_created()) {
            $data->questionid = $newquestionid;
            $newitemid = $DB->insert_record('qtype_sc_rows', $data);
        } else {
            $originalrecords = $DB->get_records('qtype_sc_rows', array('questionid' => $newquestionid));
            foreach ($originalrecords as $record) {
                if ($data->number == $record->number) {
                    $newitemid = $record->id;
                }
            }
        }
        if (!$newitemid) {
            $info = new stdClass();
            $info->filequestionid = $oldquestionid;
            $info->dbquestionid = $newquestionid;
            $info->answer = $data->optiontext;
            throw new restore_step_exception('error_question_answers_missing_in_db', $info);
        } else {
            $this->set_mapping('qtype_sc_rows', $oldid, $newitemid);
        }
    }

    public function recode_response($questionid, $sequencenumber, array $response) {
        if (array_key_exists('_order', $response)) {
            $response['_order'] = $this->recode_option_order($response['_order']);
        }
        return $response;
    }

    /**
     * Recode the option order as stored in the response.
     *
     * @param string $order the original order.
     *
     * @return string the recoded order.
     */
    protected function recode_option_order($order) {
        $neworder = array();
        foreach (explode(',', $order) as $id) {
            if ($newid = $this->get_mappingid('qtype_sc_rows', $id)) {
                $neworder[] = $newid;
            }
        }
        return implode(',', $neworder);
    }

    /**
     * Return the contents of this qtype to be processed by the links decoder.
     */
    public static function define_decode_contents() {
        $contents = array();

        $fields = array('optiontext', 'optionfeedback');
        $contents[] = new restore_decode_content('qtype_sc_rows', $fields, 'qtype_sc_rows');

        return $contents;
    }
}
