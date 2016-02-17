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
});

function modifQuestionSuccessCallBack(responseText,status,xhr,form)
{
    if((responseText != null || responseText != undefined) && responseText != 'ok')
    {
        form.clearForm();

        if($('.noQuestion').is(':visible'))
        {
            $('.noQuestion').hide()
        }

        $('#quizzAllQuestions').append(responseText);

        var nbQuestion = $('#quizzAllQuestions .quizzQuestion').length+1;
        var title = $('#newQuestionTitle');
        var replacePos = title.html().search('n°')+2;

        title.html(title.html().substring(0,replacePos)+nbQuestion);

        form.find('.validSuccess').fadeIn();

        setTimeout(function(){
            form.find('.validSuccess').fadeOut();
        },3000);

        $("#quizzAllQuestions .questionName").last().on('click',displayQuestion);
    }
    else if(responseText == 'ok')
    {
        form.find('.validSuccess').fadeIn();

        setTimeout(function(){
            form.find('.validSuccess').fadeOut();
        },3000);
    }
}

function setDeleteImage(elem)
{
    elem.parent().parent().find('#deleteImg').val(1);
    elem.parent().remove();
}
