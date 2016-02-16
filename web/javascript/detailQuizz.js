/**
 * Created by sylvain on 14/02/2016.
 */

$(".questionName").on('click',displayQuestion);

function displayQuestion()
{
    if($(this).next().is(":visible") == false)
    {
        $(this).next().slideDown();
    }
    else
    {
        $(this).next().slideUp();
    }
}

$('.modifQuizzForm').on('submit',function(event){
    event.preventDefault();
    $(this).ajaxSubmit({ success: modifQuizzSuccessCallBack });
    /*$.ajax({
        url: $(this).attr('action'),
        type: $(this).attr('method'),
        data: $(this).serialize(),
        success: function(data) {
            if(data == "ok")
            {
                $("#successQuizzSave").fadeIn();

                setTimeout(function(){
                    $("#successQuizzSave").fadeOut();
                },3000);

                $('#quizzTitle').html($('#appbundle_quizz_title').val());
            }
            else if(data == 'error')
            {
                $("#errorQuizzSave").fadeIn();

                setTimeout(function(){
                    $("#errorQuizzSave").fadeOut();
                },10000)
            }
        }
    });*/
});

function modifQuizzSuccessCallBack(responseText,status,xhr,form)
{
    if(responseText == "ok")
    {
        $("#successQuizzSave").fadeIn();

        setTimeout(function(){
            $("#successQuizzSave").fadeOut();
        },3000);

        $('#quizzTitle').html($('#appbundle_quizz_title').val());
    }
    else if(responseText == 'error')
    {
        $("#errorQuizzSave").fadeIn();

        setTimeout(function(){
            $("#errorQuizzSave").fadeOut();
        },10000)
    }
}

$('.modifQuestionForm').on('submit',function(event){
    event.preventDefault();
    $(this).ajaxSubmit({ success: modifQuestionSuccessCallBack });
    /*$.ajax({
        url: $(this).attr('action'),
        type: $(this).attr('method'),
        data: $(this).serialize(),
        success: function(data) {
            if((data != null || data != undefined) && data != 'ok')
            {
                if($('.noQuestion').is(':visible'))
                {
                    $('.noQuestion').hide()
                }

                var title = $('#newQuestionTitle');
                var replacePos = title.html().search('n°');

                title.html(title.html().substring(0,replacePos)+$('#nbQuestion').val());

                $('#quizzAllQuestions').append(data);

                $(this).find('.validSuccess').fadeIn();

                setTimeout(function(){
                    $(this).find('.validSuccess').fadeOut();
                },3000)

                $(".questionName").on('click',displayQuestion);
            }
        }
    });*/
});

function modifQuestionSuccessCallBack(responseText,status,xhr,form)
{
    if((responseText != null || responseText != undefined) && responseText != 'ok')
    {
        if($('.noQuestion').is(':visible'))
        {
            $('.noQuestion').hide()
        }

        var title = $('#newQuestionTitle');
        var replacePos = title.html().search('n°');

        title.html(title.html().substring(0,replacePos)+$('#nbQuestion').val());

        $('#quizzAllQuestions').append(responseText);

        form.find('.validSuccess').fadeIn();

        setTimeout(function(){
            form.find('.validSuccess').fadeOut();
        },3000);

        $(".questionName").on('click',displayQuestion);
    }
}
