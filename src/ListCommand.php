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

class ListCommand extends Command {

    private $configs;
    private $fapi;
    private $db;

    public function __construct(Array $configs, FAPI $fapi, DB $db) {
        parent::__construct('list', [$this, 'handle']);

        $this->setDescription('List queued pages, or posts of a page if given a page_id as a paramater.');

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
