<?php
date_default_timezone_set('prc');

use PHPMailer\PHPMailer\PHPMailer;
use Slim\Views\PhpRENDerer;
use Slim\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class exam
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getSchoolDataName($data)
    {
        $values = [
            "tid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [SchoolList].[Sid],[CityId],[PostId],[SchoolID]
                ,[SchoolName],[SchoolList].[Class],[SchoolList].[PassWD],[PassWD_MD5]
                ,[SchoolList].[PWHis],[SchoolList].[PassWD_ChangeDate],[SchoolList].[Used],[ExamPower]
                ,[Principal],[Senate],[Contact],[Contact_Titles]
                ,[Contact_Phone],[Contact_CellPhone],[Contact_EMail_1],[Contact_EMail_2]
                ,[ApplyFiles],[SchoolList].[AddTime],[SchoolList].[UpdateTime],[SendPWTime]
                ,[MsgRM],[MasterRM],[UpInfo],[ExamProgramKind],[SchDelFlag]
            
                FROM [Literacy].[dbo].[SchoolList]
                LEFT JOIN [Literacy].[dbo].[Teacher] ON Teacher.Sid = SchoolList.Sid 
                WHERE Tid = :tid
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_q_select($data)
    {
        $values = [
            "Pid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT *,[StuName] AS [lbl_Student_StuName],  '（座號 : ' + [SeatNum] + '）' AS [lbl_Student_SeatNum]
            FROM Student 
            WHERE Pid = :Pid";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure"
            ];
        }
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_student_info($data)
    {
        $values = [
            "Pid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT Student.*, Teacher.Grade, Teacher.Class 
            FROM Student 
            INNER JOIN Teacher ON Student.Tid = Teacher.Tid 
            WHERE Student.Pid = :Pid";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure"
            ];
        }
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_exam_info($data)
    {
        $sql = "SELECT 
            (SELECT [OptionValues] FROM Options WHERE (OptionItem = 'Exam_Year')) AS [Year],
            (SELECT [OptionValues] FROM Options WHERE (OptionItem = 'Exam_Term')) AS [Term],
            (SELECT [OptionValues] FROM Options WHERE (OptionItem = 'Exam_TKind')) AS [TKind],
            (SELECT [OptionValues] FROM Options WHERE (OptionItem = 'Exam_Year')) + '學年度 ' + (SELECT [OptionValues] FROM Options WHERE (OptionItem = 'Exam_Term')) + '學期 ' + (SELECT [OptionValues] FROM Options WHERE (OptionItem = 'Exam_TKind')) AS [lbl_Exam_Time]
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute()) {
            return [
                "status" => "failure"
            ];
        }
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_exam_flag($data)
    {
        $sql = "SELECT CASE WHEN (DATEDIFF(day,CURRENT_TIMESTAMP,[OItemDateS])<=0) AND (DATEDIFF(day,CURRENT_TIMESTAMP,[OItemDateE])>=0) THEN 1 ELSE 0 END flag,
            state,
            '抱歉，目前非施測期間。' as message_flag,
            '目前施測系統關閉中。' as message_state
            FROM Options WHERE (OptionItem = 'Exam')
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute()) {
            return [
                "status" => "failure"
            ];
        }
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_exam_choose_info($data)
    {
        $values = [
            "Pid" => '',
            "Year" => '',
            "Term" => '',
            "TKind" => '',
            "ExamProgramKind" => '',
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT * 
            FROM Exam_Word_Score 
            WHERE Pid = :Pid
                AND Exam_Year = :Year
                AND Exam_Term = :Term
                AND Exam_TKind = :TKind
                AND ExamProgramKind = :ExamProgramKind
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure"
            ];
        }
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


    public function get_exam_student_data($data)
    {
        $values = [
            "p_id" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $schooSql = "SELECT Teacher.Grade, Teacher.Class, Student.StuName, Student.StuNum
                    FROM Student 
                    INNER JOIN Teacher ON Student.Tid = Teacher.Tid  
                    WHERE Student.Pid = :p_id
                    ";

        $sth = $this->container->db->prepare($schooSql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function get_exam_data($data)
    {
        $values = [
            "grade" => '',
            "EPKind" => '',
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $schooSql = "SELECT * 
                    FROM [Exam_Word_CV_2] 
                    WHERE ([Grade] = :grade) AND ([Scoring] = :EPKind) 
                    Order By LiteracyScore ASC
                    ";

        $sth = $this->container->db->prepare($schooSql);
        $sth->execute($values);
        $result['myCommand_NoScoringWord'] = $sth->fetchAll(PDO::FETCH_ASSOC);


        $schooSql = "SELECT Top (3) * 
                    FROM [Exam_Word_CV_2] 
                    WHERE ([ThreeWord_C] = 'Y') 
                    Order By ThetaMax ASC
                    ";

        $sth = $this->container->db->prepare($schooSql);
        $sth->execute();
        $result['CommandText_C'] = $sth->fetchAll(PDO::FETCH_ASSOC);

        $values = [
            "grade" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $schooSql = "SELECT Top (3) * 
                    FROM [Exam_Word_CV_2] 
                    WHERE ([Grade] = :grade) AND ([ThreeWord] = 'Y') 
                    Order By ThetaMax ASC
                    ";

        $sth = $this->container->db->prepare($schooSql);
        $sth->execute($values);
        $result['CommandText'] = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function check_exam_origin_passwd($data)
    {
        $values = [
            "tid" => '',
            "passwd_old" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT * 
                FROM [Literacy].[dbo].[Teacher] 
                WHERE [Tid] = :tid
                AND [PassWD] = :passwd_old
            ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            $status = [
                "status" => "failure"
            ];
        }
        $status = ["status" => "success"];
        return $status;
    }

    public function get_exam_openset($data)
    {
        $values = [
            "tid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT * 
                FROM [Teacher] 
                WHERE [Tid] = :tid
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function check_exam_transfer_in_student_stunum($data)
    {
        $values = [
            "sid" => '',
            "stunum" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT COUNT(*) 
                FROM [Student] 
                WHERE [StuNum] = :stunum
                AND [Sid] = :sid
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchColumn(0);
        return $result;
    }

    public function patch_exam_openset($data)
    {
        $values = [
            "tid" => '',
            "openset" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "Update [Teacher] Set 
                [OpenSet] = :openset
                WHERE [Tid] = :tid
                ";

        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure"
            ];
        }
        return [
            "status" => "success"
        ];
    }

    public function patch_exam_passwd($data)
    {
        $values = [
            "tid" => '',
            "passwd" => '',
            "passwd_again" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (!$values['passwd'] == $values['passwd_again']) {
            return [
                "status" => "failure",
                "message" => "請檢查「確認密碼」是否有誤!"
            ];
        } else {
            unset($values['passwd_again']);

            $sql = "Update [Teacher] Set 
                    [PassWD] = :passwd
                    , [PassWD_ChangeDate] = GETDATE()
                    WHERE [Tid] = :tid
            ";
        }

        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "message" => "修改失敗"
            ];
        }
        return [
            "status" => "success",
            "message" => "修改成功"
        ];
    }

    public function check_student_id($data)
    {

        if (strlen($data['idnumber']) != 6) {
            return [
                "status" => "failure",
                "message" => "請檢查身分證後六碼，是否超過或小於六位元",
                "long" => strlen($data['idnumber'])
            ];
        }

        $values = [
            "sid" => '',
            "idnumber" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT *
                FROM [Literacy].[dbo].[Student]
                WHERE [IDNumber] = :idnumber 
                AND [Sid] = :sid
        ";

        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($result)) {
            return [
                "status" => "failure",
                "message" => "經系統檢查, 目前資料庫已有身份證後六碼為{$values['idnumber']}的學生存在系統，請與平台管理人員聯絡"
            ];
        } else {
            return ["status" => "success"];
        }
    }

    public function check_student_stunum($data)
    {
        $values = [
            "stunum" => '',
            "sid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT * 
                FROM [Literacy].[dbo].[Student]
                WHERE [StuNum] = :stunum 
                AND [Sid] = :sid
        ";

        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($result)) {
            return [
                "status" => "failure",
                "message" => "學號「{$values['stunum']}」已存在，請確認此學生是否已存在，避免新增多筆重覆學生資料。"
            ];
        } else {
            return ["status" => "success"];
        }
    }

    public function get_teacher_school($data)
    {
        $values = [
            "tid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Sid] 
                FROM [Literacy].[dbo].[Teacher]
                WHERE [Tid] = :tid
        ";

        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetchColumn(0);
        return $result;
    }

    // public function max_Pid($data)
    // {
    //     $sql = "SELECT MAX([Pid]) MaxNumber
    //             FROM [Literacy].[dbo].[Student]
    //     ";
    //     $sth = $this->container->db->prepare($sql);
    //     $sth->execute();
    //     $result = $sth->fetchColumn(0);
    //     return $result;
    // }

    public function post_student($data)
    {
        $values = [
            'sid' => '',
            'tid' => '',
            'year' => '',
            'stuname' => '',
            'stunum' => '',
            'idnumber' => '',
            'seatnum' => '',
            'birth' => '',
            'sex' => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $values['seatnum1'] = $data['seatnum'];

        $sql = "INSERT INTO [Student] (
                    [Sid], [Tid], [Year], [StuName], 
                    [IDNumber], [StuNum], [SeatNum], [Pre_SeatNum],
                    [Birth], [Sex] )
                VALUES (:sid, :tid, :year, :stuname, 
                    :idnumber, :stunum, :seatnum, :seatnum1, 
                    :birth, :sex )";

        $sth = $this->container->db->prepare($sql);
        if ($sth->execute($values)) {
            return [
                "status" => "success",
                "message" => "新增成功"
            ];
        } else {
            return [
                "status" => "failure",
                "message" => "新增失敗",
                "error" => $sth->errorInfo()
            ];
        }
    }

    public function get_teacher_class($data)
    {
        $values = [
            "tid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT * 
                FROM [Teacher] 
                WHERE [Tid] = :tid
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_exam_transfer_student($data)
    {
        $values = [
            "tid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT * 
                FROM [Student] 
                WHERE [Tid] = :tid
                ORDER BY CAST([SeatNum] AS int)
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_tea_data($data)
    {
        $values = [
            "tid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }


        $sql = "SELECT [Sid], [TeacherName]
                , [Grade], [Class]
                FROM [Teacher] 
                WHERE [Tid] = :tid
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function patch_exam_transfer_student_out($data)
    {
        $values = [
            "pre_grade" => '',
            "pre_class" => '',
            'pid' => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[Student] 
                Set [Tid] = 0
                , [Pre_Grade] = :pre_grade
                , [Pre_Class] = :pre_class
                WHERE [Pid] = :pid
            ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            $status = [
                "status" => "failure",
                "message" => "修改失敗"
            ];
            return $status;
        }
        $status = [
            "status" => "success",
            "message" => "修改成功"

        ];
        return $status;
    }

    public function patch_exam_transfer_student_in($data)
    {
        $values = [
            "tid" => '',
            "stunum" => '',
            'sid' => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[Student] 
                Set [Tid] = :tid
                WHERE [StuNum] = :stunum
                AND [Sid] = :sid
            ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            $status = [
                "status" => "failure"
            ];
            return $status;
        }
        $status = [
            "status" => "success",
            "status" => "已轉入學生，StuNum = {$values['stunum']}"
        ];
        return $status;
    }

    public function post_import_student_data($data)
    {
        $string = "";
        $value_array = [];
        foreach ($data as $key => $value) {
            $values = [
                'sid' => '',
                'tid' => '',
                'year' => '',
                'stuname' => '',
                'idnumber' => '',
                'stunum' => '',
                'seatnum' => '',
                'pre_seatnum' => '',
                'birth' => '',
                'sex' => ''
            ];

            $string .= "( ";
            foreach ($values as $values_key => $values_value) {
                $values[$values_key . "_{$key}"] = $values_value;
                $string .= ":{$values_key}_{$key},";
                if (array_key_exists($values_key, $value)) {
                    $values[$values_key . "_{$key}"] = $data[$key][$values_key];
                }
                unset($values[$values_key]);
            }
            $string = rtrim($string, ',');
            $string .= "),";
            // $value_array[] = $values;
            $value_array = array_merge($values, $value_array);
        }
        // return $value_array;
        $string = rtrim($string, ',');

        $sql = "INSERT INTO [Student] (
                    [Sid], [Tid], [Year], [StuName]
                    , [IDNumber], [StuNum], [SeatNum]
                    , [Pre_SeatNum]
                    , [Birth], [Sex]
                )VALUES {$string}
        ";
        // var_dump($sql);
        // exit(0);

        $sth = $this->container->db->prepare($sql);
        if ($sth->execute($value_array)) {
            return [
                "status" => "success",
                "message" => "新增成功"
            ];
        } else {
            var_dump($sth->errorInfo());
            return [
                "status" => "failure",
                "message" => "新增失敗"
            ];
        }
    }

    public function get_analysis_pi()
    {
        $schooSql = "SELECT * 
                    FROM [Exam_Word_CV_2] 
                    WHERE [Scoring] = 'Y'
                    ";

        $sth = $this->container->db->prepare($schooSql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function get_exam_choose_title($data)
    {
        $result = [];
        $values = [
            "Sid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT *  FROM SchoolList WHERE (Sid = :Sid)";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure"
            ];
        }
        $Grade = $sth->fetchAll(PDO::FETCH_ASSOC);
        if ($Grade[0]["Class"] === "國中") {
            $values = [
                "Grade2" => 6
            ];
            $sql = "SELECT [Wid], [Title], [Grade] FROM [Exam_Word] WHERE [Show] = 1 AND [Title] like '%國中%' AND [Grade] = :Grade2 ";
            $sth = $this->container->db->prepare($sql);
            if (!$sth->execute($values)) {
                return [
                    "status" => "failure"
                ];
            }
            $Exam_Word = $sth->fetchAll(PDO::FETCH_ASSOC);

            $result['Grade'] = $Exam_Word[0]["Grade"];
            $result['ltr_Wid'] = $Exam_Word[0]["Wid"];
            $result['lbl_Exam'] = $Exam_Word[0]["Title"];
        } else {
            $values = [
                "Tid" => 0
            ];
            foreach ($values as $key => $value) {
                if (array_key_exists($key, $data)) {
                    $values[$key] = $data[$key];
                }
            }
            $sql = "SELECT Grade FROM Teacher WHERE ([Tid] = :Tid)";
            $sth = $this->container->db->prepare($sql);
            if (!$sth->execute($values)) {
                return [
                    "status" => "failure"
                ];
            }
            $Teacher = $sth->fetchAll(PDO::FETCH_ASSOC);
            $Grade = "6";
            $left = $Teacher[0]["Grade"];
            if (($left === "畢" || $left === "六")) {
                $Grade = '6';
            } else if ($left === "五") {
                $Grade = '5';
            } else if ($left === "四") {
                $Grade = '4';
            } else if ($left === "三") {
                $Grade = '3';
            } else if ($left === "二") {
                $Grade = '2';
            } else if ($left === "一") {
                $Grade = '1';
            }
            $values = [
                "Grade" => $Grade
            ];
            $sql = "SELECT [Wid], [Title], [Grade] FROM [Exam_Word] WHERE [Show] = 1 AND [Title] like '%年級%' AND [Grade] = :Grade";
            $sth = $this->container->db->prepare($sql);
            if (!$sth->execute($values)) {
                return [
                    "status" => "failure"
                ];
            }
            $Exam_Word = $sth->fetchAll(PDO::FETCH_ASSOC);

            $result['Grade'] = $Exam_Word[0]["Grade"];
            $result['ltr_Wid'] = $Exam_Word[0]["Wid"];
            $result['lbl_Exam'] = $Exam_Word[0]["Title"];
        }
        /*  */
        return $result;
    }


    public function base_question_number($data)
    {
        $sql = "SELECT COUNT(*) AS lbl_ScoringWordCount
                FROM [Exam_Word_CV_2] 
                WHERE [Grade] = :Grade AND [Scoring] = :Scoring";
        $sth = $this->container->db->prepare($sql);
        $sth->bindValue(':Grade', $data['Grade'], PDO::PARAM_STR);
        $sth->bindValue(':Scoring', $data['Scoring'], PDO::PARAM_STR);
        if ($sth->execute()) {
            $result = $sth->fetchColumn(0);
            return $result;
        }
        return ["message" => "Cannot get questions！"];
    }

    // lbl_ExamStr : 題目字串
    // lbl_ExamNum : 目前題號
    // lbl_ExamScore : 分數字串
    // lbl_ErrorNum : 答錯次數
    // lbl_ScoringWordCount : 基本題數數量

    public function calculate_analysis_pi($data)
    {
        $info = $this->get_student_year_term_tkind($data["StudentInfo"]);
        // var_dump($info);
        // exit(0);
        $check = $this->check_student_exam_time($info[0]);
        // var_dump($check);
        // exit(0);
        if ($check['status'] === 'failure') {
            return $check;
        } else {
            if (strlen($data["lbl_ExamScore"]) >= (12 + (int)$data["lbl_ScoringWordCount"] * 2)) {
                if (strlen($data["lbl_ExamScore"]) % 2 == 0) $result = $this->AnalysisPi($data);
            }
        }
        return $result;
    }

    public function get_student_year_term_tkind($data)
    {
        $values = [
            "Pid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [Pid], [Exam_Year], [Exam_Term], [Exam_TKind]
                FROM [Literacy].[dbo].[Student]
                WHERE Pid = :Pid
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function check_student_exam_time($data)
    {
        // var_dump($data);
        // exit(0);
        $values = [
            "Pid" => '',
            "Exam_Year" => '',
            "Exam_Term" => '',
            "Exam_TKind" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        // var_dump($values);
        // exit(0);


        $sql = "SELECT [Pid]
                FROM [Literacy].[dbo].[Exam_Word_Score]
                WHERE [Pid] = :Pid
                AND [Exam_Year] = :Exam_Year
                AND [Exam_Term] = :Exam_Term
                AND [Exam_TKind] = :Exam_TKind
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        // var_dump($result);
        // exit(0);
        if (count($result) > 0) {
            return [
                "status" => "failure",
                "message" => "該生於{$values['Exam_Year']}學年度{$values['Exam_Term']}學期{$values['Exam_TKind']}已進行施測"
            ];
        }
        return [
            "status" => "success",
            "message" => "尚未施測"
        ];
    }

    public function AnalysisPi($data)
    {
        // lbl_FinPi.Text = "";

        $WordQ_ExamStr = mb_substr($data["lbl_ExamStr"], (int)$data["lbl_ScoringWordCount"], mb_strlen($data["lbl_ExamStr"]) - (int)$data["lbl_ScoringWordCount"]);

        $WordQ_IN_SQL = "";
        $WordQ_Order_SQL = "";
        $num = mb_strlen($WordQ_ExamStr);
        $ew = 1;

        while (true) {
            $num2 = $ew;
            $num3 = $num;
            if ($num2 > $num3) break;
            $WordQ_IN_SQL .= "'" . mb_substr($WordQ_ExamStr, $ew - 1, 1) . "'";
            $WordQ_IN_SQL .=  ",";
            $WordQ_Order_SQL .= " WHEN [WordQuestion] = " . "'" . mb_substr($WordQ_ExamStr, $ew - 1, 1) . "'" . " THEN " . strval($ew) . " ";
            $ew++;
        }
        $WordQ_IN_SQL = rtrim($WordQ_IN_SQL, ",");
        $sql = "SELECT * 
                FROM [Exam_Word_CV_2] 
                WHERE [Scoring] = 'Y' AND [WordQuestion] IN ({$WordQ_IN_SQL}) 
                ORDER BY CASE {$WordQ_Order_SQL} END ASC";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchALL(PDO::FETCH_ASSOC);

        $sql = "SELECT COUNT(*) count 
                FROM [Exam_Word_CV_2] 
                WHERE [Scoring] = 'Y' AND [WordQuestion] IN ({$WordQ_IN_SQL}) 
                -- ORDER BY CASE {$WordQ_Order_SQL} END ASC
                ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result_count = $sth->fetchColumn(0);


        $WordQ = [];
        $WordAns = [];
        $WordA = [];
        $WordB = [];
        $WordC = [];

        $num4 = $result_count - 1;
        $k = 0;
        while (true) {
            $num5 = $k;
            $num3 = $num4;
            if ($num5 > $num3) break;

            $WordQ[$k + 1] = $result[$k]["WordQuestion"];
            $WordA[$k + 1] = number_format($result[$k]["PL_A"], 2);
            $WordB[$k + 1] = number_format($result[$k]["PL_B"], 2);
            $WordC[$k + 1] = number_format($result[$k]["PL_C"], 2);
            $k++;
        }
        $REStr = "";
        $ScoringWord = substr($data["lbl_ExamScore"], ((int)$data["lbl_ScoringWordCount"] * 2), strlen($data["lbl_ExamScore"]) - ((int)$data["lbl_ScoringWordCount"] * 2));
        $num6 = (float)((int)$data["lbl_ExamNum"] - (int)$data["lbl_ScoringWordCount"] * 2) / 2.0;
        $AQ = 1.0;
        while (true) {
            $num7 = $AQ;
            $num8 = $num6;
            if (!($num7 <= $num8)) break;

            $round = ((int)round(($AQ - 1.0) * 2.0 + 1.0)) - 1;
            if (substr($ScoringWord, $round, 2) === "11") {
                $WordAns[(int)round($AQ)] = "1";
            } else {
                $WordAns[(int)round($AQ)] = "0";
            }
            $REStr .= $WordAns[(int)round($AQ)];
            $AQ += 1.0;
        }

        $lbl_RE = $REStr;
        $Pi = []; //二維陣列
        $FinPi = []; //一維陣列
        $LocationNum = 1;
        $Theta = -4.0;

        // lbl_RE.Text = REStr;
        // decimal[,] Pi = new decimal[27, 802];
        // decimal[] FinPi = new decimal[802];
        // int LocationNum = 1;
        // double Theta = -4.0;
        // double num11;
        // Label label;
        do {
            $FinPi[$LocationNum] = 1;
            $ExamNum = (int)round((float)strlen($ScoringWord) / 2.0);
            $PiStr = "";
            $num9 = $ExamNum;
            $i = 0;
            while (true) {
                $num10 = $i;
                $num3 = $num9;
                if ($num10 > $num3) break;

                if ($i == 0) {
                    $Pi[$i][$LocationNum] = 0.39894816344486078 * exp(-0.5 * pow((float)(number_format($Theta, 4)), 2.0));
                } else {
                    $Pi[$i][$LocationNum] = (float)($WordC[$i]) + (float)(1 - $WordC[$i]) / (1.0 + exp(-1.7 * (float)($WordA[$i]) * ((float)(number_format($Theta, 4)) - (float)($WordB[$i]))));
                }
                $PQi = (($i !== 0) ? (((float)($WordAns[$i]) !== 1.0) ? (1 - $Pi[$i][$LocationNum]) : $Pi[$i][$LocationNum]) : $Pi[$i][$LocationNum]);
                $FinPi[$LocationNum] = $FinPi[$LocationNum] * $PQi;
                // var_dump($Pi[$i][$LocationNum]);
                $PiStr = $PiStr . ", Pi(" . $i . ", " . $LocationNum . ")=" . strval($Pi[$i][$LocationNum]);
                $i++;
            }
            // label.Text = label.Text + "[" + FinPi[LocationNum] + "] " + PiStr + "<br>";
            $LocationNum++;
            $Theta += 0.01;
            $num11 = $Theta;
            $num8 = 4.0;
        } while ($num11 <= $num8);

        $MaxFinPi = 0.0;
        $num12 = array_keys($FinPi, max($FinPi));

        $j = 1;
        $MaxLocal = 0;
        while (true) {
            $num13 = $j;
            $num3 = $num12[0];
            if ($num13 > $num3) {
                break;
            }

            if ($FinPi[strval($j)] > $MaxFinPi) {
                $MaxFinPi = $FinPi[$j];
                $MaxLocal = $j;
            }
            $j++;
        }
        $MaxTheta = (float)(number_format(-4.0 + 0.01 * (float)($MaxLocal - 1), 2));
        // return $MaxTheta;
        // label = lbl_MaxWQPi;
        // label.Text = label.Text + Conversions.ToString(MaxFinPi) + "_[" + Conversions.ToString(MaxLocal) + "] , Theta = " + Conversions.ToString(MaxTheta) + "<br>";

        // return ((double)(int)($data["lbl_ExamNum"]) - (double)($data["lbl_ScoringWordCount"]) * 2.0);
        // return mb_convert_encoding($sql, 'UTF-8', 'UTF-8');    
        if ((float)(int)($data["lbl_ExamNum"]) - (float)($data["lbl_ScoringWordCount"]) * 2.0 >= 53.0) {
            return $this->ScoreCount(0, $MaxTheta, $data);
        }
        // $txt_PreTheta = strval(9.0);
        $txt_PreTheta = strval($data["txt_PreTheta"]);
        $DiffTheta = (float)($txt_PreTheta) - (float)($MaxTheta);
        if ($DiffTheta < 0) {
            $DiffTheta = $DiffTheta * -1;
        }
        if ((float)$DiffTheta <= 0.01) {
            return $this->ScoreCount(1, $MaxTheta, $data);
        }
        $txt_PreTheta = strval($MaxTheta);
        return [
            "WordQuestion" => $this->AnalysisNextWordQuestion($MaxTheta, $MaxFinPi, $FinPi, $data),
            "txt_PreTheta" => $txt_PreTheta
        ];
    }

    public function ScoreCount($State, $MaxTheta, $data)
    {
        // ImgBtn_NoPass.Enabled = false;
        // ImgBtn_Pass.Enabled = false;
        // MaxTheta = Math.Round($MaxTheta, 2, MidpointRounding.AwayFromZero);
        $MaxTheta = round($MaxTheta, 2);
        $sql = "SELECT * 
                FROM [Exam_Word_Score] 
                WHERE ([Pid] = '" . $data["StudentInfo"]["Pid"] . "') AND ([StartTime] = convert(varchar(60), getdate(), 120))";
        // WHERE ([Pid] = '" + $data["StudentInfo"]["Pid"] + "') AND ([StartTime] = '" + $data["txt_StartTime"] + "')";	
        // var_dump($sql);
        // exit(0);
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchALL(PDO::FETCH_ASSOC);

        $sql = "SELECT COUNT(*) count 
                FROM [Exam_Word_Score] 
                WHERE ([Pid] = '" . $data["StudentInfo"]["Pid"] . "') AND ([StartTime] = convert(varchar(60), getdate(), 120))";
        // WHERE ([Pid] = '" + $data["StudentInfo"]["Pid"] + "') AND ([StartTime] = '" + $data["txt_StartTime"] + "')";	
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result_count = $sth->fetchColumn(0);

        if ((int)$result_count === 0) {
            $LiteracyScore_2WV = 0;
            $LiteracyScore_3WV = 0;
            $LiteracyScore_4WV = 0;
            $WordStr_ExamStr = mb_substr($data["lbl_ExamStr"], mb_strlen($data["lbl_ExamStr"]) - 4, 4);
            // return mb_convert_encoding($WordStr_ExamStr, 'UTF-8', 'UTF-8');    
            $WordStr_IN_SQL = "";
            $num = mb_strlen($WordStr_ExamStr);
            $ew2 = 1;
            while (true) {
                $num2 = $ew2;
                $num3 = $num;
                if ($num2 > $num3) break;
                $WordStr_IN_SQL = $WordStr_IN_SQL . "'" . mb_substr($WordStr_ExamStr, $ew2 - 1, 1) . "',";
                $ew2++;
            }
            $WordStr_IN_SQL = rtrim($WordStr_IN_SQL, ",");
            $sql = "SELECT LiteracyScore FROM [Exam_Word_CV_2] WHERE WordQuestion in (" . $WordStr_IN_SQL . ")";
            $sth = $this->container->db->prepare($sql);
            $sth->execute();
            $result_LiteracyScore = $sth->fetchALL(PDO::FETCH_ASSOC);

            $sql = "SELECT COUNT(LiteracyScore) count FROM [Exam_Word_CV_2] WHERE WordQuestion in (" . $WordStr_IN_SQL . ")";
            $sth = $this->container->db->prepare($sql);
            $sth->execute();
            $result_LiteracyScore_count = $sth->fetchColumn(0);
            $num4 = $result_LiteracyScore_count - 1;
            $i = 0;
            while (true) {
                $num5 = $i;
                $num3 = $num4;
                if ($num5 > $num3) break;
                if ($i >= 2) $LiteracyScore_2WV += (int)($result_LiteracyScore[$i]["LiteracyScore"]);
                if ($i >= 1) $LiteracyScore_3WV += (int)($result_LiteracyScore[$i]["LiteracyScore"]);
                $LiteracyScore_4WV += (int)($result_LiteracyScore[$i]["LiteracyScore"]);
                $i++;
            }
            $LiteracyScore_2WV = (int)round((float)$LiteracyScore_2WV / 2.0);
            $LiteracyScore_3WV = (int)round((float)$LiteracyScore_3WV / 3.0);
            $LiteracyScore_4WV = (int)round((float)$LiteracyScore_4WV / 4.0);
            $sql = "SELECT * FROM [Theta_LiteracyScore_Table] WHERE ([Theta] = '" . strval($MaxTheta) . "')";
            // return $sql;
            $sth = $this->container->db->prepare($sql);
            $sth->execute();
            $result_Theta_LiteracyScore_Table = $sth->fetchALL(PDO::FETCH_ASSOC);

            $sql = "SELECT COUNT(*) count FROM [Theta_LiteracyScore_Table] WHERE ([Theta] = '" . strval($MaxTheta) . "')";
            $sth = $this->container->db->prepare($sql);
            $sth->execute();
            $result_Theta_LiteracyScore_Table_count = $sth->fetchColumn(0);

            $LiteracyScore_Theta = "";
            if ($result_Theta_LiteracyScore_Table_count > 0) {
                $LiteracyScore_Theta = strval($result_Theta_LiteracyScore_Table[0]["LiteracyScore"]);
            }
            $StateRM = "";
            if ($State == 1) {
                $StateRM = "[Theta <= 0.01 提前結束施測]";
            }
            $sql = "SELECT Student.*, Teacher.Grade, Teacher.Class 
                    FROM Student 
                    INNER JOIN Teacher ON Student.Tid = Teacher.Tid
				    WHERE Student.Pid = '" . $data["StudentInfo"]["Pid"] . "'";
            $sth = $this->container->db->prepare($sql);
            $sth->execute();
            $grade_and_class = $sth->fetchALL(PDO::FETCH_ASSOC);
            $lbl_Grade = strval($grade_and_class[0]["Grade"]);
            $lbl_Class = strval($grade_and_class[0]["Class"]);

            $lbl_Wid = $data["Wid"];
            $information = $this->get_all_data_by_pid($data["StudentInfo"]);
            $sql = "INSERT INTO Exam_Word_Score (Tid, Pid, Exam_Sid
                                                , Exam_Grade, Exam_Class, Exam_TeacherName
                                                , Exam_Year, Exam_Term, Exam_TKind
                                                , Wid, Score, Theta
                                                , LiteracyScore, LiteracyScore_2WV, LiteracyScore_3WV, LiteracyScore_4WV, LiteracyScore_Theta
                                                , Z_Value, PR_Value
                                                , StartTime, EndTime, ExamRM, ExamProgramKind, AddTime)
                    VALUES ('" . $information["SchUserInfo"]["Tid"] . "'
                            , '" . $data["StudentInfo"]["Pid"] . "'
                            , '" . $information["SchUserInfo"]["Sid"] . "'
                            , N'" . $lbl_Grade . "'
                            , N'" . $lbl_Class . "'
                            , N'" . $information["SchUserInfo"]["TeacherName"] . "'
                            , N'" . $information["ExamInfo"]["Year"] . "'
                            , N'" . $information["ExamInfo"]["Term"] . "'
                            , N'" . $information["ExamInfo"]["TKind"] . "'
                            , '" . $lbl_Wid . "'
                            , '" . trim($data["lbl_ExamScore"]) . "'
                            , " . strval($MaxTheta) . "
                            , '" . intval($LiteracyScore_Theta) . "'
                            , '" . strval($LiteracyScore_2WV) . "'
                            , '" . strval($LiteracyScore_3WV) . "'
                            , '" . strval($LiteracyScore_4WV) . "'
                            , '" . intval($LiteracyScore_Theta) . "'
                            , " . strval($MaxTheta) . "
                            , " . strval($this->normdist($MaxTheta, 0, 1, true)) . "
                            , '" . date('Y/m/d A h:i:s') . "'
                            , '" . date('Y/m/d A h:i:s') . "'
                            , '" . $data["lbl_ExamStr"] . $StateRM . "'
                            ,'" . $data["EPKind"] . "'
                            , GETDATE())";
            $sth = $this->container->db->prepare($sql);
            $sth->execute();
            $insert_Exam_Word_Score = $sth->fetchALL(PDO::FETCH_ASSOC);

            $WordQ_ExamStr = $data["lbl_ExamStr"];
            $WordQ_IN_SQL = "";
            $num6 = strlen($WordQ_ExamStr);
            $ew = 1;
            while (true) {
                $num7 = $ew;
                $num3 = $num6;
                if ($num7 > $num3) break;
                $WordQ_IN_SQL = $WordQ_IN_SQL . "'" . substr($WordQ_ExamStr, $ew - 1, $ew) . "',";
                $ew++;
            }
            $WordQ_IN_SQL = rtrim($WordQ_IN_SQL, ",");
            $sql = "UPDATE Exam_Word_CV_2 SET Counter = Counter + 1
                    WHERE (WordQuestion IN (" . $WordQ_IN_SQL . "))";
            $sth = $this->container->db->prepare($sql);
            $sth->execute();

            $ColorNum = "";
            $num8 = (int)($LiteracyScore_Theta);
            if ($num8 >= 3041) {
                $ColorNum = "12";
            } else if ($num8 >= 2841) {
                $ColorNum = "11";
            } else if ($num8 >= 2761) {
                $ColorNum = "10";
            } else if ($num8 >= 2491) {
                $ColorNum = "09";
            } else if ($num8 >= 2291) {
                $ColorNum = "08";
            } else if ($num8 >= 1871) {
                $ColorNum = "0607";
            } else if ($num8 >= 1561) {
                $ColorNum = "05";
            } else if ($num8 >= 1301) {
                $ColorNum = "04";
            } else if ($num8 >= 1211) {
                $ColorNum = "03";
            } else if ($num8 >= 731) {
                $ColorNum = "02";
            } else if ($num8 <= 730) {
                $ColorNum = "01";
            }
            $sql = "UPDATE Student SET ColorNum = '" . $ColorNum . "'
                    , Theta = '" . strval($MaxTheta) . "'
                    , LiteracyScore = '" . $LiteracyScore_Theta . "'
                    , StartTime = '" . date('Y/m/d A h:i:s') . "'
                    , EndTime = '" . date('Y/m/d A h:i:s') . "'
                    , Z_Value = '" . strval($MaxTheta) . "'
                    , PR_Value = '" . strval($this->normdist($MaxTheta, 0, 1, true)) . "'
                    , ExamProgramKind = '" . $data["EPKind"] . "'
                    , Wid = '" . $lbl_Wid . "'
                    , Exam_Year = '" . $information["ExamInfo"]["Year"] . "'
                    , Exam_Term = '" . $information["ExamInfo"]["Term"] . "'
                    , Exam_TKind = '" . $information["ExamInfo"]["TKind"] . "'
                    WHERE (Pid = " . $data["StudentInfo"]["Pid"] . ")";
            $sth = $this->container->db->prepare($sql);
            $sth->execute();

            return [
                "status" => "success",
                "message" => $StateRM,
                "LiteracyScore" => (int)$LiteracyScore_Theta,
                "ColorNum" => $ColorNum,
                "WordsCount" => $this->WordsCount((int)$LiteracyScore_Theta)
            ];
        }
        // Application.Lock();
        // if (Operators.ConditionalCompareObjectGreater(Application["online_Exam"], 0, TextCompare: false))
        // {
        //     Application["online_Exam"] = Conversions.ToInteger(Application["online_Exam"]) - 1;
        // }
        // Application.UnLock();
        // Response.Write("<script>top.location.href='Exam_Word_Mode_D_Finish.aspx?EPKind=" + Request.QueryString["EPKind"] + "&Wd=" + lbl_Wid.Text + "&Score=" + Strings.Trim(lbl_ExamScore.Text) + "&ST=" + Strings.Trim(txt_StartTime.Text) + "'</script>");
    }
    private function normdist($x, $mu, $rho, $bool)
    {
        $pi = atan(1.0) * 4.0;
        return (float)((1 / (sqrt(2.0 * $pi) * $rho)) * exp((float)(((($mu - $x) ^ 2) * -1) / (2 * ($rho ^ 2)))));
    }

    public function AnalysisNextWordQuestion($MaxTheta, $MaxFinPi, $FinPi, $data)
    {
        $MaxWQI_Theta_ExamStr = "";
        $WordQ_ExamStr = $data["lbl_ExamStr"];
        $WordQ_IN_SQL = "";
        $num = mb_strlen($WordQ_ExamStr);
        $ew = 1;

        while (true) {
            $num2 = $ew;
            $num3 = $num;
            if ($num2 > $num3) {
                break;
            }
            $WordQ_IN_SQL = $WordQ_IN_SQL . "'" . mb_substr($WordQ_ExamStr, $ew - 1, 1) . "',";
            $ew++;
        }
        $WordQ_IN_SQL = rtrim($WordQ_IN_SQL, ",");
        $sql = "SELECT * 
                FROM [Exam_Word_CV_2] 
                WHERE [WordQuestion] NOT IN ($WordQ_IN_SQL) AND [Scoring] = 'Y' AND [ThetaMax] = $MaxTheta
                ORDER BY NEWID()";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchALL(PDO::FETCH_ASSOC);

        $sql = "SELECT COUNT(*) count 
                FROM [Exam_Word_CV_2] 
                WHERE [WordQuestion] NOT IN ($WordQ_IN_SQL) AND [Scoring] = 'Y' AND [ThetaMax] = $MaxTheta
                ORDER BY NEWID()";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result_count = $sth->fetchColumn(0);

        if ((int)$result_count > 0) {
            $MaxWQI_Theta = (float)($result[0]["ThetaMax"]);
            $MaxWQI_Theta_ExamStr = strval($result[0]["WordQuestion"]);
            return $MaxWQI_Theta_ExamStr;
        } else {
            $sql = "SELECT Top (1) * 
                    FROM [Exam_Word_CV_2] 
                    WHERE [WordQuestion] NOT IN ($WordQ_IN_SQL) AND [Scoring] = 'Y' 
                    ORDER BY ABS($MaxTheta - [ThetaMax])";
            $sth = $this->container->db->prepare($sql);
            $sth->execute();
            $result_top = $sth->fetch(PDO::FETCH_ASSOC);

            $Similarity = abs((float)($MaxTheta - $result_top["ThetaMax"]));

            $sql = "SELECT * 
                    FROM [Exam_Word_CV_2] 
                    WHERE [WordQuestion] NOT IN ($WordQ_IN_SQL) AND [Scoring] = 'Y' 
                        AND CONVERT(NUMERIC(18, 2), ABS([ThetaMax] - " . strval($MaxTheta) . ")) = CONVERT(NUMERIC(18, 2), " . strval($Similarity) . ") 
                    ORDER BY NEWID()";
            $sth = $this->container->db->prepare($sql);
            $sth->execute();
            $result = $sth->fetchALL(PDO::FETCH_ASSOC);
            // return $Similarity;

            $sql = "SELECT COUNT(*) count 
                    FROM [Exam_Word_CV_2] 
                    WHERE [WordQuestion] NOT IN ($WordQ_IN_SQL) AND [Scoring] = 'Y' 
                        AND CONVERT(NUMERIC(18, 2), ABS([ThetaMax] - " . strval($MaxTheta) . ")) = CONVERT(NUMERIC(18, 2), " . strval($Similarity) . ") 
                    ORDER BY NEWID()";
            $sth = $this->container->db->prepare($sql);
            $sth->execute();
            $result_count = $sth->fetchColumn(0);
            if ($result_count > 0) {
                $MaxWQI_Theta = (float)($result[0]["ThetaMax"]);
                $MaxWQI_Theta_ExamStr = strval($result[0]["WordQuestion"]);
                return $MaxWQI_Theta_ExamStr;
            } else {
                $MaxWQI_Theta = 99;
                $MaxWQI_Theta_ExamStr = "爆";
                return $MaxWQI_Theta_ExamStr;
            }
        }
        // Label label = lbl_FitWQ;
        // label.Text = label.Text + "NextWord ->  I(θ) = " + Conversions.ToString(MaxWQI_Theta) + " [" + MaxWQI_Theta_ExamStr + "]<br>";
        // lbl_ExamStr.Text += MaxWQI_Theta_ExamStr;
        // ShowWord();
    }
    public function WordsCount($LiteracyScore_Theta)
    {
        if ($LiteracyScore_Theta < 731) {
            return [
                "Img_ScoreResult_Color" => "images/color/01.jpg",
                "lbl_ScoreResult_Color_Text" => "白色"
            ];
        } else if (($LiteracyScore_Theta >= 731 && $LiteracyScore_Theta <= 1210) ? true : false) {
            return [
                "Img_ScoreResult_Color" => "images/color/02.jpg",
                "lbl_ScoreResult_Color_Text" => "黑色"
            ];
        } else if (($LiteracyScore_Theta >= 1211 && $LiteracyScore_Theta <= 1300) ? true : false) {
            return [
                "Img_ScoreResult_Color" => "images/color/03.jpg",
                "lbl_ScoreResult_Color_Text" => "紅色"
            ];
        } else if (($LiteracyScore_Theta >= 1301 && $LiteracyScore_Theta <= 1560) ? true : false) {
            return [
                "Img_ScoreResult_Color" => "images/color/04.jpg",
                "lbl_ScoreResult_Color_Text" => "橙色"
            ];
        } else if (($LiteracyScore_Theta >= 1561 && $LiteracyScore_Theta <= 1870) ? true : false) {
            return [
                "Img_ScoreResult_Color" => "images/color/05.jpg",
                "lbl_ScoreResult_Color_Text" => "黃色"
            ];
        } else if (($LiteracyScore_Theta >= 1871 && $LiteracyScore_Theta <= 2290) ? true : false) {
            return [
                "Img_ScoreResult_Color" => "images/color/0607.jpg",
                "lbl_ScoreResult_Color_Text" => "綠藍色"
            ];
        } else if (($LiteracyScore_Theta >= 2291 && $LiteracyScore_Theta <= 2490) ? true : false) {
            return [
                "Img_ScoreResult_Color" => "images/color/08.jpg",
                "lbl_ScoreResult_Color_Text" => "靛色"
            ];
        } else if (($LiteracyScore_Theta >= 2491 && $LiteracyScore_Theta <= 2760) ? true : false) {
            return [
                "Img_ScoreResult_Color" => "images/color/09.jpg",
                "lbl_ScoreResult_Color_Text" => "紫色"
            ];
        } else if (($LiteracyScore_Theta >= 2761 && $LiteracyScore_Theta <= 2840) ? true : false) {
            return [
                "Img_ScoreResult_Color" => "images/color/10.jpg",
                "lbl_ScoreResult_Color_Text" => "銅色"
            ];
        } else if (($LiteracyScore_Theta >= 2841 && $LiteracyScore_Theta <= 3040) ? true : false) {
            return [
                "Img_ScoreResult_Color" => "images/color/11.jpg",
                "lbl_ScoreResult_Color_Text" => "銀色"
            ];
        } else if ($LiteracyScore_Theta > 3040) {
            return [
                "Img_ScoreResult_Color" => "images/color/12.jpg",
                "lbl_ScoreResult_Color_Text" => "金色"
            ];
        }
    }

    public function get_all_data_by_pid($data)
    {
        $result = [
            "ExamInfo" => array_pop($this->get_exam_info($data))
        ];

        $values = [
            "Pid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [Student].[Sid],[Teacher].[Tid],[Teacher].TeacherName
            FROM [Literacy].[dbo].[Teacher]
            INNER JOIN [Literacy].[dbo].[Student] ON [Literacy].[dbo].[Teacher].Tid = [Literacy].[dbo].[Student].Tid
            WHERE Pid = :Pid
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return ["status" => "failure"];
        }
        $result["SchUserInfo"] = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    public function post_learn_group($data)
    {
        $values = [
            "GroupName" => ''
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $stmt_array = [
            "GroupName" => $values["GroupName"]
        ];
        $sql = "INSERT INTO [Literacy].[dbo].[Learn_Group] ([GroupName],[AppendDate])
            VALUES (:GroupName,GETDATE());
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($stmt_array)) {
            return ["status" => "failure"];
        }
        $result = array_merge(
            $data,
            [
                "status" => "success",
                "GroupId" => $this->container->db->lastInsertId()
            ]
        );
        return $result;
    }
    public function post_learn_group_student_assign($data)
    {
        $values = [
            "GroupId" => 0,
            "Tid" => 0,
            "Pid" => []
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        if (count($values['Pid']) === 0) {
            $result = array_merge(
                $data,
                [
                    "status" => "success",
                    "message" => "新增成功"
                ]
            );
            return $result;
        }
        $stmt_string = "";
        $stmt_array = [];
        foreach ($values["Pid"] as $key => $value) {
            $stmt_string .= "(?,?,?),";
            array_push($stmt_array, $value);
            array_push($stmt_array, $values['Tid']);
            array_push($stmt_array, $values['GroupId']);
        }
        $stmt_string = rtrim($stmt_string, ',');

        $sql = "INSERT INTO [Literacy].[dbo].[Learn_Teacher_Student] 
                ([Pid] ,[Tid] ,[GroupId])
                VALUES 
                    {$stmt_string}
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($stmt_array)) {
            return [
                "status" => "failure",
                "message" => "新增失敗"
            ];
        }
        $result = array_merge(
            $data,
            [
                "status" => "success",
                "message" => "新增成功"
            ]
        );
        return $result;
    }

    public function random_learn_group_student($data)
    {
        $Pid = array_map(
            function ($value) {
                return array_key_exists('Pid', $value) ? $value['Pid'] : 0;
            },
            $data['Pid']
        );
        $values = [
            "Num" => 0
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $group = [];
        for ($i = 0; $i < $values['Num']; $i++) {
            $group[] = [];
        }
        $length = count($Pid);
        for ($i = 0; $i < $length; $i++) {
            array_push($group[$i % count($group)], array_pop(array_splice($Pid, rand(0, count($Pid) - 1), 1)));
        }
        return $group;
    }

    public function get_learn_class_student($data)
    {
        $values = [
            "tid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Pid], [StuName]
                FROM [Literacy].[dbo].[Student]
                LEFT JOIN [Literacy].[dbo].[Teacher] ON [Literacy].[dbo].[Student].[Tid] = [Literacy].[dbo].[Teacher].[Tid]
                WHERE [Literacy].[dbo].[Teacher].[Tid] = :tid
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_teacher_group($data)
    {
        $values = [
            "tid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Learn_Group].[GroupId]
                        ,[GroupName]
                FROM [Literacy].[dbo].[Learn_Group]
                LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId] = [Learn_Group].[GroupId] 
                WHERE [Learn_Teacher_Student].[Tid] = :tid
                GROUP BY [Learn_Group].[GroupId], [GroupName] 
  
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function patch_learn_class_student($data)
    {
        $values = [
            "GroupId" => 0,
            "Tid" => 0,
            "Pid" => []
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "DELETE FROM [Literacy].[dbo].[Learn_Teacher_Student] 
                WHERE [Tid] = :Tid AND [GroupId] = :GroupId
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute([
            "GroupId" => $values['GroupId'],
            "Tid" => $values['Tid'],
        ])) {
            return [
                "status" => "failure",
                "message" => "編輯失敗"
            ];
        }

        if (count($values['Pid']) === 0) {
            $result = array_merge(
                $data,
                [
                    "status" => "success",
                    "message" => "編輯成功"
                ]
            );
            return $result;
        }

        $values1 = [
            "GroupId" => 0,
            "GroupName" => ''
        ];
        foreach ($values1 as $key => $value) {
            array_key_exists($key, $data) && $values1[$key] = $data[$key];
        }
        $sql = "Update [Literacy].[dbo].[Learn_Group] Set
                [GroupName] = :GroupName
                WHERE [GroupId] = :GroupId
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values1)) {
            return [
                "status" => "failure",
                "message" => "編輯失敗"
            ];
        }

        $stmt_string = "";
        $stmt_array = [];
        foreach ($values["Pid"] as $key => $value) {
            $stmt_string .= "(?,?,?),";
            array_push($stmt_array, $value);
            array_push($stmt_array, $values['Tid']);
            array_push($stmt_array, $values['GroupId']);
        }
        $stmt_string = rtrim($stmt_string, ',');

        $sql = "INSERT INTO [Literacy].[dbo].[Learn_Teacher_Student] 
                ([Pid] ,[Tid] ,[GroupId])
                VALUES 
                    {$stmt_string}
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($stmt_array)) {
            return [
                "status" => "failure",
                "message" => "編輯失敗"
            ];
        }
        $result = array_merge(
            $data,
            [
                "status" => "success",
                "message" => "編輯成功"
            ]
        );
        return $result;
    }

    public function delete_learn_teacher_group($data)
    {
        $values = [
            "GroupId" => 0
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "DELETE FROM [Literacy].[dbo].[Learn_Group] 
                WHERE [GroupId] = :GroupId
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "message" => "刪除群組失敗",
                "info" => $stmt->errorInfo()
            ];
        }

        $sql1 = "DELETE FROM [Literacy].[dbo].[Learn_Group_Task]
                WHERE [GroupId] = :GroupId
        ";
        $stmt = $this->container->db->prepare($sql1);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "message" => "刪除群組任務失敗"
            ];
        }

        $sql2 = "DELETE FROM [Literacy].[dbo].[Learn_Teacher_Student]
                WHERE [GroupId] = :GroupId
        ";
        $stmt = $this->container->db->prepare($sql2);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "message" => "刪除老師群組任務失敗"
            ];
        }
        $result = [
            "status" => "success",
            "message" => "刪除群組成功"
        ];

        return $result;
    }

    public function get_learn_class_student_count($data)
    {
        $values = [
            "tid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT COUNT([Pid]) number
                FROM [Literacy].[dbo].[Student]
                LEFT JOIN [Literacy].[dbo].[Teacher] ON [Literacy].[dbo].[Student].[Tid] = [Literacy].[dbo].[Teacher].[Tid]
                WHERE [Literacy].[dbo].[Teacher].[Tid] = :tid
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_class_student_group($data)
    {
        $values = [
            "tid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT STUFF((
                SELECT [Student].[Pid], [Student].[StuName]
                FROM [Literacy].[dbo].[Learn_Teacher_Student]
                LEFT JOIN [Literacy].[dbo].[Student] ON [Student].[Pid] = [Learn_Teacher_Student].[Pid]
                WHERE [Literacy].[dbo].[Learn_Teacher_Student].[GroupId] = [Learn_Group].GroupId
                GROUP BY [Student].[Pid], [Student].[StuName]
                FOR XML RAW, ELEMENTS
                ), 1, 0, ''
            ) AS student_list,[Learn_Group].[GroupName],[Learn_Group].[GroupId],[Learn_Group].[AppendDate],[Learn_Teacher_Group].count student_number,COALESCE([Learn_Teacher_Task].task_count,0)task_count,COALESCE([Learn_Teacher_Task_unfinish].task_unfinish_count,0)task_unfinish_count
            FROM [Literacy].[dbo].[Learn_Group]
            INNER JOIN(
                SELECT [Learn_Teacher_Student].[GroupId],COUNT(*) count
                FROM [Literacy].[dbo].[Learn_Teacher_Student] 
                WHERE [Learn_Teacher_Student].[Tid] = :tid
                GROUP BY [Learn_Teacher_Student].[GroupId] 
            )[Learn_Teacher_Group] ON [Learn_Teacher_Group].[GroupId] = [Learn_Group].[GroupId]
            LEFT JOIN(
                SELECT [Learn_Group_Task].[GroupId],COUNT(*) task_count
                FROM [Literacy].[dbo].[Learn_Group_Task]
                GROUP BY [Learn_Group_Task].[GroupId] 
            )[Learn_Teacher_Task] ON [Learn_Teacher_Task].[GroupId] = [Learn_Group].[GroupId]
            LEFT JOIN(
                SELECT dt.[GroupId],COUNT(
                    CASE 
                        WHEN dt.task_unfinish_count > 0 THEN 1
                    END
                )task_unfinish_count
                FROM(
                    SELECT [Learn_Group_Task].[GroupId],[Learn_Group_Task].[TaskId]
                        ,COUNT(
                            CASE
                                WHEN [Learn_Task_Word_Student].[step] != 9 OR [Learn_Task_Word_Student].[step] IS NULL THEN 1
                            END
                        ) task_unfinish_count
                    FROM [Literacy].[dbo].[Learn_Group_Task]
                    LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task].[GroupId] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] AND [Learn_Teacher_Student].[Pid] = [Learn_Task_Word_Student].[Pid] 
                    GROUP BY [Learn_Group_Task].[GroupId],[Learn_Group_Task].[TaskId]
                )dt 
                GROUP BY dt.[GroupId]
            )[Learn_Teacher_Task_unfinish] ON [Learn_Teacher_Task_unfinish].[GroupId] = [Learn_Group].[GroupId]
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $key_result => $value) {
            $tmpvalue = $value['student_list'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue</a>");
            if ($tmpvalue == "") {
                $result[$key_result]['student_list'] = $tmpArrs;
                goto Endquotation;
            }
            foreach ($xml as $t) {
                $tmpArr = [];
                foreach ($t as $a => $b) {
                    $tmpArr[$a] = '';
                    foreach ((array)$b as $c => $d) {
                        $tmpArr[$a] = $d;
                    }
                }
                $tmpArrs[] = $tmpArr;
            }
            $result[$key_result]['student_list'] = $tmpArrs;
            Endquotation:
        }
        return $result;
    }

    public function get_teacher_task($data)
    {
        $values = [
            "tid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [TaskId]
                    ,[Tid]
                    ,[Pid]
                    ,[TaskName]
                    ,[highlight]
                    ,[ApplyDate]
                    ,[EndDate]
                    ,[ExpireDate]
                    ,[AppendDate]
                FROM [Literacy].[dbo].[Learn_Task]
                WHERE [Tid] = :tid 
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_word_list($data)
    {
        $values = [
            "word" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('excel', $data)) {
            unset($data['excel']);
            $select = "SELECT [Learn_Word_Romanization].[word] '生字'
                    , [Learn_Sentence].[ans] '語詞'  
                    , [Learn_Sentence].[sentence] '句子'
            ";
        } else {
            $select = "SELECT [Learn_Word_Romanization].[nid]
                    , [Learn_Word_Romanization].[word]
                    , [Learn_Sentence].[sentence]
                    , [Learn_Sentence].[ans]
            ";
        }

        $sql = "{$select}
                FROM [Literacy].[dbo].[Learn_Word_Romanization]
                LEFT JOIN [Literacy].[dbo].[Learn_Word_Sentence] ON [Learn_Word_Sentence].[nid] = [Learn_Word_Romanization].[nid] 
                LEFT JOIN [Literacy].[dbo].[Learn_Sentence] ON [Learn_Word_Sentence].[sentence_id] = [Learn_Sentence].[sentence_id] 
                WHERE [Learn_Word_Romanization].[word] = :word 
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_task_learned_words_teacher($data)
    {
        $values = [
            "TaskId" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT STUFF(
                    (
                        SELECT CAST([Learn_Word_Romanization].[word] AS VARCHAR(MAX))
                        FROM [Literacy].[dbo].[Learn_Task_Word]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].task_word_id
                        LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Word_Romanization].[nid] = [Learn_Task_Word].[nid] 
                        WHERE [Learn_Task_Word].[task_id] = :TaskId
                        FOR XML PATH ('')
                    ), 1, 0, ''
                )words
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchColumn(0);
        return $result;
    }

    public function post_task($data)
    {
        $values = [
            "task_name" => '',
            "apply_date" => '',
            "end_date" => '',
            'tid' => null,
            'pid' => null
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $author = ",null,null";
        if (is_null($values['tid'])) {
            unset($values['tid']);
            $author = ",:pid,null";
        } else if (is_null($values['pid'])) {
            unset($values['pid']);
            $author = ",null,:tid";
        }

        $sql = "INSERT INTO [Literacy].[dbo].[Learn_Task]
                    (
                        [TaskName]
                        ,[ApplyDate]
                        ,[EndDate]
                        ,[AppendDate]
                        ,[Pid]
                        ,[Tid]
                    )
                VALUES
                    (
                        :task_name
                        ,:apply_date
                        ,:end_date
                        , GETDATE()
                        {$author}
                    )
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "任務新增失敗"
            ];
        }
        return [
            "status" => "success",
            "message" => "任務新增成功",
            "TaskId" => $this->container->db->lastInsertId()
        ];
    }

    public function patch_task($data)
    {
        $values = [
            "TaskId" => '',
            "ApplyDate" => '',
            "EndDate" => '',
            "TaskName" => ''
        ];

        $values['ApplyDate'] = date(time());
        $values['EndDate'] = date(time());

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[Learn_Task]
                SET [TaskName] = :TaskName
                , [ApplyDate] = :ApplyDate
                , [EndDate] = :EndDate
                WHERE [TaskId] = :TaskId
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "任務名稱更新失敗"
            ];
        }
        return [
            "status" => "success",
            "message" => "任務名稱更新成功"
        ];
    }

    public function post_task_group($data)
    {
        $values = [
            "task_id" => '',
            "group_id" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "INSERT INTO [Literacy].[dbo].[Learn_Group_Task]
                    (
                        [GroupId]
                        , [TaskId]
                    )
                VALUES
                    (
                        :group_id
                        ,:task_id
                    )
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "任務新增失敗"
            ];
        }
        return [
            "status" => "success",
            "message" => "任務新增成功"
        ];
    }

    public function post_task_word($data)
    {
        $values = [
            "task_id" => 0,
            "nid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "INSERT INTO [Literacy].[dbo].[Learn_Task_Word]
                    (
                        [task_id]
                        , [nid]
                    )
                VALUES
                    (
                        :task_id
                        , :nid
                    )
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "任務生字新增失敗"
            ];
        }
        return [
            "status" => "success",
            "message" => "任務生字新增成功"
        ];
    }

    public function get_task_id($data)
    {
        $values = [
            "tid" => 0,
            "task_name" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [TaskId]
                FROM [Literacy].[dbo].[Learn_Task]
                WHERE [Tid] = :tid
                AND [TaskName] = :task_name
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_task($data)
    {
        $values = [
            "Tid" => 0
        ];

        $string = "";

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            $string .= "  AND [Learn_Task].[ApplyDate] >= :start_date AND [Learn_Task].[ApplyDate] <= dateadd(day,1,:end_date)";
            $values['start_date'] = $data['start_date'];
            $values['end_date'] = $data['end_date'];
        }
        if (array_key_exists('Year', $data)) {
            $string .= "AND YEAR([Learn_Task].[ApplyDate]) = :Year";
            $values['Year'] = $data['Year'] + 1911;
            $check = true;
        }
        if (array_key_exists('Term', $data)) {
            $string .= " AND [Learn_Task].[Term] = :Term";
            $values['Term'] = $data['Term'];
            $check = true;
        }

        if (array_key_exists('excel', $data)) {
            unset($data['excel']);
            $select = "SELECT 
                        CASE [Highlight]
                        WHEN 1 THEN '*'
                        ELSE ' ' 
                        END '標記'
                        , CASE 
                            WHEN (convert(bigint, datediff(day, GETDATE() , [Learn_Task].[ApplyDate])) * 24 * 60 * 60) 
                                - (datediff(second, dateadd(day, datediff(day, 0, GETDATE()), 0), GETDATE()))
                                + (datediff(second, dateadd(day, datediff(day, 0, [Learn_Task].[ApplyDate]), 0), [Learn_Task].[ApplyDate])) > 0
                            THEN '未開始'
                            WHEN [Learn_Task_Word].[unfinish] = 0 THEN '已完成' 
                            WHEN [Learn_Task_Word].[unfinish] != 0 AND GETDATE() BETWEEN [Learn_Task].[ApplyDate] AND [Learn_Task].[EndDate] THEN '進行中'
                            ELSE '未完成' 
                        END '任務狀態'
                        ,[TaskName] '任務名稱'
                        ,[ApplyDate] '指派時間'
                        ,[EndDate] '結束時間'
                        ,[Learn_Task].[TaskId],[Learn_Task_Group].[count] '指派小組'
            ";
        } else {
            $select = "SELECT [Learn_Task].[TaskId],[Learn_Task_Group].[count]
                        ,CASE 
                            WHEN (convert(bigint, datediff(day, GETDATE() , [Learn_Task].[ApplyDate])) * 24 * 60 * 60) 
                                - (datediff(second, dateadd(day, datediff(day, 0, GETDATE()), 0), GETDATE()))
                                + (datediff(second, dateadd(day, datediff(day, 0, [Learn_Task].[ApplyDate]), 0), [Learn_Task].[ApplyDate])) > 0
                            THEN 'unstart'
                            WHEN [Learn_Task_Word].[unfinish] = 0 THEN 'finish' 
                            WHEN [Learn_Task_Word].[unfinish] != 0 AND GETDATE() BETWEEN [Learn_Task].[ApplyDate] AND [Learn_Task].[EndDate] THEN 'doing'
                            ELSE 'unfinish' 
                        END [status]
                        ,[TaskName]
                        ,[Highlight]
                        ,[ApplyDate]
                        ,[EndDate]
            ";
        }

        $sql = "{$select}
                FROM [Literacy].[dbo].[Learn_Task]
                LEFT JOIN (
                    SELECT [Learn_Task_Word].[task_id], COUNT(CASE WHEN ([Learn_Task_Word_Student].[step] != 9 OR [Learn_Task_Word_Student].[step] IS NULL) THEN 1 END) unfinish
                    FROM [Literacy].[dbo].[Learn_Task_Word]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON 
                        [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] 
                    GROUP BY [Learn_Task_Word].[task_id]
                )[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId]
                LEFT JOIN (
                    SELECT [Learn_Group_Task].[TaskId], COUNT(*) [count]
                    FROM [Literacy].[dbo].[Learn_Group_Task]
                    GROUP BY [Learn_Group_Task].[TaskId] 
                )[Learn_Task_Group] ON [Learn_Task_Group].[TaskId] = [Learn_Task].[TaskId] 
                WHERE [Learn_Task].[Tid] = :Tid
                {$string}
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_task_group($data)
    {
        $values = [
            "Tid" => 0,
            "TaskId" => 0,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [Learn_Group_Task].[TaskId],[Learn_Group].[GroupName],
                STUFF((
                    SELECT [Student].[StuName],
                        CASE
                            WHEN COUNT(
                                CASE
                                    WHEN step != 9 OR step IS NULL THEN 1
                                END
                            )>0 THEN 0 ELSE 1 
                        END [status]
                    FROM [Literacy].[dbo].[Learn_Task_Word] dt
                    LEFT JOIN [Literacy].[dbo].[Learn_Group_Task] dt2 ON dt2.[TaskId] = dt.[task_id] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId] = dt2.[GroupId]
                    LEFT JOIN [Literacy].[dbo].[Student] ON [Student].[Pid] = [Learn_Teacher_Student].[Pid]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON dt.[task_word_id] = [Learn_Task_Word_Student].[task_word_id]
                        AND [Learn_Teacher_Student].[Pid] = [Learn_Task_Word_Student].[Pid] 
                    WHERE [Learn_Group_Task].[GroupId] = dt2.[GroupId] AND dt2.[TaskId] = [Learn_Group_Task].[TaskId]
                    GROUP BY [Learn_Teacher_Student].[Pid],[Student].[StuName]
                    FOR XML RAW, ELEMENTS
                ), 1, 0, ''
                )progress
            FROM [Literacy].[dbo].[Learn_Group_Task]
            LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Group_Task].[TaskId] = [Learn_Task].[TaskId]
            LEFT JOIN [Literacy].[dbo].[Learn_Group] ON [Learn_Group_Task].[GroupId] = [Learn_Group].[GroupId]
            WHERE [Learn_Group_Task].[TaskId] = :TaskId AND [Learn_Task].[Tid] = :Tid
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $key_result => $value) {
            $tmpvalue = $value['progress'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue</a>");
            if ($tmpvalue == "") {
                $result[$key_result]['progress'] = $tmpArrs;
                goto Endquotation;
            }
            foreach ($xml as $t) {
                $tmpArr = [];
                foreach ($t as $a => $b) {
                    $tmpArr[$a] = '';
                    foreach ((array)$b as $c => $d) {
                        $tmpArr[$a] = $d;
                    }
                }
                $tmpArrs[] = $tmpArr;
            }
            $result[$key_result]['progress'] = $tmpArrs;
            Endquotation:
        }
        return $result;
    }

    public function check_exam($data)
    {
        $values = [
            "email" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Tid], [TeacherName], [TeacherMail] 
                FROM [Literacy].[dbo].[Teacher]
                WHERE [TeacherMail] = :email
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) == 0) {
            $result = array([
                "status" => "failure",
                "message" => "請輸入註冊申請時之EMail！"
            ]);
        }
        return $result;
    }

    public function patch_exam_tmp_passwd($data)
    {

        $values = [
            "passwd" => '',
            "TeacherMail" => '',
            "Tid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "Update [Literacy].[dbo].[Teacher] Set 
                [PassWD] = :passwd, 
                [PassWD_ChangeDate] = GETDATE()
                WHERE [Tid] = :Tid
                AND [TeacherMail] = :TeacherMail
        ";

        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "修改失敗",
                "message" => $sth->errorInfo()
            ];
        }
        return [
            "status" => "success"
        ];
    }

    public function exam_SendMail($data)
    {
        $account = $data['email'];
        $subject = "【閱讀評量與教學服務網】重設密碼通知信";
        $passwd = $data['passwd'];

        $body = "";
        $body .= "<p style='line-height: 150%'>";
        $body .= "您所申請的教師帳號，臨時管理碼為「<font color=\"blue\">{$passwd}</font>」，請依使用臨時密碼登入並重新修改密碼。</p>";
        $body .= "<br><a href='{$_SERVER['HTTP_HOST']}/School_Login.aspx' target='_blank'>閱讀評量與教學中心 識字量 施測管理人員登入</a><br>";
        $body .= "<br><br>***本E-Mail信件為系統自動寄發，請勿直接回覆本信***<br>";
        $body .= "<br>";
        $body .= "<br>閱讀評量與教學服務網(<a href=\"http://pair.nknu.edu.tw\">http://pair.nknu.edu.tw</a>)";
        $body .= "<br>如有任何疑問，請聯絡「閱讀評量與教學中心」，(07)7172930轉1815";
        $body .= "<br>電子信箱：pair@nknu.edu.tw";
        $body .= "<br>National Kaohsiung Normal University (<a href=\"http://www.nknu.edu.tw\">http://www.nknu.edu.tw</a>)";
        $body .= "</p>";

        $mail = new PHPMailer(true); // Passing `true` enables exceptions
        try {
            //伺服器配置
            $mail->CharSet = "UTF-8"; //設定郵件編碼
            $mail->SMTPDebug = 0; // 除錯模式輸出
            $mail->isSMTP(); // 使用SMTP
            $mail->Host = 'smtp.nknu.edu.tw'; // SMTP伺服器
            $mail->SMTPAuth = false; // 允許 SMTP 認證
            $mail->SMTPSecure = false; // 允許 TLS 或者ssl協議
            $mail->Port = 25; // 伺服器埠 25 或者465 具體要看郵箱伺服器支援

            $mail->setFrom('pair@nknu.edu.tw', '閱讀評量與教學服務網 - 識字量'); //發件人
            $mail->addAddress($account, 'receiver'); // 收件人

            $mail->isHTML(true); // 是否以HTML文件格式傳送  傳送後客戶端可直接顯示對應HTML內容
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $body;

            $mail->send();
            return [
                "status" => "success",
                "message" => "寄信成功!"
            ];
        } catch (Exception $e) {
            return [
                "message" => $mail->ErrorInfo,
                "status" => "failure",
            ];
        }
    }

    public function get_task_word_progress($data)
    {
        $values = [
            "Tid" => 0,
            "TaskId" => 0,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT dt.[task_id],dt.[task_word_id],[Learn_Word_Romanization].[word] ,COUNT(*) total,
                COUNT(
                    CASE 
                        WHEN dt.[status] = 1 THEN 1 
                    END
                )finish,
                COUNT(
                    CASE 
                        WHEN dt.[status] = 1 THEN 1 
                    END
                )*100/REPLACE(COUNT(*),0,1) percentage
            FROM(
                SELECT [Learn_Task_Word].[task_word_id],[Learn_Task_Word].[nid],[Learn_Group_Task].[GroupId],[Learn_Task_Word].[task_id],
                    CASE
                        WHEN COUNT(
                            CASE
                                WHEN step != 9 OR step IS NULL THEN 1
                            END
                        )>0 THEN 0 ELSE 1 
                    END [status]
                FROM [Literacy].[dbo].[Learn_Task_Word]
                LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId]
                LEFT JOIN [Literacy].[dbo].[Learn_Group_Task] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id] 
                LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task].[GroupId]
                LEFT JOIN [Literacy].[dbo].[Student] ON [Student].[Pid] = [Learn_Teacher_Student].[Pid]
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word].[task_word_id] = [Learn_Task_Word_Student].[task_word_id]
                    AND [Learn_Teacher_Student].[Pid] = [Learn_Task_Word_Student].[Pid] 
                WHERE [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task].[GroupId] AND [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                    AND [Learn_Task_Word].[task_id] = :TaskId AND [Learn_Task].[Tid] = :Tid
                GROUP BY [Learn_Task_Word].[task_word_id],[Learn_Task_Word].[nid],[Learn_Group_Task].[GroupId],[Learn_Task_Word].[task_id]
            )dt
            LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Word_Romanization].[nid] = dt.[nid]
            GROUP BY dt.[task_id],dt.[task_word_id],[Learn_Word_Romanization].[word] 
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function delete_task($data)
    {
        $values = [
            "Tid" => 0,
            "now_TaskId" => 0,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "DELETE FROM [Literacy].[dbo].[Learn_Task]
                WHERE [Learn_Task].[TaskId] = :now_TaskId 
                AND [Learn_Task].[Tid] = :Tid
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            $status = [
                "status" => "failure"
            ];
        }
        $status = ["status" => "success"];


        unset($values['Tid']);
        $sql = "DELETE FROM [Literacy].[dbo].[Learn_Group_Task]
                WHERE [Learn_Group_Task].[TaskId] = :now_TaskId
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            $status = [
                "status" => "failure",
                "message" => "刪除失敗"
            ];
        }
        $status = [
            "status" => "success",
            "message" => "刪除成功"
        ];

        $sql = "DELETE FROM [Literacy].[dbo].[Learn_Task_Word]
                WHERE [Learn_Task_Word].[task_id] = :now_TaskId
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            $status = [
                "status" => "failure",
                "message" => "刪除生字失敗"
            ];
        }
        $status = [
            "status" => "success",
            "message" => "刪除成功"
        ];
        return $status;
    }

    public function delete_task_group($data)
    {
        $values = [
            "TaskId" => 0,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "DELETE FROM [Literacy].[dbo].[Learn_Group_Task]
                WHERE [Learn_Group_Task].[TaskId] = :TaskId
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            $status = [
                "status" => "failure",
                "message" => "刪除失敗"
            ];
        }
        $status = [
            "status" => "success",
            "message" => "刪除成功"
        ];
        return $status;
    }

    public function delete_task_word($data)
    {
        $values = [
            "TaskId" => 0,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "DELETE FROM [Literacy].[dbo].[Learn_Task_Word]
                WHERE [Learn_Task_Word].[task_id] = :TaskId
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            $status = [
                "status" => "failure",
                "message" => "刪除失敗"
            ];
        }
        $status = [
            "status" => "success",
            "message" => "刪除成功"
        ];
        return $status;
    }

    public function get_stu_data($data)
    {
        $values = [
            "tid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('excel', $data)) {
            unset($data['excel']);
            $select = "SELECT [Literacy].[dbo].[Student].Year '入學學年度'
                    , [Literacy].[dbo].[Student].SeatNum '座號'
                    , [Literacy].[dbo].[Student].StuName '姓名'
                    , [Literacy].[dbo].[Student].StuNum '學號'
                    , [Literacy].[dbo].[Student].IDNumber '身分證後六碼'
                    , [Literacy].[dbo].[Student].Birth '生日(西元)'
                    , [Literacy].[dbo].[Student].Sex '性別'
            
            ";
        } else {
            $select = "SELECT ROW_NUMBER() OVER(ORDER BY [Student].[Sid]) AS 'key'
                    , [Literacy].[dbo].[Student].Pid, [Literacy].[dbo].[Student].Sid
                    , [Literacy].[dbo].[Student].Tid, [Literacy].[dbo].[Student].Year
                    , [Literacy].[dbo].[Student].SeatNum, [Literacy].[dbo].[Student].StuName
                    , [Literacy].[dbo].[Student].StuNum, [Literacy].[dbo].[Student].IDNumber
                    , [Literacy].[dbo].[Student].Birth, [Literacy].[dbo].[Student].Sex
                    , CASE WHEN db.online_time IS NULL THEN 0 ELSE 1 END online
            ";
        }

        $sql = "SELECT *
                FROM (
                    {$select}
                    FROM [Literacy].[dbo].[Student] 
                    LEFT OUTER JOIN [Literacy].[dbo].[Exam_Word_Score] ON [Literacy].[dbo].[Student].Pid = [Literacy].[dbo].[Exam_Word_Score].Pid
                    LEFT JOIN (
                        SELECT dt.Pid, MAX(dt.AddTime) online_time
                        FROM(
                            SELECT X.Y.value('(Pid)[1]', 'VARCHAR(MAX)') as Pid ,[AddTime]
                            FROM [Literacy].[dbo].[Event_Logging]
                            OUTER APPLY [Event_Logging].[Session].nodes('SESSION') as X(Y) 
                            )dt
                        WHERE dt.Pid IS NOT NULL 
                        AND DATEDIFF(MINUTE, dt.[AddTime], GETDATE()) < 10
                        GROUP BY Pid
                    )db ON db.Pid = [Literacy].[dbo].[Student].Pid
                    GROUP BY [Literacy].[dbo].[Student].Pid, [Literacy].[dbo].[Student].Sid
                    , [Literacy].[dbo].[Student].Tid, [Literacy].[dbo].[Student].Year
                    , [Literacy].[dbo].[Student].StuName, [Literacy].[dbo].[Student].IDNumber
                    , [Literacy].[dbo].[Student].StuNum, [Literacy].[dbo].[Student].SeatNum
                    , [Literacy].[dbo].[Student].Birth, [Literacy].[dbo].[Student].Sex
                    , db.online_time
                    , CAST([Literacy].[dbo].[Student].SeatNum AS int)
                    HAVING [Literacy].[dbo].[Student].Tid = :tid
                    --ORDER BY CAST([Literacy].[dbo].[Student].SeatNum AS int)
                )dt
                ORDER BY online DESC, CAST(dt.SeatNum AS int)
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_student($data)
    {
        $values = [
            "Tid" => 0,
            "StuName" => '',
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (array_key_exists('excel', $data)) {
            unset($data['excel']);
            $select = "SELECT [Student].[SeatNum] '座號',[Student].[StuName] '姓名',
            COALESCE([Learn_Teacher_Student].[group_count],0)  '加入小組數',
            CAST(COALESCE([Learn_Group_Task_assign].[percentage],0) AS varchar)
            +'%('+CAST(COALESCE([Learn_Teacher_Task].finish,0) AS varchar)
            +'/'+CAST(COALESCE([Learn_Teacher_Task].total,0) AS varchar)+')' '教師指派任務完成率(完成/總任務量)',
            CAST(COALESCE([Learn_Teacher_Task].percentage,0) AS varchar)
            +'%('+CAST(COALESCE([Learn_Group_Task_self].[finish],0)AS varchar)
            +'/'+CAST(COALESCE([Learn_Group_Task_self].[total],0)AS varchar)+')' '自主學習任務完成率(完成/總任務量)',
            COALESCE([Learn_Task_Word_Student].learned_semester_count,0) '本學期學習生字量',
            COALESCE([Learn_Task_Word_Student].learned_count,0) '已學習生字量',
            COALESCE([score].[score],0) '個人積分'
            ";
        } else {
            $select = "SELECT [Student].[Pid],[Student].[StuName],[Student].[SeatNum],
                        COALESCE([Learn_Teacher_Student].[group_count],0) [group_count],
                        COALESCE([Learn_Task_Word_Student].learned_count,0)learned_count,
                        COALESCE([Learn_Task_Word_Student].learned_semester_count,0)learned_semester_count,
                        COALESCE([score].[score],0) person_score,
                        COALESCE([Learn_Teacher_Task].finish,0)[assign_finish],
                        COALESCE([Learn_Teacher_Task].total,0) [assign_total],
                        COALESCE([Learn_Teacher_Task].percentage,0)[assign_percentage],
                        COALESCE([Learn_Group_Task_self].[finish],0)[self_finish],
                        COALESCE([Learn_Group_Task_self].[total],0)[self_total],
                        COALESCE([Learn_Group_Task_self].[percentage],0)[self_percentage]
            ";
        }

        $sql = "{$select}
                FROM [Literacy].[dbo].[Student]
                LEFT JOIN (
                    SELECT dt.[Pid]
                        , COUNT(CASE WHEN unfinish = 0 THEN 1 END) finish
                        , COUNT(*)total
                        , COUNT(CASE WHEN unfinish = 0 THEN 1 END)*100/REPLACE(COUNT(*),0,1) percentage
                    FROM (
                        SELECT [Learn_Task].[Pid], [Learn_Task].[TaskId]
                            , COUNT(CASE WHEN [Learn_Task_Word_Student].step != 9 OR [Learn_Task_Word_Student].step IS NULL  THEN 1 END) unfinish
                            , COUNT(CASE WHEN [Learn_Task_Word_Student].step = 9 THEN 1 END) finish
                        FROM [Literacy].[dbo].[Learn_Task] 
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].task_word_id
                        GROUP BY [Learn_Task].[TaskId], [Learn_Task].[Pid]
                    ) dt
                    GROUP BY dt.[Pid]
                ) [Learn_Group_Task_self] ON [Learn_Group_Task_self].[Pid] = [Student].[Pid]
                LEFT JOIN (
                    SELECT [Learn_Teacher_Student].[Pid],COUNT(*) group_count
                    FROM [Literacy].[dbo].[Learn_Teacher_Student]
                    GROUP BY [Learn_Teacher_Student].[Pid]
                )[Learn_Teacher_Student] ON [Learn_Teacher_Student].[Pid] = [Student].[Pid]
                LEFT JOIN (
                    SELECT [Learn_Task_Word_Student].[Pid],
                        COUNT(
                            CASE WHEN step = 9 THEN 1 END
                        ) learned_count,
                        COUNT(
                            CASE WHEN step = 9 THEN 1 END
                        ) learned_semester_count
                    FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                    GROUP BY [Learn_Task_Word_Student].[Pid]
                )[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[Pid] = [Student].[Pid]
                
                LEFT JOIN [Literacy].[dbo].[View_Learn_Total_Score] ON [Student].[Pid] = [View_Learn_Total_Score].[Pid] 
                LEFT JOIN(
                            SELECT [Pid], COUNT([isFinish]) total
                                , COUNT(CASE WHEN [isFinish] = 1 THEN 1 END) finish
                                , COUNT(
                                    CASE
                                        WHEN [isFinish] = 1 THEN 1
                                    END
                                )*100/REPLACE(COUNT(*),0,1) percentage
                            FROM( 
                                SELECT dt.[Pid],dt.[TaskId],[Learn_Task].[TaskName],dt.isFinish
                                FROM(
                                    SELECT [Learn_Teacher_Student].[Pid],[Learn_Group_Task].[TaskId],
                                        CASE
                                            WHEN COUNT(
                                                CASE
                                                    WHEN step != 9 OR step IS NULL THEN 1
                                                END
                                            )>0
                                            THEN 0
                                            ELSE 1
                                        END isFinish
                                    FROM [Literacy].[dbo].[Learn_Group_Task]
                                    LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Group_Task].[GroupId] = [Learn_Teacher_Student].[GroupId]
                                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id]
                                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[Pid] = [Learn_Teacher_Student].[Pid]
                                        AND [Learn_Task_Word].[task_word_id] = [Learn_Task_Word_Student].[task_word_id]
                                    GROUP BY [Learn_Teacher_Student].[Pid],[Learn_Group_Task].[TaskId]
                                    --, [Learn_Group_Task].GroupId
                                )dt
                                LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Task].[TaskId] = dt.[TaskId] 
                                --WHERE [Learn_Task].[Tid] = 7204
                            )dt
                            GROUP BY Pid
                        )[Learn_Teacher_Task] ON [Learn_Teacher_Task].[Pid] = [Student].[Pid]
                LEFT JOIN (
                    SELECT [with].[Pid], SUM([with].score) score
                    FROM (
                        SELECT [teacher_word].Pid,[score].score
                        FROM(
                            SELECT dt.[Pid],dt.task_term,dt.now_grade,COUNT(*)teacher_word_count
                            FROM(
                                SELECT [Learn_Teacher_Student].[Pid],
                                    DATEPART(yy, ApplyDate) AS task_year, CASE WHEN DATEPART(mm, [View_Learn_Task].[ApplyDate]) >= 7 OR
                                    DATEPART(mm, [View_Learn_Task].[ApplyDate]) <= 2 THEN '上' WHEN DATEPART(mm, [View_Learn_Task].[ApplyDate]) >= 1 OR
                                    DATEPART(mm, [View_Learn_Task].[ApplyDate]) <= 8 THEN '下' END AS task_term, 
                                    CASE 6-[student].GraduationYear2+DATEPART(yy, [View_Learn_Task].ApplyDate)
                                        WHEN 1 THEN '一'
                                        WHEN 2 THEN '二'
                                        WHEN 3 THEN '三'
                                        WHEN 4 THEN '四'
                                        WHEN 5 THEN '五'
                                        WHEN 6 THEN '六'
                                        ELSE '畢' END
                                    AS now_grade
                                FROM [Literacy].[dbo].[View_Learn_Task] 
                                INNER JOIN [Literacy].[dbo].[Learn_Group_Task] ON [View_Learn_Task].[TaskId] = [Learn_Group_Task].[TaskId]
                                LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task].[GroupId]
                                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [View_Learn_Task].[TaskId] 
                                LEFT JOIN (
                                    SELECT     Student_1.Pid, Student_1.Sid, Student_1.Tid, Student_1.Year, Student_1.StuName, Student_1.IDNumber, Student_1.StuNum, Student_1.SeatNum, 
                                    Student_1.Birth, Student_1.Sex, Student_1.Parent_Edu, Student_1.Parent_Edu_M, Student_1.Parent_Job, Student_1.Parent_Job_M, 
                                    Student_1.AddTime, Student_1.Pre_Grade, Student_1.Pre_Class, Student_1.Pre_SeatNum, Student_1.GraduationYear, Student_1.ClassIDs, 
                                    Student_1.ColorNum, Student_1.Theta, Student_1.LiteracyScore, Student_1.StartTime, Student_1.EndTime, Student_1.Z_Value, Student_1.PR_Value, 
                                    Student_1.ExamProgramKind, Student_1.Wid, Student_1.Exam_Year, Student_1.Exam_Term, Student_1.Exam_TKind, Student_1.StuDelFlag, 
                                    Student_1.chk_IDNum5, Student_1.chk_BirthMMDD, Student_1.Flag_6ID4Bir, Student_1.Flag_5ID, Student_1.Flag_6IDBirth, dbo.Teacher.Grade, 
                                    CASE WHEN [Teacher].[Grade] = '一' THEN DATEPART(yy, GETDATE()) + 5 WHEN [Teacher].[Grade] = '二' THEN DATEPART(yy, GETDATE()) 
                                    + 4 WHEN [Teacher].[Grade] = '三' THEN DATEPART(yy, GETDATE()) + 3 WHEN [Teacher].[Grade] = '四' THEN DATEPART(yy, GETDATE()) 
                                    + 2 WHEN [Teacher].[Grade] = '五' THEN DATEPART(yy, GETDATE()) + 1 WHEN [Teacher].[Grade] = '六' THEN DATEPART(yy, GETDATE()) 
                                    WHEN [Teacher].[Grade] = '畢' THEN REPLACE([Teacher].[GraduationYear], 'OOO', DATEPART(yy, GETDATE() - 1911)) 
                                    + 1911 END AS GraduationYear2
                                    FROM          dbo.Student AS Student_1 LEFT OUTER JOIN
                                    dbo.Teacher ON dbo.Teacher.Tid = Student_1.Tid
                                )[student] ON [student].Pid = [Learn_Teacher_Student].Pid
                            )dt
                            GROUP BY dt.[Pid],dt.task_term,dt.now_grade
                        )[teacher_word]
                        LEFT JOIN (
                            SELECT dt.[task_term],dt.now_grade ,dt.[Pid],SUM(dt.[score]) score
                            FROM(
                                SELECT CASE [View_Learn_Total_Score_new].Grade 
                                        WHEN 1 THEN '一'
                                        WHEN 2 THEN '二'
                                        WHEN 3 THEN '三'
                                        WHEN 4 THEN '四'
                                        WHEN 5 THEN '五'
                                        WHEN 6 THEN '六'
                                        ELSE '畢' END
                                AS now_grade,[View_Learn_Total_Score_new].[task_term],[View_Learn_Total_Score_new].[Pid],[View_Learn_Total_Score_new].[score]
                                FROM [Literacy].[dbo].[View_Learn_Total_Score_new]
                            )dt
                            GROUP BY dt.Pid ,dt.now_grade ,dt.task_term 
                        )[score] ON [score].task_term = teacher_word.task_term AND [score].[Pid] = teacher_word.[Pid] AND  [score].[now_grade] = teacher_word.[now_grade]             
                        WHERE teacher_word.[Pid] IS NOT NULL  
                    )[with]
                    GROUP BY [with].[Pid]
                )[score] ON [score].[Pid] = [View_Learn_Total_Score].[Pid] 
                WHERE [Student].[Tid] = :Tid 
                AND [Student].[StuName] LIKE '%'+:StuName+'%'
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_task_group_member($data)
    {
        $values = [
            "Tid" => 0,
            "Pid" => 0,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [Learn_Group].[GroupName],[Learn_Teacher_Student].[GroupId],CONVERT(VARCHAR,[Learn_Group].[AppendDate],111)[AppendDate],[Student].[StuName],
                STUFF((
                    SELECT [Student].[StuName]
                    FROM [Literacy].[dbo].[Learn_Teacher_Student] dt
                    LEFT JOIN [Literacy].[dbo].[Student] ON [Student].[Pid] = dt.[Pid] 
                    WHERE [Learn_Teacher_Student].[GroupId] = dt.[GroupId] 
                    FOR XML RAW,ELEMENTS
                ),1,0,''
                )member
            FROM [Literacy].[dbo].[Learn_Teacher_Student]
            LEFT JOIN [Literacy].[dbo].[Student] ON [Student].[Pid] = [Learn_Teacher_Student].[Pid]
            LEFT JOIN [Literacy].[dbo].[Learn_Group] ON [Learn_Teacher_Student].[GroupId] = [Learn_Group].[GroupId] 
            WHERE [Learn_Teacher_Student].[Pid] = :Pid AND [Learn_Teacher_Student].[Tid] = :Tid
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $key_result => $value) {
            $tmpvalue = $value['member'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue</a>");
            if ($tmpvalue == "") {
                $result[$key_result]['member'] = $tmpArrs;
                goto Endquotation;
            }
            foreach ($xml as $t) {
                $tmpArr = [];
                foreach ($t as $a => $b) {
                    $tmpArr[$a] = '';
                    foreach ((array)$b as $c => $d) {
                        $tmpArr[$a] = $d;
                    }
                }
                $tmpArrs[] = $tmpArr;
            }
            $result[$key_result]['member'] = $tmpArrs;
            Endquotation:
        }
        return $result;
    }

    public function exam_go_student($data)
    {

        $values = [
            "Pid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT TOP 1 *
                FROM [Literacy].[dbo].[Student]
                WHERE [Pid] = :Pid
                ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $status = ["status" => "failed"];
        session_start();
        foreach ($result as $key => $value) {
            $_SESSION['Pid'] = $value['Pid'];
            $_SESSION['login'] = 0;
            $status = ["status" => "success"];
        }
        session_write_close();
        return $status;
    }

    public function get_task_assign($data)
    {
        $values = [
            "Tid" => 0,
            "Pid" => 0,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (array_key_exists('Pid', $data)) {
            $values['Pid1'] = $data['Pid'];
        }

        $sql = "SELECT dt.[Pid],dt.[TaskId],[Learn_Task].[TaskName],dt.isFinish,STUFF(
                    (
                        SELECT '、' + CAST(convert(varchar, [Learn_Word_Romanization].[word] )  AS VARCHAR(MAX))
                        FROM [Literacy].[dbo].[Learn_Group_Task]
                        LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Group_Task].[GroupId] = [Learn_Teacher_Student].[GroupId]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id]
                        LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Task_Word].[nid] = [Learn_Word_Romanization].[nid]
                        WHERE dt.[TaskId] = [Learn_Group_Task].[TaskId]
                        AND [Learn_Teacher_Student].[Pid] = :Pid
                        GROUP BY [Learn_Teacher_Student].[Pid], [Learn_Group_Task].[TaskId], [Learn_Word_Romanization].[word]
                        FOR XML PATH(''),TYPE).value('(./text())[1]','VARCHAR(MAX)') ,1,1,''
                    )words
                FROM(
                    SELECT [Learn_Teacher_Student].[Pid],[Learn_Group_Task].[TaskId],
                        CASE
                            WHEN COUNT(
                                CASE
                                    WHEN step != 9 OR step IS NULL THEN 1
                                END
                            )>0
                            THEN 0
                            ELSE 1
                        END isFinish
                    FROM [Literacy].[dbo].[Learn_Group_Task]
                    LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Group_Task].[GroupId] = [Learn_Teacher_Student].[GroupId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[Pid] = [Learn_Teacher_Student].[Pid]
                        AND [Learn_Task_Word].[task_word_id] = [Learn_Task_Word_Student].[task_word_id]
                    WHERE [Learn_Teacher_Student].[Pid] = :Pid1
                    GROUP BY [Learn_Teacher_Student].[Pid],[Learn_Group_Task].[TaskId]
                    --, [Learn_Group_Task].GroupId
                )dt
                LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Task].[TaskId] = dt.[TaskId] 
                WHERE [Learn_Task].[Tid] = :Tid
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_task_self($data)
    {
        $values = [
            "Tid" => 0,
            "Pid" => 0,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT dt.[Pid],dt.[TaskId],[Learn_Task].[TaskName],dt.isFinish,
                STUFF((
                    SELECT '、'+CAST(convert(varchar, [Learn_Word_Romanization].[word] )  AS VARCHAR(MAX))
                    FROM [Literacy].[dbo].[Learn_Task]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task].[TaskId] = [Learn_Task_Word].[task_id] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Task_Word].[nid] = [Learn_Word_Romanization].[nid]
                    WHERE dt.[TaskId] = [Learn_Task].[TaskId]
                    FOR XML PATH(''),TYPE).value('(./text())[1]','VARCHAR(MAX)')
                ,1,1,''
                )words
            FROM(
                SELECT [Learn_Task].[Pid],[Learn_Task].[TaskId],
                    CASE
                        WHEN COUNT(
                            CASE
                                WHEN step != 9 OR step IS NULL THEN 1
                            END
                        )>0
                        THEN 0
                        ELSE 1
                    END isFinish
                FROM (
                    SELECT [Learn_Task].[TaskId],[Learn_Task].[Pid]
                    FROM [Literacy].[dbo].[Learn_Task]
                    WHERE [Learn_Task].[Pid] IS NOT NULL
                )[Learn_Task]
                LEFT JOIN [Literacy].[dbo].[Student] ON [Student].[Pid] = [Learn_Task].[Pid] 
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task].[TaskId] = [Learn_Task_Word].[task_id]
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[Pid] = [Learn_Task].[Pid]
                    AND [Learn_Task_Word].[task_word_id] = [Learn_Task_Word_Student].[task_word_id]
                WHERE [Learn_Task].[Pid] = :Pid AND [Student].[Tid] = :Tid
                GROUP BY [Learn_Task].[Pid],[Learn_Task].[TaskId]
            )dt
            LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Task].[TaskId] = dt.[TaskId] 
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_task_learned_word($data)
    {
        $values = [
            "Tid" => 0,
            "Pid" => 0,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT STUFF(( 
            SELECT '、'+tmp.word 
            FROM (
                SELECT DISTINCT [Learn_Word_Romanization].[word]
                FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                LEFT JOIN [Literacy].[dbo].[Student] ON [Student].[Pid] = [Learn_Task_Word_Student].[Pid] 
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Word_Romanization].[nid] = [Learn_Task_Word] .[nid]
                WHERE [Learn_Task_Word_Student].[Pid] = :Pid  
                AND [Student].[Tid] = :Tid 
                AND [Learn_Task_Word_Student].step = 9 
                AND [Learn_Word_Romanization].[word] IS NOT NULL
                GROUP BY [Learn_Word_Romanization].[word]
            )tmp
            FOR XML PATH('')
            ),1,1,''
            )words 
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_task_self_detail($data)
    {
        $values = [
            "Pid" => 0,
            "Pid1" => 0,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $values['Pid1'] = $values['Pid'];
        $sql = "WITH [with] AS(
                SELECT [teacher_word].Pid,[teacher_word].now_grade,[teacher_word].task_term,
                    COALESCE(dt.teacher_finish,0)teacher_finish,
                    COALESCE(dt.student_finish,0)student_finish,
                    COALESCE(dt.count,0) finish,
                    COALESCE([teacher_word].teacher_word_count,0)teacher_word_count,
                    COALESCE([student_word].student_word_count,0)student_word_count,
                    [score].score
                FROM(
                    SELECT dt.[Pid],dt.task_term,dt.now_grade,COUNT(*)teacher_word_count
                    FROM(
                        SELECT [Learn_Teacher_Student].[Pid],
                            DATEPART(yy, ApplyDate) AS task_year, CASE WHEN DATEPART(mm, [View_Learn_Task].[ApplyDate]) >= 7 OR
                            DATEPART(mm, [View_Learn_Task].[ApplyDate]) <= 2 THEN '上' WHEN DATEPART(mm, [View_Learn_Task].[ApplyDate]) >= 1 OR
                            DATEPART(mm, [View_Learn_Task].[ApplyDate]) <= 8 THEN '下' END AS task_term, 
                            CASE 6-[student].GraduationYear2+DATEPART(yy, [View_Learn_Task].ApplyDate)
                                WHEN 1 THEN '一'
                                WHEN 2 THEN '二'
                                WHEN 3 THEN '三'
                                WHEN 4 THEN '四'
                                WHEN 5 THEN '五'
                                WHEN 6 THEN '六'
                                ELSE '畢' END
                            AS now_grade
                        FROM [Literacy].[dbo].[View_Learn_Task] 
                        INNER JOIN [Literacy].[dbo].[Learn_Group_Task] ON [View_Learn_Task].[TaskId] = [Learn_Group_Task].[TaskId]
                        LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task].[GroupId]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [View_Learn_Task].[TaskId] 
                        LEFT JOIN (
                            SELECT     Student_1.Pid, Student_1.Sid, Student_1.Tid, Student_1.Year, Student_1.StuName, Student_1.IDNumber, Student_1.StuNum, Student_1.SeatNum, 
                            Student_1.Birth, Student_1.Sex, Student_1.Parent_Edu, Student_1.Parent_Edu_M, Student_1.Parent_Job, Student_1.Parent_Job_M, 
                            Student_1.AddTime, Student_1.Pre_Grade, Student_1.Pre_Class, Student_1.Pre_SeatNum, Student_1.GraduationYear, Student_1.ClassIDs, 
                            Student_1.ColorNum, Student_1.Theta, Student_1.LiteracyScore, Student_1.StartTime, Student_1.EndTime, Student_1.Z_Value, Student_1.PR_Value, 
                            Student_1.ExamProgramKind, Student_1.Wid, Student_1.Exam_Year, Student_1.Exam_Term, Student_1.Exam_TKind, Student_1.StuDelFlag, 
                            Student_1.chk_IDNum5, Student_1.chk_BirthMMDD, Student_1.Flag_6ID4Bir, Student_1.Flag_5ID, Student_1.Flag_6IDBirth, dbo.Teacher.Grade, 
                            CASE WHEN [Teacher].[Grade] = '一' THEN DATEPART(yy, GETDATE()) + 5 WHEN [Teacher].[Grade] = '二' THEN DATEPART(yy, GETDATE()) 
                            + 4 WHEN [Teacher].[Grade] = '三' THEN DATEPART(yy, GETDATE()) + 3 WHEN [Teacher].[Grade] = '四' THEN DATEPART(yy, GETDATE()) 
                            + 2 WHEN [Teacher].[Grade] = '五' THEN DATEPART(yy, GETDATE()) + 1 WHEN [Teacher].[Grade] = '六' THEN DATEPART(yy, GETDATE()) 
                            WHEN [Teacher].[Grade] = '畢' THEN REPLACE([Teacher].[GraduationYear], 'OOO', DATEPART(yy, GETDATE() - 1911)) 
                            + 1911 END AS GraduationYear2
                            FROM          dbo.Student AS Student_1 LEFT OUTER JOIN
                            dbo.Teacher ON dbo.Teacher.Tid = Student_1.Tid
                        )[student] ON [student].Pid = [Learn_Teacher_Student].Pid
                    )dt
                    GROUP BY dt.[Pid],dt.task_term,dt.now_grade
                )[teacher_word]
                LEFT JOIN(
                    SELECT dt.[Pid], dt.task_term, dt.now_grade ,COUNT(*) student_word_count
                    FROM(
                        SELECT [View_Learn_Task].[Pid],
                            DATEPART(yy, ApplyDate) AS task_year, CASE WHEN DATEPART(mm, [View_Learn_Task].[ApplyDate]) >= 7 OR
                            DATEPART(mm, [View_Learn_Task].[ApplyDate]) <= 2 THEN '上' WHEN DATEPART(mm, [View_Learn_Task].[ApplyDate]) >= 1 OR
                            DATEPART(mm, [View_Learn_Task].[ApplyDate]) <= 8 THEN '下' END AS task_term, 
                            CASE 6-[student].GraduationYear2+DATEPART(yy, [View_Learn_Task].ApplyDate)
                                WHEN 1 THEN '一'
                                WHEN 2 THEN '二'
                                WHEN 3 THEN '三'
                                WHEN 4 THEN '四'
                                WHEN 5 THEN '五'
                                WHEN 6 THEN '六'
                                ELSE '畢' END
                            AS now_grade
                        FROM [Literacy].[dbo].[View_Learn_Task] 
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [View_Learn_Task].[TaskId] 
                        LEFT JOIN (
                            SELECT     Student_1.Pid, Student_1.Sid, Student_1.Tid, Student_1.Year, Student_1.StuName, Student_1.IDNumber, Student_1.StuNum, Student_1.SeatNum, 
                            Student_1.Birth, Student_1.Sex, Student_1.Parent_Edu, Student_1.Parent_Edu_M, Student_1.Parent_Job, Student_1.Parent_Job_M, 
                            Student_1.AddTime, Student_1.Pre_Grade, Student_1.Pre_Class, Student_1.Pre_SeatNum, Student_1.GraduationYear, Student_1.ClassIDs, 
                            Student_1.ColorNum, Student_1.Theta, Student_1.LiteracyScore, Student_1.StartTime, Student_1.EndTime, Student_1.Z_Value, Student_1.PR_Value, 
                            Student_1.ExamProgramKind, Student_1.Wid, Student_1.Exam_Year, Student_1.Exam_Term, Student_1.Exam_TKind, Student_1.StuDelFlag, 
                            Student_1.chk_IDNum5, Student_1.chk_BirthMMDD, Student_1.Flag_6ID4Bir, Student_1.Flag_5ID, Student_1.Flag_6IDBirth, dbo.Teacher.Grade, 
                            CASE WHEN [Teacher].[Grade] = '一' THEN DATEPART(yy, GETDATE()) + 5 WHEN [Teacher].[Grade] = '二' THEN DATEPART(yy, GETDATE()) 
                            + 4 WHEN [Teacher].[Grade] = '三' THEN DATEPART(yy, GETDATE()) + 3 WHEN [Teacher].[Grade] = '四' THEN DATEPART(yy, GETDATE()) 
                            + 2 WHEN [Teacher].[Grade] = '五' THEN DATEPART(yy, GETDATE()) + 1 WHEN [Teacher].[Grade] = '六' THEN DATEPART(yy, GETDATE()) 
                            WHEN [Teacher].[Grade] = '畢' THEN REPLACE([Teacher].[GraduationYear], 'OOO', DATEPART(yy, GETDATE() - 1911)) 
                            + 1911 END AS GraduationYear2
                            FROM          dbo.Student AS Student_1 LEFT OUTER JOIN
                            dbo.Teacher ON dbo.Teacher.Tid = Student_1.Tid
                        )[student] ON [student].Pid = [View_Learn_Task].Pid
                        WHERE [View_Learn_Task].[Pid] IS NOT NULL
                    )dt
                    GROUP BY dt.[Pid],dt.task_term,dt.now_grade
                )[student_word] ON [student_word].[Pid] = teacher_word.[Pid] AND [student_word].[now_grade] = [student_word].[now_grade] AND [student_word].[task_term] = teacher_word.[task_term] 
                LEFT JOIN (
                    SELECT dt2.[Pid],
                        COUNT(CASE WHEN dt2.Pid_task IS NULL THEN 1 END) teacher_finish,
                        COUNT(CASE WHEN dt2.Tid_task IS NULL THEN 1 END) student_finish,
                        COUNT(*) count,
                        dt2.task_term,dt2.now_grade
                    FROM(
                        SELECT student.[Pid], [Learn_Task_Word].nid,[Learn_Task].[Tid] Tid_task, [Learn_Task].[Pid] Pid_task,
                            DATEPART(yy, ApplyDate) AS task_year, CASE WHEN DATEPART(mm, [Learn_Task].[ApplyDate]) >= 7 OR
                            DATEPART(mm, [Learn_Task].[ApplyDate]) <= 2 THEN '上' WHEN DATEPART(mm, [Learn_Task].[ApplyDate]) >= 1 OR
                            DATEPART(mm, [Learn_Task].[ApplyDate]) <= 8 THEN '下' END AS task_term, 
                            CASE 6-[student].GraduationYear2+DATEPART(yy, [Learn_Task].ApplyDate)
                                WHEN 1 THEN '一'
                                WHEN 2 THEN '二'
                                WHEN 3 THEN '三'
                                WHEN 4 THEN '四'
                                WHEN 5 THEN '五'
                                WHEN 6 THEN '六'
                                ELSE '畢' END
                            AS now_grade
                        FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word_Student].task_word_id = [Learn_Task_Word].task_word_id 
                        LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Task_Word].task_id = [Learn_Task].TaskId 
                        LEFT JOIN (
                            SELECT     Student_1.Pid, Student_1.Sid, Student_1.Tid, Student_1.Year, Student_1.StuName, Student_1.IDNumber, Student_1.StuNum, Student_1.SeatNum, 
                            Student_1.Birth, Student_1.Sex, Student_1.Parent_Edu, Student_1.Parent_Edu_M, Student_1.Parent_Job, Student_1.Parent_Job_M, 
                            Student_1.AddTime, Student_1.Pre_Grade, Student_1.Pre_Class, Student_1.Pre_SeatNum, Student_1.GraduationYear, Student_1.ClassIDs, 
                            Student_1.ColorNum, Student_1.Theta, Student_1.LiteracyScore, Student_1.StartTime, Student_1.EndTime, Student_1.Z_Value, Student_1.PR_Value, 
                            Student_1.ExamProgramKind, Student_1.Wid, Student_1.Exam_Year, Student_1.Exam_Term, Student_1.Exam_TKind, Student_1.StuDelFlag, 
                            Student_1.chk_IDNum5, Student_1.chk_BirthMMDD, Student_1.Flag_6ID4Bir, Student_1.Flag_5ID, Student_1.Flag_6IDBirth, dbo.Teacher.Grade, 
                            CASE WHEN [Teacher].[Grade] = '一' THEN DATEPART(yy, GETDATE()) + 5 WHEN [Teacher].[Grade] = '二' THEN DATEPART(yy, GETDATE()) 
                            + 4 WHEN [Teacher].[Grade] = '三' THEN DATEPART(yy, GETDATE()) + 3 WHEN [Teacher].[Grade] = '四' THEN DATEPART(yy, GETDATE()) 
                            + 2 WHEN [Teacher].[Grade] = '五' THEN DATEPART(yy, GETDATE()) + 1 WHEN [Teacher].[Grade] = '六' THEN DATEPART(yy, GETDATE()) 
                            WHEN [Teacher].[Grade] = '畢' THEN REPLACE([Teacher].[GraduationYear], 'OOO', DATEPART(yy, GETDATE() - 1911)) 
                            + 1911 END AS GraduationYear2
                            FROM          dbo.Student AS Student_1 LEFT OUTER JOIN
                            dbo.Teacher ON dbo.Teacher.Tid = Student_1.Tid
                        )[student] ON [student].Pid = [Learn_Task_Word_Student].Pid
                        WHERE ApplyDate IS NOT NULL AND step = 9
                    )dt2
                    GROUP BY dt2.[Pid],dt2.task_term,dt2.now_grade
                )dt ON dt.[Pid] = teacher_word.[Pid] AND teacher_word.[now_grade] = dt.[now_grade] AND dt.[task_term]  = teacher_word.[task_term] 
                LEFT JOIN (
                    SELECT dt.[task_term],dt.now_grade ,dt.[Pid],SUM(dt.[score]) score
                    FROM(
                        SELECT CASE [View_Learn_Total_Score_new].Grade 
                                WHEN 1 THEN '一'
                                WHEN 2 THEN '二'
                                WHEN 3 THEN '三'
                                WHEN 4 THEN '四'
                                WHEN 5 THEN '五'
                                WHEN 6 THEN '六'
                                ELSE '畢' END
                        AS now_grade,[View_Learn_Total_Score_new].[task_term],[View_Learn_Total_Score_new].[Pid],[View_Learn_Total_Score_new].[score]
                        FROM [Literacy].[dbo].[View_Learn_Total_Score_new]
                    )dt
                    GROUP BY dt.Pid ,dt.now_grade ,dt.task_term 
                )[score] ON [score].task_term = teacher_word.task_term AND  [score].[Pid] = teacher_word.[Pid] AND  [score].[now_grade] = teacher_word.[now_grade]             
                WHERE teacher_word.[Pid] IS NOT NULL           
            )        
            SELECT *
            FROM [with]
            WHERE [Pid] = :Pid
            UNION ALL(
                SELECT [with].[Pid],'合計' now_grade,''task_term
                    ,SUM([with].teacher_finish )teacher_finish 
                    ,SUM([with].student_finish )student_finish
                    ,SUM([with].finish)finish
                    ,SUM([with].teacher_word_count  )teacher_word_count
                    ,SUM([with].student_word_count  )student_word_count
                    ,SUM([with].score) score
                FROM [with]
                WHERE [Pid] = :Pid1
                GROUP BY [with].[Pid]
            )
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_task_assign_detail($data)
    {
        $values = [
            "Pid" => 0,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "WITH [with] AS(
                SELECT dt2.[Pid], dt2.now_grade, dt2.GroupName
                    ,dt2.AppendDate, dt2.[task_term], dt2.[score]
                    , COALESCE(task_count,0)task_count
                    , COALESCE(task_finish_count,0)task_finish_count
                    , COALESCE(word_count,0)word_count
                FROM(
                    SELECT [View_Learn_Total_Score_Group].[Pid]
                        ,[View_Learn_Total_Score_Group].[Grade]
                        ,[View_Learn_Total_Score_Group].[GroupId]
                        ,[View_Learn_Total_Score_Group].[task_year]
                        ,[View_Learn_Total_Score_Group].[task_term]
                        ,CASE 6 - [View_Learn_Total_Score_Group].[GraduationYear] + [View_Learn_Total_Score_Group].[task_year]
                                        WHEN 1 THEN '一'
                                        WHEN 2 THEN '二'
                                        WHEN 3 THEN '三'
                                        WHEN 4 THEN '四'
                                        WHEN 5 THEN '五'
                                        WHEN 6 THEN '六'
                                        ELSE '畢' END
                                    AS now_grade
                        ,[View_Learn_Total_Score_Group].[score]
                        ,[Learn_Group].GroupName 
                        ,CONVERT(varchar,[Learn_Group].AppendDate,111)AppendDate
                        ,[group_task_count].task_count
                        ,[group_task_count].task_finish_count
                        ,[group_task_count].word_count
                    FROM [Literacy].[dbo].[View_Learn_Total_Score_Group]
                    LEFT JOIN [Literacy].[dbo].[Learn_Group] ON [View_Learn_Total_Score_Group].[GroupId] = [Learn_Group].[GroupId] 
                    LEFT JOIN(
                        SELECT dt.[GroupId],dt.task_term,dt.task_year,COUNT(CASE WHEN dt.task_unfinish = 0 THEN 1 END) task_finish_count,
                            COUNT(*) task_count,SUM(dt.word_count) word_count
                        FROM(
                            SELECT [Learn_Group_Task].[GroupId],[Learn_Group_Task].[TaskId] ,COUNT(CASE WHEN [Learn_Task_Word_Student].step IS NULL OR [Learn_Task_Word_Student].step !=9 THEN 1 END) task_unfinish,DATEPART(yy,[Learn_Task].ApplyDate) task_year,
                            CASE WHEN DATEPART(mm, [Learn_Task].[ApplyDate]) <= 2 OR DATEPART(mm, [Learn_Task].[ApplyDate]) > 8 THEN '上' WHEN DATEPART(mm, [Learn_Task].[ApplyDate]) >= 1 OR
                                    DATEPART(mm, [Learn_Task].[ApplyDate]) <= 8 THEN '下' END AS task_term,
                                    COUNT(CASE WHEN [Learn_Task_Word_Student].step = 9 THEN 1 END) word_count
                            FROM [Literacy].[dbo].[Learn_Group_Task]
                            LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].GroupId = [Learn_Group_Task].[GroupId] 
                            LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Group_Task].[TaskId] = [Learn_Task].[TaskId] 
                            LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].task_id  = [Learn_Group_Task].[TaskId] 
                            LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student]  ON [Learn_Task_Word].task_word_id   = [Learn_Task_Word_Student].task_word_id AND [Learn_Teacher_Student].[Pid] = [Learn_Task_Word_Student].Pid 
                            GROUP BY [Learn_Group_Task].[GroupId],[Learn_Group_Task].[TaskId] ,DATEPART(yy,[Learn_Task].ApplyDate),
                                CASE WHEN DATEPART(mm, [Learn_Task].[ApplyDate]) <= 2 OR DATEPART(mm, [Learn_Task].[ApplyDate]) > 8 THEN '上' WHEN DATEPART(mm, [Learn_Task].[ApplyDate]) >= 1 OR
                                    DATEPART(mm, [Learn_Task].[ApplyDate]) <= 8 THEN '下' END
                        )dt
                        GROUP BY dt.[GroupId],dt.task_term,dt.task_year
                        )[group_task_count] ON [group_task_count].[GroupId] = [View_Learn_Total_Score_Group].[GroupId]
                            AND [group_task_count].task_term = [View_Learn_Total_Score_Group].task_term
                            AND [group_task_count].task_year = [View_Learn_Total_Score_Group].task_year
                )dt2
                WHERE dt2.[Pid] = :Pid
                GROUP BY dt2.[Pid], dt2.now_grade, dt2.GroupName 
                    , dt2.[task_term], dt2.[task_term], dt2.AppendDate, dt2.score
                    , dt2.task_count, dt2.task_finish_count, dt2.word_count
            )
            SELECT *
            FROM [with]
            UNION ALL(
                SELECT [with].[Pid],'合計','','','',SUM(score),SUM(task_count),SUM(task_finish_count),SUM(word_count)
                FROM [with]
                GROUP BY [with].Pid
            )
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


    public function get_performance_task($data)
    {
        $values = [
            "Tid" => 0,
        ];
        $string = "";
        $check = false;
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (array_key_exists('taskname', $data) && $data['taskname'] != '') {
            $string .= " AND [TaskName] LIKE '%'+:taskname+'%' ";
            $values['taskname'] = $data['taskname'];
            $check = true;
        }
        if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            $string .= " AND [Learn_Task].[ApplyDate] >= :start_date AND [Learn_Task].[ApplyDate] <= dateadd(day,1,:end_date)";
            $values['start_date'] = $data['start_date'];
            $values['end_date'] = $data['end_date'];
            $check = true;
        }

        if (array_key_exists('excel', $data)) {
            unset($data['excel']);
            $select = "SELECT DISTINCT [Learn_Task].[TaskName] '任務名稱'
            , STUFF((
                SELECT '、'+[Learn_Word_Romanization].[word]
                FROM [Literacy].[dbo].[Learn_Task_Word]
                LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Task_Word].[nid] = [Learn_Word_Romanization].[nid]
                WHERE [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                FOR XML PATH('')
            ),1,1,'') '任務生字'
            , COALESCE(task_group.group_count,0) '指派小組'
            , CAST(task_group_finish.[percentage] AS varchar)
            +'%'+'('+CAST([task_group_finish].[finish] AS varchar)
            +'/'+CAST([task_group_finish].total AS varchar)+')' '小組任務完成率(完成/總指派量)'
            ,  CASE 
                WHEN task_group_finish.[percentage] = 100 THEN '已結束'
                WHEN GETDATE() BETWEEN [Learn_Task].[ApplyDate] AND [Learn_Task].[EndDate] THEN '進行中'
                WHEN (convert(bigint, datediff(day, GETDATE() , [Learn_Task].[ApplyDate])) * 24 * 60 * 60) 
                    - (datediff(second, dateadd(day, datediff(day, 0, GETDATE()), 0), GETDATE()))
                    + (datediff(second, dateadd(day, datediff(day, 0, [Learn_Task].[ApplyDate]), 0), [Learn_Task].[ApplyDate])) > 0
                THEN '未開始'
                ELSE '未完成'
            END '任務狀態'
            , [Learn_Task].[ApplyDate] '指派日期'
            , [Learn_Task].[EndDate] '結束日期'
            ";
        } else {
            $select = "SELECT DISTINCT [Learn_Group_Task].[TaskId], [Learn_Task].[TaskName], COALESCE(task_group.group_count,0) group_count
                        ,[Learn_Task].[ApplyDate],[Learn_Task].[EndDate]
                        ,STUFF((
                            SELECT '、'+[Learn_Word_Romanization].[word]
                            FROM [Literacy].[dbo].[Learn_Task_Word]
                            LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Task_Word].[nid] = [Learn_Word_Romanization].[nid]
                            WHERE [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                            FOR XML PATH('')
                        ),1,1,'')
                        words
                        , task_group_finish.[percentage],[task_group_finish].[finish],[task_group_finish].total
                        ,  CASE 
                            WHEN task_group_finish.[percentage] = 100 THEN 'finish'
                            WHEN GETDATE() BETWEEN [Learn_Task].[ApplyDate] AND [Learn_Task].[EndDate] THEN 'doing'
                            WHEN (convert(bigint, datediff(day, GETDATE() , [Learn_Task].[ApplyDate])) * 24 * 60 * 60) 
                                - (datediff(second, dateadd(day, datediff(day, 0, GETDATE()), 0), GETDATE()))
                                + (datediff(second, dateadd(day, datediff(day, 0, [Learn_Task].[ApplyDate]), 0), [Learn_Task].[ApplyDate])) > 0
                            THEN 'unstart'
                            ELSE 'unfinish'
                        END status
            ";
        }

        $sql = "{$select}
                FROM [Literacy].[dbo].[Learn_Group_Task]
                LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Group_Task].[TaskId] = [Learn_Task].[TaskId]
                LEFT JOIN (
                    SELECT [Learn_Group_Task].[TaskId],COUNT(*) group_count
                    FROM [Literacy].[dbo].[Learn_Group_Task]
                    GROUP BY [Learn_Group_Task].[TaskId]
                )task_group ON task_group.[TaskId] = [Learn_Group_Task].[TaskId]
                LEFT JOIN(
                    SELECT dt.[TaskId], SUM(CASE WHEN dt.finish = dt.total THEN 1 ELSE 0 END) *100/COUNT(*) percentage
                        , SUM(CASE WHEN dt.finish = dt.total THEN 1 ELSE 0 END) finish, COUNT (*) total
                    FROM (
                        SELECT dt.[TaskId], dt.[GroupId], COUNT(CASE WHEN dt.isFinish = 1 THEN 1 END) finish, COUNT(*)total
                        FROM(
                            SELECT [Learn_Group_Task].[TaskId], [Learn_Group_Task].[GroupId],[Learn_Teacher_Student].[Pid]
                                ,CASE WHEN COUNT(CASE
                                    WHEN [Learn_Task_Word_Student].[step] IS NULL OR [Learn_Task_Word_Student].[step] !=9 THEN 1
                                END)>0 THEN 0 ELSE 1 END isFinish
                            FROM [Literacy].[dbo].[Learn_Group_Task]
                            LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Group_Task].[GroupId] = [Learn_Teacher_Student].[GroupId]
                            LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                            LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] 
                            AND [Learn_Task_Word_Student].[Pid] = [Learn_Teacher_Student].[Pid]
                            GROUP BY [Learn_Group_Task].[TaskId], [Learn_Group_Task].[GroupId],[Learn_Teacher_Student].[Pid]
                        )dt
                        GROUP BY dt.[TaskId], dt.[GroupId]
                    )dt
                    GROUP BY dt.[TaskId]
                )task_group_finish ON task_group_finish.[TaskId] = [Learn_Group_Task].[TaskId]
                WHERE [Learn_Task].[Tid] = :Tid {$string}
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_performance_task_group_finish($data)
    {
        $values = [
            "TaskId" => 0,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT dt.TaskId,dt.GroupId,[Learn_Group].[GroupName]
                    , CASE WHEN dt.unfinish = 0 THEN 1 ELSE 0 END status   
                FROM(
                    SELECT [Learn_Task].[TaskId],[Learn_Teacher_Student].[GroupId]
                        , [Learn_Teacher_Student].[Pid]
                        , COUNT( CASE WHEN step !=9 OR step IS NULL THEN 1 END) unfinish
                    FROM [Literacy].[dbo].[Learn_Task]
                    LEFT JOIN [Literacy].[dbo].[Learn_Group_Task] ON [Learn_Group_Task].[TaskId]=[Learn_Task].[TaskId] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId]=[Learn_Group_Task].[GroupId] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id]=[Learn_Task].[TaskId] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] 
                    WHERE [Learn_Task].[TaskId] = :TaskId
                    GROUP BY [Learn_Task].[TaskId],[Learn_Teacher_Student].[GroupId] ,[Learn_Teacher_Student].[Pid]
                )dt
                LEFT JOIN [Literacy].[dbo].[Learn_Group] ON [Learn_Group].[GroupId] = dt.GroupId
                GROUP BY dt.TaskId,dt.GroupId,[Learn_Group].[GroupName], dt.unfinish       
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_word_performance($data)
    {
        $values = [
            "Tid" => 0,
            'word' => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $values = [
            $values['word'],
            $values['Tid'],
            $values['word'],
        ];

        $sql = "SELECT DISTINCT dt.[TaskId], dt.status, dt.[ApplyDate]
                    , dt.[TaskName], dt.group_count, dt.words
                    , dt.total-dt.finish unfinish, ? word
                FROM(
                    SELECT[Learn_Task].[ApplyDate], [Learn_Group_Task].[TaskId]
                        , [Learn_Task].[TaskName], COALESCE(task_group.group_count,0) group_count
                        ,STUFF((
                            SELECT '、'+[Learn_Word_Romanization].[word]
                            FROM [Literacy].[dbo].[Learn_Task_Word]
                            LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Task_Word].[nid] = [Learn_Word_Romanization].[nid]
                            WHERE [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                            FOR XML PATH('')
                        ),1,1,'') words
                        , task_group_finish.[percentage],[task_group_finish].[finish],[task_group_finish].total
                        , CASE 
                            WHEN task_group_finish.[percentage] = 100 THEN 'finish'
                            WHEN GETDATE() BETWEEN [Learn_Task].[ApplyDate] AND [Learn_Task].[EndDate] THEN 'doing'
                            WHEN (convert(bigint, datediff(day, GETDATE() , [Learn_Task].[ApplyDate])) * 24 * 60 * 60) 
                                - (datediff(second, dateadd(day, datediff(day, 0, GETDATE()), 0), GETDATE()))
                                + (datediff(second, dateadd(day, datediff(day, 0, [Learn_Task].[ApplyDate]), 0), [Learn_Task].[ApplyDate])) > 0
                            THEN 'unstart'
                            ELSE 'unfinish'
                        END status
                    FROM [Literacy].[dbo].[Learn_Group_Task]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Group_Task].[TaskId] = [Learn_Task].[TaskId]
                    LEFT JOIN (
                        SELECT [Learn_Group_Task].[TaskId], COUNT(*) group_count
                        FROM [Literacy].[dbo].[Learn_Group_Task]
                        GROUP BY [Learn_Group_Task].[TaskId]
                    )task_group ON task_group.[TaskId] = [Learn_Group_Task].[TaskId]
                    LEFT JOIN(
                        SELECT dt.[TaskId], COUNT(CASE WHEN dt.isFinish = 1 THEN 1 END)*100/COUNT(*) percentage
                            , COUNT(CASE WHEN dt.isFinish = 1 THEN 1 END) finish, COUNT(*)total
                        FROM(
                            SELECT [Learn_Group_Task].[TaskId], [Learn_Group_Task].[GroupId],[Learn_Teacher_Student].[Pid]
                                ,CASE WHEN COUNT( 
                                    CASE WHEN [Learn_Task_Word_Student].[step] IS NULL OR [Learn_Task_Word_Student].[step] !=9 THEN 1 END
                                ) > 0 THEN 0 
                                ELSE 1 
                                END isFinish
                            FROM [Literacy].[dbo].[Learn_Group_Task]
                            LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Group_Task].[GroupId] = [Learn_Teacher_Student].[GroupId]
                            LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                            LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                            GROUP BY [Learn_Group_Task].[TaskId], [Learn_Group_Task].[GroupId],[Learn_Teacher_Student].[Pid]
                        )dt
                        GROUP BY dt.[TaskId]
                    )task_group_finish ON task_group_finish.[TaskId] = [Learn_Group_Task].[TaskId]
                    WHERE [Learn_Task].[Tid] = ?
                )dt
                WHERE words LIKE '%' + ? + '%'
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_word_performance_excel($data)
    {
        $values = [
            "Tid" => 0,
            'word' => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $values = [
            $values['word'],
            $values['Tid'],
            $values['word'],
        ];

        $sql = "SELECT DISTINCT ? '指派生字'
                    , dt.[ApplyDate] '指派日期'
                    , dt.status '任務狀態'
                    , dt.[TaskName] '任務名稱'
                    , dt.words '指派任務生字'
                    , dt.group_count '指派小組'
                    , dt.total-dt.finish '未完成學生'
                    
                FROM(
                    SELECT[Learn_Task].[ApplyDate], [Learn_Group_Task].[TaskId]
                        , [Learn_Task].[TaskName], COALESCE(task_group.group_count,0) group_count
                        ,STUFF((
                            SELECT '、'+[Learn_Word_Romanization].[word]
                            FROM [Literacy].[dbo].[Learn_Task_Word]
                            LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Task_Word].[nid] = [Learn_Word_Romanization].[nid]
                            WHERE [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                            FOR XML PATH('')
                        ),1,1,'') words
                        , task_group_finish.[percentage],[task_group_finish].[finish],[task_group_finish].total
                        , CASE 
                            WHEN task_group_finish.[percentage] = 100 THEN '已完成'
                            WHEN GETDATE() BETWEEN [Learn_Task].[ApplyDate] AND [Learn_Task].[EndDate] THEN '進行中'
                            WHEN (convert(bigint, datediff(day, GETDATE() , [Learn_Task].[ApplyDate])) * 24 * 60 * 60) 
                                - (datediff(second, dateadd(day, datediff(day, 0, GETDATE()), 0), GETDATE()))
                                + (datediff(second, dateadd(day, datediff(day, 0, [Learn_Task].[ApplyDate]), 0), [Learn_Task].[ApplyDate])) > 0
                            THEN '未開始'
                            ELSE '未完成'
                        END status
                    FROM [Literacy].[dbo].[Learn_Group_Task]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Group_Task].[TaskId] = [Learn_Task].[TaskId]
                    LEFT JOIN (
                        SELECT [Learn_Group_Task].[TaskId], COUNT(*) group_count
                        FROM [Literacy].[dbo].[Learn_Group_Task]
                        GROUP BY [Learn_Group_Task].[TaskId]
                    )task_group ON task_group.[TaskId] = [Learn_Group_Task].[TaskId]
                    LEFT JOIN(
                        SELECT dt.[TaskId], COUNT(CASE WHEN dt.isFinish = 1 THEN 1 END)*100/COUNT(*) percentage
                            , COUNT(CASE WHEN dt.isFinish = 1 THEN 1 END) finish, COUNT(*)total
                        FROM(
                            SELECT [Learn_Group_Task].[TaskId], [Learn_Group_Task].[GroupId],[Learn_Teacher_Student].[Pid]
                                ,CASE WHEN COUNT( 
                                    CASE WHEN [Learn_Task_Word_Student].[step] IS NULL OR [Learn_Task_Word_Student].[step] !=9 THEN 1 END
                                ) > 0 THEN 0 
                                ELSE 1 
                                END isFinish
                            FROM [Literacy].[dbo].[Learn_Group_Task]
                            LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Group_Task].[GroupId] = [Learn_Teacher_Student].[GroupId]
                            LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                            LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                            GROUP BY [Learn_Group_Task].[TaskId], [Learn_Group_Task].[GroupId],[Learn_Teacher_Student].[Pid]
                        )dt
                        GROUP BY dt.[TaskId]
                    )task_group_finish ON task_group_finish.[TaskId] = [Learn_Group_Task].[TaskId]
                    WHERE [Learn_Task].[Tid] = ?
                )dt
                WHERE words LIKE '%' + ? + '%'
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


    public function get_word_performance_unfinish($data)
    {
        $values = [
            "Tid" => 0,
            'TaskId' => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [Learn_Group_Task_base].[TaskId],[Learn_Group].[GroupName],
                STUFF((
                    SELECT [Student].[StuName],
                        CASE
                            WHEN COUNT(
                                CASE
                                    WHEN step != 9 OR step IS NULL THEN 1
                                END
                            )>0 THEN 0 ELSE 1 
                        END [status]
                    FROM [Literacy].[dbo].[Learn_Group_Task]
                    LEFT JOIN  [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task].[GroupId]
                    LEFT JOIN [Literacy].[dbo].[Student] ON [Student].[Pid] = [Learn_Teacher_Student].[Pid]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word].[task_word_id] = [Learn_Task_Word_Student].[task_word_id]
                        AND [Learn_Teacher_Student].[Pid] = [Learn_Task_Word_Student].[Pid] 
                    WHERE [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task_base].[GroupId] AND [Learn_Task_Word].[task_id] = [Learn_Group_Task_base].[TaskId]
                    GROUP BY [Learn_Teacher_Student].[Pid], [Student].[StuName]
                    HAVING COUNT(
                        CASE
                            WHEN step != 9 OR step IS NULL THEN 1
                        END
                    )>0
                    FOR XML RAW, ELEMENTS
                ), 1, 0, ''
                )progress
            FROM [Literacy].[dbo].[Learn_Group_Task] [Learn_Group_Task_base]
            LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Group_Task_base].[TaskId] = [Learn_Task].[TaskId]
            LEFT JOIN [Literacy].[dbo].[Learn_Group] ON [Learn_Group_Task_base].[GroupId] = [Learn_Group].[GroupId]
            WHERE [Learn_Group_Task_base].[TaskId] = :TaskId AND [Learn_Task].[Tid] = :Tid
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $key_result => $value) {
            $tmpvalue = $value['progress'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue</a>");
            if ($tmpvalue == "") {
                $result[$key_result]['progress'] = $tmpArrs;
                goto Endquotation;
            }
            foreach ($xml as $t) {
                $tmpArr = [];
                foreach ($t as $a => $b) {
                    $tmpArr[$a] = '';
                    foreach ((array)$b as $c => $d) {
                        $tmpArr[$a] = $d;
                    }
                }
                $tmpArrs[] = $tmpArr;
            }
            $result[$key_result]['progress'] = $tmpArrs;
            Endquotation:
        }
        return $result;
    }
    public function get_learn_group_member($data)
    {
        $values = [
            "Tid" => 0,
            "GroupId" => 0,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT STUFF
        (
            (
                SELECT'、'+ [dbo].[Student].[StuName]
                FROM [dbo].[Student]
                LEFT JOIN [dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[Pid] = [Student].[Pid]
                WHERE [Learn_Teacher_Student].[Tid]= :Tid AND [Learn_Teacher_Student].[GroupId]=:GroupId
                GROUP BY Student.StuName
                FOR XML PATH('')
            ), 1, 1, ''
        )StuName
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_online_student($data)
    {
        $values = [
            // 'Tid' => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT COUNT(DISTINCT Pid) student_count
                FROM(
                    SELECT [RRNid]
                        ,[UID]
                        ,[Page]
                        ,[SQLCmd]
                        ,[IP]
                        ,[AddTime]
                        ,[Session],   
                        X.Y.value('(Pid)[1]', 'VARCHAR(MAX)') as Pid,
                        X.Y.value('(Tid)[1]', 'VARCHAR(MAX)') as Tid
                    FROM [Literacy].[dbo].[Admin_Online_Record]
                    OUTER APPLY [Admin_Online_Record].[Session].nodes('SESSION') as X(Y) 
                    WHERE X.Y.value('(Pid)[1]', 'VARCHAR(MAX)') IS NOT NULL
                    AND X.Y.value('(Pid)[1]', 'VARCHAR(MAX)') != ''
                )dt
                WHERE 
                -- dt.Tid = :Tid
                -- AND 
                DATEDIFF(MINUTE, dt.[AddTime], GETDATE()) < 15
        ";
        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_group_select($data)
    {
        $values = [
            "cur_page" => 1,
            "size" => 10,
            "Tid" => 0,
        ];
        $string = "AND";
        $check = false;
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $values['length'] = $values['cur_page'] * $values['size'];
        $values['start'] = $values['length'] - $values['size'];

        unset($values['cur_page']);
        unset($values['size']);

        if (array_key_exists('groupname', $data) && $data['groupname'] != '') {
            $string .= " [Learn_Group].[GroupName] LIKE '%'+:groupname+'%' AND";
            $values['groupname'] = $data['groupname'];
            $check = true;
        }
        if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            $string .= "[Learn_Group].[AppendDate] >= :start_date AND [Learn_Group].[AppendDate] < =dateadd(day,1,:end_date) AND";
            $values['start_date'] = $data['start_date'];
            $values['end_date'] = $data['end_date'];
            $check = true;
        }
        if ($check) {
            $string = rtrim($string, "AND");
        } else {
            $string = ltrim($string, "AND");
        }

        $sql = "SELECT *
                FROM(
                    SELECT ROW_NUMBER() OVER(ORDER BY [Learn_Group].[GroupId]) AS row_num
                            , COALESCE(([Learn_Teacher_Task].task_count-[Learn_Teacher_Task_unfinish].task_unfinish_count)*100/[Learn_Teacher_Task].task_count,0) percentage
                            , [Learn_Group].[GroupName],[Learn_Group].[GroupId],[Learn_Group].[AppendDate],[Learn_Teacher_Group].count student_number
                            , COALESCE([Learn_Teacher_Task].task_count,0)task_count
                            , COALESCE([Learn_Teacher_Task_unfinish].task_unfinish_count,0)task_unfinish_count
                            ,  COALESCE([View_Learn_Total_Score_Group].[score],0) [score]
                    FROM [Literacy].[dbo].[Learn_Group]
                    INNER JOIN(
                        SELECT [Learn_Teacher_Student].[GroupId], [Learn_Teacher_Student].[Tid], COUNT(*) count
                        FROM [Literacy].[dbo].[Learn_Teacher_Student] 
                        GROUP BY [Learn_Teacher_Student].[GroupId],[Learn_Teacher_Student].[Tid]
                    )[Learn_Teacher_Group] ON [Learn_Teacher_Group].[GroupId] = [Learn_Group].[GroupId]
                    LEFT JOIN(
                        SELECT [Learn_Group_Task].[GroupId],COUNT(*) task_count
                        FROM [Literacy].[dbo].[Learn_Group_Task]
                        GROUP BY [Learn_Group_Task].[GroupId] 
                    )[Learn_Teacher_Task] ON [Learn_Teacher_Task].[GroupId] = [Learn_Group].[GroupId]
                    LEFT JOIN(
                        SELECT dt.[GroupId],COUNT(
                            CASE 
                                WHEN dt.task_unfinish_count > 0 THEN 1
                            END
                        )task_unfinish_count
                        FROM(
                            SELECT [Learn_Group_Task].[GroupId],[Learn_Group_Task].[TaskId]
                                ,COUNT(
                                    CASE
                                        WHEN [Learn_Task_Word_Student].[step] != 9 OR [Learn_Task_Word_Student].[step] IS NULL THEN 1
                                    END
                                ) task_unfinish_count
                            FROM [Literacy].[dbo].[Learn_Group_Task]
                            LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task].[GroupId] 
                            LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id] 
                            LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] 
                            AND [Learn_Teacher_Student].[Pid] = [Learn_Task_Word_Student].[Pid] 
                            GROUP BY [Learn_Group_Task].[GroupId],[Learn_Group_Task].[TaskId]
                        )dt 
                        GROUP BY dt.[GroupId]
                            )[Learn_Teacher_Task_unfinish] ON [Learn_Teacher_Task_unfinish].[GroupId] = [Learn_Group].[GroupId]
                    LEFT JOIN [Literacy].[dbo].[View_Learn_Total_Score_Group] ON [View_Learn_Total_Score_Group].[GroupId]=[Learn_Group].[GroupId]
                    WHERE [Learn_Teacher_Group].[Tid] = :Tid
                    GROUP BY [Learn_Group].[GroupName], [Learn_Group].[GroupId]
                        , [Learn_Group].[AppendDate],[Learn_Teacher_Task].[task_count]
                        , [Learn_Teacher_Task_unfinish].[task_unfinish_count], [Learn_Teacher_Group].count
                        , [View_Learn_Total_Score_Group].[score]

                    ) AS selection
                WHERE selection.row_num >:start AND selection.row_num <=:length
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_group_select_excel($data)
    {
        $values = [
            "Tid" => 0
        ];

        $string = "";
        $check = false;

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('groupname', $data)) {
            $string .= "AND [GroupName] LIKE '%'+:groupname+'%' ";
            $values['groupname'] = $data['groupname'];
            $check = true;
        }
        if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            $string .= "AND [Learn_Group].[AppendDate] >= :start_date AND [Learn_Group].[AppendDate] < =dateadd(day,1,:end_date) ";
            $values['start_date'] = $data['start_date'];
            $values['end_date'] = $data['end_date'];
            $check = true;
        }
        if ($check) {
            $string = rtrim($string, "AND");
        } else {
            $string = ltrim($string, "AND");
        }

        $sql = "SELECT COALESCE(([Learn_Teacher_Task].task_count-[Learn_Teacher_Task_unfinish].task_unfinish_count)*100/[Learn_Teacher_Task].task_count,0) '小組完成率'
                    ,[Learn_Group].[GroupName] '小組名稱'
                    ,[Learn_Group].[AppendDate] '指派時間'
                    ,[Learn_Teacher_Group].count '學生數量'
                    ,COALESCE([Learn_Teacher_Task].task_count,0) '任務數量'
                    ,COALESCE([Learn_Teacher_Task_unfinish].task_unfinish_count,0) '未完成任務數量'
                    ,COALESCE(SUM([group_score].[score]),0) '小組分數'
                FROM [Literacy].[dbo].[Learn_Group]
                INNER JOIN(
                    SELECT [Learn_Teacher_Student].[GroupId],[Learn_Teacher_Student].[Tid],COUNT(*) count
                    FROM [Literacy].[dbo].[Learn_Teacher_Student] 
                    GROUP BY [Learn_Teacher_Student].[GroupId],[Learn_Teacher_Student].[Tid]
                )[Learn_Teacher_Group] ON [Learn_Teacher_Group].[GroupId] = [Learn_Group].[GroupId]
                LEFT JOIN(
                    SELECT [Learn_Group_Task].[GroupId],COUNT(*) task_count
                    FROM [Literacy].[dbo].[Learn_Group_Task]
                    GROUP BY [Learn_Group_Task].[GroupId] 
                )[Learn_Teacher_Task] ON [Learn_Teacher_Task].[GroupId] = [Learn_Group].[GroupId]
                LEFT JOIN(
                    SELECT dt.[GroupId],COUNT(
                        CASE 
                            WHEN dt.task_unfinish_count > 0 THEN 1
                        END
                    )task_unfinish_count
                    FROM(
                        SELECT [Learn_Group_Task].[GroupId],[Learn_Group_Task].[TaskId]
                            ,COUNT(
                                CASE
                                    WHEN [Learn_Task_Word_Student].[step] != 9 OR [Learn_Task_Word_Student].[step] IS NULL THEN 1
                                END
                            ) task_unfinish_count
                        FROM [Literacy].[dbo].[Learn_Group_Task]
                        LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task].[GroupId] 
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id] 
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] AND [Learn_Teacher_Student].[Pid] = [Learn_Task_Word_Student].[Pid] 
                        GROUP BY [Learn_Group_Task].[GroupId],[Learn_Group_Task].[TaskId]
                    )dt 
                    GROUP BY dt.[GroupId]
                        )[Learn_Teacher_Task_unfinish] ON [Learn_Teacher_Task_unfinish].[GroupId] = [Learn_Group].[GroupId]
                LEFT JOIN(
                SELECT [Learn_Group_Task].[GroupId], [Learn_Teacher_Student].[Pid], [Learn_Teacher_Student].[Tid]
                    , SUM(CASE WHEN [Learn_Task_Word_Student].[step] = 9 THEN 1 ELSE 0 END)* 5 AS score
                FROM [Literacy].[dbo].[Learn_Task]
                LEFT JOIN [Literacy].[dbo].[Learn_Group_Task] ON [Literacy].[dbo].[Learn_Group_Task].[TaskId] = [Literacy].[dbo].[Learn_Task].[TaskId]
                LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Literacy].[dbo].[Learn_Group_Task].[GroupId] = [Literacy].[dbo].[Learn_Teacher_Student].[GroupId]
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Literacy].[dbo].[Learn_Task_Word_Student].[Pid] = [Literacy].[dbo].[Learn_Teacher_Student].[Pid]
                WHERE ([Literacy].[dbo].[Learn_Teacher_Student].[Pid] IS NOT NULL AND [Literacy].[dbo].[Learn_Teacher_Student].[Tid] IS NOT NULL)
                AND [Literacy].[dbo].[Learn_Task].[Pid] IS NULL
                GROUP BY [Literacy].[dbo].[Learn_Group_Task].[GroupId], [Literacy].[dbo].[Learn_Teacher_Student].[Pid]
                , [Literacy].[dbo].[Learn_Teacher_Student].[Tid]
                ) group_score ON [group_score].[GroupId]=[Learn_Group].[GroupId]
                WHERE [Learn_Teacher_Group].[Tid] = :Tid
                {$string}
                GROUP BY [Learn_Group].[GroupName]
                    , [Learn_Group].[AppendDate]
                    , [Learn_Teacher_Task].[task_count]
                    , [Learn_Teacher_Task_unfinish].[task_unfinish_count]
                    , [Learn_Teacher_Group].count

        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_mission_list($data)
    {
        $condition = "";
        if ($condition) {
            $condition = " AND dt.isFinish=0 ";
        }
        $values = [
            "Tid" => 0,
            "GroupId" => 0,
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT dt.[GroupId],dt.[TaskId],[Learn_Task].[TaskName],
                    STUFF((
                        SELECT '、'+CAST(convert(varchar, [Learn_Word_Romanization].[word] )  AS VARCHAR(MAX))
                        FROM [Literacy].[dbo].[Learn_Task]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task].[TaskId] = [Learn_Task_Word].[task_id] 
                        LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Task_Word].[nid] = [Learn_Word_Romanization].[nid]
                        WHERE dt.[TaskId] = [Learn_Task].[TaskId]
                        FOR XML PATH(''),TYPE).value('(./text())[1]','VARCHAR(MAX)')
                            ,1,1,''
                        )words,CASE WHEN SUM(dt.[isFinish])>0 THEN 0 ELSE 1 END isfinish
                    FROM(
                        SELECT[Learn_Group_Task].GroupId,[Learn_Group_Task].[TaskId] ,[Learn_Teacher_Student].[Pid] ,[Learn_Teacher_Student].[Tid], CASE WHEN  COUNT( CASE  WHEN step != 9 OR step IS NULL THEN 1  END)>0 THEN 1 ELSE 0 END isFinish
                        FROM [Learn_Group_Task]
                        LEFT JOIN [Literacy].[dbo]. [Learn_Teacher_Student] ON [Learn_Group_Task].[GroupId]=[Learn_Teacher_Student].[GroupId]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId]=[Learn_Task_Word].[task_id]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[Pid]=[Learn_Teacher_Student].[Pid] AND [Learn_Task_Word_Student].[task_word_id]=[Learn_Task_Word].[task_word_id]
                        GROUP BY [Learn_Group_Task].[GroupId],[Learn_Group_Task].[TaskId],[Learn_Teacher_Student].[Pid],[Learn_Teacher_Student].[Tid] 
                    )dt
                    LEFT JOIN [Learn_Task] ON [Learn_Task].[TaskId]=dt.[TaskId]
                    WHERE dt.[GroupId]=:GroupId AND dt.[Tid]=:Tid
                    GROUP BY dt.[TaskId],dt.[GroupId],[Learn_Task].[TaskName]
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_learn_group_select_count($data)
    {
        $values = [
            "Tid" => 0,
        ];
        $string = "AND";
        $check = false;
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (array_key_exists('groupname', $data) && $data['groupname'] != '') {
            $string .= " [GroupName] LIKE '%'+:groupname+'%' AND";
            $values['groupname'] = $data['groupname'];
            $check = true;
        }
        if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            $string .= "[Learn_Group].[AppendDate] >= :start_date AND [Learn_Group].[AppendDate] < =dateadd(day,1,:end_date) AND";
            $values['start_date'] = $data['start_date'];
            $values['end_date'] = $data['end_date'];
            $check = true;
        }
        if ($check) {
            $string = rtrim($string, "AND");
        } else {
            $string = ltrim($string, "AND");
        }
        $sql = "SELECT COUNT(*)total
                FROM(
                    SELECT  ROW_NUMBER() OVER(ORDER BY [Learn_Group].[GroupId]) AS row_num,COALESCE(([Learn_Teacher_Task].task_count-[Learn_Teacher_Task_unfinish].task_unfinish_count)*100/[Learn_Teacher_Task].task_count,0) percentage,
                    [Learn_Group].[GroupName],[Learn_Group].[GroupId],[Learn_Group].[AppendDate],[Learn_Teacher_Group].count student_number,
                    COALESCE([Learn_Teacher_Task].task_count,0)task_count,COALESCE([Learn_Teacher_Task_unfinish].task_unfinish_count,0)task_unfinish_count,COALESCE(SUM([group_score].[score]),0) score
                FROM [Literacy].[dbo].[Learn_Group]
                INNER JOIN(
                    SELECT [Learn_Teacher_Student].[GroupId],[Learn_Teacher_Student].[Tid],COUNT(*) count
                    FROM [Literacy].[dbo].[Learn_Teacher_Student] 
                    GROUP BY [Learn_Teacher_Student].[GroupId],[Learn_Teacher_Student].[Tid]
                )[Learn_Teacher_Group] ON [Learn_Teacher_Group].[GroupId] = [Learn_Group].[GroupId]
                LEFT JOIN(
                    SELECT [Learn_Group_Task].[GroupId],COUNT(*) task_count
                    FROM [Literacy].[dbo].[Learn_Group_Task]
                    GROUP BY [Learn_Group_Task].[GroupId] 
                )[Learn_Teacher_Task] ON [Learn_Teacher_Task].[GroupId] = [Learn_Group].[GroupId]
                LEFT JOIN(
                    SELECT dt.[GroupId],COUNT(
                        CASE 
                            WHEN dt.task_unfinish_count > 0 THEN 1
                        END
                    )task_unfinish_count
                    FROM(
                        SELECT [Learn_Group_Task].[GroupId],[Learn_Group_Task].[TaskId]
                            ,COUNT(
                                CASE
                                    WHEN [Learn_Task_Word_Student].[step] != 9 OR [Learn_Task_Word_Student].[step] IS NULL THEN 1
                                END
                            ) task_unfinish_count
                        FROM [Literacy].[dbo].[Learn_Group_Task]
                        LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task].[GroupId] 
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id] 
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] AND [Learn_Teacher_Student].[Pid] = [Learn_Task_Word_Student].[Pid] 
                        GROUP BY [Learn_Group_Task].[GroupId],[Learn_Group_Task].[TaskId]
                    )dt 
                    GROUP BY dt.[GroupId]
                        )[Learn_Teacher_Task_unfinish] ON [Learn_Teacher_Task_unfinish].[GroupId] = [Learn_Group].[GroupId]
                LEFT JOIN(
                SELECT [Learn_Group_Task].[GroupId], [Learn_Teacher_Student].[Pid], [Learn_Teacher_Student].[Tid]
                    , SUM(CASE WHEN [Learn_Task_Word_Student].[step] = 9 THEN 1 ELSE 0 END)* 5 AS score
                FROM [Literacy].[dbo].[Learn_Task]
                LEFT JOIN [Literacy].[dbo].[Learn_Group_Task] ON [Literacy].[dbo].[Learn_Group_Task].[TaskId] = [Literacy].[dbo].[Learn_Task].[TaskId]
                LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Literacy].[dbo].[Learn_Group_Task].[GroupId] = [Literacy].[dbo].[Learn_Teacher_Student].[GroupId]
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Literacy].[dbo].[Learn_Task_Word_Student].[Pid] = [Literacy].[dbo].[Learn_Teacher_Student].[Pid]
                WHERE ([Literacy].[dbo].[Learn_Teacher_Student].[Pid] IS NOT NULL AND [Literacy].[dbo].[Learn_Teacher_Student].[Tid] IS NOT NULL)
                AND [Literacy].[dbo].[Learn_Task].[Pid] IS NULL
                GROUP BY [Literacy].[dbo].[Learn_Group_Task].[GroupId], [Literacy].[dbo].[Learn_Teacher_Student].[Pid]
                , [Literacy].[dbo].[Learn_Teacher_Student].[Tid]
                ) group_score ON [group_score].[GroupId]=[Learn_Group].[GroupId]
                WHERE [Learn_Teacher_Group].[Tid] =:Tid {$string}
                GROUP BY [Learn_Group].[GroupName],[Learn_Group].[GroupId],[Learn_Group].[AppendDate],[Learn_Teacher_Task].[task_count],[Learn_Teacher_Task_unfinish].[task_unfinish_count],[Learn_Teacher_Group].count
                ) AS selection
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_group_task($data)
    {
        $values = [
            "Tid" => 0,
            "GroupId" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT 
                STUFF((
                    SELECT '、'+[Learn_Word_Romanization].[word]
                    FROM [Literacy].[dbo].[Learn_Task_Word]
                    LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Word_Romanization].[nid] = [Learn_Task_Word].[nid]
                    WHERE [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                    FOR XML PATH('')
                ),1,1,'') task_words,
                [Learn_Task].[TaskName],dt.is_finish ,[Learn_Group_Task].[GroupId]
            FROM [Literacy].[dbo].[Learn_Group_Task]
            LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Task].[TaskId] = [Learn_Group_Task].[TaskId]
            LEFT JOIN (
                SELECT [GroupId]
                FROM [Literacy].[dbo].[Learn_Teacher_Student] 
                WHERE [Learn_Teacher_Student].[Tid] = :Tid
                GROUP BY [GroupId]
            )[Learn_Teacher_Student] ON [Learn_Group_Task].[GroupId] = [Learn_Teacher_Student].[GroupId] 
            LEFT JOIN (
                SELECT [Learn_Group_Task].[TaskId],[Learn_Group_Task].[GroupId]
                ,CASE WHEN COUNT(CASE WHEN [Learn_Task_Word_Student].step IS NULL OR [Learn_Task_Word_Student].step != 9 THEN 1 END)>0 THEN 0 ELSE 1 END is_finish
                FROM [Literacy].[dbo].[Learn_Group_Task]
                LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Group_Task].[GroupId] = [Learn_Teacher_Student].[GroupId] 
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word].[task_word_id] = [Learn_Task_Word_Student].[task_word_id] 
                    AND [Learn_Teacher_Student].[Pid] = [Learn_Task_Word_Student].[Pid] 
                GROUP BY [Learn_Group_Task].[TaskId],[Learn_Group_Task].[GroupId]
            )dt ON [Learn_Group_Task].[GroupId] = dt.[GroupId] AND [Learn_Group_Task].[TaskId] = dt.[TaskId]
            WHERE [Learn_Group_Task].[GroupId] = :GroupId
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_group_task_unfinish($data)
    {
        $values = [
            "Tid" => 0,
            "GroupId" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT dt.[GroupId],dt.[TaskId],[Learn_Task].[TaskName],dt.isFinish,STUFF((
                    SELECT '、'+CAST(convert(varchar, [Learn_Word_Romanization].[word] )  AS VARCHAR(MAX))
                    FROM [Literacy].[dbo].[Learn_Group_Task]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Task_Word].[nid] = [Learn_Word_Romanization].[nid]
                    WHERE dt.[TaskId] = [Learn_Group_Task].[TaskId]
                    FOR XML PATH(''),TYPE).value('(./text())[1]','VARCHAR(MAX)')
                ,1,1,''
                )words
            FROM(
                SELECT [Learn_Group_Task].[GroupId],[Learn_Group_Task].[TaskId],
                    CASE
                        WHEN COUNT(
                            CASE
                                WHEN step != 9 OR step IS NULL THEN 1
                            END
                        )>0
                        THEN 0
                        ELSE 1
                    END isFinish
                FROM [Literacy].[dbo].[Learn_Group_Task]
                LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Group_Task].[GroupId] = [Learn_Teacher_Student].[GroupId]
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id]
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[Pid] = [Learn_Teacher_Student].[Pid]
                    AND [Learn_Task_Word].[task_word_id] = [Learn_Task_Word_Student].[task_word_id]
                WHERE [Learn_Group_Task].[GroupId] = :GroupId
                GROUP BY [Learn_Group_Task].[GroupId],[Learn_Group_Task].[TaskId]
            )dt
            LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Task].[TaskId] = dt.[TaskId] 
            WHERE [Learn_Task].[Tid] = :Tid AND dt.isFinish = 0
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function patch_task_management_highlight($data)
    {
        $values = [
            "Tid" => 0,
            "TaskId" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "UPDATE [Literacy].[dbo].[Learn_Task]
            SET [Highlight] = ([Highlight]+1)%2
            WHERE [Tid] = :Tid AND [TaskId] = :TaskId
        ";

        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ["status" => "failure"];
        return ["status" => "success"];
    }

    public function get_task_download($data)
    {
        $values = [
            "Tid" => 0,
        ];
        $string = "AND";
        $check = false;
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (array_key_exists('taskname', $data)) {
            $string .= " [TaskName] LIKE '%'+:taskname+'%' AND";
            $values['taskname'] = $data['taskname'];
            $check = true;
        }
        if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            $string .= " task_download.[download_time] >= :start_date AND task_download.[download_time] < =dateadd(day,1,:end_date) AND";
            $values['start_date'] = $data['start_date'];
            $values['end_date'] = $data['end_date'];
            $check = true;
        }
        if ($check) {
            $string = rtrim($string, "AND");
        } else {
            $string = ltrim($string, "AND");
        }

        if (array_key_exists('excel', $data)) {
            unset($data['excel']);
            $select = "SELECT [Learn_Task].[TaskName] '任務名稱'
                        , COALESCE(task_group.group_count,0) '群組數量'
                        , [Learn_Task].[ApplyDate] '指派時間'
                        ,STUFF((
                            SELECT '、'+[Learn_Word_Romanization].[word]
                            FROM [Literacy].[dbo].[Learn_Task_Word]
                            LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Task_Word].[nid] = [Learn_Word_Romanization].[nid]
                            WHERE [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId]
                            FOR XML PATH('')
                        ), 1, 1, '') '生字'
                        , COALESCE(task_download.[download_count], 0) '下載次數'
                        , COALESCE(CAST(convert(varchar,task_download.[download_time], 120) as nvarchar), '-')  '下載時間'
            ";
        } else {
            $select = "SELECT [Learn_Task].[TaskId], [Learn_Task].[TaskName]
                        , COALESCE(task_group.group_count,0) group_count
                        , [Learn_Task].[ApplyDate]
                        ,STUFF((
                            SELECT '、'+[Learn_Word_Romanization].[word]
                            FROM [Literacy].[dbo].[Learn_Task_Word]
                            LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Task_Word].[nid] = [Learn_Word_Romanization].[nid]
                            WHERE [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId]
                            FOR XML PATH('')
                        ), 1, 1, '') words
                        , COALESCE(task_download.[download_count], 0) [download_count]
                        , COALESCE(CAST(convert(varchar,task_download.[download_time], 120) as nvarchar), '-') [download_time]
            ";
        }

        $sql = "{$select}
                FROM [Literacy].[dbo].[Learn_Task]
                LEFT JOIN (
                    SELECT [Learn_Group_Task].[TaskId], COUNT(*) group_count
                    FROM [Literacy].[dbo].[Learn_Group_Task]
                    GROUP BY [Learn_Group_Task].[TaskId]
                ) task_group ON task_group.[TaskId] = [Learn_Task].[TaskId]
                LEFT JOIN (
                    SELECT [Learn_Task_Word].[task_id] ,  COALESCE(MAX([Learn_Task_Word_Student].Download), 0) download_count
                        , MAX([Learn_Task_Word_Student].DownloadTime) download_time
                    FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] 
                    GROUP BY [Learn_Task_Word].[task_id] 
                ) task_download ON task_download.[task_id] = [Learn_Task].[TaskId]
                WHERE [Learn_Task].[Tid] = :Tid 
                {$string}
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_manage_identity($data)
    {
        $values = [
            "Tid" => 0,
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [Tid],[Grade]+'年'+[Teacher].[Class]+'班' class, [TeacherName] name
              FROM [Literacy].[dbo].[Teacher]
              WHERE [Tid] = :Tid
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_manage_current($data)
    {
        $values = [
            "Tid" => 0,
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $month = date("n");
        $year = date("Y");
        if ($month <= 12 && 8 <= $month || $month == 1) {
            $years = date("Y") + 1;
            $string = "AND Learning_Task.[EndDate] BETWEEN '$year'+'-8-1' AND '$years'+'-1-31'";
        } else {
            $string = "AND Learning_Task.[EndDate] BETWEEN '$year'+'-2-1' AND $year+'-7-31'";
        }
        $sql = "SELECT DISTINCT [Learn_Group_Task].[TaskId], Learning_Task.[TaskName],Learning_Task.[ApplyDate],Learning_Task.[EndDate],
                COALESCE(STUFF( 
                        (
                            SELECT '、' + CAST([Learn_Word_Romanization].[word] AS VARCHAR(MAX))
                            FROM [Literacy].[dbo].[Learn_Task_Word]
                            LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] 
                            LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Word_Romanization].[nid] = [Learn_Task_Word].[nid] 
                            WHERE [Learn_Task_Word].[task_id] = Learning_Task.[TaskId]
                            GROUP BY [Learn_Task_Word].[task_word_id], [Learn_Word_Romanization].[word]
                            FOR XML PATH ('')
                        ), 1, 1, ''
                    ),'-')words
                ,COALESCE(STUFF( 
                    (
                        SELECT '、' + CAST(dt.[word] AS VARCHAR(MAX))
                        FROM(
                            SELECT DISTINCT [Learn_Word_Romanization].[word]
                            FROM [Literacy].[dbo].[Learn_Group_Task]
                            LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id]
                            LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] 
                            LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Word_Romanization].[nid] = [Learn_Task_Word].[nid] 
                            WHERE [Learn_Task_Word].[task_id] = Learning_Task.[TaskId] AND [Learn_Word_Romanization].[word] NOT IN (
                
                            SELECT DISTINCT [word]
                                FROM(
                                    SELECT [Learn_Word_Romanization].[word], [Learn_Group_Task].[TaskId]
                                    FROM [Literacy].[dbo].[Learn_Group_Task]
                                    LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Group_Task].GroupId = [Learn_Teacher_Student].GroupId 
                                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id]
                                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] 
                                        AND [Learn_Task_Word_Student].[Pid] = [Learn_Teacher_Student].[Pid]
                                    LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Word_Romanization].[nid] = [Learn_Task_Word].[nid] 
                                    LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Group_Task].[TaskId] = [Learn_Task].[TaskId]
                                    WHERE ([Learn_Task_Word_Student].[step] != 9 OR [Learn_Task_Word_Student].[step] IS NULL)
                                    AND [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId] 
                                    AND [Learn_Task].[TaskId] = Learning_Task.[TaskId]
                                ) yy
                                WHERE [word] IS NOT NULL	
                            )
                        )dt
                        FOR XML PATH ('')
                    ), 1, 1, ''
                ),'-')finish_word
                ,COALESCE(STUFF( 
                    (
                        SELECT '、' + CAST(word.[word] AS VARCHAR(MAX))
                        FROM(
                            SELECT DISTINCT [word]
                            FROM(
                                SELECT [Learn_Word_Romanization].[word], [Learn_Teacher_Student].[Pid]
                                FROM [Literacy].[dbo].[Learn_Group_Task]
                                LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Group_Task].GroupId = [Learn_Teacher_Student].GroupId 
                                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id]
                                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] 
                                    AND [Learn_Task_Word_Student].[Pid] = [Learn_Teacher_Student].[Pid]
                                LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Word_Romanization].[nid] = [Learn_Task_Word].[nid] 
                                LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Group_Task].[TaskId] = [Learn_Task].[TaskId]
                                WHERE ([Learn_Task_Word_Student].[step] != 9 OR [Learn_Task_Word_Student].[step] IS NULL)
                                AND [Learn_Task_Word].[task_id] = Learning_Task.[TaskId] 
                                --AND [Learn_Task].[TaskId] = 151
                            ) yy
                        ) word
                        FOR XML PATH ('')
                    ), 1, 1, ''
                ),'-')unfinish_word
                FROM [Literacy].[dbo].[Learn_Group_Task]
                LEFT JOIN [Literacy].[dbo].[Learn_Task] AS Learning_Task ON [Learn_Group_Task].[TaskId] = Learning_Task.[TaskId]
                WHERE Learning_Task.[Tid] = :Tid 
                {$string}
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_manage_current_excel($data)
    {
        $values = [
            "Tid" => 0,
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $month = date("n");
        $year = date("Y");
        if ($month <= 12 && 8 <= $month || $month == 1) {
            $years = date("Y") + 1;
            $string = "AND Learning_Task.[EndDate] BETWEEN '$year'+'-8-1' AND '$years'+'-1-31'";
        } else {
            $string = "AND Learning_Task.[EndDate] BETWEEN '$year'+'-2-1' AND $year+'-7-31'";
        }
        $sql = "SELECT DISTINCT Learning_Task.[ApplyDate] '指派日期', Learning_Task.[EndDate] '結束日期', Learning_Task.[TaskName] '任務名稱',
                COALESCE(STUFF( 
                        (
                            SELECT '、' + CAST([Learn_Word_Romanization].[word] AS VARCHAR(MAX))
                            FROM [Literacy].[dbo].[Learn_Task_Word]
                            LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] 
                            LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Word_Romanization].[nid] = [Learn_Task_Word].[nid] 
                            WHERE [Learn_Task_Word].[task_id] = Learning_Task.[TaskId]
                            GROUP BY [Learn_Task_Word].[task_word_id], [Learn_Word_Romanization].[word]
                            FOR XML PATH ('')
                        ), 1, 1, ''
                    ),'-') '任務生字'
                ,COALESCE(STUFF( 
                    (
                        SELECT '、' + CAST(word.[word] AS VARCHAR(MAX))
                        FROM(
                            SELECT DISTINCT [word]
                            FROM(
                                SELECT [Learn_Word_Romanization].[word], [Learn_Teacher_Student].[Pid]
                                FROM [Literacy].[dbo].[Learn_Group_Task]
                                LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Group_Task].GroupId = [Learn_Teacher_Student].GroupId 
                                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id]
                                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] 
                                    AND [Learn_Task_Word_Student].[Pid] = [Learn_Teacher_Student].[Pid]
                                LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Word_Romanization].[nid] = [Learn_Task_Word].[nid] 
                                LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Group_Task].[TaskId] = [Learn_Task].[TaskId]
                                WHERE ([Learn_Task_Word_Student].[step] != 9 OR [Learn_Task_Word_Student].[step] IS NULL)
                                AND [Learn_Task_Word].[task_id] = Learning_Task.[TaskId] 
                                --AND [Learn_Task].[TaskId] = 151
                            ) yy
                        ) word
                        FOR XML PATH ('')
                    ), 1, 1, ''
                ),'-') '未完成生字'
                FROM [Literacy].[dbo].[Learn_Group_Task]
                LEFT JOIN [Literacy].[dbo].[Learn_Task] AS Learning_Task ON [Learn_Group_Task].[TaskId] = Learning_Task.[TaskId]
                WHERE Learning_Task.[Tid] = :Tid 
                {$string}
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_manage_group_unfinish_num($data)
    {
        $values = [
            "Tid" => 0,
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $month = date("n");
        $year = date("Y");
        if ($month <= 12 && 8 <= $month || $month == 1) {
            $years = date("Y") + 1;
            $string = "AND [Learn_Task].[EndDate] BETWEEN '$year'+'-8-1' AND '$years'+'-1-31'";
        } else {
            $string = "AND [Learn_Task].[EndDate] BETWEEN '$year'+'-2-1' AND $year+'-7-31'";
        }
        $sql = "SELECT COUNT(dt2.TaskId) total ,COUNT(dt2.status) unfinish
                FROM(
                SELECT DISTINCT [Learn_Group_Task].[TaskId] , CASE  WHEN task_group_finish.[percentage] = 100 THEN '0'  ELSE  '1' END status
                FROM [Literacy].[dbo].[Learn_Group_Task]
                LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Group_Task].[TaskId] = [Learn_Task].[TaskId]
                LEFT JOIN(
                    SELECT dt.[TaskId],COUNT(CASE WHEN dt.isFinish = 1 THEN 1 END)*100/COUNT(*) percentage,COUNT(CASE WHEN dt.isFinish = 1 THEN 1 END) finish,COUNT(*)total
                    FROM(
                        SELECT [Learn_Group_Task].[TaskId], [Learn_Group_Task].[GroupId],[Learn_Teacher_Student].[Pid]
                            ,CASE WHEN COUNT(CASE
                                WHEN [Learn_Task_Word_Student].[step] IS NULL OR [Learn_Task_Word_Student].[step] !=9 THEN 1
                            END)>0 THEN 0 ELSE 1 END isFinish
                        FROM [Literacy].[dbo].[Learn_Group_Task]
                        LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Group_Task].[GroupId] = [Learn_Teacher_Student].[GroupId]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                        GROUP BY [Learn_Group_Task].[TaskId], [Learn_Group_Task].[GroupId],[Learn_Teacher_Student].[Pid]
                    )dt
                    GROUP BY dt.[TaskId]
                )task_group_finish ON task_group_finish.[TaskId] = [Learn_Group_Task].[TaskId]
                WHERE [Learn_Task].[Tid] =:Tid {$string}
                )dt2     
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_manage_mission_word($data)
    {
        $values = [
            "Tid" => 0,
            "word" => '',
            "task_id" => 0,
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT STUFF (( 
                    SELECT '、'+tmp.[StuName]
                    FROM(
                        SELECT dt.TaskId ,dt.Tid ,dt.word ,[Student].[StuName] 
                        FROM  (
                        SELECT  [Learn_Task].[TaskId],[Learn_Task].[Tid],[Learn_Group_Task].[GroupId]
                            ,[Learn_Teacher_Student].[Pid],[Learn_Word_Romanization].[word],[Learn_Task_Word_Student].[step]
                            ,(CASE WHEN [Learn_Task_Word_Student].[step]!=9 OR [Learn_Task_Word_Student].[step] IS NULL THEN 0 ELSE 1 END )finish
                        FROM [Literacy].[dbo].[Learn_Task]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task].[TaskId]=[Learn_Task_Word].[task_id] 
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] 
                        LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Word_Romanization].[nid] = [Learn_Task_Word].[nid] 
                        LEFT JOIN [Literacy].[dbo].[Learn_Group_Task] ON [Learn_Group_Task].[TaskId]=[Learn_Task].[TaskId]
                        LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId]=[Learn_Group_Task].[GroupId] 
                        WHERE [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId] AND [Learn_Task].[TaskId]=:task_id 
                    )dt
                    LEFT JOIN [Literacy].[dbo].[Student] ON [Student].[Pid]=dt.Pid 
                    WHERE dt.[Tid] =:Tid AND dt.word=:word AND dt.finish=0
                )tmp
                FOR XML PATH('')
                ), 1, 1, ''
                )StuName              
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_learn_analysis_assign($data)
    {
        $values = [
            "Tid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [Learn_Task_Count].task_count [teacher_task_count],
                CAST(
                    (([Learn_Task_Count].task_count-[Learn_Task_unfinish_Count].unfinish_count )*100/[Learn_Task_Count].task_count)
                AS varchar(MAX)) + '%' [teacher_task_percentage],
                [Learn_Task_Count].task_count-[Learn_Task_unfinish_Count].unfinish_count [teacher_task_finish_count],
                [teacher_word_count].[word_count],
                [teacher_word_count].[word_finish_count],
                CAST([teacher_word_count].[word_finish_count]*100/[teacher_word_count].[word_count] AS varchar(max))+ '%' [word_percentage]
            FROM [Literacy].[dbo].[Teacher]
            LEFT JOIN (
                SELECT [Learn_Task].[Tid], COUNT(*) task_count
                FROM [Literacy].[dbo].[Learn_Task]
                GROUP BY [Learn_Task].[Tid]
            )[Learn_Task_Count] ON [Teacher].[Tid] = [Learn_Task_Count].[Tid] 
            LEFT JOIN(
                SELECT dt.[Tid],COUNT(
                        CASE
                            WHEN dt.unfinish_count !=0 OR dt.unfinish_count IS NULL THEN 1
                        END
                    )unfinish_count
                FROM(
                    SELECT [Learn_Teacher_Student].[Tid],[Learn_Group_Task].[TaskId],
                        COUNT(
                            CASE
                                WHEN [Learn_Task_Word_Student].step != 9 OR [Learn_Task_Word_Student].step IS NULL THEN 1
                            END
                        )unfinish_count
                    FROM [Literacy].[dbo].[Learn_Group_Task] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Group_Task].[GroupId] = [Learn_Teacher_Student].[GroupId]
                    INNER JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Group_Task].[TaskId] = [Learn_Task].[TaskId] AND [Learn_Task].[Tid] = [Learn_Teacher_Student].[Tid] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                    GROUP BY [Learn_Teacher_Student].[Tid],[Learn_Group_Task].[TaskId]
                )dt
                GROUP BY dt.[Tid]
            )[Learn_Task_unfinish_Count] ON [Teacher].[Tid] = [Learn_Task_unfinish_Count].[Tid] 
            LEFT JOIN (
            SELECT dt.[Tid],
                COUNT(
                    CASE WHEN unfinish_count = 0 THEN 1 END
                )word_finish_count,
                COUNT(*) word_count
            FROM(
                SELECT [Learn_Task].[Tid],[Learn_Task_Word].[task_word_id],
                    COUNT(
                        CASE WHEN [Learn_Task_Word_Student].step != 9 OR [Learn_Task_Word_Student].step IS NULL THEN 1 END
                    )unfinish_count
                FROM [Literacy].[dbo].[Learn_Task]
                LEFT JOIN [Literacy].[dbo].[Learn_Group_Task] ON [Learn_Group_Task].[TaskId] = [Learn_Task].[TaskId] 
                LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Group_Task].[GroupId] = [Learn_Teacher_Student].[GroupId]
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task].[TaskId] = [Learn_Task_Word].[task_id]
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                GROUP BY [Learn_Task].[Tid],[Learn_Task_Word].[task_word_id]
            )dt 
            GROUP BY dt.[Tid]
            )[teacher_word_count] ON [Teacher].[Tid] = [teacher_word_count].[Tid] 
            WHERE [Teacher].[Tid] = :Tid
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_learn_analysis_self($data)
    {
        $values = [
            "Tid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "WITH [students] AS(
                SELECT [Student].[Pid]
                FROM [Literacy].[dbo].[Student]
                WHERE [Student].[Tid] = :Tid
            )
            SELECT SUM([Learn_Task_Count].task_count) [self_task_count],
                CAST(
                    ((SUM([Learn_Task_Count].task_count)-SUM([Learn_Task_unfinish_Count].unfinish_count) )*100/SUM([Learn_Task_Count].task_count))
                AS varchar(MAX)) + '%' [self_task_percentage],
                SUM([Learn_Task_Count].task_count)-SUM([Learn_Task_unfinish_Count].unfinish_count) [self_task_finish_count],
                SUM([teacher_word_count].[word_count]) [word_count],
                SUM([teacher_word_count].[word_finish_count]) [word_finish_count],
                CAST(SUM([teacher_word_count].[word_finish_count])*100/SUM([teacher_word_count].[word_count]) AS varchar(max))+ '%' [word_percentage]
            FROM [Literacy].[dbo].[Student]
            LEFT JOIN (
                SELECT [Learn_Task].[Pid], COUNT(*) task_count
                FROM [Literacy].[dbo].[Learn_Task]
                WHERE [Learn_Task].[Pid] IN (
                    SELECT [students].[Pid]
                    FROM [students]
                )
                GROUP BY [Learn_Task].[Pid]
            )[Learn_Task_Count] ON [Student].[Pid] = [Learn_Task_Count].[Pid] 
            LEFT JOIN(
                SELECT dt.[Pid],COUNT(
                        CASE
                            WHEN dt.unfinish_count !=0 OR dt.unfinish_count IS NULL THEN 1
                        END
                    )unfinish_count
                FROM(
                    SELECT [Learn_Task].[Pid],[Learn_Task].[TaskId],
                        COUNT(
                            CASE
                                WHEN [Learn_Task_Word_Student].step != 9 OR [Learn_Task_Word_Student].step IS NULL THEN 1
                            END
                        )unfinish_count
                    FROM [Literacy].[dbo].[Learn_Task]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                    WHERE [Learn_Task].[Pid] IN (
                        SELECT [students].[Pid]
                        FROM [students]
                    )
                    GROUP BY [Learn_Task].[Pid],[Learn_Task].[TaskId]
                )dt
                GROUP BY dt.[Pid]
            )[Learn_Task_unfinish_Count] ON [Student].[Pid] = [Learn_Task_unfinish_Count].[Pid] 
            LEFT JOIN (
            SELECT dt.[Pid],
                COUNT(
                    CASE WHEN unfinish_count = 0 THEN 1 END
                )word_finish_count,
                COUNT(*) word_count
            FROM(
                SELECT [Learn_Task].[Pid],[Learn_Task_Word].[task_word_id],
                    COUNT(
                        CASE WHEN [Learn_Task_Word_Student].step != 9 OR [Learn_Task_Word_Student].step IS NULL THEN 1 END
                    )unfinish_count
                FROM [Literacy].[dbo].[Learn_Task]
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task].[TaskId] = [Learn_Task_Word].[task_id]
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                    WHERE [Learn_Task].[Pid] IN (
                        SELECT [students].[Pid]
                        FROM [students]
                    )
                GROUP BY [Learn_Task].[Pid],[Learn_Task_Word].[task_word_id]
            )dt 
            GROUP BY dt.[Pid]
            )[teacher_word_count] ON [Student].[Pid] = [teacher_word_count].[Pid] 
            WHERE [Student].[Pid] IN (
                SELECT [students].[Pid]
                FROM [students]
            )
            GROUP BY [Student].[Tid] 
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_exam_account_data($data)
    {
        $values = [
            "Tid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [Teacher].[Tid], [Teacher].[TeacherName] 
                , [SchoolList].[CityId] + ' ' + [SchoolList].[SchoolName] area
                , [Teacher].[Grade]+'年'+(CASE [Teacher].[Class]
                                            WHEN '資優班' THEN '資優'
                                            WHEN '資源班' THEN '資源'
                                            WHEN '測試用班' THEN '測試用'
                                            WHEN '補救教學班' THEN '補救教學'
                                            WHEN '音樂班' THEN '音樂'
                                            WHEN '體育班' THEN '體育'
                                                ELSE [Teacher].[Class] END)+'班'  class
                , [Teacher].[TeacherMail], [Teacher].[PassWD_ChangeDate]
                , [Teacher].[Photo]
                FROM [Literacy].[dbo].[Teacher]
                LEFT JOIN [Literacy].[dbo].[SchoolList] ON [Teacher].[Sid] = [SchoolList].[Sid] 
                WHERE [Teacher].[Tid] = :Tid
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function post_exam_photo($data)
    {
        $values = [
            "Tid" => '',
            "photo" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[Teacher] Set 
                [Photo] = :photo
                WHERE [Tid] = :Tid
                ";
        $sth = $this->container->db->prepare($sql);
        if ($sth->execute($values)) {
            $status = [
                "status" => "success",
                "message" => "修改成功"
            ];
        } else {
            $status = [
                "status" => "failure",
                "message" => "修改失敗"
            ];
        }
        return $status;
    }

    public function patch_learn_exam_mail($data)
    {
        $values = [
            "tid" => '',
            "mail" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[Teacher] Set 
                [TeacherMail] = :mail
                WHERE [Tid] = :tid
                ";
        $sth = $this->container->db->prepare($sql);
        if ($sth->execute($values)) {
            $status = [
                "status" => "success",
                "message" => "修改成功"
            ];
        } else {
            $status = [
                "status" => "failure",
                "message" => "修改失敗"
            ];
        }
        return $status;
    }
    public function get_task_download_download($data)
    {
        $values = [
            "TaskId" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT *
                FROM(
                SELECT [Learn_Group_Task].[TaskId],[Learn_Group_Task].[GroupId],[Learn_Group].[GroupName],
                STUFF((
                SELECT [Learn_Task_Word].[task_id]
                    ,[Learn_Teacher_Student].[GroupId]
                    ,[Learn_Task_Word_Student].[Pid]
                    ,[Student].[StuName]
                    ,MAX([Learn_Task_Word_Student].[DownloadTime])[DownloadTime]
                FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_word_id] = [Learn_Task_Word_Student].[task_word_id] 
                LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[Pid] = [Learn_Task_Word_Student].[Pid] 
                LEFT JOIN [Literacy].[dbo].[Student] ON [Learn_Task_Word_Student].[Pid]=[Student].[Pid]
                WHERE [Learn_Task_Word_Student].[DownloadTime] IS NOT NULL
                    AND [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task].[GroupId]
                    AND [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId]
                GROUP BY [Learn_Task_Word].[task_id], [Learn_Task_Word_Student].[Pid],[Learn_Teacher_Student].[GroupId]  ,[Student].[StuName]
                FOR XML RAW, ELEMENTS
                        ), 1, 0, '') group_member
                FROM [Literacy].[dbo].[Learn_Group_Task]
                LEFT JOIN  [Literacy].[dbo].[Learn_Task] ON [Learn_Group_Task].[TaskId] = [Learn_Task].[TaskId] 
                LEFT JOIN  [Literacy].[dbo].[Learn_Group] ON [Learn_Group_Task].[GroupId]=[Learn_Group].[GroupId]
                )dt
                WHERE dt.group_member IS NOT NULL AND dt.TaskId=:TaskId
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $key_result => $value) {
            $tmpvalue = $value['group_member'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue</a>");
            if ($tmpvalue == "") {
                $result[$key_result]['group_member'] = $tmpArrs;
                goto Endquotation;
            }
            foreach ($xml as $t) {
                $tmpArr = [];
                foreach ($t as $a => $b) {
                    $tmpArr[$a] = '';
                    foreach ((array)$b as $c => $d) {
                        $tmpArr[$a] = $d;
                    }
                }
                $tmpArrs[] = $tmpArr;
            }
            $result[$key_result]['group_member'] = $tmpArrs;
            Endquotation:
        }
        return $result;
    }
    public function get_task_task($data)
    {
        $values = [
            "TaskId" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [TaskId],[Tid],[TaskName],[ApplyDate],[EndDate], 
                STUFF( 
                    (
                        SELECT [Learn_Word_Romanization].[word],[Learn_Task_Word].[nid] 
                        FROM [Literacy].[dbo].[Learn_Task_Word]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] 
                        LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Word_Romanization].[nid] = [Learn_Task_Word].[nid] 
                        WHERE [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId]
                        FOR XML RAW, ELEMENTS
                    ), 1,0, '')words,
                STUFF((
                    SELECT [Learn_Group].[GroupName],[Learn_Group].[GroupId] 
                    FROM [Literacy].[dbo].[Learn_Group_Task]
                    LEFT JOIN [Literacy].[dbo].[Learn_Group] ON [Learn_Group_Task].[GroupId]=[Learn_Group].[GroupId]        
                    WHERE [Learn_Group_Task].[TaskId] = [Learn_Task].[TaskId]
                    FOR XML RAW, ELEMENTS
                    ), 1, 0, '') groupname
                FROM [Literacy].[dbo].[Learn_Task]
                WHERE [TaskId]=:TaskId
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $key_result => $value) {
            $tmpvalue = $value['groupname'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue</a>");
            if ($tmpvalue == "") {
                $result[$key_result]['groupname'] = $tmpArrs;
                goto Endquotation_group;
            }
            foreach ($xml as $t) {
                $tmpArr = [];
                foreach ($t as $a => $b) {
                    $tmpArr[$a] = '';
                    foreach ((array)$b as $c => $d) {
                        $tmpArr[$a] = $d;
                    }
                }
                $tmpArrs[] = $tmpArr;
            }
            $result[$key_result]['groupname'] = $tmpArrs;
            Endquotation_group:
            $tmpvalue_word = $value['words'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue_word</a>");
            if ($tmpvalue_word == "") {
                $result[$key_result]['words'] = $tmpArrs;
                goto Endquotation_word;
            }
            foreach ($xml as $t) {
                $tmpArr = [];
                foreach ($t as $a => $b) {
                    $tmpArr[$a] = '';
                    foreach ((array)$b as $c => $d) {
                        $tmpArr[$a] = $d;
                    }
                }
                $tmpArrs[] = $tmpArr;
            }
            $result[$key_result]['words'] = $tmpArrs;
            Endquotation_word:
        }

        return $result;
    }
    public function get_change_role($data)
    {
        $values = [
            "Tid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [Pid],[StuName],CONVERT(INT, [SeatNum])SeatNum
                FROM [Literacy].[dbo].[Student] 
                WHERE Tid = :Tid
                ORDER BY SeatNum
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}
