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


abstract class STATUS {
    const __default = self::IN_PROGRESS;
    const DONE = 0;
    const IN_PROGRESS = 1;
}

class DB extends \SQLite3 {
    function __construct($configs) {
        $this->open($configs['db_path']);
    }

    function __destruct() {
        $this->close();
    }


    public function enable_sqlite_foreign_key_constraints() {
        // SQLite3 requires enabling this at run time to fail on non-existent
        // foreign keys.
        $sql_query = 'PRAGMA foreign_keys = ON;';

        if(!$this->exec($sql_query))
            exit($this->lastErrorMsg());
    }


    public function init_missing_tables() {

        $sql_table_definitions = [

            '
                CREATE TABLE IF NOT EXISTS PAGES
            (
                PAGE_ID              TEXT  PRIMARY KEY  NOT NULL,
                STATUS               INT   DEFAULT 1    NOT NULL,
                NEXT_PAGE_CURSOR     TEXT                       ,

                CHECK (STATUS == 0 OR STATUS ==1)
            );','
                CREATE TABLE IF NOT EXISTS POSTS
            (
                POST_ID              TEXT               NOT NULL,
                PAGE_ID              TEXT               NOT NULL,
                MESSAGE              TEXT                       ,
                CREATED_TIME         TEXT               NOT NULL,

                FOREIGN KEY (PAGE_ID) REFERENCES PAGES (PAGE_ID)
            );'
        ];

        foreach ($sql_table_definitions as $sql_table_definition) {
            if(!$this->exec($sql_table_definition))
                exit($this->lastErrorMsg());
        }
    }


    public function add_page_to_queue(String $page_id) {
        $status = STATUS::IN_PROGRESS;
        $statement = $this->prepare("INSERT INTO PAGES (PAGE_ID, STATUS)
                                     VALUES (:page_id, :status);");

        $statement->bindValue(':page_id', $page_id);
        $statement->bindValue(':status', $status);

        if(!$statement->execute()) {
            exit($this->lastErrorMsg());
        } else {
            echo "Page Added To Queue Successfully\n";
        }

    }


    public function get_next_queue_page() {

        $status = STATUS::IN_PROGRESS;
        $result = $this->query("SELECT PAGE_ID, NEXT_PAGE_CURSOR
                                FROM PAGES WHERE STATUS = $status
                                LIMIT 1");

        if(!$result) exit($this->lastErrorMsg());

        return $result->fetchArray(SQLITE3_ASSOC);
    }

    function update_queue_page_status($page_id, $next_page_cursor) {
        $statement = $this
                   ->prepare('UPDATE PAGES
                              SET STATUS=:status,
                              NEXT_PAGE_CURSOR=:next_page_cursor
                              WHERE PAGE_ID = :page_id');
        $statement->bindValue(':status', $next_page_cursor ?
                              STATUS::IN_PROGRESS : STATUS::DONE);
        $statement->bindValue(':next_page_cursor', $next_page_cursor ?
                              $next_page_cursor : null);
        $statement->bindValue(':page_id', $page_id);
        $statement->execute();
    }

    function save_posts($page_id, $posts) {

        $statement = $this
                   ->prepare('INSERT INTO POSTS
                                 (POST_ID, PAGE_ID, MESSAGE, CREATED_TIME)
                          VALUES (:post_id, :page_id, :message, :created_time)');

        foreach($posts as $post) {

            $message = null;
            if (array_key_exists('message', $post)) {
                $message = $post['message'];
            } else if (array_key_exists('story', $post)) {
                $message = $post['story'];
            }

            $statement->bindValue(':post_id', $post['id']);
            $statement->bindValue(':page_id', $page_id);
            $statement->bindValue(':message', $message);
            $statement->bindValue(':created_time', $post['created_time']);
            $statement->execute()->finalize();
        }
    }


    function get_queue_pages() {
        $statement = $this
                   ->prepare('SELECT * FROM PAGES');
        $result = $statement->execute()->fetchArray(SQLITE3_ASSOC);
        return $result;
    }


    function get_stored_page_posts(string $page_id) {
        $statement = $this
                   ->prepare('SELECT POST_ID, MESSAGE, CREATED_TIME FROM PAGES
                              LEFT JOIN POSTS ON POSTS.PAGE_ID = PAGES.PAGE_ID
                              WHERE PAGES.PAGE_ID = :page_id');
        $statement->bindValue(':page_id', $page_id);
        $result = $statement->execute();
        $result_arr = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            array_push($result_arr, $row);
        }
        return $result_arr;
    }

    function validate_database_tables() {
        $result = $this->query("SELECT name FROM sqlite_master
                                WHERE TYPE='table' AND
                                NAME IN ('PAGES', 'POSTS')");
        $result_arr = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            array_push($result_arr, $row);
        }
        if (sizeof($result_arr) < 2) {
            exit("Error: Database Not Ready. Please run `feegra init`\n");
        }
    }
}
