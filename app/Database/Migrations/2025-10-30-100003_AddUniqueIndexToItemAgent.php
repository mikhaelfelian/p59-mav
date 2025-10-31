<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueIndexToItemAgent extends Migration
{
    public function up()
    {
        // Add unique index on (user_id, item_id) to prevent duplicate agent-item prices
        $this->db->query('CREATE UNIQUE INDEX IF NOT EXISTS uq_item_agent_user_item ON item_agent (user_id, item_id)');
    }

    public function down()
    {
        // Drop the unique index if exists (MySQL syntax compatible)
        try {
            $this->db->query('DROP INDEX uq_item_agent_user_item ON item_agent');
        } catch (\Throwable $e) {
            // ignore
        }
    }
}


