/**
 * @filename : editor_plugin.js
 * @description : jQuery UI Inline Popups plugin to replace the default inlinepopups
 * @developer : badsyntax (Richard Willis)
 * @contact : http://badsyntax.co
 * @moreinfo : http://is.gd/j1FuI
 */

(function() {

	var DOM = tinymce.DOM, 
		Event = tinymce.dom.Event, 
		each = tinymce.each;

	// Create the editor plugin
	tinymce.create('tinymce.plugins.jQueryInlinePopups', {
			
		init : function(ed, url) {

			// Replace window manager
			ed.onBeforeRenderUI.add(function() {				
				ed.windowManager = new tinymce.InlineWindowManager(ed);
			});
		},

		getInfo : function() {
			return {
				longname : 'jQuery UI Inline Popups',
				author : 'Richard Willis',
				authorurl : 'http://badsyntax.co',
				infourl : 'http://is.gd/j1FuI',
				version : '0.1b'
			};
		}
	});

	// Create the window manager
	tinymce.create('tinymce.InlineWindowManager:tinymce.WindowManager', {
			
		InlineWindowManager : function(ed) {
			this.parent(ed);
			this.windows = {};
		},

		open : function(f, p) {

			f = f || {};
			p = p || {};

			// Run native windows
			if (!f.inline) {
				return t.parent(f, p);
			}
				
			var 
				t = this,
				id = DOM.uniqueId(),

				// Dialog config
				config = {
					title: f.title || '',
					width: 'auto',
					height: 'auto',
					modal: true,
					resizable: false,
					draggable: true,
					dialogClass: 'ui-dialog-tinymce',
					// match the tinymce inlinepopups starting z-index (fixes #3)
					zIndex: 300000
				},

				// Dialog element
				dialog = $('<div />')
					.attr('id', 'dialog-' + id)
					.hide()
					.appendTo('body'),

				// Window info
				w = {
					id : id,
					features : f,
					element: dialog
				};

			// Only store selection if the type is a normal window.
			// This is required at least for IE to remeber the position
			// before the focus from editor is lost.
			if (!f.type) {
				this.bookmark = this.editor.selection.getBookmark(1);
			}

			// Inline content
			if (f.content){

				if (f.type == 'confirm'){
					config.buttons = [{
						'text': __('answer_yes'),
						'click': function(e){
							f.button_func(true);
						}
					}, {
						'text': __('answer_no'),
						'click': function(e){
							f.button_func(false);
						}
					}];
					config.open = function(event, ui) {
						$('.ui-dialog-tinymce-content').parent().prev().find('.ui-dialog-titlebar-close').hide();
						$('.ui-dialog-tinymce-content').parent().next('.ui-dialog-buttonpane').find('button:first').addClass('attention');
					}
				}
				else if (f.type == 'alert'){
					config.buttons = [{
						'text': __('dismiss_button_label'),
						'click': function(e){
							f.button_func(true);
						}
					}];
				}

				config.width = f.width;
			
				dialog.html($('<div />', {
					'class': 'ui-dialog-tinymce-content',
					'html': f.content
				}));
			}
			// iFramed document
			else 
			{
				var iframe = $('<iframe />', { 
					id: id + '_ifr',
					frameborder: 0 
				})
				.css({ 
					width: f.width,
					height: f.height
				})
				.appendTo(dialog);
			}

			p.mce_inline = true;
			p.mce_window_id = id;
			p.mce_auto_focus = f.auto_focus;
					
			this.features = f;
			this.params = p;
			this.onOpen.dispatch(this, f, p);

			dialog.dialog(config);
			
			// Load in iframe src
			if (!f.content) {
				iframe.attr( 'src', f.url || f.file );
			}

			// Add window
			t.windows[id] = w;

			return w;
		},

		resizeBy : function(dw, dh, id) { return; },

		focus : function(id) { return; },

		close : function(win, id) {
	
			var t = this, w, id = id || win.frameElement.id.replace(/_ifr$/, '');

			// Probably not inline
			if (!t.windows[id]) {
				t.parent(win);
				return;
			}

			if (w = t.windows[id]) {
				w.element.dialog('destroy').remove();
				delete t.windows[id];
			}
		},

		setTitle : function(w, ti) {
			var id = w.frameElement.id.replace(/_ifr$/, '');
			$('#ui-dialog-title-dialog-' + id).html(ti);
		},

		alert : function(txt, cb, s) {
			this._messagePopup('alert', __('notice'), txt, cb, s);
		},

		confirm : function(txt, cb, s) {
			this._messagePopup('confirm', __('confirm_box_title'), txt, cb, s);
		},
		
		_messagePopup : function(type, title, txt, cb, s) {
			var t = this, w;
			w = t.open({
				title : title,
				type : type,
				button_func : function(s) {
					(cb) && cb.call(s || t, s);
					t.close(null, w.id);
				},
				content : DOM.encode(t.editor.getLang(txt, txt)),
				inline : 1,
				width : 400,
				height : 130
			});
		}
	});

	// Register plugin
	tinymce.PluginManager.add('jqueryinlinepopups', tinymce.plugins.jQueryInlinePopups);
})();