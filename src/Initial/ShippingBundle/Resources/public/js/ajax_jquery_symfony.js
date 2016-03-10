/**
 * Created by lawrance on 30/1/16.
 */
$(document).ready(function ()
{




    $('#reading_kpi_values_monthdetail_day').hide();
    $('#initial_shipping_bundle_add_excel_file_dataofmonth_day').hide();
    $('#chart_fromdate_day').hide();
    $('#chart_todate_day').hide();

    $('#shipid').change(function()
    {
        var addDiv = $('#TextBoxesGroup');
        $('.hiddenclass').remove();
        $('.dyaminc').remove();


        var data = {shipid : $('#shipid').val()};

        if($(this).val())
        {
            $.ajax({
                type: "post",
                data: data,
                url: "/readingkpivalues/kpilist",
                success: function(data)
                {
                  // alert(data);
                    var j=1;
                    $.each(data.kpiNameArray, function(i, listkpi)
                    {

                        var k=$.isNumeric(i);
                        if(k)
                        {
                            $('<p class="hiddenclass"><input class="hiddenfield" type="hidden" id="kpids"  name="kpiids[]" value="'+i+'"  />').appendTo(addDiv);
                            $.each(listkpi,function(mykey,myvalue)
                            {
                                $('<p class="hiddenclass"><input type="hidden" id="elementid"  name="elementid[]" value="'+myvalue+'"  />').appendTo(addDiv);
                            });

                        }
                        if(!k)
                        {
                            $('<p class="dyaminc"><label style="float: left"><b>'+i+'</b></label>').appendTo(addDiv);
                            $.each(listkpi,function(mykey,myvalue)
                            {
                                $('<p class="dyaminc"><tr><td><label style="margin-left: 50px">'+myvalue+'</label></td><td><input type="text" id="'+j+'" class="showhiddenclass"  name="'+myvalue+'" required/><input class="hiddenfield" type="hidden" id="h_'+j+'"  name="newelemetvalues[]" value=" "  /></td></tr>').appendTo(addDiv);
                            });

                        }
                        j++;


                    });


                },
                error: function(XMLHttpRequest, textStatus, errorThrown)
                {
                    alert('Error : ' + errorThrown);
                }
            });
        }
    });

    $('.showhiddenclass').live('focusout',function()
    {
       var test = $(this).attr('id');
       var mydata=$('#'+test).val();
       var elementname=$(this).attr('name');
        var datavalue = {myvalue : mydata,myelement:elementname};
        if($(this).val())
        {

            $.ajax({
                type: "post",
                data: datavalue,
                url: "/readingkpivalues/elementlist",
                success: function(data)
                {
                     alert(data);
                    $.each(data.ElementNameArray, function(i, listelemnt)
                    {
                        $('<p class="dyaminc"><label></label></label><input type="text" id="p_new"  name="' + listelemnt.id +'" value="" placeholder="'+listelemnt.elementName+'" />').appendTo(addDiv);

                     /*   $('#elementId').append($('<option>', {
                            value: listelemnt.id, text : listelemnt.elementName
                        }));*/
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