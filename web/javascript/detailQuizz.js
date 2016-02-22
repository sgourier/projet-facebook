/**
 * Created by sylvain on 14/02/2016.
 */

$(".questionName").on('click',displayQuestion);

function displayQuestion()
{
    var wasVisible = false;
    if($(this).next().is(':visible'))
    {
        wasVisible = true;
    }
    $(".questionName").each(function(){
        $(this).next().slideUp();
    });

    if(wasVisible == false)
    {
        $(this).next().slideDown();
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

function removeQuestion(elem,path)
{
    if(confirm('Attention, cette action est irreversible. Continuer ?'))
    {
        $.ajax({
            url: path,
            method: 'POST'
        }).done(function(data)
        {
            if(data == "ok")
            {
                elem.parents('.quizzQuestion').nextAll('.questionName ').each(function(){
                    var text = $(this).html();
                    var oldNb = text.substring(text.search('n°')+2);
                    var newNb = oldNb - 1;
                    $(this).html(text.replace(oldNb,newNb))
                });

                var text = $('#newQuestionTitle').html();
                var oldNb = text.substring(text.search('n°')+2);
                var newNb = oldNb - 1;
                $('#newQuestionTitle').html(text.replace(oldNb,newNb));

                elem.parents('.quizzQuestion').prev().remove();
                elem.parents('.quizzQuestion').remove();
            }
        });
    }
}

function setDeleteImage(elem)
{
    elem.parent().parent().find('#deleteImg').val(1);
    elem.parent().remove();
}

$(window).bind('scroll', function () {
    if ($(window).scrollTop() > 50) {
        $('.navBack').addClass('fixed');
    } else {
        $('.navBack').removeClass('fixed');
    }
});

$('#deleteQuizz').on('click',function(e){
    if(!confirm('Attention, cette action est irreversible. Continuer ?'))
    {
        e.preventDefault();
    }
});