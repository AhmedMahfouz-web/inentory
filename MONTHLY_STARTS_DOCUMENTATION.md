# Monthly Start System Documentation

## Overview
This document describes the enhanced monthly start system for the inventory management application. The system automatically generates monthly starting quantities for both main inventory and branch inventory based on previous month's ending quantities.

## System Architecture

### 1. Database Structure
- **`starts`**: Monthly starting quantities for branch products
  - `product_branch_id`: Foreign key to product_branches table
  - `month`: Date (first day of month)
  - `qty`: Starting quantity

- **`start__inventories`**: Monthly starting quantities for main inventory
  - `product_id`: Foreign key to products table
  - `month`: Date (first day of month)
  - `qty`: Starting quantity

### 2. Key Components

#### A. Service Layer
- **`MonthlyStartService`**: Core business logic for monthly start operations
  - Generate monthly starts for main/branch inventory
  - Calculate ending quantities based on: start + added - sold
  - Provide reporting and status checking

#### B. Command Layer
- **`GenerateMonthlyStarts`**: Artisan command for manual/scheduled execution
  - Supports specific month and type parameters
  - Provides detailed console output
  - Error handling and logging

#### C. Controller Layer
- **`MonthlyStartController`**: Web interface for monthly start management
  - Dashboard for current/previous month status
  - Manual generation for specific months
  - Reporting and summary views

#### D. MySQL Stored Procedures
- **`GenerateMainInventoryStarts(month)`**: High-performance main inventory generation
- **`GenerateBranchInventoryStarts(month)`**: High-performance branch inventory generation
- **`GenerateAllMonthlyStarts(month)`**: Combined generation for both inventories
- **`GetMonthlyInventorySummary(month)`**: JSON summary function

## Usage Guide

### 1. Web Interface
Access the monthly starts management at: `/monthly-starts`

**Features:**
- View current and previous month status
- Generate starts for current month automatically
- Manual generation for specific months and types
- Comprehensive reporting with export capabilities

### 2. Command Line Interface

#### Generate for current month:
```bash
php artisan inventory:generate-monthly-starts
```

#### Generate for specific month:
```bash
php artisan inventory:generate-monthly-starts --month=2024-01
```

#### Generate specific type only:
```bash
php artisan inventory:generate-monthly-starts --type=main
php artisan inventory:generate-monthly-starts --type=branch
php artisan inventory:generate-monthly-starts --type=both
```

### 3. MySQL Direct Execution

#### For better performance with large datasets:
```sql
-- Generate main inventory starts for January 2024
CALL GenerateMainInventoryStarts('2024-01-01');

-- Generate branch inventory starts for January 2024
CALL GenerateBranchInventoryStarts('2024-01-01');

-- Generate both inventories
CALL GenerateAllMonthlyStarts('2024-01-01');

-- Get summary
SELECT GetMonthlyInventorySummary('2024-01-01') as summary;
```

### 4. Scheduled Automation

The system includes automatic scheduling:
- **Monthly Generation**: 1st of each month at 1:00 AM
- **Daily Check**: Generates current month starts if missing
- **Overlap Protection**: Prevents concurrent executions

## API Endpoints

### Check if monthly starts exist:
```
GET /monthly-starts/check-exists?month=2024-01
```

### Get monthly summary:
```
GET /monthly-starts/summary?month=2024-01
```

## Calculation Logic

### Ending Quantity Formula:
```
ending_qty = MAX(0, start_qty + added_qty - sold_qty)
```

Where:
- `start_qty`: Starting quantity from previous month
- `added_qty`: Products added during the month
- `sold_qty`: Products sold during the month
- `MAX(0, ...)`: Ensures no negative quantities

### Data Sources:
- **Added Quantities**: `product_addeds` table
- **Sold Quantities**: `sells` table
- **Previous Starts**: `starts` or `start__inventories` tables

## Error Handling

### 1. Database Transactions
- All operations wrapped in transactions
- Automatic rollback on errors
- Detailed error logging

### 2. Validation
- Month format validation (Y-m)
- Type parameter validation
- Data existence checks

### 3. Logging
- Comprehensive logging to Laravel log files
- Error details with context
- Performance metrics

## Performance Considerations

### 1. MySQL Stored Procedures
- Use stored procedures for large datasets (>10,000 products)
- Significantly faster than PHP loops
- Reduced memory usage

### 2. Batch Processing
- Process products in batches to avoid memory issues
- Configurable batch sizes
- Progress tracking

### 3. Indexing
Ensure proper database indexes:
```sql
-- Recommended indexes for optimal performance
CREATE INDEX idx_starts_product_branch_month ON starts(product_branch_id, month);
CREATE INDEX idx_start_inventories_product_month ON start__inventories(product_id, month);
CREATE INDEX idx_sells_created_at ON sells(created_at);
CREATE INDEX idx_product_addeds_created_at ON product_addeds(created_at);
```

## Troubleshooting

### Common Issues:

1. **Missing Previous Month Data**
   - Solution: Generate previous month manually or input starting data

2. **Negative Quantities**
   - System prevents negative quantities (sets to 0)
   - Check for data inconsistencies in sales/additions

3. **Performance Issues**
   - Use MySQL stored procedures for large datasets
   - Check database indexes
   - Consider batch processing

4. **Scheduling Not Working**
   - Ensure Laravel scheduler is running: `php artisan schedule:work`
   - Check cron job configuration
   - Verify command permissions

### Debug Commands:

```bash
# Test command execution
php artisan inventory:generate-monthly-starts --month=2024-01 --type=both

# Check scheduled tasks
php artisan schedule:list

# View logs
tail -f storage/logs/laravel.log
```

## Migration Instructions

### 1. Run Database Migration:
```bash
php artisan migrate
```

### 2. Install Dependencies:
No additional dependencies required - uses existing Laravel/MySQL features.

### 3. Configure Scheduling:
Add to your server's crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Test Installation:
```bash
# Test command
php artisan inventory:generate-monthly-starts --month=$(date +%Y-%m) --type=both

# Test web interface
# Visit: http://your-domain/monthly-starts
```

## Security Considerations

1. **Access Control**: Ensure proper authentication for web routes
2. **Input Validation**: All user inputs are validated
3. **SQL Injection**: Uses parameterized queries and prepared statements
4. **Error Disclosure**: Sensitive information not exposed in error messages

## Future Enhancements

1. **Email Notifications**: Notify administrators of generation results
2. **Advanced Reporting**: More detailed analytics and trends
3. **API Integration**: RESTful API for external system integration
4. **Audit Trail**: Track who generated what and when
5. **Backup Integration**: Automatic backup before major operations

## Support

For technical support or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review database queries and performance
3. Verify system requirements and configuration
4. Test with small datasets first
