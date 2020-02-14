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
    function clickOptionButton(clickedRadio, questionid, highlighting) {

        var distractorRadio = $('table#questiontable' + questionid)
            .find('#q' + questionid + '_distractor' + clickedRadio.data('number'));

        var optiontextspan = $('table#questiontable' + questionid)
            .find('#q' + questionid + '_optiontext' + clickedRadio.data('number'));

        if (clickedRadio) {
            toggleRadioButton(clickedRadio, questionid);
        }

        if (clickedRadio.attr('checked')) {
            if (distractorRadio && distractorRadio.attr('checked')) {
                toggleRadioButton(distractorRadio, questionid);
            }
            if (optiontextspan) {
                optiontextspan.removeClass('linethrough');
            }
        }

        var otheroptionradios = $('table#questiontable' + questionid)
            .find('input.optioncheckbox[value=1][data-number!="' + clickedRadio.data('number') + '"]');

        for (var i = 0; i < otheroptionradios.length; i++) {
            if ($(otheroptionradios[i]) && $(otheroptionradios[i]).attr('checked')) {
                toggleRadioButton($(otheroptionradios[i]), questionid);
            }
        }

        highlightrows(questionid, highlighting);
    }

    /*
     * Manages checking and unchecking of distractor radio buttons.
     */
    function clickDistractorButton(clickedRadio, questionid, highlighting) {

        var optiontextspan = $('table#questiontable' + questionid)
            .find('#q' + questionid + '_optiontext' + clickedRadio.data('number'));

        var optionRadio = $('table#questiontable' + questionid)
            .find('#q' + questionid + '_optionbutton' + clickedRadio.data('number'));

        if (clickedRadio) {
            toggleRadioButton(clickedRadio, questionid);
        }

        if (clickedRadio.attr('checked')) {
            if (optionRadio && optionRadio.attr('checked')) {
                toggleRadioButton(optionRadio, questionid);
            }
            if (optiontextspan) {
                optiontextspan.addClass('linethrough');
            }
        } else {
            if (optiontextspan) {
                optiontextspan.removeClass('linethrough');
            }
        }

        highlightrows(questionid, highlighting);
    }

    function toggleRadioButton(clickedradio, questionid) {
        var correspondingradio = $('table#questiontable' + questionid + ' [name="' + $(clickedradio).prop('name') + '"][value=0]');

        if (clickedradio.attr('checked')) {
            correspondingradio.click();
            clickedradio.removeAttr('checked');
            correspondingradio.attr('checked', 'checked');
            correspondingradio.prop('checked', true);
        } else {

            correspondingradio.removeAttr('checked');
            clickedradio.attr('checked', 'checked');
            clickedradio.prop('checked', true);
        }
    }

    function numberOptionsChosen(questionid) {
        var chosenOptionRadios = $('table#questiontable' + questionid)
            .find('.optioncheckbox[checked][value=1]');

        return chosenOptionRadios.length;
    }

    function numberDistractorsChosen(questionid) {
        var chosenOptionRadios = $('table#questiontable' + questionid)
            .find('.distractorcheckbox[checked][value=1]');

        return chosenOptionRadios.length;
    }

    function highlightrows(questionid, highlighting) {
        if (highlighting) {

            var rows = $('table#questiontable' + questionid)
                .find('tr[class^="optionrow"].highlight');

            for (var i = 0; i < rows.length; i++) {
                $(rows[i]).removeClass('highlight');
            }

            if (numberOptionsChosen(questionid) == 0 && numberDistractorsChosen(questionid) > 0) {
                var notChosenDistractors = $('table#questiontable' + questionid)
                    .find('input.distractorcheckbox[checked][value=0]');

                for (var i = 0; i < notChosenDistractors.length; i++) {
                    var row = $('table#questiontable' + questionid)
                        .find('tr[class*="optionrow' + $(notChosenDistractors[i]).data('number') + '"]');
                    row.addClass('highlight');
                }
            }
        }
    }

    return {
        init: function(optionHighlighting, questionid) {

            var distractors = $('table#questiontable' + questionid)
                .find('input.distractorcheckbox[value=1]');

            for (var i = 0; i < distractors.length; i++) {
                $(distractors[i]).click(function () {
                    clickDistractorButton($(this), questionid, optionHighlighting);
                });
            }

            var optionbuttons = $('table#questiontable' + questionid)
                .find('input.optioncheckbox[value=1]');

            for (var i = 0; i < optionbuttons.length; i++) {
                $(optionbuttons[i]).click(function () {
                    clickOptionButton($(this), questionid, optionHighlighting);
                });
            }

            highlightrows(questionid, optionHighlighting);
        },
        wifi_init: function(questionid) {

            console.log("Wifi Resilience Backup loaded for questionid: " + questionid);

            var distractors = $('table#questiontable' + questionid).find('input.distractorcheckbox[value=1]'); // Only check visbible distractors (value=1).
            for (var i = 0; i < distractors.length; i++) {
                if ($(distractors[i]).attr('checked')) {
                    $('table#questiontable' + questionid + ' [name="' + $(distractors[i]).prop('name') + '"][value=0]').removeAttr('checked');
                    $('table#questiontable' + questionid + ' #q' + questionid + '_optiontext' + $(distractors[i]).data('number')).addClass('linethrough');
                }
            }

            var optionbuttons = $('table#questiontable' + questionid).find('input.optioncheckbox[value=1]'); // Only check visbible options (value=1).
            for (var i = 0; i < optionbuttons.length; i++) {
                if ($(optionbuttons[i]).attr('checked')) {
                    $('table#questiontable' + questionid + ' [name="' + $(optionbuttons[i]).prop('name') + '"][value=0]').removeAttr('checked');
                }
            }

            var scoringmethod = $('#qtype_sc_scoring_method_' + questionid).val();
            if(scoringmethod && (scoringmethod == 'aprime' || scoringmethod == 'subpoints')) {
                highlightrows(questionid, true);
            }
        },
    };
});