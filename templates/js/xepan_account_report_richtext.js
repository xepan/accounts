$.each({
	xepan_account_report_richtext: function(obj,options,frontend,mention_options,accounts_list){
		tinymce.baseURL = "./vendor/tinymce/tinymce";

        // tinymce.editors=[];
        // tinymce.activeEditors=[];
        $(tinymce.editors).each(function(index, el) {
            if(el.id == $(obj).attr('id')) {
                try{
                        $(obj).tinymce().remove();
                }catch(err){
                        console.log(err);
                        console.log('tineymce.remove() on ');
                        console.log(el);
                }
            }
        });

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
                            folders: false
                        }
                    }
                }).dialogelfinder('instance');
            },
            plugins: [
                "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                "save table contextmenu directionality emoticons template paste textcolor colorpicker imagetools mention"
            ],
            external_plugins:{'mention':'/vendor/xepan/base/templates/js/tinymce-plugins/mention/mention/plugin.min.js'},
            mentions: $.extend({
                renderDropdown: function() {
                    //add twitter bootstrap dropdown-menu class
                    return '<ul class="rte-autocomplete" style="z-index:3000"></ul>';
                },
                source: [],
                delimiter: []
            }, mention_options),
            
            toolbar1: "insertfile undo redo | styleselect | bold italic fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons tmp",
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
            document_base_url : $('head base').attr('href'),
            // valid_elements: '*[*]',
            // force_br_newlines: false,
            // force_p_newlines: false,
            // forced_root_block: '',
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
                ed.addButton('tmp', {
                  icon:'codesample',
                  tooltip: "Insert Accounts Template",
                  onclick: function(){
                    $.univ().dialogOK('HAHA',JSON.stringify(accounts_list),function(){
                        ed.insertContent(prompt("ASDASD"));
                    });
                  }
                });
            }
        },options);
            

        tinymce.init(com_options);
	}
},$.univ._import);