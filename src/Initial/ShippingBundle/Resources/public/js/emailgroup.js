/**
 * Created by lawrance on 28/3/16.
 */
$(document).ready(function()
{
    $('#nogroupselected').hide();
    $('#registercontentid').show();
    $('#viewcontentid').hide();
    $('#updatebuttonid').hide();
    var defalutchecboxid = 'activecheckbox';
    $('#activecheckbox').prop('checked', true);
    $('#activecheckbox').attr("disabled", true);
    /*checboxcheckeddisplay(defalutchecboxid);*/
    var count=1;
    $('#adduserscreenbutton').click(function($e)
        {
            $e.preventDefault();
            $('#nogroupselected').hide();
            $('#addgroupidhtml').val('');
            $('#inputforemailid').text('');
            $('#tabledata').text('');
            $('#viewcontentid').hide();
            $('#updatebuttonid').hide();
            $('#savegroupid').show();
            $('#registercontentid').show();

        });
    $('#cancelbuttonId').click(function($e)
    {
        $e.preventDefault();
        $('#nogroupselected').hide();
        $('#addgroupidhtml').val('');
        $('#emailid').val('');
        $('#tabledata').html('');
        $('#inputforemailid').text('');
        $('#tabledata').text('');
        $('#viewcontentid').hide();
        $('#updatebuttonid').hide();
        $('#savegroupid').show();
        $('#registercontentid').show();

    });
//Script For Add email group
    $('#addemailid').click(function($e)
        {
            $e.preventDefault();
            var email=$('#emailid').val();
            var atpos = email.indexOf("@");
            var dotpos = email.lastIndexOf(".");
            if (atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length)
            {

                jAlert('', "Not a valid e-mail address");

            }
            else
            {
                var b=$("#viewmailtable > tbody > tr").length;
                $('<tr id="'+b+'"><td id="emaildata_'+b+'">'+email+'</td><td><a id="emailedit_'+b+'" class="edit_icon_btn ss-write">&nbsp;</a></td><td><a id="emaildelete_'+b+'" class="delete_icon_btn ss-delete">&nbsp;</a></td></tr>').appendTo('#tabledata');
                $('<input id="inputid_'+b+'" value="'+email+'" class="addedemailclass" type="hidden" name="listofemail[]">').appendTo('#inputforemailid');
                $('#emailid').val('')

            }
        }
    );
//Script For Edit email group
    $('.edit_icon_btn').live('click',function($e)
        {
            $e.preventDefault();
            var id = $(this).attr('id');
            var idarray = id.split('_');
            var data=$('#emaildata_'+idarray[1]).text();
            $('#emailid').val(data)
            $('#'+idarray[1]).remove();
            $('#inputid_'+idarray[1]).remove();
            counter--;
        }
    );
    //Remove email form email group
    $('.delete_icon_btn').live('click',function($e)
        {
            $e.preventDefault();
            var id = $(this).attr('id');
            var idarray = id.split('_');
            $('#'+idarray[1]).remove();
            $('#inputid_'+idarray[1]).remove();
            counter--;
        }
    );
    //Script for Add email group Starts Here
    $("#savegroupid").click(function($e)
        {
            $e.preventDefault();
            var form = $('#addemailgroupform');
            var gname=$('#addgroupidhtml').val();
            var numItems = $('.addedemailclass').length
            if(gname=="")
            {
                jAlert('', 'Group Name Required');
                $('#addgroupidhtml').focus();
                return false

            }
            if(numItems==0)
            {
                jAlert('', 'Email Required');
                $('#emailid').focus();

            }
            if(gname!="" && numItems>0)
            {
                $.ajax({
                    type: "post",
                    data: form.serialize(),
                    url: Routing.generate('emailgroup'),
                    success: function(data)
                    {
                        $('#registercontentid').show();
                        $('#smalltest').text((data.listofuserCount));
                        $('#listgroupcontent').html('');
                        $.each(data.listofusergrop, function (indexvalue) {
                            $('<div title="'+data.listofusergrop[indexvalue]['groupname']+'" style="cursor: pointer" class="users_list_grid"> <span class="user_image"><img src="/images/new_icon.png">' +
                                '</span> <span  class="users_name_row"> <span  id="userlistid_'+data.listofusergrop[indexvalue]['id']+'" class="name">'+data.listofusergrop[indexvalue]['groupname']+'</span><span class="text">&nbsp;</span>' +
                                '</span></div>').appendTo('#listgroupcontent');
                        });
                        $('#viewcontentid').hide();
                        $('#viewtabledata').text('');
                        $('#tabledata').text('');
                        $('#groupname').text('');
                        $('#addgroupidhtml').val('')
                        $('#groupid').val('');
                        $('#inputforemailid').text('');
                        jAlert('', data.savemsg);

                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown)
                    {
                        window.location.href = 'http://shipreports/login';
                    }
                });
            }



        }
    );
    //Script for add email group Ends Here
    //Script for View Particular email group Starts Here
    $('.name').live('click',function($e)
        {
            $e.preventDefault();
            $('#nogroupselected').hide();
            var textboxid = $(this).attr('id');
            var grouptextbox = textboxid.split('_');
            var emailgroupid=grouptextbox[1]
            var datavalue = {emailgroupid : emailgroupid};


            $.ajax({
                type: "post",
                data: datavalue,
                url: Routing.generate('ajaxviewemailgroup'),
                success: function(data)
                {

                    $('#viewtabledata').text('');
                    $('#tabledata').text('');
                    $('#groupname').text('');
                    $('#addgroupidhtml').val('')
                    $('#groupid').val('');
                    $('#inputforemailid').text('');
                    var groupname=data.groupname;
                    $('#groupname').text(groupname);
                    $('#addgroupidhtml').val(groupname);
                    var groupid=data.groupid
                    $('#groupid').val(groupid);

                    $.each(data.groupofemailid, function(i, groupemailid)
                    {
                        var groupStatus=data.groupofemailid[0]['groupstatus'];
                        if(groupStatus==0){
                            $('#archivebuttonid').text('Active');
                        }
                        else {
                            $('#archivebuttonid').text('Archive');
                        }
                        var tablerowcount=$("#viewmailtable > tbody > tr").length;
                        $('<tr><td>'+groupemailid.useremailid+'</td></tr>').appendTo('#viewtabledata');

                        $('<tr id="'+tablerowcount+'"><td id="emaildata_'+tablerowcount+'">'+groupemailid.useremailid+'</td><td></td><td><a id="emaildelete_'+tablerowcount+'" class="delete_icon_btn ss-delete">&nbsp;</a></td></tr>').appendTo('#tabledata');
                        $('<input id="inputid_'+tablerowcount+'" value="'+groupemailid.useremailid+'" class="addedemailclass" type="hidden" name="listofemail[]">').appendTo('#inputforemailid');
                        $('#emailid').val('')



                    });
                    var input = $('#addemailgroupform');
                    input.removeAttr( "action" )
                    $('#viewcontentid').show();
                    $('#registercontentid').hide();
                    $('#nogroupselected').hide();

                },
                error: function(XMLHttpRequest, textStatus, errorThrown)
                {
                    window.location.href = 'http://shipreports/login';
                }
            });

        }
    );
    //Script for View Particular email group Ends Here
    $('#editbuttonid').click(function($e)
    {
        $e.preventDefault();
        $('#viewcontentid').hide();
        $('#registercontentid').show();
        $('#nogroupselected').hide();
        $('#savegroupid').hide();
        $('#updatebuttonid').show();

    });
    //Script for Edit email group Starts Here
    $('#updatebuttonid').click(function($e)
        {
            $e.preventDefault();
            var form = $('#addemailgroupform');
            var gname=$('#addgroupidhtml').val();
            var numItems = $('.addedemailclass').length
            if(gname=="")
            {
                jAlert('', 'Group Name Required');
                $('#addgroupidhtml').focus();
                return false

            }
            if(numItems==0)
            {
                jAlert('', 'Email Required');
                $('.addedemailclass').focus();
                return false

            }
            if(gname!="" && numItems>0) {
                $.ajax({
                    type: "post",
                    data: form.serialize(),
                    url: "/mailing/updatemailgroup",
                    success: function (data) {
                        $('#registercontentid').show();
                        $('#viewcontentid').hide();
                        $('#viewtabledata').text('');
                        $('#tabledata').text('');
                        $('#groupname').text('');
                        $('#addgroupidhtml').val('')
                        $('#groupid').val('');
                        $('#inputforemailid').text('');

                        jAlert('', data.updatemsg);
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        window.location.href = 'http://shipreports/login';
                    }
                });
            }

        }
    );
    //Script for Edit email group Ends Here
    //Script for Archive email group Starts Here
    $('#archivebuttonid').click(function($e)
    {
        $e.preventDefault();
            var groupid = $('#groupid').val();
            var sendingdata = {groupid : groupid};
            $.ajax({
                type: "post",
                data: sendingdata,
                url:  Routing.generate('archivegroup'),
                success: function (data) {
                    $('#registercontentid').show();
                    $('#smalltest').text((data.listofuserCount));
                    $('#listgroupcontent').html('');
                    $.each(data.listofusergrop, function (indexvalue) {
                        $('<div title="'+data.listofusergrop[indexvalue]['groupname']+'" style="cursor: pointer" class="users_list_grid"> <span class="user_image"><img src="/images/new_icon.png">' +
                            '</span> <span  class="users_name_row"> <span  id="userlistid_'+data.listofusergrop[indexvalue]['id']+'" class="name">'+data.listofusergrop[indexvalue]['groupname']+'</span><span class="text">&nbsp;</span>' +
                            '</span></div>').appendTo('#listgroupcontent');
                    });
                    $('#viewcontentid').hide();
                    $('#viewtabledata').text('');
                    $('#tabledata').text('');
                    $('#groupname').text('');
                    $('#addgroupidhtml').val('')
                    $('#groupid').val('');
                    $('#inputforemailid').text('');
                   // alert(data.archivemsg);
                    jAlert('', data.archivemsg);

                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    window.location.href = 'http://shipreports/login';
                }
            });

        }
    );
//Script for Archive email group Ends Here
//While click cancel button do remove the from Starts Here
    $('#cancelbuttonid').click(function($event)
        {
            $event.preventDefault();
            $('#nogroupselected').hide();
            $('#savegroupid').show();
            $('#registercontentid').show();
            $('#viewcontentid').hide();
            $('#updatebuttonid').hide();
        }
    );
    //While click cancel button do remove the from Ends Here

    //Find the group active or inactive checkbox value Starts Here
    $('.inline-checkbox').live('change',function ()
    {
        var checkboxid = $(this).attr('id');
        var chkArray = [];
        $(".inline-checkbox:checked").each(function()
        {
            chkArray.push($(this).val());
        });
        var selected;
        selected = chkArray.join(',') + ",";
        if(selected.length > 1)
        {
            var sendingdata = {checkboxvalue : chkArray};
            $.ajax({
                type: "post",
                data: sendingdata,
                url: "/mailing/ajaxgroupchange",
                success: function (data)
                {
                    $('#countid').text('');
                    $('#countid').text(data.countofgroup);
                    $('#listgroupcontent').text('');
                    $.each(data.listofgroup, function(i, groupemail)
                    {
                        $('<div  class="users_list_grid"> <span class="user_image"><img src="/images/new_icon.png"></span><span  class="users_name_row">' +
                            '<span id="userlistid_'+groupemail.id+'" class="name">'+groupemail.groupname+'</span></span></div>').appendTo('#listgroupcontent');
                    });
                    $('#activecheckbox').attr("disabled", false);
                    if(chkArray.length==2)
                    {
                     $('#archivebuttonid').text('Active/Archive')
                    }
                    else
                    {
                        if(chkArray[0]==0)
                        {
                            $('#archivebuttonid').text('Archive')
                        }
                        else
                        {
                            $('#archivebuttonid').text('Active')
                        }
                    }

                },
                error: function (XMLHttpRequest, textStatus, errorThrown)
                {
                    window.location.href = 'http://shipreports/login';
                }
            });
        }
        else
        {
            alert("Please at least one of the checkbox");
        }

    });
    //Find the group active or inactive checkbox value Ends Here
    function checboxcheckeddisplay(id)
    {
        $('#'+id).prop('checked', true);
        $('#activecheckbox').css('display','block')

    }
    var showCheckBox = $('#showCheckbox');
    var activeInactiveCheckBox = $('#active_inactive_checkbox');
    showCheckBox.click(function ($e)
    {
        $e.preventDefault();
        activeInactiveCheckBox.toggleClass('opened');
    });
    $(document).click(function ($e) {
        if (!$($e.target).parents().andSelf().is('#showCheckbox')) {
            activeInactiveCheckBox.removeClass("opened");
        }
    });
});
