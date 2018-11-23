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
 * @package qtype_sc
 * @author        Jürgen Zimmer (juergen.zimmer@edaktik.at)
 * @author        Andreas Hruska (andreas.hruska@edaktik.at)
 * @copyright     2017 eDaktik GmbH {@link http://www.edaktik.at}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {

    /*
     * Manages checking and unchecking of option radio buttons.
     * Expects jquery to be loaded.
     */
    function toggleOptionButton(optionRadio, questionid, highlighting) {
        var optionRadioId = optionRadio.prop('id');

        // Set the highlighting of the row if it is enabled.
        if (highlighting) {
            if (optionRadio.prop('checked')) {
                setRowHighlighting(questionid, optionRadio.data('number'), false);
            }
        }

        // Uncheck all other option checkboxes and switch off highlighting in their rows.
        var optionCheckboxes = $('table#questiontable' + questionid).find('input.optioncheckbox');
        for (var i = 0; i < optionCheckboxes.length; i++) {
            var otherOptionCheckbox = $(optionCheckboxes[i]);
            if (otherOptionCheckbox.prop('id') !== optionRadioId) {
                otherOptionCheckbox.prop('checked', false);
                if (highlighting) {
                    setRowHighlighting(questionid, otherOptionCheckbox.data('number'), false);
                }
            }
        }

        if (highlighting && !optionRadio.prop('checked') && anyDistractorSelected(questionid)) {
            highlightAvailableRows(questionid);
        }

        if (optionRadio.prop('checked')) {
            var optiontextspan = $('table#questiontable' + questionid)
                .find('#q' + questionid + '_optiontext' + optionRadio.data('number'));
            if (optiontextspan) {
                optiontextspan.removeClass('linethrough');
            }
            var distractor = $('table#questiontable' + questionid)
                .find('#q' + questionid + '_distractor' + optionRadio.data('number'));
            if (distractor) {
                distractor.prop('checked', false);
            }
        }
    }

    /*
     * Manages checking and unchecking of distractor radio buttons.
     * Expects jquery to be loaded, i.e. $PAGE->requires->jquery(); has been called.
     */
    function toggleDistractorButton(distractorCheckbox, questionid, highlighting) {
        var optionNumber = distractorCheckbox.data('number');
        var optiontextspan = $('table#questiontable' + questionid).find('span#q' + questionid + '_optiontext' + optionNumber);

        if (distractorCheckbox.prop('checked')) {
            if (highlighting) {
                setRowHighlighting(questionid, distractorCheckbox.data('number'), false);
            }
            if (optiontextspan) {
                optiontextspan.addClass('linethrough');
            }

            // Now disable the corresponding optionbutton.
            var optionCheckboxid = 'q' + questionid + '_optionbutton' + optionNumber;
            var optionCheckbox = $('#' + optionCheckboxid);
            if (optionCheckbox && optionCheckbox.prop('checked')) {
                toggleOptionButton(optionCheckbox, questionid,  highlighting);
            }
        } else {
            if (optiontextspan) {
                optiontextspan.removeClass('linethrough');
            }
        }

        // Now check whether n-1 distractors have been checked.
        // If so, retrieve the single unchecked distractor and set its option checkbox.
        var uncheckedDistractorElem = getSingleUncheckedDistractor(questionid);
        if (uncheckedDistractorElem !== null) {
            var uncheckedDistractor = $(uncheckedDistractorElem);
            var number = uncheckedDistractor.data('number');
            var uncheckedOptionCheckboxId = 'q' + questionid + '_optionbutton' + number;
            var uncheckedOptionCheckbox = $('#' + uncheckedOptionCheckboxId);
            if (uncheckedOptionCheckbox) {
                uncheckedOptionCheckbox.prop('checked', true);
                toggleOptionButton(uncheckedOptionCheckbox, questionid, highlighting);
            }
        }
        if (highlighting && !anyOptionChecked(questionid)) {
            if (anyDistractorSelected(questionid)) {
                highlightAvailableRows(questionid);
            } else {
                unhighlightAllRows(questionid);
            }
        }

    }

    function anyOptionChecked(questionid) {
        var options = $('table#questiontable' + questionid).find('input.optioncheckbox');
        var checked = false;
        for (var i = 0; i < options.length && !checked; i++) {
            checked = options[i].checked;
        }
        return checked;
    }

    function anyDistractorSelected(questionid) {
        var distractors = $('table#questiontable' + questionid).find('input.distractorcheckbox');
        for (var i = 0; i < distractors.length; i++) {
            if  ($(distractors[i]).prop('checked')) {
                return true;
            }
        }
        return false;
    }

    function setRowHighlighting(questionid, number, highlightRow) {
        var row = $('table#questiontable' + questionid).find('tr[class*="optionrow' + number + '"]');
        if (highlightRow) {
            row.addClass('highlight');
        } else {
            row.removeClass('highlight');
        }
    }

    function highlightAvailableRows(questionid) {
        if (getSingleUncheckedDistractor(questionid)) {
            return;
        }
        var distractors = $('table#questiontable' + questionid).find('input.distractorcheckbox');
        for (var i = 0; i < distractors.length; i++) {
            var distractor = $(distractors[i]);
            if (!distractor.prop('checked')) {
                setRowHighlighting(questionid, distractor.data('number'), true);
            }
        }

    }

    function unhighlightAllRows(questionid) {
        var rows = $('table#questiontable' + questionid).find('tr[class*="optionrow"]');
        for (var i = 0; i < rows.length; i++) {
            $(rows[i]).removeClass('highlight');
        }
    }

    function getSingleUncheckedDistractor(questionid) {
        var distractors = $('table#questiontable' + questionid).find('input.distractorcheckbox');
        var result = null;
        var count = 0;
        for (var i = 0; i < distractors.length; i++) {
            if (distractors[i].checked) {
                count++;
            } else {
                result = distractors[i];
            }
        }
        if (count !== (distractors.length - 1)) {
            result = null;
        }
        return result;
    }

    return {
        init: function(optionHighlighting, questionid) {

            // Put whatever you like here. $ is available
            // to you as normal.
            var distractors = $('table#questiontable' + questionid).find('input.distractorcheckbox');
            for (var i = 0; i < distractors.length; i++) {
                var distractor = $(distractors[i]);
                distractor.change(function () {
                    toggleDistractorButton($(this), questionid, optionHighlighting);
                });
            }

            var optionbuttons = $('table#questiontable' + questionid).find('input.optioncheckbox');
            for (var i = 0; i < optionbuttons.length; i++) {
                var optionbutton = $(optionbuttons[i]);
                optionbutton.change(function () {
                    toggleOptionButton($(this), questionid, optionHighlighting);
                });
            }

            if (optionHighlighting && !anyOptionChecked(questionid)) {
                if (anyDistractorSelected(questionid)) {
                    highlightAvailableRows(questionid);
                }
            }
        }
    };
});
