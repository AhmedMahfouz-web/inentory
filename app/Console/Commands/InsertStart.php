<?php

namespace App\Console\Commands;

use App\Models\Product_branch;
use App\Models\Start;
use Carbon\Carbon;
use Illuminate\Console\Command;

class InsertStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:insert-start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'inserting the start of every product';

    /**
     * Execute the console command.
     */

    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $date = date('Y-m');
        $products = Product_branch::get();
        $qty = $products->map(function ($product) use ($date) {
            return [
                'product' => $product,
                'qty' => $product->qty($date),
            ];
        });

        foreach ($qty as $product) {
            Start::create([
                'product_branch_id' => 1,
                'month' => date('Y-m') . '-07',
                'qty' => 100,
                // Add other columns and their values as needed
            ]);
        }

        $this->info('Data inserted successfully');
    }
}
