<?php
namespace Aero\Generator\Types;

interface Generateable
{
    function __construct();
    function generate();
}