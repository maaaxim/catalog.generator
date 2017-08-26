<?php
/**
 * Created by PhpStorm.
 * User: maxim
 * Date: 26.08.2017
 * Time: 21:38
 */

namespace Aero\Generator;


class JsonBar
{
    protected $response = [];

    public function __construct($max, $step){
        $this->response = [
            "max"      => $max,
            "step"  => $step,
            "percent"  => false,
            "finished" => false
        ];

        // Calc percent
        if ($max === $step) {
            $this->response["percent"] = false;
        } else {
            $this->response["percent"] = (float) $step / $max * 100;
        }
    }

    /**
     * Advances the progress output X steps.
     *
     * @param int $step Number of steps to advance
     */
    public function advance($step){
        sleep(1);
        $this->response["percent"] += $step;
        $this->notify();
    }

    /**
     * Finishing the process.
     */
    public function finish(){
        $this->response["finished"] = true;
        $this->notify();
    }

    public static function isAjax(){
        if(!(empty($_REQUEST["step"]))) // @TODO wtf
            return true;
        else
            return false;
    }

    private function notify(){
        echo json_encode($this->response);
        die();
    }
}