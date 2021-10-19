var mcsbOther = $('.mcsbOther');

/* --- */

function mcsbGoToTop() {
    $('div.mcsbOther .mCSB_container').css({top:0});
}

function toggleSearchPanel() {
    var minBtn = $('a.minBtn');
    var exhibitorSP = $('.exhibitorSearchPanel');
    var panelH = 138;
    
    if(minBtn.hasClass('minBtnExpand')) {
        minBtn.html(trans_hide_filter).removeClass('minBtnExpand').addClass('minBtnCollapse');
        $('.mcsbOther').css({height:'-='+panelH});
        exhibitorSP.css({height: 108, display:'block'});
    } else {
        minBtn.html(trans_show_filter).removeClass('minBtnCollapse').addClass('minBtnExpand');
        $('.mcsbOther').css({height:'+='+panelH})
        exhibitorSP.css({height: 0, display:'none'});
    }
    $('.mcsbOther').mCustomScrollbar("update");
    //mcsbOther.mcsbOther("scrollTo","top"); // nie działała w jednym przypadku - rzadkim
    
}

function exhibitorsDataFilter() {
    var eName = $.trim($('#eName').val());
    var eBrand = $.trim($('#eBrand').val());
    var eRegion = $.trim($('#eRegion').val());
    var eActiveChat = $('#eActiveChat').is(':checked');
    var strName, strKeys, strDesc, strBrand, strRegion, strAllRegion, strActiveChat;
    
    var strKeysFlag;
    
    var eNameArr = [];
    
    // remove extra spaces
    eName = eName.replace(/[\s]+/g, ' ');   // g = replace all instances

    eNameArr = eName.split(' ');
    
    
    var urlsArr = [];
    
    $('div.exhibitorMscb .mCSB_container ul li a').each(function() {
        strName = $(this).html();
        strKeys = $.trim($(this).parent().find('input.hKeysDesc').val());
        strKeys = strKeys.replace(/[\s]+/g, ' ');   // remove extra spaces
        strKeys = strKeys.replace(',', ' ');
        strKeys = strKeys.replace(';', ' ');
        
        strDesc = $(this).parent().find('input.hShortDesc').val();
        strBrand = $(this).parent().find('input.hBrand').val();
        strRegion = $(this).parent().find('input.hRegion').val();
        strAllRegion = $(this).parent().find('input.hAllRegion').val();
        strActiveChat = $(this).find('span.chatIcon');
        
        if(eNameArr.length > 0) {
            strKeysFlag = false;
            $.each(eNameArr, function(i, v) {
                if(strKeys.toLowerCase().search(v.toLowerCase()) >= 0) {
                    strKeysFlag = true;
                } else {
                    strKeysFlag = false;
                    return false;
                }
            });
        }
        
        if (strActiveChat.hasClass('chatAvalibleIcon ')) 
          activeChatFlag = 1; 
        else 
           activeChatFlag = 0;
        
        var thisHref = $(this).attr('href');
        
        if(
            (strKeysFlag || strName.toLowerCase().search(eName.toLowerCase()) >= 0 || strDesc.toLowerCase().search(eName.toLowerCase())) >= 0 &&
            (strBrand.toLowerCase().search(eBrand.toLowerCase()) >= 0) &&
            (strRegion.toLowerCase().search(eRegion.toLowerCase()) >= 0 || strAllRegion == 1) &&
            ( ( eActiveChat && activeChatFlag == 1 ) || ( (!eActiveChat) ) )
   
        ) {
            $(this).parent().removeClass('hiddenElem').addClass('visibleElem');
            urlsArr.push(thisHref);
        } 
        else {
            $(this).parent().removeClass('visibleElem').addClass('hiddenElem');
        }
    });
    
    $('.map .logotypes a').each(function() {
        if(jQuery.inArray($(this).attr('href'), urlsArr) >= 0) {
            $(this).css({backgroundColor:'#fff'}).find('img').stop().animate({opacity:1},300);
        } else {
            $(this).css({backgroundColor: '#d7d7d7'}).find('img').stop().animate({opacity:.3},300);
        }
    });
    
    if($('div.exhibitorMscb .mCSB_container ul li.visibleElem').size() > 0) {
        $('div.exhibitorMscb .mCSB_container ul').show();
        $('div.exhibitorMscb div.noResults').hide();
    } else {
        $('div.exhibitorMscb .mCSB_container ul').hide();
        $('div.exhibitorMscb div.noResults').show();
    }
    
    //mcsbOther.mcsbOther("scrollTo","top");// nie działała w jednym przypadku - rzadkim
    $('.mcsbOther').mCustomScrollbar("update", function() {
        mcsbGoToTop();
    });
    
}

function exhibitorsClearInputsVal() {
    $('#eName').val('');
    $('#eBrand').val('');
    $('#eRegion option').removeAttr("selected","selected");
    $("#eRegion option[value='']").attr("selected","selected");
    $(".eActiveChat").attr("checked",false);
    exhibitorsDataFilter();
}


$('document').ready(function() {
    $('.exhibitorSearchPanel').find('input').bind('keyup', function(event) {
        exhibitorsDataFilter();
    });
    $('.exhibitorSearchPanel').find('select').bind('change', function(event) {
        exhibitorsDataFilter();
    });
    $('.exhibitorSearchPanel').find('input:checkbox').bind('change', function(event) {
        exhibitorsDataFilter();
    });
});

$(window).bind('orientationchange', function(){
        //$.doTimeout( 'refresh', 300, function() {
            //window.location.reload();
            fitMapToScreen();
        //});
}).resize(function () { 
	//initMap();
});