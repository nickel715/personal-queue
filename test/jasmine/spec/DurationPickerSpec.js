describe('DurationPicker', function() {
    var durationPicker;

    beforeEach(function() {
        durationPicker = $('html').durationPicker().data('plugin_durationPicker');
    });

    it('should extract the value for a designator', function() {
        var result = durationPicker.extract('5d', 'd');
        expect(result).toEqual(5);
    });

    it('should return 0 if designator is not in input', function() {
        var result = durationPicker.extract('5d', 'm');
        expect(result).toEqual(0);
    });

    it('should return input as pretty string', function() {
        var result = durationPicker.toString('2d 5m 3s');
        expect(result).toEqual('2 days 5 min 3 sec');
    });

    it('should return input in seconds', function() {
        var result = durationPicker.toSeconds('1d 6h 2m 4s');
        expect(result).toEqual(108124);
    });

});
