<?php
/**
 * Interface for transport classes
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

interface Transport
{
    public function check(Environment $environment);
    public function postAndForget(Environment $environment, $url, $payload);
    public function post(Environment $environment, $url, $payload);
}