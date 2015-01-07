<?php

namespace Nebiros\PhpUtils\Form\Element;

interface FormElementInterface
{
    public function __construct($name, Array $options = null);
    public function build();
}