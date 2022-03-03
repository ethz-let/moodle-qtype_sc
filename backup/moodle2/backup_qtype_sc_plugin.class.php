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
 * Backup code for the qtype_sc plugin.
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
 * Provides the backup for qtype_sc questions.
 */
class backup_qtype_sc_plugin extends backup_qtype_plugin {

    /**
     * Returns the qtype information to attach to the question element.
     */
    protected function define_question_plugin_structure() {
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '../../qtype', 'sc');

        // Create one standard named plugin element (the visible container).
        $name = $this->get_recommended_name();
        $pluginwrapper = new backup_nested_element($name);

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        // Now create the qtype own structures.
        $sc = new backup_nested_element('sc', array('id'),
                array('scoringmethod', 'shuffleanswers', 'answernumbering', 'numberofrows', 'correctrow'));

        $rows = new backup_nested_element('rows');
        $row = new backup_nested_element('row', array('id'),
                array('number', 'optiontext', 'optiontextformat', 'optionfeedback', 'optionfeedbackformat'));

        // Now the qtype tree.
        $rows->add_child($row);
        $pluginwrapper->add_child($sc);
        $pluginwrapper->add_child($rows);

        // Set sources to populate the data.
        $sc->set_source_table('qtype_sc_options', array('questionid' => backup::VAR_PARENTID));
        $row->set_source_table('qtype_sc_rows', array('questionid' => backup::VAR_PARENTID));

        return $plugin;
    }

    /**
     * Returns one array with filearea => mappingname elements for the qtype.
     * Used by {@see get_components_and_fileareas} to know about all the qtype
     * files to be processed both in backup and restore.
     */
    public static function get_qtype_fileareas() {
        return array('optiontext' => 'qtype_sc_rows', 'feedbacktext' => 'qtype_sc_rows');
    }
}
