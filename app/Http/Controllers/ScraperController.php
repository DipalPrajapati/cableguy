<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScraperController extends Controller
{
    public function reloadRecords(){
        $script_path = app_path() . '/scraper';
        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
            2 => array("file", $script_path . "/logs.txt", "a") // stderr is a file to write to
         );
        $cwd = $script_path . '/';
        $process = proc_open('/usr/bin/python3 ' . $script_path . '/scraper.py', $descriptorspec, $pipes, $cwd);
        $file = file_put_contents($script_path . '/process.txt',$process);
        return redirect() -> back();
    }

    // public function stopScraper() {
    //     proc_terminate($this->process);
    // }   
}
