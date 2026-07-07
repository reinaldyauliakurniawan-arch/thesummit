<?php
use Illuminate\Support\Facades\Schedule;
Schedule::command('turns:process-timeout')->everyFiveMinutes();
