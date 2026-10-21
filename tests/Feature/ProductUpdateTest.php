<?php

namespace Tests\Feature;

use App\Models\Image\ProductImage\ProductImage;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductUpdateTest extends TestCase
{
    use RefreshDatabase;

    public static function descriptions(): array
    {
        return [
            'null' => [['en_description' => null], ''],
            'empty' => [['en_description' => ''], ''],
            'omitted' => [[], ''],
            'translated' => [['en_description' => 'English description'], 'English description'],
        ];
    }

    #[DataProvider('descriptions')]
    public function test_product_updates_with_an_optional_english_description(array $description, string $expected): void
    {
        $image = ProductImage::create(['path' => '/storage/product/test.jpg']);
        $product = Product::create([
            'name' => 'Proizvod', 'slug' => 'proizvod',
            'description' => 'Opis', 'price' => 100,
            'product_image_id' => $image->id,
        ]);
        $this->actingAs(User::factory()->admin()->create());
        $data = array_merge([
            'name' => 'Izmenjen proizvod', 'slug' => 'izmenjen-proizvod',
            'description' => 'Izmenjen opis', 'price' => 200,
            'product_image_id' => $image->id,
            'product_image_ids' => [$image->id],
            'en_name' => 'Updated product', 'en_slug' => 'updated-product',
        ], $description);

        // Exercise both creation of a translation and updating an existing one.
        foreach ([201, 202] as $price) {
            $this->put('/admin/products/'.$product->id.'/update', array_merge($data, ['price' => $price]))
                ->assertSessionHasNoErrors()
                ->assertRedirect('/admin/products');

            $this->assertDatabaseHas('products', [
                'id' => $product->id, 'name' => 'Izmenjen proizvod', 'price' => $price,
            ]);
            $this->assertDatabaseHas('product_translations', [
                'product_id' => $product->id, 'locale' => 'en',
                'name' => 'Updated product', 'description' => $expected,
            ]);
            $this->assertDatabaseHas('product_product_image', [
                'product_id' => $product->id, 'product_image_id' => $image->id,
            ]);
            $this->assertDatabaseCount('product_translations', 1);
        }
    }
}
