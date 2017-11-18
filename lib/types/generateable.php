<?php
namespace Catalog\Generator\Types;

interface Generateable
{
    public function generate();
    public function getCountToGenerate();
}