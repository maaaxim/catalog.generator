(function(){

    "use strict";

    var catalogLoader = new function () {

        /**
         * Self
         *
         * @type {catalogLoader}
         */
        var that = this;

        /**
         *  Current step
         *
         *  @type integer
         */
        var step;

        /**
         *  Jquery objects
         */
        var $progresbar;
        var $form;
        var $text1;
        var $text2;

        /**
         * Initializes
         */
        this.init = function () {
            this.initFields();
            this.initHandlers();
        };

        /**
         * Update progressbar staus
         */
        this.update = function () {
            var request = {
                "step" : that.step,
                "ajax" : "y"
            };
            var process = $.post("catalog_generator_controller.php", request, function(){}, "json");
            process.done(function (data) {
                that.step++;
                that.setText(data.text);
                if(data.finished == true){
                    that.setSize(100);
                    return;
                }
                that.setSize(data.percent);
                that.update(that.step);
            });
        };

        /**
         * Handle submit etc
         */
        this.initHandlers = function () {
            $(document).ready(function () {
                $(document).on("submit", "#progress-starter", function () {
                    that.setText("Initalization...");
                    that.update(that.step);
                    // @TODO deactivate event after click
                    return false;
                });
            });
        };

        /**
         * Initializes fields
         */
        this.initFields = function () {
            $(document).ready(function () {
                that.$progressbar = $(".pg-progress");
                that.$form = $("#progress-starter");
                that.$text1 = $("#pg-text-1");
                that.$text2= $("#pg-text-2");
                var data = that.$form.data();
                that.step = data.step;
            });
        };

        /**
         * Set progressbar size
         *
         * @param percent
         */
        this.setSize = function (percent) {
            that.$progressbar.css("width", Math.ceil(percent) + "%");
        };

        /**
         * Set progressbar message
         *
         * @param remaining
         * @param finish
         */
        this.setText = function (text) {
            that.$text1.html(text);
            that.$text2.html(text);
        };
    };

    catalogLoader.init();

})();