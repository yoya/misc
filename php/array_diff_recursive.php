<?php
function array_diff_recursive($a, $b) {
    $result = array();
    foreach ($a as $k => $v) {
        if (array_key_exists($k, $b)) {
            if (is_array($v)) {
                $result[$k] = array_diff_recursive($v, $b[$k]);
            } else {
                if ($v != $b[$k]) {
                    $result[$k] = $v;
                }
            }
        } else {
            $result[$k] = $v;
        }
    }
    return $result;
}
