<?php

use Slim\Views\PhpRenderer;

class citycontroller
{
    protected $container;
    public function __construct()
    {
        global $container;
        $this->container = $container;
    }
    public function get_area($request, $response, $args)
    {
        global $container;
        $container->logging = "鄉鎮市名字";

        $data = $request->getQueryParams();    #  "高雄市"
        $admin = new Admin($this->container);
        $data += ["city" => $_SESSION['CityName']];
        $result = $admin->getArea($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_school($request, $response, $args)
    {
        global $container;
        $container->logging = "鄉鎮市名字";

        $data = $request->getQueryParams();    #  "高雄市"
        $data += ["cityid" => $_SESSION['CityName']];
        $admin = new Admin($this->container);
        $result = $admin->getSchool($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_exam_info($request, $response, $args)
    {
        global $container;
        $container->logging = "班上線上識字量測驗報表";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $result = $exam->get_exam_info($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_exam_state($request, $response, $args)
    {
        global $container;
        $container->logging = "學生線上識字量測驗紀錄";

        $data = $request->getQueryParams();
        $data['cityname'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $admin = new admin($this->container);
        $result = $admin->getCityExamState($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_exam_state_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "學生線上識字量測驗紀錄";

        $data = $request->getQueryParams();
        $data['cityname'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->getCityExamState($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function get_report_current_rank_person($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 縣市端 本學期各年級最佳個人積分";

        $data = $request->getQueryParams();
        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $city = new city($this->container);
        $result = $city->get_report_current_rank_person($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_report_current_rank_group($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 縣市端 本學期各年級最佳小組積分";

        $data = $request->getQueryParams();
        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $city = new city($this->container);
        $result = $city->get_report_current_rank_group($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_report_current_rank_avg_word($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 縣市端 本學期各年級最佳平均生字";

        $data = $request->getQueryParams();
        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $city = new city($this->container);
        $result = $city->get_report_current_rank_avg_word($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_report_current_rank_avg_word_grades($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 縣市端 本學期各年級最佳平均生字";

        $data = $request->getQueryParams();
        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $city = new city($this->container);
        $result = $city->get_report_current_rank_avg_word_grades($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_report_current_school_avg_word_grades($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 縣市端 本學期各年級最佳平均生字";

        $data = $request->getQueryParams();
        // $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $city = new city($this->container);
        $result = $city->get_report_current_school_avg_word_grades($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_report_current_school_word($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 縣市端 各校各年級平均學習生字量";

        $data = $request->getQueryParams();
        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $city = new city($this->container);
        $result = $city->get_report_current_school_word($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_report_current_school_word_avg($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 縣市端 各年級平均學習生字量數據圖";

        $data = $request->getQueryParams();
        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $city = new city($this->container);
        $result = $city->get_report_current_school_word_avg($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_report_current_school_word_avg_one_school($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 縣市端 學校各年級平均學習生字量數據圖";

        $data = $request->getQueryParams();
        $city = new city($this->container);
        $result = $city->get_report_current_school_word_avg($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_exam_account_data($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 縣市端 各區學習生字量情況";

        $data = $request->getQueryParams();
        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $data['CityId1'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $city = new city($this->container);
        $result = $city->get_report_area_rank_avg_word_grades($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_report_area_rank_avg_word_grades($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 縣市端 各區學習生字量情況";

        $data = $request->getQueryParams();
        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $city = new city($this->container);
        $result = $city->get_report_area_rank_avg_word_grades($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_city_account_data($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 縣市端 帳號管理";

        $data = $request->getQueryParams();
        $city = new city($this->container);
        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $result = $city->get_learn_city_account_data($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_city_account($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 縣市端 帳號管理";

        $data = $request->getQueryParams();
        $city = new city($this->container);
        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $result = $city->get_learn_city_account($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_city_report_summary($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 縣市端 即時報表";

        $data = $request->getQueryParams();
        $city = new city($this->container);
        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $result = $city->get_learn_city_report_summary($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_city_account_photo($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 縣市圖片";

        $data = $request->getQueryParams();
        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $city = new city($this->container);
        $result = $city->get_learn_city_account_data($data);

        if (is_null($result[0]['Photo'])) {
            $result[0]['Photo'] = 'login-avatar.png';
        }
        $file = $this->container->upload_directory . DIRECTORY_SEPARATOR . $result[0]["Photo"];
        $source = $this->compressImage($file, $file, 100);
        imagealphablending($source, false);
        imagesavealpha($source, true);
        imagepng($source);

        $response = $response->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment;filename="' . $result[0]["Photo"] . '"')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public');
        return $response;
    }

    public function post_learn_city_photo($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 新增縣市帳號照片";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $city = new city($this->container);

        $data1 = $data;
        unset($data1['inputFile']);
        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $photo = $city->get_learn_city_account_data($data1);
        if (!is_null($photo[0]['Photo'])) {
            unlink($this->container->upload_directory . $photo[0]['Photo']);
        }

        $data['files'] = $request->getUploadedFiles();
        $files = $admin->upload_manager_photo($data);
        if ($files['status'] === 'failed') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($files);
            return $files;
        }
        $data1['photo'] = $files['file_name'];
        $data1['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'

        $result = $city->post_city_photo($data1);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_learn_city_photo($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 修改縣市帳號照片";

        $data = $request->getParsedBody();
        $city = new city($this->container);

        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $photo = $city->get_learn_city_account_data($data);
        if (!is_null($photo[0]['Photo'])) {
            unlink($this->container->upload_directory . $photo[0]['Photo']);
        }

        $data['photo'] = null;
        $result = $city->post_city_photo($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_learn_city_account_mail($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 修改縣市帳號mail";

        $data = $request->getParsedBody();
        $city = new city($this->container);

        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'

        $result = $city->patch_learn_city_mail($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_learn_city_account_passwd($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 修改縣市帳戶密碼";

        $data = $request->getParsedBody();
        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'

        $city = new city($this->container);
        $check = $city->check_city_origin_passwd($data);
        if (!$check['status'] == 'success') {
            return [
                "message" => "密碼不可與原密碼相同"
            ];
        }
        unset($data['passwd_old']);
        $admin = new admin($this->container);
        $check2 = $admin->check_password($data);
        if ($check2["status"] == "failure") {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($check2);
            return $response;
        }
        $result = $city->patch_city_passwd($data);
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
    public function get_report_current_rank_avg_word_grades_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 縣市端 即時報表 excel";

        $data = $request->getQueryParams();
        $data['CityId'] = $_SESSION['CityName'];  #  $_SESSION['CityName']   '彰化縣'
        $admin = new admin($this->container);
        $city = new city($this->container);
        $result = [
            "data" => $city->get_report_current_rank_avg_word_grades($data),
            "response" => $response,
            "name" => '本學期全市學習生字量平均數'
        ];
        $response = $admin->getExcel($result);
        return $response;
    }
    public function get_report_current_school_avg_word_grades_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 縣市端 各校學習生字量情況 excel";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $city = new city($this->container);
        $result = [
            "data" => $city->get_report_current_school_avg_word_grades($data),
            "response" => $response,
            "name" => '各年級平均學習生字量情況'
        ];
        $response = $admin->getExcel($result);
        return $response;
    }
    public function get_report_current_school_word_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 縣市端 各校各年級平均學習生字量 excel";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $city = new city($this->container);
        $result = [
            "data" => $city->get_report_current_school_word($data),
            "response" => $response,
            "name" => '各年級平均學習生字量'
        ];
        $response = $admin->getExcel($result);
        return $response;
    }
}
