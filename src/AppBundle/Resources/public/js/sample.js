$(document).ready(function(){
    $('#jsid').change(function()
    {
        var id = $('#jsid').val();
        $('#element option:gt(0)').remove();
        var data = {jsid : $('#jsid').val()};
        //alert(data.jsid);
        if($(this).val())
        {

            $.ajax({
                type: "POST",
                data: data,
                url: "/shipping_development/web/app_dev.php/rules/new_temp",
                success: function(data)
                {
                    $.each(data.kpiNameArray, function(i, listkpi) {

                        $('#element').append($('<option>', {
                            value: listkpi.id, text : listkpi.elementName
                        }));
                    });


                },
                error: function(XMLHttpRequest, textStatus, errorThrown)
                {
                    alert('Error : ' + errorThrown);
                }
            });
        }
    });
});



