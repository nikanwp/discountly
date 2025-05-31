import { useEffect, useRef } from '@wordpress/element';

const RichTextEditor = ({ value, onChange }) => {
    const editorRef = useRef(null);
    const editorIdRef = useRef(null);

    useEffect(() => {
        const initializeEditor = () => {
            if (typeof wp !== 'undefined' && wp.editor && editorRef.current) {
                editorIdRef.current = editorRef.current.id;

                wp.editor.initialize(editorIdRef.current, {
                    tinymce: {
                        wpautop: false,
                        formats: {
                            alignleft: [
                                { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'left' } },
                                { selector: 'img,table,dl.wp-caption', classes: 'alignleft' },
                            ],
                            aligncenter: [
                                { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'center' } },
                                { selector: 'img,table,dl.wp-caption', classes: 'aligncenter' },
                            ],
                            alignright: [
                                { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'right' } },
                                { selector: 'img,table,dl.wp-caption', classes: 'alignright' },
                            ],
                            strikethrough: { inline: 'del' },
                        },
                        relative_urls: false,
                        remove_script_host: false,
                        convert_urls: false,
                        browser_spellcheck: true,
                        fix_list_elements: true,
                        entities: '38,amp,60,lt,62,gt',
                        entity_encoding: 'raw',
                        keep_styles: false,
                        paste_webkit_styles: 'font-weight font-style color',
                        preview_styles: 'font-family font-size font-weight font-style text-decoration text-transform',
                        tabfocus_elements: ':prev,:next',
                        plugins:
                            'charmap,hr,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview',
                        resize: 'vertical',
                        menubar: false,
                        indent: false,
                        toolbar1:
                            'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
                        toolbar2:
                            'formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                        toolbar3: '',
                        toolbar4: '',
                        body_class: 'id post-type-post post-status-publish post-format-standard',
                        wpeditimage_disable_captions: false,
                        wpeditimage_html5_captions: true,
                    },
                    quicktags: true,
                    mediaButtons: true,
                });
            }
        };

        initializeEditor();

        const updateEditorContent = () => {
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get(editorIdRef.current)) {
                const editor = tinyMCE.get(editorIdRef.current);
                if (editor) {
                    editor.setContent(value);
                }
            }
        };

        updateEditorContent();

        const handleEditorBlur = () => {
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get(editorIdRef.current)) {
                const editor = tinyMCE.get(editorIdRef.current);
                if (editor) {
                    onChange(editor.getContent()); // Update value on blur
                }
            }
        };

        const handleEditorChange = () => {
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get(editorIdRef.current)) {
                const editor = tinyMCE.get(editorIdRef.current);
                if (editor) {
                    editor.on('blur', handleEditorBlur); // Attach blur event listener
                }
            }
        };

        // Event listener to handle editor changes
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get(editorIdRef.current)) {
            const editor = tinyMCE.get(editorIdRef.current);
            editor.on('change', handleEditorChange);
        }

        return () => {
            if (typeof wp !== 'undefined' && wp.editor && editorIdRef.current) {
                wp.editor.remove(editorIdRef.current);
            }
        };

    }, [value, onChange]);

    return (
        <div>
            <textarea ref={editorRef} id="RichTextEditor" className="wp-editor-area" value={value} />
        </div>
    );
};

export default RichTextEditor;
