/*!
 * dialogBox v0.0.2
 * by Liuyuchao
 * Copyright 2015.3
 * Date: Wed Mar 25 2015

 */

 ;(function($,window,document){

 	var pluginName = 'dialogBox',
 		defaults = {

 			width: null, //number,弹出层宽度
 			height: null,  //number,弹出层高度
 			autoSize: true,  //boolean,是否自适应尺寸,默认自适应
 			autoHide: false,  //boolean,是否自自动消失，配合time参数共用
 			time: 3000,  //number,自动消失时间，单位毫秒
 			zIndex: 99999,  //number,弹出层定位层级
 			hasMask: false,  //boolean,是否显示遮罩层
 			hasClose: false,  //boolean,是否显示关闭按钮
 			hasBtn: false,  //boolean,是否显示操作按钮，如取消，确定
 			confirmValue: null,  //string,确定按钮文字内容
 			confirm: function(){}, //function,点击确定后回调函数
 			cancelValue: null,  //string,取消按钮文字内容
 			cancel: function(){},  //function,点击取消后回调函数，默认关闭弹出框
 			effect: '', //string,动画效果：fade(默认),newspaper,fall,scaled,flip-horizontal,flip-vertical,sign,
 			type: 'normal', //string,对话框类型：normal(普通对话框),correct(正确/操作成功对话框),error(错误/警告对话框)
 			title: '',  //string,标题内容，如果不设置，则连同关闭按钮（不论设置显示与否）都不显示标题
 			content: '',  //string,DOM object,jQuery Object,正文内容，可以为DOM对象，jQuery对象，纯字符串，html标签字符串，以及URL地址，当content为URL地址时，将内嵌目标页面的iframe。
 			callback: function(){},   //function,回调函数
 			close: function(){}  //function,关闭回调函数

 		};

 	function DialogBox(element,options){
 		this.element = element;
 		this.settings = $.extend({}, defaults, options);
 		this.init();
 	}

 	DialogBox.prototype = {

 		//初始化弹出框
 		init: function(){
 			var that = this,
 				element = this.element;

 			that.render(element);
 			that.setStyle();
 			that.show();
 			that.trigger(element);
 		},

 		//获取元素id值
        getIdValue: function(element){
            var $this = $(element),
                selectID = $this.attr('id');

            if(typeof(selectID) !== 'undefined'){
                return selectID;
            }else{
                selectID='';
                return selectID;
            }
        },

 		//创建弹出框
 		create: function(element){
 			var that = this,
 				$this = $(element),
 				id = that.getIdValue(element),
 				title =  that.settings.title,
 				hasBtn = that.settings.hasBtn,
 				hasMask = that.settings.hasMask,
 				hasClose = that.settings.hasClose,
 				confirmValue = that.settings.confirmValue,
 				cancelValue = that.settings.cancelValue,
 				dialogHTML = [];

 			if(!title){
 				dialogHTML[0] = '<section id="' + id + '" class="dialog-box"><div class="dialog-box-container"><div class="dialog-box-content"></div>';
 			}else{
 				if(!hasClose){
					dialogHTML[0] = '<section id="' + id + '" class="dialog-box"><div class="dialog-box-container"><div class="dialog-box-title"><h3>'+ title + '</h3></div><div class="dialog-box-content"></div>';
 				}else{
 					dialogHTML[0] = '<section id="' + id + '" class="dialog-box"><div class="dialog-box-container"><div class="dialog-box-title"><h3>'+ title + '</h3><span class="dialog-box-close">×</span></div><div class="dialog-box-content"></div>';
 				}
 			}

 			if(!hasBtn){
 				dialogHTML[1] = '</div></section>';
 			}else{
 				if(confirmValue && cancelValue){
 					dialogHTML[1] = '<div class="dialog-btn"><button class="dialog-btn-cancel">' + cancelValue + '</button><button class="dialog-btn-confirm">' + confirmValue + '</button></div></div></section>';
 				}else if(cancelValue){
 					dialogHTML[1] = '<div class="dialog-btn"><button class="dialog-btn-cancel">' + cancelValue + '</button></div></div></section>';
 				}else if(confirmValue){
 					dialogHTML[1] = '<div class="dialog-btn"><button class="dialog-btn-confirm">' + confirmValue + '</button></div></div></section>';
 				}else{
 					dialogHTML[1] = '<div class="dialog-btn"><button class="dialog-btn-cancel">Cancel</button><button class="dialog-btn-confirm">Ok</button></div></div></section>';
 				}
 			}

 			if(!hasMask){
 				dialogHTML[2] = '';
 			}else{
 				dialogHTML[2] = '<div id="dialog-box-mask"></div>';
 			}

 			return dialogHTML;
 		},

 		//渲染弹出框
 		render: function(element){
 			var that = this,
 				$this = $(element),
 				dialogHTML = that.create($this),
 				$content = that.parseContent();

 			$this.replaceWith(dialogHTML[0] + dialogHTML[1]);

 			if(typeof($content) === 'object'){
 				$content.appendTo('.dialog-box-content');
 			}else{
 				$('.dialog-box-content').append($content);
 			}

 			$('body').append(dialogHTML[2]);
 		},

 		//解析并处理弹出框内容
 		parseContent: function(){
 			var that = this,
 				content = that.settings.content,
 				width = that.settings.width,
 				height = that.settings.height,
 				type = that.settings.type,
 				$iframe = $('<iframe>'),
 				random = '?tmp=' + Math.random(),
 				urlReg = /^(https?:\/\/|\/|\.\/|\.\.\/)/;

 			//是外部页面时
 			if(urlReg.test(content)){

 				$iframe.attr({
 					src: content + random,
 					marginheight: '0',
 					marginwidth: '0',
 					frameborder: 'no',
 					scrolling: 'no',
 					name: 'dialog-box-iframe',
 					id: 'dialog-box-iframe'
 				})
 				.on('load',function(){

 					//动态自适应iframe高度;
 					var $iframe = $(window.frames['dialog-box-iframe'].document),
 						$iframeBody = $(window.frames['dialog-box-iframe'].document.body),
 						iframeWidth = $iframe.outerWidth() - 8,
 						iframeHeight = $iframe.outerHeight() - 16,
 						$dialogBox = $('.dialog-box'),
 						$content = $('.dialog-box-content'),
 						$container = $('.dialog-box-container');

 						dialogBoxWidth = iframeWidth + 40;
 						dialogBoxHeight = iframeHeight + 130;

 					if(that.settings.autoSize){
 						$(this).width(iframeWidth);
 						$(this).height(iframeHeight);

 						$iframeBody.css({
 							margin: '0',
 							padding: '0'
 						});

 						$content.css({
 							width: iframeWidth + 'px',
 							height: iframeHeight + 'px'
 						});

 						$container.css({
 							width: dialogBoxWidth + 'px',
 							height: function(){
 								if(that.settings.hasBtn){
 									return dialogBoxHeight + 'px';
 								}else{
 									return dialogBoxHeight - 54 + 'px';
 								}
 							}
 						});

 						$dialogBox.css({
 							width: dialogBoxWidth,
 							height: function(){
 								if(that.settings.hasBtn){
									if(type === '' || type === 'normal'){
	 									return dialogBoxHeight + 'px';
	 								}else if(type === 'error' || type === 'correct'){
	 									dialogBoxHeight = dialogBoxHeight + 4;
	 									return dialogBoxHeight + 'px';
	 								}
 								}else{
 									return dialogBoxHeight - 50 + 'px';
 								}
 							},
 							'margin-top': function(){
 								if(that.settings.hasBtn){
									if(type === '' || type === 'normal'){
	 									return -Math.round(dialogBoxHeight/2) + 'px';
	 								}else if(type === 'error' || type === 'correct'){
	 									dialogBoxHeight = dialogBoxHeight;
	 									return -Math.round(dialogBoxHeight/2) + 'px';
	 								}
 								}else{
 									return -Math.round((dialogBoxHeight - 50)/2) + 'px';
 								}
 							},
 							'margin-left': -Math.round(dialogBoxWidth/2) + 'px'
 						});

 					}else{
 						$(this).width(that.settings.width - 40);
 						$(this).height(that.settings.height - 130);
 					}
 				});
				return $iframe;

			//是jQuery对象时
 			}else if(content instanceof jQuery){
 				return content.clone(true,true);
 			//是DOM对象时
 			}else if(content.nodeType === 1 && content.nodeName === 'string'){
 				return content.innerHTML;
 			}else{
 				return content;
 			}
 		},

 		//显示弹出框
 		show: function(){
 			$('.dialog-box').css({display:'block'});

 			setTimeout(function(){
 				$('.dialog-box').addClass('show');
 			},50)

 			$('#dialog-box-mask').show();
 		},

 		//隐藏弹出框
 		hide: function(element){
 			var $this = $(element),
 				$dialogBox = $('.dialog-box'),
 				$iframe = $('#dialogBox-box-iframe');

 			$dialogBox.removeClass('show');

 			setTimeout(function(){
 				if($iframe){
 					$iframe.attr('src','_blank');
 				}

 				$dialogBox.replaceWith('<div id="' + $this.attr('id') + '"></div/>');
 				$('#dialog-box-mask').remove();
 			},150)
 		},

 		//设置弹出框样式
 		setStyle: function(){
 			var that = this,
 				$dialog = $('.dialog-box'),
 				$container = $('.dialog-box-container'),
 				$content = $('.dialog-box-content'),
 				$mask  = $('#dialog-box-mask'),
 				type = that.settings.type,
 				EFFECT = 'effect';

 			//弹出框外框样式
 			$dialog.css({
 				width: function(){
 					if(that.settings.width){
 						return that.settings.width + 'px';
 					}else{
 						if(that.settings.autoSize){
 							return $(this).width();
 						}
 					}
 				},
 				height: function(){
 					if(that.settings.height){
 						if(type === '' || type === 'normal'){
 							return that.settings.height + 'px';
 						}else if(type === 'error' || type === 'correct'){
 							return that.settings.height + 4 + 'px';
 						}
 					}else{
 						if(that.settings.autoSize){
 							return $(this).height();
 						}
 					}
 				},
 				'margin-top': function(){
 					var height;

					if(type === '' || type === 'normal'){
						if(that.settings.autoSize){
							height = $(this).height();
						}else{
							height = that.settings.height;
						}
					}else if(type === 'error' || type === 'correct'){
						if(that.settings.autoSize){
							height = $(this).height();
						}else{
							height = that.settings.height + 4;
						}
					}
					return -Math.round(height/2) + 'px';
 				},
 				'margin-left': function(){
 					var width = $(this).width();
 					return -Math.round(width/2) + 'px';
 				},
 				'z-index': that.settings.zIndex
 			});

 			//弹出框内层容器样式
 			$container.css({
 				width: function(){
 					if(that.settings.width){
						return that.settings.width + 'px';
 					}else{
 						return;
 					}
 				},
 				height: function(){
 					if(that.settings.height){
 						return that.settings.height + 'px';
 					}else{
 						return;
 					}
 				}
 			});

 			//弹出框内容样式
 			$content.css({
 				width: function(){
 					if(that.settings.width){
 						return that.settings.width - 40 + 'px';
 					}else{
 						return;
 					}
 				},
 				height: function(){
 					if(that.settings.height){
 						return that.settings.height - 130 + 'px';
 					}else{
 						return;
 					}
 				}
 			});

 			//遮罩层样式
 			$mask.css({
 				height: $(document).height() + 'px'
 			});


 			//判断弹出框类型
 			switch(that.settings.type){
 				case 'correct':
 					$container.addClass('correct');
 					break;
 				case 'error':
 					$container.addClass('error');
 					break;
 				default:
 					$container.addClass('normal');;
 					break;
 			}

 			//弹出框多种动画效果
 			switch(that.settings.effect){
 				case 'newspaper':
 					$dialog.addClass(EFFECT + '-newspaper');
 					break;
 				case 'fall':
 					$dialog.addClass(EFFECT + '-fall');
 					break;
 				case 'scaled':
 					$dialog.addClass(EFFECT + '-scaled');
 					break;
 				case 'flip-horizontal':
 					$dialog.addClass(EFFECT + '-flip-horizontal');
 					break;
 				case 'flip-vertical':
 					$dialog.addClass(EFFECT + '-flip-vertical');
 					break;
 				case 'sign':
 					$dialog.addClass(EFFECT + '-sign');
 					break;
 				default:
 					$dialog.addClass(EFFECT + '-fade');
 					break;
 			}

 		},

 		//弹出框触屏器(系列事件)
 		trigger: function(element,event){
 			var that = this,
 				$this = $(element);

 			if($.isFunction(that.settings.callback)){
 				that.settings.callback();
 			}

 			$('.dialog-box-close,#dialog-box-mask').on('click',function(event){
				that.settings.close.call(that);
				event.stopPropagation();
			})

 			$('.dialog-box-close,#dialog-box-mask,.dialog-btn-cancel,.dialog-btn-confirm').on('click',function(event){
 				that.hide($this);
 				event.stopPropagation();
 			});

 			$(document).keyup(function(event){
 				if(event.keyCode === 27){
 					that.hide($this);
 				}
 			});

 			if(that.settings.autoHide){
 				setTimeout(function(){
 					that.hide($this);
 				},that.settings.time)
 			}

 			if($.isFunction(that.settings.confirm)){
 				$('.dialog-btn-confirm').on('click',function(){
 					that.settings.confirm();
 				})
 			}

 			if($.isFunction(that.settings.cancel)){
 				$('.dialog-btn-cancel').on('click',function(){
 					that.settings.cancel();
 				})
 			}

 		}

 	};

 	$.fn[pluginName] = function(options) {
        this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new DialogBox(this, options));
			}
        });
		return this;
    };

 })(jQuery,window,document);
