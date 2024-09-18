<?php

namespace App\Console\Commands;

use Illuminate\{
    Console\Command,
    Support\Facades\Http
};

use App\Models\ProductsModel;
use App\Services\ProductService;

class ImportProductsFromApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:import {--id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from an external API into the database';

    /**
     * Execute the console command.
     */

    private $productService;
    public function __construct(ProductService $productService)
    {
        parent::__construct();
        $this->productService = $productService;
    }

    public function handle()
    {
        if ($this->option('id')) {
            $this->importSingleProduct($this->option('id'));
        } else {
            $this->importAllProducts();
        }
    }

    protected function importSingleProduct($externalId)
    {
        $this->info("Importing product with ID: $externalId");

        $apiUrl = env('FAKE_STORE_URL') . "products/$externalId";

        $response = Http::get($apiUrl);

        if ($response->successful()) {
            $data = $response->json();

            $product = $this->productService->all()->where('name', $data['title'])->first();

            if (!$product) {
                $this->productService->create(
                    [
                        'name' => $data['title'],
                        'category' => $data['category'],
                        'description' => $data['description'],
                        'price' => $data['price'],
                        'image_url' => $data['image'],
                    ]
                );
                $this->info("Product ID $externalId imported successfully.");
            }
            $this->info("Product {$data['title']} already exists, skipping.");
        } else {
            $this->error('Failed to retrieve the product from the API.');
        }
    }
    public function importAllProducts()
    {
        $this->info('Starting data import...');

        $apiUrl = env('FAKE_STORE_URL') . 'products';

        $response = Http::get($apiUrl);

        if ($response->successful()) {
            $data = $response->json();

            foreach ($data as $item) {
                $product = $this->productService->all()->where('name', $item['title'])->first();

                if (!$product) {
                    $productsImported = [
                        'name' => $item['title'],
                        'category' => $item['category'],
                        'description' => $item['description'],
                        'price' => $item['price'],
                        'image_url' => $item['image'],
                    ];

                    $this->productService->create($productsImported);
                    $this->info("Product {$item['title']} imported successfully.");
                } else {
                    $this->info("Product {$item['title']} already exists, skipping.");
                }
            }

            $this->info('Data import completed successfully!');
        } else {
            $this->error('Failed to retrieve data from the API.');
        }
    }
}
