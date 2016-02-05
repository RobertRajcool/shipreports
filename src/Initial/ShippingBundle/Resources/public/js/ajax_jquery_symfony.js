/**
 * Created by lawrance on 30/1/16.
 */
$(document).ready(function () {
    $('#reading_kpi_values_month_day').hide();
    $('#app_bundle_excel_type_dataofmonth_day').hide();

    $('#shipid').change(function()
    {

        $('#kpiid option:gt(0)').remove();
        var data = {shipid : $('#shipid').val()};

        if($(this).val())
        {
            $.ajax({
                type: "post",
                data: data,
                url: "/Demo_app/web/app_dev.php/readingkpivalues/kpilist",
                success: function(data)
                {

                    $.each(data.kpiNameArray, function(i, listkpi) {

                        $('#kpiid').append($('<option>', {
                            value: listkpi.id, text : listkpi.kpiname
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
           // alert(mydata.elementId);
        if($(this).val())
        {
            $.ajax({
                type: "post",
                data: mydata,
                url: "/Demo_app/web/app_dev.php/readingkpivalues/elementlist",
                success: function(data)
                {
                    // alert(data);
                    $.each(data.ElementNameArray, function(i, listelemnt) {

                        $('#elementId').append($('<option>', {
                            value: listelemnt.id, text : listelemnt.elementname
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