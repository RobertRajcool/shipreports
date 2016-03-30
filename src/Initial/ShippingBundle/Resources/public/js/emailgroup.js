/**
 * Created by lawrance on 28/3/16.
 */
$(document).ready(function()
{
    $('#registercontentid').hide();
    $('#viewcontentid').hide();

    var counter = 1;


    $('#adduserscreenbutton').click(
        function()
        {
            $('#nogroupselected').hide();
            $('#addgroupidhtml').val('');
            $('#inputforemailid').text('');
            $('#tabledata').text('');
            $('#viewcontentid').hide();
            $('#updatebuttonid').hide();
            $('#savegroupid').show();
            $('#registercontentid').show();

        }
    );
//Script For Add email group
    $('#addemailid').click
    (
        function()
        {
            var email=$('#emailid').val();
            var atpos = email.indexOf("@");
            var dotpos = email.lastIndexOf(".");
            if (atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length)
            {
                alert("Not a valid e-mail address");

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
    $('.edit_icon_btn').live('click',
        function()
        {
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
    $('.delete_icon_btn').live('click',
        function()
        {
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
                alert("Group Name Required");
                return false

            }
            if(numItems==0)
            {
                alert('Email Required');
                return false

            }
            if(gname!="" && numItems>0)
            {


                $.ajax({
                    type: "post",
                    data: form.serialize(),
                    url: "/mailing/emailgroup",
                    success: function(data)
                    {
                        $('#registercontentid').hide();
                        $('#viewcontentid').hide();
                        $('#viewtabledata').text('');
                        $('#tabledata').text('');
                        $('#groupname').text('');
                        $('#addgroupidhtml').val('')
                        $('#groupid').val('');
                        $('#inputforemailid').text('');
                        alert(data.savemsg);

                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown)
                    {
                        alert('Error : ' + errorThrown);
                    }
                });
            }



        }
    );
    //Script for add email group Ends Here
    //Script for View Particular email group Starts Here
    $('.name').live('click',
        function()
        {
            $('#nogroupselected').hide();
            var textboxid = $(this).attr('id');
            var grouptextbox = textboxid.split('_');
            var emailgroupid=grouptextbox[1]
            var datavalue = {emailgroupid : emailgroupid};


            $.ajax({
                type: "post",
                data: datavalue,
                url: "/mailing/ajaxviewemailgroup",
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
                    alert('Error : ' + errorThrown);
                }
            });

        }
    );
    //Script for View Particular email group Ends Here
    $('#editbuttonid').click(function()
    {
        $('#viewcontentid').hide();
        $('#registercontentid').show();
        $('#nogroupselected').hide();
        $('#savegroupid').hide();
        $('#updatebuttonid').show();

    });
    //Script for Edit email group Starts Here
    $('#updatebuttonid').click(
        function($e)
        {
            $e.preventDefault();
            var form = $('#addemailgroupform');
            var gname=$('#addgroupidhtml').val();
            var numItems = $('.addedemailclass').length
            if(gname=="")
            {
                alert("Group Name Required");
                return false

            }
            if(numItems==0)
            {
                alert('Email Required');
                return false

            }
            if(gname!="" && numItems>0) {


                $.ajax({
                    type: "post",
                    data: form.serialize(),
                    url: "/mailing/updatemailgroup",
                    success: function (data) {
                        $('#registercontentid').hide();
                        $('#viewcontentid').hide();
                        $('#viewtabledata').text('');
                        $('#tabledata').text('');
                        $('#groupname').text('');
                        $('#addgroupidhtml').val('')
                        $('#groupid').val('');
                        $('#inputforemailid').text('');
                        alert(data.updatemsg);

                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        alert('Error : ' + errorThrown);
                    }
                });
            }

        }
    );
    //Script for Edit email group Ends Here
    //Script for Archive email group Starts Here
    $('#archivebuttonid').click(function($e){
            $e.preventDefault();

            var groupid = $('#groupid').val();
            var sendingdata = {groupid : groupid};
            $.ajax({
                type: "post",
                data: sendingdata,
                url: "/mailing/archivegroup",
                success: function (data) {
                    $('#registercontentid').hide();
                    $('#viewcontentid').hide();
                    $('#viewtabledata').text('');
                    $('#tabledata').text('');
                    $('#groupname').text('');
                    $('#addgroupidhtml').val('')
                    $('#groupid').val('');
                    $('#inputforemailid').text('');
                    alert(data.archivemsg);

                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert('Error : ' + errorThrown);
                }
            });

        }
    );
//Script for Archive email group Ends Here
//While click cancel button do remove the from Starts Here
    $('#cancelbuttonid').click(function($event)
        {
            $event.preventDefault();
            $('#registercontentid').hide();
            $('#viewcontentid').hide();
            $('#nogroupselected').show();
        }
    );
    //While click cancel button do remove the from Ends Here
});
