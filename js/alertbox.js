/**
 * Created with JetBrains PhpStorm.
 * User: storoj
 * Date: 14.01.13
 * Time: 3:10
 * To change this template use File | Settings | File Templates.
 */

function alertbox(content, type, autohide, timeout)
{
    if(typeof autohide === 'undefined'){
        autohide = true;
    }
    var hide_timeout = 4000;
    if (!isNaN(parseInt(timeout))) hide_timeout = parseInt(timeout);
    autohide = !!autohide;
    type = typeof type !== 'undefined' ? type : '';

    if(type != '') {
        type = 'alert-'+type;
    }
    var time = new Date().getTime();
    var itemID = 'notification-'+time;
    var alertHTML = $('<div class="alert" id="'+itemID+'">'
        + '<button type="button" class="close" data-dismiss="alert">&times;</button>'
        + content
        + '</div>');

    alertHTML.addClass(type);
    var h = $(window).height();
    var dh = $(document).height();
    var w = $(window).width();
    var hidden = dh - h, bottom = 20;
    if (hidden < 122) {
        bottom += 122 - hidden;
    }
    $('#alertsContainer')
        .prepend(alertHTML);

    autohide && setTimeout(function(){
        $('#'+itemID).fadeOut(900, function(){
            $(this).remove();
        })
    }, hide_timeout);
    $('.alert .close').click(function(){
        $(this).parent().remove();
    });
}

function alert(message, autohide)
{
    alertbox(message, 'error', autohide)
}

$(function(){
    if ($('#alertsContainer').length == 0) {
        $('body').prepend('<div id="alertsContainer"></div>');
    }
});