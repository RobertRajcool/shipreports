/**
 * Created by lawrance on 22/1/16.
 */



$(document).ready(function()

{
    $('#readingid').hide();
    $('#overlay').hide();
    $("#form_excel").submit(function()
    {
        var uploadedFile = document.getElementById('fileid');
        var mimetype = uploadedFile.files[0].type;
        var match= ["application/vnd.ms-excel","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"];
        if(!((mimetype==match[0]) || (mimetype==match[1])))
        {
            alert('Choose Valid File......');
            return false;
        }
        else
        {
            //$('#submitid').hide();
            //$('#readingid').show();
            return true;

        }



    });


    $('#submitid').click(function()
    {
        var uploadedFile = document.getElementById('fileid');
        var mimetype = uploadedFile.files[0].type;
        var match= ["application/vnd.ms-excel","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"];
        if(!((mimetype==match[0]) || (mimetype==match[1])))
        {
            alert('Choose Valid File......');
            return false;
        }
        else
        {
            $('#submitid').hide();
            $('#readingid').show();
            $('#overlay').show();
        }


    });




    $("#fileid").change(function() {
        var file = this.files[0];

        var imagefile1 = file.type;


        var match= ["application/vnd.ms-excel","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",];
        if(!((imagefile1==match[0]) || (imagefile1==match[1])))
        {
            alert('Choose Valid File......')
            return false;
        }
        else
        {

            return true;
        }
    });


    function validation()
    {
        var file = $("#fileid").val();


        if (file === '' )
        {
            alert("Please fill all fields...!!!!!!");
            return false;
        }
        else {
            return true;
        }
    }








});