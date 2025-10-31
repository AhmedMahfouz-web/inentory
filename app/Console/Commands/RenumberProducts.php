<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RenumberProducts extends Command
{
    protected $signature = 'products:renumber {--start=1001 : Starting number for product codes}';
    protected $description = 'Renumber all product codes starting from a specified number';

    public function handle()
    {
        $startNumber = (int) $this->option('start');
        
        $this->info('Starting product renumbering from ' . $startNumber);
        
        try {
            DB::beginTransaction();
            
            // Get all products ordered by ID
            $products = Product::orderBy('id', 'asc')->get();
            
            if ($products->isEmpty()) {
                $this->warn('No products found in database.');
                return Command::SUCCESS;
            }
            
            $currentNumber = $startNumber;
            $updated = 0;
            
            $this->info('Found ' . $products->count() . ' products to renumber.');
            
            $progressBar = $this->output->createProgressBar($products->count());
            $progressBar->start();
            
            foreach ($products as $product) {
                $oldCode = $product->code;
                $newCode = (string) $currentNumber;
                
                $product->update(['code' => $newCode]);
                
                $updated++;
                $currentNumber++;
                $progressBar->advance();
            }
            
            $progressBar->finish();
            $this->newLine(2);
            
            DB::commit();
            
            $this->info('Successfully renumbered ' . $updated . ' products.');
            $this->info('Last product code: ' . ($currentNumber - 1));
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error renumbering products: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
