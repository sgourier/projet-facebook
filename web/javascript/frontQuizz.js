/**
 * Created by Sylvain Gourier on 20/02/2016.
 */

var responses = [];

$(document).ready(function(){
    $('.responseButton').on('click',setValue);
});

function setValue()
{
    $(this).next().click();
    if($(this).next().is(':checked'))
    {
        $(this).css('border','3px solid #DE1256');
    }
    else
    {
        $(this).css('border','3px solid #69cbaa');
    }
}

function nextQuestion(path)
{
    var values = [];
    var idQuestion = $('#idQuestion').val();
    var idResponse;
    var valueResponse;
    $(".responseInput").each(function ()
    {
        idResponse = $(this).next().val();

        if($(this).is(':checked'))
        {
            valueResponse = 1;
        }
        else
        {
            valueResponse = 0;
        }
        values.push([idResponse,valueResponse]);
    });

    responses.push(values);

    $.ajax({
        url: path,
        method: 'POST'
    }).done(function(data){
        $('#questionContainer').html(data);
        $('.responseButton').on('click',setValue);
    });
}

function submitQuizz(path)
{
    $.ajax({
        url: path,
        method: 'POST',
        data: 'responses=' + JSON.stringify(responses)
    }).done(function(data){
        $('#contentQuizz').html(data);
    });
}