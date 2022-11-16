<?php

use Slim\Views\PhpRenderer;

class homecontroller
{
    protected $container;
    public function __construct()
    {
        global $container;
        $this->container = $container;
    }

    public function home($request, $response, $args)
    {
        global $container;
        $container->logging = "home";
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/frontend/home/home.html');
    }
    public function get_online_apply_school_drawPic($request, $response, $args)
    {
        global $container;
        $container->logging = "get_online_apply_school_drawPic";
        $admin = new admin($this->container);
        session_start();
        $admin->drawPic();
        session_write_close();
        $response = $response->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Type', 'image/png')
            ->withHeader('Content-Disposition', 'attachment;filename="' . "drawPic.png" . '"')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public');
        return $response;
    }

    public function get_city_area_class_school($request, $response, $args)
    {
        global $container;
        $container->logging = "island申請書下拉選單";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [];
        $result['city'] = $admin->getCitySite($data);
        $result['area'] = $admin->getArea($data);
        $result['level'] = $admin->getLevel($data);
        $result['school'] = $admin->getSchool($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getSchoolID($request, $response, $args)
    {
        global $container;
        $container->logging = "getSchoolID";
        $home = new home($this->container);
        $data = $request->getQueryParams();
        $result = $home->getSchoolID($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_student_rank($request, $response, $args)
    {
        global $container;
        $container->logging = "get_learn_student_rank";
        $home = new home($this->container);
        $data = $request->getQueryParams();
        $data['Pid'] = $_SESSION['Pid'];
        $result = $home->get_learn_student_rank($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_group_rank($request, $response, $args)
    {
        global $container;
        $container->logging = "get_learn_group_rank";
        $home = new home($this->container);
        $data = $request->getQueryParams();
        $data['Pid'] = $_SESSION['Pid'];
        $data['GroupId'] = $home->get_learn_group_id($data);
        $result = $home->get_learn_group_rank($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_student_today_rank($request, $response, $args)
    {
        global $container;
        $container->logging = "get_learn_student_today_rank";
        $home = new home($this->container);
        $data = $request->getQueryParams();
        $data['Pid'] = $_SESSION['Pid'];
        $result = $home->get_learn_student_today_rank($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_group_today_rank($request, $response, $args)
    {
        global $container;
        $container->logging = "get_learn_group_today_rank";
        $home = new home($this->container);
        $data = $request->getQueryParams();
        $data['Pid'] = $_SESSION['Pid'];
        $data['GroupId'] = $home->get_learn_group_id($data);
        $result = $home->get_learn_group_today_rank($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function upload_apply_file_island($request, $response, $args)
    {
        global $container;
        $container->logging = "上傳檔案";

        global $container;
        $admin = new admin($container->db);
        $data = $request->getParams();
        $data['files'] = $request->getUploadedFiles();

        //上傳檔案
        $files = $admin->upload_file($data);
        $file = '';
        if ($files['status'] === 'failed') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($files);
            return $files;
        }

        $result = [
            'ApplyFiles' => $files['file_name']
        ];

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_online_apply_school($request, $response, $args)
    {
        global $container;
        $container->logging = "post_online_apply_school";
        $home = new home($this->container);
        $admin = new admin($this->container);
        $data = $request->getParsedBody();

        $verify = $data["verification"];
        $data["check_school"] = $home->getSchoolID($data);
        unset($data["verification"]);
        $verify_result = $admin->checkCode($verify);
        if ($verify_result['result'] != "success") {
            $result = [
                "status" => "failed",
                "message" => "驗證碼錯誤"
            ];
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        }
        $result = $home->post_online_apply_school($data);
        if ($result['status'] === 'success') {
            $admin = new admin($this->container);
            $pdf = $admin->apply_pdf($data);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_set_password($request, $response, $args)
    {
        global $container;
        $container->logging = "post_set_password";
        $home = new home($this->container);
        $data = $request->getParsedBody();

        if ($data['passwd'] === $data['passwd_again']) {
            unset($data['passwd_again']);
            $admin = new admin($this->container);
            $check_passwd = $admin->check_password($data);
            if ($check_passwd["status"] === "failure") {
                // $response = $response->withStatus(500);
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($check_passwd);
                return $response;
            }
            $result = $home->post_set_password($data);
        } else {
            $result = [
                "status" => 'failure',
                "message" => '確認密碼請與新密碼一致!'
            ];
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_online_apply_school_city_list($request, $response, $args)
    {
        global $container;
        $container->logging = "get_online_apply_school_city_list";
        $home = new home($this->container);
        $data = $request->getQueryParams();
        $result = $home->get_online_apply_school_city_list($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_online_apply_school_area_list($request, $response, $args)
    {
        global $container;
        $container->logging = "get_online_apply_school_area_list";
        $home = new home($this->container);
        $data = $request->getQueryParams();
        $result = $home->get_online_apply_school_area_list($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_online_apply_school_school_list($request, $response, $args)
    {
        global $container;
        $container->logging = "get_online_apply_school_school_list";
        $home = new home($this->container);
        $data = $request->getQueryParams();
        $result = $home->get_online_apply_school_school_list($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_user_role($request, $response, $args)
    {
        global $container;
        $container->logging = "get_user_role";
        $home = new home($this->container);
        $data = $request->getQueryParams();
        $data += $_SESSION;
        $result = $home->get_user_role($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function set_user_role($request, $response, $args)
    {
        global $container;
        $container->logging = "set_user_role";
        $home = new home($this->container);
        $data = $request->getParsedBody();
        $data += $_SESSION;
        $result = $home->set_user_role($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getNewList($request, $response, $args)
    {
        global $container;
        $container->logging = "訊息列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getNewList($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getNewList_island($request, $response, $args)
    {
        global $container;
        $container->logging = "Learn 訊息列表";

        $data = $request->getQueryParams();
        $home = new home($this->container);
        if (!is_null($data['Nid'])) {
            $result = $home->patch_island_news_click($data);
        }
        $result = $home->getNewList_island($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_word_learned($request, $response, $args)
    {
        global $container;
        $container->logging = "首頁 生字布告欄";

        $data = $request->getQueryParams();
        $home = new home($this->container);
        $result = $home->get_learn_word_learned($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
   
    public function get_learn_word_learned_count($request, $response, $args)
    {
        global $container;
        $container->logging = "首頁 生字布告欄總數";

        $data = $request->getQueryParams();
        $home = new home($this->container);
        $result = $home->get_learn_word_learned_count($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
   
    public function get_problem_island($request, $response, $args)
    {
        global $container;
        $container->logging = "常見問題 island";

        $data = $request->getQueryParams();
        $home = new home($this->container);
        $result = $home->get_problem_island($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_problem_count_island($request, $response, $args)
    {
        global $container;
        $container->logging = "常見問題count island";

        $data = $request->getQueryParams();
        $home = new home($this->container);
        $result = $home->get_problem_count_island($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_achievement_island($request, $response, $args)
    {
        global $container;
        $container->logging = "island 歷屆成果列表";

        $data = $request->getQueryParams();
        $home = new home($this->container);
        $result = $home->get_achievement_island($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getNewList_learn($request, $response, $args)
    {
        global $container;
        $container->logging = "Learn 訊息列表";

        $data = $request->getQueryParams();
        $home = new home($this->container);
        if (!is_null($data['Nid'])) {
            $result = $home->patch_learn_news_click($data);
        }
        $result = $home->getNewList_learn($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_online_exam_people($request, $response, $args)
    {
        global $container;
        $container->logging = "線上施測人數";

        $data = $request->getQueryParams();
        $home = new home($this->container);
        $result = $home->get_online_exam_people($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getThreeNewList($request, $response, $args)
    {
        global $container;
        $container->logging = "訊息列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $data['three'] = true;
        $result = $admin->getNewList($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getFiveNewList($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 拿五條訊息列表";

        $data = $request->getQueryParams();
        $home = new home($this->container);
        $data['three'] = true;
        $result = $home->getNewList_island($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_online($request, $response, $args)
    {
        global $container;
        $container->logging = "get_online";
        $result = [
            "online" => count(glob(session_save_path() . '/*'))
        ];
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
}
