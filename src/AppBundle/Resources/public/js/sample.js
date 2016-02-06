$(document).ready(function(){
    $('#jsid').change(function()
    {
        //var id = $('#jsid').val();
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
    $('#weightageid').change(function(){

        var kpi = {kpiid: $('#kpiid').val(),id:$('#weightageid').val()};
        if($(this).val())
        {
            $.ajax({
                type: "POST",
                data: kpi,
                url: "/shipping_development/web/app_dev.php/elementdetails/weightage",
                success: function(data)
                {
                    $('#weightage_result').val(data.test);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown)
                {
                    alert('Error : ' + errorThrown);
                }
            })
        }
    });
    $('#weightage_submit').click(function(){
        var n = $('#weightage_result').val();
        if(n==-1)
        {
            alert("Please check the weightage for this kpi");
            return false;
        }
        else
        {
            return true;
        }
    })
});



