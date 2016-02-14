/**
 * Created by sylvain on 14/02/2016.
 */

$(".questionName").on('click',function(){
    if($(this).next().is(":visible") == false)
    {
        $(this).next().slideDown();
    }
    else
    {
        $(this).next().slideUp();
    }
});

$('.modifQuizzForm').on('submit',function(){
    $.ajax({
        url: $(this).attr('action'),
        type: $(this).attr('method'),
        data: $(this).serialize(),
        success: function(data) {
            if(data == "ok")
            {
                $("#successQuizzSave").fadeIn();

                setTimeout(function(){
                    $("#successQuizzSave").fadeOut();
                },3000)
            }
        }
    });
});
