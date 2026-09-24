<?php

namespace App\Console\Commands;

use App\Models\Delivery;
use App\Models\User;
use App\Notifications\UpcomingDeliveryReminder;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckUpcomingDeliveries extends Command
{
    protected $signature = 'deliveries:check-upcoming';
    protected $description = 'Notify staff of deliveries due in 2 days';

    public function handle(): void
    {
        $targetDate = Carbon::today()->addDays(2)->toDateString();

        $deliveries = Delivery::whereDate('delivery_date', $targetDate)
            ->where('status', '!=', 'complete')
            ->get();

        if ($deliveries->isEmpty()) {
            $this->info('No deliveries due in 2 days.');
            return;
        }

        // For now, notify everyone with role Owner or Manager
        $recipients = User::whereIn('role', ['Owner', 'Manager'])->get();

        foreach ($deliveries as $delivery) {
            foreach ($recipients as $user) {
                $user->notify(new UpcomingDeliveryReminder($delivery));
            }
            $this->info("Notified team about delivery {$delivery->delivery_number}");
        }
    }
}