<?php

use Slim\Views\PhpRenderer;

class studentcontroller
{
    protected $container;
    public function __construct()
    {
        global $container;
        $this->container = $container;
    }
    public function get_student_data($request, $response, $args)
    {
        global $container;
        $container->logging = "鄉鎮市名字";

        $data = $request->getQueryParams();
        $student = new student($this->container);
        $result = $student->get_student_data($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

}
