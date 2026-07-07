<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
public function up():void{Schema::create('users',fn(B &$t)=>{$t->id();$t->string('name');$t->string('email')->unique();$t->timestamp('email_verified_at')->nullable();$t->string('password');$t->string('avatar')->nullable();$t->rememberToken();$t->timestamps();});
Schema::create('password_reset_tokens',fn(B &$t)=>{$t->string('email')->primary();$t->string('token');$t->timestamp('created_at')->nullable();});
Schema::create('cache',fn(B &$t)=>{$t->string('key')->primary();$t->mediumText('value');$t->integer('expiration');});
Schema::create('cache_locks',fn(B &$t)=>{$t->string('key')->primary();$t->string('owner');$t->integer('expiration');});
Schema::create('jobs',fn(B &$t)=>{$t->bigIncrements('id');$t->string('queue')->index();$t->longText('payload');$t->unsignedTinyInteger('attempts');$t->unsignedInteger('reserved_at')->nullable();$t->unsignedInteger('available_at');$t->unsignedInteger('created_at');});
Schema::create('failed_jobs',fn(B &$t)=>{$t->id();$t->string('uuid')->unique();$t->text('connection');$t->text('queue');$t->longText('payload');$t->longText('exception');$t->timestamp('failed_at')->useCurrent();});
Schema::create('notifications',fn(B &$t)=>{$t->uuid('id')->primary();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('type');$t->string('notifiable_type');$t->unsignedBigInteger('notifiable_id');$t->text('data');$t->timestamp('read_at')->nullable();$t->timestamps();$t->index(['notifiable_type','notifiable_id']);});
}
public function down():void{Schema::dropIfExists('notifications');Schema::dropIfExists('failed_jobs');Schema::dropIfExists('jobs');Schema::dropIfExists('cache_locks');Schema::dropIfExists('cache');Schema::dropIfExists('password_reset_tokens');Schema::dropIfExists('users');}
};
