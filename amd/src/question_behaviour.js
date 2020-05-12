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

    /*
     * Manages checking and unchecking of option radio buttons.
     */
    function clickoption(clickedRadio, questionid, highlighting) {

        var distractorCheckbox = $('table#questiontable' + questionid)
            .find('#q' + questionid + '_distractor' + clickedRadio.data('number'));

        if (clickedRadio.is(':checked')) {
            if (distractorCheckbox) {
                $(distractorCheckbox).prop("checked", false);
            }
        }

        linethroughrows(questionid);
        highlightrows(questionid, highlighting);
        toggleclearselection(questionid);
    }

    /*
     * Manages checking and unchecking of distractor radio buttons.
     */
    function clickDistractorButton(clickedDistractor, questionid, highlighting) {

        var optionRadio = $('table#questiontable' + questionid)
            .find('#q' + questionid + '_option' + clickedDistractor.data('number'));

        var hiddenOptionRadio = $('table#questiontable' + questionid)
            .find('#q' + questionid + '_option-1');

        if (clickedDistractor.is(':checked')) {
            if (optionRadio && hiddenOptionRadio) {
                if (optionRadio.is(':checked')) {
                    $(hiddenOptionRadio).prop('checked', true);
                }
            }
        }

        linethroughrows(questionid);
        highlightrows(questionid, highlighting);
        toggleclearselection(questionid);
    }

    function isOptionSelected(questionid) {

        var numHiddenOptionRadio = $('table#questiontable' + questionid)
            .find('#q' + questionid + '_option-1:checked').length;

        var numOptionRadios = $('table#questiontable' + questionid)
            .find('[id^="q' + questionid + '_option"]:checked').length

        return numOptionRadios == 1 && numHiddenOptionRadio == 0;
    }

    function numberDistractorsChosen(questionid) {

        var numDistractors = $('table#questiontable' + questionid)
            .find('.distractorcheckbox:checked').length;

        return numDistractors;
    }

    function linethroughrows(questionid) {

        var optionlabels = $('table#questiontable' + questionid)
            .find('label[for^="q' + questionid + '_option"]');

        for (var i = 0; i < optionlabels.length; i++) {
            $(optionlabels[i]).removeClass('linethrough');
        }

        var chosenDistractors = $('table#questiontable' + questionid)
            .find('input.distractorcheckbox:checked');

        for (var i = 0; i < chosenDistractors.length; i++) {
            var optionlabel = $('table#questiontable' + questionid)
                .find('label[for="q' + questionid + '_option' + $(chosenDistractors[i]).data('number') + '"]')[0];
            $(optionlabel).addClass('linethrough');
        }
    }

    function highlightrows(questionid, highlighting) {
        if (highlighting) {

            var rows = $('table#questiontable' + questionid)
                .find('tr[class^="optionrow"].highlight');

            for (var i = 0; i < rows.length; i++) {
                $(rows[i]).removeClass('highlight');
            }

            if (numberDistractorsChosen(questionid) > 0 && !isOptionSelected(questionid)) {
                var notChosenDistractors = $('table#questiontable' + questionid)
                    .find('input.distractorcheckbox:not(:checked)');

                for (var i = 0; i < notChosenDistractors.length; i++) {
                    var row = $('table#questiontable' + questionid)
                        .find('tr[class*="optionrow' + $(notChosenDistractors[i]).data('number') + '"]');
                    row.addClass('highlight');
                }
            }
        }
    }

    function toggleclearselection(questionid) {

        var clearselectionrow = $('table#questiontable' + questionid + ' .optionrow-1')[0];

        if (clearselectionrow) {
            if (!isOptionSelected(questionid)) {
                $(clearselectionrow).addClass('sr-only');
            } else {
                $(clearselectionrow).removeClass('sr-only');
            }
        }
    }

    return {
        init: function(optionHighlighting, questionid) {

            var distractors = $('table#questiontable' + questionid + ' input.distractorcheckbox');

            for (var i = 0; i < distractors.length; i++) {
                $(distractors[i]).change(function () {
                    clickDistractorButton($(this), questionid, optionHighlighting);
                });
            }

            var options = $('table#questiontable' + questionid + ' input.optionradio');

            for (var i = 0; i < options.length; i++) {
                $(options[i]).change(function () {
                    clickoption($(this), questionid, optionHighlighting);
                });
            }

            linethroughrows(questionid);
            highlightrows(questionid, optionHighlighting);
            toggleclearselection(questionid);
        }
    };
});