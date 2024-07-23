var that = this;
var result = {

    componentInit: function() {

        this.clickdistractor = function clickdistractor(isSelected, optionvalue) {

            if (this.question.optionselected && isSelected && this.question.optionselected == optionvalue) {
                this.question.optionselected = -1;
            }

            this.calculatehighlightrows();
        }

        this.clickoptionbutton = function clickoptionbutton(optionvalue) {

            if (!this.question.rows[optionvalue]) {
                return;
            }

            if (this.question.rows[optionvalue].distractorselected == 1) {
                this.question.rows[optionvalue].distractorselected = 0;
            }
        }

        this.calculatehighlightrows = function calculatehighlightrows() {

            if (!this.question.scoringmethod) {
                return;
            }

            if(this.question.scoringmethod == "sconezero") {
                return;
            }

            const hasCheckedDistractor = this.question.rows.some(row => row.distractorselected == 1);

            this.question.rows.forEach(row => {
                row.highlight = hasCheckedDistractor && row.optionvalue != -1 && !row.distractorselected &&
                    this.question.optionselected == -1;
            });

        }

        // Get the question raw data for initializing the template.

        if (!this.question) {
            return that.CoreQuestionHelperProvider.showComponentError(that.onAbort);
        }

        var div = document.createElement('div');
        div.innerHTML = this.question.html;

        that.CoreQuestionHelperProvider.replaceCorrectnessClasses(div);
        that.CoreQuestionHelperProvider.replaceFeedbackClasses(div);
        that.CoreQuestionHelperProvider.treatCorrectnessIcons(div);

        var questiontext = div.querySelector('.qtext');
        this.question.text = questiontext.innerHTML;

        if (typeof this.question.text === 'undefined') {
            return that.CoreQuestionHelperProvider.showComponentError(that.onAbort);
        }

        var prompt = div.querySelector('.prompt');
        var scoringmethod = div.querySelector('.que.sc [id^="scoringmethodinfo_q"]');
        var scoringmethodhelp = div.querySelector('.que.sc [id^="scoringmethodinfo_q"] a');

        this.question.prompt = prompt !== null ? prompt.innerHTML : null;
        this.question.scoringmethod = scoringmethod !== null ? scoringmethod.getAttribute('data-scoringmethod') : null;
        this.question.scoringmethodlabel = scoringmethod !== null ? scoringmethod.getAttribute('data-scoringmethodlabel') : null;
        this.question.scoringmethodhelp = scoringmethodhelp !== null ? scoringmethodhelp.getAttribute('data-content') : null;
        this.question.optionselected = -1;
        this.question.optiongroupname = null;

        var rows = [];
        var answeroptions = div.querySelector('.que.sc .generaltable tbody');
        var divs = answeroptions.querySelectorAll('tr');

        for (var i = 0; i < divs.length; i++) {
            var d = divs[i];

            var optionlabel = d.querySelector('.scoptionbutton.c0 label');
            var optiontext = optionlabel !== null ? optionlabel.innerHTML : null;

            var optionradio = d.querySelector('.scoptionbutton.c0 input');
            var disabled = optionradio !== null ? (optionradio.hasAttribute('disabled') ? true : false) : false;
            var optionid = optionradio !== null ? optionradio.getAttribute('id') : null;
            var optionname = optionradio !== null ? optionradio.getAttribute('name') : null;
            var optionvalue = optionradio !== null ? optionradio.getAttribute('value') : null;

            this.question.optiongroupname = optionname;
            this.question.optionselected = optionradio !== null ? (optionradio.checked ? optionvalue : this.question.optionselected) : this.question.optionselected;

            var distractorcheckbox = d.querySelector('.scdistractorbutton.c1 input[type=checkbox]');
            var distractorid = distractorcheckbox !== null ? distractorcheckbox.getAttribute('id') : null;
            var distractorname = distractorcheckbox !== null ? distractorcheckbox.getAttribute('name') : null;
            var distractorselected = distractorcheckbox !== null ? (distractorcheckbox.checked ? 1 : 0) : 0;

            var feedback = d.querySelector('div');
            feedback = feedback !== null ? feedback.innerHTML : '';

            var qclass = d.getAttribute('class');

            rows.push({
                optionid: optionid,
                optionname: optionname,
                optiontext: optiontext,
                optionvalue: optionvalue,
                distractorid: distractorid,
                distractorname: distractorname,
                distractorselected: distractorselected,
                feedback: feedback,
                disabled: disabled,
                qclass: qclass,
                highlight: false,
            });
        }

        this.question.rows = rows;

        this.calculatehighlightrows();

        return true;
    }
};

result;
