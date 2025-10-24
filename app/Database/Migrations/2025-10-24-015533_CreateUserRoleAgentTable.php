<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-24
 * Github: github.com/mikhaelfelian
 * Description: Migration to create user_role_agent table for managing user roles within agent organizations
 * This file represents the Migration for CreateUserRoleAgentTable.
 */
class CreateUserRoleAgentTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'agent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['1', '2'],
                'null'       => false,
                'default'    => '2',
                'comment'    => '1=owner, 2=staff',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('agent_id');
        $this->forge->addKey('role');
        $this->forge->addKey(['user_id', 'agent_id'], false, false, 'unique_user_agent');
        
        // Foreign key constraints with CASCADE
        $this->forge->addForeignKey('user_id', 'user', 'id_user', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('agent_id', 'agent', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('user_role_agent', false, [
            'COMMENT' => 'Junction table for user roles within agent organizations - FK user.id=agent.id'
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('user_role_agent');
    }
}