<?php

namespace Controller;

use Core\BaseController;

class Error extends BaseController
{
    public function index()
    {
        $this->render("error/404");
    }
}