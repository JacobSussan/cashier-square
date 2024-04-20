<?php

namespace Laravel\Cashier\Database\Factories;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Subscription;
use Square\Models\Subscription as SquareSubscription;
use Square\Models\SubscriptionStatus;

class SubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $model = Cashier::$customerModel;

        return [
            (new $model)->getForeignKey() => ($model)::factory(),
            'type' => 'default',
            'square_id' => 'sub_'.Str::random(40),
            'square_status' => SubscriptionStatus::ACTIVE,
            'square_plan_id' => null,
            'quantity' => null,
            'trial_ends_at' => null,
            'ends_at' => null,
        ];
    }

    /**
     * Add a plan identifier to the model.
     *
     * @return $this
     */
    public function withPlan(string $planId): static
    {
        return $this->state([
            'square_plan_id' => $planId,
        ]);
    }

    /**
     * Mark the subscription as active.
     *
     * @return $this
     */
    public function active(): static
    {
        return $this->state([
            'square_status' => SubscriptionStatus::ACTIVE,
        ]);
    }

    /**
     * Mark the subscription as being within a trial period.
     *
     * @return $this
     */
    public function trialing(DateTimeInterface $trialEndsAt = null): static
    {
        return $this->state([
            'square_status' => SubscriptionStatus::TRIAL,
            'trial_ends_at' => $trialEndsAt,
        ]);
    }

    /**
     * Mark the subscription as canceled.
     *
     * @return $this
     */
    public function canceled(): static
    {
        return $this->state([
            'square_status' => SubscriptionStatus::CANCELED,
            'ends_at' => now(),
        ]);
    }

    /**
     * Mark the subscription as incomplete.
     *
     * @return $this
     */
    public function incomplete(): static
    {
        return $this->state([
            'square_status' => SubscriptionStatus::INCOMPLETE,
        ]);
    }

    /**
     * Mark the subscription as incomplete where the allowed completion period has expired.
     *
     * @return $this
     */
    public function incompleteAndExpired(): static
    {
        return $this->state([
            'square_status' => SubscriptionStatus::INCOMPLETE_EXPIRED,
        ]);
    }

    /**
     * Mark the subscription as being past the due date.
     *
     * @return $this
     */
    public function pastDue(): static
    {
        return $this->state([
            'square_status' => SubscriptionStatus::PAST_DUE,
        ]);
    }

    /**
     * Mark the subscription as unpaid.
     *
     * @return $this
     */
    public function unpaid(): static
    {
        return $this->state([
            'square_status' => SubscriptionStatus::UNPAID,
        ]);
    }
}
