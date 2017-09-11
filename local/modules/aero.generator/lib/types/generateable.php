<?php
namespace Aero\Generator\Types;

interface Generateable
{
    public function generate();
    public function getStepSize();
}