$(function(){
    $('#ptab').on('click', function(e){
        var x = $(e.target);
        if (x.hasClass('delb'))
        {
            e.preventDefault();
            e.stopPropagation();
            bootbox.confirm('Are you sure you you want to delete this Log Entry?', function(r){
                if (r)
                { // user picked OK
                    var tr = $(x).parent().parent()
                    $.post('/csc3123/ajax.php', {
                        op :'delentry',
                        'bean' : 'entry',
                        id : tr.data('id')
                    },
                    function(data){
                        tr.css('background-color', 'yellow').fadeOut(1500, function(){ tr.remove() });
                    });
                }
            });
        }
        /*else if (x.hasClass('editb'))
        {
            var tr = $(x).parent().parent()
            $.post('/csc3123/ajax.php', {
                op :'grabentry',
                bean : 'entry',
                id : tr.data('id')
            }, function(data){
                var jsonObj = JSON.parse(data);
                console.log(jsonObj);
                $('#editform input[name=title]').val(jsonObj.title);
                $('#editform textarea').html(jsonObj.body);
            });
        }
        $('#npage').on('show.bs.modal', function(e){
            $('#npform input,select').val('');
        });*/
    });
    $('#newentry').on('show.bs.modal', function(e){
	$('#form input,select').val('')
    })
});