<?php namespace Caleano\Freifunk\MeetupRegister;

use WP;
use WP_Post;
use WP_Query;

defined('ABSPATH') or die('NOPE');

/**
 * Class WordpressPage
 */
class WordpressRouting
{
    public static $routes = [];

    public function __construct()
    {
        add_filter('the_posts', [$this, 'filterPosts']);
    }

    /**
     * On any request
     *
     * @param string   $url
     * @param callable $callback
     */
    public static function all($url, callable $callback)
    {
        self::$routes['all'][] = [
            'url'      => $url,
            'callback' => $callback,
        ];
    }

    /**
     * On GET-request
     *
     * @param string   $url
     * @param callable $callback
     */
    public static function get($url, callable $callback)
    {
        self::$routes['GET'][] = [
            'url'      => $url,
            'callback' => $callback,
        ];
    }

    /**
     * On POST-request
     *
     * @param string   $url
     * @param callable $callback
     */
    public static function post($url, callable $callback)
    {
        self::$routes['POST'][] = [
            'url'      => $url,
            'callback' => $callback,
        ];
    }

    /**
     * On PUT-request
     *
     * @param string   $url
     * @param callable $callback
     */
    public static function put($url, callable $callback)
    {
        self::$routes['PUT'][] = [
            'url'      => $url,
            'callback' => $callback,
        ];
    }

    /**
     * On HEAD-request
     *
     * @param string   $url
     * @param callable $callback
     */
    public static function head($url, callable $callback)
    {
        self::$routes['HEAD'][] = [
            'url'      => $url,
            'callback' => $callback,
        ];
    }

    /**
     * Apply custom routing if site is 404
     *
     * @param array $posts
     * @return array
     */
    public function filterPosts(array $posts)
    {
        /**  @var WP_Query $wp_query */
        global $wp_query;

        if (count($posts) != 0 && $wp_query->query_vars['error'] != 404) {
            return $posts;
        }

        $requestType = $_SERVER['REQUEST_METHOD'];

        if ($return = $this->handleRoutes($requestType)) {
            return $return;
        }

        if ($return = $this->handleRoutes('all')) {
            return $return;
        }

        return $posts;
    }

    protected function handleRoutes($type)
    {
        /**
         * @var WP       $wp
         * @var WP_Query $wp_query
         */
        global $wp, $wp_query;

        if (empty(self::$routes[$type])) {
            return null;
        }

        foreach (self::$routes[$type] as $route) {
            if (
                $wp->request == $route['url']
                || $wp->query_vars['page_id'] == $route['url']
            ) {
                $emptyPage = $this->getEmptyPage($wp->request);
                $page = $route['callback']($emptyPage);
                if (!$page) {
                    continue;
                }

                $this->setSiteFound($wp_query);
                return [$page];
            }
        }

        return null;
    }

    /**
     * Inform wordpress that the requested site isn't a 404 page
     *
     * @param WP_Query $query
     */
    protected function setSiteFound(WP_Query $query)
    {
        $query->is_page = true;
        $query->is_singular = true;
        $query->is_home = false;
        $query->is_archive = false;
        $query->is_category = false;
        unset($query->query['error']);
        $query->query_vars['error'] = '';
        $query->is_404 = false;
    }

    /**
     * Create a new empty page
     *
     * @param string $slug
     * @return WP_Post
     */
    protected function getEmptyPage($slug)
    {
        $url = get_home_url('/' . $slug);
        $currentTime = current_time('mysql');
        $currentTimeGmt = current_time('mysql', true);

        $post = (object)[
            'ID'                    => -1,
            'post_author'           => 1,
            'post_date'             => $currentTime,
            'post_date_gmt'         => $currentTimeGmt,
            'post_content'          => '',
            'post_title'            => '',
            'post_excerpt'          => '',
            'post_status'           => 'publish',
            'comment_status'        => 'closed',
            'ping_status'           => 'closed',
            'post_password'         => '',
            'post_name'             => $slug,
            'to_ping'               => '',
            'pinged'                => '',
            'modified'              => $currentTime,
            'modified_gmt'          => $currentTimeGmt,
            'post_content_filtered' => '',
            'post_parent'           => 0,
            'guid'                  => $url,
            'menu_order'            => 0,
            'post_style'            => 'page',
            'post_mime_type'        => '',
            'comment_count'         => 0,
            'filter'                => 'raw',
        ];

        $post = new WP_Post($post);

        return $post;
    }
}
