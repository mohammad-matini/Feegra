<?php namespace Feegra;

class DB extends \SQLite3 {
    function __construct($configs) {
        $this->open($configs['db_path']);
    }

    function init_database() {

    }

    function queue_page() {

    }

    function get_next_queue_page() {

    }

    function list_queue_pages() {

    }

    function update_queue_page_status() {

    }
}
