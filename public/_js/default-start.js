
function fitContentToScreen() {
    
    var documentH = parseInt($('body').height());
    var windowH = parseInt($(window).height());
    var calc;
    var headerSel = $('header');
    var contentSel = $('section.content');
    var footerSel = $('footer');
    var linksSel = $('div.links');
    
    calc = (windowH-parseInt(headerSel.outerHeight(true))-parseInt(contentSel.outerHeight(true))-parseInt(footerSel.outerHeight(true)))/2;
    
    if(calc > 1) {
        contentSel.css({
            paddingTop:calc-(0.2*calc)
        });
        
        linksSel.css({
            paddingTop:calc+(0.2*calc)
        });
        
        
    }
    
}

$(window).bind('orientationchange', function(){
    fitContentToScreen();
}).resize(function () { 
    //fitContentToScreen();
});


$(document).ready(function(){
    fitContentToScreen();
});