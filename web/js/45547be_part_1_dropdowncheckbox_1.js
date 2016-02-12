/**
 * Created by lawrance on 5/2/16.
 */
$(function()
{
    $('#shipid').change(function() {

        console.log($(this).val());
    }).multipleSelect({
        width: '10%'
    });
});