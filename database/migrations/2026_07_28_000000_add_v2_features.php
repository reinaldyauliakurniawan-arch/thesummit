<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Consequences Table (Persistent Consequences Engine) ──
        Schema::create('consequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('originating_turn_id')->constrained('game_turns')->cascadeOnDelete();
            $table->string('effect_type'); // immediate, delayed, conditional
            $table->string('trigger_type')->nullable(); // after_rounds, on_condition, on_level_change, on_event
            $table->integer('trigger_value')->nullable(); // e.g. rounds to wait
            $table->string('trigger_condition')->nullable(); // JSON condition
            $table->string('stat'); // mp, sp, tt, reputation, resources
            $table->integer('delta');
            $table->text('description');
            $table->boolean('is_hidden')->default(false); // Hidden Information feature
            $table->boolean('is_triggered')->default(false);
            $table->timestamp('triggered_at')->nullable();
            $table->timestamps();

            $table->index(['game_room_id', 'game_player_id', 'is_triggered']);
            $table->index(['game_room_id', 'trigger_type', 'trigger_value']);
        });

        // ── 2. Player Behaviors Table (Leadership Identity Tracking) ──
        Schema::create('player_behaviors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_turn_id')->constrained()->cascadeOnDelete();
            $table->string('behavior_type'); // risk_taking, collaboration, empathy, decisiveness, coaching, control, adaptability
            $table->integer('score'); // -2 to +2 scale
            $table->text('evidence');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['game_player_id', 'behavior_type']);
        });

        // ── 3. Promises Table (Social Mechanics) ──
        Schema::create('promises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promiser_player_id')->constrained('game_players')->cascadeOnDelete();
            $table->foreignId('recipient_player_id')->constrained('game_players')->cascadeOnDelete();
            $table->string('promise_type'); // vote_for, help_rescue, share_resource, support_bridge, protect_trust
            $table->text('description');
            $table->boolean('is_fulfilled')->default(false);
            $table->boolean('is_broken')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();

            $table->index(['game_room_id', 'promiser_player_id']);
        });

        // ── 4. Votes Table (Social Mechanics - Vote Events) ──
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('triggering_player_id')->constrained('game_players')->cascadeOnDelete();
            $table->string('vote_topic'); // e.g. "Siapa yang mendapat bonus TT?"
            $table->text('vote_description');
            $table->string('vote_type'); // single_choice, approval
            $table->json('options')->nullable();
            $table->json('votes_cast')->nullable(); // {player_id: choice}
            $table->boolean('is_resolved')->default(false);
            $table->string('result')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['game_room_id', 'is_resolved']);
        });

        // ── 5. Leadership Profiles Table (Reflection Engine) ──
        Schema::create('leadership_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_result_id')->constrained()->cascadeOnDelete();
            $table->string('leadership_style');
            $table->json('strengths'); // top 3
            $table->json('blind_spots'); // top 3
            $table->json('decision_timeline'); // summary of key decisions
            $table->text('missed_opportunities');
            $table->text('key_turning_point');
            $table->text('coaching_recommendations');
            $table->json('behavior_scores'); // {behavior_type: aggregate_score}
            $table->timestamps();

            $table->unique(['game_player_id']);
        });

        // ── 6. Real World Challenges Table (Real World Action Loop) ──
        Schema::create('real_world_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_result_id')->constrained()->cascadeOnDelete();
            $table->text('challenge');
            $table->string('challenge_type'); // delegate, feedback, conversation, initiative, reflection
            $table->text('why_this_challenge');
            $table->boolean('is_completed')->default(false);
            $table->text('completion_notes')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->timestamps();

            $table->unique(['game_player_id']);
        });

        // ── 7. Cross-Player Effects Table (Team Interdependency) ──
        Schema::create('cross_player_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_player_id')->constrained('game_players')->cascadeOnDelete();
            $table->foreignId('target_player_id')->constrained('game_players')->cascadeOnDelete();
            $table->foreignId('game_turn_id')->constrained('game_turns')->cascadeOnDelete();
            $table->string('stat'); // mp, sp, tt, reputation
            $table->integer('delta');
            $table->text('description');
            $table->string('effect_type'); // penalty, bonus, shared
            $table->timestamps();

            $table->index(['game_room_id', 'target_player_id']);
        });

        // ── 8. Add new columns to expedition_cards ──
        Schema::table('expedition_cards', function (Blueprint $table) {
            // Delayed effects per option
            $table->json('opsi_a_delayed_effects')->nullable();
            $table->json('opsi_b_delayed_effects')->nullable();
            // Conditional effects per option
            $table->json('opsi_a_conditional_effects')->nullable();
            $table->json('opsi_b_conditional_effects')->nullable();
            // Cross-player effects
            $table->json('opsi_a_cross_player')->nullable();
            $table->json('opsi_b_cross_player')->nullable();
            // Hidden info flag
            $table->boolean('has_hidden_info')->default(false);
            $table->text('hidden_info_reveal')->nullable();
            // Reputation and Resources dimensions
            $table->tinyInteger('opsi_a_reputation')->default(0);
            $table->tinyInteger('opsi_b_reputation')->default(0);
            $table->tinyInteger('opsi_a_resources')->default(0);
            $table->tinyInteger('opsi_b_resources')->default(0);
            // Future flexibility indicator
            $table->tinyInteger('opsi_a_flexibility')->default(0); // negative = less flexible
            $table->tinyInteger('opsi_b_flexibility')->default(0);
            // Behavior tags for leadership tracking
            $table->json('opsi_a_behavior_tags')->nullable();
            $table->json('opsi_b_behavior_tags')->nullable();
        });

        // ── 9. Add new columns to game_players ──
        Schema::table('game_players', function (Blueprint $table) {
            $table->integer('reputation')->default(0);
            $table->integer('resources')->default(0);
            $table->integer('flexibility')->default(0); // 0 = full, goes negative
            $table->integer('promises_kept')->default(0);
            $table->integer('promises_broken')->default(0);
        });

        // ── 10. Add new columns to game_turns ──
        Schema::table('game_turns', function (Blueprint $table) {
            $table->json('consequences_created')->nullable();
            $table->json('cross_player_effects')->nullable();
            $table->json('behavior_data')->nullable();
            $table->boolean('was_hidden')->default(false);
            $table->text('hidden_info_shown')->nullable();
            $table->integer('reputation_effect')->default(0);
            $table->integer('resources_effect')->default(0);
            $table->integer('flexibility_effect')->default(0);
        });

        // ── 11. Add new columns to game_results ──
        Schema::table('game_results', function (Blueprint $table) {
            $table->integer('final_reputation')->default(0);
            $table->integer('final_resources')->default(0);
            $table->integer('final_flexibility')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cross_player_effects');
        Schema::dropIfExists('real_world_challenges');
        Schema::dropIfExists('leadership_profiles');
        Schema::dropIfExists('votes');
        Schema::dropIfExists('promises');
        Schema::dropIfExists('player_behaviors');
        Schema::dropIfExists('consequences');

        Schema::table('game_results', function (Blueprint $table) {
            $table->dropColumn(['final_reputation', 'final_resources', 'final_flexibility']);
        });

        Schema::table('game_turns', function (Blueprint $table) {
            $table->dropColumn([
                'consequences_created', 'cross_player_effects', 'behavior_data',
                'was_hidden', 'hidden_info_shown', 'reputation_effect',
                'resources_effect', 'flexibility_effect',
            ]);
        });

        Schema::table('game_players', function (Blueprint $table) {
            $table->dropColumn([
                'reputation', 'resources', 'flexibility',
                'promises_kept', 'promises_broken',
            ]);
        });

        Schema::table('expedition_cards', function (Blueprint $table) {
            $table->dropColumn([
                'opsi_a_delayed_effects', 'opsi_b_delayed_effects',
                'opsi_a_conditional_effects', 'opsi_b_conditional_effects',
                'opsi_a_cross_player', 'opsi_b_cross_player',
                'has_hidden_info', 'hidden_info_reveal',
                'opsi_a_reputation', 'opsi_b_reputation',
                'opsi_a_resources', 'opsi_b_resources',
                'opsi_a_flexibility', 'opsi_b_flexibility',
                'opsi_a_behavior_tags', 'opsi_b_behavior_tags',
            ]);
        });
    }
};
