<?php
// includes/helpers/validate.php

function get_int_query($key, $default = null)
{
    if (!isset($_GET[$key]) || $_GET[$key] === '') {
        return $default;
    }
    return filter_var($_GET[$key], FILTER_VALIDATE_INT, [
        'options' => ['default' => $default]
    ]);
}

function get_string_query($key, $default = '')
{
    if (!isset($_GET[$key])) {
        return $default;
    }
    return trim((string)$_GET[$key]);
}

function get_pagination($defaultPage = 1, $defaultLimit = 20, $maxLimit = 100)
{
    $page = get_int_query('page', $defaultPage);
    $limit = get_int_query('limit', $defaultLimit);

    if ($page === null || $page < 1) {
        $page = $defaultPage;
    }

    if ($limit === null || $limit < 1) {
        $limit = $defaultLimit;
    }

    if ($limit > $maxLimit) {
        $limit = $maxLimit;
    }

    $offset = ($page - 1) * $limit;

    return [$page, $limit, $offset];
}
?>