<?php namespace Feegra;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;

class ListCommand extends Command {

    private $configs;
    private $fapi;
    private $db;

    public function __construct(Array $configs, FAPI $fapi, DB $db) {
        parent::__construct('list', [$this, 'handle']);

        $this->configs = $configs;
        $this->fapi = $fapi;
        $this->db = $db;

        $this->addOperands([
            Operand::create('page_id', Operand::OPTIONAL)
        ]);

    }

    public function handle(GetOpt $getOpt) {
        $this->db->validate_database_tables();
        $page_id = $getOpt->getOperand('page_id');
        if ($page_id) {
            print_r($this->db->get_stored_page_posts($page_id));
        } else {
            print_r($this->db->get_queue_pages());
        }
    }
}
