<?php namespace Feegra;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;

class ProcessCommand extends Command {

    private $configs;
    private $fapi;
    private $db;

    public function __construct(Array $configs, FAPI $fapi, DB $db) {
        parent::__construct('process', [$this, 'handle']);

        $this->configs = $configs;
        $this->fapi = $fapi;
        $this->db = $db;
    }

    public function handle(GetOpt $getOpt) {

        $page_id = $this->db->get_next_queue_page()['PAGE_ID'];
        $access_token = $this->configs['user_access_token'];

        if (!$page_id)
            exit("No unfinished pages to process.\n");

        $response = $this->fapi
                  ->get_page_feed($page_id, $access_token, 10, null)
                  ->getDecodedBody();
        $next_page_cursor = array_key_exists('next', $response['paging']) ?
                          $response['paging']['cursors']['after'] :
                          null;
        $posts = $response['data'];
        $this->db->save_posts($page_id, $posts);
        print($next_page_cursor);
        print_r($posts);
    }

    private function check_valid_facebook_page(String $page_id) {
        try {
            $this->fapi->get(
                "/$page_id", "{$this->configs['user_access_token']}"
            );
        } catch (\Throwable $e) {
            echo 'Facebook error:' . $e->getMessage();
            exit;
        }
    }
}
