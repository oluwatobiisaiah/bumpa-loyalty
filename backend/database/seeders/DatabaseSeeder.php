<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Achievement;
use App\Models\Badge;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder
 *
 * Main seeder that orchestrates all seeding
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BadgeSeeder::class,
            AchievementSeeder::class,
            UserSeeder::class,
        ]);
    }
}

/**
 * BadgeSeeder
 *
 * Seeds the badge progression system
 */
class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Bronze Member',
                'description' => 'Your journey begins! Welcome to the loyalty program.',
                'level' => 1,
                'points_required' => 0,
                'achievements_required' => 0,
                'icon' => '🥉',
                'color' => '#CD7F32',
                'benefits' => [
                    'Base cashback rate',
                    'Access to exclusive deals',
                ],
            ],
            [
                'name' => 'Silver Member',
                'description' => 'You\'re making great progress!',
                'level' => 2,
                'points_required' => 500,
                'achievements_required' => 3,
                'icon' => '🥈',
                'color' => '#C0C0C0',
                'benefits' => [
                    '+0.5% cashback bonus',
                    'Priority customer support',
                    'Early access to sales',
                ],
            ],
            [
                'name' => 'Gold Member',
                'description' => 'You\'re a valued member of our community!',
                'level' => 3,
                'points_required' => 2000,
                'achievements_required' => 8,
                'icon' => '🥇',
                'color' => '#FFD700',
                'benefits' => [
                    '+1% cashback bonus',
                    'Free shipping on all orders',
                    'Exclusive products access',
                    'Birthday rewards',
                ],
            ],
            [
                'name' => 'Platinum Member',
                'description' => 'Elite status achieved! You\'re among the best.',
                'level' => 4,
                'points_required' => 5000,
                'achievements_required' => 15,
                'icon' => '💎',
                'color' => '#E5E4E2',
                'benefits' => [
                    '+1.5% cashback bonus',
                    'VIP customer service',
                    'Personal shopping assistant',
                    'Exclusive event invitations',
                ],
            ],
            [
                'name' => 'Diamond Member',
                'description' => 'The pinnacle of loyalty! You\'re a legend.',
                'level' => 5,
                'points_required' => 10000,
                'achievements_required' => 25,
                'icon' => '💍',
                'color' => '#B9F2FF',
                'benefits' => [
                    '+2% cashback bonus',
                    'Concierge service',
                    'Lifetime benefits',
                    'Annual exclusive gift',
                    'Partner brand access',
                ],
            ],
        ];

        foreach ($badges as $badge) {
            Badge::create($badge);
        }
    }
}

/**
 * AchievementSeeder
 *
 * Seeds various achievements users can unlock
 */
class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            // Purchase count achievements
            [
                'name' => 'First Purchase',
                'description' => 'Complete your first purchase',
                'type' => 'purchase',
                'criteria' => ['target' => 1],
                'points' => 50,
                'tier' => 'bronze',
                'icon' => '🛒',
            ],
            [
                'name' => 'Regular Shopper',
                'description' => 'Complete 5 purchases',
                'type' => 'purchase',
                'criteria' => ['target' => 5],
                'points' => 100,
                'tier' => 'bronze',
                'icon' => '🛍️',
            ],
            [
                'name' => 'Dedicated Customer',
                'description' => 'Complete 10 purchases',
                'type' => 'purchase',
                'criteria' => ['target' => 10],
                'points' => 200,
                'tier' => 'silver',
                'icon' => '🎯',
            ],
            [
                'name' => 'Shopping Expert',
                'description' => 'Complete 25 purchases',
                'type' => 'purchase',
                'criteria' => ['target' => 25],
                'points' => 500,
                'tier' => 'gold',
                'icon' => '⭐',
            ],

            // Spending achievements
            [
                'name' => 'First 10K',
                'description' => 'Spend a total of ₦10,000',
                'type' => 'spending',
                'criteria' => ['target' => 10000],
                'points' => 100,
                'tier' => 'bronze',
                'icon' => '💰',
            ],
            [
                'name' => 'Big Spender',
                'description' => 'Spend a total of ₦50,000',
                'type' => 'spending',
                'criteria' => ['target' => 50000],
                'points' => 300,
                'tier' => 'silver',
                'icon' => '💸',
            ],
            [
                'name' => 'VIP Spender',
                'description' => 'Spend a total of ₦100,000',
                'type' => 'spending',
                'criteria' => ['target' => 100000],
                'points' => 600,
                'tier' => 'gold',
                'icon' => '💎',
            ],
            [
                'name' => 'Elite Spender',
                'description' => 'Spend a total of ₦250,000',
                'type' => 'spending',
                'criteria' => ['target' => 250000],
                'points' => 1200,
                'tier' => 'platinum',
                'icon' => '👑',
            ],

            // Tier achievements
            [
                'name' => 'Bronze Achiever',
                'description' => 'Unlock 3 bronze tier achievements',
                'type' => 'purchase',
                'criteria' => ['target' => 3],
                'points' => 150,
                'tier' => 'silver',
                'icon' => '🏅',
            ],
            [
                'name' => 'Silver Achiever',
                'description' => 'Unlock 3 silver tier achievements',
                'type' => 'purchase',
                'criteria' => ['target' => 6],
                'points' => 300,
                'tier' => 'gold',
                'icon' => '🎖️',
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::create($achievement);
        }
    }
}

/**
 * UserSeeder
 *
 * Seeds test users including admin
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@loyalty.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create test customer
        User::create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'total_points' => 0,
            'total_cashback' => 0,
        ]);

        // Create additional test customers with varying progress
        User::factory()->count(10)->create();
    }
}
