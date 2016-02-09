$(document).ready(function(){
    //alert("Hi");
    var data = 5;
    $.ajax({
        type: "POST",
        data: data,
        url: "/shipping_development/web/app_dev.php/companydetails/newcompany",
        success: function(data)
        {
            //alert("inside success");
            $.each(data.companyNameArray, function(i, listcompany) {

                $('#selectid').append($('<option>', {
                    value: listcompany.id, text : listcompany.companyName
                }));
            });

        },
        error: function(XMLHttpRequest, textStatus, errorThrown)
        {
            alert('Error : ' + errorThrown);
        }
    });
    $("#selectid").change(function(){
        var data = {selectid : $('#selectid').val()};
        alert(data.selectid);

        if($(this).val())
        {

            $.ajax({
                type: "POST",
                data: data,
                url: "/shipping_development/web/app_dev.php/companydetails/newadmin",
                success: function(data)
                {
                    alert(data);
                    var email = JSON.stringify(data.adminNameArray);
                    alert(email);
                    $('#email_id').val(data.adminNameArray.emailId);
                    $('#name_id').val(data.adminNameArray.adminName);

                },
                error: function(XMLHttpRequest, textStatus, errorThrown)
                {
                    alert('Error : ' + errorThrown);
                }
            });
        }
    })
});