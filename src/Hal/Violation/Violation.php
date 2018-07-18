<?php
namespace Hal\Violation;


use Hal\Application\Config\Config;
use Hal\Metric\Metric;

interface Violation
{
    const INFO = 0;
    const WARNING = 1;
    const ERROR = 2;
    const CRITICAL = 3;

    /**
     * @return string
     */
    public function getName();

    /**
     * @param Metric $metric
     */
    public function apply(Metric $metric, Config $config);

    /**
     * @return integer
     */
    public function getLevel();

    /**
     * @return string
     */
    public function getDescription();
}
