<?php
namespace App\Providers;

use App\Events\PurchaseCompleted;
use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
use App\Events\CashbackProcessed;
use App\Events\CashbackFailed;
use App\Listeners\ProcessPurchaseForLoyalty;
use App\Listeners\NotifyUserOfAchievement;
use App\Listeners\NotifyUserOfBadge;
use App\Listeners\NotifyUserOfCashback;
use App\Listeners\NotifyUserOfCashbackFailure;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        PurchaseCompleted::class => [
            ProcessPurchaseForLoyalty::class,
        ],
        AchievementUnlocked::class => [
            NotifyUserOfAchievement::class,
        ],
        BadgeUnlocked::class => [
            NotifyUserOfBadge::class,
        ],
        CashbackProcessed::class => [
            NotifyUserOfCashback::class,
        ],
        CashbackFailed::class => [
            NotifyUserOfCashbackFailure::class,
        ],
    ];


    public function boot(): void
    {

    }


    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
