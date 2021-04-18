<?php

class Logger 
{
    private $logs = array();

    function __construct() {
        date_default_timezone_set('Asia/Kolkata');   
    }

    private function saveLogs() {
        $base   =  $_SERVER['DOCUMENT_ROOT']."/logs/";
        $fname  = $base . date("d-m-y").".txt";
        if(!is_dir($base)) {
           mkdir($base, 0777, true); 
        }

        $file   = fopen($fname, 'a');
        foreach ($this->logs as $log)
            fwrite($file, $log);
        fclose($file);
        $this->logs = array();
    }

    function addLog($tag, $msg, $type = 'i') {
        $finalMsg = date("d-m H:i:s") . "=>[" . $type . "] ".$tag."::". $msg . "\n";
        if ($type == 'i') {
            array_push($this->logs, $finalMsg);
        }
        else if ($type == 'p' || $type == '+') {
            $finalMsg = "<p><span style=\"color: green;\"><strong>$finalMsg</strong></span></p>";
            array_push($this->logs, $finalMsg);
        }
        else if ($type == 'e' || $type == '-') {
            $finalMsg = "<p><span style=\"color: red;\"><strong>$finalMsg</strong></span></p>";
            array_push($this->logs, $finalMsg);
            $this->saveLogs();
        }
    }


    function __destruct() {
        $this->saveLogs();
    }
}
