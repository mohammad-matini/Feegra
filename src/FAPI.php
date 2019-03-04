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


class FAPI extends \Facebook\Facebook {

    function __construct($configs) {
        parent::__construct([
            'default_graph_version' => 'v3.2',
            'app_id' => $configs['app_id'],
            'app_secret' => $configs['app_secret'],
            'default_access_token' => $configs['user_access_token']
        ]);
    }

    public function get_page_feed($page_id, $access_token,
                                  $limit, $next_page_cursor) {
        try {
            $response = $this->get(
                "/$page_id/feed?limit=$limit&after=$next_page_cursor",
                "$access_token"
            );
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage() . PHP_EOL;
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: '
                . $e->getMessage() . PHP_EOL;
            exit;
        }
        return $response;
    }
}
