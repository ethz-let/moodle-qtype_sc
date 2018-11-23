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

define(['jquery', 'qtype_sc/jquery.form'], function($) {

    var previousValue = null;
    var messages = {};
    var previewUrl = null;
    var loadingUrl = null;

    function setFocus(numberOfRows) {
        previousValue = numberOfRows.val();
    }

    function updateValue(numberOfRows) {
        var newValueInt = parseInt(numberOfRows.val());
        var oldValueInt = parseInt(previousValue);
        if (newValueInt < oldValueInt) {
            if (confirm(messages.warningreduceoptions)) {
                previousValue = numberOfRows.val();
                $("input[name='updatebutton']").click();
            } else {
                numberOfRows.val(previousValue);
            }
        }
        if (newValueInt > oldValueInt) {
            previousValue = numberOfRows.val();
            $("input[name='updatebutton']").click();
        }
    }

    function validate_options() {
        var numberOfRows = parseInt($('#id_numberofrows').val());
        for (var i = 1; i <= numberOfRows; i++) {
            var textarea = $('#id_option_' + i);
            var textValue = textarea.val();

            textValue.replace('<br/>', '');
            textValue.replace('<p></p>', '');

            if (textValue == '') {
                return false;
            }
        }
        return true;
    }

    function saveAndPreview(previewButton) {
        if (!validate_options()) {
            $("input[name='updatebutton']").click();
            return;
        }

        var form = previewButton.closest("form");
        if (form) {
            var previewWindow = window.open(loadingUrl, 'PreviewQuestion', 'height=620,width=750');

            form.ajaxForm(function(response) {
                previewWindow.location.href = previewUrl;
            });
            form.submit();
            form.off();
        }
    }

    return {
        init: function(messageStrings, prevUrl, loadUrl) {
            messages = messageStrings;
            previewUrl = prevUrl;
            loadingUrl = loadUrl;

            var numberOfRows = $('#id_numberofrows');
            numberOfRows.focus(function () {
                setFocus($(this));
            });
            numberOfRows.change(function () {
                updateValue($(this));
            });

            if (previewUrl) {
                // Check if preview window is open.
                var previewButton = $("button[name='previewbutton']");
                if (previewButton) {
                    previewButton.click(function () {
                        saveAndPreview($(this));
                    });
                }
                var previewInput = $("input[name='previewbutton']");
                if (previewInput) {
                    previewInput.click(function () {
                        saveAndPreview($(this));
                    });
                }
            }
        }
    };
});
