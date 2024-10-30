<?php

namespace LaiserTag;

class LTInsight
{
    private $api_endpoint = "https://laisertag.com/";
    private $api_key = "";
    private $range = "";

    function __construct()
    {
        add_action('admin_init', array($this, 'checkLTActive'));
        // Don't run anything else in the plugin, if we're on an incompatible WordPress version
        if (!self::checkLTActive()) {
            return;
        }

        add_action('admin_menu', array($this, 'setupMenus'), 11);
        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_style('lti-styles', LTI_ASSETS_URL . 'css/lti-style.css');
            wp_enqueue_script('flot', LTI_ASSETS_URL . 'flot/jquery.flot.js');
            wp_enqueue_script('flot-stack', LTI_ASSETS_URL . 'flot/jquery.flot.stack.js', array('flot'));
            wp_enqueue_script('flot-time', LTI_ASSETS_URL . 'flot/jquery.flot.time.js', array('flot'));
            wp_enqueue_script('flot-tooltip', LTI_ASSETS_URL . 'flot/jquery.flot.tooltip.js', array('flot'));
        });

        $this->api_key = get_option('lti_ltbb_key');
        if (isset($_POST['range']) && in_array($_POST['range'], array('7days', '2weeks', '30days'))) {
            $this->range = $_POST['range'];
        }

        //add the api endpoint functions
        add_action('wp_ajax_lti_ltbb_get_tags_by_views', array($this, 'getTagsByViews'));
        add_action('wp_ajax_lti_ltbb_get_tags_by_clicks', array($this, 'getTagsByClicks'));
        add_action('wp_ajax_lti_ltbb_get_aggregate_metrics', array($this, 'getAggregateMetrics'));
        add_action('wp_ajax_lti_ltbb_get_pages_at_position', array($this, 'getPagesAtPosition'));
        add_action('wp_ajax_lti_ltbb_get_tag_pages_at_position', array($this, 'getTagPagesAtPosition'));
        add_action('wp_ajax_lti_ltbb_get_tag_view_percentage', array($this, 'getTagViewPercentage'));
        add_action('wp_ajax_lti_ltbb_get_tag_click_percentage', array($this, 'getTagClickPercentage'));
        add_action('wp_ajax_lti_ltbb_get_article_metrics', array($this, 'getArticleMetrics'));

        if (is_null($this->api_key) || empty($this->api_key)) {
            try {
                $this->api_key = bin2hex(random_bytes(16));
                update_option('lti_ltbb_key', $this->api_key);
            } catch (\Exception $e) {
                // fall back to uniqid() in case this is an older version of PHP
                // upgrade your damn PHP to minimum 7.0, people!
                $this->api_key = uniqid(phpversion() . ".", true);
                update_option('lti_ltbb_key', $this->api_key);
            }
        }

        add_action( 'lti_post_analysis', array($this, 'postUpdate') );
    }

    // The primary sanity check, automatically disable the plugin on activation if it doesn't
    // meet minimum requirements.
    static function activationCheck()
    {
        if (!self::checkLTActive()) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('Primary Laiser Tag plugin not detected. Please check to make sure that Laiser Tag is installed and active before trying to use Laiser Tag Insights.', 'ltinsights'));
        }
        wp_schedule_event(time(), '1min', 'lti_post_analysis');
    }

    static function deactivation() {
        wp_clear_scheduled_hook('lti_post_analysis');
    }

    public function setupMenus()
    {
        add_submenu_page(
            'laiser-tag',
            'Laiser Tag Insights',
            'LT Insights',
            'manage_options',
            'laiser-tag-insights',
            array($this, 'setupGraphPage')
        );
        return true;
    }

    public function setupGraphPage()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $lti_settings_updated = false;
        if (isset($_POST['lti_ltbb_key']) && $_POST['lti_ltbb_key'] !== '') {
            update_option('lti_ltbb_key', $_POST['lti_ltbb_key']);
            $lti_settings_updated = true;
        }
        if(isset($_GET['email_notify'])) {
            $this->setEmailNotifications($_GET['email_notify']);
        }

        $lti_ltbb_key = get_option('lti_ltbb_key');
        $lti_ltbb_issues = $this->checkIssues($lti_ltbb_key);
        $lti_ltbb_status = "Waiting";
        $lti_ltbb_in_active_site_list = false;
        $lti_ltbb_valid_gw = false;
        $lti_ltbb_email = "";
        $lti_ltbb_email_notifications = false;
        if($lti_ltbb_issues) {
            if ($lti_ltbb_issues['in_allowed_site_list'] == 1) {
                $lti_ltbb_in_active_site_list = true;
            }
            if ($lti_ltbb_issues['get_emails'] == 1) {
                $lti_ltbb_email_notifications = true;
            }
            if ($lti_ltbb_issues['working'] == 1) {
                $lti_ltbb_valid_gw = true;
            }
            $lti_ltbb_email = $lti_ltbb_issues['email'];
            $lti_ltbb_status = $lti_ltbb_issues['status'];
        }
        $current_user = wp_get_current_user();
        $oauthparams = array(
            'site_key' => $lti_ltbb_key,
            'base_url' => get_site_url(),
            'email' => $current_user->user_email,
            'first_name' => $current_user->user_firstname,
            'last_name' => $current_user->user_lastname
        );
        $lti_ltbb_api_oauth = $this->api_endpoint . 'wpoauth?' . http_build_query($oauthparams);
        include(LTI_TEMPLATES . 'graphs.php');
        return true;
    }

    public function getArticleMetrics()
    {
        $return = wp_remote_post($this->api_endpoint . "get-article-metrics", array('body' => array('site_key' => $this->api_key, 'range' => $this->range)));
        $response = $return['body'];
        header('Content-Type: application/json');
        echo $response;
        wp_die();
    }

    public function getTagsByViews()
    {
        $return = wp_remote_post($this->api_endpoint . "get-tags-by-views", array('body' => array('site_key' => $this->api_key, 'range' => $this->range)));
        $response = $return['body'];
        header('Content-Type: application/json');
        echo $response;
        wp_die();
    }

    public function getTagsByClicks()
    {
        $return = wp_remote_post($this->api_endpoint . "get-tags-by-clicks", array('body' => array('site_key' => $this->api_key, 'range' => $this->range)));
        $response = $return['body'];
        header('Content-Type: application/json');
        echo $response;
        wp_die();
    }

    public function getAggregateMetrics()
    {
        $return = wp_remote_post($this->api_endpoint . "get-aggregate-metrics", array('body' => array('site_key' => $this->api_key, 'range' => $this->range)));
        $response = $return['body'];
        header('Content-Type: application/json');
        echo $response;
        wp_die();
    }

    public function getPagesAtPosition()
    {
        $return = wp_remote_post($this->api_endpoint . "get-pages-at-position", array('body' => array('site_key' => $this->api_key, 'range' => $this->range)));
        $response = $return['body'];
        header('Content-Type: application/json');
        echo $response;
        wp_die();
    }

    public function getTagPagesAtPosition()
    {
        $return = wp_remote_post($this->api_endpoint . "get-tag-pages-at-position", array('body' => array('site_key' => $this->api_key, 'range' => $this->range)));
        $response = $return['body'];
        header('Content-Type: application/json');
        echo $response;
        wp_die();
    }

    public function getTagViewPercentage()
    {
        $return = wp_remote_post($this->api_endpoint . "get-tag-view-percentage", array('body' => array('site_key' => $this->api_key, 'range' => $this->range)));
        $response = $return['body'];
        header('Content-Type: application/json');
        echo $response;
        wp_die();
    }

    public function getTagClickPercentage()
    {
        $return = wp_remote_post($this->api_endpoint . "get-tag-click-percentage", array('body' => array('site_key' => $this->api_key, 'range' => $this->range)));
        $response = $return['body'];
        header('Content-Type: application/json');
        echo $response;
        wp_die();
    }

    public function postUpdate()
    {
        global $wpdb;
        $response = wp_remote_post($this->api_endpoint . "post-info-required", array('body' => array('site_key' => $this->api_key)));
        if(is_a($response, 'WP_Error')) {
            error_log($response->get_error_message());
            return;
        }
        $data = json_decode($response['body'], true);
        if(!isset($data['data']['urls'])) {
            // no urls to process, return
            return;
        }
        $url_list = $data['data']['urls'];
        $return_array = array();
        foreach ($url_list as $u) {
            $post = $wpdb->get_row( $wpdb->prepare( "SELECT ID, post_title, post_date, post_content FROM $wpdb->posts WHERE guid=%s", $u ) );
            if($post) {
                $post->tags_input = array();
                if(isset($post->ID)) {
                    $tags = wp_get_post_tags($post->ID);
                    foreach ($tags as $t) {
                        $post->tags_input[] = $t->name;
                    }
                }
                $post->guid = $u;
                $return_array[$u] = $post;
            }
            else {
                $post = array('guid' => $u);
                $return_array[$u] = $post;
            }
        }
        $response = wp_remote_post($this->api_endpoint . "post-analysis", array('body' => array('site_key' => $this->api_key, 'posts' => $return_array)));
    }

    // The backup sanity check, in case the plugin is activated in a weird way,
    // or the versions change after activation.
    private function check_version()
    {
        if (!self::checkLTActive()) {
            if (is_plugin_active(plugin_basename(__FILE__))) {
                deactivate_plugins(plugin_basename(__FILE__));
                add_action('admin_notices', array($this, 'disabledNotice'));
                if (isset($_GET['activate'])) {
                    unset($_GET['activate']);
                }
            }
        }
    }

    private function setEmailNotifications($param) {
        wp_remote_post($this->api_endpoint . "set-email-notifications", array('body' => array('site_key' => $this->api_key, 'set' => $param)));
    }

    private function disabledNotice()
    {
        echo '<strong>' . esc_html__('Primary Laiser Tag plugin not detected. Please check to make sure that Laiser Tag is installed and active before trying to use Laiser Tag Insights.', 'my-plugin') . '</strong>';
    }

    private function checkIssues($key)
    {
        $response = wp_remote_post($this->api_endpoint . "get-site-issues", array('body' => array('site_key' => $key)));
        $body = json_decode($response['body'], true);
        if(isset($body['data']['data'])) {
            return $body['data']['data'];
        }
        return false;
    }

    private function successfulConnectionNotice()
    {
        echo '<strong>' . esc_html__('Connection successful! LT Insights is now connected and will begin gathering metrics from Google Webmaster.', 'my-plugin') . '</strong>';
    }

    static function checkLTActive()
    {
        if (!function_exists('is_plugin_active')) {
            require_once(ABSPATH . '/wp-admin/includes/plugin.php');
        }
        return is_plugin_active('laiser-tag/laisertag.php');
    }
}