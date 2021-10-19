/* ---------------- AJAXOWA OBSŁUGA FORULARZY ------------------- */
var optionsForm = {
    beforeSubmit: defaultShowRequest,
    success: defaultShowResponse,
    error: defaultShowErrorResponse,
    dataType: 'json',
    type: 'post'
};


$(document).ready(function(){
    bindAjaxForm();
});

function bindAjaxForm()
{
    //$('body').find
    $('.ajaxForm').ajaxForm(optionsForm);
}


function defaultShowRequest()
{
    $('.ajaxForm').append('<input type="hidden" name="isXmlHttpRequest" value="1" />');
    $(".ajaxForm input, .ajaxForm select").attr("disabled", true);
    $(".ajaxForm").hide();
    $(".ajaxForm").parent('section').find('div, p, h2').hide();
    $(".ajaxForm").parent('section.login-content').css('visibility', 'hidden');
    $(".ajaxForm").parent('section.register-content').css('visibility', 'hidden');
    if ($(".ajaxForm").parent('section.login-content').length > 0 && $(".ajaxForm").parent('section.register-content').length > 0){
        $('.mcsb').mCustomScrollbar("scrollTo", 300);
    }

    if($('.overlay_block').is(':visible')){
        $('.overlay_content').addClass('loading');
    } else {
        $('.send-to-friendContent section,  .mcsbContact').addClass('loading');
        $('.mcsbRegister .mCSB_container').addClass('loading');

    }

    if($('.iframe-body').length > 0){
        parent.defaultShowRequest();
    }

}

function onResponse(){
    $(".ajaxForm input, .ajaxForm select").attr("disabled", false);
    $(".ajaxForm").parent('section').find('div, p, h2').show();
    $(".ajaxForm").parent('section.login-content').css('visibility', 'visible');
    $(".ajaxForm").parent('section.register-content').css('visibility', 'visible');

    $(".ajaxForm").show();

    if($('.overlay_block').is(':visible')){
        $('.overlay_content').removeClass('loading');
    } else {
        $('.send-to-friendContent section,  .mcsbContact').removeClass('loading');
        $('.mcsbRegister .mCSB_container').removeClass('loading');

    }

    // $(window).load(function(){
    //     mcsbGoToTop();
    // })

}

function defaultShowResponse(response)
{
    // przekierowanie na inną stronę
    if( response.reload ){
        window.location.reload(true);
        return; 
    }
    
    // przekierowanie na inną stronę
    if( response.redirect ){
        window.location = response.redirect;
        return; 
    }

    onResponse();

    if($('.iframe-body').length > 0){
        parent.onResponse();
    }

    // odebranie odpowiedzi z aplikacji
    if( response.messageType == 'html' ){
        //usuwamy formularz - w przeciwnym razie dublujemy content i id
        $('#'+response.content_id).parent().find('.message-error').remove();
        $(response.message).insertAfter($('#'+response.content_id));
        $('#'+response.content_id).remove();
        //$('#'+response.content_id).html(response.message);
        $('#'+response.pagination_id).html(response.pagination);
        bindAjaxForm();
        fitMapToScreen();
        
       if (typeof(refreshMcsbContact) === 'function') { refreshMcsbContact(); }

    } else {
        $(".ajaxForm input, .ajaxForm select").removeAttr("disabled");

    }
    $(".mcsbRegister").mCustomScrollbar("scrollTo", "top");

    if ($(".ajaxForm").parent('section.login-content').length > 0 && $(".ajaxForm").parent('section.register-content').length > 0){
        $('.register-extended').hide();
        $('.register-content').height($('#UserRegisterForm').height());
        $('.mcsb').mCustomScrollbar('update');
        $('.register-extended-header').off('click');
        $('.register-extended-header').click(function(){
            $('.register-extended').slideToggle(500, function(){
                $('.register-content').height($('#UserRegisterForm').height() + 360); //obejście hacka z default js
            });
        });
        $('label[for="UserRegister-accept_data"]').shorten({
            showChars: 53,
            moreText: 'rozwiń',
            lessText: 'zwiń'
        });

        $('label[for="UserRegister-accept_marketing"]').shorten({
            showChars: 53,
            moreText: 'rozwiń',
            lessText: 'zwiń'
        });
        $('a.morelink').click(function(){
            if($(this).hasClass('less')){
                $('.register-content').height($('#UserRegisterForm').height() + 280); //obejście hacka z default js
            } else{
                $('.register-content').height($('#UserRegisterForm').height() + 390); //obejście hacka z default js
            }
            $('.mcsb').mCustomScrollbar('update');
        });
        $('select.companyPosition').change(function(){
            var value = $(this).find('option:selected').val();
            if(value  >1 && value < 5){
                $('select.companyPositionDetail2').parents('.form-item').hide();
                $('select.companyPositionDetail').parents('.form-item').show('fast');
                $('.register-content').height($('#UserRegisterForm').height() + 320);
                $('.mcsb').mCustomScrollbar('update');

            } else if(value == 5 ) {
                $('select.companyPositionDetail').parents('.form-item').hide();
                $('select.companyPositionDetail2').parents('.form-item').show('fast');
                $('.register-content').height($('#UserRegisterForm').height() + 320);
                $('.mcsb').mCustomScrollbar('update');

            } else {
                $('select.companyPositionDetail').parents('.form-item').hide('fast');
                $('select.companyPositionDetail2').parents('.form-item').hide('fast');
            }
        }).change();
    }

}

function defaultShowErrorResponse(){
    $(".ajaxForm input").removeAttr("disabled");
    $(".mcsbRegister").mCustomScrollbar("scrollTo", "top");
}

/* ---------------- AJAXOWA OBSŁUGA FORULARZY ------------------- */
