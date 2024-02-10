<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class ConsoleKernel extends BaseKernel
{
    use MicroKernelTrait;
}