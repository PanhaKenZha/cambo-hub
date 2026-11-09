<?php

namespace Database\Factories;

use App\Models\ApprovalEvent;
use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApprovalEvent>
 */
class ApprovalEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'approval_request_id' => ApprovalRequest::factory(),
            'actor_id' => User::factory(),
            'action' => 'submitted',
            'note' => null,
        ];
    }
}
