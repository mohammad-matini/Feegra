<?php namespace Feegra;

class FAPI extends \Facebook\Facebook {

    private $fb;

    function __construct($configs) {
        parent::__construct([
            'default_graph_version' => 'v3.2',
            'app_id' => $configs['app_id'],
            'app_secret' => $configs['app_secret'],
            'default_access_token' => $configs['user_access_token']
        ]);
    }

    public function get_page_feed($page_id, $access_token, $limit, $next_page_cursor) {
        try {
            $response = $this->get(
                "/$page_id/feed?limit=$limit&after=$next_page_cursor",
                "$access_token"
            );
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        return $response;
    }
}
