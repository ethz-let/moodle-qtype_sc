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
    $('select[id="id_numberofrows"]').change(function() {
        numberofrowschanged();
    });

    /**
     * Events handles when number of rows is changed.
     */
    function numberofrowschanged() {

        /* eslint-disable no-alert */
        var numberofrowsCur = parseInt($('select[id="id_numberofrows"]').val());
        var numberofrowsPre = parseInt($('input[name="lastnumberofrows"]').val());
        var numberofrowsMax = 5;
        var permission = true;

        // Step1: Check if the number of rows has decreased. If true: Show prompts.
        if (numberofrowsPre > numberofrowsCur) {
            var differenceinrows = numberofrowsPre - numberofrowsCur;

            if (confirm(M.util.get_string('deleterawswarning', 'qtype_sc', differenceinrows))) {
                permission = true;
            } else {
                permission = false;
                if (numberofrowsPre > numberofrowsMax) {

                    numberofrowsPre = numberofrowsMax;
                    alert(M.util.get_string('mustdeleteextrarows', 'qtype_sc', differenceinrows));
                }
                $('select[id="id_numberofrows"]').val(numberofrowsPre);
            }
        }

        // Step2: Update the DOM by adding or deleting the defined amount of optionboxes.
        if (permission) {
            $('input[name="lastnumberofrows"]').val(numberofrowsCur);
            $('[id^="optionbox_response_"]').hide();

            var numberofrowsRendered = numberofrowsCur < numberofrowsMax ? numberofrowsCur : numberofrowsMax;

            for (var i = 1; i <= numberofrowsRendered; i++) {
                $('#optionbox_response_' + i).show();
            }

            if (numberofrowsPre > numberofrowsCur) {
                var corretrow = 1;
                for (var j = 0; j <= numberofrowsMax; j++) {
                    if ($('#id_correctrow_' + j).is(':checked')) {
                        corretrow = j;
                    }
                }
                if (corretrow > numberofrowsCur) {
                    alert(M.util.get_string('correctvaluereset', 'qtype_sc'));
                    $('#id_correctrow_1').prop('checked', true);
                }
            }
        }
        /* eslint-enable */
    }
    return {
        init: function() {
            numberofrowschanged();
        }
    };
});