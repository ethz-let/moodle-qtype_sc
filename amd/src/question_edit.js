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

define(['jquery'], function($) {
    // Setting up an Event listener.
    $('select[id="id_numberofrows"]').change(function () {
        numberofrowschanged();
    });

    function numberofrowschanged() {
        // Setting up some variables.
        var numberofrows_cur = parseInt($('select[id="id_numberofrows"]').val());
        var numberofrows_pre = parseInt($('input[name="lastnumberofrows"]').val());
        var numberofrows_max = 5;
        var permission = true;
        // Check if the number of rows has decreased.
        // If true: Show prompts.
        if (numberofrows_pre > numberofrows_cur) {
            var differenceinrows = numberofrows_pre - numberofrows_cur;
            if (confirm(M.util.get_string('deleterawswarning', 'qtype_sc', differenceinrows))) {
                permission = true;
            } else {
                permission = false;
                if (numberofrows_pre > numberofrows_max) {
                    numberofrows_pre = numberofrows_max;
                    alert(M.util.get_string('mustdeleteextrarows', 'qtype_sc', differenceinrows));
                }
                $('select[id="id_numberofrows"]').val(numberofrows_pre);
            }
        }
        // Proceed with updating the DOM by.
        // Adding or deleting the defined amount of optionboxes.
        if (permission) {
            // Backup the current numberofrows value.
            $('input[name="lastnumberofrows"]').val(numberofrows_cur);
            // Update the visibility of all optionboxes.
            $('[id^="optionbox_response_"]').hide();
            var numberofrows_rendered = numberofrows_cur < numberofrows_max ? numberofrows_cur : numberofrows_max;
            for (var i = 1; i <= numberofrows_rendered; i++) {
                $('#optionbox_response_' + i).show();
            }
            // Reset the correctness radio button if the.
            // previously ticked button is out of range now.
            // This will also be checked serverside but improves.
            // usability.
            if (numberofrows_pre > numberofrows_cur) {
                var corretrow = 1;
                for (var i = 0; i <= numberofrows_max; i++) {
                    if ($('#id_correctrow_' + i).is(':checked')) {
                        corretrow = i;
                    }
                }
                if (corretrow > numberofrows_cur) {
                    alert(M.util.get_string('correctvaluereset', 'qtype_sc'));
                    $('#id_correctrow_1').prop('checked',true);
                }
            }
        }
    }
    return {
        init: function() {
            // Initial setup.
            numberofrowschanged();
        }
    };
});