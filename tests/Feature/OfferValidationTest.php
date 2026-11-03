<?php

use App\Enums\OfferScope;
use App\Enums\OfferType;
use App\Models\Customer;
use App\Models\Offer;
use App\Models\OfferUsage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Modules\Offer\Services\OfferService;

uses(RefreshDatabase::class);

function makeBaseOffer(array $overrides = []): Offer
{
    return Offer::query()->create(array_merge([
        'name' => 'Test Offer',
        'coupon_code' => 'SAVE10',
        'type' => OfferType::Percentage->value,
        'value' => 10,
        'min_purchase' => null,
        'max_discount' => null,
        'usage_limit' => null,
        'per_customer_limit' => null,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
        'scope' => OfferScope::Order->value,
        'is_active' => true,
    ], $overrides));
}

function orderContext(array $overrides = []): array
{
    return array_merge([
        'customer_id' => null,
        'subtotal' => 100,
        'items' => [
            ['product_id' => 1, 'category_id' => 1, 'line_subtotal' => 100],
        ],
        'coupon_code' => 'SAVE10',
    ], $overrides);
}

function expectOfferValidationError(callable $callback, string $message): void
{
    try {
        $callback();
        test()->fail('Expected ValidationException was not thrown.');
    } catch (ValidationException $exception) {
        expect($exception->errors()['offer'][0] ?? null)->toBe($message);
    }
}

test('expired offer is rejected', function () {
    makeBaseOffer([
        'ends_at' => now()->subHour(),
    ]);

    $service = app(OfferService::class);

    expectOfferValidationError(
        fn () => $service->validateAndCalculate(orderContext()),
        'This offer has expired.'
    );
});

test('inactive offer is rejected', function () {
    makeBaseOffer([
        'is_active' => false,
    ]);

    $service = app(OfferService::class);

    expectOfferValidationError(
        fn () => $service->validateAndCalculate(orderContext()),
        'This offer is inactive.'
    );
});

test('min purchase amount is enforced', function () {
    makeBaseOffer([
        'min_purchase' => 150,
    ]);

    $service = app(OfferService::class);

    expectOfferValidationError(
        fn () => $service->validateAndCalculate(orderContext(['subtotal' => 100])),
        'Order does not meet the minimum purchase amount for this offer.'
    );
});

test('usage limit is enforced', function () {
    $offer = makeBaseOffer([
        'usage_limit' => 1,
    ]);

    $user = User::factory()->create(['session_version' => 1]);
    $customer = Customer::query()->create([
        'name' => 'Walk-in',
        'is_walk_in' => true,
        'is_active' => true,
    ]);

    $order = Order::query()->create([
        'order_number' => 'ORD-'.uniqid(),
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'subtotal' => 50,
        'discount_amount' => 5,
        'tax_amount' => 0,
        'total' => 45,
    ]);

    OfferUsage::query()->create([
        'offer_id' => $offer->id,
        'order_id' => $order->id,
        'customer_id' => $customer->id,
        'discount_amount' => 5,
    ]);

    $service = app(OfferService::class);

    expectOfferValidationError(
        fn () => $service->validateAndCalculate(orderContext([
            'customer_id' => $customer->id,
            'subtotal' => 100,
        ])),
        'This offer has reached its usage limit.'
    );
});

test('percentage discount is calculated with max cap', function () {
    $offer = makeBaseOffer([
        'type' => OfferType::Percentage->value,
        'value' => 50,
        'max_discount' => 20,
    ]);

    $service = app(OfferService::class);

    $result = $service->validateAndCalculate(orderContext([
        'subtotal' => 100,
        'offer_id' => $offer->id,
        'coupon_code' => null,
    ]));

    expect($result['offer_id'])->toBe($offer->id)
        ->and($result['discount_amount'])->toBe(20.0);
});
