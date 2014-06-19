<?php

interface App_Form_Element_FormElementInterface
{
    public function __construct($name, Array $options = null);
    public function build();
}