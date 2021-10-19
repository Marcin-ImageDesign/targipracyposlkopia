(function( $ ){

    
    $.fn.briefcase = function(is_briefcase_page){
        var extraInfo = {"handler":this,
                         "fileAdded":'favorite_added.png',
                         "fileToAdd":'favorite_add.png'
        };    
        
        $(this).bind('click',extraInfo,function(e){
        var uri = $(this).attr('href');
        if(uri){
            e.preventDefault();
            $.ajax( {
                url:uri,
                type:'post',
                dataType: 'json',
                async:false,
                data: { emptyData: "1" },
                success:function(responseResult){
                    var jqObject = $(extraInfo.handler);
                    jqObject.show();
                    jqObject.parent().children('a.briefcase_preloader').remove();  
                    jqObject.parent().children('div.preloader_briefcase').remove();  
                    var iObject = jqObject.children('img').first();
                    var lObject = jqObject.children('span').first();
                    
                    if(responseResult.remove == 1) {
                        jqObject.removeClass('remove_element').addClass('add_element').text(responseResult.title);
                    } else {
                        jqObject.removeClass('add_element').addClass('remove_element').text(responseResult.title);
                    }
                    
                    var link = typeof(responseResult.link)=='undefined' ? jqObject.attr('href') : responseResult.link;
                    var title = 
                        typeof(responseResult.title)=='undefined' ? 
                                                    typeof(jqObject.attr('title'))=='undefined' ? jqObject.attr('title') : ''
                                                        : responseResult.title;

                    jqObject.attr('href',link);
                    jqObject.attr('title',title);
                    if(typeof(iObject)=='undefined'){
                        throw "Briefcase: Invalid html structure?";
                    }
                    if(iObject.size() != 1){
                        throw "Briefcase: To many/few <img> elements";
                    }
                    if(typeof(responseResult.remove)!='undefined'){
                        jqObject.closest('li').remove();
                        iObject.attr('src',iObject.attr('src').toString().replace(extraInfo.fileAdded,extraInfo.fileToAdd));
                         lObject.text(title);
                        return this;
                    }
                    
                    iObject.attr('src',iObject.attr('src').toString().replace(extraInfo.fileToAdd,extraInfo.fileAdded));
                    lObject.text(title);
                    
                },
                beforeSend:function(){
                    var jqObject = $(extraInfo.handler);
                    if( is_briefcase_page === true)
                        jqObject.parent().append('<a href="#" class="briefcase_preloader"><img style="margin-left: 7px; width: 16px;" src="/private/base_user/_vtargi.pl/_images/loading.gif"></a>');
                    else
                        jqObject.parent().append('<div class="preloader_briefcase"></div>');
                    jqObject.hide();
                }
            });
            
        }
        return false;
        });
        return this;
    }
})(jQuery);

function runBriefcase() {
    $('.add_element').each(function() {
        $(this).briefcase();
    });

    $('.remove_element').each(function() {
        $(this).briefcase();
    });
}



var fitMapToScreenCount = 0;
function fitMapToScreen() {
    var documentH = parseInt($('body').height());
    var windowH = parseInt($(window).height());
    var calc;
    var nav = $('section.content nav');
    var section = $('section.content section, section .map .wrapper');
    var mcsbOther = $('.mcsbOther');
    var mcsbRight = $('section.content section .mcsb, section.content section .mCustomScrollbar');
    var padding_factor = (parseInt(section.css('padding-top')) + parseInt(section.css('padding-bottom')));

    
    //dla recepcji wyłączamy resize - kiepsko wygląda
    if (section.parent().hasClass('receptionContent')){
        return;
    }

    if(windowH > documentH) {
        calc = windowH-documentH;

        if (calc > 0){
            nav.css({height:'+='+calc});
            section.css({height:'+='+calc});
        }
    }else{
        //ustalamy minimalną wysokość. W przeciwnym wypadku znikają elementy
        if (section.parent().hasClass('stand-productSection')){
            section_h = 590 - padding_factor;
            nav.css('height', 570);
            section.css('height', section_h); 
        }else{
            section_h = 530 - padding_factor;
            nav.css('height', 510);
            section.css('height', section_h);
        }
    }
    //hack dla formularza logowania
    if ($('#UserRegisterForm').length > 0){
        $('.register-content').height(($('#UserRegisterForm').height()));
        $('.login-content').height(($('#UserRegisterForm').height()));
    }
    $('.mcsbOther').css({height:(nav.height() - 169)});
    $('.mcsbOther').mCustomScrollbar("update");

    $('section.content section .mcsb, section.content section .mCustomScrollbar').css({height:(section.height() - 47)});
    $('section.content section .mcsb, section.content section .mCustomScrollbar').mCustomScrollbar("update");

    $('#source_wrapper').css({height:($('section.content section, section .map .wrapper').height() - 47)});
    $('#source_wrapper').mCustomScrollbar("update");
    //$('#source_wrapper, .mCustomScrollbar').mCustomScrollbar("update");
    
    if(fitMapToScreenCount == 0) fitMapToScreenCount += 1;
    
}

var partnersItemsWidth = 0;
var itemsWidthArr = new Array();
var slideBlock=false;
var currentSliderItem = 0;

function partnersSliderInit() {
    
    var autoSlide = false;
    
    $('.partnersSlider .partnerItem').each(function(i,v) {
        partnersItemsWidth += parseInt($(this).outerWidth(true));
        itemsWidthArr[i] = parseInt($(this).outerWidth(true));
    });
    /*$.each(itemsWidthArr, function(i,v) {
        alert(i+' - > '+itemsWidthArr[i]);
    });*/
    
    if(autoSlide) {
        autoPlay();
    }
}

function autoPlay() {
    $.doTimeout( 'autoSlide', 5000, function(){
        $('.partnersSlider .next').click();
        return true;
    });
    $('.partnersSlider').hover(function() {
            $.doTimeout('autoSlide');
    }, function() {//hoverout
            autoPlay();
    });
}

function prevSlide() {
    if(!slideBlock) {
        if(parseInt($('.partnersSlider .partnersSliderWrapper').css('left')) < 0) {
            slideBlock = true;
            var sliderSize = 0;
            
            if(currentSliderItem > 0)
            {
                sliderSize  = itemsWidthArr[currentSliderItem];
                currentSliderItem--;
            }
            else{
                currentSliderItem = itemsWidthArr.length;
            }
            
            $('.partnersSlider .partnersSliderWrapper').animate({left: '+='+sliderSize}, '500', function() {
                slideBlock = false;
            });
        } else {
            slideBlock = true;
            $('.partnersSlider .partnersSliderWrapper').animate({left: 0}, '500', function() {
                slideBlock = false;
            });
        }
    }
}

function nextSlide() {
    if(!slideBlock) {
        if(parseInt($('.partnersSlider .partnersSliderWrapper').css('left')) > -(partnersItemsWidth-936)) {
            slideBlock = true;
            var sliderSize = 0;
            if(currentSliderItem < (itemsWidthArr.length -1))
            {
                sliderSize  = itemsWidthArr[currentSliderItem];
                currentSliderItem++;
            }
            else{
                currentSliderItem = 0;
            }
            $('.partnersSlider .partnersSliderWrapper').animate({left: '-='+sliderSize}, '500', function() {
                slideBlock = false;
            });
        } else {
            slideBlock = true;
            $('.partnersSlider .partnersSliderWrapper').animate({left: 0}, '500', function() {
                slideBlock = false;
            });
        }
    }
}



var defaultPosChat;
var replaceChatContent = 0;
var cBtn = $('div.toggleChat a.toggleChat');
var cCont = $('div.toggleChat');
var cloud = $('<div class="lets_talk"><span class="chat_text">'+window.chat_trans+'</span></div>');
var close_cloud = $('<div class="close_cloud"></div>');
var chatGroupStr; // = '';
var chatDefaultMsg = $('a.toggleChat').html();
var lastChatSession;
var groupName;

function toggleChat(group, rhino_url) {

        $.ajax({
            type:"GET",
            url: rhino_url+'/api.php',
                dataType: 'json',
                success:function(response){
                    for(var i in response){
                        if(response[i].id == group){
                            replaceChatContent = 1;
                            groupName = response[i].title;
                        }
                    }
                }
                
        })

    var iframeCode = '<iframe src="'+rhino_url+'/index.php?p=start&lang=pl&slide=1&dep='+group+'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:325px; height:520px;" allowTransparency="true"></iframe>';
	defaultPosChat = parseInt(cCont.css('bottom'));
        
	cBtn.bind('click', function() {
        var chatAnchor = replaceChatContent == 1? -80: 45;

        //label z kim gadamy na czacie
        //lastChatSession = getCookie('active_chat');
        if (lastChatSession !== undefined && lastChatSession.length > 0){
            chatGroupStr = lastChatSession;
        }else if (groupName !== undefined) {
            chatGroupStr = groupName;
            createCookie('active_chat', chatGroupStr, 60);
        }

		if(parseInt(cCont.css('bottom')) < chatAnchor) {
            cloud.hide();
            close_cloud.hide();
			cCont.animate({bottom: chatAnchor}, 500, 'easeOutBack');
                        if(replaceChatContent){
                            $('div.controlChat').html(iframeCode);
                        }
                        $('a.toggleChat').addClass('open');
						if (chatGroupStr !== undefined) {
                            $('a.toggleChat').html('Rozmawiasz z: ' + chatGroupStr);
						} else {
							$('a.toggleChat').html('Rozmawiasz z Wystawcą');
						}
                        createCookie(cookie_name, 31, 10);
		} else {
			cCont.animate({bottom: defaultPosChat}, 700, 'easeOutBack');
                        //$('div.controlChat').html('');
                        $('a.toggleChat').removeClass('open');
		}
	});
}

function checkForClose(){
    if(window.location.hash == "#closeChat"){
        closeChat();
    }else{
        setTimeout(checkForClose, 300);
    }
}

setTimeout(checkForClose, 300);

function closeChat(){
    var cBtn = $('div.toggleChat a.toggleChat');
    cBtn.trigger('click');
    $('a.toggleChat').html(chatDefaultMsg);
    eraseCookie('active_chat');
    window.location.hash ='';
    setTimeout(checkForClose, 300);
}

function initializeCloud(){

    cloud.insertBefore('div.toggleChat');
    close_cloud.insertBefore('div.toggleChat');
    cloud.click(function(){
        cloud.hide();
        close_cloud.hide();
        cBtn.trigger('click');
    })
    close_cloud.click(function(){
        close_cloud.fadeOut(350);
        cloud.fadeOut(350);
    })
}

function createCookie(name,value,mins) {
    if (mins) {
        var date = new Date();
        date.setTime(date.getTime()+(mins*60*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
}

function getCookie(c_name) {
    if (document.cookie.length > 0) {
        c_start = document.cookie.indexOf(c_name + "=");
        if (c_start != -1) {
            c_start = c_start + c_name.length + 1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) {
                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return "";
}

function eraseCookie(name) {
    createCookie(name,"",-1);
}

function clearStandCookies(){
    // Get an array of all cookie names (the regex matches what we don't want)
    var cookieNames = document.cookie.split(/=[^;]*(?:;\s*|$)/);

    // Remove any that match the pattern
    for (var i = 0; i < cookieNames.length; i++) {
        if (/^standVisit_/.test(cookieNames[i])) {
            document.cookie = cookieNames[i] + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
        }
    }
}

$(document).ready(function() {
    $('#eName').keypress(function(event) {
        if ( event.which == 13 ) {
            event.preventDefault();
            exhibitorsDataFilter();
        }
    });
    runBriefcase();
    // fitMapToScreen();

    $("section.content div.mcsbOther, section.content div.mcsb").mCustomScrollbar({
        autoDraggerLength:true,
        scrollButtons:{
            enable:false
        },
        advanced:{
            updateOnContentResize:true
        }
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
    // toggleChat();
});

$(window).load(function() {
    $("section.content div.mcsbOther, section.content div.mcsb").mCustomScrollbar('update');
    
    // DISABLED SLIDER
    //partnersSliderInit();
    
    fitMapToScreen();

    if (replaceChatContent === 1){
        initializeCloud();
    }

});
