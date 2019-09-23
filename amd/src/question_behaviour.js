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


    function handleOptionClick(activatedRadio, questionid) {

        updateRadioButton_onClick(activatedRadio, questionid);

        if (activatedRadio.attr('checked') && activatedRadio.attr('value') == 1) {

            // Uncheck other optionbuttons.
            var otheroptionradios = $('table#questiontable' + questionid)
            .find('input.optioncheckbox[value=0][data-number!="' + activatedRadio.data('number') + '"]');

            for (var i = 0; i < otheroptionradios.length; i++) {
                if ($(otheroptionradios[i]) && !$(otheroptionradios[i]).attr('checked')) {
                    updateRadioButton_onClick($(otheroptionradios[i]), questionid);
                }
            }

            // Disable distractor.
            var distractorRadio = $('table#questiontable' + questionid)
            .find('#q' + questionid + '_distractor' + activatedRadio.data('number') + '_hid');

            if (!distractorRadio.attr('checked')) {
                updateRadioButton_onClick(distractorRadio, questionid);
            }
        }
    }

    function handleOptionChange(activatedRadio, questionid, highlighting) {

        updateRadioButton_onChange(activatedRadio, questionid);

        if (activatedRadio.attr('checked') && activatedRadio.attr('value') == 1) {

            var optiontextspan = $('table#questiontable' + questionid)
            .find('#q' + questionid + '_optiontext' + activatedRadio.data('number'));

            optiontextspan.removeClass('linethrough');
        }

        highlightrows(questionid, highlighting); 
    }

    function handleDistractorClick(activatedRadio, questionid) {

        updateRadioButton_onClick(activatedRadio, questionid);

        // Disable correspondig option tick
        if (activatedRadio.attr('checked') && activatedRadio.attr('value') == 1) {

            var optionRadio = $('table#questiontable' + questionid)
            .find('#q' + questionid + '_optionbutton' + activatedRadio.data('number') + '_hid');

            if (optionRadio && !optionRadio.attr('checked')) {
                updateRadioButton_onClick(optionRadio, questionid);
            }
        }
    }

    function handleDistractorChange(activatedRadio, questionid, highlighting) {

        updateRadioButton_onChange(activatedRadio, questionid);

        var optiontextspan = $('table#questiontable' + questionid)
            .find('#q' + questionid + '_optiontext' + activatedRadio.data('number'));

        if (activatedRadio.attr('checked') && activatedRadio.attr('value') == 1) {
            optiontextspan.addClass('linethrough');
        } else {
            optiontextspan.removeClass('linethrough');
        }

        highlightrows(questionid, highlighting);
    }

    // Called on click. Adds actual checked prop.
    function updateRadioButton_onClick(activatedRadio, questionid) {

        var correspondingradio;

        if ($(activatedRadio).prop('value') == 1) {
            correspondingradio = $('table#questiontable' + questionid + ' [name="' + $(activatedRadio).prop('name') + '"][value=0]');
        } else {
            correspondingradio = $('table#questiontable' + questionid + ' [name="' + $(activatedRadio).prop('name') + '"][value=1]');
        }

        if ($(activatedRadio).attr('checked')) {
            correspondingradio.prop('checked', true);
            correspondingradio.trigger( "change" );
            
        } else  {
            activatedRadio.prop('checked', true);
            activatedRadio.trigger( "change" );
        }
        // Trigger autosave.
        $("[id=qtype_sc_changed_value_" + questionid + "]").val(Math.random());
    }

    // Called on change. Changes attributes.
    function updateRadioButton_onChange(activatedRadio, questionid) {

        var correspondingradio;
        if ($(activatedRadio).prop('value') == 1) {
            correspondingradio = $('table#questiontable' + questionid + ' [name="' + $(activatedRadio).prop('name') + '"][value=0]');
        } else {
            correspondingradio = $('table#questiontable' + questionid + ' [name="' + $(activatedRadio).prop('name') + '"][value=1]');
        }

        correspondingradio.removeAttr('checked');
        activatedRadio.attr('checked', 'checked');
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
                .find('input.distractorcheckbox');

            for (var i = 0; i < distractors.length; i++) {
                $(distractors[i]).click(function () {
                    handleDistractorClick($(this), questionid);
                });
                $(distractors[i]).change(function (e) {
                    handleDistractorChange($(this), questionid, optionHighlighting);
                    e.preventDefault();
                    e.stopPropagation();
                });
            }

            var optionbuttons = $('table#questiontable' + questionid)
                .find('input.optioncheckbox');

            for (var i = 0; i < optionbuttons.length; i++) {
                $(optionbuttons[i]).click(function () {
                    handleOptionClick($(this), questionid);
                });
                $(optionbuttons[i]).change(function (e) {
                    handleOptionChange($(this), questionid, optionHighlighting);
                    e.preventDefault();
                    e.stopPropagation();
                });
            }

            highlightrows(questionid, optionHighlighting);
        }
    };
});