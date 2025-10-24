<?php

if (!function_exists('formatDuration')) {
    function formatDuration($seconds) {
        $seconds = (int) $seconds;
        $mins = floor($seconds / 60);
        $secs = $seconds % 60;
        return sprintf('%d:%02d', $mins, $secs);
    }
}