<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create stored procedure for generating main inventory monthly starts
        DB::unprepared('
            DROP PROCEDURE IF EXISTS GenerateMainInventoryStarts;
            
            CREATE PROCEDURE GenerateMainInventoryStarts(IN target_month DATE)
            BEGIN
                DECLARE done INT DEFAULT FALSE;
                DECLARE product_id_var INT;
                DECLARE start_qty INT DEFAULT 0;
                DECLARE added_qty INT DEFAULT 0;
                DECLARE sold_qty INT DEFAULT 0;
                DECLARE ending_qty INT DEFAULT 0;
                DECLARE previous_month_start DATE;
                DECLARE previous_month_end DATE;
                
                DECLARE product_cursor CURSOR FOR 
                    SELECT id FROM products;
                
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
                
                -- Calculate previous month dates
                SET previous_month_start = DATE_SUB(target_month, INTERVAL 1 MONTH);
                SET previous_month_end = LAST_DAY(previous_month_start);
                
                START TRANSACTION;
                
                OPEN product_cursor;
                
                product_loop: LOOP
                    FETCH product_cursor INTO product_id_var;
                    
                    IF done THEN
                        LEAVE product_loop;
                    END IF;
                    
                    -- Get start quantity for previous month
                    SELECT COALESCE(qty, 0) INTO start_qty
                    FROM start__inventories 
                    WHERE product_id = product_id_var 
                    AND month = previous_month_start
                    LIMIT 1;
                    
                    -- Get added quantities for previous month
                    SELECT COALESCE(SUM(qty), 0) INTO added_qty
                    FROM product_addeds 
                    WHERE product_id = product_id_var 
                    AND DATE(created_at) BETWEEN previous_month_start AND previous_month_end;
                    
                    -- Get sold quantities for previous month (aggregate from all branches)
                    SELECT COALESCE(SUM(s.qty), 0) INTO sold_qty
                    FROM sells s
                    JOIN product_branches pb ON s.product_branch_id = pb.id
                    WHERE pb.product_id = product_id_var 
                    AND DATE(s.created_at) BETWEEN previous_month_start AND previous_month_end;
                    
                    -- Calculate ending quantity (minimum 0)
                    SET ending_qty = GREATEST(0, start_qty + added_qty - sold_qty);
                    
                    -- Insert or update the start for target month
                    INSERT INTO start__inventories (product_id, month, qty, created_at, updated_at)
                    VALUES (product_id_var, target_month, ending_qty, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE 
                        qty = ending_qty,
                        updated_at = NOW();
                        
                END LOOP;
                
                CLOSE product_cursor;
                COMMIT;
                
            END
        ');

        // Create stored procedure for generating branch inventory monthly starts
        DB::unprepared('
            DROP PROCEDURE IF EXISTS GenerateBranchInventoryStarts;
            
            CREATE PROCEDURE GenerateBranchInventoryStarts(IN target_month DATE)
            BEGIN
                DECLARE done INT DEFAULT FALSE;
                DECLARE product_branch_id_var INT;
                DECLARE start_qty INT DEFAULT 0;
                DECLARE added_qty INT DEFAULT 0;
                DECLARE sold_qty INT DEFAULT 0;
                DECLARE ending_qty INT DEFAULT 0;
                DECLARE previous_month_start DATE;
                DECLARE previous_month_end DATE;
                DECLARE product_id_var INT;
                DECLARE branch_id_var INT;
                
                DECLARE branch_cursor CURSOR FOR 
                    SELECT id, product_id, branch_id FROM product_branches;
                
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
                
                -- Calculate previous month dates
                SET previous_month_start = DATE_SUB(target_month, INTERVAL 1 MONTH);
                SET previous_month_end = LAST_DAY(previous_month_start);
                
                START TRANSACTION;
                
                OPEN branch_cursor;
                
                branch_loop: LOOP
                    FETCH branch_cursor INTO product_branch_id_var, product_id_var, branch_id_var;
                    
                    IF done THEN
                        LEAVE branch_loop;
                    END IF;
                    
                    -- Get start quantity for previous month
                    SELECT COALESCE(qty, 0) INTO start_qty
                    FROM starts 
                    WHERE product_branch_id = product_branch_id_var 
                    AND month = previous_month_start
                    LIMIT 1;
                    
                    -- Get added quantities for previous month for this specific branch
                    SELECT COALESCE(SUM(qty), 0) INTO added_qty
                    FROM product_addeds 
                    WHERE product_id = product_id_var 
                    AND branch_id = branch_id_var
                    AND DATE(created_at) BETWEEN previous_month_start AND previous_month_end;
                    
                    -- Get sold quantities for previous month for this specific product-branch
                    SELECT COALESCE(SUM(qty), 0) INTO sold_qty
                    FROM sells 
                    WHERE product_branch_id = product_branch_id_var 
                    AND DATE(created_at) BETWEEN previous_month_start AND previous_month_end;
                    
                    -- Calculate ending quantity (minimum 0)
                    SET ending_qty = GREATEST(0, start_qty + added_qty - sold_qty);
                    
                    -- Insert or update the start for target month
                    INSERT INTO starts (product_branch_id, month, qty, created_at, updated_at)
                    VALUES (product_branch_id_var, target_month, ending_qty, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE 
                        qty = ending_qty,
                        updated_at = NOW();
                        
                END LOOP;
                
                CLOSE branch_cursor;
                COMMIT;
                
            END
        ');

        // Create stored procedure for generating both inventories
        DB::unprepared('
            DROP PROCEDURE IF EXISTS GenerateAllMonthlyStarts;
            
            CREATE PROCEDURE GenerateAllMonthlyStarts(IN target_month DATE)
            BEGIN
                CALL GenerateMainInventoryStarts(target_month);
                CALL GenerateBranchInventoryStarts(target_month);
            END
        ');

        // Create function to get monthly inventory summary
        DB::unprepared('
            DROP FUNCTION IF EXISTS GetMonthlyInventorySummary;
            
            CREATE FUNCTION GetMonthlyInventorySummary(target_month DATE) 
            RETURNS JSON
            READS SQL DATA
            DETERMINISTIC
            BEGIN
                DECLARE main_count INT DEFAULT 0;
                DECLARE branch_count INT DEFAULT 0;
                DECLARE main_total_qty INT DEFAULT 0;
                DECLARE branch_total_qty INT DEFAULT 0;
                DECLARE result JSON;
                
                -- Get main inventory summary
                SELECT COUNT(*), COALESCE(SUM(qty), 0) 
                INTO main_count, main_total_qty
                FROM start__inventories 
                WHERE month = target_month;
                
                -- Get branch inventory summary
                SELECT COUNT(*), COALESCE(SUM(qty), 0) 
                INTO branch_count, branch_total_qty
                FROM starts 
                WHERE month = target_month;
                
                SET result = JSON_OBJECT(
                    "month", target_month,
                    "main_inventory", JSON_OBJECT(
                        "count", main_count,
                        "total_qty", main_total_qty
                    ),
                    "branch_inventory", JSON_OBJECT(
                        "count", branch_count,
                        "total_qty", branch_total_qty
                    )
                );
                
                RETURN result;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS GenerateMainInventoryStarts');
        DB::unprepared('DROP PROCEDURE IF EXISTS GenerateBranchInventoryStarts');
        DB::unprepared('DROP PROCEDURE IF EXISTS GenerateAllMonthlyStarts');
        DB::unprepared('DROP FUNCTION IF EXISTS GetMonthlyInventorySummary');
    }
};
