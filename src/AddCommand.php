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

class AddCommand extends Command {

    private $configs;
    private $fapi;
    private $db;

    public function __construct(Array $configs, FAPI $fapi, DB $db) {
        parent::__construct('add', [$this, 'handle']);

        $this->setDescription('Add a Facebook page to the scrapping queue. Requires the page_id as a paramanter.');

        $this->configs = $configs;
        $this->fapi = $fapi;
        $this->db = $db;

        $this->addOperands([
            Operand::create('page_id', Operand::REQUIRED)
        ]);

    }

    public function handle(GetOpt $getOpt) {
        $this->db->validate_database_tables();
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
