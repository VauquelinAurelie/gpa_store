<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class ProductController extends Controller
{
    public function getProductsByCategory($category)
    {
        // Encode the category to avoid problems with special characters
        $encodedCategory = rawurlencode($category);
        $url = "https://fakestoreapi.com/products/category/{$encodedCategory}";

        Log::info("Fetching products from URL: {$url}");

        try {
            $response = Http::get($url);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                Log::error("API response error: " . $response->status());
                return response()->json(['error' => 'Unable to fetch products'], 500);
            }
        } catch (\Exception $e) {
            Log::error("Exception: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching products'], 500);
        }
    }

    // categories page - display products from all categories
    public function categories()
    {
        $categories = ['electronics', 'jewelery', 'men\'s clothing', 'women\'s clothing'];

        $categoryTranslations = [
            'electronics' => 'Électronique',
            'jewelery' => 'Bijoux',
            'men\'s clothing' => 'Vêtements homme',
            'women\'s clothing' => 'Vêtements femme',
        ];

        $categoryProducts = [];
        foreach ($categories as $category) {
            $encodedCategory = rawurlencode($category);
            $url = "https://fakestoreapi.com/products/category/{$encodedCategory}";

            Log::info("Fetching products for category '{$category}' from URL: {$url}");

            try {
                $response = Http::get($url);

                if ($response->successful()) {
                    $categoryProducts[$category] = $response->json();
                } else {
                    Log::error("API response error for category '{$category}': " . $response->status());
                    $categoryProducts[$category] = [];
                }
            } catch (\Exception $e) {
                Log::error("Exception fetching products for category '{$category}': " . $e->getMessage());
                $categoryProducts[$category] = [];
            }
        }

        return view('categories.index', [
            'categoryProducts' => $categoryProducts,
            'categoryTranslations' => $categoryTranslations
        ]);
    }

    public function show($id)
    {
        $url = "https://fakestoreapi.com/products/{$id}";

        try {
            $response = Http::get($url);

            if ($response->successful()) {
                $product = $response->json();
                return view('products.show', ['product' => $product]);
            } else {
                return redirect()->route('categories.index')->with('error', 'Unable to fetch product details.');
            }
        } catch (\Exception $e) {
            Log::error("Exception fetching product details: " . $e->getMessage());
            return redirect()->route('categories.index')->with('error', 'An error occurred while fetching product details.');
        }
    }
}

