/**
 * Created by lawrance on 30/1/16.
 */
$(document).ready(function ()
{




    $('#reading_kpi_values_monthdetail_day').hide();
    $('#initial_shipping_bundle_add_excel_file_dataofmonth_day').hide();

    $('#shipid').change(function()
    {

        $('#kpiid option:gt(0)').remove();
        var data = {shipid : $('#shipid').val()};

        if($(this).val())
        {
            $.ajax({
                type: "post",
                data: data,
                url: "/readingkpivalues/kpilist",
                success: function(data)
                {

                    $.each(data.kpiNameArray, function(i, listkpi) {

                        $('#kpiid').append($('<option>', {
                            value: listkpi.id, text : listkpi.kpiName
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

    $('#kpiid').change(function()
    {


        $('#elementId option:gt(0)').remove();
        var mydata = {elementId : $('#kpiid').val()};

        if($(this).val())
        {
            //alert('call function');
            $.ajax({
                type: "post",
                data: mydata,
                url: "/readingkpivalues/elementlist",
                success: function(data)
                {
                    // alert(data);
                    $.each(data.ElementNameArray, function(i, listelemnt) {

                        $('#elementId').append($('<option>', {
                            value: listelemnt.id, text : listelemnt.elementName
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