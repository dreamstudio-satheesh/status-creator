<?php

use App\Models\ActivityLog;

if (!function_exists('activity')) {
    function activity(?string $description = null): \App\Models\ActivityLogBuilder|\App\Models\ActivityLog
    {
        if (is_null($description)) {
            return new ActivityLog;
        }

        return ActivityLog::log($description);
    }
}

if (!function_exists('logAdminActivity')) {
    function logAdminActivity(string $description, $subject = null, array $properties = []): ActivityLog
    {
        $activity = activity($description)
            ->useLog('admin')
            ->withProperties($properties);

        if (auth('admin')->check()) {
            $activity->by(auth('admin')->user());
        }

        if ($subject) {
            $activity->on($subject);
        }

        return $activity->log();
    }
}

if (!function_exists('logUserActivity')) {
    function logUserActivity(string $description, $subject = null, array $properties = []): ActivityLog
    {
        $activity = activity($description)
            ->useLog('user')
            ->withProperties($properties);

        if (auth()->check()) {
            $activity->by(auth()->user());
        }

        if ($subject) {
            $activity->on($subject);
        }

        return $activity->log();
    }
}