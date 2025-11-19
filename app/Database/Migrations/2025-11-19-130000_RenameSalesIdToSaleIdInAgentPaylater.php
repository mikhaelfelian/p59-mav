<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-19
 * Github: github.com/mikhaelfelian
 * Description: Migration for renaming sales_id column to sale_id in agent_paylater table
 * and updating the index accordingly.
 */
class RenameSalesIdToSaleIdInAgentPaylater extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('agent_paylater')) {
            log_message('info', 'RenameSalesIdToSaleIdInAgentPaylater: Table agent_paylater does not exist, skipping migration.');
            return;
        }

        try {
            // Check if column sales_id exists
            $columns = $this->db->getFieldData('agent_paylater');
            $salesIdExists = false;
            $saleIdExists = false;
            
            foreach ($columns as $column) {
                if ($column->name === 'sales_id') {
                    $salesIdExists = true;
                }
                if ($column->name === 'sale_id') {
                    $saleIdExists = true;
                }
            }

            // Only proceed if sales_id exists and sale_id doesn't exist
            if ($salesIdExists && !$saleIdExists) {
                // Drop foreign key constraint first (if exists)
                try {
                    $this->db->query("ALTER TABLE `agent_paylater` DROP FOREIGN KEY `fk_agent_paylater_sales_id`");
                } catch (\Exception $e) {
                    log_message('info', 'RenameSalesIdToSaleIdInAgentPaylater: Could not drop fk_agent_paylater_sales_id: ' . $e->getMessage());
                }

                // Drop existing index on sales_id if it exists
                try {
                    $this->db->query("ALTER TABLE `agent_paylater` DROP INDEX `sales_id`");
                } catch (\Exception $e) {
                    // Index might not exist or have different name, try to find and drop it
                    log_message('info', 'RenameSalesIdToSaleIdInAgentPaylater: Could not drop sales_id index: ' . $e->getMessage());
                    
                    // Try to find the index name
                    $dbName = $this->db->database;
                    $indexQuery = $this->db->query("
                        SELECT INDEX_NAME 
                        FROM information_schema.STATISTICS 
                        WHERE TABLE_SCHEMA = ? 
                        AND TABLE_NAME = 'agent_paylater' 
                        AND COLUMN_NAME = 'sales_id'
                    ", [$dbName]);
                    
                    $indexResult = $indexQuery->getRow();
                    if ($indexResult && !empty($indexResult->INDEX_NAME)) {
                        try {
                            $this->db->query("ALTER TABLE `agent_paylater` DROP INDEX `{$indexResult->INDEX_NAME}`");
                        } catch (\Exception $e2) {
                            log_message('info', 'RenameSalesIdToSaleIdInAgentPaylater: Could not drop index by name: ' . $e2->getMessage());
                        }
                    }
                }

                // Rename column and change definition
                $this->db->query("
                    ALTER TABLE `agent_paylater`
                    CHANGE COLUMN `sales_id` `sale_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL 
                    COMMENT 'FK to sales.id (optional)' 
                    AFTER `agent_id`
                ");

                // Add new index on sale_id
                try {
                    $this->db->query("ALTER TABLE `agent_paylater` ADD INDEX `sales_id` (`sale_id`) USING BTREE");
                } catch (\Exception $e) {
                    log_message('error', 'RenameSalesIdToSaleIdInAgentPaylater: Could not add index on sale_id: ' . $e->getMessage());
                }

                // Re-add foreign key constraint with new column name
                if ($this->db->tableExists('sales')) {
                    try {
                        $this->db->query("
                            ALTER TABLE `agent_paylater`
                            ADD CONSTRAINT `fk_agent_paylater_sale_id`
                            FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`)
                            ON DELETE SET NULL ON UPDATE CASCADE
                        ");
                    } catch (\Exception $e) {
                        log_message('error', 'RenameSalesIdToSaleIdInAgentPaylater: Could not add foreign key on sale_id: ' . $e->getMessage());
                    }
                }
            } elseif ($saleIdExists) {
                log_message('info', 'RenameSalesIdToSaleIdInAgentPaylater: Column sale_id already exists, skipping migration.');
            } else {
                log_message('info', 'RenameSalesIdToSaleIdInAgentPaylater: Column sales_id does not exist, skipping migration.');
            }
        } catch (\Exception $e) {
            log_message('error', 'RenameSalesIdToSaleIdInAgentPaylater: Error during migration: ' . $e->getMessage());
            throw $e;
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('agent_paylater')) {
            return;
        }

        try {
            // Check if column sale_id exists
            $columns = $this->db->getFieldData('agent_paylater');
            $saleIdExists = false;
            $salesIdExists = false;
            
            foreach ($columns as $column) {
                if ($column->name === 'sale_id') {
                    $saleIdExists = true;
                }
                if ($column->name === 'sales_id') {
                    $salesIdExists = true;
                }
            }

            // Only proceed if sale_id exists and sales_id doesn't exist
            if ($saleIdExists && !$salesIdExists) {
                // Drop foreign key constraint first (if exists)
                try {
                    $this->db->query("ALTER TABLE `agent_paylater` DROP FOREIGN KEY `fk_agent_paylater_sale_id`");
                } catch (\Exception $e) {
                    log_message('info', 'RenameSalesIdToSaleIdInAgentPaylater down: Could not drop fk_agent_paylater_sale_id: ' . $e->getMessage());
                }

                // Drop index on sale_id
                try {
                    $this->db->query("ALTER TABLE `agent_paylater` DROP INDEX `sales_id`");
                } catch (\Exception $e) {
                    // Try to find the index name
                    $dbName = $this->db->database;
                    $indexQuery = $this->db->query("
                        SELECT INDEX_NAME 
                        FROM information_schema.STATISTICS 
                        WHERE TABLE_SCHEMA = ? 
                        AND TABLE_NAME = 'agent_paylater' 
                        AND COLUMN_NAME = 'sale_id'
                    ", [$dbName]);
                    
                    $indexResult = $indexQuery->getRow();
                    if ($indexResult && !empty($indexResult->INDEX_NAME)) {
                        try {
                            $this->db->query("ALTER TABLE `agent_paylater` DROP INDEX `{$indexResult->INDEX_NAME}`");
                        } catch (\Exception $e2) {
                            log_message('info', 'RenameSalesIdToSaleIdInAgentPaylater down: Could not drop index: ' . $e2->getMessage());
                        }
                    }
                }

                // Rename column back to sales_id
                $this->db->query("
                    ALTER TABLE `agent_paylater`
                    CHANGE COLUMN `sale_id` `sales_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL 
                    COMMENT 'FK to sales.id (optional)' 
                    AFTER `agent_id`
                ");

                // Re-add index on sales_id
                try {
                    $this->db->query("ALTER TABLE `agent_paylater` ADD INDEX `sales_id` (`sales_id`) USING BTREE");
                } catch (\Exception $e) {
                    log_message('error', 'RenameSalesIdToSaleIdInAgentPaylater down: Could not add index on sales_id: ' . $e->getMessage());
                }

                // Re-add foreign key constraint with old column name
                if ($this->db->tableExists('sales')) {
                    try {
                        $this->db->query("
                            ALTER TABLE `agent_paylater`
                            ADD CONSTRAINT `fk_agent_paylater_sales_id`
                            FOREIGN KEY (`sales_id`) REFERENCES `sales` (`id`)
                            ON DELETE SET NULL ON UPDATE CASCADE
                        ");
                    } catch (\Exception $e) {
                        log_message('error', 'RenameSalesIdToSaleIdInAgentPaylater down: Could not add foreign key on sales_id: ' . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'RenameSalesIdToSaleIdInAgentPaylater down: Error during rollback: ' . $e->getMessage());
            throw $e;
        }
    }
}

