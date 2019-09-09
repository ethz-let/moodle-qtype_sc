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
 * @author        Martin hanusch martin.hanusch@let.ethz.ch
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
            toggleRadioButton(clickedRadio);
        }

        if (clickedRadio.hasClass('checked')) {
            if (distractorRadio && distractorRadio.hasClass('checked')) {
                toggleRadioButton(distractorRadio);
            }
            if (optiontextspan) {
                optiontextspan.removeClass('linethrough');
            }
        }

        var otheroptionradios = $('table#questiontable' + questionid)
            .find('input.optioncheckbox[value=1][data-number!="' + clickedRadio.data('number') + '"]');

        for (var i = 0; i < otheroptionradios.length; i++) {
            if ($(otheroptionradios[i]) && $(otheroptionradios[i]).hasClass('checked')) {
                toggleRadioButton($(otheroptionradios[i]));
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
            toggleRadioButton(clickedRadio);
        }

        if (clickedRadio.hasClass('checked')) {
            if (optionRadio && optionRadio.hasClass('checked')) {
                toggleRadioButton(optionRadio);
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

    function toggleRadioButton(clickedradio) {

        var correspondingradio = $('[name="' + $(clickedradio).prop('name') + '"][value=0]');

        if (clickedradio.hasClass('checked')) {
            correspondingradio.click();
            correspondingradio.prop('checked', true);
            correspondingradio.addClass('checked');
            clickedradio.removeClass('checked');
        } else {
            clickedradio.prop('checked', true);
            clickedradio.addClass('checked');
            correspondingradio.removeClass('checked');
        }
    }

    function numberOptionsChosen(questionid) {
        var chosenOptionRadios = $('table#questiontable' + questionid)
            .find('.optioncheckbox.checked[value=1]');

        return chosenOptionRadios.length;
    }

    function numberDistractorsChosen(questionid) {
        var chosenOptionRadios = $('table#questiontable' + questionid)
            .find('.distractorcheckbox.checked[value=1]');

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
                    .find('input.distractorcheckbox.checked[value=0]');

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
        }
    };
});