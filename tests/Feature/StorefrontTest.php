<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\VendorProfile;
use App\Models\Product;
use App\Models\Category;
use App\Models\District;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function store_page_shows_vendor_and_products()
    {
        $district = District::create(['name' => 'Test District']);
        $category = Category::create(['name' => 'Test Cat', 'slug' => 'test-cat']);
        $vendor = VendorProfile::create([
            'shop_name' => 'Acme Shop',
            'slug' => 'acme-shop',
            'status' => 'approved',
            'district_id' => $district->id,
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => 'Handmade Vase',
            'slug' => 'handmade-vase',
            'price' => 120.00,
            'status' => 'approved',
            'stock' => 5,
        ]);
        $response = $this->get(route('shop.store.show', $vendor->slug));
        $response->assertStatus(200);
        $response->assertSee('Acme Shop');
        $response->assertSee('Handmade Vase');
        $response->assertSee(route('shop.product.show', $product->slug));
    }

    /** @test */
    public function non_existent_store_returns_404()
    {
        $this->get('/store/no-such-store')->assertStatus(404);
    }
}
