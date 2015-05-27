<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Personal Queue</title>

    <!-- Bootstrap -->

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>

<div class="container">
    <div class="jumbotron">
        <h2>Your Job <small id="count">(<?= $count ?>)</small></h2>
        <blockquote>
            <p id="job"><?= $job->getData() ?></p>
        </blockquote>
        <button class="btn btn-success" id="done">Done</button>
        <button class="btn btn-warning" id="reschedule">Reschedule</button>
    </div>
    <div class="jumbotron">
        <form id="add">
            <div class="form-group">
                <input type="text" class="form-control" id="new" placeholder="todo..." />
            </div>
            <button type="submit" class="btn btn-default">Add</button>
        </form>
    </div>
</div>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->

<!-- Latest compiled and minified JavaScript -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {

        // https://github.com/bryanwoods/autolink-js/
        (function(){var h=[].slice;String.prototype.autoLink=function(){var b,f,d,a,e,g;a=1<=arguments.length?h.call(arguments,0):[];e=/(^|[\s\n]|<br\/?>)((?:https?|ftp):\/\/[\-A-Z0-9+\u0026\u2019@#\/%?=()~_|!:,.;]*[\-A-Z0-9+\u0026@#\/%=~()_|])/gi;if(!(0<a.length))return this.replace(e,"$1<a href='$2'>$2</a>");d=a[0];f=function(){var c;c=[];for(b in d)g=d[b],"callback"!==b&&c.push(" "+b+"='"+g+"'");return c}().join("");return this.replace(e,function(c,b,a){c=("function"===typeof d.callback?d.callback(a):void 0)||"<a href='"+
        a+"'"+f+">"+a+"</a>";return""+b+c})}}).call(this);

        var $job = $('#job');
        $($job).html(
            $job.html().autoLink({ target: "_blank" })
        );

        $('#done').click(function() {
            $.ajax({
                url: '<?= $_SERVER['PHP_SELF'] ?>',
                type: 'delete',
                data: $('#new').val(),
                success: function() {
                    $('#new').val('');
                }
            });
        });

        $('#reschedule').click(function() {
            $.get('<?= $_SERVER['PHP_SELF'] ?>', {
                'd': $('#job').html()
            }, function() {
                window.location.reload();
            });
        });

        $('#add').submit(function(e) {
            $.ajax({
                url: '<?= $_SERVER['PHP_SELF'] ?>',
                type: 'PUT',
                data: $('#new').val(),
                success: function() {
                    $('#new').val('');
                }
            });
            e.preventDefault();
        });
    });
</script>
</body>
</html>
