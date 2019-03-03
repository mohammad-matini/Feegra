<?php namespace Feegra;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;

class AddCommand extends Command {

    private $configs;
    private $fapi;
    private $db;

    public function __construct(Array $configs, FAPI $fapi, DB $db) {
        parent::__construct('add', [$this, 'handle']);

        $this->configs = $configs;
        $this->fapi = $fapi;
        $this->db = $db;

        $this->addOperands([
            Operand::create('page_id', Operand::REQUIRED)
        ]);

    }

    public function handle(GetOpt $getOpt) {
        $page_id = $getOpt->getOperand('page_id');
        $this->check_valid_facebook_page($page_id);
        $this->db->add_page_to_queue($page_id);
    }

    private function check_valid_facebook_page(String $page_id) {
        try {
            $this->fapi->get(
                "/$page_id", "{$this->configs['user_access_token']}"
            );
        } catch (\Throwable $e) {
            echo 'Facebook error:' . $e->getMessage() . PHP_EOL;
            exit;
        }
    }
}
