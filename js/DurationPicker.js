;(function($, window, document) {
    var pluginName = 'durationPicker',
        defaults = {
        },
        validDesignators = ['w', 'd', 'h', 'm', 's'],
        designatorLabels = {
            'w': 'weeks',
            'd': 'days',
            'h': 'hours',
            'm': 'min',
            's': 'sec'
        },
        designatorSecondsMultiplcators = {
            'w': 604800,
            'd': 86400,
            'h': 3600,
            'm': 60,
            's': 1
        };
    function Plugin(element, options) {
        this.element = element;
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this._validDesignators = validDesignators;
        this._designatorLabels = designatorLabels;
        this._designatorSecondsMultiplcators = designatorSecondsMultiplcators;
        this.init();
    }

    Plugin.prototype = {
        init: function() {

        },
        parse: function(input, callback) {
            var plugin = this;
            this._validDesignators.forEach(function(designator) {
                var extract = plugin.extract(input,  designator);
                callback(extract, designator, plugin);
            });
        },
        toString: function(input) {
            var result = '';
            this.parse(input, function(extract, designator, plugin) {
                if (extract) {
                    result += ' ' + extract + ' ' + plugin._designatorLabels[designator];
                }
            });
            return result.trim();
        },
        toSeconds: function(input) {
            var result = 0;
            this.parse(input, function(extract, designator, plugin) {
               result += extract * plugin._designatorSecondsMultiplcators[designator];
            });
            return result;
        },
        extract: function(input, designator) {
            var re = new RegExp('([0-9]+)' + designator);
            var match = input.match(re);
            var result = 0;
            if (match && match.length == 2) {
                result = parseInt(match[1]);
            }
            return result;
        }
    };
    $.fn[pluginName] = function(options) {
        this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });
        return this;
    };
})(jQuery, window, document);
