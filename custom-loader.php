<?php
/**
 * Plugin Name: Custom Script and Style Loader
 * Description: A plugin to load custom JavaScript and CSS files in WordPress, with the capability to override functions defined in the theme.
 * Version: 1.0
 * Author: Amri Karisma
 * Author URI: https://github.com/amrikarisma
 */

function custom_script_style_loader_enqueue_scripts() {
    $js_file = plugin_dir_path(__FILE__) . 'js/custom-script.js';
    $css_file = plugin_dir_path(__FILE__) . 'css/custom-style.css';
    
    $js_version = file_exists($js_file) ? filemtime($js_file) : null;
    $css_version = file_exists($css_file) ? filemtime($css_file) : null;

    $js_content = get_option('custom_js_content', '');
    $css_content = get_option('custom_css_content', '');

    if (!empty($js_content)) {
        file_put_contents($js_file, $js_content);
    }

    if (!empty($css_content)) {
        file_put_contents($css_file, $css_content);
    }

    wp_enqueue_script('custom-js-loader-script', plugins_url('js/custom-script.js', __FILE__), array(), $js_version, true);
    wp_enqueue_style('custom-css-loader-style', plugins_url('css/custom-style.css', __FILE__), array(), $css_version, 'all');
}
add_action('wp_enqueue_scripts', 'custom_script_style_loader_enqueue_scripts', 999);

function custom_file_editor_menu() {
    add_submenu_page(
        'tools.php',
        'Custom File Editor',
        'Custom File Editor',
        'manage_options',
        'custom-file-editor',
        'custom_file_editor_page'
    );
}
add_action('admin_menu', 'custom_file_editor_menu');

function custom_file_editor_enqueue_ace($hook) {
    if ($hook != 'tools_page_custom-file-editor') return;

    wp_enqueue_script('ace-editor', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js', array(), null, true);

    wp_add_inline_script('ace-editor', "
        document.addEventListener('DOMContentLoaded', function() {
            var editorJS = ace.edit('editor-js');
            editorJS.setTheme('ace/theme/monokai');
            editorJS.session.setMode('ace/mode/javascript');
            editorJS.setOptions({ maxLines: Infinity });
            
            var editorCSS = ace.edit('editor-css');
            editorCSS.setTheme('ace/theme/monokai');
            editorCSS.session.setMode('ace/mode/css');
            editorCSS.setOptions({ maxLines: Infinity });

            document.getElementById('custom-js-content').value = editorJS.getValue();
            document.getElementById('custom-css-content').value = editorCSS.getValue();
            
            document.querySelector('form').addEventListener('submit', function() {
                document.getElementById('custom-js-content').value = editorJS.getValue();
                document.getElementById('custom-css-content').value = editorCSS.getValue();
            });
        });
    ");
}
add_action('admin_enqueue_scripts', 'custom_file_editor_enqueue_ace');

function custom_file_editor_page() {
    if (isset($_POST['custom_js_content']) || isset($_POST['custom_css_content'])) {
        if (current_user_can('manage_options')) {
            if (isset($_POST['custom_js_content'])) {
                update_option('custom_js_content', stripslashes($_POST['custom_js_content']));
            }
            if (isset($_POST['custom_css_content'])) {
                update_option('custom_css_content', stripslashes($_POST['custom_css_content']));
            }
            echo '<div class="notice notice-success"><p>Files updated successfully.</p></div>';
        }
    }

    $js_content = get_option('custom_js_content', '');
    $css_content = get_option('custom_css_content', '');

    ?>
    <div class="wrap">
        <h1>Custom File Editor</h1>
        <form method="post">
            <h2>Edit custom-script.js</h2>
            <div id="editor-js" style="height: 300px; width: 100%;"><?php echo esc_textarea($js_content); ?></div>
            <textarea id="custom-js-content" name="custom_js_content" style="display:none;"><?php echo esc_textarea($js_content); ?></textarea>

            <h2>Edit custom-style.css</h2>
            <div id="editor-css" style="height: 300px; width: 100%;"><?php echo esc_textarea($css_content); ?></div>
            <textarea id="custom-css-content" name="custom_css_content" style="display:none;"><?php echo esc_textarea($css_content); ?></textarea>

            <?php submit_button('Save Changes'); ?>
        </form>
    </div>
    <?php
}