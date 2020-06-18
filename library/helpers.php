<?php

function fixColors($line) {
    // detect if there's incomplete color code on this line of text
    if (strpos($line, ",") !== false) {
        // get ALL color codes on this line of text
        preg_match_all('/\(\d?),?(\d?)/', $line, $matches);

        $latest_fg = '1';
        foreach ($matches[0] as $key => $match) {
            // check if this color code has a foreground color
            if (preg_match('/\(\d),?(\d?)/', $match)) {
                $latest_fg = substr($match, 1, 1);
            }

            // check if this is a broken color
            if (strpos($match, ",") !== false) {
                // replace the broken color by adding the last foreground color to it
                $bg_color = substr($match, -1);
                $replacement = "$latest_fg,$bg_color";
                $line = preg_replace("/\,(\d)/", $replacement, $line, $limit = 1);
            }
        }
    }
    return $line;
}

function randomColors($text, $bold = false) {
    $color1 = rand(0,30);
    $color2 = rand(0,30);
    while($color1 == $color2) {
        $color2 = rand(0,30);
    }
    $return = "";
    if($bold)
        $return .= "";
    $return .= "$color1,$color2".$text."";

    if($bold) $return .= "";
    return $return;
}

function getBetween($content,$start,$end) {
    $r = explode($start, $content);
    if (isset($r[1])) {
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return '';
}

function rglob($pattern, $flags = 0) {
    $files = glob($pattern, $flags); 
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, rglob($dir.'/'.basename($pattern), $flags));
    }
    return $files;
}

function findFile($file_requested, $glob_pattern) {
    $all_files = rglob($glob_pattern);

    foreach($all_files as $file) {
        if(basename($file, '.txt') == $file_requested || basename($file) == $file_requested) {
            return $file;
        }
    }

    return false;
}