<?php namespace Feegra;


// Copyright (C) 2019 Mohammad Matini

// Author: Mohammad Matini <mohammad.matini@outlook.com>
// Maintainer: Mohammad Matini <mohammad.matini@outlook.com>

// This file is part of Feegra.

// Feegra is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// Feegra is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with Feegra.  If not, see <https://www.gnu.org/licenses/>.


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
        $this->db->validate_database_tables();
        $queue_page = $this->db->get_next_queue_page();

        $log = "[" . date("c") . "] :: ";

        if (!$queue_page) {
            $log = $log . "Empty Queue." . PHP_EOL;
            file_put_contents($this->configs['log_path'], $log, FILE_APPEND);
            exit("No unfinished pages to process.\n");
        }

        $page_id = $queue_page['PAGE_ID'];
        $next_page_cursor = $queue_page['NEXT_PAGE_CURSOR'];
        $access_token = $this->configs['user_access_token'];

        $log = $log . "Get Page Feed, PAGE_ID:" . $page_id . PHP_EOL;
        file_put_contents($this->configs['log_path'], $log, FILE_APPEND);

        $response = $this->fapi
                  ->get_page_feed($page_id, $access_token,
                                  $this->configs['pagination_size'],
                                  $next_page_cursor)
                  ->getDecodedBody();
        $new_next_page_cursor = array_key_exists('next', $response['paging']) ?
                          $response['paging']['cursors']['after'] :
                          null;
        $posts = $response['data'];
        if ($posts) {
            $this->db->save_posts($page_id, $posts);
            $this->db->update_queue_page_status($page_id, $new_next_page_cursor);
        } else {
            // TODO: investigate what happens if page has no data. Should
            // probably update page status to finished.
            exit("Unknown Error: No Page Data?");
        }
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
