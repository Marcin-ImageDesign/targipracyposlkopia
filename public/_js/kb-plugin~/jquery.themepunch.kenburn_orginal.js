/**************************************************************************
 * jquery.themepunch.kenburn.js - jQuery Plugin for kenburn Slider
 * @version: 2.0 (09.02.2012)
 * @requires jQuery v1.2.2 or later 
 * @author Krisztian Horvath
**************************************************************************/




(function($,undefined){	
	
	
	
	////////////////////////////
	// THE PLUGIN STARTS HERE //
	////////////////////////////
	
	$.fn.extend({
	
		
		// OUR PLUGIN HERE :)
		kenburn: function(options) {
	
		
			
		////////////////////////////////
		// SET DEFAULT VALUES OF ITEM //
		////////////////////////////////
		var defaults = {	
			width: 876, // width of banner
			height: 300, // height of banner						
			thumbWidth:90,
			thumbHeight:50,
			thumbAmount:6,
			thumbSpaces:4,
			thumbPadding:4,
			thumbStyle:"bullet",		// bullet, image,none
			shadow:'true',			
			timer:2000,
			touchenabled:"on",
			pauseOnRollOverThumbs:'off',
			pauseOnRollOverMain:'on',
			preloadedSlides:50,
			googleFonts:'PT+Sans+Narrow:400,700',
			googleFontJS:'http://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js',
			debug:"no"
			
		};
		
			options = $.extend({}, $.fn.kenburn.defaults, options);
		
				WebFontConfig = {
						google: { families: [ options.googleFonts ] },
						active: function() { jQuery('body').data('googlefonts','loaded');},
						inactive: function() { jQuery('body').data('googlefonts','loaded');}
					};
				
					
			return this.each(function() {
							
					
				var opt=options;
				
				// GOOGLE FONT HANDLING
				if (opt.googleFonts!=undefined && opt.googleFonts.length>0) {
					var wf = document.createElement('script');
					wf.src = opt.googleFontJS;
					wf.type = 'text/javascript';
					wf.async = 'true';
					var s = document.getElementsByTagName('script')[0];
					s.parentNode.insertBefore(wf, s);
					jQuery('body').data('googlefonts','wait');
				} else {
					jQuery('body').data('googlefonts','loaded');
				}
				
				
				opt.savedTimer=opt.timer;
				var top_container=$(this);
				
				// DEBUGGING INFORMATIONS HERE
				if (opt.debug==="on")
					$('body').append('<div class="khinfo" style="background:#fff;color:#000;width:300px;height:250px;position:fixed;left:10px;top:10px;"></div>');

				top_container.css({'width':opt.width+"px",'height':opt.height+"px"});
				
				top_container.append('<div class="kenburn-preloader"></div>');
				$('body').find('.khinfo').html('Start Slider');
				
				prepareSlidesContainer(top_container,opt);
				$('body').find('.khinfo').html('Prepared Container');
				
				prepareSlides(top_container,opt);
				$('body').find('.khinfo').html('Prepared Preloaded Slides');
				
				prepareShadows(top_container,opt);		
				$('body').find('.khinfo').html('Prepared Shadows');
				
				
								
				$('body').find('.khinfo').html('Waiting for Images...');
				opt.loadedImages=0;
				top_container.waitForImages(
					function() {   
						$('body').find('.khinfo').html('Preloaded Images has been loaded');
						var waitForWF = setInterval(function() {
										
											
												
												
												$('body').find('.khinfo').html('Waiting for Google Fonts');
											
											// IF THE GOOGLE FONT IS LOADED WE CAN START TO ROTATE THE IMAGES
											if ($('body').data('googlefonts') != undefined && $('body').data('googlefonts')=="loaded") {
											
												// CREATE THE THUMBNAILS HERE
												if (opt.thumbStyle=="image" || opt.thumbStyle=="both" || opt.thumbStyle=="thumb")
													createThumbnails(top_container,opt);				
												 
												if (opt.thumbStyle=="bullet" || opt.thumbStyle=="both")
													createBullets(top_container,opt);
													
													
												$('body').find('.khinfo').html('Google Fonts are here');
												clearInterval(waitForWF);
												startRotation(top_container,opt);
												$('body').find('.khinfo').html('Rotation Started');
												prepareRestSlides(top_container,opt);										
											}
										},10);				
					}, 
					function() {
						$('body').find('.khinfo').html(opt.loadedImages+'. Image has been Loaded');
						opt.loadedImages=opt.loadedImages+1;
					});
				
				
				
				startTimer(top_container,opt);
					
				// TOUCH ENABLED SCROLL
				if (opt.touchenabled=="on")
						
						top_container.swipe( {data:top_container, 
										swipeLeft:function() 
												{ 
													var newitem = top_container.data('currentSlide');	
													if (newitem.index()<opt.maxitem-1) {
														var next=top_container.find('ul li:eq('+(newitem.index()+1)+')');
													} else {
														var next=top_container.find('ul li:first');
													}
													swapBanner(newitem,next,top_container,opt);
												}, 
										swipeRight:function() 
												{
													var newitem = top_container.data('currentSlide');	
													if (newitem.index()>0) {
														var next=top_container.find('ul li:eq('+(newitem.index()-1)+')');
													} else {
														var next=top_container.find('ul li:eq('+(opt.maxitem-1)+')');
													}
													swapBanner(newitem,next,top_container,opt);
												}, 
									allowPageScroll:"auto"} );
			
			})
	}
})
		

		///////////////////////////////
		//  --  LOCALE FUNCTIONS -- //
		///////////////////////////////
		
					
					///////////////////////////////////////////
					//	--	Set the Containers of Slides --	 //
					///////////////////////////////////////////
					
					
					function prepareSlides(top,opt) {
						top.find('iframe').attr("frameborder",0);						
						top.find('ul').wrap('<div class="slide_mainmask" style="z-index:10;position:absolute;top:'+(opt.padtop+1)+'px;left:'+(opt.padleft+1)+'px;width:'+opt.width+'px;height:'+opt.height+'px;overflow:hidden"></div>');
						top.find('ul .slide_mainmask').css({'opacity':'0.0'});
						
						opt.maxitem=0;
						top.find('ul >li').each(function(i) {
							opt.maxitem=opt.maxitem+1;
							var $this=$(this);
							var img = $this.find('img:first');
							img.data('src',img.attr('src'));
							img.attr('src',"");
						});
						
						for (var i=0;i<opt.preloadedSlides;i++) {
								prepareSlide(top,opt,i);
						}
					}
					
					
					////////////////////////////////////
					// Prepare THe Rest of the Slides //
					///////////////////////////////////
					function prepareRestSlides(top,opt) {
						for (var i=opt.preloadedSlides;i<opt.maxitem;i++) {
								prepareSlide(top,opt,i);
								
						}
					}
					
					
					//////////////////////////////
					// PREPARE SLIDE ONE BY ONE //
					//////////////////////////////
					function prepareSlide(top,opt,nr) {						
						
						top.find('ul >li').each(function(i) {
							if (i==nr) {
										var $this = $(this);
										$this.find('.creative_layer').wrap('<div class="layer_container" style="position:absolute;left:0px;top:0px;width:'+opt.width+'px;height:'+opt.height+'px"></div>');
										$this.wrapInner('<div class="slide_container" style="z-index:10;position:absolute;top:0px;left:0px;width:'+opt.width+'px;height:'+opt.height+'px;overflow:hidden"><div class="parallax_container" style="position:absolute;top:0pxleft:0px"><div class="kb_container"></div></div></div>');
										
										var ie_old = ( $.browser.msie ) && ($.browser.version<9);
										
										// PREPARE THE BLACK AND WHITE IMAGES HERE
										if ($this.find('img:first').data('bw') != undefined && $this.find('img:first').data('bw').length>0 && !ie_old)
											$this.find('.kb_container').append('<img class="bw-main-image" src="'+$this.find('img:first').data('bw')+'" style="position:absolute;top:0px;left:0px">');
					
										$this.find('img:first').attr('src',$this.find('img:first').data('src'));
										/******************************* 
										################################
											THE STRUCTUE:
											
											->slide_container
												->parallax_container	
													->kb_container
										################################
										********************************/
										$this.find('.slide_container').css({'opacity':'0.0'});
										
										$this.find('.slide_container .parallax_container .kb_container .video_pradigm').each(function() {
											var $this=$(this);
											$this.closest('.slide_container').append('<div class="kenburn-video-overlay"></div>');								
											$this.closest('.slide_container').append('<div class="kenburn-video-button"></div>');	
											
											$this.closest('.slide_container').data('video',1);
											
											var pbutton = $this.closest('.slide_container').parent().find('.kenburn-video-button');
											var over = $this.closest('.slide_container').parent().find('.kenburn-video-overlay');
											var _width  = parseInt(pbutton.css('width'),0);
											var _height = parseInt(pbutton.css('height'),0);
											var mwidth  = parseInt($this.closest('.slide_container').css('width'),0);
											var mheight = parseInt($this.closest('.slide_container').css('height'),0);
											
											pbutton.css({'left':(mwidth/2-_width/2)+'px','top':(mheight/2-_height/2)+'px'});
											pbutton.data('top',top);
											pbutton.data('url',$this.html());
											$this.remove();
											over.data('origopa',over.css('opacity'));
											
											
											// VIDEO IS DEFINED, SO HOVER ON VIDEO BUTTON SHOULD MAKE SOME EFFECT
											pbutton.hover(
													function() {
														
														var $this = $(this);																					
														var $over = $this.parent().find('.kenburn-video-overlay');											
														if ( $.browser.msie )
															$over.animate({'opacity':'0.5'},{duration:100});
														else
															$over.cssAnimate({'opacity':'0.5'},{duration:100});
														
														if ($.browser.version > 7 && $.browser.version < 9) {											
															$over.css({'display':'block'});
														}
													}, 
													function() {
														var $this = $(this);											
														var $over = $this.parent().find('.kenburn-video-overlay');
														
														if ( $.browser.msie )
															$over.animate({'opacity':$over.data('origopa')},{duration:100});
														else
															$over.cssAnimate({'opacity':$over.data('origopa')},{duration:100});
														
														if ($.browser.msie && $.browser.version > 7 && $.browser.version < 9) {												
															$over.css({'display':'none'});
														}
												});

												
											// VIDEO IS DEFINED, SO CLICK ON VIDEO BUTTON SHOULD START TO PLAY THE VIDEO HERE
											pbutton.click(
												function() {
													var $this=$(this);			
													var top=$this.data('top');
													var slidemask = top.find('.slide_mainmask');
													slidemask.addClass("videoon");
													top.data('currentSlide').animate({'top':opt.height+"px"},{duration:500,queue:false});										
													
													top.find('.slide_mainmask').append('<div class="video_kenburn" style="z-index:9999;width:'+opt.width+'px;height:'+opt.height+'px">'+$this.data('url')+'</div>');
													var video = top.find('.slide_mainmask .video_kenburn');
													video.css({'top':(0-opt.height)+"px"});
													video.animate({'top':'0px'},{duration:500,queue:false});		
											
													video.find('* .close').click(
														function() {
															
															var slidemask = top.find('.slide_mainmask');
															slidemask.removeClass("videoon");
															top.data('currentSlide').animate({'top':"0px"},{duration:600,queue:false});																																										
															video.animate({'top':(0-opt.height)+'px'},{duration:600,queue:false});		
															setTimeout(function() {video.remove()},600);
														});
												});
											
										});
								}
						});
					}
					
					
					////////////////////////////////////////////////
					//	--	BACKGROUND AND DEFAULT VALUES --	 //
					//////////////////////////////////////////////
					function prepareSlidesContainer(top,opt) {
						top.append('<div class="kenburn-bg" style="z-index:7;position:absolute;top:0px;left:0px;width:'+opt.width+'px;height:'+opt.height+'px;overflow:hidden"></div>');
						var bg=top.find('.kenburn-bg');
						opt.padtop=parseInt(bg.css('paddingTop'),0);
						opt.padleft=parseInt(bg.css('paddingLeft'),0);
						opt.padright=parseInt(bg.css('paddingRight'),0);
						opt.padbottom=parseInt(bg.css('paddingBottom'),0);						
					}
					
		
					////////////////////////////////////////////////
					//	--	ADD THE SHADOWS IN CASE WE NEED --	 //
					//////////////////////////////////////////////
					function prepareShadows(top,opt) {
					
						if (opt.shadow=="true" || opt.shadow==true || opt.shadow=="on") {
								// CALCULATE THE SIZES OF THE SHADOWS
								var full = opt.width + opt.padleft + opt.padright;
								var fifty = full/2;
								
								if (fifty>50) fifty=50;
								full = full - 2*fifty;
								
								
								// CREATE LEFT, MIDDLE AND RIGHT SHADOWS
								var leftshadow=$('<div class="kenburn-leftshadow" style="top:'+(1+opt.height+opt.padtop+opt.padbottom)+'px;left:0px;width:'+fifty+'px"></div>');
								var rightshadow=$('<div class="kenburn-rightshadow" style="top:'+(1+opt.height+opt.padtop+opt.padbottom)+'px"></div>');
								var repeatshadow=$('<div class="kenburn-repeatshadow" style="top:'+(1+opt.height+opt.padtop+opt.padbottom)+'px;left:'+fifty+'px;width:'+full+'px"></div>');
								
								
								// APPEND THE SHADOWS
								top.append(leftshadow);
								top.append(repeatshadow);
								top.append(rightshadow);
								
						} else {
							var thc=top.find('.kenburn_thumb_container');
								//alert(thc);
						}
					}
					
					
					
					///////////////////////////
					// CREATE THE THUMBNAILS //
					//////////////////////////
					function createThumbnails(top,opt) {
						
						var maxitem = top.find('ul >li').length;
						
											
						// CALCULATE THE MAX WIDTH OF THE THUMB HOLDER
						var maxwidth = (opt.thumbAmount * opt.thumbWidth)	+ ((opt.thumbAmount-1) * opt.thumbSpaces);
						var maxheight = opt.thumbHeight;
						
						var bgwidth = maxwidth-opt.thumbSpaces + 2*opt.thumbPadding;
						var bgheight = maxheight + opt.thumbPadding;
						var full = opt.width + opt.padleft + opt.padright;
						var centerl = Math.round(full /2 - bgwidth/2);

						var max= (maxitem * opt.thumbWidth)	+ ((maxitem-1) * opt.thumbSpaces);
						
						
						// CREATE THE BACKGROUND 1 PIXEL ROUND BG 
						top.append('<div class="kenburn_thumb_container" style="position:absolute;left:'+centerl+'px;top:'+(1+opt.height+opt.padtop+opt.padbottom)+'px;width:'+(bgwidth+2)+'px;height:'+(bgheight+2)+'px;"></div>');
						
						// CREATE THE WHITE HOLDER
						var thc=top.find('.kenburn_thumb_container');

						
						if (opt.thumbAmount==0) thc.css({'visibility':'hidden'});
						thc.append('<div class="kenburn_thumb_container_bg" style="position:absolute;left:1px;top:0px;width:'+(bgwidth)+'px;height:'+(bgheight)+'px"></div>');
						thc.find('.kenburn_thumb_container_bg').append('<div class="kenburn-repeatshadow" style="top:0px;left:'+(-2)+'px;width:'+(bgwidth+4)+'px"></div>');
						
						// CREATE THE MASK INSIDE
						thc.append('<div id="thumbmask" style="overflow:hidden;position:absolute;top:0px;left:'+(opt.thumbPadding+1)+'px; width:'+(maxwidth-opt.thumbSpaces)+'px;	height:'+opt.thumbHeight+'px;overflow:hidden;"></div>');					
						var thma=thc.find('#thumbmask');							
	
						// CREATE THE SLIDER CONTAINER
						thma.append('<div class="thumbsslide" style="width:'+max+'px"></div>');										
						var thbg=thma.find('.thumbsslide');
						
						
						
						/**********************************************
						##############################################
								STRUCTURE OF THUMBNAILS
							
							->.kenburn_thumb_container
									->#thumbmask
										->.thumbsslide
											->thumb (thumb"i")
							->.kenburn_thumb_container_bg
							
						##############################################
						*********************************************/
											
						// GO THROUGHT THE ITEMS, AND CREATE AN THUMBNAIL AS WE NEED
						top.find('ul >li').each(function(i) {
									
									var $this=$(this);
									
									// READ OUT THE DATA INFOS
									var img=$this.find('img:first');
									var bgsrc=img.data('thumb_bw');
									var src=img.data('thumb');
									var isvideo = $this.find('.slide_container').data('video')==1;
									
									// CREATE THE THUMBS
									var thumb=$('<div class="kenburn-thumbs" id="thumb'+i+'" style="cursor:pointer;position:absolute;top:0px;left:'+((i*opt.thumbWidth)+((i-1)*opt.thumbSpaces))+'px;width:'+opt.thumbWidth+'px;height:'+opt.thumbHeight+'px;background:url('+bgsrc+');"></div>');
																	
									thumb.data('id',i);
									
									if (i==maxitem)	thumb.css({'margin-right':'0px'});							
							
									thbg.append(thumb);
																		
									// CREATE THE IMG ON IT
									var new_img=$('<div id="over" style="cursor:pointer"><img id="over_img" src="'+src+'"></div>');
									thumb.append(new_img);									
									var ovv=thumb.find('#over');
									
									ovv.css({'opacity':'0.0'});
									
									if ($.browser.msie && $.browser.version > 7 && $.browser.version < 9) {
										//ovv.css("filter","alpha(opacity=0)");
										ovv.css({'display':'none'});
									}
									
									
									ovv.css({'overflow':'hidden','position':'absolute','left':'0px','opacity':'0.0','height':opt.thumbHeight+"px",'width':opt.thumbWidth+"px"});
									ovv.find('img').css({'position':'absolute','left':'0px'});
									
									if ( $.browser.msie ) {
										ovv.animate({'left':'0px','opacity':'0.0','height':opt.thumbHeight+"px",'width':opt.thumbWidth+"px"},{duration:50,queue:false});
										ovv.find('img').animate({'left':'0px'},{duration:50,queue:false});
										
									} else {
										ovv.cssAnimate({'left':'0px','opacity':'0.0','height':opt.thumbHeight+"px",'width':opt.thumbWidth+"px"},{duration:50,queue:false});
										ovv.find('img').cssAnimate({'left':'0px'},{duration:50,queue:false});
									}
									
									// CREATE VIDEO BUTTON ON THE THUMBNAIL
									if (isvideo) {
										var new_video=$('<div class="video"></div>');
										thumb.append(new_video);									
										thumb.find('.video').css({'position':'absolute',
																  'top':opt.thumbHeight/2+'px',
																  'left':opt.thumbWidth/2+'px',
																  'z-index':'1000'});
										}
										
										 
									// ADD SHADOWS
									if (opt.shadow=="true") {
										var repeatshadow=$('<div class="kenburn-repeatshadow" style="position:relative;margin-left:0px;width:'+opt.thumbWidth+'px;"></div>');
										thumb.append(repeatshadow);
									}
									
									
									///////////////////////////////////////
									// SHOW THE COLORED THUMBNAIL HERE  //
									//////////////////////////////////////
									var thumbnail = thbg.find('#thumb'+i);
									thumbnail.hover(
										function() {
											var $this=$(this).find('#over');
											
											if (!$this.parent().hasClass("selected")) {												
													//$this.stop();									
													if ( $.browser.msie ) {
														if ($.browser.msie && $.browser.version > 7 && $.browser.version < 9)
																$this.css({'display':'block'});
															else
																$this.animate({'opacity':'1.0'},{duration:300,queue:false});												
														
													 } else {														
														$this.css({'left':'0px','display':'block'});
														$this.find("img").css({'display':'block','opacity':'1.0','left':'0px'});												
														$this.cssAnimate({'opacity':'1.0'},{duration:300,queue:false});
														
													}
											}
										},
										function() {
											var $this=$(this).find('#over');
											
											if (!$this.parent().hasClass("selected")) {
												if ( $.browser.msie ) {
																									
													if ($.browser.msie && $.browser.version > 7 && $.browser.version < 9)
														$this.css({'display':'none'});
													else
														$this.animate({'opacity':'0'},{duration:300,queue:false});												
													
												 } else {
													$this.cssAnimate({'opacity':'0.0'},{duration:300,queue:false});
												}
											}
										});
									
									thumbnail.click(function() {
										var $this=$(this);
										if (!$this.hasClass("selected")) {
											var newslide = top.find('ul li:eq('+$this.data('id')+')');										
											swapBanner(top.data('currentSlide'),newslide,top,opt);
										}
									});
									
									
								
							});
							
							
							
							////////////////////////							
							// SLIDE TO POSITION  //
							////////////////////////							
							if (maxwidth<max) {
								$(document).mousemove(function(e) {
									$('body').data('mousex',e.pageX);							
								});
								
								var diff=(max- maxwidth);								
								top.data('thumbnailmaxdif',diff);
								
								var steps = diff / (maxwidth-opt.thumbWidth);
								
								
								thma.data('steps',steps);				
								thma.data('thw',opt.thumbWidth);		
								thma.data('maxw',diff);
								
								
								// ON MOUSE MOVE ON THE THUMBNAILS EVERYTHING SHOULD MOVE :)
								
								thma.mouseenter(function() {
									var $this=$(this);
										if (opt.pauseOnRollOverThumbs != "off")
											$this.addClass('overme');
										
										var offset = $this.offset();
										var x = $('body').data('mousex')-offset.left;
										x=x-$this.data('thw')/2;
										
										// STEPS AND ETC
										var steps=$this.data('steps');												
										
										//ANIMATE TO POSITION
										var pos=(0-steps*x);

										if (pos>0) pos =0;						
										if (pos<0-diff) pos=0-diff;
										if (opt.pauseOnRollOverMain != "off")
											$this.addClass("overon");
										$this.find('.thumbsslide').css({'position':'absolute'});
										
										if ( $.browser.msie ) 
											$this.find('.thumbsslide').animate({'left':pos+'px'},{duration:200,queue:false});										
										else
											$this.find('.thumbsslide').cssAnimate({'left':pos+'px'},{duration:200,queue:false});										
										
								});
								
								thma.mousemove(function() {										
										var $this=$(this);
										if (opt.pauseOnRollOverThumbs != "off")
											$this.addClass('overme');
										
										var offset = $this.offset();
										var x = $('body').data('mousex')-offset.left;
										x=x-$this.data('thw')/2;
										
										// STEPS AND ETC
										var steps=$this.data('steps');												
										
										//ANIMATE TO POSITION
										var pos=(0-steps*x);

										if (pos>0) pos =0;						
										if (pos<0-diff) pos=0-diff;
										
										if (!$this.hasClass("overon")) {
											$this.find('.thumbsslide').css({'position':'absolute'});
											//$this.find('.thumbsslide').stop();
											if ( $.browser.msie ) 
												$this.find('.thumbsslide').animate({'left':pos+'px'},{duration:0,queue:false});										
											else
												$this.find('.thumbsslide').cssAnimate({'left':pos+'px'},{duration:0,queue:false});										
										} else {
											setTimeout(function() {$this.removeClass('overon');},100);
										}
								});
								
								thma.mouseout(function() {
									var $this=$(this);
									$this.removeClass('overme');
										
								});
							}
							
					}
					
		
					///////////////////////////
					// CREATE THE BULLETS //
					//////////////////////////
					function createBullets(top,opt) {
						
						var maxitem = top.find('ul >li').length;
						
											
						// CALCULATE THE MAX WIDTH OF THE THUMB HOLDER
						var full = opt.width + opt.padleft + opt.padright;
						
						// Create BULLET CONTAINER
						top.append('<div class="thumbbuttons"><div class="grainme"><div class="leftarrow"></div><div class="thumbs"></div><div class="rightarrow"></div></div></div>');
						var leftb = top.find('.leftarrow');
						var rightb = top.find('.rightarrow');
						
						
						rightb.click(function() 
												{ 
													var newitem = top.data('currentSlide');	
													if (newitem.index()<opt.maxitem-1) {
														var next=top.find('ul li:eq('+(newitem.index()+1)+')');
													} else {
														var next=top.find('ul li:first');
													}
													swapBanner(newitem,next,top,opt);
												});
						leftb.click(function() 
												{
													var newitem = top.data('currentSlide');	
													if (newitem.index()>0) {
														var next=top.find('ul li:eq('+(newitem.index()-1)+')');
													} else {
														var next=top.find('ul li:eq('+(opt.maxitem-1)+')');
													}
													swapBanner(newitem,next,top,opt);
												});
									
						var minithumbs = top.find('.thumbs');
						
								
						
						
						// GO THROUGHT THE ITEMS, AND CREATE AN THUMBNAIL AS WE NEED
						top.find('ul >li').each(function(i) {
									
									var $this=$(this);
									
						
									var thumb_mini=$('<div class="minithumb" id="minithumb'+i+'"></div>');
									

									thumb_mini.data('id',i);
									minithumbs.append(thumb_mini);
																											
									thumb_mini.click(function() {
										var $this=$(this);
										if (!$this.hasClass("selected")) {
											var newslide = top.find('ul li:eq('+$this.data('id')+')');										
											swapBanner(top.data('currentSlide'),newslide,top,opt);
										}
									});
								
							});
							
							minithumbs.waitForImages(function() {
								
								minithumbs.parent().parent().css({'left':(full/2 - parseInt(minithumbs.parent().width(),0)/2)+"px"});
								
							});
							
							
							
					}
						
					/////////////////////////////////////////////
					// - START THE ROTATION OF THE BANNER HERE //
					/////////////////////////////////////////////						
					function startRotation(item,opt) {
						if ( $.browser.msie ) {
							item.find('.kenburn-preloader').animate({'opacity':'0.0'},{duration:300,queue:false});
							
						} else {
							item.find('.kenburn-preloader').cssAnimate({'opacity':'0.0'},{duration:300,queue:false});
						}
						setTimeout(function() {item.find('.kenburn-preloader').remove();},300);
						var first_slide = item.find('ul li:first') ;
						swapBanner(first_slide,first_slide,item,opt);	
						startParallax(item,opt);
						opt.loaded=1;
						
					}
					
					
					
					
					/////////////////////////////////
					// - START THE PARALLAX EFFECT //
					////////////////////////////////						
					function startParallax(slidertop,opt) {
												
						// FIND THE RIGHT OBJECT 
						var top = slidertop.find('.slide_mainmask');
												
						// SET WIDTH AND HEIGHT
						top.data('maxwidth',opt.width+opt.padleft+opt.padright);
						top.data('maxheight',opt.height+opt.padtop+opt.padbottom);
						top.data('pdistance',opt.parallaxX);
						top.data('pdistancey',opt.parallaxY);
						top.data('cdistance',opt.captionParallaxX);
						top.data('cdistancey',opt.captionParallaxY);
						top.data('opt',opt);
						
						// SOME HELP DIV IF WE NEED TO DEBUG //
								//top.append('<div style="z-index:1000000;opacity:0.3;background-color:#ff0000;width:'+opt.width+'px;height:'+opt.height+'px;position:absolute"></div>');						
						$('body').append('<div id="mpinfo" style="z-index:1000000;background-color:#fff;position:absolute;top:10px;left:10px;font-size:15px"></div>');
						
						
						// KEN BURN ANIMATION
						var slide = top.parent().data('currentSlide');							
						var par = top.find('.parallax_container');
						var layers = slide.find('.layer_container');
						
						
						// THE FIRST MOUSE OVER ON THE TOP
						top.mouseenter(function(e) {							
							var $this = $(this);			
							// FIND THE RIGHT THUMBNAIL HOLDER OBJECT
							var thma = $this.parent().find('.kenburn_thumb_container #thumbmask');
							
							// IF MOUSE IS NOT OVER THE THUMBS AND START ANIMATION NOT RUNNING															
								var slide = $this.parent().data('currentSlide');							
								var par = slide.find('.parallax_container');
								var layers = slide.find('.layer_container');
								
								
									$this.addClass('overon');						
									
								
						});
						
						// BACK TO CENTER AFTER LEAVE
						top.mouseleave(function(e) {							
							var $this = $(this);																			
								var slide = $this.parent().data('currentSlide');							
								var par = slide.find('.parallax_container');
								var layers = slide.find('.layer_container');
								$this.removeClass("overme");		
								
								
									$this.addClass('overon');
								
						});
						
						// HERE COME THE DIRECT PARALLAX HANDLING FOR QUICK ANIMATIONS
						top.mousemove(function(e) {
						
							var $this = $(this);	
							if (opt.pauseOnRollOverMain != "off") 
								$this.addClass("overme");
							// FIND THE RIGHT THUMBNAIL HOLDER OBJECT							
							var thma = $this.parent().find('.kenburn_thumb_container #thumbmask');
							
							// IF MOUSE IS NOT OVER THE THUMBS AND START ANIMATION NOT RUNNING
							if (!thma.hasClass('overme') && !$this.hasClass('overon')) {
									
									
									var slide = $this.parent().data('currentSlide');							
									var par = slide.find('.parallax_container');
									var layers = slide.find('.layer_container');																		
									
								
							} else {
								
								setTimeout(function() {$this.removeClass('overon')},100);
							}
						});
						
						
					
					}
						
						
					/////////////////////////////
					// RANDOM ALIGN GENERATOR //
					////////////////////////////
					function randomAlign() {
						
						var align="";
						var a=Math.floor(Math.random()*3);
						var b=Math.floor(Math.random()*3);
						
						if (a==0) align="left"; 
							else
								if (a==1) align="center" 
									else
										align="right";
						
						if (b==0) align=align+",top" 
							else
								if (b==1) align=align+",center" 
									else
										align=align+",bottom";
						return align;
					}
										
					////////////////////////////////////////////////////
					// - THE BANNER SWAPPER, ONE AGAINST THE OTHER :) //
					////////////////////////////////////////////////////
					function swapBanner(item,newitem,slider_container,opt) {
											
							var trans=false;
							
							slider_container.find('ul >li').each(function(i) {
								var $this=$(this);
								
								if ($this.index() !=item.index() && $this.index() !=newitem.index()) {
									$this.css({'display':'none','position':'absolute','left':'0px','z-index':'994'});									
								}
							});
							
									
							var video = slider_container.find('.slide_mainmask .video_kenburn');														
							setTimeout(function() {video.remove()},600);
							
							var slidemask = slider_container.find('.slide_mainmask');
							slidemask.removeClass("videoon");
							
							item.css({'position':'absolute','top':'0px','left':'0px','z-index':'900'});
							newitem.css({'position':'absolute','top':'0px','left':'0px','z-index':'1000'});
							newitem.css({'display':'block'});

							//Lets find the Image 
							var sour=newitem.find('img:first');
							var sourbw=newitem.find('.bw-main-image');
							
							
							// Lets Save the Size of the IMG first in the DATA
							if (newitem.data('ww') == undefined) {
								var oldW=newitem.find('img').width();			//Read out the Width					
								var oldH=newitem.find('img').height();			//Read out the Width					
								if (oldW!=0) {									// If the Width is not 0 (the image is loaded)
									
									// Let See if the KenBurn Img is smaller than the stage (width). If yes, we need to scale it first ! 
									if (sour.width()>0 && sour.width()<opt.width) {														
										var factor=opt.width / oldW;														
										oldW=oldW*factor;
										oldH=oldH*factor;														
										
									}
																
									// Let See if the KenBurn IMG is smaller then the stage height). If yes, we need to scale it first !!
									if (sour.height()>0 && sour.height()<opt.height) {														
										var factor=opt.height / oldH;														
										oldW=oldW*factor;
										oldH=oldH*factor;																								
									}
									
									newitem.data('ww',oldW);
									newitem.data('hh',oldH);
								}
							} else {
								
								var oldW = newitem.data('ww');
								var oldH = newitem.data('hh');
							}
							
							
							
							// Create the Standard Values
							var newT=0;
							var newL=0;
							var endT=0;
							var endL=0;
							
							var startalign = newitem.data('startalign');
							var endalign = newitem.data('endalign');

							if (startalign=="random") startalign = randomAlign();							
							if (endalign=="random") endalign = randomAlign();							
							
							// Lets Compute the Start Position here depending on the Start Align
							if (startalign != undefined) {	
								

								var salignh = startalign;								
								var horiz = salignh.split(',')[0];
								var vert = salignh.split(',')[1];
							
								
								if (horiz == "left") newL=0;
								 else 
									if (horiz == "center") newL=(opt.width/2 - oldW/2);
									   else
										 if (horiz == "right") newL= 0 - Math.abs(opt.width - oldW);	
								
								if (vert == "top") newT=0;
								 else 
									if (vert == "center") newT=(opt.height/2 - oldH/2);
									   else
										 if (vert == "bottom") newT= 0 - Math.abs(opt.height - oldH);										 
							}
							
							
							// Lets compute the End Positions depending on the End Align
							if (endalign != undefined) {		
								
								var ealignh = endalign;								
								var horiz = ealignh.split(',')[0];
								var vert = ealignh.split(',')[1];
								
								if (horiz == "left") endL=0;
								 else 
									if (horiz == "center") endL=(opt.width/2 - oldW/2);
									   else
										 if (horiz == "right") endL= 0 - Math.abs(opt.width - oldW);	
								
								if (vert == "top") endT=0;
								 else 
									if (vert == "center") endT=(opt.height/2 - oldH/2);
									   else
										 if (vert == "bottom") endT= 0 - Math.abs(opt.height - oldH);										 
							}
							
							
							
							// Remove the Interval of the old item. So it do not disturb the CPU any more
							clearInterval(item.data('interval'));
							
							
							
														
							sour.parent().find('.canvas-now').remove();
							sour.parent().find('.canvas-now-bw').remove();
							
							// CHECK THE CANVAS SUPPORT HERE
							var hasCanvas=isCanvasSupported();
							var isIEunder9 = $.browser.msie && $.browser.version < 9;
							
							if (isIEunder9) hasCanvas=false;
							
							
							// IF THERE IS CANVAS AVAILABLE, WE CAN CREATE A CANVAS HERE....
							if (hasCanvas) {
								sour.parent().append('<div style="position:absolute;z-index:1" class="canvas-now"><canvas class="canvas-now-canvas" width="'+oldW+'" height="'+oldH+'"></canvas></div>');
								sour.css({'display':'none'});
								var canvas=sour.parent().find('.canvas-now-canvas')[0];
								var context = canvas.getContext('2d');
								
								if (sourbw.length>0) {
									sour.parent().append('<div style="position:absolute;z-index:20" class="canvas-now-bw"><canvas class="canvas-now-canvas-bw" width="'+oldW+'" height="'+oldH+'"></canvas></div>');
									sourbw.css({'display':'none'});
									var canvasbw=sour.parent().find('.canvas-now-canvas-bw')[0];
									var contextbw = canvasbw.getContext('2d');
						        }
							} else {
								
								
							}
														
							
							// LETS GET THE TIME 
							var time=newitem.data('panduration');
							
							// DEFAULT VALUES FOR SCALING AND MOVING THE IMAGE
							var scalerX=0;
							var scalerY=0;
							var newW=oldW;
							var newH=oldH;
							
							// READ OUT THE ZOOMFACTOR
							var zoomfact=newitem.data('zoomfact')
							var zoom = newitem.data('zoom');
							
							if (zoom=="random") {
								if (Math.floor(Math.random()*2) == 0) zoom="out" 
									else 
										zoom="in";
							}
							
							if (newitem.data('zoomfact')=="random") {
								zoomfact=(Math.random()*1 + 1);
							}
							
							
							// IF WE ZOOM OUT, WE NEED TO RESET THE ZOOM FIRST TO "BIG"
							if (zoom == "out") {						
								newW=newW*zoomfact;
								newH=newH*zoomfact;
								newL=newL*zoomfact;
								newT=newT*zoomfact;
							}
							
							// NOW LETS CALCULATE THE STEPS HERE
							var movX = (endL-newL) / (time*25);
							var movY = (endT-newT) / (time*25);
							
							
							
							var opaStep = 1/(time*25);
							// STANDARD ZOOM STEPS
							scalerX=(oldW*zoomfact) / (time*25)/10;
							scalerY=(oldH*zoomfact) / (time*25)/10;															
							
							// IF ZOOM OUT, WE INVERT THE ZOOM STEPS
							if (zoom == "out") {							
								scalerX=scalerX*-1;
								scalerY=scalerY*-1;
							} 
							
							
							// Lets compute the End Zoom Offsets depending on the End Align
							if (newitem.data('endalign') != undefined) {																
								var ealignh = newitem.data('endalign');								
								var horiz = ealignh.split(',')[0];
								var vert = ealignh.split(',')[1];
								
								if (horiz == "left") movX = movX + scalerX/4;
								 else 
									if (horiz == "center") movX = movX - scalerX/2;
									   else
										 if (horiz == "right") movX = movX - scalerX;	
								
								if (vert == "top") movY = movY + scalerY/4;
								 else 
									if (vert == "center") movY = movY - scalerY/2;
									   else
										 if (vert == "bottom") movY = movY - scalerY;
							}
							
							
							
							
							
							// IF THE TIMER IS SMALLER THAN THE BASIC TIMER, THAN THE MAIN TIMER NEED TO BE REDUCED TO 
							if (opt.timer>parseInt(newitem.data('panduration'),0)*10) {
								opt.timer=parseInt(newitem.data('panduration'),0)*10 
							} else {
								opt.timer=opt.savedTimer*10;
								
							}
							
							if (hasCanvas) {
								context.drawImage(sour[0],newL,newT,newW,newH);
								if (sourbw.length>0) {		
										contextbw.drawImage(sourbw[0],newL,newT,newW,newH);
										setTimeout(function() {
											sour.parent().find('.canvas-now-bw').cssAnimate({'opacity':'0.0'},{duration:newitem.data('colortransition')*1000,queue:false});
										},500);
										
								}
							} 
							
							sour.cssStop(true,true);
							sourbw.cssStop(true,true);
							
							sour.css({	'position':'absolute',
										'left':newL+"px",
										'top':newT+"px",
										'width':newW+"px",
										'height':newH+"px",
										'opacity':1.0});
										
							sourbw.css({'position':'absolute',
										'left':newL+"px",
										'top':newT+"px",
										'width':newW+"px",
										'height':newH+"px",
										'opacity':1.0});
							
							var oldL = newL;
							var oldT = newT;
							var oldWW = newW;
							step=0;
							// NOW WE CAN CREATE AN INTERVAL, WHICH WILL SHOW 25 FRAMES PER SEC (TO MINIMIZE THE CPU STEPS)
							newitem.data('interval',setInterval(function() {								
								if (!slider_container.parent().parent().find('.kenburn_thumb_container #thumbmask').hasClass('overme') && !slider_container.find('.slide_mainmask').hasClass('overme') && !slider_container.find('.slide_mainmask').hasClass('videoon')) {
									
									newW=newW+scalerX;		//CHANGE THE SCALING PARAMETES
									newH=newH+scalerY;									
									
									newL=newL+movX;			// MOVE THE POSITION OF THE IMAGES 
									newT=newT+movY;
									
									if (newL>0) newL=0;
									if (newT>0) newT=0;
									if (newL<(opt.width - newW)) newL=opt.width-newW;
									if (newT<(opt.height - newH)) newT=opt.height-newH;
									
									if (hasCanvas) {																			
										context.drawImage(sour[0],newL,newT,newW,newH);
										if (sourbw.length>0) contextbw.drawImage(sourbw[0],newL,newT,newW,newH);										
									} else {
											
												var s=newW/oldWW;
												var p1=newL - oldL;
												var p2=newT - oldT;
												 
												 if( jQuery.browser.msie ) {
													 
													   sour.css({'filter': 'progid:DXImageTransform.Microsoft.Matrix(FilterType="bilinear",M11=' + s + ',M12=0,M21=0,M22=' + s + ',Dx=' + p1 + ',Dy=' + p2 + ')'});
													   sour.css({'-ms-filter': 'progid:DXImageTransform.Microsoft.Matrix(FilterType="bilinear",M11=' + s + ',M12=0,M21=0,M22=' + s + ',Dx=' + p1 + ',Dy=' + p2 + ')'});
													   sourbw.remove();
													
												 } else {
									
														sour.cssAnimate({	'left':newL+"px",
																			'top':newT+"px",
																			'width':newW+"px",
																			'height':newH+"px"},
																		{ duration:38, easing:'linear',queue:false});
														
														if (sourbw.length>0 && sourbw.css('opacity')>0) {				
															sourbw.cssAnimate({	'left':newL+"px",
																				'top':newT+"px",
																				'width':newW+"px",
																				'height':newH+"px"},
																			{ duration:38, easing:'linear',queue:false});														
															sourbw.css({'opacity':(sourbw.css('opacity')-opaStep)});
														}
												}
										
									}
								}
							},40));
																																									
							
							// TRANSITION OF THE SLIDES
							if (newitem.data('transition')=="slide") {
									if (trans==false) {
												var left=true;																				
												if (item.index()>newitem.index()) left = false;
												
												if (left) {		
													
													video.animate({'left':(0-opt.width)+'px'},{duration:600,queue:false});																												
													newitem.find('.slide_container').stop(true,true);
													newitem.find('.slide_container').css({'opacity':'1.0','left':opt.width+"px"});
													//setTimeout(function() {
														//if ( $.browser.msie )  {
															item.find('.slide_container').animate({'left':0-opt.width+'px'},{duration:600,queue:false});
															newitem.find('.slide_container').animate({'left':'0px','top':'0px','opacity':'1.0'},{duration:600,queue:false});
														/*} else {
															item.find('.slide_container').cssAnimate({'left':0-opt.width+'px'},{duration:600,queue:false});
															newitem.find('.slide_container').cssAnimate({'left':'0px','top':'0px','opacity':'1.0'},{duration:600,queue:false});
														}*/
													//},600);
													
													
														
													
												} else {
												
													video.animate({'left':(opt.width)+'px'},{duration:600,queue:false});
													newitem.find('.slide_container').stop(true,true);
													newitem.find('.slide_container').css({'opacity':'1.0','position':'absolute','top':'0px','left':0-opt.width+'px'});	
													
													//if ( $.browser.msie )  {
														item.find('.slide_container').animate({'left':opt.width+'px'},{duration:600,queue:false});											
														newitem.find('.slide_container').animate({'left':'0px','top':'0px','opacity':'1.0'},{duration:600,queue:false});
													//} else {
														//item.find('.slide_container').cssAnimate({'left':opt.width+'px'},{duration:600,queue:false});											
														//newitem.find('.slide_container').cssAnimate({'left':'0px','top':'0px','opacity':'1.0'},{duration:600,queue:false});
													//}
													
													
													
													
												}
										}
							} else {																								
								//if ( $.browser.msie ) 
									item.find('.slide_container').stop(true,true);
									item.find('.slide_container').animate({'opacity':'0'},{duration:600,queue:false});	
								//else
									//item.find('.slide_container').cssAnimate({'opacity':'0'},{duration:600,queue:false});	
								
								video.animate({'opacity':'0.0'},{duration:600,queue:false});		
								
								
								//if ( $.browser.msie ) 
									newitem.find('.slide_container').stop(true,true);
									newitem.find('.slide_container').css({'opacity':'0.0','left':'0px','top':'0px'});								
									newitem.find('.slide_container').animate({'opacity':'1.0'},{duration:600,queue:false});
								//else
									//newitem.find('.slide_container').cssAnimate({'opacity':'1.0'},{duration:600,queue:false});
							}
							
							
							
						// SET THE THUMBNAIL
						
						if (slider_container.find('.kenburn_thumb_container').length>0) {
								var thumb = slider_container.find('.kenburn_thumb_container #thumbmask .thumbsslide #thumb'+newitem.index()+' #over');
							
								slider_container.find('.kenburn_thumb_container #thumbmask .thumbsslide #thumb'+item.index()).each(function(i) {						
										var $this=$(this);								
										if ($this.attr('id')!="thumb"+newitem.index()) {
											
											$this.removeClass('selected');																
											var over=$this.find('#over');
											over.stop();		
											over.css({'position':'absolute','opacity':'0.0'});
											setTimeout(function() {
												over.find('img').css({'position':'absolute','left':'0px'});
											},30);
											
											
											if ( $.browser.msie ) { 												
												over.animate({'left':'0px','height':opt.thumbHeight+"px",'width':opt.thumbWidth+"px"},{duration:1,queue:false});										
											} else {
												over.cssAnimate({'left':'0px','height':opt.thumbHeight+"px",'width':opt.thumbWidth+"px"},{duration:1,queue:false});
											}
											
											if ($.browser.msie && $.browser.version > 7 && $.browser.version < 9)
												over.css({'display':'none'});
											
										} 
								});
								
								thumb.parent().addClass('selected');
								thumb.animate({'opacity':'1.0'},{duration:300,queue:false});
								if ($.browser.msie && $.browser.version > 7 && $.browser.version < 9)
									thumb.css({'display':'block'});
						
						
						
								// SET THE CURRENT ITEM IN DATA
								
								slider_container.data('currentThumb',thumb);
						} 
						
						if (slider_container.find('.minithumb').length>0) {
							var thumb = slider_container.find('#minithumb'+newitem.index());
							slider_container.find('.minithumb').removeClass('selected');
							thumb.addClass('selected');
							if (opt.thumbStyle!="both") slider_container.data('currentThumb',thumb);
						}
						
						//SAVE THE LAST SLIDE
						slider_container.data('currentSlide',newitem);								
						
						// START 
						textanim(newitem,100,slider_container);
						opt.cd=0;
																
					}
					
				
				
				//////////////////////////////////////////
				// CHECK IF CANCAS (HTML5) IS SUPPORTED //
				//////////////////////////////////////////
				function isCanvasSupported(){
				  var elem = document.createElement('canvas');
				  return !!(elem.getContext && elem.getContext('2d'));
				}

				
				
				////////////////////////////////////
				// AUTOMATIC COUNTDOWN FOR SLIDER //
				////////////////////////////////////
				function startTimer(top,opt) {
					opt.cd=0;
					
					if (opt.thumbStyle=="image" || opt.thumbStyle=="both" || opt.thumbStyle=="thumb") {
										//opt.timer=opt.timer*10;
										if ( $.browser.msie ) 									
											top.find('.kenburn_thumb_container #thumbmask .thumbsslide').cssAnimate({'left':'0px'},{duration:300,queue:false});
										else
											top.find('.kenburn_thumb_container #thumbmask .thumbsslide').animate({'left':'0px'},{duration:300,queue:false});
										var tmask = top.find('.kenburn_thumb_container #thumbmask');
										var tslide = tmask.find('.thumbsslide');
										
										var slidemask = top.find('.slide_mainmask');
										
										// HIER COMES THE INTERVAL ES IT SHOULD
										setInterval(function() {
											if (opt.loaded==1) {
												var newitem = top.data('currentSlide');							
												var thumb = top.data('currentThumb');
												
												if (!tmask.hasClass('overme') && !slidemask.hasClass('overme') && !slidemask.hasClass('videoon')) {
																
																opt.cd=opt.cd+1;
																
																var offsetme = Math.floor(opt.thumbWidth * ((opt.cd/opt.timer)))
																
																if ( $.browser.msie )  {											
																	thumb.find('img').animate({'left':(0-offsetme)+'px'},{duration:10,queue:false});
																	thumb.animate({'left':offsetme+'px'},{duration:10,queue:false});
																	
																 } else {
																	
																	thumb.find('img').cssAnimate({'left':(0-offsetme)+'px'},{duration:10,queue:false});
																	thumb.cssAnimate({'left':offsetme+'px'},{duration:10,queue:false});
																}
																
																	
																
																
																if (opt.cd==opt.timer) {
																	opt.cd=0;
																	tslide.find('.kenburn-thumbs').each(function() {
																		var $this=$(this);
																		if ( $.browser.msie ) {
																			$this.stop();
																			$this.find('img').stop(true,true);
																		} else {
																			$this.cssStop(true,true);
																			$this.find('img').cssStop(true,true);
																		}
																		$this.find('img').css({'left':'0px'});														
																		tslide.css({'left':'0px'});		
																		
																		
																	});
																	
																	if (newitem.index()<opt.maxitem-1) {
																		var next=top.find('ul li:eq('+(newitem.index()+1)+')');
																		swapBanner(newitem,next,top,opt);
																		var minus = 0-parseInt(thumb.parent().css('left'),0);
																		tslide.css({'position':'absolute'});
																		if (Math.abs(minus)<=top.data('thumbnailmaxdif')) {							
																			if ( $.browser.msie ) 											
																				tslide.animate({'left':minus+'px'},{duration:300,queue:false});
																			else
																				tslide.cssAnimate({'left':minus+'px'},{duration:300,queue:false});
																		} else {
																			minus = 0-top.data('thumbnailmaxdif');							
																			if ( $.browser.msie ) 																					
																				tslide.animate({'left':minus+'px'},{duration:300,queue:false});
																			else
																				tslide.cssAnimate({'left':minus+'px'},{duration:300,queue:false});
																		}
																		
																	} else {
																		swapBanner(newitem,top.find('ul li:first'),top,opt);	
																		if ( $.browser.msie ) 																					
																			tslide.animate({'left':'0px'},{duration:300,queue:false});
																		else
																			tslide.cssAnimate({'left':'0px'},{duration:300,queue:false});
																		
																	}
																	
																	

																}
													}
											}
										},100);
							} else {
							
								var slidemask = top.find('.slide_mainmask');
								
								setInterval(function() {
											if (opt.loaded==1) {
												var newitem = top.data('currentSlide');							
												var thumb = top.data('currentThumb');
												
												if (!slidemask.hasClass('overme') && !slidemask.hasClass('videoon')) {
																
																opt.cd=opt.cd+1;
																if (opt.cd==opt.timer) {
																	opt.cd=0;
																	if (newitem.index()<opt.maxitem-1) {
																		var next=top.find('ul li:eq('+(newitem.index()+1)+')');
																		swapBanner(newitem,next,top,opt);
																		if (opt.thumbStyle!="none") {
																			var minus = 0-parseInt(thumb.parent().css('left'),0);
																			if (tslide!=null && tslide!=undefined) {
																				tslide.css({'position':'absolute'});
																				if (Math.abs(minus)<=top.data('thumbnailmaxdif')) {							
																					if ( $.browser.msie ) 											
																						tslide.animate({'left':minus+'px'},{duration:300,queue:false});
																					else
																						tslide.cssAnimate({'left':minus+'px'},{duration:300,queue:false});
																				} else {
																					minus = 0-top.data('thumbnailmaxdif');							
																					if ( $.browser.msie ) 																					
																						tslide.animate({'left':minus+'px'},{duration:300,queue:false});
																					else
																						tslide.cssAnimate({'left':minus+'px'},{duration:300,queue:false});
																				}
																			}
																		}
																		
																	} else {
																		swapBanner(newitem,top.find('ul li:first'),top,opt);	
																		if (tslide!=null && tslide!=undefined) {
																			if ( $.browser.msie ) 																					
																				tslide.animate({'left':'0px'},{duration:300,queue:false});
																			else
																				tslide.cssAnimate({'left':'0px'},{duration:300,queue:false});
																		}
																		
																	}
																}
													}
											}			
									},100);
																
								
							}
				}
				
							
				
				
				///////////////////
				// TEXTANIMATION //
				//////////////////			
				function textanim (item,edelay,slider_container) {
												
								var counter=2;
				
									item.find('.creative_layer div').each(function(i) {
															
															var $this=$(this);
															
															// REMEMBER OLD VALUES
															if ($this.data('_top') == undefined) $this.data('_top',parseInt($this.css('top'),0));
															if ($this.data('_left') == undefined) $this.data('_left',parseInt($this.css('left'),0));
															if ($this.data('_op') == undefined) { $this.data('_op',$this.css('opacity'));}
															
													
															// CHANGE THE z-INDEX HERE
															$this.css({'z-index':1200});
															
															
																	
																	
																	
																	
																	//// -  FADE UP   -   ////
																	if ($this.hasClass('fadeup')) {
																			$this.animate({'top':$this.data('_top')+20+"px",
																							 'opacity':0},
																							{duration:0,queue:false})
																				   .delay(edelay + (counter+1)*350)
																				   .animate({'top':$this.data('_top')+"px",
																							 'opacity':$this.data('_op')},
																							{duration:500,queue:true})
																		counter++;
																	}
																	
																	
																	//// -  FADE RIGHT   -   ////
																	if ($this.hasClass('faderight')) {
																		$this.animate({'left':$this.data('_left')-20+"px",
																					 'opacity':0},
																					{duration:0,queue:false})
																		   .delay(edelay + (counter+1)*350)
																		   .animate({'left':$this.data('_left')+"px",
																					'opacity':$this.data('_op')},
																					{duration:500,queue:true})
																		counter++;
																	}
																	
																	
																	//// -  FADE DOWN  -   ////
																	if ($this.hasClass('fadedown')) {
																			$this.animate({'top':$this.data('_top')-20+"px",
																							 'opacity':0},
																							{duration:0,queue:false})
																				   .delay(edelay + (counter+1)*350)
																				   .animate({'top':$this.data('_top')+"px",
																							 'opacity':$this.data('_op')},
																							{duration:500,queue:true})
																		counter++;
																	}
																	
																	
																	//// -  FADE LEFT   -   ////
																	if ($this.hasClass('fadeleft')) {
																		$this.animate({'left':$this.data('_left')+20+"px",
																					 'opacity':0},
																					{duration:0,queue:false})
																		   .delay(edelay + (counter+1)*350)
																		   .animate({'left':$this.data('_left')+"px",
																					'opacity':$this.data('_op')},
																					{duration:500,queue:true})
																		counter++;
																	}
																	
																	//// -  FADE   -   ////
																	if ($this.hasClass('fade')) {
																		$this.animate({'opacity':0},
																					{duration:0,queue:false})
																		   .delay(edelay + (counter+1)*350)
																		   .animate({'opacity':$this.data('_op')},
																					{duration:500,queue:true})
																		counter++;
																	}
																	
																	
																	//// - WIPE UP/DOWN/LEFT/RIGHT - ////
																	if ($this.hasClass('wipeup') || $this.hasClass('wipedown') || $this.hasClass('wipeleft') || $this.hasClass('wiperight')) {
																		$this.animate({'opacity':0},{duration:0,queue:false});
																		setTimeout(function() {
																			if ($this.find('.wipermode').length==0) {
																				var actww=$this.outerWidth();
																				var acthh=$this.outerHeight();
																				var params={	
																							color:$this.css('backgroundColor'),
																							border:$this.css('border'),
																							
																							borderradiusmoz:$this.css('-moz-border-radius-topleft'),
																							borderradiusweb:$this.css('-webkit-border-top-left-radius'),
																							borderradius:$this.css('borderTopLeftRadius'),
																							
																							boxmoz:$this.css('-moz-box-shadow'),
																							boxweb:$this.css('-webkit-box-shadow'),
																							box:$this.css('box-shadow'),
																							
																							padtop:"0px",//$this.css('paddingTop'),
																							padleft:"0px",//$this.css('paddingLeft')
																							
																							paddingT:parseInt($this.css('paddingTop'),0),
																							paddingB:parseInt($this.css('paddingBottom'),0),
																							paddingL:parseInt($this.css('paddingLeft'),0),
																							paddingR:parseInt($this.css('paddingRight'),0),
																							
																							ww:actww + 30,
																							hh:acthh + 20
																						  };
																				$this.data('params',params);
																				
																				$this.wrapInner('<div style="position:absolute;overflow:hidden;width:'+(actww-(params.paddingL+params.paddingR))+'px;height:'+(acthh-(params.paddingT+params.paddingB))+'px;"><div class="wipermode-origin" style="top:0px;left:0px;position:absolute;width:'+actww+'px;height:'+acthh+'px;"></div></div>');			
																				$this.prepend('<div class="wipermode" style="width:'+actww+'px;height:'+acthh+'px;background-color:'+params.color+';top:0px;left:0px;position:absolute;border-radius:'+params.borderradius+';-moz-border-radius:'+params.borderradiusmoz+';-webkit-border-radius:'+params.borderradiusweb+';-moz-box-shadow:'+params.boxmoz+';-webkit-box-shadow:'+params.boxweb+';box-shadow:'+params.box+';"></div>');																				
																				$this.css({'background':'none'});																																								
																				
																			} 
																			
																			var params = $this.data('params');
																			// STOP ANIMATION, AND RESTORE ORIGINAL POSITION
																			$this.stop(true,true).find('.wipermode-origin')
																			$this.stop(true,true);
																			$this.find('.wipermode').stop(true,true);
																			

																			
																			
																			// REGISTER THE BG AND TEXT AT THE RIGHT POSITION (START POSITION)
																			if ($this.hasClass('wipeup')) {
																				$this.find('.wipermode-origin').css({'top':(-1*params.hh)+"px",'left':params.padleft});																			
																				$this.find('.wipermode').css({'top':(params.hh)+"px"});																				
																			} else {
																				if ($this.hasClass('wipedown')) {
																					$this.find('.wipermode-origin').css({'top':(params.hh)+"px",'left':params.padleft});																			
																					$this.find('.wipermode').css({'top':(-1*params.hh)+"px"});	
																				} else {
																					if ($this.hasClass('wipeleft')) {
																						$this.find('.wipermode-origin').css({'top':params.padtop,'left':(-1*params.ww)+"px"});																			
																						$this.find('.wipermode').css({'left':(params.ww)+"px"});	
																					} else {
																							$this.find('.wipermode-origin').css({'top':params.padtop,'left':(params.ww)+"px"});																			
																							$this.find('.wipermode').css({'left':(-1*params.ww)+"px"});	
																					}
																				}
																			}
																																						
																			$this.animate({'opacity':'1.0'},{duration:300,queue:false});
																			$this.find('.wipermode-origin').animate({'top':params.padtop, 'left':params.padleft},{duration:500,easing:'easeOutSine', queue:false});
																			$this.find('.wipermode').animate({'top':'0px','left':'0px'},{duration:500,easing:'easeOutExpo', queue:false});
																			
																		},(edelay + (counter+1)*350));
																		counter++;
																	}
																	
																	
																	//// - masklesswipe UP/DOWN/LEFT/RIGHT - ////
																	if ($this.hasClass('masklesswipeup') || $this.hasClass('masklesswipedown') || $this.hasClass('masklesswipeleft') || $this.hasClass('masklesswiperight')) {
																		$this.animate({'opacity':0},{duration:0,queue:false});
																		setTimeout(function() {
																			if ($this.find('.masklesswipemode').length==0) {
																				var actww=$this.outerWidth();
																				var acthh=$this.outerHeight();
																				var params={	
																							color:$this.css('backgroundColor'),
																							border:$this.css('border'),
																							
																							borderradiusmoz:$this.css('-moz-border-radius-topleft'),
																							borderradiusweb:$this.css('-webkit-border-top-left-radius'),
																							borderradius:$this.css('borderTopLeftRadius'),
																							
																							boxmoz:$this.css('-moz-box-shadow'),
																							boxweb:$this.css('-webkit-box-shadow'),
																							box:$this.css('box-shadow'),
																							
																							padtop:$this.css('paddingTop'),
																							padleft:$this.css('paddingLeft')
																						  };
																				$this.data('params',params);
																				$this.wrapInner('<div class="masklesswipemode-origin" style="top:0px;left:0px;position:absolute;width:'+actww+'px;height:'+acthh+'px;"></div>');			
																				$this.prepend('<div class="masklesswipemode" style="width:'+actww+'px;height:'+acthh+'px;background-color:'+params.color+';top:0px;left:0px;position:absolute;border-radius:'+params.borderradius+';-moz-border-radius:'+params.borderradiusmoz+';-webkit-border-radius:'+params.borderradiusweb+';-moz-box-shadow:'+params.boxmoz+';-webkit-box-shadow:'+params.boxweb+';box-shadow:'+params.box+';"></div>');																				
																				$this.css({'background':'none'});																																								
																				
																			} 
																			
																			var params = $this.data('params');
																			// STOP ANIMATION, AND RESTORE ORIGINAL POSITION
																			$this.stop(true,true).find('.masklesswipemode-origin')
																			$this.stop(true,true);
																			$this.find('.masklesswipemode').stop(true,true);
																			
																			var distance=50;
																			
																			// REGISTER THE BG AND TEXT AT THE RIGHT POSITION (START POSITION)
																			if ($this.hasClass('wipeup')) {
																				$this.find('.masklesswipemode-origin').css({'top':(-1*distance)+"px",'left':params.padleft});																			
																				$this.find('.masklesswipemode').css({'top':(distance)+"px"});																				
																			} else {
																				if ($this.hasClass('masklesswipedown')) {
																					$this.find('.masklesswipemode-origin').css({'top':(distance)+"px",'left':params.padleft});																			
																					$this.find('.masklesswipemode').css({'top':(-1*distance)+"px"});	
																				} else {
																					if ($this.hasClass('masklesswipeleft')) {
																						$this.find('.masklesswipemode-origin').css({'top':params.padtop,'left':(-1*distance)+"px"});																			
																						$this.find('.masklesswipemode').css({'left':(distance)+"px"});	
																					} else {
																							$this.find('.masklesswipemode-origin').css({'top':params.padtop,'left':(distance)+"px"});																			
																							$this.find('.masklesswipemode').css({'left':(-1*distance)+"px"});	
																					}
																				}
																			}
																																						
																			$this.animate({'opacity':'1.0'},{duration:800,queue:false});
																			$this.find('.masklesswipemode-origin').animate({'top':params.padtop, 'left':params.padleft},{duration:800,easing:'easeInExpo', queue:false});
																			$this.find('.masklesswipemode').animate({'top':'0px','left':'0px'},{duration:800,easing:'easeOutExpo', queue:false});
																			
																		},(edelay + (counter+1)*350));
																		counter++;
																	}
																	
															
														});	// END OF TEXT ANIMS ON DIVS
					
				}
})(jQuery);			

				
			

			   