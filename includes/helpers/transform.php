<?php
// includes/helpers/transform.php

function normalize_image_url($image)
{
    if ($image === null) {
        return null;
    }

    $image = trim((string) $image);
    if ($image === '') {
        return null;
    }

    if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://') || str_starts_with($image, '/')) {
        return $image;
    }

    return 'uploads/' . $image;
}

function normalize_location_item(array $item)
{
    $item['image_url'] = normalize_image_url($item['image'] ?? null);

    return $item;
}

function normalize_location_list(array $items)
{
    foreach ($items as $index => $item) {
        $items[$index] = normalize_location_item($item);
    }

    return $items;
}

function normalize_route_stops(array $stops)
{
    foreach ($stops as $index => $stop) {
        $stops[$index]['image_url'] = normalize_image_url($stop['image'] ?? null);
    }

    return $stops;
}
?>