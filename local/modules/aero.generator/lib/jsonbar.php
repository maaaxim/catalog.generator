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
    protected $response;
    protected $percent;

    public function __construct(){
        $this->response = [
            "max"      => false,
            "step"     => false,
            "percent"  => false,
            "finished" => false,
            "text"     => false
        ];
    }

    /**
     * Advances the progress output X steps.
     *
     * @param Steps $stepsInstance
     * @internal param int $step Number of steps to advance
     */
    public function advance(Steps $stepsInstance){

        $this->response["text"]  = "Generating...";

        $this->response["step"] = $stepsInstance->getCurrent();
        $this->response["max"] = $stepsInstance->getCount();

        if($this->response["step"] == 0)
            $this->finish();

        // Calc percent
        if ($this->response["max"] === $this->response["step"]) {
            $this->response["percent"] = 100;
        } else {
            $this->response["percent"] = ceil($this->response["step"] / $this->response["max"] * 100);
        }

        $this->notify();
    }

    /**
     * Finishing the process.
     */
    public function finish(){
        $this->response["finished"] = true;
        $this->response["percent"]  = 100;
        $this->response["text"]  = "Finished!";
        $this->notify();
    }

    /**
     * Check ajax request
     *
     * @return bool
     */
    public static function isAjax(){
        if($_REQUEST["ajax"] == "y")
            return true;
        else
            return false;
    }

    /**
     * Show json response
     */
    private function notify(){
        echo json_encode($this->response);
        die();
    }
}