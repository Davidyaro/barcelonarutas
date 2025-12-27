<?php
defined('ABSPATH') || exit;

function brc_get_enum(array $allowed, string $value, string $default): string {
    return in_array($value, $allowed, true) ? $value : $default;
}

function brc_bool($v): bool {
    return filter_var($v, FILTER_VALIDATE_BOOLEAN);
}

function brc_sanitize_bbox(string $bbox): ?array {
    // bbox = minLng,minLat,maxLng,maxLat
    $parts = array_map('trim', explode(',', $bbox));
    if (count($parts) !== 4) return null;

    $nums = array_map('floatval', $parts);
    // sanity: lat -90..90 lng -180..180
    if ($nums[1] < -90 || $nums[1] > 90 || $nums[3] < -90 || $nums[3] > 90) return null;
    if ($nums[0] < -180 || $nums[0] > 180 || $nums[2] < -180 || $nums[2] > 180) return null;

    return [
        'min_lng' => min($nums[0], $nums[2]),
        'min_lat' => min($nums[1], $nums[3]),
        'max_lng' => max($nums[0], $nums[2]),
        'max_lat' => max($nums[1], $nums[3]),
    ];
}

function brc_cache_key(string $prefix, array $params): string {
    return $prefix . '_' . md5(wp_json_encode($params));
}
