<input type="string" class="delayParser"> =>
<input type="number" name="delay" value="0" step="60"> sec

<script type="text/javascript" src="js/DurationPicker.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        var $delayParser = $('.delayParser');
        $delayParser.durationPicker();
        $delayParser.on('input', function() {
            var $this = $(this);
            var sec = $this.data('plugin_durationPicker').toSeconds($this.val());
            $(this).next('input[name=delay]').val(sec);
        });
    });
</script>
