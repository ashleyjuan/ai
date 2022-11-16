<?php

use Slim\Views\PhpRenderer;

class examcontroller
{
    protected $container;
    public function __construct()
    {
        global $container;
        $this->container = $container;
    }

    public function getExamRM($request, $response, $args)
    {
        global $container;
        $container->logging = "班級施測指導語";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $result = $admin->getExamRM($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getExamRMStudentList($request, $response, $args)
    {
        global $container;
        $container->logging = "班上線上識字量測驗報表";

        $data = $request->getQueryParams();
        $data['tid'] = $_SESSION['Tid'];  # 18054   $_SESSION['Tid']
        $admin = new admin($this->container);
        $data += ["admin" => false];
        $result = $admin->getSingleClassAmount($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_all_data_by_pid($request, $response, $args)
    {
        global $container;
        $container->logging = "班上線上識字量測驗報表";

        $data = $request->getQueryParams();
        $data +=  $_SESSION;  # 18054   $_SESSION['Tid']
        $exam = new exam($this->container);
        $result = $exam->get_all_data_by_pid($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function check_student_id($request, $response, $args)
    {
        global $container;
        $container->logging = "檢查新增學生生分證是否重複";

        $data = $request->getQueryParams();
        $data['sid'] =  $_SESSION['Sid'];  # 11   $_SESSION['Sid']
        $exam = new exam($this->container);
        $result = $exam->check_student_id($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function check_student_stunum($request, $response, $args)
    {
        global $container;
        $container->logging = "檢查新增學生座號是否重複";

        $data = $request->getQueryParams();
        $data['sid'] =  $_SESSION['Sid'];  # 11   $_SESSION['Sid']
        $exam = new exam($this->container);
        $result = $exam->check_student_stunum($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_student($request, $response, $args)
    {
        global $container;
        $container->logging = "個別新增學生";

        $data = $request->getParsedBody();
        $data['tid'] = $_SESSION['Tid'];  # 23153  $_SESSION['Tid']
        $exam = new exam($this->container);
        $Sid = $exam->get_teacher_school($data);

        $data['sid'] = $Sid;

        $stunum_check = $exam->check_student_stunum($data);

        if ($stunum_check["status"] === "failure") {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($stunum_check);
            return $response;
        }
        $birth_check = $exam->check_student_id($data);
        if ($birth_check["status"] === "failure") {
            // $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($birth_check);
            return $response;
        } else {
            // var_dump($data);
            // exit(0);
            $result = $exam->post_student($data);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        }
    }

    public function get_export_student_sample_data($request, $response, $args) //Geting the excel data of teacher data for uploading.
    {
        global $container;
        $container->logging = "學生資料批次上傳匯入檔案";

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

    public function post_import_student_data($request, $response, $args)
    {
        global $container;
        $container->logging = "批次新增學生";

        $data = $request->getParsedBody();
        $exam = new exam($this->container);
        // $max_pid = $exam->max_Pid($data);

        foreach ($data as $key => $value) {

            $data[$key]['tid'] =  $_SESSION['Tid'];  # 21064     $_SESSION['Tid']

            $Sid = $exam->get_teacher_school($data[$key]);
            $data[$key]['sid'] = $Sid;

            $data[$key]['pre_seatnum'] = $data[$key]['seatnum'];  # 21064     $_SESSION['Tid']
            // $data[$key]['pid'] =  $max_pid + $key + 1;  #      $_SESSION['Tid']

            $stunum_check = $exam->check_student_stunum($data[$key]);
            if ($stunum_check["status"] === "failure") {
                // $response = $response->withStatus(500);
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($stunum_check);
                return $response;
            }
            $birth_check = $exam->check_student_id($data[$key]);
            if ($birth_check["status"] === "failure") {
                // $response = $response->withStatus(500);
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($birth_check);
                return $response;
            } else {
                $result = $exam->post_import_student_data($data);
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_teacher_class($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 轉出學生老師名字、班級";

        $data = $request->getQueryParams();
        $data['tid'] =  $_SESSION['Tid'];  # 18054   $_SESSION['Tid']
        $exam = new exam($this->container);
        $result = $exam->get_teacher_class($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_tea_data($request, $response, $args)
    {
        global $container;
        $container->logging = "老師名稱";

        $data = $request->getQueryParams();
        $data['tid'] = $_SESSION['Tid'];  # 18054   $_SESSION['Tid']
        $exam = new exam($this->container);
        $result = $exam->get_tea_data($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_exam_transfer_student($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 轉出頁面學生列表";

        $data = $request->getQueryParams();
        $data['tid'] =  $_SESSION['Tid'];  # 18054   $_SESSION['Tid']
        $exam = new exam($this->container);
        $result = $exam->get_exam_transfer_student($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_exam_transfer_student_out($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 轉出頁面學生列表轉出功能";

        $data = $request->getParsedBody();
        $exam = new exam($this->container);
        foreach ($data as $key => $value) {
            $result = $exam->patch_exam_transfer_student_out($value);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function check_exam_transfer_in_student_stunum($request, $response, $args)
    {
        global $container;
        $container->logging = "校方轉入尋找是否有此學生";
        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $result = $exam->check_exam_transfer_in_student_stunum($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_exam_transfer_student_in($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 轉出頁面學生列表轉出功能";

        $data = $request->getParsedBody();
        $data['tid'] =  $_SESSION['Tid'];  # 18054   $_SESSION['Tid']
        $exam = new exam($this->container);
        $result = $exam->check_exam_transfer_in_student_stunum($data);
        if ($result == 0) {
            return [
                "message" => "找不到該學號學生，請檢查後再轉入。"
            ];
        }
        $result = $exam->patch_exam_transfer_student_in($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function check_exam_origin_passwd($request, $response, $args)
    {
        global $container;
        $container->logging = "施測人員原本密碼";
        $data['tid'] =  $_SESSION['Tid'];  # 18054   $_SESSION['Tid']
        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $result = $exam->check_exam_origin_passwd($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_exam_passwd($request, $response, $args)
    {
        global $container;
        $container->logging = "修改施測人員密碼";
        $data = $request->getParsedBody();
        $data['tid'] =  $_SESSION['Tid'];  # 18054   $_SESSION['Tid']
        $exam = new exam($this->container);
        $check = $exam->check_exam_origin_passwd($data);
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
        $result = $exam->patch_exam_passwd($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_exam_openset($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 設定是否開放學生施測";


        $data = $request->getQueryParams();
        $data['tid'] =  $_SESSION['Tid'];  # 18054   $_SESSION['Tid']
        $exam = new exam($this->container);
        $result = $exam->get_exam_openset($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_exam_openset($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 修改是否開放施測";

        $data = $request->getParsedBody();
        $data['tid'] =  $_SESSION['Tid'];  # 18054   $_SESSION['Tid']
        $exam = new exam($this->container);
        $result = $exam->patch_exam_openset($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_q_select($request, $response, $args)
    {
        global $container;
        $container->logging = "班上線上識字量測驗報表";

        $data = $request->getQueryParams();
        $data['tid'] =  $_SESSION['Tid'];  # 18054   $_SESSION['Tid']
        $exam = new exam($this->container);
        $result = $exam->get_q_select($data);
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

    public function get_exam_flag($request, $response, $args)
    {
        global $container;
        $container->logging = "班上線上識字量測驗報表";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $result = $exam->get_exam_flag($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_exam_choose_title($request, $response, $args)
    {
        global $container;
        $container->logging = "班上線上識字量測驗報表";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data += $_SESSION;
        $result = $exam->get_exam_choose_title($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_exam_choose_info($request, $response, $args)
    {
        global $container;
        $container->logging = "班上線上識字量測驗報表";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $result = $exam->get_exam_choose_info($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_exam_student_data($request, $response, $args)
    {
        global $container;
        $container->logging = "get_exam_student_data";
        $exam = new exam($this->container);
        $data = $request->getQueryParams();

        $result = $exam->get_exam_student_data($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_exam_data($request, $response, $args)
    {
        global $container;
        $container->logging = "get_exam_data";
        $exam = new exam($this->container);
        $data = $request->getQueryParams();

        $result = $exam->get_exam_data($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_analysis_pi($request, $response, $args)
    {
        global $container;
        $container->logging = "get_analysis_pi";
        $exam = new exam($this->container);
        $data = $request->getQueryParams();

        $result = $exam->get_analysis_pi($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function base_question_number($request, $response, $args)
    {
        global $container;
        $container->logging = "線上施測不計分問題";
        $exam = new exam($this->container);
        $data = $request->getQueryParams();
        $result = $exam->base_question_number($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function calculate_analysis_pi($request, $response, $args)
    {
        global $container;
        $container->logging = "線上施測分數計算";
        $exam = new exam($this->container);
        $data = $request->getParsedBody();
        $result = $exam->calculate_analysis_pi($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_school_exam($request, $response, $args)
    {
        global $container;
        $container->logging = "get_school_exam";
        $exam = new exam($this->container);
        $data = $request->getQueryParams();
        $data['tid'] = $_SESSION['Tid'];
        $result = $exam->getSchoolDataName($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_student_info($request, $response, $args)
    {
        global $container;
        $container->logging = "get_student_info";
        $exam = new exam($this->container);
        $data = $request->getQueryParams();
        $result = $exam->get_student_info($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_learn_group_student_assign($request, $response, $args)
    {
        global $container;
        $container->logging = "自行分組";

        $data = $request->getParsedBody();
        $exam = new exam($this->container);
        $data["Tid"] = $_SESSION['Tid'];
        $result = $exam->post_learn_group($data);
        if ($result["status"] === "failure") {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        }
        $result = $exam->post_learn_group_student_assign($result);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_learn_group_student_random($request, $response, $args)
    {
        global $container;
        $container->logging = "隨機分組";

        $data = $request->getParsedBody();
        $exam = new exam($this->container);
        $admin = new admin($this->container);
        $data["Tid"] = $_SESSION['Tid'];
        $data["tid"] = $_SESSION['Tid'];
        $data['Pid'] = $admin->getSingleClassAmount($data);
        $result = $exam->random_learn_group_student($data);
        foreach ($result as $key => $value) {
            $data["GroupName"] = date('YmdHis');
            $data["Pid"] = $value;
            $result = $exam->post_learn_group($data);
            if ($result["status"] === "failure") {
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($result);
                return $response;
            }
            $result = $exam->post_learn_group_student_assign($result);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_class_student($request, $response, $args)
    {
        global $container;
        $container->logging = "班上學生名單";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data["tid"] = $_SESSION['Tid'];
        $result = $exam->get_learn_class_student($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_teacher_group($request, $response, $args)
    {
        global $container;
        $container->logging = "班上小組名單";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data["tid"] = $_SESSION['Tid'];
        $result = $exam->get_learn_teacher_group($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_learn_teacher_group($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 刪除分組";

        $data = $request->getParsedBody();
        $exam = new exam($this->container);
        foreach ($data['GroupId'] as $key => $value) {
            $input['GroupId'] = $value;
            // var_dump($input);
            $result = $exam->delete_learn_teacher_group($input);
            if ($result['status'] === 'failure') {
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($result);
                return $response;
            }
        }
        // exit(0);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_learn_class_student($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 編輯分組";

        $data = $request->getParsedBody();
        $exam = new exam($this->container);
        $data["Tid"] = $_SESSION['Tid'];
        $result = $exam->patch_learn_class_student($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_class_student_count($request, $response, $args)
    {
        global $container;
        $container->logging = "班上學生數量";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data["tid"] = $_SESSION['Tid'];
        $result = $exam->get_learn_class_student_count($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_class_student_group($request, $response, $args)
    {
        global $container;
        $container->logging = "班上學生群組";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data["tid"] = $_SESSION['Tid'];
        $result = $exam->get_learn_class_student_group($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_teacher_task($request, $response, $args)
    {
        global $container;
        $container->logging = "班上老師任務";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data["tid"] = $_SESSION['Tid'];
        $result = $exam->get_teacher_task($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function post_task($request, $response, $args)
    {
        global $container;
        $container->logging = "新增任務";

        $data = $request->getParsedBody();
        $exam = new exam($this->container);
        $student = new student($this->container);

        $data['tid'] = $_SESSION['Tid'];

        if (count($data['wordlist']) > 5) {
            return [
                "status" => "failure",
                "message" => "任務只能新增5個生字，請減少生字數量!"
            ];;
        }

        $task_insert = $exam->post_task($data);
        if ($task_insert['status'] === 'failure') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($task_insert);
            return $task_insert;
        }

        $data['task_id'] = $task_insert['TaskId'];
        $group['task_id'] = $data['task_id'];
        foreach ($data['group_id'] as $key => $value) {
            $group['group_id'] = $data['group_id'][$key];
            $task_group_insert = $exam->post_task_group($group);
            if ($task_group_insert['status'] === 'failure') {
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($task_group_insert);
                return $task_group_insert;
            }
        }

        $task_word['task_id'] = $data['task_id'];
        foreach ($data['wordlist'] as $key => $value) {
            $word['word'] = $data['wordlist'][$key];
            $nid = $student->get_word_nid($word);
            $task_word['nid'] = $nid['nid'];
            $task_word_insert = $exam->post_task_word($task_word);

            if ($task_word_insert['status'] === 'failure') {
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($task_word_insert);
                return $task_word_insert;
            // foreach ($data['group_id'] as $key1 => $value) {
            //     $task_word['group_id'] = $data['group_id'][$key1];
            //     $word['word'] = $data['wordlist'][$key];
            //     $nid = $student->get_word_nid($word);
            //     $task_word['nid'] = $nid['nid'];
                
            //     // var_dump($task_word);
            //     $task_word_insert = $exam->post_task_word($task_word);

            //     if ($task_word_insert['status'] === 'failure') {
            //         $response = $response->withHeader('Content-type', 'application/json');
            //         $response = $response->withJson($task_word_insert);
            //         return $task_word_insert;
            //     }
            //     $result['message'] = '新增任務生字成功';
            }
            $result['message'] = '新增任務生字成功';
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_task($request, $response, $args)
    {
        global $container;
        $container->logging = "修改任務";

        $data = $request->getParsedBody();
        $exam = new exam($this->container);
        $student = new student($this->container);

        $data['tid'] = $_SESSION['Tid'];

        if (count($data['wordlist']) > 5) {
            return [
                "status" => "failure",
                "message" => "任務只能包含5個生字，請減少生字數量!"
            ];;
        }

        $task_insert = $exam->patch_task($data);
        if ($task_insert['status'] === 'failure') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($task_insert);
            return $task_insert;
        }

        $result = $exam->delete_task_group($data);

        $group['task_id'] = $data['TaskId'];
        foreach ($data['group_id'] as $key => $value) {
            $group['group_id'] = $data['group_id'][$key];
            $task_group_insert = $exam->post_task_group($group);
            if ($task_group_insert['status'] === 'failure') {
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($task_group_insert);
                return $task_group_insert;
            }
        }

        $result = $exam->delete_task_word($data);

        $task_word['task_id'] = $data['TaskId'];
        foreach ($data['wordlist'] as $key => $value) {
            $word['word'] = $data['wordlist'][$key];
            $nid = $student->get_word_nid($word);
            $task_word['nid'] = $nid['nid'];
            $task_word_insert = $exam->post_task_word($task_word);

            if ($task_word_insert['status'] === 'failure') {
                $response = $response->withHeader('Content-type', 'application/json');
                $response = $response->withJson($task_word_insert);
                return $task_word_insert;
            }
            $result['message'] = '修改任務生字成功';
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_task_management_highlight($request, $response, $args)
    {
        global $container;
        $container->logging = "任務管理 老師指派-任務列表 標記";

        $data = $request->getParsedBody();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->patch_task_management_highlight($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_word_list($request, $response, $args)
    {
        global $container;
        $container->logging = "learn exam 任務生字預覽";

        // $data = $request->getParsedBody();

        $data = $request->getQueryParams();
        $exam = new exam($this->container);

        foreach ($data['wordlist'] as $key => $value) {
            $word['word'] = $data['wordlist'][$key];
            $list = $exam->get_learn_word_list($word);
            foreach ($list as $list_key => $list_value)
                $result[] = $list_value;
        }

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_word_list_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "learn exam 任務生字預覽 excel";

        $data = $request->getQueryParams();
        $admin = new admin($this->container);
        $exam = new exam($this->container);

        // $data['wordlist'] = ["我","好","不"];
        // $data['wordlist'] = ["好","不"];
        $word['excel'] = 'excel';
        // var_dump($data);
        // exit(0);
        foreach ($data['wordlist'] as $key => $value) {
            $word['word'] = $data['wordlist'][$key];
            $list = $exam->get_learn_word_list($word);
            foreach ($list as $list_key => $list_value)
                $return[] = $list_value;
        }

        $result = [
            "data" => $return,
            "response" => $response,
            "name" => '任務生字'
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function get_stu_data($request, $response, $args)
    {
        global $container;
        $container->logging = "班級學生列表";

        $data['tid'] = $_SESSION['Tid']; #  18054  $_SESSION['Tid']
        $exam = new exam($this->container);
        $result = $exam->get_stu_data($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_stu_data_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "班上學生成績報表excel";

        $data = $request->getQueryParams();
        $data['tid'] = $_SESSION['Tid']; #18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $exam = new exam($this->container);
        $data['excel'] = 'excel';
        $result = [
            "data" => $exam->get_stu_data($data),
            "response" => $response,
            "name" => '學生資料'
        ];
        $response = $admin->getExcel($result);
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

    public function get_task($request, $response, $args)
    {
        global $container;
        $container->logging = "任務管理 指派任務列表";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_task_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "指派任務列表excel";

        $data = $request->getQueryParams();
        $data['Tid'] = $_SESSION['Tid']; #18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $exam = new exam($this->container);
        $data['excel'] = 'excel';
        $result = [
            "data" => $exam->get_task($data),
            "response" => $response,
            "name" => '指派任務'
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function get_task_group($request, $response, $args)
    {
        global $container;
        $container->logging = "任務管理 指派任務列表 指派小組";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_group($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_task_word_progress($request, $response, $args)
    {
        global $container;
        $container->logging = "任務管理 指派任務列表 生字完成狀態";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_word_progress($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function delete_task($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 刪除任務";

        $data = $request->getParsedBody();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];

        foreach ($data['TaskId'] as $key => $value) {
            $data['now_TaskId'] = $data['TaskId'][$key];
            $result = $exam->delete_task($data);
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_student($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 學生學習成績查詢";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_learn_student($data);
        $total = count($result);
        $result_array['data'] = $result;
        $result_array['total'] = $total;
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result_array);
        return $response;
    }

    public function get_learn_student_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "班上學生成績報表excel";

        $data = $request->getQueryParams();
        $data['Tid'] = $_SESSION['Tid']; #18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $exam = new exam($this->container);
        $data['excel'] = 'excel';
        $result = [
            "data" => $exam->get_learn_student($data),
            "response" => $response,
            "name" => '學生學習成績'
        ];

        $response = $admin->getExcel($result);
        return $response;
    }

    public function get_task_group_member($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 學生學習成績查詢 小組成員";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_group_member($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function exam_go_student($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 前往學生";
        $exam = new exam($this->container);
        $data = $request->getQueryParams();
        session_start();
        $result = $exam->exam_go_student($data);
        session_write_close();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_task_assign($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 學生學習成績查詢 教師指派任務";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_assign($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_task_self($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 學生學習成績查詢 自主學習任務";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_self($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function check_exam($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 原本密碼";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $result = $exam->check_exam($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function exam_SendMail($request, $response, $args)
    {
        global $container;
        $container->logging = "寄送系統通知信";

        $exam = new exam($this->container);
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

        $check = $exam->check_exam($data);
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

        $change = $exam->patch_exam_tmp_passwd($check[0]);
        if ($change['status'] == 'failure') {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($change);
            return $response;
        }

        $result = $exam->exam_SendMail($check[0]);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_task_learned_word($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 學生學習成績查詢 學習生字清單";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_learned_word($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_task_self_detail($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 學生學習成績查詢 歷史成績檢視 個人積分";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        // $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_self_detail($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_task_assign_detail($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 學生學習成績查詢 歷史成績檢視 小組積分";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        // $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_assign_detail($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_performance_task($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 任務學習成果查詢 任務資料";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result_array = array();
        $result = $exam->get_performance_task($data);
        $total = count($result);
        $result_array['data'] = $result;
        $result_array['total'] = $total;
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result_array);
        return $response;
    }

    public function get_performance_task_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "learn 任務學習成果查詢 excel";

        $data = $request->getQueryParams();
        $data['Tid'] = $_SESSION['Tid']; #18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $exam = new exam($this->container);
        $data['excel'] = 'excel';
        $result = [
            "data" => $exam->get_performance_task($data),
            "response" => $response,
            "name" => '任務學習成績'
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function get_performance_task_group_member($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 任務學習成果查詢 指派小組";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_group($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_performance_task_group_finish($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 任務學習成果查詢 任務完成率";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_performance_task_group_finish($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_word_performance($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 生字學習成果查詢 生字資料";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_word_performance($data);
        $total = count($result);
        $result_array['data'] = $result;
        $result_array['total'] = $total;
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result_array);
        return $response;
    }

    public function get_word_performance_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 生字學習成果查詢 生字資料excel";

        $data = $request->getQueryParams();
        $data['Tid'] = $_SESSION['Tid']; #18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $exam = new exam($this->container);
        // $data['excel'] = 'excel';
        $result = [
            "data" => $exam->get_word_performance_excel($data),
            "response" => $response,
            "name" => '生字學習成果'
        ];
        $response = $admin->getExcel($result);
        return $response;
    }

    public function get_word_performance_group($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 生字學習成果查詢 指派小組";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_group($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_word_performance_unfinish($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 生字學習成果查詢 未完成學生";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_word_performance_unfinish($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_group_member($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 小組學習成果查詢 小組成員 ";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_learn_group_member($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_group_select($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 小組學習成果查詢 查詢 ";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_learn_group_select($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }


    public function get_learn_group_select_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "班上學生成績報表excel";

        $data = $request->getQueryParams();
        $data['Tid'] = $_SESSION['Tid']; #18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $exam = new exam($this->container);
        // $data['excel'] = 'excel';
        // $in = $exam->get_learn_group_select_excel($data);
        // var_dump($in);
        // exit(0);
        $result = [
            "data" => $exam->get_learn_group_select_excel($data),
            "response" => $response,
            "name" => '小組學習成果'
        ];
        // var_dump($result['data']);
        // exit(0);
        $response = $admin->getExcel($result);
        return $response;
    }

    public function get_learn_mission_list($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 小組學習成果查詢 任務清單 ";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $flag = false;
        $result = $exam->get_learn_mission_list($data, $flag);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_learn_not_mission_list($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 小組學習成果查詢 小組未完成任務 ";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $flag = true;
        $result = $exam->get_learn_mission_list($data, $flag);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_learn_group_select_count($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 小組學習成果查詢 查詢人數 ";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $flag = true;
        $result = $exam->get_learn_group_select_count($data, $flag);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_learn_group_task($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 學生群組 未完成任務清單";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $flag = true;
        $result = $exam->get_learn_group_task($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_learn_group_task_unfinish($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 學生群組 未完成任務清單";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $flag = true;
        $result = $exam->get_learn_group_task_unfinish($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_task_download($request, $response, $args)
    {
        global $container;
        $container->logging = "student 筆順下載紀錄 ";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $flag = true;
        $result = $exam->get_task_download($data);
        $total = count($result);
        $result_array['data'] = $result;
        $result_array['total'] = $total;
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result_array);
        return $response;
    }

    public function get_task_download_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "班上筆順下載紀錄報表excel";

        $data = $request->getQueryParams();
        $data['Tid'] = $_SESSION['Tid']; #18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $exam = new exam($this->container);
        $data['excel'] = 'excel';
        $result = [
            "data" => $exam->get_task_download($data),
            "response" => $response,
            "name" => '筆順下載紀錄'
        ];

        $response = $admin->getExcel($result);
        return $response;
    }

    public function get_exam_report_score_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "班上學生成績報表excel";

        $data = $request->getQueryParams();
        $data['tid'] = $_SESSION['Tid']; #18054  $_SESSION['Tid']
        $admin = new admin($this->container);
        $exam = new exam($this->container);

        $result = [
            "data" => $exam->get_task_download($data),
            "response" => $response
        ];
        $response = $admin->getExcel($result);
        return $response;
    }
    public function get_manage_identity($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 登入頁面 身份";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_manage_identity($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_manage_current($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 登入頁面 當期指派任務完成情形";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_manage_current($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_manage_current_excel($request, $response, $args)
    {
        global $container;
        $container->logging = "班上學生成績報表excel";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $admin = new admin($this->container);
        $result = [
            "data" => $exam->get_manage_current_excel($data),
            "response" => $response,
            "name" => '當期指派任務完成情形'
        ];
        // var_dump($result['data']);
        // exit(0);
        $response = $admin->getExcel($result);
        return $response;
    }

    public function get_manage_group_member($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 登入頁面 指派小組";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_group($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_manage_group_unfinish_num($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 登入頁面 未完成任務數";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_manage_group_unfinish_num($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_manage_mission_word($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 登入頁面 任務生字未完成者";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_manage_mission_word($data);
        $result_arr['word'] = $data['word'];
        foreach ($result as $key => $value) {
            $result_arr['StuName'] = $value['StuName'];
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result_arr);
        return $response;
    }
    public function get_learn_analysis_assign($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 教師端 數據管理系統 教師指派";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_learn_analysis_assign($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_analysis_self($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 教師端 數據管理系統 自主學習";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_learn_analysis_self($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_online_student($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 線上學生";
        $exam = new exam($this->container);
        $data = $request->getQueryParams();
        // $data = $request->getParsedBody();
        // $data['Tid'] = $_SESSION['Tid'];

        $logs = $exam->get_online_student($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($logs);
        return $response;
    }

    public function get_word_library($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 exam 查字";

        $data = $request->getQueryParams();
        $student = new student($this->container);
        $result = $student->get_word_library($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_task_download_group($request, $response, $args)
    {
        global $container;
        $container->logging = "成績管理 筆順下載紀錄 指派小組";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_group($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_exam_account_data($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 老師端 帳號管理";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_learn_exam_account_data($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_exam_account_photo($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 老師圖片";

        $data = $request->getQueryParams();
        $data['Tid'] = $_SESSION['Tid'];
        $exam = new exam($this->container);
        $result = $exam->get_learn_exam_account_data($data);
        // var_dump($result[0]['Photo']);
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

    public function post_learn_exam_photo($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 新增老師帳號照片";

        $data = $request->getParsedBody();
        $admin = new admin($this->container);
        $exam = new exam($this->container);

        $data1 = $data;
        unset($data1['inputFile']);
        $data1['Tid'] = $_SESSION['Tid'];
        $photo = $exam->get_learn_exam_account_data($data1);
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
        $data1['Tid'] = $_SESSION['Tid'];

        $result = $exam->post_exam_photo($data1);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_learn_exam_photo($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 修改管理者帳號照片";

        $data = $request->getParsedBody();
        $exam = new exam($this->container);

        $data['Tid'] = $_SESSION['Tid'];
        $photo = $exam->get_learn_exam_account_data($data);
        if (!is_null($photo[0]['Photo'])) {
            unlink($this->container->upload_directory . $photo[0]['Photo']);
        }

        $data['photo'] = null;
        $result = $exam->post_exam_photo($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function get_learn_words_practice_pdf($request, $response, $args)
    {
        global $container;
        $container->logging = "exam 拿字生字練習簿";

        $data = $request->getQueryParams();
        $student = new student($this->container);
        $exam = new exam($this->container);
        $data['words'] = $exam->get_task_learned_words_teacher($data);
        $response->getBody()->write($student->get_words_practice_pdf($data));
        $response = $response->withHeader('Content-type', 'application/octet-stream');
        $response = $response->withHeader('Content-Disposition', 'attachment;filename=生字練習簿.pdf');
        return $response;
    }

    public function patch_learn_exam_account_mail($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 修改管理者帳號mail";

        $data = $request->getParsedBody();
        $exam = new exam($this->container);

        $data['tid'] = $_SESSION['Tid'];
        $result = $exam->patch_learn_exam_mail($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patch_learn_exam_account_passwd($request, $response, $args)
    {
        global $container;
        $container->logging = "學習島嶼 修改老師帳戶密碼";
        $data = $request->getParsedBody();
        $data['tid'] =  $_SESSION['Tid'];  # 18054   $_SESSION['Tid']
        $exam = new exam($this->container);
        $check = $exam->check_exam_origin_passwd($data);
        // var_dump($check['status'] == 'failure');
        // exit(0);
        if ($check['status'] == 'failure') {
            $return = [
                "status" => "failure",
                "message" => "請輸入原密碼!"
            ];
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($return);
            return $response;
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
        $result = $exam->patch_exam_passwd($data);
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
    public function get_task_download_download($request, $response, $args)
    {
        global $container;
        $container->logging = "成績管理 筆順下載紀錄 筆順下載紀錄";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        // $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_download_download($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_task_task($request, $response, $args)
    {
        global $container;
        $container->logging = "任務管理 任務列表 查看編輯資料";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        // $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_task_task($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_change_role($request, $response, $args)
    {
        global $container;
        $container->logging = "帳號管理 角色變更 學生資料";

        $data = $request->getQueryParams();
        $exam = new exam($this->container);
        $data['Tid'] = $_SESSION['Tid'];
        $result = $exam->get_change_role($data);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
}
