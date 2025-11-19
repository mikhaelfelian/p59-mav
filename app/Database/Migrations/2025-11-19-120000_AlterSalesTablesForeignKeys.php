<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-19
 * Github: github.com/mikhaelfelian
 * Description: Migration for altering sales-related tables to add/update foreign key constraints
 * This migration ensures proper referential integrity for sales tables.
 */
class AlterSalesTablesForeignKeys extends Migration
{
    public function up()
    {
        // 1. Add foreign key constraint to sales_detail table
        if ($this->db->tableExists('sales_detail') && $this->db->tableExists('sales')) {
            try {
                // Check if constraint already exists
                $constraintExists = $this->checkForeignKeyExists('sales_detail', 'FK_sales_detail_sales');
                if (!$constraintExists) {
                    $this->db->query("
                        ALTER TABLE `sales_detail`
                        ADD CONSTRAINT `FK_sales_detail_sales` 
                        FOREIGN KEY (`sale_id`) 
                        REFERENCES `sales` (`id`) 
                        ON UPDATE CASCADE 
                        ON DELETE CASCADE
                    ");
                }
            } catch (\Exception $e) {
                log_message('info', 'AlterSalesTablesForeignKeys: Could not add FK_sales_detail_sales: ' . $e->getMessage());
            }
        }

        // 2. Drop foreign key constraint from sales_fee table
        if ($this->db->tableExists('sales_fee')) {
            try {
                $this->db->query("ALTER TABLE `sales_fee` DROP FOREIGN KEY `fk_sales_fee_fee_type_id`");
            } catch (\Exception $e) {
                // Foreign key might not exist, ignore
                log_message('info', 'AlterSalesTablesForeignKeys: Could not drop fk_sales_fee_fee_type_id: ' . $e->getMessage());
            }
        }

        // 3. Alter sales_gateway_logs table
        if ($this->db->tableExists('sales_gateway_logs') && $this->db->tableExists('sales')) {
            try {
                // Check if sale_id column already exists
                $columns = $this->db->getFieldData('sales_gateway_logs');
                $saleIdExists = false;
                foreach ($columns as $column) {
                    if ($column->name === 'sale_id') {
                        $saleIdExists = true;
                        break;
                    }
                }

                // Add sale_id column if it doesn't exist
                if (!$saleIdExists) {
                    $this->db->query("
                        ALTER TABLE `sales_gateway_logs`
                        ADD COLUMN `sale_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`
                    ");
                }

                // Drop existing platform_id foreign key if it exists
                try {
                    $this->db->query("ALTER TABLE `sales_gateway_logs` DROP FOREIGN KEY `fk_sales_gateway_logs_platform_id`");
                } catch (\Exception $e) {
                    // Foreign key might not exist, ignore
                    log_message('info', 'AlterSalesTablesForeignKeys: Could not drop fk_sales_gateway_logs_platform_id: ' . $e->getMessage());
                }

                // Add new foreign key constraint to sales table
                $constraintExists = $this->checkForeignKeyExists('sales_gateway_logs', 'FK_sales_gateway_logs_sales');
                if (!$constraintExists) {
                    $this->db->query("
                        ALTER TABLE `sales_gateway_logs`
                        ADD CONSTRAINT `FK_sales_gateway_logs_sales` 
                        FOREIGN KEY (`sale_id`) 
                        REFERENCES `sales` (`id`) 
                        ON UPDATE CASCADE 
                        ON DELETE NO ACTION
                    ");
                }
            } catch (\Exception $e) {
                log_message('error', 'AlterSalesTablesForeignKeys: Error altering sales_gateway_logs: ' . $e->getMessage());
            }
        }

        // 4. Alter sales_item_sn table
        if ($this->db->tableExists('sales_item_sn') && $this->db->tableExists('sales')) {
            try {
                // Check if sale_id column already exists
                $columns = $this->db->getFieldData('sales_item_sn');
                $saleIdExists = false;
                foreach ($columns as $column) {
                    if ($column->name === 'sale_id') {
                        $saleIdExists = true;
                        break;
                    }
                }

                // Add sale_id column if it doesn't exist
                if (!$saleIdExists) {
                    $this->db->query("
                        ALTER TABLE `sales_item_sn`
                        ADD COLUMN `sale_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`
                    ");
                }

                // Add foreign key constraint to sales table
                $constraintExists = $this->checkForeignKeyExists('sales_item_sn', 'FK_sales_item_sn_sales');
                if (!$constraintExists) {
                    $this->db->query("
                        ALTER TABLE `sales_item_sn`
                        ADD CONSTRAINT `FK_sales_item_sn_sales` 
                        FOREIGN KEY (`sale_id`) 
                        REFERENCES `sales` (`id`) 
                        ON UPDATE CASCADE 
                        ON DELETE CASCADE
                    ");
                }
            } catch (\Exception $e) {
                log_message('error', 'AlterSalesTablesForeignKeys: Error altering sales_item_sn: ' . $e->getMessage());
            }
        }

        // 5. Add foreign key constraint to sales_payments table
        if ($this->db->tableExists('sales_payments') && $this->db->tableExists('sales')) {
            try {
                // Check if constraint already exists (might have different name)
                $constraintExists = $this->checkForeignKeyExists('sales_payments', 'FK_sales_payments_sales');
                if (!$constraintExists) {
                    // Also check for existing constraint with different name
                    $existingConstraint = $this->checkForeignKeyExists('sales_payments', 'fk_sales_payments_sale_id');
                    if (!$existingConstraint) {
                        $this->db->query("
                            ALTER TABLE `sales_payments`
                            ADD CONSTRAINT `FK_sales_payments_sales` 
                            FOREIGN KEY (`sale_id`) 
                            REFERENCES `sales` (`id`) 
                            ON UPDATE CASCADE 
                            ON DELETE CASCADE
                        ");
                    }
                }
            } catch (\Exception $e) {
                log_message('info', 'AlterSalesTablesForeignKeys: Could not add FK_sales_payments_sales: ' . $e->getMessage());
            }
        }

        // 6. Drop foreign key constraint from sales_payment_log table
        if ($this->db->tableExists('sales_payment_log')) {
            try {
                $this->db->query("ALTER TABLE `sales_payment_log` DROP FOREIGN KEY `fk_sales_payment_log_platform_id`");
            } catch (\Exception $e) {
                // Foreign key might not exist, ignore
                log_message('info', 'AlterSalesTablesForeignKeys: Could not drop fk_sales_payment_log_platform_id: ' . $e->getMessage());
            }
        }
    }

    public function down()
    {
        // Reverse operation 6: Re-add foreign key to sales_payment_log (if platform table exists)
        if ($this->db->tableExists('sales_payment_log') && $this->db->tableExists('platform')) {
            try {
                $constraintExists = $this->checkForeignKeyExists('sales_payment_log', 'fk_sales_payment_log_platform_id');
                if (!$constraintExists) {
                    $this->db->query("
                        ALTER TABLE `sales_payment_log`
                        ADD CONSTRAINT `fk_sales_payment_log_platform_id`
                        FOREIGN KEY (`platform_id`) 
                        REFERENCES `platform` (`id`) 
                        ON DELETE SET NULL 
                        ON UPDATE CASCADE
                    ");
                }
            } catch (\Exception $e) {
                log_message('info', 'AlterSalesTablesForeignKeys down: Could not re-add fk_sales_payment_log_platform_id: ' . $e->getMessage());
            }
        }

        // Reverse operation 5: Drop foreign key from sales_payments
        if ($this->db->tableExists('sales_payments')) {
            try {
                $this->db->query("ALTER TABLE `sales_payments` DROP FOREIGN KEY `FK_sales_payments_sales`");
            } catch (\Exception $e) {
                // Try alternative constraint name
                try {
                    $this->db->query("ALTER TABLE `sales_payments` DROP FOREIGN KEY `fk_sales_payments_sale_id`");
                } catch (\Exception $e2) {
                    log_message('info', 'AlterSalesTablesForeignKeys down: Could not drop FK from sales_payments: ' . $e2->getMessage());
                }
            }
        }

        // Reverse operation 4: Drop foreign key and column from sales_item_sn
        if ($this->db->tableExists('sales_item_sn')) {
            try {
                $this->db->query("ALTER TABLE `sales_item_sn` DROP FOREIGN KEY `FK_sales_item_sn_sales`");
            } catch (\Exception $e) {
                log_message('info', 'AlterSalesTablesForeignKeys down: Could not drop FK_sales_item_sn_sales: ' . $e->getMessage());
            }

            try {
                $this->db->query("ALTER TABLE `sales_item_sn` DROP COLUMN `sale_id`");
            } catch (\Exception $e) {
                log_message('info', 'AlterSalesTablesForeignKeys down: Could not drop sale_id column from sales_item_sn: ' . $e->getMessage());
            }
        }

        // Reverse operation 3: Re-add platform FK and drop sales FK, then drop sale_id column from sales_gateway_logs
        if ($this->db->tableExists('sales_gateway_logs')) {
            try {
                $this->db->query("ALTER TABLE `sales_gateway_logs` DROP FOREIGN KEY `FK_sales_gateway_logs_sales`");
            } catch (\Exception $e) {
                log_message('info', 'AlterSalesTablesForeignKeys down: Could not drop FK_sales_gateway_logs_sales: ' . $e->getMessage());
            }

            // Re-add platform foreign key if platform table exists
            if ($this->db->tableExists('platform')) {
                try {
                    $constraintExists = $this->checkForeignKeyExists('sales_gateway_logs', 'fk_sales_gateway_logs_platform_id');
                    if (!$constraintExists) {
                        $this->db->query("
                            ALTER TABLE `sales_gateway_logs`
                            ADD CONSTRAINT `fk_sales_gateway_logs_platform_id`
                            FOREIGN KEY (`platform_id`) 
                            REFERENCES `platform` (`id`) 
                            ON DELETE SET NULL 
                            ON UPDATE CASCADE
                        ");
                    }
                } catch (\Exception $e) {
                    log_message('info', 'AlterSalesTablesForeignKeys down: Could not re-add fk_sales_gateway_logs_platform_id: ' . $e->getMessage());
                }
            }

            try {
                $this->db->query("ALTER TABLE `sales_gateway_logs` DROP COLUMN `sale_id`");
            } catch (\Exception $e) {
                log_message('info', 'AlterSalesTablesForeignKeys down: Could not drop sale_id column from sales_gateway_logs: ' . $e->getMessage());
            }
        }

        // Reverse operation 2: Re-add foreign key to sales_fee (if fee_type table exists)
        if ($this->db->tableExists('sales_fee') && $this->db->tableExists('fee_type')) {
            try {
                $constraintExists = $this->checkForeignKeyExists('sales_fee', 'fk_sales_fee_fee_type_id');
                if (!$constraintExists) {
                    $this->db->query("
                        ALTER TABLE `sales_fee`
                        ADD CONSTRAINT `fk_sales_fee_fee_type_id`
                        FOREIGN KEY (`fee_type_id`) 
                        REFERENCES `fee_type` (`id`) 
                        ON DELETE RESTRICT 
                        ON UPDATE CASCADE
                    ");
                }
            } catch (\Exception $e) {
                log_message('info', 'AlterSalesTablesForeignKeys down: Could not re-add fk_sales_fee_fee_type_id: ' . $e->getMessage());
            }
        }

        // Reverse operation 1: Drop foreign key from sales_detail
        if ($this->db->tableExists('sales_detail')) {
            try {
                $this->db->query("ALTER TABLE `sales_detail` DROP FOREIGN KEY `FK_sales_detail_sales`");
            } catch (\Exception $e) {
                log_message('info', 'AlterSalesTablesForeignKeys down: Could not drop FK_sales_detail_sales: ' . $e->getMessage());
            }
        }
    }

    /**
     * Check if a foreign key constraint exists on a table
     * 
     * @param string $tableName
     * @param string $constraintName
     * @return bool
     */
    protected function checkForeignKeyExists(string $tableName, string $constraintName): bool
    {
        try {
            $dbName = $this->db->database;
            $query = $this->db->query("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ? 
                AND CONSTRAINT_NAME = ? 
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ", [$dbName, $tableName, $constraintName]);
            
            $result = $query->getRow();
            return !empty($result);
        } catch (\Exception $e) {
            // If we can't check, assume it doesn't exist to avoid errors
            return false;
        }
    }
}

