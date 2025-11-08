<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-08
 * Github: github.com/mikhaelfelian
 * Description: Migration to add status_agent and status_pos columns to platform table
 * status_agent: Active for agent/post integration
 * status_pos: Active for POS integration
 */
class AddStatusAgentAndStatusPosToPlatform extends Migration
{
    public function up()
    {
        $fields = [
            'status_agent' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'after'      => 'status',
                'comment'    => 'Status aktif untuk agent/post: 1=active, 0=inactive'
            ],
            'status_pos' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'after'      => 'status_agent',
                'comment'    => 'Status aktif untuk POS: 1=active, 0=inactive'
            ]
        ];

        $this->forge->addColumn('platform', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('platform', ['status_agent', 'status_pos']);
    }
}

