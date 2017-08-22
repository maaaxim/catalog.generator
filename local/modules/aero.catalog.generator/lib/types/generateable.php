<?php
namespace Aero\Types;

interface Generateable
{
    function __construct();
    function generate();
}