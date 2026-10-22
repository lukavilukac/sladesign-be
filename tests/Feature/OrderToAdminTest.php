<?php

namespace Tests\Feature;

use App\Mail\OrderToAdmin;
use App\Models\Order\Order;
use App\Models\Shipping\Shipping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrderToAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_link_opens_the_order_for_an_admin_and_requires_login(): void
    {
        $customer = User::factory()->create();
        $shipping = Shipping::create([
            'country' => 'Srbija',
            'city' => 'Beograd',
            'address' => 'Test 1',
            'phone_number' => '0601234567',
            'apartment' => '',
        ]);
        $order = Order::create([
            'user_id' => $customer->id,
            'shipping_id' => $shipping->id,
            'order_date' => now(),
            'order_status_id' => Order::PENDING,
            'price' => 1200,
        ]);

        $html = (new OrderToAdmin($order, $customer))->render();
        $this->assertSame(1, preg_match('/<a href="([^"]+)">Pogledaj porudžbinu #'.$order->id.'<\/a>/u', $html, $matches));
        $url = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
        $this->assertMatchesRegularExpression('#^https?://#', $url);
        $this->assertSame('/admin/orders/'.$order->id, parse_url($url, PHP_URL_PATH));

        $this->get($url)->assertRedirect('/login');
        $this->actingAs($customer)->get($url)->assertRedirect('/login');
        $this->actingAs(User::factory()->create(['is_admin' => 1]))
            ->get($url)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Orders/Order')
                ->where('order.id', $order->id)
                ->where('order.user.id', $customer->id));
    }
}
