<?php
session_start();

use Slim\Views\PhpRenderer;

class admincontroller
{
    protected $container;
    public function __construct()
    {
        global $container;
        $this->container = $container;
    }

    public function admin($request, $response, $args)
    {
        global $container;
        $container->logging = "admin";
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/backend/home/home.html');
    }

    public function getAdminTest($request, $response, $args)
    {
        global $container;
        $container->logging = "getAdminTest";
        $admin = new admin($this->container);
        $result = $admin->getAdminTest();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function renderlogin($request, $response, $args)
    {
        global $container;
        $container->logging = "renderlogin";
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/frontend/home/home.html');
    }

    public function get_client_ip()
    {
        global $container;
        $container->logging = "get_client_ip";

        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    public function cityLogin($request, $response, $args)
    {
        global $container;
        $container->logging = "縣市管理人員登入";


        $data = $request->getParsedBody(); //$_post

        if (!isset($_SESSION['login']) || ($_SESSION['login']) == 0) {
            $_SESSION['login'] = 1;
        } else if (time() <  $_SESSION['time_strat']) {
            $result = [
                "status" => "failed",
                "message" => "您需等待15分鐘後方能再試!"
            ];
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        } else if ($_SESSION['login'] === 3) {
            $_SESSION['time_strat'] = time() + 900;
            $result = [
                "status" => "failed",
                "message" => "您已輸入錯誤三次，請於15分鐘後再試(敬請重新整理頁面)。"
            ];
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        } else {
            $_SESSION['login']++;
        }

        $verify = $data["verification"];
        unset($data["verification"]);
        $admin = new admin($this->container);
        $verify_result = $admin->checkCode($verify);
        if ($verify_result['status'] == "success") {
            $result = $admin->cityLogin($data);
        } else {
            $result = [
                "status" => "failed",
                "message" => "驗證碼錯誤"
            ];
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        }

        // $admin = new admin($this->container);
        // $result = $admin->cityLogin($data);

        if (array_key_exists("status", $result)) {
            if ($result["status"] == "failed") {
                $result = [
                    "status" => "failed",
                    "message" => "密碼錯誤"
                ];
                // $response = $response->withStatus(500);
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function check_password($request, $response, $args)
    {
        global $container;
        $container->logging = "縣市管理人員管理碼修改";


        $data = $request->getParsedBody(); //$_post
        $admin = new admin($this->container);
        $check2 = $admin->check_password($data);
        // $response = $response->withStatus(500);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($check2);
        return $response;
    }
    public function patch_city($request, $response, $args)
    {
        global $container;
        $container->logging = "縣市管理人員管理碼修改";


        $data = $request->getParsedBody(); //$_post
        $admin = new admin($this->container);
        $check2 = $admin->check_password($data);
        if ($check2["status"] == "failure") {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($check2);
            return $response;
        }
        $result = $admin->patch_city($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function teacherLogin($request, $response, $args)
    {
        global $container;
        $container->logging = "班級施測人員登入";

        $data = $request->getParsedBody();

        if (!isset($_SESSION['login']) || ($_SESSION['login']) == 0) {
            $_SESSION['login'] = 1;
        } else if (time() <  $_SESSION['time_strat']) {
            $result = [
                "status" => "failed",
                "message" => "您需等待15分鐘後方能再試!"
            ];
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        } else if ($_SESSION['login'] === 3) {
            $_SESSION['time_strat'] = time() + 900;
            $result = [
                "status" => "failed",
                "message" => "您已輸入錯誤三次，請於15分鐘後再試(敬請重新整理頁面)。"
            ];
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        } else {
            $_SESSION['login']++;
        }

        $verify = $data["verification"];
        unset($data["verification"]);
        $admin = new admin($this->container);
        $verify_result = $admin->checkCode($verify);
        if ($verify_result['status'] == "success") {
            $result = $admin->teacherLogin($data);
        } else {
            $result = [
                "status" => "failed",
                "message" => "驗證碼錯誤"
            ];
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        }

        // $admin = new admin($this->container);
        // $result = $admin->teacherLogin($data);

        if (array_key_exists("status", $result)) {
            if ($result["status"] === "failed") {
                $result = [
                    "status" => "failed",
                    "message" => "密碼錯誤"
                ];
                // $response = $response->withStatus(500);
            }
        }


        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function schoolLogin($request, $response, $args)
    {
        global $container;
        $container->logging = "學校登入";

        $data = $request->getParsedBody();

        if (!isset($_SESSION['login']) || ($_SESSION['login']) == 0) {
            $_SESSION['login'] = 1;
        } else if (time() <  $_SESSION['time_strat']) {
            $result = [
                "status" => "failed",
                "message" => "您需等待15分鐘後方能再試!"
            ];
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        } else if ($_SESSION['login'] === 3) {
            $_SESSION['time_strat'] = time() + 900;
            $result = [
                "status" => "failed",
                "message" => "您已輸入錯誤三次，請於15分鐘後再試(敬請重新整理頁面)。"
            ];
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        } else {
            $_SESSION['login']++;
        }

        $verify = $data["verification"];
        unset($data["verification"]);
        $admin = new admin($this->container);
        $verify_result = $admin->checkCode($verify);
        if ($verify_result['status'] == "success") {
            $result = $admin->schoolLogin($data);
        } else {
            $result = [
                "status" => "failed",
                "message" => "驗證碼錯誤"
            ];
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        }

        // $admin = new admin($this->container);
        // $result = $admin->schoolLogin($data);

        if (array_key_exists("message", $result)) {
            if ($result["message"] === "failed") {
                $result = [
                    "status" => "failed",
                    "message" => "貴校尚未通過申請，請耐心等候並注意申請信箱!"
                ];
                // $response = $response->withStatus(500);
            }
        }
        if (array_key_exists("status", $result)) {
            if ($result["status"] === "failed") {
                $result = [
                    "status" => "failed",
                    "message" => "密碼錯誤"
                ];
                // $response = $response->withStatus(500);
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function studentLogin($request, $response, $args)
    {
        global $container;
        $container->logging = "學生識字量查詢";

        $data = $request->getParsedBody();

        if (!isset($_SESSION['login']) || ($_SESSION['login']) == 0) {
            $_SESSION['login'] = 1;
        } else if (time() <  $_SESSION['time_strat']) {
            $result = [
                "status" => "failed",
                "message" => "您需等待15分鐘後方能再試!"
            ];
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        } else if ($_SESSION['login'] === 3) {
            $_SESSION['time_strat'] = time() + 900;
            $result = [
                "status" => "failed",
                "message" => "您已輸入錯誤三次，請於15分鐘後再試(敬請重新整理頁面)。"
            ];
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        } else {
            $_SESSION['login']++;
        }

        $verify = $data["verification"];
        unset($data["verification"]);
        $admin = new admin($this->container);
        $verify_result = $admin->checkCode($verify);
        if ($verify_result['status'] == "success") {

            // $admin = new admin($this->container);
            if (strlen($data['birth']) > 6) {
                $birth1 = str_split($data['birth'], 3);
                $data['birth1'] = intval($birth1[0]);
                $data['birth1'] += 1911;
                $data['birth1'] = strval($data['birth1']);
                $birth2 = substr($data['birth'], -4);
                $birth3 = str_split($birth2, 2);
                $data['birth2'] = $birth3[0];
                $data['birth3'] = $birth3[1];
                $data['birth'] = "{$data['birth1']}/{$data['birth2']}/{$data['birth3']}";
            } else {
                $birth = str_split($data['birth'], 2);
                $data['birth1'] = intval($birth[0]);
                $data['birth1'] += 1911;
                $data['birth1'] = strval($data['birth1']);
                $data['birth2'] = $birth[1];
                $data['birth3'] = $birth[2];
                $data['birth'] = "{$data['birth1']}/{$data['birth2']}/{$data['birth3']}";
            }
            unset($data['birth1']);
            unset($data['birth2']);
            unset($data['birth3']);

            $result = $admin->studentLogin($data);

            if (array_key_exists("status", $result)) {
                if ($result["status"] === "failed") {
                    $result = [
                        "status" => "failed",
                        "message" => "密碼錯誤"
                    ];
                    // $response = $response->withStatus(500);
                }
            }
        } else {
            $result = [
                "status" => "failed",
                "message" => "驗證碼錯誤"
            ];
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function adminLogin($request, $response, $args)
    {
        global $container;
        $container->logging = "管理者管理人員登入";


        $data = $request->getParsedBody(); //$_post

        if (!isset($_SESSION['login']) || ($_SESSION['login']) == 0) {
            $_SESSION['login'] = 1;
        } else if (time() <  $_SESSION['time_strat']) {
            $result = [
                "status" => "failed",
                "message" => "您需等待15分鐘後方能再試!"
            ];
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        } else if ($_SESSION['login'] === 3) {
            $_SESSION['time_strat'] = time() + 900;
            $result = [
                "status" => "failed",
                "message" => "您已輸入錯誤三次，請於15分鐘後再試(敬請重新整理頁面)。"
            ];
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        } else {
            $_SESSION['login']++;
        }

        $verify = $data["verification"];
        unset($data["verification"]);
        $admin = new admin($this->container);
        $verify_result = $admin->checkCode($verify);

        if ($verify_result['status'] == "success") {
            $result = $admin->adminLogin($data);
        } else {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($verify_result);
            return $response;
        }

        // $admin = new admin($this->container);
        // $result = $admin->adminLogin($data);

        if (array_key_exists("status", $result)) {
            if ($result["status"] === "failed") {
                $result = [
                    "status" => "failed",
                    "message" => "密碼錯誤"
                ];
                // $response = $response->withStatus(500);
            }
        }


        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_login_dropdown($request, $response, $args)
    {
        global $container;
        $container->logging = "登入頁面下拉選單";

        // $data = $request->getParsedBody();
        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [];
        $result['City'] = $admin->getCitySite($data);
        $result['Area'] = $admin->getArea($data);
        $result['Level'] = $admin->getLevel($data);
        $result['school'] = $admin->getSchool($data);
        $result['Grade'] = $admin->getGrade($data);
        $result['Class'] = $admin->getClass($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_dropdown($request, $response, $args)
    {
        global $container;
        $container->logging = "Learn 學習生字量報表 下拉選單";

        // $data = $request->getParsedBody();
        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [];
        $result['City'] = $admin->getCitySite($data);
        $result['Area'] = $admin->getArea($data);
        $result['school'] = $admin->getSchool($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getTeaData($request, $response, $args)
    {
        global $container;
        $container->logging = "班級老師列表";

        $data['sid'] = $_SESSION['Sid'];    #  11 $_SESSION['Sid']
        $admin = new admin($this->container);
        $result = $admin->getTeaData($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_page_identify($request, $response, $args)
    {
        global $container;
        $container->logging = "身分回傳";

        $result = [];
        $admin = new admin($this->container);
        if (array_key_exists('Sid', $_SESSION)) {
            $data = ['sid' => $_SESSION['Sid']];    #  11 $_SESSION['Sid']
            $result['master'] = $admin->get_page_identify($data);
        }
        if (array_key_exists('Tid', $_SESSION)) {
            $data = ['tid' => $_SESSION['Tid']];    #  11 $_SESSION['Sid']
            $result['exam'] = $admin->get_page_identify($data);
        }
        if (array_key_exists('Uid', $_SESSION)) {
            $data = ['uid' => $_SESSION['Uid']];    #  11 $_SESSION['Sid']
            $result['admin'] = $admin->get_page_identify($data);
        }
        if (array_key_exists('Pid', $_SESSION)) {
            $data = ['pid' => $_SESSION['Pid']];    #  11 $_SESSION['Sid']
            $result['student'] = $admin->get_page_identify($data);
        }
        if (array_key_exists('CityName', $_SESSION)) {
            $data = ['cityname' => $_SESSION['CityName']];  # '宜蘭縣'
            $result['city'] = $admin->get_page_identify($data);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function logout_identify($request, $response, $args)
    {
        global $container;
        $container->logging = "登出";

        $admin = new admin($this->container);

        if (array_key_exists('Sid', $_SESSION)) {
            $data['Sid'] = $_SESSION['Sid']; #  18054  $_SESSION['Tid']
            $result = $admin->logout_identify($data);
        }
        if (array_key_exists('Tid', $_SESSION)) {
            $data['Tid'] = $_SESSION['Tid']; #  18054  $_SESSION['Tid']
            $result = $admin->logout_identify($data);
        }
        if (array_key_exists('Uid', $_SESSION)) {
            $data['Uid'] = $_SESSION['Uid']; #  18054  $_SESSION['Tid']
            $result = $admin->logout_identify($data);
        }
        if (array_key_exists('Pid', $_SESSION)) {
            $data['Pid'] = $_SESSION['Pid']; #  18054  $_SESSION['Tid']
            $result = $admin->logout_identify($data);
        }
        if (array_key_exists('CityName', $_SESSION)) {
            $data['CityName'] = $_SESSION['CityName']; #  18054  $_SESSION['Tid']
            $result = $admin->logout_identify($data);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getStuData($request, $response, $args)
    {
        global $container;
        $container->logging = "班級學生列表";

        $data['tid'] = $_SESSION['Tid']; #  18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $result = $admin->getStuData($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_single_student_data($request, $response, $args)
    {
        global $container;
        $container->logging = "班級學生列表";

        $data = $request->getQueryParams();
        $data['tid'] = $_SESSION['Tid']; #  23153  $_SESSION['Tid']
        $admin = new admin($this->container);
        $result = $admin->get_single_student_data($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_teacher_student_data($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 修改學生基本資料";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);

        $result = $admin->patch_teacher_student_data($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function transfer_school_teacher_student($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 轉出學生";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        foreach ($data as $key => $value) {
            $result = $admin->transfer_school_teacher_student($value);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_single_student_transfer($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 修改學生基本資料";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        foreach ($data as $key => $value) {
            $result = $admin->patch_single_student_transfer($value);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_single_student_data($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 修改學生基本資料";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->patch_single_student_data($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_school_city_area($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 拿學校名字";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_school_city_area($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getScore($request, $response, $args)
    {
        global $container;
        $container->logging = "班級學生成績列表";

        $data['tid'] = $_SESSION['Tid']; #18054 $_SESSION['Tid']
        $admin = new admin($this->container);
        $result = $admin->getScore($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_exam_report_score_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "班上學生成績報表excel";

        $data = $request->getQueryParams();
        $data['tid'] = $_SESSION['Tid']; #18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->getScore($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }
    public function get_city_examreport_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "班上學生成績報表excel";

        $data = $request->getQueryParams();
        $data['tid'] = $_SESSION['Tid']; #18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->getCityExamReport($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function searchScore($request, $response, $args)
    {
        global $container;
        $container->logging = "搜尋班級學生成績列表";

        $data = $request->getQueryParams();
        $data['tid'] = $_SESSION['Tid']; #18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $result = $admin->searchScore($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_exam_report_search_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "搜尋班級學生成績列表結果excel";

        $data = $request->getQueryParams();
        $data['tid'] = $_SESSION['Tid']; #18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->searchScore($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function getStuReport($request, $response, $args)
    {
        global $container;
        $container->logging = "全校學生成績列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getStuReport($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getStuReportView($request, $response, $args)
    {
        global $container;
        $container->logging = "老師查看學生成績紀錄";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getStuReportView($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_exam_report_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "識字施測結果報表excel";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->getStuReportView($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }


    public function get_certificate_choose($request, $response, $args)
    {
        global $container;
        $container->logging = "列印獎狀一筆";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_certificate_choose($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_certificate($request, $response, $args)
    {
        global $container;
        $container->logging = "列印獎狀多筆";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_certificate($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getFormKind($request, $response, $args)
    {
        global $container;
        $container->logging = "全校學生成績列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getFormKind($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getCitySite($request, $response, $args)
    {
        global $container;
        $container->logging = "縣市名字";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getCitySite($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getArea($request, $response, $args)
    {
        global $container;
        $container->logging = "鄉鎮市名字";

        $data = $request->getQueryParams();    #  "高雄市"
        $admin = new admin($this->container);
        $result = $admin->getArea($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getLevel($request, $response, $args)
    {
        global $container;
        $container->logging = "學校等級";

        $data = $request->getQueryParams();    #  "高雄市"
        $admin = new admin($this->container);
        $result = $admin->getLevel($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getSchool($request, $response, $args)
    {
        global $container;
        $container->logging = "學校名字";

        $data['cityid'] = $_SESSION['CityName'];  #  $_SESSION['CityName']
        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getSchool($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getSchoolData($request, $response, $args)
    {
        global $container;
        $container->logging = "學校基本資料";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [];
        $result['name'] = $admin->getSchoolDataName($data);
        $result['school'] = $admin->getSchoolDataSchool($data);
        $result['exam'] = $admin->getSchoolDataExam($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_stuednt_transfer($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 轉移學生基本資料";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_stuednt_transfer($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_stuednt_transfer_count($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 轉移學生基本資料";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_stuednt_transfer_count($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_stuednt_transfer($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 刪除轉移學生基本資料";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->delete_stuednt_transfer($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_stuednt_transfers($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 刪除轉移學生基本資料";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        foreach ($data as $key => $value) {
            $result = $admin->delete_stuednt_transfer($data);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_learn_master_stuednt($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 刪除學生 基本資料";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        foreach ($data as $key => $value) {
            $result = $admin->delete_stuednt_transfer($value);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_stuednt_transfer($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 轉移學生基本資料";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        foreach ($data as $key => $value) {
            $result = $admin->patch_stuednt_transfer($value);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getExamDate($request, $response, $args)
    {
        global $container;
        $container->logging = "上下學期";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getExamDate($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getSemster($request, $response, $args)
    {
        global $container;
        $container->logging = "期初期末";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getSemster($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getYear($request, $response, $args)
    {
        global $container;
        $container->logging = "年分";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getYear($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getClass($request, $response, $args)
    {
        global $container;
        $container->logging = "班級種類";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getClass($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_new_class($request, $response, $args)
    {
        global $container;
        $container->logging = "班級種類";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_new_class($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getGrade($request, $response, $args)
    {
        global $container;
        $container->logging = "年級";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getGrade($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_school_dropdown($request, $response, $args)
    {
        global $container;
        $container->logging = "下拉選單集合";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [];
        $result['city'] = $admin->getCitySite($data);
        $result['area'] = $admin->getArea($data);
        $result['school'] = $admin->getSchool($data);
        $result['grade_class_teacher'] = $admin->get_new_class($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_learn_task_dropdown($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 學習任務 下拉選單";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [];
        $result['city'] = $admin->getCitySite($data);
        $result['area'] = $admin->getArea($data);
        $result['school'] = $admin->getSchool($data);
        $result['grade'] = $admin->getGrade($data);
        $result['class'] = $admin->getClass($data);
        $result['year'] = $admin->getYear($data);
        $result['smester'] = $admin->getSemster($data);
        $result['term'] = $admin->getTerm($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_admin_tea_export_student_sample_data($request, $response, $args) //Geting the excel data of teacher data for uploading.
    {
        global $container;
        $container->logging = "admin學生資料批次上傳匯入檔案";

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['inputFile'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFile->file);
            $worksheet = $spreadsheet->getActiveSheet();
            // Get the highest row number and column letter referenced in the worksheet
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            // Increment the highest column letter
            $highestColumn++;
            $data = [];
            for ($row = 2; $row <= $highestRow; ++$row) {
                $tmp = [];
                for ($col = 'A'; $col != $highestColumn; ++$col) {
                    // $tmp[] = strval($worksheet->getCell($col . $row)->getValue());
                    $tmp["year"] = trim(strval($worksheet->getCell('A' . $row)->getValue()));
                    $tmp["stunum"] = trim(strval($worksheet->getCell('B' . $row)->getValue()));
                    $tmp["stuname"] = trim(strval($worksheet->getCell('C' . $row)->getValue()));
                    $tmp["idnumber"] = trim(strval($worksheet->getCell('D' . $row)->getValue()));
                    $tmp["seatnum"] = trim(strval($worksheet->getCell('E' . $row)->getValue()));
                    $tmp["sex"] = trim(strval($worksheet->getCell('F' . $row)->getValue()));
                    $tmp["birth"] = trim(strval($worksheet->getCell('G' . $row)->getValue()));
                }
                $data[] = $tmp;
            }
            $result = $data;
        } else {
            $result = array(
                "status" => "failure"
            );
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_admin_transfer_export_student_sample_data($request, $response, $args) //Geting the excel data of teacher data for uploading.
    {
        global $container;
        $container->logging = "admin學生資料批次上傳匯入檔案";

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['inputFile'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFile->file);
            $worksheet = $spreadsheet->getActiveSheet();
            // Get the highest row number and column letter referenced in the worksheet
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            // Increment the highest column letter
            $highestColumn++;
            $data = [];
            for ($row = 2; $row <= $highestRow; ++$row) {
                $tmp = [];
                for ($col = 'A'; $col != $highestColumn; ++$col) {
                    // $tmp[] = strval($worksheet->getCell($col . $row)->getValue());
                    $tmp["year"] = trim(strval($worksheet->getCell('A' . $row)->getValue()));
                    $tmp["pre_grade"] = trim(strval($worksheet->getCell('B' . $row)->getValue()));
                    $tmp["pre_class"] = trim(strval($worksheet->getCell('C' . $row)->getValue()));
                    $tmp["stuname"] = trim(strval($worksheet->getCell('D' . $row)->getValue()));
                    $tmp["grade"] = trim(strval($worksheet->getCell('E' . $row)->getValue()));
                    $tmp["class"] = trim(strval($worksheet->getCell('F' . $row)->getValue()));
                    $tmp["seatnum"] = trim(strval($worksheet->getCell('G' . $row)->getValue()));
                }
                $data[] = $tmp;
            }
            $result = $data;
        } else {
            $result = array(
                "status" => "failure"
            );
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_export_word_library_sample_data($request, $response, $args) //Geting the excel data of teacher data for uploading.
    {
        global $container;
        $container->logging = "learn 批次上傳生字檔案";

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['inputFile'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFile->file);
            $worksheet = $spreadsheet->getActiveSheet();
            // Get the highest row number and column letter referenced in the worksheet
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            // Increment the highest column letter
            $highestColumn++;
            $data = [];
            for ($row = 2; $row <= $highestRow; ++$row) {
                $tmp = [];
                for ($col = 'A'; $col != $highestColumn; ++$col) {
                    $tmp["word"] = trim(strval($worksheet->getCell('A' . $row)->getValue()));
                    $tmp["bopomo"] = trim(strval($worksheet->getCell('B' . $row)->getValue()));
                    $tmp["romanization"] = trim(strval($worksheet->getCell('C' . $row)->getValue()));
                    $tmp["part"] = trim(strval($worksheet->getCell('D' . $row)->getValue()));
                    $tmp["part_stroke"] = trim(strval($worksheet->getCell('E' . $row)->getValue()));
                    $tmp["stroke"] = trim(strval($worksheet->getCell('F' . $row)->getValue()));
                    $tmp["stroke_num"] = trim(strval($worksheet->getCell('G' . $row)->getValue()));
                    $tmp["choose"] = trim(strval($worksheet->getCell('H' . $row)->getValue()));
                    $tmp["ans1"] = trim(strval($worksheet->getCell('I' . $row)->getValue()));
                    $tmp["ans2"] = trim(strval($worksheet->getCell('J' . $row)->getValue()));
                    $tmp["ans3"] = trim(strval($worksheet->getCell('K' . $row)->getValue()));
                    $tmp["sentence1"] = trim(strval($worksheet->getCell('L' . $row)->getValue()));
                    $tmp["sentence2"] = trim(strval($worksheet->getCell('M' . $row)->getValue()));
                    $tmp["sentence3"] = trim(strval($worksheet->getCell('N' . $row)->getValue()));
                }
                $data[] = $tmp;
            }
            $result = $data;
        } else {
            $result = array(
                "status" => "fail"
            );
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_import_word_library_data($request, $response, $args)
    {
        global $container;
        $container->logging = "批次新增生字查詢";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        foreach ($data as $key => $value) {
            $result = $admin->post_word_excel($value);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_export_version_library_sample_data($request, $response, $args) //Geting the excel data of teacher data for uploading.
    {
        global $container;
        $container->logging = "learn 批次上傳生字檔案";

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['inputFile'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFile->file);
            $worksheet = $spreadsheet->getActiveSheet();
            // Get the highest row number and column letter referenced in the worksheet
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            // Increment the highest column letter
            $highestColumn++;
            $data = [];
            for ($row = 2; $row <= $highestRow; ++$row) {
                $tmp = [];
                for ($col = 'A'; $col != $highestColumn; ++$col) {
                    $tmp["Version"] = trim(strval($worksheet->getCell('A' . $row)->getValue()));
                    $tmp["Year"] = trim(strval($worksheet->getCell('B' . $row)->getValue()));
                    $tmp["Term"] = trim(strval($worksheet->getCell('C' . $row)->getValue()));
                    $tmp["Grade"] = trim(strval($worksheet->getCell('D' . $row)->getValue()));
                    $tmp["Lesson"] = trim(strval($worksheet->getCell('E' . $row)->getValue()));
                    $tmp["LessonName"] = trim(strval($worksheet->getCell('F' . $row)->getValue()));
                    $tmp["wordlist"] = trim(strval($worksheet->getCell('G' . $row)->getValue()));
                }
                $data[] = $tmp;
            }
            $result = $data;
        } else {
            $result = array(
                "status" => "fail"
            );
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_import_version_library_data($request, $response, $args)
    {
        global $container;
        $container->logging = "批次新增版本生字";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        foreach ($data as $key => $value) {
            $result = $admin->post_version_word_excel($value);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_admin_import_student_data($request, $response, $args)
    {
        global $container;
        $container->logging = "admin批次新增學生";

        $data = $request->getParsedBody();
        $exam = new exam($this->container);
        $admin = new admin($this->container);
        // $max_pid = $exam->max_Pid($data);

        foreach ($data as $key => $value) {
            $data[$key]['pre_seatnum'] =  $data[$key]['seatnum'];
        }

        $stunum_check = $exam->check_student_stunum($data);
        $birth_check = $exam->check_student_id($data);
        if ($stunum_check["status"] === "failure") {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($stunum_check);
            return $response;
        } else if ($birth_check["status"] === "failure") {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($birth_check);
            return $response;
        } else {
            $result = $admin->post_admin_import_student_data($data);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        }
    }

    public function getSchoolclassAmount($request, $response, $args)
    {
        global $container;
        $container->logging = "各年級施測統計報表";

        $data = $request->getQueryParams();
        $data['exam_sid'] = $_SESSION['Sid'];  # 317  $_SESSION['Sid']
        $admin = new admin($this->container);
        $result = $admin->getSchoolclassAmount($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getSchoolAmountReport($request, $response, $args)
    {
        global $container;
        $container->logging = "校內分年級施測統計報表";

        $data = $request->getQueryParams();
        $data['exam_sid'] = $_SESSION['Sid'];  # 317  $_SESSION['Sid']
        $admin = new admin($this->container);
        $result = $admin->getSchoolAmountReport($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getSchoolStuAmountReport($request, $response, $args)
    {
        global $container;
        $container->logging = "校內施測統計報表";

        $data = $request->getQueryParams();
        $data['exam_sid'] = $_SESSION['Sid'];  # 317  $_SESSION['Sid']
        $admin = new admin($this->container);
        $result = [];
        $result['report'] = $admin->getSchoolAmountReport($data);
        $result['grade_amount'] = $admin->getSchoolclassAmount($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_school_stu_amount_report($request, $response, $args)
    {
        global $container;
        $container->logging = "班上學生成績報表excel";

        $data = $request->getQueryParams();
        $data['exam_sid'] = $_SESSION['Sid'];  # 11  $_SESSION['Sid']
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->getSchoolAmountReport($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function getClassAmount($request, $response, $args)
    {
        global $container;
        $container->logging = "各班施測統計報表";

        $data = $request->getQueryParams();
        $data['exam_sid'] = $_SESSION['Sid'];  # 71  $_SESSION['Sid']
        $admin = new admin($this->container);
        $result = $admin->getClassGender($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_exam_report3($request, $response, $args)
    {
        global $container;
        $container->logging = "各班施測統計報表";

        $data = $request->getQueryParams();
        $data['sid'] = $_SESSION['Sid'];  # 11  $_SESSION['Sid']
        $data['tid'] = $_SESSION['Tid'];  # 11  $_SESSION['Sid']
        $admin = new admin($this->container);
        $result = $admin->get_exam_report3($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_exam_report3_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "班上學生總數報表excel";

        $data = $request->getQueryParams();
        $data['sid'] = $_SESSION['Sid'];  # 11  $_SESSION['Sid']
        $data['tid'] = $_SESSION['Tid'];  # 18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->get_exam_report3($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }
    public function getClassAmountExcel($request, $response, $args)
    {
        global $container;
        $container->logging = "班上學生總數報表excel";

        $data = $request->getQueryParams();
        $data['exam_sid'] = $_SESSION['Sid'];  # 11  $_SESSION['Sid']
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->getClassGender($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function getSingleClass($request, $response, $args)
    {
        global $container;
        $container->logging = "各班級老師名字";

        $data = $request->getQueryParams();
        $data['tid'] = $_SESSION['Tid'];  # 18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $result = $admin->getSingleClass($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getSingleClassAmount($request, $response, $args)
    {
        global $container;
        $container->logging = "班上線上識字量測驗報表";

        $data = $request->getQueryParams();
        $data['tid'] = $_SESSION['Tid'];  # 18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $result = $admin->getSingleClassAmount($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_admin_school_teacher_msg($request, $response, $args)
    {
        global $container;
        $container->logging = "校方修改老師資訊";

        $data = $request->getParsedBody();

        $admin = new admin($this->container);
        $check2 = $admin->check_password($data);

        if ($check2["status"] == "failure") {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($check2);
            return $response;
        }

        $result = $admin->patch_admin_school_teacher_msg($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }


    public function getExamYear($request, $response, $args)
    {
        global $container;
        $container->logging = "施測年分";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getExamYear($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getExamTerm($request, $response, $args)
    {
        global $container;
        $container->logging = "施測學期";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getExamTerm($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getExamTkind($request, $response, $args)
    {
        global $container;
        $container->logging = "施測期初期末";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getExamTkind($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getSchoolName($request, $response, $args)
    {
        global $container;
        $container->logging = "學校名稱";

        $data['sid'] = $_SESSION['Sid'];
        $admin = new admin($this->container);
        $result = $admin->getSchoolName($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getTeaName($request, $response, $args)
    {
        global $container;
        $container->logging = "老師名稱";

        $data['tid'] = $_SESSION['Tid'];  #  18054
        $admin = new admin($this->container);
        $result = $admin->getTeaName($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_admin_tea_name($request, $response, $args)
    {
        global $container;
        $container->logging = "老師名稱";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_admin_tea_name($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getExamRM($request, $response, $args)
    {
        global $container;
        $container->logging = "施測指導語ExamRM";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getExamRM($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getPage($request, $response, $args)
    {
        global $container;
        $container->logging = "施測指導語";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getPage($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_page_island($request, $response, $args)
    {
        global $container;
        $container->logging = "抓取頁面island";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_page_island($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_page_learn($request, $response, $args)
    {
        global $container;
        $container->logging = "抓取頁面learn";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_page_learn($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_page_list_learn($request, $response, $args)
    {
        global $container;
        $container->logging = "抓取頁面list learn";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_page_list_learn($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_manager_data($request, $response, $args)
    {
        global $container;
        $container->logging = "抓取管理者island";

        $data = $request->getQueryParams();
        $data['uid'] = $_SESSION['Uid'];
        $admin = new admin($this->container);
        $result = $admin->get_manager_data($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_page($request, $response, $args)
    {
        global $container;
        $container->logging = "修改施測指導語";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->patch_page($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_page_learn($request, $response, $args)
    {
        global $container;
        $container->logging = "修改頁面learn";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->patch_page_learn($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_page_island($request, $response, $args)
    {
        global $container;
        $container->logging = "修改頁面island";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->patch_page_island($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_news($request, $response, $args)
    {
        global $container;
        $container->logging = "新增、修改最新消息";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->delete_news($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_news_island($request, $response, $args)
    {
        global $container;
        $container->logging = "island新增、修改最新消息";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->delete_news_island($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_problem_island($request, $response, $args)
    {
        global $container;
        $container->logging = "island刪除問題";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->delete_problem_island($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_achievement_island($request, $response, $args)
    {
        global $container;
        $container->logging = "island刪除歷屆成果";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->delete_achievement_island($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_admin_school($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 刪除學校";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->delete_admin_school($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_basic_datamsg($request, $response, $args)
    {
        global $container;
        $container->logging = "刪除學校";

        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        $result = $admin->post_basic_datamsg($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_school($request, $response, $args)
    {
        global $container;
        $container->logging = "刪除學校";

        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        $insert = $admin->delete_school($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($insert);
        return $response;
    }

    public function post_deleted_school($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 新增被刪除學校";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $check = $admin->delete_school($data);
        if ($check['status'] === 'failure') {
            return $check;
        } else {
            $result = $admin->post_deleted_school($data);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        }
    }

    public function post_news($request, $response, $args)
    {
        global $container;
        $container->logging = "新增、修改最新消息";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->post_news($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_news_island($request, $response, $args)
    {
        global $container;
        $container->logging = "island 新增、修改最新消息";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->post_news_island($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_problem_island($request, $response, $args)
    {
        global $container;
        $container->logging = "island 新增、修改最新消息";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->post_problem_island($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_achievement_island($request, $response, $args)
    {
        global $container;
        $container->logging = "island 修改歷屆成果列表";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->post_achievement_island($data);
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
        if (!is_null($data['Nid'])) {
            $result = $admin->patch_news_click($data);
        }
        $result = $admin->getNewList($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getSystemManager($request, $response, $args)
    {
        global $container;
        $container->logging = "管理者帳號列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getSystemManager($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function post_SystemManager_account($request, $response, $args)
    {
        global $container;
        $container->logging = "新增管理者帳號列表";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $check2 = $admin->check_password($data);
        if ($check2["status"] == "failure") {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($check2);
            return $response;
        }
        $result = $admin->post_SystemManager_account($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function patch_SystemManager_account($request, $response, $args)
    {
        global $container;
        $container->logging = "修改管理者帳號列表";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $check = $admin->check_password($data);
        if ($check["status"] === "failure") {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($check);
            return $response;
        }
        $result = $admin->patch_SystemManager_account($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_system_manager_data($request, $response, $args)
    {
        global $container;
        $container->logging = "修改管理者資訊";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->patch_system_manager_data($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_event_logging($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 事件紀錄";

        $data = $request->getParams();
        $admin = new admin($this->container);

        $files = $admin->get_event_logging($request);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($files);
        return $response;
    }

    public function get_event_logging_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "get_event_logging_excel";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->get_event_logging_excel($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function post_word_mp3($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 新增生字mp3";

        $data = $request->getParams();
        $admin = new admin($this->container);

        $files = $admin->upload_word_mp3($request);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($files);
        return $response;
    }

    public function post_word($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 新增生字";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $student = new student($this->container);

        $word_insert = $admin->post_word($data);
        if ($word_insert['status'] === 'failure') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($word_insert);
            return $word_insert;
        }

        $nid = $student->get_word_romanization_word($data);
        $data['nid'] = $nid['nid'];
        $identify_insert = $admin->post_identify($data);
        if ($identify_insert['status'] === 'failure') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($identify_insert);
            return $identify_insert;
        }

        foreach ($data['Learn_Sentence_agg'] as $key => $value) {
            $sentence_insert = $admin->post_sentence($data['Learn_Sentence_agg'][$key]);
            if ($sentence_insert['status'] === 'failure') {
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($identify_insert);
                return $identify_insert;
            }

            $sid = $student->get_sentence_id($data['Learn_Sentence_agg'][$key]);
            $data['Learn_Sentence_agg'][$key]['nid'] = $nid['nid'];
            $data['Learn_Sentence_agg'][$key]['sentence_id'] = $sid['sentence_id'];
            $word_sentence_insert = $admin->post_word_sentence($data['Learn_Sentence_agg'][$key]);
            if ($word_sentence_insert['status'] === 'failure') {
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($word_sentence_insert);
                return $word_sentence_insert;
            }
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($word_sentence_insert);
        return $response;
    }

    public function patch_word($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 修改生字";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $student = new student($this->container);

        $word_update = $admin->patch_word($data);
        if ($word_update['status'] === 'failure') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($word_update);
            return $word_update;
        }

        $identify_insert = $admin->delete_identify($data);
        $identify_insert = $admin->post_identify($data);
        if ($identify_insert['status'] === 'failure') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($identify_insert);
            return $identify_insert;
        }

        foreach ($data['Learn_Sentence_agg'] as $key => $value) {
            $identify_delete = $admin->delete_sentence($data['Learn_Sentence_agg'][$key]);
            $word_sentence_delete = $admin->delete_word_sentence($data);
        }

        foreach ($data['Learn_Sentence_agg'] as $key => $value) {
            $sentence_insert = $admin->post_sentence($data['Learn_Sentence_agg'][$key]);
            if ($sentence_insert['status'] === 'failure') {
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($identify_insert);
                return $identify_insert;
            }

            $sid = $student->get_sentence_id($data['Learn_Sentence_agg'][$key]);
            $data['Learn_Sentence_agg'][$key]['nid'] = $data['nid'];
            $data['Learn_Sentence_agg'][$key]['sentence_id'] = $sid['sentence_id'];
            $word_sentence_insert = $admin->post_word_sentence($data['Learn_Sentence_agg'][$key]);
            if ($word_sentence_insert['status'] === 'failure') {
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($word_sentence_insert);
                return $word_sentence_insert;
            }
        }

        // $identify_insert = $admin->patch_identify($data);
        // if ($identify_insert['status'] === 'failure') {
        //     $response = $response->withHeader('Content-type', 'application/json');
        //     $response = $response->withJson($identify_insert);
        //     return $identify_insert;
        // }

        // foreach ($data['Learn_Sentence_agg'] as $key => $value) {
        //     $sentence_insert = $admin->patch_sentence($data['Learn_Sentence_agg'][$key]);
        //     if ($sentence_insert['status'] === 'failure') {
        //         $response = $response->withHeader('Content-type', 'application/json');
        //         $response = $response->withJson($identify_insert);
        //         return $identify_insert;
        //     }
        // }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($sentence_insert);
        return $response;
    }

    public function post_system_manager_photo($request, $response, $args)
    {
        global $container;
        $container->logging = "新增管理者帳號照片";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);

        $data1 = $data;
        unset($data1['inputFile']);
        $data1['uid'] = $_SESSION['Uid'];
        $photo = $admin->get_system_manager_photo($data1);
        if (!is_null($photo['Photo'])) {
            unlink($this->container->upload_directory . $photo['Photo']);
        }

        $data['files'] = $request->getUploadedFiles();
        $files = $admin->upload_manager_photo($data);
        if ($files['status'] === 'failed') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($files);
            return $files;
        }
        $data1['photo'] = $files['file_name'];
        $data1['uid'] = $_SESSION['Uid'];

        $result = $admin->post_system_manager_photo($data1);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_system_manager_photo($request, $response, $args)
    {
        global $container;
        $container->logging = "修改管理者帳號照片";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);

        $data1['uid'] = $_SESSION['Uid'];
        $photo = $admin->get_system_manager_photo($data);
        if (!is_null($photo['Photo'])) {
            unlink($this->container->upload_directory . $photo['Photo']);
        }

        $result = $admin->patch_system_manager_photo($data1);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_SystemManager_account($request, $response, $args)
    {
        global $container;
        $container->logging = "刪除管理者帳號列表";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->delete_SystemManager_account($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_school_teacher_student_account($request, $response, $args)
    {
        global $container;
        $container->logging = "admin刪除老師管理學生帳號";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->delete_school_teacher_student_account($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_school_teacher_student_accounts($request, $response, $args)
    {
        global $container;
        $container->logging = "admin多筆刪除老師管理學生帳號";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        foreach ($data as $key => $value) {
            $result = $admin->delete_school_teacher_student_account($value);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getManagerRecord($request, $response, $args)
    {
        global $container;
        $container->logging = "管理者帳號動作紀錄";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getManagerRecord($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_surf_people($request, $response, $args)
    {
        global $container;
        $container->logging = "瀏覽人次";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_surf_people($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getCitySchool($request, $response, $args)
    {
        global $container;
        $container->logging = "縣市學校列表管理";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getCitySchool($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getUp1Y($request, $response, $args)
    {
        global $container;
        $container->logging = "全資料庫升年級標語";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getUp1Y($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_up_1Y($request, $response, $args)
    {
        global $container;
        $container->logging = "全資料庫升年級";

        $data = $request->getParsedBody();

        $admin = new admin($this->container);
        $check = $admin->check_up_1Y($data);
        $check = explode("/", $check);
        if ($check[0] == $data['year']) {
            $msg = [
                "status" => "failure",
                "message" => "今年已升過年級"
            ];
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($msg);
            return $response;
        }
        $result = $admin->patch_up_1Y($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_class_up_1Y($request, $response, $args)
    {
        global $container;
        $container->logging = "本校各班升年級";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);

        $check = $admin->check_class_up_1Y($data);
        $check = explode("/", $check);
        if ($check[0] == $data['year']) {
            $msg = [
                "status" => "failure",
                "message" => "今年已升過年級"
            ];
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($msg);
            return $response;
        }
        $result = $admin->patch_class_up_1Y($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function StuReportCase($request, $response, $args)
    {
        global $container;
        $container->logging = "下拉選單集合";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [];
        $result['ExamDate'] = $admin->getExamDate($data);
        $result['Semster'] = $admin->getSemster($data);
        $result['Grade'] = $admin->getGrade($data);
        $result['Class'] = $admin->getClass($data);
        $result['FormKind'] = $admin->getFormKind($data);
        $result['year'] = $admin->getYear($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getCityAccount($request, $response, $args)
    {
        global $container;
        $container->logging = "縣市管理者帳號列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getCityAccount($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_city_account($request, $response, $args)
    {
        global $container;
        $container->logging = "修改縣市帳號";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->patch_city_account($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getSchoolMsg($request, $response, $args)
    {
        global $container;
        $container->logging = "Admin學校帳號管理列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getSchoolMsg($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_school_msg_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "Admin識字施測結果報表excel";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->getSchoolMsg($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function get_schoolMsg_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "Admin學校帳號管理列表excel";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->get_schoolMsg_excel($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function getExamReport($request, $response, $args)
    {
        global $container;
        $container->logging = "Admin識字施測情況報表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getExamReport($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getExamReportExcel($request, $response, $args)
    {
        global $container;
        $container->logging = "Admin識字施測情況excel報表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->getExamReport($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function delete_exam($request, $response, $args)
    {
        global $container;
        $container->logging = "Admin刪除施測人員";
        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->delete_exam($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getExamResult_count($request, $response, $args)
    {
        global $container;
        $container->logging = "Admin識字施測結果報表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getExamResult_count($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getExamResult($request, $response, $args)
    {
        global $container;
        $container->logging = "Admin識字施測結果報表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getExamResult($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getStuReportExcel($request, $response, $args)
    {
        global $container;
        $container->logging = "admin識字施測結果報表excel";

        $data = $request->getQueryParams();
        $data['sid'] = $_SESSION['Sid']; # 11  $_SESSION['Sid']
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->getStuReportExcel($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function getExamExcel($request, $response, $args)
    {
        global $container;
        $container->logging = "Admin識字施測結果報表excel";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->getExamResult_excel($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function getNoExamReportExcel($request, $response, $args)
    {
        global $container;
        $container->logging = "Admin期間未施測報表excel";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->getNoExamReport($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function getNoExamReport($request, $response, $args)
    {
        global $container;
        $container->logging = "Admin期間未施測報表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getNoExamReport($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getLibraryA($request, $response, $args)
    {
        global $container;
        $container->logging = "Admin識字施測題庫A版列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getLibraryA($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getLibraryBC($request, $response, $args)
    {
        global $container;
        $container->logging = "Admin識字施測題庫BC版列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getLibraryBC($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patchExamWord($request, $response, $args)
    {
        global $container;
        $container->logging = "Admin修改識字施測題庫";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->patchExamWord($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getAccountAmount($request, $response, $args)
    {
        global $container;
        $container->logging = "Admin個帳號總數列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [];
        $data['identity'] = 'student';
        $result['student'] = $admin->getAccountAmount($data);
        $data['identity'] = 'teacher';
        $result['teacher'] = $admin->getAccountAmount($data);
        $data['identity'] = 'school';
        $result['school'] = $admin->getAccountAmount($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getCommonWordCount($request, $response, $args)
    {
        global $container;
        $container->logging = "前三固定字總數報表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getCommonWordCount($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getWordEdit($request, $response, $args)
    {
        global $container;
        $container->logging = "修改識字題庫";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getWordEdit($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getCommonWord($request, $response, $args)
    {
        global $container;
        $container->logging = "前三固定字列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getCommonWord($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getfileList($request, $response, $args)
    {
        global $container;
        $container->logging = "匯入上傳檔案列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getfileList($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_file_count($request, $response, $args)
    {
        global $container;
        $container->logging = "匯入上傳檔案列表總數";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_file_count($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_uploadfile($request, $response, $args)
    {
        global $container;
        $container->logging = "刪除上傳檔案";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        foreach ($data as $key => $value) {
            $result = $admin->delete_uploadfile($value);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }


    // public function insertfile($request, $response, $args)
    // {
    //     global $container;
    //     $container->logging = "上傳檔案";

    //     $file = $this->container->upload_file_directory;
    //     $dir = "/var/www/html/public/../uploads/UploadFile/";
    //     // $dir = "D:\lab\pair_system\uploads\UploadFile";

    //     // $a = scandir("{$this->container->upload_file_directory}");
    //     $data = scandir($dir);
    //     // $a = scandir($dir);
    //     // return $file;
    //     // $arry = array();
    //     foreach ($data as $key => $value) {
    //         // array_push($arry, ['file_name' => $value]);
    //         // array_push($arry, ['upload_name' => $value]);
    //         $data['file_name'] = $value;
    //         $data['upload_name'] = $value;

    //         $admin = new admin($this->container);
    //         $result = $admin->insertfile($data);
    //     }
    //     // var_dump($arry) ;
    //     // exit(0);

    //     // $admin = new admin($this->container);
    //     // $result = $admin->insertfile($data);
    //     $response = $response->withHeader('Content-type', 'application/json');
    //     $response = $response->withJson($result);
    //     return "success";
    // }




    public function getStuExamRecord($request, $response, $args)
    {
        global $container;
        $container->logging = "學生線上識字量測驗紀錄";

        $data['pid'] = $_SESSION['Pid'];  #  210388 $_SESSION['Pid']
        $admin = new admin($this->container);
        $result = $admin->getStuExamRecord($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getCityExamState($request, $response, $args)
    {
        global $container;
        $container->logging = "縣市測驗紀錄";

        $data = $request->getQueryParams();
        $data['cityname'] =  $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $admin = new admin($this->container);
        $result = $admin->getCityExamState($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_literacy_amount_count($request, $response, $args)
    {
        global $container;
        $container->logging = "學生線上識字量測驗紀錄";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_literacy_amount_count($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function transfer_admin_school_teacher($request, $response, $args)
    {
        global $container;
        $container->logging = "admin個別修改學校老師資料";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->transfer_admin_school_teacher($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_admin_school_teacher($request, $response, $args)
    {
        global $container;
        $container->logging = "admin個別修改學校老師資料";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);

        $check2 = $admin->check_password($data);
        if ($check2["status"] == "failure") {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($check2);
            return $response;
        }

        $result = $admin->patch_admin_school_teacher($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_export_admin_tea_sample_data($request, $response, $args) //Geting the excel data of teacher data for uploading.
    {
        global $container;
        $container->logging = "教師資料批次上傳匯入檔案";

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['inputFile'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedFile->file);
            $worksheet = $spreadsheet->getActiveSheet();
            // Get the highest row number and column letter referenced in the worksheet
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            // Increment the highest column letter
            $highestColumn++;
            $data = [];
            for ($row = 2; $row <= $highestRow; ++$row) {
                $tmp = [];
                for ($col = 'A'; $col != $highestColumn; ++$col) {
                    $tmp["Grade"] = trim(strval($worksheet->getCell('A' . $row)->getValue()));
                    $tmp["Class"] = trim(strval($worksheet->getCell('B' . $row)->getValue()));
                    $tmp["TeacherName"] = trim(strval($worksheet->getCell('C' . $row)->getValue()));
                    $tmp["TeacherMail"] = trim(strval($worksheet->getCell('D' . $row)->getValue()));
                    $tmp["PassWD"] = trim(strval($worksheet->getCell('E' . $row)->getValue()));
                }
                $data[] = $tmp;
            }
            $result = $data;
        } else {
            $result = array(
                "status" => "fail"
            );
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_import_admin_tea_data($request, $response, $args)
    {
        global $container;
        $container->logging = "批次新增老師";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        foreach ($data as $key => $value) {
            $result = $admin->post_import_admin_tea_data($value);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_import_learn_master_tea_data($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 master 批次新增老師";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        foreach ($data as $key => $value) {
            $value['Sid'] = $_SESSION['Sid'];
            $result = $admin->post_import_admin_tea_data($value);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_admin_import_transfer_student_data($request, $response, $args)
    {
        global $container;
        $container->logging = "批次新增老師";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        foreach ($data as $key => $value) {
            $check = $admin->check_patch_admin_import_transfer_student_data($value);
            if ($check['status'] == 'success') {
                $data['tid'] = $check['Tid'];
                $data['pid'] = $admin->find_patch_admin_import_transfer_student_data($data);
                $result = $admin->patch_admin_import_transfer_student_data($data);
            } else {
                return $check;
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_pid_data($request, $response, $args)
    {
        global $container;
        $container->logging = "admin送合併之pid之學生資料";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_pid_data($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_pid_data_amount($request, $response, $args)
    {
        global $container;
        $container->logging = "admin送合併之pid之學生資料總數";

        $data = $request->getQueryParams();
        $pid1 = $data;
        $pid2 = $data;
        $pid3 = $data;
        $admin = new admin($this->container);
        unset($pid1['pid2']);
        unset($pid1['pid3']);
        $result['pid1'] = $admin->get_pid_data($pid1);

        unset($pid2['pid1']);
        unset($pid2['pid3']);
        $result['pid2'] = $admin->get_pid_data($pid2);

        unset($pid3['pid1']);
        unset($pid3['pid2']);
        $result['pid3'] = $admin->get_pid_data($pid3);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function check_pid($request, $response, $args)
    {
        global $container;
        $container->logging = "admin檢查合併之pid是否存在";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->check_pid($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function merge_pid($request, $response, $args)
    {
        global $container;
        $container->logging = "縣市管理人員管理碼修改";


        $data = $request->getParsedBody(); //$_post
        $admin = new admin($this->container);
        $result = $admin->merge_pid($data);

        $verify = $data['check_pid'];
        unset($data['check_pid']);

        if ($data['pid1'] === $verify) {
            unset($data['pid1']);
            $del = $admin->delete_pid($data);
        } else if ($data['pid2'] === $verify) {
            unset($data['pid2']);
            $del = $admin->delete_pid($data);
        } else if ($data['pid3'] !== '') {
            if ($data['pid3'] === $verify) {
                unset($data['pid3']);
                $del = $admin->delete_pid($data);
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_pid($request, $response, $args)
    {
        global $container;
        $container->logging = "縣市管理人員管理碼修改";


        $data = $request->getParsedBody(); //$_post
        $admin = new admin($this->container);
        $result = $admin->delete_pid($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function check_add_admin_school_teacher($request, $response, $args)
    {
        global $container;
        $container->logging = "admin檢查新增的老師是否存在";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->check_add_admin_school_teacher($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function check_admin_school_teacher_student($request, $response, $args)
    {
        global $container;
        $container->logging = "admin檢查新增的學生是否存在";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->check_admin_school_teacher_student($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_admin_school_teacher_student($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 老師新增學生基本資料";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $check = $admin->check_admin_school_teacher_student($data);
        if ($check['status'] == "success") {
            $result = $admin->post_admin_school_teacher_student($data);
        } else {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($check);
            return $response;
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function post_admin_school_teacher($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 新增老師基本資料";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $check = $admin->check_add_admin_school_teacher($data);
        if ($check['status'] == "success") {
            $check2 = $admin->check_password($data);
            if ($check2["status"] == "failure") {
                // $response = $response->withStatus(500);
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($check2);
                return $response;
            } else {
                $result = $admin->post_admin_school_teacher($data);
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($result);
                return $response;
            }
        } else {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($check);
            return $response;
        }
    }

    public function check_delete_admin_school_teacher($request, $response, $args)
    {
        global $container;
        $container->logging = "admin學校老師列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->check_delete_admin_school_teacher($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_admin_school_teacher($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 刪除老師基本資料";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $check = $admin->check_delete_admin_school_teacher($data);
        if ($check['status'] == "success") {
            $result = $admin->delete_admin_school_teacher($data);
        } else {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($check);
            return $response;
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_admin_school_teacher($request, $response, $args)
    {
        global $container;
        $container->logging = "admin學校老師列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_admin_school_teacher($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_teacher_student($request, $response, $args)
    {
        global $container;
        $container->logging = "admin學校老師列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_teacher_student($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_teacher_student_count($request, $response, $args)
    {
        global $container;
        $container->logging = "admin學校老師列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_teacher_student_count($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_admin_school_teacher_data($request, $response, $args)
    {
        global $container;
        $container->logging = "admin學校老師的資訊";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_admin_school_teacher_data($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_teacher_student_id($request, $response, $args)
    {
        global $container;
        $container->logging = "admin修改班級學生id";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        foreach ($data as $key => $value) {
            $result = $admin->patch_teacher_student_id($value);
            if ($result['status'] == 'failure') {
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($result);
                return $response;
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_basic_datamsg($request, $response, $args)
    {
        global $container;
        $container->logging = "admin更新學校基本資料";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->patch_basic_datamsg($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_school_passwd_md5($request, $response, $args)
    {
        global $container;
        $container->logging = "顯示校方管理碼md5";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_school_passwd_md5($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function check_school_passwd($request, $response, $args)
    {
        global $container;
        $container->logging = "檢查更新的密碼強弱";

        $data = $request->getQueryParams();

        $admin = new admin($this->container);
        $result = $admin->check_school_passwd($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_school_passwd($request, $response, $args)
    {
        global $container;
        $container->logging = "更改校方管理碼";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $check = $admin->check_school_passwd($data);
        if ($check["status"] === "failed") {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($check);
            return $response;
        }
        $check2 = $admin->check_password($data);
        if ($check2["status"] === "failure") {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($check2);
            return $response;
        } else {
            $result = $admin->patch_school_passwd($data);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        }
    }

    public function get_literacy_amount($request, $response, $args)
    {
        global $container;
        $container->logging = "分析識字量資料";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_literacy_amount($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_word_stroke($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 生字筆畫";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_word_stroke($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_word_library($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 相似字";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->delete_word_library($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_version($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 版本字庫刪除";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->delete_version($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_word_library($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 相似字";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_word_library($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_word_library_dropdown($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 相似字";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_word_library_dropdown($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_word_library_count($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 相似字";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_word_library_count($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_version_library($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 相似字";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_version_library($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_version_library_count($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 相似字";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_version_library_count($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_version_library_dropdown($request, $response, $args)
    {
        global $container;
        $container->logging = "learn admin各版本字庫查詢下拉選單";

        // $data = $request->getParsedBody();
        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [];
        $result['Year'] = $admin->get_year_learn($data);
        $result['Term'] = $admin->get_term_learn($data);
        $result['Grade'] = $admin->get_grade_learn($data);
        $result['Version'] = $admin->get_version_learn($data);
        $result['Lesson'] = $admin->get_lesson_learn($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_version_word($request, $response, $args)
    {
        global $container;
        $container->logging = "Learn 修改版本生字資訊";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $student = new student($this->container);

        $wordlist = $data;
        unset($data['wordlist']);
        $delete_result = $admin->delete_version_word($data);
        if ($delete_result['status'] === 'failure') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($delete_result);
            return $response;
        }

        $patch_result = $admin->patch_version_word($data);
        if ($patch_result['status'] === 'failure') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($patch_result);
            return $response;
        }

        foreach ($wordlist['wordlist'] as $key => $value) {
            $word['word'] = $wordlist['wordlist'][$key];
            $nid = $student->get_word_nid($word);
            $lesson_word = ['Lid' => $data['Lid']];
            $lesson_word['nid'] = $nid['nid'];
            $lesson_word['sequence'] = (int)$key;
            $result = $admin->insert_version_word($lesson_word);
            if ($result['status'] === 'failure') {
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($result);
                return $result;
            }
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_version_word($request, $response, $args)
    {
        global $container;
        $container->logging = "Learn 新增版本生字資訊";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $student = new student($this->container);

        $wordlist = $data;
        unset($data['wordlist']);

        $post_result = $admin->post_version_word($data);
        if ($post_result['status'] === 'failure') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($post_result);
            return $response;
        }

        foreach ($wordlist['wordlist'] as $key => $value) {
            $word['word'] = $wordlist['wordlist'][$key];
            $nid = $student->get_word_nid($word);
            $lesson_word = ['Lid' => $post_result['Lid']];
            $lesson_word['nid'] = $nid['nid'];
            $lesson_word['sequence'] = (int)$key;
            $result = $admin->insert_version_word($lesson_word);
            if ($result['status'] === 'failure') {
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($result);
                return $result;
            }
            $result['message'] = '新增成功';
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_literacyscore($request, $response, $args)
    {
        global $container;
        $container->logging = "初始化A、B版literacyscore";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $result = $admin->patch_literacyscore($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getCityExamReport($request, $response, $args)
    {
        global $container;
        $container->logging = "縣市施測結果報表";

        $data = $request->getQueryParams();
        $data['cityid'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $admin = new admin($this->container);
        $result = $admin->getCityExamReport($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_school_applyfile_name($request, $response, $args)
    {
        global $container;
        $container->logging = "學校申請檔名";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_school_applyfile_name($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function upload_apply_file($request, $response, $args)
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
        // foreach ($files as $file_name) {
        //     $file = $file_name;
        // }

        $data['name'] = $files['file_name'];
        $result = $admin->patch_apply_file($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);

        // $file = '';
        // foreach ($files as $file_name) {
        //     	$file = $file_name;
        // }

        // $data['name'] = $file;
        // $result = $admin->post_attach_file($data);

        // $response = $response->withHeader('Content-type', 'application/json');
        // $response = $response->withJson($files);
        return $response;
    }

    public function get_upload_editor_file($request, $response, $args)
    {
        global $container;
        $container->logging = "下載所見即所得檔案";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);

        $file = $data['files'];

        $file = $this->container->upload_file_directory . 'News' . $file;
        readfile($file);
        $response = $response->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', "attachment;filename={$file}")
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public');
        return $response;
    }

    public function upload_editor_file($request, $response, $args)
    {
        global $container;
        $container->logging = "所見即所得上傳檔案";

        global $container;
        $admin = new admin($container->db);
        $data = $request->getParams();
        $data['files'] = $request->getUploadedFiles();

        $files = $admin->upload_editor_file($data);
        if ($files['status'] === 'failed') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($files);
            return $files;
        }
        $result = [
            'data' => [
                'baseurl' => '/api/literacy/admin/editor',
                'code' => 220,
                'files' => array(
                    $files['file_name']
                ),
                'isImages' => array(
                    false
                ),
                'messages' => array()
            ],
            'elapsedTime' => null,
            'success' => true,
            'time' => date('Y/m/d H:i:s')

        ];
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function upload_editor_img($request, $response, $args)
    {
        global $container;
        $container->logging = "所見即所得上傳照片";

        global $container;
        $admin = new admin($container->db);
        $data = $request->getParams();
        $data['files'] = $request->getUploadedFiles();

        $files = $admin->upload_editor_file($data);
        if ($files['status'] === 'failed') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($files);
            return $files;
        }
        $result = [
            'data' => [
                'baseurl' => '/api/literacy/admin/editor',
                'code' => 220,
                'files' => array(
                    $files['file_name']
                ),
                'isImages' => array(
                    true
                ),
                'messages' => array()
            ],
            'elapsedTime' => null,
            'success' => true,
            'time' => date('Y/m/d H:i:s')

        ];
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function compressImage($source = false, $destination = false, $quality = 80, $filters = false)
    {
        global $container;
        $container->logging = "compressImage";
        $info = getimagesize($source);
        switch ($info['mime']) {
            case 'image/jpeg':
                /* Quality: integer 0 - 100 */
                if (!is_int($quality) or $quality < 0 or $quality > 100) $quality = 80;
                return imagecreatefromjpeg($source);
            case 'image/gif':
                return imagecreatefromgif($source);
            case 'image/png':
                /* Quality: Compression integer 0(none) - 9(max) */
                if (!is_int($quality) or $quality < 0 or $quality > 9) $quality = 6;
                return imagecreatefrompng($source);
            case 'image/webp':
                /* Quality: Compression 0(lowest) - 100(highest) */
                if (!is_int($quality) or $quality < 0 or $quality > 100) $quality = 80;
                return imagecreatefromwebp($source);
            case 'image/bmp':
                /* Quality: Boolean for compression */
                if (!is_bool($quality)) $quality = true;
                return imagecreatefrombmp($source);
            default:
                return;
        }
    }

    public function getElementPic($request, $response, $args)
    {
        global $container;
        $container->logging = "國中小色點圖對照表";

        $data = $request->getQueryParams();
        $data['sid'] = $_SESSION['Sid'];    #  11 $_SESSION['Sid'] 868 junjor
        $admin = new admin($this->container);
        $result = $admin->getElementPic($data);
        foreach ($result as $key => $values) {
            $string = $values['Class'];
        }
        if ($string == "國中") {
            $file = $this->container->upload_directory . DIRECTORY_SEPARATOR . '顏色對照表_國中.jpg';
            $source = $this->compressImage($file, $file, 100);
            imagejpeg($source);
            $response = $response->withHeader('Content-Description', 'File Transfer')
                ->withHeader('Content-Type', 'application/octet-stream')
                ->withHeader('Content-Disposition', 'attachment;filename="' . '顏色對照表_國中.jpg' . '"')
                ->withHeader('Expires', '0')
                ->withHeader('Cache-Control', 'must-revalidate')
                ->withHeader('Pragma', 'public');
        } else {
            $file = $this->container->upload_directory . DIRECTORY_SEPARATOR . '顏色對照表_國小.jpg';
            $source = $this->compressImage($file, $file, 100);
            imagejpeg($source);
            $response = $response->withHeader('Content-Description', 'File Transfer')
                ->withHeader('Content-Type', 'application/octet-stream')
                ->withHeader('Content-Disposition', 'attachment;filename="' . '顏色對照表_國小.jpg' . '"')
                ->withHeader('Expires', '0')
                ->withHeader('Cache-Control', 'must-revalidate')
                ->withHeader('Pragma', 'public');
        }
        return $response;
    }

    public function get_manager_photo($request, $response, $args)
    {
        global $container;
        $container->logging = "管理者圖片";

        $data = $request->getQueryParams();
        $data['uid'] = $_SESSION['Uid'];
        $admin = new admin($this->container);
        $result = $admin->get_manager_data($data);
        $file = $this->container->upload_directory . DIRECTORY_SEPARATOR . $result["Photo"];
        $source = $this->compressImage($file, $file, 100);
        imagejpeg($source);
        $response = $response->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment;filename="' . $result["Photo"] . '"')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public');
        return $response;
    }

    public function getFile($request, $response, $args)
    {
        global $container;
        $container->logging = "下載已入上傳檔案列表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getFile($data);
        // $response = $response->withHeader('Content-type', 'application/json');
        // $response = $response->withJson($result);
        // foreach ($result as $key => $values) {
        //     $file = $this->container->upload_file_directory . DIRECTORY_SEPARATOR . "大肚區山陽國民小學_劉美文_Student_2021-7-4 下午 05-21-06.xls";
        //     $response = $response->withHeader('Content-Description', 'File Transfer')
        //         ->withHeader('Content-Type', 'application/octet-stream')
        //         ->withHeader('Content-Disposition', 'attachment;filename="' . "大肚區山陽國民小學_劉美文_Student_2021-7-4 下午 05-21-06.xls" . '"')
        //         ->withHeader('Expires', '0')
        //         ->withHeader('Cache-Control', 'must-revalidate')
        //         ->withHeader('Pragma', 'public')
        //         ->withHeader('Content-Length', filesize($file));
        //     return $response;
        // }
        foreach ($result as $key => $values) {
            $file = $this->container->upload_file_directory . $values["File_name"];
            readfile($file);
            $response = $response->withHeader('Content-Description', 'File Transfer')
                ->withHeader('Content-Type', 'application/octet-stream')
                ->withHeader('Content-Disposition', "attachment;filename={$values["Upload_name"]}")
                ->withHeader('Expires', '0')
                ->withHeader('Cache-Control', 'must-revalidate')
                ->withHeader('Pragma', 'public');
            return $response;
        }
    }

    public function getFile_args($request, $response, $args)
    {
        global $container;
        $container->logging = "下載已入上傳檔案列表";

        $data = $args;
        $admin = new admin($this->container);
        $result = $admin->getFile($data);
        foreach ($result as $key => $values) {
            $file = $this->container->upload_file_directory . $values["File_name"];
            readfile($file);
            $response = $response->withHeader('Content-Description', 'File Transfer')
                ->withHeader('Content-Type', 'application/octet-stream')
                ->withHeader('Content-Disposition', "attachment;filename={$values["Upload_name"]}")
                ->withHeader('Expires', '0')
                ->withHeader('Cache-Control', 'must-revalidate')
                ->withHeader('Pragma', 'public');
            return $response;
        }
    }

    public function sampleFile($request, $response, $args)
    {
        global $container;
        $container->logging = "下載施測範例手冊";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->sampleFile($data);
        foreach ($result as $key => $values) {
            $string = $values['Manual_name'];

            if ((pathinfo($string, PATHINFO_BASENAME) === 'png') || (pathinfo($string, PATHINFO_BASENAME) === 'jpg')) {
                $file = $this->container->sample_directory . DIRECTORY_SEPARATOR . $values["Manual_name"];
                $source = $this->compressImage($file, $file, 100);
                imagejpeg($source);
                $response = $response->withHeader('Content-Description', 'File Transfer')
                    ->withHeader('Content-Type', 'application/octet-stream')
                    ->withHeader('Content-Disposition', 'attachment;filename="' . $values["Manual_name"] . '"')
                    ->withHeader('Expires', '0')
                    ->withHeader('Cache-Control', 'must-revalidate')
                    ->withHeader('Pragma', 'public');
                return $response;
            } else {

                $file = $this->container->sample_directory . $values["Manual_name"];
                readfile($file);
                $response = $response->withHeader('Content-Description', 'File Transfer')
                    ->withHeader('Content-Type', 'application/octet-stream')
                    ->withHeader('Content-Disposition', "attachment;filename={$values["Manual_name"]}")
                    ->withHeader('Expires', '0')
                    ->withHeader('Cache-Control', 'must-revalidate')
                    ->withHeader('Pragma', 'public');
                return $response;
            }
        }
    }

    public function get_school_apply($request, $response, $args)
    {
        global $container;
        $container->logging = "下載學校申請書";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);

        $file = $data['ApplyFiles'];
        $name = ltrim($data['ApplyFiles'], "UploadFile/ApplyFiles/");

        $file = $this->container->upload_directory . $file;
        readfile($file);
        $response = $response->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', "attachment;filename={$name}")
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public');
        return $response;

        // if ((pathinfo($file, PATHINFO_BASENAME) === 'png') || (pathinfo($file, PATHINFO_BASENAME) === 'jpg')) {
        //     $file = $this->container->upload_directory . DIRECTORY_SEPARATOR . $file;
        //     $source = $this->compressImage($file, $file, 100);
        //     imagejpeg($source);
        //     $response = $response->withHeader('Content-Description', 'File Transfer')
        //         ->withHeader('Content-Type', 'application/octet-stream')
        //         ->withHeader('Content-Disposition', 'attachment;filename="' . $name . '"')
        //         ->withHeader('Expires', '0')
        //         ->withHeader('Cache-Control', 'must-revalidate')
        //         ->withHeader('Pragma', 'public');
        //     return $response;
        // } else {

        //     $file = $this->container->upload_directory . $file;
        //     readfile($file);
        //     $response = $response->withHeader('Content-Description', 'File Transfer')
        //         ->withHeader('Content-Type', 'application/octet-stream')
        //         ->withHeader('Content-Disposition', "attachment;filename={$name}")
        //         ->withHeader('Expires', '0')
        //         ->withHeader('Cache-Control', 'must-revalidate')
        //         ->withHeader('Pragma', 'public');
        //     return $response;
        // }

    }

    public function get_school_apply_args($request, $response, $args)
    {
        global $container;
        $container->logging = "下載學校申請書";

        $data = $args;
        $admin = new admin($this->container);

        $file = $data['ApplyFiles'];
        $name = ltrim($data['ApplyFiles'], "UploadFile/ApplyFiles/");

        $file = $this->container->upload_directory . $file;
        readfile($file);
        $response = $response->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', "attachment;filename={$name}")
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public');
        return $response;
    }

    public function renderdrawpic($request, $response, $args)
    {
        global $container;
        $container->logging = "renderdrawpic";

        $admin = new admin($this->container);
        $response = $admin->drawPic();
        return $response;
    }

    public function get_mail_count($request, $response, $args)
    {
        global $container;
        $container->logging = "校與老師共計mail數量";

        $admin = new admin($this->container);
        $data = $request->getQueryParams();

        $accounts = $admin->get_mail_count($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($accounts);
        return $response;
    }

    public function get_all_school_mail_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "學校mail報表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->get_all_school_mail($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        // $response = $response->withHeader('Content-type', 'application/json');
        // $response = $response->withJson($result);
        return $response;
    }

    public function getSendMailAccount($request, $response, $args)
    {
        global $container;
        $container->logging = "抓取寄信帳號";

        $admin = new admin($this->container);
        $data = $request->getQueryParams();

        $accounts = $admin->getSendMailAccount($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($accounts);
        return $response;
    }

    public function admin_SendMail($request, $response, $args)
    {
        global $container;
        $container->logging = "寄送系統通知信";

        $admin = new admin($this->container);
        $data = $request->getParsedBody();

        $accounts = $admin->getSendMailAccount($data);

        $result = [
            'success' => 0,
            'failed' => 0
        ];

        foreach ($accounts['school'] as $account) {
            $data['account'] = $account['Contact_EMail_1'];

            $this_result = $admin->admin_SendMail($data);
            if ($this_result['status'] = "success") {
                $result['success']++;
            } else {
                $result['failed']++;
            }
        }
        if ($data['has_teacher']) {
            foreach ($accounts['teacher'] as $account) {
                $data['account'] = $account['TeacherMail'];

                $this_result = $admin->admin_SendMail($data);
                if ($this_result['status'] = "success") {
                    $result['success']++;
                } else {
                    $result['failed']++;
                }
            }
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function admin_SendTestMail($request, $response, $args)
    {
        $admin = new admin($this->container);
        $data = $request->getParsedBody();

        $data['subject'] = "測試";
        $data['body'] = "測試";
        $result = $admin->admin_SendMail($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_event_log($request, $response, $args)
    {
        global $container;
        $container->logging = "拿異動事件";
        $admin = new admin($this->container);
        $data = $request->getQueryParams();
        // $data = $request->getParsedBody();
        $logs = $admin->get_event_log($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($logs);
        return $response;
    }

    public function get_online($request, $response, $args)
    {
        global $container;
        $container->logging = "拿異動事件";
        $admin = new admin($this->container);
        $data = $request->getQueryParams();
        // $data = $request->getParsedBody();
        $logs = $admin->get_online($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($logs);
        return $response;
    }

    public function get_exam_color($request, $response, $args)
    {
        global $container;
        $container->logging = "get_exam_color";

        $admin = new admin($this->container);
        $data = [
            'request' => $request,
            'args' => $args,
            'response' => $response
        ];
        $response = $admin->get_exam_color($data);
        return $response;
    }

    public function add_school($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 新增學校";

        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        $check2 = $admin->check_password($data);
        if ($check2["status"] == "failure") {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($check2);
            return $response;
        }
        $insert = $admin->add_school($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($insert);
        return $response;
    }

    public function insert_question($request, $response, $args)
    {
        global $container;
        $container->logging = "識字施測題庫新增題目";
        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        $insert = $admin->insert_question($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($insert);
        return $response;
    }
    public function score_yes($request, $response, $args)
    {
        global $container;
        $container->logging = "識字施測題庫計分";
        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        $scoring = $admin->score_yes($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($scoring);
        return $response;
    }
    public function scoring_n_d($request, $response, $args)
    {
        global $container;
        $container->logging = "識字施測題庫不計分";
        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        $scoring = $admin->scoring_n_d($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($scoring);
        return $response;
    }
    public function delete_question($request, $response, $args)
    {
        global $container;
        $container->logging = "識字施測題庫刪除題目";
        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        $scoring = $admin->delete_question($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($scoring);
        return $response;
    }
    public function get_three_words($request, $response, $args)
    {
        global $container;
        $container->logging = "取得識字施測題庫前三字";
        $admin = new admin($this->container);
        $data = $request->getQueryParams();
        $scoring = $admin->get_three_words($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($scoring);
        return $response;
    }
    public function patch_three_words($request, $response, $args)
    {
        global $container;
        $container->logging = "識字施測題庫前三字設定";
        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        $scoring = $admin->patch_three_words($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($scoring);
        return $response;
    }
    public function patch_same_three_words($request, $response, $args)
    {
        global $container;
        $container->logging = "識字施測題庫共同字設定";
        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        $scoring = $admin->patch_same_three_words($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($scoring);
        return $response;
    }

    public function get_three_words_detail($request, $response, $args)
    {
        global $container;
        $container->logging = "取得識字施測題庫前三字";
        $admin = new admin($this->container);
        $data = $request->getQueryParams();
        $scoring = $admin->get_three_words_detail($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($scoring);
        return $response;
    }

    public function update_3PL_and_literacy_score($request, $response, $args)
    {
        global $container;
        $container->logging = "識字施測題庫更新3PL、字頻資料";
        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        $scoring = $admin->update_3PL_and_literacy_score($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($scoring);
        return $response;
    }
    public function reset_counter($request, $response, $args)
    {
        global $container;
        $container->logging = "出題次數歸零";
        $admin = new admin($this->container);
        $scoring = $admin->reset_counter();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($scoring);
        return $response;
    }

    public function get_school_msg_list($request, $response, $args)
    {
        global $container;
        $container->logging = "取得學校列表";
        $admin = new admin($this->container);
        $data = $request->getQueryParams();
        $result = $admin->get_school_msg_list($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_school_msg_upinfo($request, $response, $args)
    {
        global $container;
        $container->logging = "取得學校列表";
        $admin = new admin($this->container);
        $data = $request->getQueryParams();
        $result = $admin->get_school_msg_upinfo($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function admin_go_school($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 前往校方";
        $admin = new admin($this->container);
        $data = $request->getQueryParams();
        session_start();
        $result = $admin->admin_go_school($data);
        session_write_close();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_school_status($request, $response, $args)
    {
        global $container;
        $container->logging = "改變學校狀態";
        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        if ($data['status'] == "open") {
            $data['Used'] = '0';
        } else if ($data['status'] == "close") {
            $data['Used'] = '1';
        }
        $result = $admin->patch_school_list($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_school_ep_status($request, $response, $args)
    {
        global $container;
        $container->logging = "改變學校特別開放施測";
        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        if ($data['status'] == "open") {
            $data['ExamPower'] = '0';
        } else if ($data['status'] == "close") {
            $data['ExamPower'] = '1';
        }
        $result = $admin->patch_school_list($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_msg_rm_status($request, $response, $args)
    {
        global $container;
        $container->logging = "改變學校處理狀態";
        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        switch ($data['status']) {
            case "done":
                $data['MsgRM'] = "已處理";
                break;
            case "doing":
                $data['MsgRM'] = "處理中";
                break;
            case "not_done":
                $data['MsgRM'] = "未處理";
                break;
        }
        $result = $admin->patch_school_list($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_master_rm($request, $response, $args)
    {
        global $container;
        $container->logging = "儲存學校備註";
        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        $data['MasterRM'] = $data['text'];
        $result = $admin->patch_school_list($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_exam_program_kind($request, $response, $args)
    {
        global $container;
        $container->logging = "改變學校施測版本";
        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        $data['ExamProgramKind'] = $data['kind'];
        $result = $admin->patch_school_list($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_admin_option_datas($request, $response, $args)
    {
        global $container;
        $container->logging = "取得施測參數設定";
        $admin = new admin($this->container);

        $result['Exam'] = $admin->get_admin_option_datas(['OptionItem' => 'Exam']);
        $result['Exam_Year'] = $admin->get_admin_option_datas(['OptionItem' => 'Exam_Year']);
        $result['Exam_Term'] = $admin->get_admin_option_datas(['OptionItem' => 'Exam_Term']);
        $result['Exam_TKind'] = $admin->get_admin_option_datas(['OptionItem' => 'Exam_TKind']);
        $result['EK2_ErrorCount'] = $admin->get_admin_option_datas(['OptionItem' => 'EK2_ErrorCount']);
        $result['EK2_RightRate'] = $admin->get_admin_option_datas(['OptionItem' => 'EK2_RightRate']);
        $result['Report_Year'] = $admin->get_admin_option_datas(['OptionItem' => 'Report_Year']);
        $result['Report_Term'] = $admin->get_admin_option_datas(['OptionItem' => 'Report_Term']);
        $result['Report_TKind'] = $admin->get_admin_option_datas(['OptionItem' => 'Report_TKind']);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_admin_options($request, $response, $args)
    {
        global $container;
        $container->logging = "取得施測參數設定";

        $admin = new admin($this->container);
        $data = $request->getParsedBody();
        $failed = [];
        foreach ($data as $key => $value) {
            if ($key == "Exam") {
                $tmp_data = $value;
                $tmp_data['option'] = 'Exam';
                $result['Exam'] = $admin->patch_admin_options($tmp_data);
                if ($result['Exam']['status'] == '設定失敗') {
                    array_push($failed, 'Exam');
                }
            } else {
                $tmp_data['option'] = $key;
                $tmp_data['OptionValues'] = $value;
                $result[$key] = $admin->patch_admin_options($tmp_data);
                if ($result[$key]['status'] == '設定失敗') {
                    array_push($failed, $key);
                }
            }
        }

        if (count($failed) == 0) {
            $result = ['status' => '設定成功'];
        } else {
            $result = ['failed' => $failed];
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function apply_pdf($request, $response, $args)
    {

        global $container;
        $container->logging = "取得pdf";

        $home = new home($this->container);
        $admin = new admin($this->container);
        $data = $request->getParsedBody();

        $verify = $data["verification"];
        unset($data["verification"]);
        $verify_result = $admin->checkCode($verify);
        if ($verify_result['result'] !== "success") {
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
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($pdf);
            return $response;
        } else {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        }
    }

    public function get_learn_word_library_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "匯出生字查詢檔案Excel";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->get_learn_word_library_excel($data),
            "response" => $response,
            "name" => '生字字庫'
        ];
        $response = $admin->getExcel($result, true);
        return $response;
    }

    public function get_learn_version_library_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "匯出版本生字檔案Excel";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->get_learn_version_library_excel($data),
            "response" => $response,
            "name" => '版本生字'
        ];
        $response = $admin->getExcel($result, true);
        return $response;
    }

    public function get_color($request, $response, $args)
    {
        global $container;
        $container->logging = "取得色點";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_color($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_word_version($request, $response, $args)
    {
        global $container;
        $container->logging = "取得生字版本";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_word_version($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_word_grade($request, $response, $args)
    {
        global $container;
        $container->logging = "取得生字年級";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_word_grade($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_word_unit($request, $response, $args)
    {
        global $container;
        $container->logging = "取得生字單元";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_word_unit($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_word_part($request, $response, $args)
    {
        global $container;
        $container->logging = "取得生字部首";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_word_part($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_word_part_stroke($request, $response, $args)
    {
        global $container;
        $container->logging = "取得生字部首筆劃";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_word_part_stroke($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_report_task_detail($request, $response, $args)
    {
        global $container;
        $container->logging = "學習任務資料查詢";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result_array = array();
        $result = $admin->get_learn_report_task_detail($data);
        $total = count($result);
        $result_array['data'] = $result;
        $result_array['total'] = $total;
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result_array);
        return $response;
    }

    public function get_learn_report_task_detail_teacher($request, $response, $args)
    {
        global $container;
        $container->logging = "班級學習任務報表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_learn_report_task_detail_teacher($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_report_task_word($request, $response, $args)
    {
        global $container;
        $container->logging = "學習生字量資料查詢";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result_array = array();
        $result = $admin->get_learn_report_task_word($data);
        $total = count($result);
        $result_array['data'] = $result;
        $result_array['total'] = $total;
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result_array);
        return $response;
    }

    public function get_learn_report_task_word_teacher($request, $response, $args)
    {
        global $container;
        $container->logging = "班級學習生字量報表";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_learn_report_task_word_teacher($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_report_word_frequency($request, $response, $args)
    {
        global $container;
        $container->logging = "老師常派字頻查詢";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_learn_report_word_frequency($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_report_word_teacher($request, $response, $args)
    {
        global $container;
        $container->logging = "老師常派字頻查詢-字";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_learn_report_word_teacher($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_report_summary($request, $response, $args)
    {
        global $container;
        $container->logging = "admin learn 報表區";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_learn_report_summary($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }


    public function get_learn_report_summary_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "admin learn 報表區excel";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->get_learn_report_summary($data),
            "response" => $response,
            "name" => '即時'
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function get_learn_report_word_avg_grade($request, $response, $args)
    {
        global $container;
        $container->logging = "admin learn 本學期各年級平均學習生字量數據圖";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->get_learn_report_word_avg_grade($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_report_word_avg_grade_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "admin learn 報表區excel";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->get_learn_report_word_avg_grade($data),
            "response" => $response,
            "name" => '即時'
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function get_task_self_detail($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 班級學習生字量報表 歷史成績檢視 個人積分";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $result = $exam->get_task_self_detail($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_task_assign_detail($request, $response, $args)
    {
        global $container;
        $container->logging = "admin 班級學習生字量報表 歷史成績檢視 小組積分";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $result = $exam->get_task_assign_detail($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
}
