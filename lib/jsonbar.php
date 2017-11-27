<?php

namespace Catalog\Generator;

use Bitrix\Main\Application;

/**
 * Class JsonBar responsible for showing response status to frontend
 * @package Catalog\Generator
 */
class JsonBar
{
    /**
     * @var array Contains response status info
     */
    protected $response;

    /**
     * @var Steps
     */
    protected $stepsInstance;

    /**
     * JsonBar constructor.
     * @param Steps $stepsInstance
     */
    public function __construct(Steps $stepsInstance)
    {
        $this->stepsInstance = $stepsInstance;
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
     * @internal param int $step Number of steps to advance
     */
    public function advance()
    {
        $this->response["text"]  = "Generating...";
        $this->response["step"] = $this->stepsInstance->getCurrent();
        $this->response["max"] = $this->stepsInstance->getCount();

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
    public function finish()
    {
        $this->response["finished"] = true;
        $this->response["percent"]  = 100;
        $error = $this->stepsInstance->getError();
        $errorMessage = $error->getMessage();
        if(strlen($errorMessage) > 0){
            $this->response["text"]  = $errorMessage;
        } else {
            $this->response["text"]  = "Finished!";
        }
        $this->notify();
    }

    /**
     * Check ajax request
     *
     * @return bool
     */
    public static function isAjax()
    {
        $request = Application::getInstance()->getContext()->getRequest();
        if($request->isAjaxRequest())
            return true;
        else
            return false;
    }

    /**
     * Show json response
     */
    private function notify()
    {
        echo json_encode($this->response);
        die();
    }
}