<?php

namespace Solital\Core\Schedule;

interface ScheduleInterface
{
    public function handle(): mixed;
}