<?php

namespace Tests\Feature;

use App\Models\Image\ProductImage\ImageProductUploadService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UpgradeCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public static function adminPages(): array
    {
        return [
            ['/admin/products', 'Admin/Products/Products'],
            ['/admin/categories', 'Admin/Categories/Categories'],
            ['/admin/modifiers', 'Admin/Modifiers/Modifiers'],
            ['/admin/modifier-groups', 'Admin/ModifierGroups/ModifierGroups'],
            ['/admin/orders', 'Admin/Orders/Orders'],
            ['/admin/table-orders', 'Admin/OrdersTable/TableOrders'],
            ['/admin/product/images', 'Admin/Images/Images'],
            ['/admin/category/images', 'Admin/Images/Images'],
        ];
    }

    #[DataProvider('adminPages')]
    public function test_admin_pages_render_with_inertia_and_ziggy(string $url, string $component): void
    {
        $admin = User::factory()->create(['is_admin' => 1]);

        $this->actingAs($admin)->get($url)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component($component)
                ->where('auth.user.id', $admin->id)
                ->has('ziggy.routes.login'));
    }

    public function test_admin_access_remains_restricted(): void
    {
        $this->get('/admin/products')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())
            ->get('/admin/products')->assertRedirect('/login');
    }

    public function test_admin_can_upload_a_product_image_and_thumbnail(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => 1]);

        $this->actingAs($admin)->post('/admin/product/images/upload', [
            'image' => UploadedFile::fake()->image('product.png', 240, 320),
            'alt' => 'Product photo',
        ])->assertRedirect('/admin/product/images');

        $image = \App\Models\Image\ProductImage\ProductImage::firstOrFail();
        $this->assertSame('Product photo', $image->alt);
        Storage::disk('public')->assertExists('product/'.$image->title);
        $thumbnailPath = 'product/thumbnails/'.$image->title;
        Storage::disk('public')->assertExists($thumbnailPath);
        $dimensions = getimagesizefromstring(Storage::disk('public')->get($thumbnailPath));
        $this->assertSame([100, 100], [$dimensions[0], $dimensions[1]]);
        $this->assertSame('image/png', $dimensions['mime']);
    }

    public function test_image_upload_without_thumbnail_keeps_a_null_thumbnail_path(): void
    {
        Storage::fake('public');

        $image = (new ImageProductUploadService)->upload(
            UploadedFile::fake()->image('product.jpg'), 'Product photo',
        );

        $this->assertNull($image->thumbnail_path);
        Storage::disk('public')->assertExists('product/'.$image->title);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_public_catalog_and_order_board_data_are_available(): void
    {
        $this->getJson('/api/products')->assertOk();
        $this->getJson('/api/categories')->assertOk();

        $this->actingAs(User::factory()->create(['is_admin' => 1]))
            ->getJson('/admin/orders/all')
            ->assertOk()
            ->assertJsonCount(5, 'json.columnOrder');
    }
}
