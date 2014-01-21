<?php
if (!isset($argv[1])) {
    $date = explode('-', date('Y-m-d'));
} else {
    $date = explode('-', date('Y-m-d', strtotime($argv[1])));
}
if (count($date)!=3) die('Please provide a valid date');
foreach(file('/var/www/irclogs/'.$date[0].'/'.$date[1].'/'.$date[2].'/EFNet-#asm.log') as $line) {
    if (preg_match('#<@SHODAN> Result: finished to .*gif#i', $line)) {
        $parts = explode(' ', trim($line));
        echo file_get_contents('http://compo.nazar.so/index/insert/file/'.base64_encode(end($parts))),"\n";
    }
}
