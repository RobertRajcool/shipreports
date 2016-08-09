/**
 * Created by lawrance on 25/7/16.
 */
$(document).ready(function()
{
    $('#updatebuttonid').hide();
    var base_url = window.location.origin;
    $('#nogroupselected').hide();
    $('#kpiformforship').show();
    var shipid='{{ currentshipid }}';
    var shipname='{{ currentshipname }}';
    var statusofship='{{ statuscount }}';
    var shipcount='{{ shipcount }}';
    var role='{{ app.user.roles[0] }}';
    console.log(role)
    if(statusofship==shipcount)
    {
        $('#allbuttondiv').hide();
        // $('#'+shipid).addClass('active');
        $('#shipid').val('');
        $('#shipname').text('');
    }
    else
    {
        $('#'+shipid).addClass('active');
        $('#shipid').val(shipid);
        $('#shipname').text(shipname);
        $('#allbuttondiv').show();
    }


    var nowdate=new Date(),
        locale = "en-us",
        month = nowdate.toLocaleString(locale, { month: "long" }),
        year=nowdate.getFullYear();
    $('#currentid').text(month+'-'+year)
    $('#currentid_data').text(month+'-'+year)
    $('#datofmonthid').val(nowdate);
    $('#datofmonthid_data').val(nowdate);
    $('.prevmonth').live('click',function($e)
    {
        $e.preventDefault();
        var linkid = $(this).attr('id');
        if(linkid=='proviousmonth_data')
        {

            var currentdate=new Date($('#datofmonthid_data').val());
        }
        else
        {
            var currentdate=new Date($('#datofmonthid').val());
        }

        currentdate.setMonth(currentdate.getMonth() - 1);
        currentdate,locale = "en-us",
            month = currentdate.toLocaleString(locale, { month: "long" }),
            year=currentdate.getFullYear();
        // var data={'dataofmonth':month+'-'+year};
        var current=month+'-'+year;
        var data = {
            dataofmonth :current
        };

        $.ajaxSetup({
            global: false,
            type: "post",
            'url': Routing.generate('prev_month_change_scorecard'),
            beforeSend: function ()
            {
                var beforsend=ajaxbefore_send();
            },
            complete: function () {
                var complete=ajax_complete();
            }
        });
        $.ajax({
            data:data,
            success: function(data)
            {
                console.log(data.commontext);
                if (data.commontext == true)
                {
                    $('#prvieoustablecontent').html(' ');
                    $('#prvieoustablecontent').text(' ');
                    var addcolumn = 0;
                    var valuecount = 0;
                    $('<table id="table_id_" class="table verfication_ranking_table_form_section"></table>').appendTo('#prvieoustablecontent');
                    $('<thead id="firsttable_head_"></thead><tbody id="firsttable_body_"></tbody>').appendTo('#table_id_');
                    $('<tr class="v_r_min_height" id="firsttable_head_tr_"></tr>').appendTo('#firsttable_head_');
                    $('<th class="kpi_lement_name"></th><th class="kpi_weightage"><div class="verfication_ranking_text_rotation_2">Weightage</div></th>').appendTo('#firsttable_head_tr_');
                    $.each(data.listofships, function (j) {
                        $('<th class="kpi_ship "><div class="verfication_ranking_text_rotation_2">' + data.listofships[j]['shipName'] + '</div></th>').appendTo('#firsttable_head_tr_');
                    });
                    $.each(data.rankingKpiList, function (i)
                    {

                        var colspan = data.shipcount + 2;
                        $('<table id="table_id_' + i + '" class="table verfication_ranking_table_form_section"></table>').appendTo('#prvieoustablecontent');
                        $('<thead id="firsttable_head_' + i + '"></thead><tbody id="firsttable_body_' + i + '"></tbody>').appendTo('#table_id_' + i);
                        $('<tr id="firsttable_head_tr_' + i + '"></tr>').appendTo('#firsttable_head_' + i);
                        $('<th colspan="' + colspan + '" class="kpi_lement_name">' + data.rankingKpiList[i]['kpiName'] + '</th>').appendTo('#firsttable_head_tr_' + i);
                        var kpiid = data.rankingKpiList[i]['id'];
                        var elementnamearray = data.elementname[kpiid];
                        var forloopcount = 0;
                        for (forloopcount = 0; forloopcount < elementnamearray.length; forloopcount++) {
                            $('<tr id="firsttable_body_tr_' + kpiid + '_' + forloopcount + '"></tr>').appendTo('#firsttable_body_' + i + '');
                            $('<td class="kpi_lement_name kpi_lement_name">' + data.elementweightage[kpiid][forloopcount] + '</td>').appendTo('#firsttable_body_tr_' + kpiid + '_' + forloopcount + '');
                            $('<td class="kpi_weightage kpi_lement_number">' + data.elementname[kpiid][forloopcount] + '</td>').appendTo('#firsttable_body_tr_' + kpiid + '_' + forloopcount + '');
                            $.each(data.listofships, function (j) {
                                var shipid = data.listofships[j]['id']
                                var elementvalue = data.elementvalues_ship[shipid][valuecount]
                                if (elementvalue != null) {
                                    $('<td class="kpi_ship kpi_lement_inputname">' + elementvalue + '</td>').appendTo('#firsttable_body_tr_' + kpiid + '_' + forloopcount + '');
                                }
                                else {
                                    $('<td class="kpi_ship kpi_lement_inputname"></td>').appendTo('#firsttable_body_tr_' + kpiid + '_' + forloopcount + '');
                                }

                            });
                            valuecount++;
                        }
                    });


                    if (linkid == 'proviousmonth_data') {
                        $('#currentid_data').text(month + '-' + year);
                        $('#datofmonthid_data').val(currentdate);
                        $('#currentid').text(month + '-' + year);
                        $('#datofmonthid').val(currentdate);
                    }
                    else {
                        $('#currentid').text(month + '-' + year);
                        $('#datofmonthid').val(currentdate);
                        $('#currentid_data').text(month + '-' + year);
                        $('#datofmonthid_data').val(currentdate);
                    }
                    $('#kpiformforship').hide();
                    $('#currentmonhthdetails').hide();
                    $('#previousmonthdata').show();
                }
                else
                {
                    $('#allbuttondiv').show();
                    $('#kpiformforship').show();
                    $('#previousmonthdata').hide();
                    $('#currentmonhthdetails').show();
                    $('#overallshids').html('');
                    $('#overallshids').text('');
                    $('<div class="views_kpi_name_total"><span>Vessels (' + data.shipcount + ')</span></div>').appendTo('#overallshids');
                    $('<div class="views_kpi_form_list_main"> <ui id="addshipids"></ui></div>').appendTo('#overallshids');
                    var shipstatus = 0
                    $.each(data.listofships, function (i) {
                        var status = data.status_ship[i];


                        $('<li><a class="linkclass" title="' + data.listofships[i]['shipName'] + '" id="' + data.listofships[i]['id'] + '" >' + data.listofships[i]['shipName'] + '</a></li>').appendTo('#addshipids');
                        if(role=='ROLE_ADMIN')
                        {
                            if (status == 3) {
                                $('#' + data.listofships[i]['id']).after('<img src="/images/tick_icon.png">');
                                shipstatus++;
                            }
                        }
                        else if(role=='ROLE_MANAGER')
                        {
                            if (status == 2) {
                                $('#' + data.listofships[i]['id']).after('<img src="/images/tick_icon.png">');
                                shipstatus++;
                            }
                        }
                        else if(role=='ROLE_KPI_INFO_PROVIDER')
                        {
                            if (status == 1) {
                                $('#' + data.listofships[i]['id']).after('<img src="/images/tick_icon.png">');
                                shipstatus++;
                            }
                        }

                    });
                    $('#elementformid').html('');
                    $('#elementformid').text('');
                    $('#shipname').text(data.currentshipname);
                    $('#currentid').text(month + '-' + year);
                    $('#datofmonthid').val(currentdate);
                    $('<div id="addkpiformid" class="table-responsive"></div>').appendTo('#elementformid');
                    //alert(data.shipstatusstring)
                    if (data.shipcount != shipstatus) {
                        $('#shipid').val(data.currentshipid);
                        $('.linkclass').removeClass("active");
                        $('#' + data.currentshipid).addClass('active');
                        var j = 1;
                        var newtemp = 0;
                        $.each(data.elementkpiarray, function (i, listkpi) {

                            var k = $.isNumeric(i);
                            if (!k) {
                                var temp = 1

                                $('<table class="table kpi_table_form_section"><thead id="firstheader_'+i+'"><tr id="firstheader_tr_'+newtemp+'"><th>'+i+'</th></tr></thead> <tbody id="tablebody'+j+'">').appendTo('#addkpiformid');

                                if(newtemp==0)
                                {
                                    $(' <th class="kpi_value">Wt</th><th class="kpi_value">Symbol</th><th class="kpi_value">Indication</th><th class="kpi_value">Value</th>').appendTo('#firstheader_tr_'+newtemp)
                                }
                                else
                                {
                                    $(' <th></th><th></th><th></th><th></th>').appendTo('#firstheader_tr_'+newtemp)
                                }
                                $.each(listkpi, function (mykey, myvalue) {
                                    if (data.elementvalues.length > 0) {

                                        $('#updatebuttonid').show();
                                        $('#savebuttonid').hide();
                                        $('#adminbuttonid').show();

                                        if (temp == 1) {
                                            $('<tr><td  class="kpi_lement_name">' + myvalue + '</td><td class="kpi_lement_number">' + data.elementweightage[newtemp] + '</td> <td class="kpi_lement_inputname">' + data.symbolIndication[newtemp] + '</td><td class="kpi_lement_inputname">' + data.indicationValue[newtemp] + '</td> <td class="kpi_lement_inputname"><input class="resetclass" placeholder="123" onkeypress="return isNumberKey(event)"  value="' + data.elementvalues[newtemp] + '" type="text" name="newelemetvalues[]" required ></td></tr>').appendTo("#tablebody" + j);
                                        }
                                        if (temp > 1) {
                                            $('<tr ><td class="kpi_lement_name">' + myvalue + '</td><td class="kpi_lement_number">' + data.elementweightage[newtemp] + '</td> <td class="kpi_lement_inputname">' + data.symbolIndication[newtemp] + '</td><td class="kpi_lement_inputname">' + data.indicationValue[newtemp] + '</td> <td class="kpi_lement_inputname"><input class="resetclass" placeholder="123" type="text" name="newelemetvalues[]" onkeypress="return isNumberKey(event)" value="' + data.elementvalues[newtemp] + '" required ></td></tr>').appendTo("#tablebody" + j);
                                        }


                                    }
                                    else {
                                        $('#updatebuttonid').hide();
                                        $('#savebuttonid').show();
                                        $('#adminbuttonid').show();
                                        if (temp == 1) {
                                            $('<tr><td  class="kpi_lement_name">' + myvalue + '</td><td class="kpi_lement_number">' + data.elementweightage[newtemp] + '</td><td class="kpi_lement_inputname">' + data.symbolIndication[newtemp] + '</td><td class="kpi_lement_inputname">' + data.indicationValue[newtemp] + '</td>  <td class="kpi_lement_inputname"><input class="resetclass" placeholder="123" type="text" name="newelemetvalues[]" required onkeypress="return isNumberKey(event)" ></td></tr>').appendTo("#tablebody" + j);
                                        }
                                        if (temp > 1) {
                                            $('<tr><td class="kpi_lement_name">' + myvalue + '</td><td class="kpi_lement_number">' + data.elementweightage[newtemp] + '</td> <td class="kpi_lement_inputname">' + data.symbolIndication[newtemp] + '</td><td class="kpi_lement_inputname">' + data.indicationValue[newtemp] + '</td> <td class="kpi_lement_inputname"><input class="resetclass" type="text" placeholder="123"  onkeypress="return isNumberKey(event)" name="newelemetvalues[]" required ></td></tr>').appendTo("#tablebody" + j);
                                        }


                                    }
                                    temp++;
                                    newtemp++;


                                });
                                $(' </tr></tbody></table>').appendTo('#addkpiformid');


                            }
                            j++;


                        });

                    }
                    else {
                        $('<span id="spanallid">All Ships Data Upload.</span>').appendTo('#elementformid');
                        $('#allbuttondiv').hide();
                    }


                }

            },
            error: function(XMLHttpRequest, textStatus, errorThrown)
            {
                $('#resultLoading .bg').height('100%');
                $('#resultLoading').fadeOut(300);
                $('body').css('cursor', 'default');
                window.location.href = base_url+'/login';
            }
        });


    });
    $('.nextmonth').live('click',function($e)
    {
        $e.preventDefault();
        var linkid = $(this).attr('id');
        if(linkid=='proviousmonth_data')
        {

            var currentdate=new Date($('#datofmonthid_data').val());
        }
        else
        {
            var currentdate=new Date($('#datofmonthid').val());
        }
        currentdate.setMonth(currentdate.getMonth() + 1);
        currentdate,locale = "en-us",
            month = currentdate.toLocaleString(locale, { month: "long" }),
            year=currentdate.getFullYear();
        var nowdate=new Date();
        if(currentdate<=nowdate)
        {
            var current=month+'-'+year;
            var data = {
                dataofmonth :current
            };

            $.ajaxSetup({
                global: false,
                type: "post",
                'url': Routing.generate('prev_month_change_scorecard'),
                beforeSend: function ()
                {
                    var beforsend=ajaxbefore_send();
                },
                complete: function () {
                    var complete=ajax_complete();
                }
            });
            $.ajax({
                data:data,
                success: function(data)
                {
                    if (data.commontext == true)
                    {
                        $('#prvieoustablecontent').html(' ');
                        $('#prvieoustablecontent').text(' ');
                        var addcolumn = 0;
                        var valuecount = 0;
                        $('<table id="table_id_" class="table verfication_ranking_table_form_section"></table>').appendTo('#prvieoustablecontent');
                        $('<thead id="firsttable_head_"></thead><tbody id="firsttable_body_"></tbody>').appendTo('#table_id_');
                        $('<tr class="v_r_min_height" id="firsttable_head_tr_"></tr>').appendTo('#firsttable_head_');
                        $('<th class="kpi_lement_name"></th><th class="kpi_weightage"><div class="verfication_ranking_text_rotation_2">Weightage</div></th>').appendTo('#firsttable_head_tr_');
                        $.each(data.listofships, function (j) {
                            $('<th class="kpi_ship "><div class="verfication_ranking_text_rotation_2">' + data.listofships[j]['shipName'] + '</div></th>').appendTo('#firsttable_head_tr_');
                        });
                        $.each(data.rankingKpiList, function (i)
                        {

                            var colspan = data.shipcount + 2;
                            $('<table id="table_id_' + i + '" class="table verfication_ranking_table_form_section"></table>').appendTo('#prvieoustablecontent');
                            $('<thead id="firsttable_head_' + i + '"></thead><tbody id="firsttable_body_' + i + '"></tbody>').appendTo('#table_id_' + i);
                            $('<tr id="firsttable_head_tr_' + i + '"></tr>').appendTo('#firsttable_head_' + i);
                            $('<th colspan="' + colspan + '" class="kpi_lement_name">' + data.rankingKpiList[i]['kpiName'] + '</th>').appendTo('#firsttable_head_tr_' + i);
                            var kpiid = data.rankingKpiList[i]['id'];
                            var elementnamearray = data.elementname[kpiid];
                            console.log(elementnamearray.length)
                            var forloopcount = 0;
                            for (forloopcount = 0; forloopcount < elementnamearray.length; forloopcount++) {
                                $('<tr id="firsttable_body_tr_' + kpiid + '_' + forloopcount + '"></tr>').appendTo('#firsttable_body_' + i + '');
                                $('<td class="kpi_lement_name kpi_lement_name">' + data.elementweightage[kpiid][forloopcount] + '</td>').appendTo('#firsttable_body_tr_' + kpiid + '_' + forloopcount + '');
                                $('<td class="kpi_weightage kpi_lement_number">' + data.elementname[kpiid][forloopcount] + '</td>').appendTo('#firsttable_body_tr_' + kpiid + '_' + forloopcount + '');
                                $.each(data.listofships, function (j) {
                                    var shipid = data.listofships[j]['id']
                                    var elementvalue = data.elementvalues_ship[shipid][valuecount]
                                    if (elementvalue != null) {
                                        $('<td class="kpi_ship kpi_lement_inputname">' + elementvalue + '</td>').appendTo('#firsttable_body_tr_' + kpiid + '_' + forloopcount + '');
                                    }
                                    else {
                                        $('<td class="kpi_ship kpi_lement_inputname"></td>').appendTo('#firsttable_body_tr_' + kpiid + '_' + forloopcount + '');
                                    }

                                });
                                valuecount++;
                            }
                        });


                        if (linkid == 'proviousmonth_data') {
                            $('#currentid_data').text(month + '-' + year);
                            $('#datofmonthid_data').val(currentdate);
                            $('#currentid').text(month + '-' + year);
                            $('#datofmonthid').val(currentdate);
                        }
                        else {
                            $('#currentid').text(month + '-' + year);
                            $('#datofmonthid').val(currentdate);
                            $('#currentid_data').text(month + '-' + year);
                            $('#datofmonthid_data').val(currentdate);
                        }
                        $('#kpiformforship').hide();
                        $('#currentmonhthdetails').hide();
                        $('#previousmonthdata').show();
                    }
                    else
                    {
                        $('#overallshids').html('');
                        $('#overallshids').text('');
                        $('#previousmonthdata').hide();
                        $('#kpiformforship').show();
                        $('#currentmonhthdetails').show();
                        $('#allbuttondiv').show();
                        $('<div class="views_kpi_name_total"><span>Vessels ('+data.shipcount+')</span></div>').appendTo('#overallshids');
                        $('<div class="views_kpi_form_list_main"> <ui id="addshipids"></ui></div>').appendTo('#overallshids');
                        var shipstatus=0
                        $.each(data.listofships, function(i)
                        {
                            var status=data.status_ship[i];


                            $('<li><a class="linkclass" title="'+data.listofships[i]['shipName']+'" id="'+data.listofships[i]['id']+'" >'+data.listofships[i]['shipName']+'</a></li>').appendTo('#addshipids');
                            if(role=='ROLE_ADMIN')
                            {
                                if (status == 3) {
                                    $('#' + data.listofships[i]['id']).after('<img src="/images/tick_icon.png">');
                                    shipstatus++;
                                }
                            }
                            else if(role=='ROLE_MANAGER')
                            {
                                if (status == 2) {
                                    $('#' + data.listofships[i]['id']).after('<img src="/images/tick_icon.png">');
                                    shipstatus++;
                                }
                            }
                            else if(role=='ROLE_KPI_INFO_PROVIDER')
                            {
                                if (status == 1) {
                                    $('#' + data.listofships[i]['id']).after('<img src="/images/tick_icon.png">');
                                    shipstatus++;
                                }
                            }
                        });
                        $('#elementformid').html('');
                        $('#elementformid').text('');
                        $('#shipname').text(data.currentshipname);
                        $('#currentid').text(month+'-'+year);
                        $('#datofmonthid').val(currentdate);
                        $('<div id="addkpiformid" class="table-responsive"></div>').appendTo('#elementformid');
                        //alert(data.shipstatusstring)
                        if(data.shipcount !=shipstatus)
                        {
                            $('#shipid').val(data.currentshipid);
                            $('.linkclass').removeClass("active");
                            $('#'+data.currentshipid).addClass('active');
                            var j=1;
                            var newtemp=0;
                            $.each(data.elementkpiarray, function(i, listkpi)
                            {

                                var k=$.isNumeric(i);
                                if(!k)
                                {
                                    var temp=1

                                    $('<table class="table kpi_table_form_section"><thead id="firstheader_'+i+'"><tr id="firstheader_tr_'+newtemp+'"><th>'+i+'</th></tr></thead> <tbody id="tablebody'+j+'">').appendTo('#addkpiformid');

                                    if(newtemp==0)
                                    {
                                        $(' <th class="kpi_value">Wt</th><th class="kpi_value">Symbol</th><th class="kpi_value">Indication</th><th class="kpi_value">Value</th>').appendTo('#firstheader_tr_'+newtemp)
                                    }
                                    else
                                    {
                                        $(' <th></th><th></th><th></th><th></th>').appendTo('#firstheader_tr_'+newtemp)
                                    }
                                    $.each(listkpi,function(mykey,myvalue)
                                    {
                                        if(data.elementvalues.length>0)
                                        {

                                            $('#updatebuttonid').show();
                                            $('#savebuttonid').hide();
                                            $('#adminbuttonid').show();

                                            if(temp==1)
                                            {
                                                $('<tr><td  class="kpi_lement_name">'+myvalue+'</td><td class="kpi_lement_number">'+data.elementweightage[newtemp]+'</td> <td class="kpi_lement_inputname">' + data.symbolIndication[newtemp] + '</td><td class="kpi_lement_inputname">' + data.indicationValue[newtemp] + '</td> <td class="kpi_lement_inputname"><input class="resetclass" placeholder="123" onkeypress="return isNumberKey(event)"  value="'+data.elementvalues[newtemp]+'" type="text" name="newelemetvalues[]" required ></td></tr>').appendTo("#tablebody"+j);
                                            }
                                            if(temp>1)
                                            {
                                                $('<tr ><td class="kpi_lement_name">'+myvalue+'</td><td class="kpi_lement_number">'+data.elementweightage[newtemp]+'</td> <td class="kpi_lement_inputname">' + data.symbolIndication[newtemp] + '</td><td class="kpi_lement_inputname">' + data.indicationValue[newtemp] + '</td> <td class="kpi_lement_inputname"><input class="resetclass" placeholder="123" type="text" name="newelemetvalues[]" onkeypress="return isNumberKey(event)" value="'+data.elementvalues[newtemp]+'" required ></td></tr>').appendTo("#tablebody"+j);
                                            }


                                        }
                                        else
                                        {
                                            $('#updatebuttonid').hide();
                                            $('#savebuttonid').show();
                                            $('#adminbuttonid').show();
                                            if(temp==1)
                                            {
                                                $('<tr><td  class="kpi_lement_name">'+myvalue+'</td><td class="kpi_lement_number">'+data.elementweightage[newtemp]+'</td>  <td class="kpi_lement_inputname">' + data.symbolIndication[newtemp] + '</td><td class="kpi_lement_inputname">' + data.indicationValue[newtemp] + '</td> <td class="kpi_lement_inputname"><input class="resetclass" placeholder="123" type="text" name="newelemetvalues[]" required onkeypress="return isNumberKey(event)" ></td></tr>').appendTo("#tablebody"+j);
                                            }
                                            if(temp>1)
                                            {
                                                $('<tr><td class="kpi_lement_name">'+myvalue+'</td><td class="kpi_lement_number">'+data.elementweightage[newtemp]+'</td> <td class="kpi_lement_inputname">' + data.symbolIndication[newtemp] + '</td><td class="kpi_lement_inputname">' + data.indicationValue[newtemp] + '</td> <td class="kpi_lement_inputname"><input class="resetclass" type="text" placeholder="123"  onkeypress="return isNumberKey(event)" name="newelemetvalues[]" required ></td></tr>').appendTo("#tablebody"+j);
                                            }


                                        }
                                        temp++;
                                        newtemp++;


                                    });
                                    $(' </tr></tbody></table>').appendTo('#addkpiformid');


                                }
                                j++;


                            });

                        }
                        else
                        {
                            $('<span id="spanallid">All Ships Data Upload.</span>').appendTo('#elementformid');
                            $('#allbuttondiv').hide();
                        }




                    }

                },
                error: function(XMLHttpRequest, textStatus, errorThrown)
                {
                    $('#resultLoading .bg').height('100%');
                    $('#resultLoading').fadeOut(300);
                    $('body').css('cursor', 'default');
                    window.location.href = base_url+'/login';
                }
            }); }
        else
        {
            $('#spantext').text('Date Exceed...');
            $('#spantext').show();
            setTimeout(function() {
                $('#spantext').fadeOut('fast');
            }, 4000)

        }
    });
    $('#spantext').hide();

    $('.linkclass').live('click',function()
    {

        var linkid = $(this).attr('id');
        var shipname = $(this).attr('title');
        $('#shipid').val('');

        $('#addkpiformid').html('');
        $('#addkpiformid').text('');
        $('#shipname').text(' ');
        var dataofmonth=$('#currentid').text();
        $.ajaxSetup({
            global: false,
            type: "post",
            'url': Routing.generate('shipskpielment', {'shipid':linkid,'monthdetail':dataofmonth}),
            beforeSend: function ()
            {
                var beforsend=ajaxbefore_send();
            },
            complete: function () {
                var complete=ajax_complete();
            }
        });


        $.ajax({

            success: function(data)
            {

                var valuearray=data.elementvalues;
                $('#shipid').val(linkid);
                $('#shipname').text(shipname);
                $('#nogroupselected').hide();
                $('#kpiformforship').show();
                $('.linkclass').removeClass("active");
                $('#'+linkid).addClass('active');
                var j=1;
                var newtemp=0;
                $.each(data.kpiNameArray, function(i, listkpi)
                {

                    var k=$.isNumeric(i);
                    if(!k)
                    {
                        var temp=1

                        $('<table class="table kpi_table_form_section"><thead><tr id="firstheader_tr_'+newtemp+'"><th >'+i+'</th></tr></thead> <tbody id="tablebody'+j+'">').appendTo('#addkpiformid');

                        if(newtemp==0)
                        {
                            $(' <th class="kpi_value">Wt</th><th class="kpi_value">Symbol</th><th class="kpi_value">Indication</th><th class="kpi_value">Value</th>').appendTo('#firstheader_tr_'+newtemp)
                        }
                        else
                        {
                            $(' <th></th><th></th><th></th><th></th>').appendTo('#firstheader_tr_'+newtemp)
                        }
                        $.each(listkpi,function(mykey,myvalue)
                        {
                            if(valuearray.length>0)
                            {

                                $('#updatebuttonid').show();
                                $('#savebuttonid').hide();
                                $('#adminbuttonid').show();

                                if(temp==1)
                                {
                                    $('<tr><td  class="kpi_lement_name">'+myvalue+'</td><td class="kpi_lement_number">'+data.elementweightage[newtemp]+'</td> <td class="kpi_lement_inputname">'+data.symbolIndication[newtemp]+'</td> <td class="kpi_lement_inputname">'+data.indicationValue[newtemp]+'</td> <td class="kpi_lement_inputname"><input class="resetclass" placeholder="123" onkeypress="return isNumberKey(event)"  value="'+data.elementvalues[newtemp]+'" type="text" name="newelemetvalues[]" required ></td></tr>').appendTo("#tablebody"+j);
                                }
                                if(temp>1)
                                {
                                    $('<tr ><td class="kpi_lement_name">'+myvalue+'</td><td class="kpi_lement_number">'+data.elementweightage[newtemp]+'</td> <td class="kpi_lement_inputname">'+data.symbolIndication[newtemp]+'</td><td class="kpi_lement_inputname">'+data.indicationValue[newtemp]+'</td> <td class="kpi_lement_inputname"><input class="resetclass" placeholder="123" type="text" name="newelemetvalues[]" onkeypress="return isNumberKey(event)" value="'+data.elementvalues[newtemp]+'" required ></td></tr>').appendTo("#tablebody"+j);
                                }


                            }
                            else
                            {
                                $('#updatebuttonid').hide();
                                $('#savebuttonid').show();
                                $('#adminbuttonid').show();
                                if(temp==1)
                                {
                                    $('<tr><td  class="kpi_lement_name">'+myvalue+'</td><td class="kpi_lement_number">'+data.elementweightage[newtemp]+'</td><td class="kpi_lement_inputname">'+data.symbolIndication[newtemp]+'</td><td class="kpi_lement_inputname">'+data.indicationValue[newtemp]+'</td>  <td class="kpi_lement_inputname"><input class="resetclass" placeholder="123" type="text" name="newelemetvalues[]" required onkeypress="return isNumberKey(event)" ></td></tr>').appendTo("#tablebody"+j);
                                }
                                if(temp>1)
                                {
                                    $('<tr><td class="kpi_lement_name">'+myvalue+'</td><td class="kpi_lement_number">'+data.elementweightage[newtemp]+'</td> <td class="kpi_lement_inputname">'+data.symbolIndication[newtemp]+'</td><td class="kpi_lement_inputname">'+data.indicationValue[newtemp]+'</td> <td class="kpi_lement_inputname"><input class="resetclass" type="text" placeholder="123"  onkeypress="return isNumberKey(event)" name="newelemetvalues[]" required ></td></tr>').appendTo("#tablebody"+j);
                                }


                            }
                            temp++;
                            newtemp++;


                        });
                        $(' </tr></tbody></table>').appendTo('#addkpiformid');


                    }
                    j++;


                });

            },
            error: function(XMLHttpRequest, textStatus, errorThrown)
            {
                window.location.href = base_url+'/login';
            }
        });

    });

    $(".textbox").live("keypress keyup blur",function (event) {
        //this.value = this.value.replace(/[^0-9\.]/g,'');
        $(this).val($(this).val().replace(/[^0-9\.]/g,''));
        if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
    });

    function isNumber(evt, element) {

        var charCode = (evt.which) ? evt.which : event.keyCode

        if (
            (charCode != 45 || $(element).val().indexOf('-') != -1) &&      // â€œ-â€ CHECK MINUS, AND ONLY ONE.
            (charCode != 46 || $(element).val().indexOf('.') != -1) &&      // â€œ.â€ CHECK DOT, AND ONLY ONE.
            (charCode < 48 || charCode > 57))
            return false;

        return true;
    }
    $('#resetbuttonid').click(function($e)
    {
        $e.preventDefault();
        $('.resetclass').val(' ');
    });

    $("#kpiform").submit(function($e){

        var preshipid=$('#shipid').val();
        var btn= $(this).find("input[type=submit]:focus").attr('id');
        $e.preventDefault();
        var formid = $('#kpiform');
        $.ajaxSetup({
            global: false,
            type: "post",
            'url': Routing.generate('addkpivaluesname', {'buttonid':btn}),
            data: formid.serialize(),
            beforeSend: function ()
            {
                var beforsend=ajaxbefore_send();
            },
            complete: function () {
                var complete=ajax_complete();
            }
        });


        $.ajax({
            success: function(data)
            {
                var valuearray=data.elementvalues;
                $('#'+preshipid).after('<img src="/images/tick_icon.png">');
                $('#shipid').val('');
                $('#addkpiformid').html('');
                $('#addkpiformid').text('');
                $('#shipname').text(' ');


                if(data.shipid!=0) {
                    $('#nogroupselected').hide();
                    $('#kpiformforship').show();
                    if (data.shipcount == data.ship_status_done_count)
                    {
                        $('.linkclass').removeClass("active");
                        $('<span id="spanallid">All Ships Data Upload.</span>').appendTo('#elementformid');
                        $('#allbuttondiv').hide();
                    }
                    else
                    {


                        $('#shipid').val(data.shipid);
                        $('#shipname').text(data.shipname);
                        $('.linkclass').removeClass("active");
                        $('#' + data.shipid).addClass('active');

                        var j = 1;
                        var newtemp = 0;
                        $.each(data.kpiNameArray, function (i, listkpi) {

                            var k = $.isNumeric(i);
                            if (!k) {
                                var temp = 1

                                $('<table  class="table kpi_table_form_section"><thead><tr id="firstheader_tr_' + newtemp + '"><th>' + i + '</th></tr></thead> <tbody id="tablebody' + j + '">').appendTo('#addkpiformid');
                                if (newtemp == 0) {
                                    $(' <th class="kpi_value">Wt</th><th class="kpi_value">Symbol</th><th class="kpi_value">Indication</th><th class="kpi_value">Value</th>').appendTo('#firstheader_tr_' + newtemp)
                                }
                                else {
                                    $(' <th></th><th></th><th></th><th></th>').appendTo('#firstheader_tr_' + newtemp)
                                }
                                $.each(listkpi, function (mykey, myvalue) {
                                    if (valuearray.length > 0) {

                                        $('#updatebuttonid').show();
                                        $('#savebuttonid').hide();
                                        $('#adminbuttonid').show();
                                        if (temp == 1) {
                                            $('<tr><td  class="kpi_lement_name">' + myvalue + '</td><td class="kpi_lement_number">' + data.elementweightage[newtemp] + '</td> <td class="kpi_lement_inputname">'+data.elementweightage[newtemp]+'</td><td class="kpi_lement_inputname">'+data.elementweightage[newtemp]+'</td><td class="kpi_lement_inputname"><input class="resetclass" value="' + data.elementvalues[newtemp] + '" placeholder="123" type="text" name="newelemetvalues[]" required></td></tr>').appendTo("#tablebody" + j);
                                        }
                                        if (temp > 1) {
                                            $('<tr><td class="kpi_lement_name">' + myvalue + '</td><td class="kpi_lement_number">' + data.elementweightage[newtemp] + '</td><td class="kpi_lement_inputname">'+data.elementweightage[newtemp]+'</td><td class="kpi_lement_inputname">'+data.elementweightage[newtemp]+'</td> <td class="kpi_lement_inputname"><input class="resetclass" type="text" placeholder="123" value="' + data.elementvalues[newtemp] + '" name="newelemetvalues[]" required ></td></tr>').appendTo("#tablebody" + j);
                                        }
                                    }
                                    else {
                                        $('#updatebuttonid').hide();
                                        $('#savebuttonid').show();
                                        $('#adminbuttonid').show();
                                        if (temp == 1) {
                                            $('<tr><td  class="kpi_lement_name">' + myvalue + '</td><td class="kpi_lement_number">' + data.elementweightage[newtemp] + '</td><td class="kpi_lement_inputname">'+data.elementweightage[newtemp]+'</td><td class="kpi_lement_inputname">'+data.elementweightage[newtemp]+'</td> <td class="kpi_lement_inputname"><input class="resetclass" placeholder="123" type="text" name="newelemetvalues[]" required ></td></tr>').appendTo("#tablebody" + j);
                                        }
                                        if (temp > 1) {
                                            $('<tr><td class="kpi_lement_name">' + myvalue + '</td><td class="kpi_lement_number">' + data.elementweightage[newtemp] + '</td><td class="kpi_lement_inputname">'+data.elementweightage[newtemp]+'</td><td class="kpi_lement_inputname">'+data.elementweightage[newtemp]+'</td> <td class="kpi_lement_inputname"><input class="resetclass" type="text" placeholder="123" name="newelemetvalues[]" required ></td></tr>').appendTo("#tablebody" + j);
                                        }
                                    }
                                    newtemp++;
                                    temp++;

                                });
                                $(' </tbody></table>').appendTo('#addkpiformid');

                            }
                            j++;


                        });
                    }
                    jAlert('',data.returnmsg);
                }
                else
                {
                    $('#shipid').val(' ');
                    $('#shipname').text(' ');
                    $('#nogroupselected').show();
                    $('#kpiformforship').hide();
                    jAlert('',data.returnmsg);
                }


            },
            error: function(XMLHttpRequest, textStatus, errorThrown)
            {
                window.location.href = base_url+'/login';
            }
        });
    });
    $('#previousmonthdata').hide();


});


function isNumberKey(e)
{
    /* var charCode = (evt.which) ? evt.which : event.keyCode
     if (charCode != 46 && charCode > 31
     && (charCode < 48 || charCode > 57))
     return false;

     return true;*/
    if (e.which != 46 && e.which != 45 && e.which != 46 &&
        !(e.which >= 48 && e.which <= 57)) {
        return false;
    }
}