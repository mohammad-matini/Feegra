<?php namespace Feegra;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;

class AddCommand extends Command {

    private $configs;

    public function __construct($configs) {
        parent::__construct('add', [$this, 'handle']);

        $this->configs = $configs;

        $this->addOperands([
            Operand::create('page_id', Operand::MULTIPLE+Operand::REQUIRED)
            ->setValidation('is_string')
        ]);

    }

    public function handle(GetOpt $getOpt) {
        print_r($getOpt->getOptions());
    }

    private function check_valid_page() {

    }
}
