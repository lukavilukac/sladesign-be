<?php

namespace App\Observers;

use App\Models\Order\Order;
use App\Models\OrderLine\OrderLine;
use App\Models\OrderLine\OrderLineModifier;
use App\Models\User;
use App\Services\OrderAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class OrderHistoryObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'created');
    }

    public function updated(Model $model): void
    {
        if ($model instanceof User) {
            return;
        }

        $this->record($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted');
    }

    private function record(Model $model, string $event): void
    {
        $attributes = Arr::except($event === 'updated' ? $model->getChanges() : $model->getAttributes(), ['created_at', 'updated_at']);
        if ($model instanceof User) {
            $attributes = Arr::only($attributes, ['first_name', 'last_name', 'email', 'country', 'city', 'address', 'phone_number', 'apartment']);
        }
        if ($attributes === []) {
            return;
        }
        $old = $event === 'created' ? [] : Arr::only($model->getRawOriginal(), array_keys($attributes));
        $changes = ['old' => $old, 'attributes' => $event === 'deleted' ? [] : $attributes];
        $orders = match (true) {
            $model instanceof Order => collect([$model]),
            $model instanceof OrderLine => collect([$model->order])->filter(),
            $model instanceof User => Order::where('user_id', $model->id)->get(),
            $model instanceof OrderLineModifier => collect([$model->orderLine?->order])->filter(),
            default => $model->orders()->get(), // Shipping changes can affect multiple orders.
        };
        foreach ($orders as $order) {
            app(OrderAudit::class)->record(strtolower(class_basename($model)).'.'.$event, $order,
                ['entity' => class_basename($model), 'entity_id' => $model->getKey()], $changes);
        }
    }
}
