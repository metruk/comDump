<?php

/*
Plugin Name: Infolinks Official Plugin (customized)
Plugin URI: https://www.infolinks.com
Description: This plugin will automatically add your Infolinks script to your website pages.
Author: Infolinks
Version: 3.2.2
Author URI: https://www.infolinks.com
*/

//install/uninstall function calls
register_activation_hook(__FILE__, 'infolinks_install');
register_uninstall_hook(__FILE__, 'infolinks_uninstall');

// actions
add_action('admin_menu', 'infolinks_pages');
add_action('wp_footer', 'integrate_infolinks_script');

// set the plugin's path
$pluginpath = plugins_url('/', __FILE__);

// define the path to the plugin's copy of jQuery
define('JS_PATH', $pluginpath . 'js/jquery.js');

/**
 * This function installs the plugin - mainly setting initial options and defaults.
 */
function infolinks_install()
{
    $plugin_data = $plugin_data = get_plugin_data(__FILE__, false, false);
    $version = strval($plugin_data['Version']);
    add_option('infolinks_publisherid', '2958936');
    add_option('infolinks_status', '1');
    add_option('infolinks_post', '1');
    update_option('infolinks_plugin_version', "WP_" . $version);
}

/**
 * This function uninstalls the plugin, by deleting set options.
 */
function infolinks_uninstall()
{
    delete_option('infolinks_status');
    delete_option('infolinks_publisherid');
    delete_option('infolinks_websiteid');
    delete_option('infolinks_excludepage');
    delete_option('infolinks_comments');
    delete_option('infolinks_post');
    delete_option('infolinks_keytag');
    delete_option('infolinks_jquery');
    delete_option('infolinks_plugin_version');
}

/**
 * Register plugin options pages (used in 'add_action' call above).
 */
function infolinks_pages()
{
    add_options_page('Info Links Text Ads', 'Infolinks Settings', 'manage_options', 'infolink-admin', 'infosettingoptions_page');
}

/**
 * This function generates the plugin settings page.
 */
function infosettingoptions_page()
{

    // is this a request to save plugin settings?
    if (isset($_POST['btnSave'])) {

        // check nonce for security
        check_admin_referer('infolinks_plugin_save');

        // obtain parameter values from request (or defaults)
        $infolinks_status = (isset($_POST['infolinks_status'])) ? $_POST['infolinks_status'] : 0;
        $infolinks_keytag = (isset($_POST['infolinks_keytag'])) ? $_POST['infolinks_keytag'] : 0;
        $infolinks_jquery = (isset($_POST['infolinks_jquery'])) ? $_POST['infolinks_jquery'] : 0;
        $infolinks_post = (isset($_POST['infolinks_post'])) ? $_POST['infolinks_post'] : 0;

        // persist the values
        update_option('infolinks_status', absint($infolinks_status));
        update_option('infolinks_publisherid', absint($_POST['infolinks_publisherid']));
        update_option('infolinks_excludepage', strip_tags($_POST['infolinks_excludepage']));
        update_option('infolinks_post', absint($infolinks_post));
        update_option('infolinks_keytag', absint($infolinks_keytag));
        update_option('infolinks_jquery', absint($infolinks_jquery));

        // show message
        echo '<div id="message" class="updated">Settings saved successfully</div>';
    }

    // is this a request to reset plugin settings?
    if (isset($_POST['btnReset'])) {

        // check nonce for security
        check_admin_referer('infolinks_plugin_save');

        // set options back to specified defaults
        update_option('infolinks_status', 1);
        update_option('infolinks_post', 1);
        update_option('infolinks_keytag', 1);
        update_option('infolinks_jquery', 0);
        delete_option('infolinks_excludepage');

        // show message
        echo '<div id="message" class="updated">Settings reset successfully</div>';
    }

    // load setting values
    $infolinks_status = get_option('infolinks_status');
    $infolinks_keytag = get_option('infolinks_keytag');
    $infolinks_jquery = get_option('infolinks_jquery');
    $infolinks_publisherid = get_option('infolinks_publisherid');
    $infolinks_excludepage = get_option('infolinks_excludepage');
    $infolinks_post = get_option('infolinks_post');
    ?>

    <!-- HTML -->

    <style type="text/css">
        .small_txt {
            font-size: 0.85em;
            color: #898989;
            font-family: Verdana, sans-serif;
        }

        h2 {
            font-family: Georgia, "Times New Roman", "Bitstream Charter", Times, serif;
            font-size: 24px;
            font-weight: bold;
            line-height: 35px;
            margin: 0;
            padding: 1em;
        }
    </style>

    <form method="post" name="frm_infolinks" id="frm_infolinks">

        <?php wp_nonce_field('infolinks_plugin_save'); ?>
        <input type="hidden" id="infoid" name="infoid" value=""/>

        <div style="float:left; width:40%;">
            <table border="0" width="100%">
                <tr>
                    <td>
                        <table width="95%">
                            <tr>
                                <td colspan="2"><h2>Infolinks Official Plugin</h2></td>
                            </tr>
                            <tr>
                                <td colspan="2">This plugin enables easy Infolinks integration in your website.</td>
                            </tr>
                            <tr>
                                <td colspan="2" height="30">&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="top" width="200">Infolinks InText ads:</td>
                                <td>
                                    <input type="radio"
                                           name="infolinks_status"
                                           value="1" <?php checked($infolinks_status, 1); ?> /> On <br/>
                                    <input type="radio"
                                           name="infolinks_status"
                                           value="0" <?php checked($infolinks_status, 0); ?> /> Off
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" height="20">&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="top"><label for="publisherid">Publisher ID:</label></td>
                                <td>
                                    <input type="text"
                                           name="infolinks_publisherid"
                                           id="infolinks_publisherid"
                                           value="<?php echo esc_attr($infolinks_publisherid); ?>"/><br/>
                                    <span class="small_txt">Please enter your Infolinks PID</span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" height="40">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <label for="excludepage">Do NOT integrate on the following pages:</label>
                                    <input type="text"
                                           name="infolinks_excludepage"
                                           id="infolinks_excludepage"
                                           value="<?php echo esc_attr($infolinks_excludepage); ?>"/><br/>
                                    <span class="small_txt"> (comma-separated page numbers)</span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" height="20">&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="top">jQuery:</td>
                                <td><input type="radio"
                                           name="infolinks_jquery"
                                           value="1" <?php checked($infolinks_jquery, 1); ?> /> Use plugin jQuery <br/>
                                    <input type="radio"
                                           name="infolinks_jquery"
                                           value="0" <?php checked($infolinks_jquery, 0); ?> /> Use site jQuery
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" height="20">&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="top">Show in header / titles: <br/>
                                    <span class="small_txt">(requires jQuery)</span></td>
                                <td>
                                    <input type="radio"
                                           name="infolinks_keytag"
                                           value="1" <?php checked($infolinks_keytag, 1); ?> />On <br/>
                                    <input type="radio"
                                           name="infolinks_keytag"
                                           value="0" <?php checked($infolinks_keytag, 0); ?> />Off
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" height="20">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div style="padding-top:3px;float:left;">
                                        <input type="checkbox"
                                               name="infolinks_post"
                                               value="1" <?php checked($infolinks_post, 1); ?> />
                                        &nbsp;</div>
                                    <div style="padding-top:3px;float:left;">Enable Infolinks on posts</div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" height="40">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <strong>Want to earn even more?</strong>
                                    <p><a href="https://publishers.infolinks.com/members/customize-intag"
                                          target="_blank">Click here to activate InTag!</a></p>
                                    <p>InTag will increase your earnings with our attractive, fully-customizable keywords cloud displayed at the bottom of your article.</p>
                                    <p>InTag operates just like our InText ads, a mouse hover reveals our ad bubble, and each click generates revenue! Our smart algorithm delivers the best keywords for each page and helps to further turn your content into money by matching each keyword with a relevant ad.</p>
                                </td>
                            <tr>
                                <td colspan="2" height="40">&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2">To view or edit your Infolinks account settings, please visit the
                        <a href="https://publishers.infolinks.com/members/1-minute-integration"
                           target="_blank">Integration guide</a> <br/>
                        and <a href="https://www.infolinks.com/faq.html" target="_blank">our FAQs</a>, or contact us at
                        <a href="mailto:support@infolinks.com">support@infolinks.com</a></td>
                </tr>
                <tr>
                    <td colspan="2" height="20">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2" height="40">
                        <input type="submit" class="button-primary" name="btnSave" value="Save Settings"/>
                        <input type="submit" class="button-secondary" name="btnReset" value="Reset to Default"/>
                    </td>
                </tr>
            </table>
        </div>
    </form>
    <?php
}

//add the script in footer
function integrate_infolinks_script()
{

    // flag of whether to actually integrate or not
    // it will be updated to yes or no (1/0) based on filters set by the user
    $display = 0;

    // to check the ads enable or disable
    $enabled = get_option('infolinks_status');
    $showEverywhere = get_option('infolinks_keytag');
    $JQuery = get_option('infolinks_jquery');
    $cJoin = '';
    $idJoin = '';
    $keyJoin = '';
    $strOnOff = '';

    if ($enabled == 1) {
        $display = 1;
        $postings = get_option('infolinks_post');
        $excludedPageIdsString = get_option('infolinks_excludepage');

        // to check the following pages
        if (!empty($excludedPageIdsString)) {
            $excludedPageIds = explode(",", $excludedPageIdsString);
            global $post;
            $currentPageId = $post->ID;
            foreach ($excludedPageIds as $excludedPageId) {
                if ($currentPageId == intval($excludedPageId)) {
                    $display = 0;
                }
            }
        }

        // disable if this page is a post, and integration in posts is disabled
        if (is_single() || is_front_page()) {
            $display = 0;
            if ($postings == 1) {
                $display = 1;
            }
        }

        // if user does not want to hook keywords in header/title, add filters
        if ($showEverywhere == 0) {

            $classArray = array(".description");
            $IdArray = array("#headerimg");
            $KeyArray = array("h1", "h2", "h3");

            // show OFF comment before elements with excluded CSS class, and ON comment right after them
            for ($c = 0; $c < count($classArray); $c++) {
                $cJoin .= "jQuery('" . $classArray[$c] . "').before('<span><!--INFOLINKS_OFF--></span>');";
                $cJoin .= "jQuery('" . $classArray[$c] . "').after('<span><!--INFOLINKS_ON--></span>');";
            }

            // show OFF comment before elements with excluded IDs, and ON comment right after them
            for ($i = 0; $i < count($IdArray); $i++) {
                $idJoin .= "jQuery('" . $IdArray[$i] . "').before('<span><!--INFOLINKS_OFF--></span>');";
                $idJoin .= "jQuery('" . $IdArray[$i] . "').after('<span><!--INFOLINKS_ON--></span>');";
            }

            // show OFF comment before elements with excluded tag names, and ON comment right after them
            for ($k = 0; $i < count($KeyArray); $i++) {
                $keyJoin .= "jQuery('" . $KeyArray[$k] . "').before('<span><!--INFOLINKS_OFF--></span>');";
                $keyJoin .= "jQuery('" . $KeyArray[$k] . "').after('<span><!--INFOLINKS_ON--></span>');";
            }

            // add built-in jQuery to the site if instructed to
            if ($JQuery == 1) {
                $strOnOff = '<script type="text/javascript" src="' . esc_url(JS_PATH) . '"></script>';
            }

            // build the full <script> tag to exclude elements from being highlighted
            $strOnOff .= '<script type="text/javascript">jQuery(document).ready(function(){' . $cJoin . $idJoin . $keyJoin . '});</script>';
        }
    }

    // now, if integrate should happen
    if ($display == 1) {

        $infoscript = '
<!-- Infolinks START -->
' . $strOnOff . '
<script type="text/javascript">
    var infolinks_pid = ' . get_option('infolinks_publisherid') . '; 
    var infolinks_plugin_version = "' . get_option('infolinks_plugin_version') . '"; 
    var infolinks_resources = "//resources.infolinks.com/js"; 
</script>
<script type="text/javascript" src="//resources.infolinks.com/js/infolinks_main.js" ></script>
<!-- Infolinks END -->';

        echo $infoscript;
    }
}

?>