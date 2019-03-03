<?php namespace Feegra;

abstract class STATUS {
    const __default = self::IN_PROGRESS;
    const DONE = 0;
    const IN_PROGRESS = 1;
}

class DB extends \SQLite3 {
    function __construct($configs) {
        $this->open($configs['db_path']);
        $this->init_missing_tables();
        $this->enable_sqlite_foreign_key_constraints();
    }

    function __destruct() {
        $this->close();
    }


    private function enable_sqlite_foreign_key_constraints() {
        // SQLite3 requires enabling this at run time to fail on non-existent
        // foreign keys.
        $sql_query = 'PRAGMA foreign_keys = ON;';

        if(!$this->exec($sql_query))
            exit($this->lastErrorMsg());
    }


    private function init_missing_tables() {

        $sql_table_definitions = [

            '
                CREATE TABLE IF NOT EXISTS PAGE
            (
                PAGE_ID              TEXT  PRIMARY KEY  NOT NULL,
                STATUS               INT   DEFAULT 1    NOT NULL,
                NEXT_PAGE_CURSOR     TEXT                       ,

                CHECK (STATUS == 0 OR STATUS ==1)
            );','
                CREATE TABLE IF NOT EXISTS POST
            (
                POST_ID              TEXT               NOT NULL,
                PAGE_ID              TEXT               NOT NULL,
                MESSAGE              TEXT                       ,
                CREATED_TIME         TEXT               NOT NULL,

                FOREIGN KEY (PAGE_ID) REFERENCES PAGE (PAGE_ID)
            );'
        ];

        foreach ($sql_table_definitions as $sql_table_definition) {
            if(!$this->exec($sql_table_definition))
                exit($this->lastErrorMsg());
        }
    }


    public function add_page_to_queue(String $page_id) {
        $status = STATUS::IN_PROGRESS;
        $statement = $this->prepare("INSERT INTO PAGE (PAGE_ID, STATUS)
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
        $statement = $this->prepare("SELECT PAGE_ID FROM PAGE
                                     WHERE STATUS = :status;");

        $statement->bindValue(':status', $status);

        $result = $statement->execute();

        if(!$result)
            exit($this->lastErrorMsg());

        return $result->fetchArray(SQLITE3_ASSOC);
    }


    function list_queue_pages() {

    }

    function update_queue_page_status() {

    }

    function save_posts($page_id, $posts) {
        $statement = $this
                   ->prepare('INSERT INTO POST
                                 (POST_ID, PAGE_ID, MESSAGE, CREATED_TIME)
                          VALUES (:post_id, :page_id, :message, :created_time)');
        foreach($posts as $post) {
            $statement->bindValue(':post_id', $post['id']);
            $statement->bindValue(':page_id', $page_id);
            $statement->bindValue(':message', $post['message']);
            $statement->bindValue(':created_time', $post['created_time']);
            $statement->execute();
        }
    }

    function list_page_posts() {

    }
}
