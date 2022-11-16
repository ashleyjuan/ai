<?php

use Slim\Views\PhpRenderer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class mastercontroller
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

    public function master_go_exam($request, $response, $args)
    {
        global $container;
        $container->logging = "master 前往班級";
        $master = new master($this->container);
        $data = $request->getQueryParams();
        session_start();
        $result = $master->master_go_exam($data);
        session_write_close();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_school_apply_datamsg($request, $response, $args)
    {
        global $container;
        $container->logging = "學校施測申請表";

        $data = $request->getQueryParams();
        $master = new master($this->container);
        $data['sid'] = $_SESSION['Sid'];  #  $_SESSION['Sid']   11
        $result = [];
        $result['basic'] = $master->getSchoolDataName($data);
        $result['school'] = $master->getSchoolDataSchool($data);
        $result['exam'] = $master->getSchoolDataExam($data);
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

    public function check_master($request, $response, $args)
    {
        global $container;
        $container->logging = "學校原本密碼";

        $data = $request->getQueryParams();
        $master = new master($this->container);
        $result = $master->check_master($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_master_tmp_passwd($request, $response, $args)
    {
        global $container;
        $container->logging = "學生線上識字量測驗紀錄";

        $master = new master($this->container);
        $data = $request->getParsedBody();

        $result = $master->patch_master_tmp_passwd($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function master_SendMail($request, $response, $args)
    {
        global $container;
        $container->logging = "寄送系統通知信";

        $master = new master($this->container);
        $data = $request->getParsedBody();

        // $verify = $data["verification"];
        // unset($data["verification"]);
        // $admin = new admin($this->container);
        // $verify_result = $admin->checkCode($verify);
        // if ($verify_result['result'] == "success") {
        //     $result = $admin->studentLogin($data);
        // } else {
        //     $result = [
        //         "status" => "failed",
        //         "message" => "驗證碼錯誤"
        //     ];
        //     // $response = $response->withStatus(500);
        //     $response = $response->withHeader('Content-type', 'application/json');
        //     $response = $response->withJson($result);
        //     return $response;
        // }

        $check = $master->check_master($data);
        foreach ($check as $key => $column) {
            if ($column['status'] == 'failure') {
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($check);
                return $response;
            }
        }

        $code = "";
        for ($i = 0; $i < 6; $i++) {
            $code .= rand(0, 9);
        }
        $code = "#Fgp-" . $code;
        foreach ($check as $key => $column) {
            $check[0]['passwd'] = $code;
        }

        $change = $master->patch_master_tmp_passwd($check[0]);
        if ($change['status'] == 'failure') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($change);
            return $response;
        }

        $result = $master->master_SendMail($check[0]);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function check_school_origin_passwd($request, $response, $args)
    {
        global $container;
        $container->logging = "學校原本密碼";

        $data = $request->getQueryParams();
        $data['sid'] = $_SESSION['Sid'];  #  $_SESSION['Sid']   11
        $master = new master($this->container);
        $result = $master->check_school_origin_passwd($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_school_passwd($request, $response, $args)
    {
        global $container;
        $container->logging = "學生線上識字量測驗紀錄";

        $data = $request->getParsedBody();
        $data['sid'] = $_SESSION['Sid'];  #  $_SESSION['Sid']   11
        $master = new master($this->container);
        $check = $master->check_school_origin_passwd($data);
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
        $result = $master->patch_school_passwd($data);
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
        $admin = new admin($this->container);
        $result = [
            "data" => $admin->getCityExamState($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function post_teacher($request, $response, $args)
    {
        global $container;
        $container->logging = "學生線上識字量測驗紀錄";

        $data = $request->getParsedBody();
        $data['sid'] = $_SESSION['Sid'];  #  $_SESSION['Sid']   11
        $master = new master($this->container);
        $admin = new admin($this->container);
        $check2 = $admin->check_password($data);
        if ($check2["status"] == "failure") {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($check2);
            return $response;
        }
        $result = $master->post_teacher($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_teacher($request, $response, $args)
    {
        global $container;
        $container->logging = "校方修改老師資訊";

        $data = $request->getParsedBody();
        $data['sid'] = $_SESSION['Sid'];  #  $_SESSION['Sid']   11

        $admin = new admin($this->container);
        $check2 = $admin->check_password($data);
        if ($check2["status"] == "failure") {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($check2);
            return $response;
        }

        $master = new master($this->container);
        $result = $master->patch_teacher($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_teacher($request, $response, $args)
    {
        global $container;
        $container->logging = "master 刪除老師基本資料";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        foreach ($data as $key => $value) {
            $check = $admin->check_delete_admin_school_teacher($value);
            if ($check['status'] == "success") {
                $result = $admin->delete_admin_school_teacher($value);
            } else {
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($check);
                return $response;
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_school_basic_datamsg($request, $response, $args)
    {
        global $container;
        $container->logging = "更新學校基本資料";

        $data = $request->getParsedBody();
        $data['Sid'] = $_SESSION['Sid'];  # 71   $_SESSION['Sid']

        $master = new master($this->container);
        $result = $master->patch_school_basic_datamsg($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_master_school_teacher($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 master 學校老師 列表";

        $data = $request->getQueryParams();
        $master = new master($this->container);
        $data['sid'] = $_SESSION['Sid'];  # 71   $_SESSION['Sid']

        $result = $master->get_master_school_teacher($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_master_school_teacher_dropdown($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 master 年級班級下拉選單";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);

        $result = [];
        $result['Year'] = $admin->getYear($data);
        $result['Semster'] = $admin->getSemster($data);
        $result['Grade'] = $admin->getGrade($data);
        $result['Class'] = $admin->getClass($data);
        $response = $response->withJson($result);
        return $response;
    }

    public function get_master_school_teacher_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 master 學校老師 列表excel";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $master = new master($this->container);
        $data['sid'] = $_SESSION['Sid'];  # 71   $_SESSION['Sid']
        $data['excel'] = 'excel';
        $result = [
            "data" => $master->get_master_school_teacher($data),
            "response" => $response,
            "name" => '學校老師'
        ];
        $response = $admin->getExcel($result);
        // $response = $response->withHeader('Content-type', 'application/json');
        // $response = $response->withJson($result);
        return $response;
    }

    public function export_exam_sample($request, $response, $args) //The sample of uploading teacher data.
    {
        global $container;
        $container->logging = "教師資料批次上傳樣本";

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $rowArray = [
            ["年级", "班级", "姓名", "信箱", "管理碼"],
            ["一", "1", "夏老師", "test@gmail.com", "000001"],
            ["一", "2", "陳老師", "test@gmail.com", "000002"],
            ["一", "3", "王老師", "test@gmail.com", "000003"],
            ["二", "1", "李老師", "test@gmail.com", "000004"],
            ["二", "2", "董老師", "test@gmail.com", "000005"],
            ["三", "1", "龍老師", "test@gmail.com", "000006"],
            ["三", "2", "黃老師", "test@gmail.com", "000007"],
            ["四", "1", "方老師", "test@gmail.com", "000008"]
        ];
        // $response = $response->withHeader('Content-type', 'application/json');
        // $response = $response->withJson($rowArray);
        // return $response;

        $spreadsheet->getActiveSheet()
            ->fromArray(
                $rowArray,   // The data to set
                NULL,        // Array values with this value will not be set
                'A1'         // Top left coordinate of the worksheet range where
                //    we want to set these values (default is A1)
            );
        $spreadsheet->getActiveSheet()->getStyle("A1:E9")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="教師資料批次上傳樣本.xlsx"');
        return $response;
    }
    public function get_export_exam_sample_data($request, $response, $args) //Geting the excel data of teacher data for uploading.
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

    public function post_import_exam_data($request, $response, $args)
    {
        global $container;
        $container->logging = "批次新增老師";

        $data = $request->getParsedBody();
        $master = new master($this->container);
        foreach ($data as $key => $value) {
            $value['Sid'] = $_SESSION['Sid'];  #  $_SESSION['CityName']   '彰化縣'
            // var_dump($value);
            $result = $master->post_import_exam_data($value);
        }
        // exit(0);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_current_rank_person($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 學校端 即時報表";

        $data = $request->getQueryParams();
        $master = new master($this->container);
        $data['Sid'] = $_SESSION['Sid'];  #  $_SESSION['CityName']   '彰化縣'
        $result = $master->get_current_rank_person($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_current_rank_group($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 學校端 即時報表";

        $data = $request->getQueryParams();
        $master = new master($this->container);
        $data['Sid'] = $_SESSION['Sid'];  #  $_SESSION['CityName']   '彰化縣'
        $result = $master->get_current_rank_group($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_current_rank_avg_word($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 學校端 即時報表";

        $data = $request->getQueryParams();
        $master = new master($this->container);
        $data['Sid'] = $_SESSION['Sid'];  #  $_SESSION['CityName']   '彰化縣'
        $result = $master->get_current_rank_avg_word($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_current_rank_avg_word_grades($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 學校端 即時報表";

        $data = $request->getQueryParams();
        $master = new master($this->container);
        $data['Sid'] = $_SESSION['Sid'];  #  $_SESSION['CityName']   '彰化縣'
        $result = $master->get_current_rank_avg_word_grades($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_report_teacher($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 學校端 教師查詢";

        $data = $request->getQueryParams();
        $master = new master($this->container);
        $data['Sid'] = $_SESSION['Sid'];  #  $_SESSION['CityName']   '彰化縣'
        $result_array = array();
        $result = $master->get_report_teacher($data);
        $total = count($result);
        $result_array['data'] = $result;
        $result_array['total'] = $total;
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result_array);
        return $response;
    }
    public function get_report_student($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 學校端 學生查詢";

        $data = $request->getQueryParams();
        $master = new master($this->container);
        $data['Sid'] = $_SESSION['Sid'];  #  $_SESSION['CityName']   '彰化縣'
        $result = $master->get_report_student($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_master_account_data($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 校方端 帳號管理";

        $data = $request->getQueryParams();
        $master = new master($this->container);
        $data['Sid'] = $_SESSION['Sid'];
        $result = $master->get_learn_master_account_data($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_master_account_photo($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 校方圖片";

        $data = $request->getQueryParams();
        $data['Sid'] = $_SESSION['Sid'];

        $master = new master($this->container);
        $result = $master->get_learn_master_account_data($data);

        // var_dump($result);
        // var_dump(is_null($result[0]['Photo']));
        // exit(0);

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

    public function post_learn_master_photo($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 新增校方帳號照片";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $master = new master($this->container);

        $data1 = $data;
        
        unset($data1['inputFile']);
        $data1['Sid'] = $_SESSION['Sid'];

        $photo = $master->get_learn_master_account_data($data1);
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
        $data1['Sid'] = $_SESSION['Sid'];

        $result = $master->post_master_photo($data1);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_learn_master_photo($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 修改校方帳號照片";

        $data = $request->getParsedBody();
        $master = new master($this->container);

        $data['Sid'] = $_SESSION['Sid'];

        $photo = $master->get_learn_master_account_data($data);
        if (!is_null($photo[0]['Photo'])) {
            unlink($this->container->upload_directory . $photo[0]['Photo']);
        }

        $data['photo'] = null;
        $result = $master->post_master_photo($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_learn_master_account_mail($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 修改校方帳號mail";

        $data = $request->getParsedBody();
        $master = new master($this->container);

        $data['Sid'] = $_SESSION['Sid'];
        $result = $master->patch_learn_master_mail($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_learn_master_account_passwd($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 修改校方帳戶密碼";
        $data = $request->getParsedBody();
        $data['Sid'] =  $_SESSION['Sid'];  # 18054   $_SESSION['Tid']
        $master = new master($this->container);
        $check = $master->check_master_origin_passwd($data);
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
        $result = $master->patch_master_passwd($data);
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
    public function get_report_student_assign($request, $response, $args)
    {
        global $container;
        $container->logging = "master 學生成績查詢 歷史成績檢視 小組積分";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        // $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_assign_detail($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_report_student_self($request, $response, $args)
    {
        global $container;
        $container->logging = "master 學生成績查詢 歷史成績檢視 個人積分";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        // $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_self_detail($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_current_rank_avg_word_grades_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 學校端 即時報表 excel";

        $data = $request->getQueryParams();
        $data['Sid'] = $_SESSION['Sid'];
        $admin = new admin($this->container);
        $master = new master($this->container);
        $result = [
            "data" => $master->get_current_rank_avg_word_grades($data),
            "response" => $response,
            "name" => '本學期各年級學習生字量平均數'
        ];
        $response = $admin->getExcel($result);
        return $response;
    }
    public function get_report_teacher_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 學校端 教師查詢 excel";

        $data = $request->getQueryParams();
        $data['Sid'] = $_SESSION['Sid'];
        $admin = new admin($this->container);
        $master = new master($this->container);
        $data['excel'] = 'excel';
        $result = [
            "data" => $master->get_report_teacher($data),
            "response" => $response,
            "name" => '教師查詢'
        ];
        $response = $admin->getExcel($result);
        return $response;
    }
    public function get_report_student_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 學校端 學生查詢 excel";

        $data = $request->getQueryParams();
        $data['Sid'] = $_SESSION['Sid'];
        $admin = new admin($this->container);
        $master = new master($this->container);
        $data['excel'] = 'excel';
        $result = [
            "data" => $master->get_report_student($data),
            "response" => $response,
            "name" => '學生查詢'
        ];
        $response = $admin->getExcel($result);
        return $response;
    }
}
