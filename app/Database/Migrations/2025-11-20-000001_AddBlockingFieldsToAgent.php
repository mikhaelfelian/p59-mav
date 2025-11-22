<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-20
 * Github: github.com/mikhaelfelian
 * Description: Migration for adding blocking fields to agent table for receivables monitoring
 */
class AddBlockingFieldsToAgent extends Migration
{
    public function up()
    {
        // Check if columns already exist
        $fields = $this->db->getFieldNames('agent');
        
        if (!in_array('is_blocked', $fields)) {
            $this->forge->addColumn('agent', [
                'is_blocked' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'null'       => false,
                    'default'    => 0,
                    'comment'    => 'Flag to block new orders (1=blocked, 0=not blocked)',
                    'after'      => 'is_active',
                ],
            ]);
        }

        if (!in_array('blocked_reason', $fields)) {
            $this->forge->addColumn('agent', [
                'blocked_reason' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => null,
                    'comment'    => 'Reason for blocking agent orders',
                    'after'      => 'is_blocked',
                ],
            ]);
        }

        if (!in_array('blocked_at', $fields)) {
            $this->forge->addColumn('agent', [
                'blocked_at' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                    'comment' => 'Timestamp when agent was blocked',
                    'after'   => 'blocked_reason',
                ],
            ]);
        }

        if (!in_array('last_reminder_sent', $fields)) {
            $this->forge->addColumn('agent', [
                'last_reminder_sent' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                    'comment' => 'Timestamp when last payment reminder was sent',
                    'after'   => 'blocked_at',
                ],
            ]);
        }
    }

    public function down()
    {
        $fields = $this->db->getFieldNames('agent');
        
        if (in_array('last_reminder_sent', $fields)) {
            $this->forge->dropColumn('agent', 'last_reminder_sent');
        }

        if (in_array('blocked_at', $fields)) {
            $this->forge->dropColumn('agent', 'blocked_at');
        }

        if (in_array('blocked_reason', $fields)) {
            $this->forge->dropColumn('agent', 'blocked_reason');
        }

        if (in_array('is_blocked', $fields)) {
            $this->forge->dropColumn('agent', 'is_blocked');
        }
    }
}

