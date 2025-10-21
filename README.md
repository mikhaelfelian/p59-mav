# Product Module - CodeIgniter 4.3.1 Migrations

This folder contains the **database migration files** for the Product Management module in CodeIgniter 4.3.1.

## 📦 Overview
The Product module provides a complete structure for managing products, variants, pricing, stock, and serial numbers.  
It follows the flow of product management described in your admin backoffice workflow.

### Tables Included
| Table | Description |
|--------|--------------|
| **product_category** | Stores product categories and descriptions. |
| **product** | Main product table with SKU and base details. |
| **product_variant** | Product variants such as color, size, or packaging. |
| **product_price** | Contains both retail and agent-specific pricing. |
| **product_serial** | Tracks unique serial numbers for individual product units. |

## ⚙️ Features
- Hierarchical product and variant structure  
- Multi-tier pricing (Retail & Agent)  
- Stock and barcode management per variant  
- Serial number tracking (manual or import)  
- Ready to integrate with promo and report modules  

## 🧩 Relationships
```
product_category 1 --- n product
product 1 --- n product_variant
product_variant 1 --- n product_price
product_variant 1 --- n product_serial
```

## 🚀 How to Use
1. Copy all migration files to your project:
   ```bash
   app/Database/Migrations/
   ```

2. Run migrations:
   ```bash
   php spark migrate
   ```

3. (Optional) Create seeders to populate base categories or product data.

## 📘 Notes
- All migrations use the default namespace `App\Database\Migrations`.
- Foreign keys use **ON DELETE CASCADE** to maintain integrity.
- Compatible with MySQL / MariaDB 10.4+.

---
**Author:** Mikhael Felian Waskito 
**Date:** 2025-10-21  
