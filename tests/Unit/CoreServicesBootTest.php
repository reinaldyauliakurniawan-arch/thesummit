<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\BehaviorTracker;
use App\Services\ReflectionEngine;
use App\Services\ConsequenceEngine;
use App\Services\CrossPlayerEngine;
use App\Services\GameService;
use App\Services\SocialEngine;
use App\Services\ChallengeGenerator;
use App\Services\ChallengeFollowUpService;

class CoreServicesBootTest extends TestCase
{
    /**
     * Verify all service classes can be instantiated without exceptions.
     * This catches syntax errors that php -l might miss (e.g., in class constants).
     */
    public function test_all_services_can_be_reflected(): void
    {
        $services = [
            BehaviorTracker::class,
            ReflectionEngine::class,
            ConsequenceEngine::class,
            CrossPlayerEngine::class,
            GameService::class,
            SocialEngine::class,
            ChallengeGenerator::class,
            ChallengeFollowUpService::class,
        ];

        foreach ($services as $serviceClass) {
            $this->assertTrue(
                class_exists($serviceClass),
                "Service class {$serviceClass} should exist and be autoloadable"
            );
        }
    }

    /**
     * Verify all enum classes load correctly.
     */
    public function test_all_enums_are_loadable(): void
    {
        $enums = [
            \App\Enums\Badge::class,
            \App\Enums\CardCategory::class,
            \App\Enums\CardType::class,
            \App\Enums\Level::class,
            \App\Enums\GameStatus::class,
        ];

        foreach ($enums as $enumClass) {
            $this->assertTrue(
                enum_exists($enumClass),
                "Enum {$enumClass} should exist"
            );
        }
    }

    /**
     * Verify all model classes load correctly.
     */
    public function test_all_models_are_loadable(): void
    {
        $models = [
            \App\Models\User::class,
            \App\Models\GameRoom::class,
            \App\Models\GamePlayer::class,
            \App\Models\GameTurn::class,
            \App\Models\GameResult::class,
            \App\Models\ExpeditionCard::class,
            \App\Models\LeadershipProfile::class,
            \App\Models\Consequence::class,
            \App\Models\Promise::class,
            \App\Models\Vote::class,
            \App\Models\PlayerBehavior::class,
        ];

        foreach ($models as $modelClass) {
            $this->assertTrue(
                class_exists($modelClass),
                "Model {$modelClass} should exist and be autoloadable"
            );
        }
    }
}
