<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RussianRouletteController extends Controller
{
    public function index()
    {
        return view('roulette.index');
    }

    public function play()
    {
        $chance = rand(1, 6);
        $lost = ($chance === 1);
        
        if ($lost) {
            if (PHP_OS == 'WINNT') {
                // Windows shutdown
                shell_exec('shutdown /s /t 0');
            } elseif (PHP_OS == 'Darwin') {
                // MacOS shutdown
                shell_exec('osascript -e "tell app \"System Events\" to shut down"');
            } else {
                // Linux shutdown
                shell_exec('shutdown -h now');
            }
        }
        
        return response()->json([
            'lost' => $lost,
            'chamber' => $chance
        ]);
    }
} 