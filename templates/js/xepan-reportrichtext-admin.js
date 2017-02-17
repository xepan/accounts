$.each({
	
	richtext: function(obj,options,frontend){
		tinymce.baseURL = "./vendor/tinymce/tinymce";

        tinymce.editors=[];
        tinymce.activeEditors=[];

        $(document).on('focusin', function(event) {
            if ($(event.target).closest(".mce-window").length) {
                event.stopImmediatePropagation();
            }
        });

        com_options = $.extend({
            selector: '#'+$(obj).attr('id'),
            init_instance_callback : function(editor) {
                // console.log("Editor: " + editor.id + " is now initialized.");
            },
            file_browser_callback: function elFinderBrowser(field_name, url, type, win) {
                $('<div/>').dialogelfinder({
                    url: 'index.php?page=xepan_base_elconnector&cut_page=true',
                    lang: 'en',
                    width: 840,
                    destroyOnClose: true,
                    getFileCallback: function(files, fm) {
                        $('#' + field_name).val(files.url);
                    },
                    commandsOptions: {
                        getfile: {
                            oncomplete: 'close',
                            folders: true
                        }
                    }
                }).dialogelfinder('instance');
            },
            content_css: "vendor/xepan/accounts/templates/css/tinymention.css",
            plugins: [
                "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                "save table contextmenu directionality emoticons template paste textcolor colorpicker imagetools mention"
            ],
            toolbar1: "insertfile undo redo | styleselect | bold italic fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons",
            fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
            image_advtab: true,
            save_enablewhendirty: false,
            // content_css: 'templates/css/epan.css',
            browser_spellcheck : true,
            valid_children : "+body[style]",
            fontsize_formats: "6px 8px 10px 12px 14px 18px 24px 36px",
            // cleanup_on_startup: false,
            // trim_span_elements: false,
            verify_html: true,
            // cleanup: false,
            convert_urls: false,
            // valid_elements: '*[*]',
            // force_br_newlines: false,
            // force_p_newlines: false,
            // forced_root_block: '',
            mentions: {
                source: [
                    { name: 'Tyra Porcelli' }, 
                    { name: 'Brigid Reddish' },
                    { name: 'Ashely Buckler' },
                    { name: 'Teddy Whelan' }
                ]
            },
            setup: function(ed) {
                ed.on("change", function(ed) {
                    tinyMCE.triggerSave();
                });
                ed.on('init',function(ed){
                    $(obj)
                        .prev('.mce-container')
                        .find('.mce-edit-area')
                        .droppable({
                            drop: function(event, ui) {
                                tinyMCE.activeEditor.execCommand('mceInsertContent', false,ui.helper.html());
                            }
                        });
                });
                // ed.addMenuItem('save', {
                //     title: 'Save Content',
                //     icon: 'save',
                //     text: 'Save',
                //     context: 'file',
                //     onclick: function() {
                //         ed.windowManager.open({
                //             title:'Content Manager',
                //             url : '?page=xepan_cms_admin_contents&cut_page=1',
                //             width : $(window).width()*.8,
                //             height : $(window).height()*.8
                //         });
                //     }
                // });
                // ed.addMenuItem('load', {
                //     title: 'Load Content',
                //     text: 'Open',
                //     context: 'file',
                //     onclick: function() {
                //         $.univ().frameURL('Content Manager','xepan_cms_admin_contents',{'data':ed.getContent()});
                //     }
                // });
            }
        },options);
            

        tinymce.init(com_options);
	}
},$.univ._import);