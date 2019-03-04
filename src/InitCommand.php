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

class InitCommand extends Command {

    private $configs;
    private $fapi;
    private $db;

    public function __construct(Array $configs, FAPI $fapi, DB $db) {
        parent::__construct('init', [$this, 'handle']);

        $this->configs = $configs;
        $this->fapi = $fapi;
        $this->db = $db;
    }

    public function handle(GetOpt $getOpt) {
        echo "Creating Database Tables" . PHP_EOL;
        $this->db->init_missing_tables();
        $this->db->enable_sqlite_foreign_key_constraints();
        echo "Adding Script To Crontab" . PHP_EOL;
        $this->init_cron_tab();
    }

    private function init_cron_tab() {
        $script_executable = __DIR__ . "/feegra.php process";
        exec("(crontab -l 2> '/dev/null' ; echo '* * * * * $script_executable') | awk '!x[$0]++' | crontab -");
    }
}
