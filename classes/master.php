<?php

use PHPMailer\PHPMailer\PHPMailer;
use Slim\Views\PhpRENDerer;
use Slim\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class master
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function patch_school_basic_datamsg($data)
    {
        $values = [
            'Sid' => 0,
            'txt_Principal' => '',
            'txt_Senate' => '',
            'txt_Contact' => '',
            'txt_Contact_Titles' => '',
            'txt_Contact_Phone' => '',
            'txt_Contact_EMail_1' => '',
            'txt_Contact_EMail_2' => '',
            'txt_School_C_1' => '',
            'txt_School_C_2' => '',
            'txt_School_C_3' => '',
            'txt_School_C_4' => '',
            'txt_School_C_5' => '',
            'txt_School_C_6' => '',
            'txt_School_C_Other' => '',
            'txt_School_C_Total' => '',
            'txt_School_S_1' => '',
            'txt_School_S_2' => '',
            'txt_School_S_3' => '',
            'txt_School_S_4' => '',
            'txt_School_S_5' => '',
            'txt_School_S_6' => '',
            'txt_School_S_Other' => '',
            'txt_School_S_Total' => '',
            'txt_Exam_C_1' => '',
            'txt_Exam_C_2' => '',
            'txt_Exam_C_3' => '',
            'txt_Exam_C_4' => '',
            'txt_Exam_C_5' => '',
            'txt_Exam_C_6' => '',
            'txt_Exam_C_Other' => '',
            'txt_Exam_C_Total' => '',
            'txt_Exam_S_1' => '',
            'txt_Exam_S_2' => '',
            'txt_Exam_S_3' => '',
            'txt_Exam_S_4' => '',
            'txt_Exam_S_5' => '',
            'txt_Exam_S_6' => '',
            'txt_Exam_S_Other' => '',
            'txt_Exam_S_Total' => ''
        ];


        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[SchoolList] SET  
                [Principal] = :txt_Principal, [Senate] = :txt_Senate, 
                [Contact] = :txt_Contact, [Contact_Titles] = :txt_Contact_Titles, 
                [Contact_Phone] = :txt_Contact_Phone, 
                [Contact_EMail_1] = :txt_Contact_EMail_1, 
                [Contact_EMail_2] = :txt_Contact_EMail_2,
                [School_C_1] = :txt_School_C_1, [School_C_2] = :txt_School_C_2, 
                [School_C_3] = :txt_School_C_3, [School_C_4] = :txt_School_C_4, 
                [School_C_5] = :txt_School_C_5, [School_C_6] = :txt_School_C_6, 
                [School_C_Other] = :txt_School_C_Other, 
                [School_C_Total] = :txt_School_C_Total, 
                [School_S_1] = :txt_School_S_1, [School_S_2] = :txt_School_S_2, 
                [School_S_3] = :txt_School_S_3, [School_S_4] = :txt_School_S_4, 
                [School_S_5] = :txt_School_S_5, [School_S_6] = :txt_School_S_6, 
                [School_S_Other] = :txt_School_S_Other, 
                [School_S_Total] = :txt_School_S_Total, 
                [Exam_C_1] = :txt_Exam_C_1, [Exam_C_2] = :txt_Exam_C_2, 
                [Exam_C_3] = :txt_Exam_C_3, [Exam_C_4] = :txt_Exam_C_4, 
                [Exam_C_5] = :txt_Exam_C_5, [Exam_C_6] = :txt_Exam_C_6, 
                [Exam_C_Other] = :txt_Exam_C_Other, 
                [Exam_C_Total] = :txt_Exam_C_Total, 
                [Exam_S_1] = :txt_Exam_S_1, [Exam_S_2] = :txt_Exam_S_2, 
                [Exam_S_3] = :txt_Exam_S_3, [Exam_S_4] = :txt_Exam_S_4, 
                [Exam_S_5] = :txt_Exam_S_5, [Exam_S_6] = :txt_Exam_S_6, 
                [Exam_S_Other] = :txt_Exam_S_Other,
                [Exam_S_Total] = :txt_Exam_S_Total,
                [UpdateTime] = GETDATE()
                WHERE [Sid] = :Sid
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            var_dump($sth->errorInfo());
            exit(0);
            return ["status" => "fail"];
        }
        return ["status" => "success"];
    }

    public function getSchoolDataName($data)
    {
        $values = [
            "sid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Sid],[CityId],[PostId],[SchoolID]
                ,[SchoolName],[Class],[PassWD],[PassWD_MD5]
                ,[PWHis],[PassWD_ChangeDate],[Used],[ExamPower]
                ,[Principal],[Senate],[Contact],[Contact_Titles]
                ,[Contact_Phone],[Contact_CellPhone],[Contact_EMail_1],[Contact_EMail_2]
                ,[ApplyFiles],[AddTime],[UpdateTime],[SendPWTime]
                ,[MsgRM],[MasterRM],[UpInfo],[ExamProgramKind],[SchDelFlag]

                FROM [Literacy].[dbo].[SchoolList]
                WHERE [Literacy].[dbo].[SchoolList].Sid = :sid
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getSchoolDataSchool($data)
    {
        $values = [
            "sid" => '',
            'sid2' => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('sid', $data)) {
            $values['sid2'] = $data['sid'];
        }

        $sql = "SELECT ( [School_C_1] ) AS Grade1
                ,( [School_C_2] ) AS Grade2
                ,( [School_C_3] ) AS Grade3
                ,( [School_C_4] ) AS Grade4
                ,( [School_C_5] ) AS Grade5
                ,( [School_C_6] ) AS Grade6
                ,( [School_C_Other] ) AS GradeOther
                ,( [School_C_Total] ) AS GradeTotal
        
                FROM [Literacy].[dbo].[SchoolList]
                WHERE [Literacy].[dbo].[SchoolList].Sid = :sid

                UNION ALL

                SELECT ( [School_S_1] ) AS Grade1
                ,( [School_S_2] ) AS Grade2
                ,( [School_S_3] ) AS Grade3
                ,( [School_S_4] ) AS Grade4
                ,( [School_S_5] ) AS Grade5
                ,( [School_S_6] ) AS Grade6
                ,( [School_S_Other] ) AS GradeOther
                ,( [School_S_Total] ) AS GradeTotal
        
                FROM [Literacy].[dbo].[SchoolList]
                WHERE [Literacy].[dbo].[SchoolList].Sid = :sid2
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getSchoolDataExam($data)
    {
        $values = [
            "sid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('sid', $data)) {
            $values['sid2'] = $data['sid'];
        }

        $sql = "SELECT ( [Exam_C_1] ) AS Grade1
                ,( [Exam_C_2] ) AS Grade2
                ,( [Exam_C_3] ) AS Grade3
                ,( [Exam_C_4] ) AS Grade4
                ,( [Exam_C_5] ) AS Grade5
                ,( [Exam_C_6] ) AS Grade6
                ,( [Exam_C_Other] ) AS GradeOther
                ,( [Exam_C_Total] ) AS GradeTotal
        
                FROM [Literacy].[dbo].[SchoolList]
                WHERE [Literacy].[dbo].[SchoolList].Sid = :sid

                UNION ALL

                SELECT ( [Exam_S_1] ) AS Grade1
                ,( [Exam_S_2] ) AS Grade2
                ,( [Exam_S_3] ) AS Grade3
                ,( [Exam_S_4] ) AS Grade4
                ,( [Exam_S_5] ) AS Grade5
                ,( [Exam_S_6] ) AS Grade6
                ,( [Exam_S_Other] ) AS GradeOther
                ,( [Exam_S_Total] ) AS GradeTotal
        
                FROM [Literacy].[dbo].[SchoolList]
                WHERE [Literacy].[dbo].[SchoolList].Sid = :sid2
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function master_SendMail($data)
    {
        $account = $data['email'];
        $subject = "【閱讀評量與教學服務網】重設密碼通知信";
        $passwd = $data['passwd'];

        $body = "";
        $body .= "<p style='line-height: 150%'>";
        $body .= "您所申請的學校帳號，臨時管理碼為「<font color=\"blue\">{$passwd}</font>」，請依使用臨時密碼登入並重新修改密碼。</p>";
        $body .= "<br><a href='{$_SERVER['HTTP_HOST']}/School_Login.aspx' target='_blank'>閱讀評量與教學中心 識字量 校方管理人員登入</a><br>";
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
                "status" => "寄信成功!",
            ];
        } catch (Exception $e) {
            return [
                "message" => $mail->ErrorInfo,
                "status" => "failure",
            ];
        }
    }

    public function check_school_origin_passwd($data)
    {
        $values = [
            "sid" => '',
            "passwd_old" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT * 
                FROM [SchoolList] 
                WHERE [Sid] = :sid
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

    public function master_go_exam($data)
    {

        $values = [
            "tid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Tid], [Sid], [TeacherName]
                , [TeacherMail], [GraduationYear]
                , [Grade], [Class]
                FROM [Literacy].[dbo].[Teacher] 
                WHERE [Tid] = :tid
                ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $status = ["status" => "failed"];
        session_start();
        foreach ($result as $key => $value) {
            $_SESSION['Tid'] = $value['Tid'];
            $_SESSION['login'] = 0;
            $status = ["status" => "success"];
        }
        session_write_close();
        return $status;
    }

    public function check_master($data)
    {
        $values = [
            "email" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Sid], [SchoolName], [Contact_EMail_1] 
                FROM [SchoolList] 
                WHERE [Contact_EMail_1] = :email
                AND [Used] = 0
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

    public function patch_master_tmp_passwd($data)
    {

        // var_dump($data);
        // exit(0);
        $values = [
            "passwd" => '',
            "Contact_EMail_1" => '',
            "Sid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "Update [SchoolList] Set 
                [PassWD] = :passwd, 
                [PassWD_ChangeDate] = NULL, 
                [MasterRM] = 'MasterRM' + ', ' +  '申請忘記密碼'
                WHERE [Sid] = :Sid
                AND [Contact_EMail_1] = :Contact_EMail_1
        ";

        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => $sth->errorInfo()
            ];
        }
        return [
            "status" => "success"
        ];
    }

    public function patch_school_passwd($data)
    {
        $values = [
            "sid" => '',
            "passwd" => '',
            "passwd_again" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (!$values['passwd'] === $values['passwd_again']) {
            return [
                "status" => "請檢查確認密碼是否有誤!"
            ];
        }
        unset($values['passwd_again']);

        $values['passwd_md5'] = md5($values['passwd']);

        $sql = "Update [SchoolList] Set 
                [PassWD] = :passwd
                , [PassWD_MD5] = :passwd_md5
                , [PassWD_ChangeDate] = GETDATE()
                WHERE [Sid] = :sid
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

    public function get_master_school_teacher($data)
    {
        $values = [
            "sid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('excel', $data)) {
            unset($data['excel']);
            $select = "SELECT [Teacher].[TeacherName] '姓名'
            , [Teacher].[TeacherMail] '信箱'
            , [Teacher].[Grade]+'年'+[Teacher].[Class]+'班' '班級資訊'
            , COUNT([Student].[Pid]) AS '學生人數' 
            , [Teacher].[PassWD] '管理碼'
            ";
        } else {
            $select = "SELECT ROW_NUMBER() OVER(ORDER BY [Teacher].[Sid]) AS 'key'
            , [Teacher].[Sid], [Teacher].[Tid]
            , [Teacher].[TeacherName], [Teacher].[TeacherMail]
            , [Teacher].[Grade], [Teacher].[Class]
            , COUNT([Student].[Pid]) AS [StuNum] 
            , [Teacher].[PassWD]
            ";
        }

        $sql = "{$select}
                FROM [Literacy].[dbo].[Teacher] 
                LEFT OUTER JOIN [Student] ON [Teacher].[Tid] = [Student].[Tid] 
                GROUP BY [Teacher].[Tid], [Teacher].[Sid], [Teacher].[TeacherName]
                , [Teacher].[TeacherMail], [Teacher].[GraduationYear]
                , [Teacher].[Grade], [Teacher].[Class], [Teacher].[PassWD]
                , [Teacher].[Used], [Teacher].[OpenSet], [Teacher].[AddTime] 
                HAVING ([Teacher].[Sid] = :sid) 
                ORDER BY (
                    CASE [Grade] 
                        WHEN '一' THEN '1' 
                        WHEN '二' THEN '2' 
                        WHEN '三' THEN '3' 
                        WHEN '四' THEN '4' 
                        WHEN '五' THEN '5' 
                        WHEN '六' THEN '6' 
                        WHEN '畢' THEN '7' 
                        END
                    ), RIGHT('0' + [Literacy].[dbo].[Teacher].[Class], 2)
        ";

        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function patch_teacher($data)
    {
        $values = [
            "tid" => '',
            "grade" => '',
            "class" => '',
            "teachername" => '',
            "teachermail" => '',
            "graduationyear" => null,
            "passwd" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "Update [Teacher] Set 
                [Grade] = :grade
                , [Class] = :class
                , [TeacherName] = :teachername
                , [TeacherMail] = :teachermail
                , [PassWD] = :passwd
                , [PassWD_ChangeDate] = GETDATE()
                , [GraduationYear] = :graduationyear
                WHERE [Tid] = :tid
                ";

        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "message" => "修改教師資訊失敗"
            ];
        }
        return [
            "status" => "success",
            "message" => "修改教師資訊成功"
        ];
    }

    public function post_teacher($data)
    {
        $values = [
            "grade" => '',
            "class" => '',
            "teachername" => '',
            "teachermail" => '',
            "passwd" => '',
            "graduationyear" => null,
            "sid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $stmt_array = [
            "Grade" => $values["grade"],
            "Class" => $values["class"],
            "Sid" => $values["sid"]
        ];
        $stmt_string = [];
        if ($values['Grade'] === "畢") {
            $stmt_string["GraduationYear"] =  " AND ( [GraduationYear] = :GraduationYear )";
            $stmt_array["GraduationYear"] = $values["graduationyear"];
        }

        $sql = "SELECT * 
                FROM Teacher 
                WHERE [Grade] = :Grade
                AND [Class] = :Class
                AND [Sid] = :Sid
                {$stmt_string["GraduationYear"]}
        ";

        $stmt = $this->container->db->prepare($sql);
        $stmt->execute($stmt_array);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 0) {
            return [
                "status" => "failure",
                "message" => "此班級「{$values['grade']}年{$values['class']}班」已存在，請確認此班級是否已存在，避免新增多筆重覆班級資料。"
            ];
        }

        $stmt_array1 = [
            "Grade1" => $values["grade"],
            "Class1" => $values["class"],
            "TeacherName1" => $values["teachername"],
            "TeacherMail1" => $values["teachermail"],
            "PassWD1" => $values["passwd"],
            "Sid1" => $values["sid"],
        ];

        if ($values['grade'] === "畢") {
            $sql = "INSERT INTO [Teacher] (
                    [Sid], [Grade], [Class], [TeacherName], 
                    [TeacherMail], [PassWD], [PassWD_ChangeDate]
                    )
                VALUES (
                    :Sid1, :Grade1, :Class1, 
                    :TeacherName1, :TeacherMail1, 
                    :PassWD1, getdate()
                    )
                ";
        } else {
            $stmt_array1["GraduationYear1"] = $values["graduationYear"];
            $sql = "INSERT INTO [Teacher] (
                        [Sid], [Grade], [Class], [TeacherName], 
                        [TeacherMail], [PassWD], [PassWD_ChangeDate], [GraduationYear]
                        )
			        VALUES (
                        :Sid1, :Grade1, :Class1, 
                        :TeacherName1, :TeacherMail1, 
                        :PassWD1, getdate(), :GraduationYear1
                        )
            ";
        }

        $result = $this->container->db->prepare($sql);
        // $result->execute($stmt_array1);
        // var_dump($result->errorInfo());
        // exit(0);
        if (!$result->execute($stmt_array1)) {
            return [
                "status" => "failure",
                "message" => "新增班級失敗",
                "info" => $result->errorInFo()
            ];
        }
        return [
            "status" => "success",
            "message" => "新增班級成功"
        ];
    }

    public function post_import_exam_data($data)
    {
        $values = [
            "Sid" => 0,
            "Grade" => '',
            "Class" => '',
            "TeacherName" => '',
            "TeacherMail" => '',
            "PassWD" => '',
            "GraduationYear" => null
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        // if ($values['Grade'] === "畢") {
        //     $sql = "INSERT INTO Teacher (Sid, Grade, Class, TeacherName, TeacherMail, PassWD, PassWD_ChangeDate, GraduationYear)
        //         VALUES (:Sid, :Grade, :Class, :TeacherName, :TeacherMail, :PassWD, getdate(), :GraduationYear";
        //     $stmt_array["GraduationYear"] = $values["GraduationYear"];
        // } else {
        //     $sql = "INSERT INTO Teacher (Sid, Grade, Class, TeacherName, TeacherMail, PassWD, PassWD_ChangeDate, GraduationYear)
        // 	    VALUES (:Sid, :Grade, :Class, :TeacherName, :TeacherMail, :PassWD, getdate(), NULL)";
        // }

        $sql = "INSERT INTO [Teacher] (
                    [Sid], [Grade], [Class], 
                    [TeacherName], [TeacherMail], 
                    [PassWD], [PassWD_ChangeDate], [GraduationYear])
                VALUES (
                    :Sid, :Grade, :Class, 
                    :TeacherName, :TeacherMail, 
                    :PassWD, GETDATE(), :GraduationYear )
            ";

        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => $sth->errorInFo()
            ];
        }
        return [
            "status" => "success"
        ];
    }

    public function get_current_rank_person($data)
    {
        $values = [
            "Sid" => 0
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }

        $sql = "WITH [with] AS (
                SELECT *
                FROM [Literacy].[dbo].[View_Learn_Total_Score_new]
                WHERE DATEPART(yy,GETDATE())=[View_Learn_Total_Score_new].[task_year]
                    AND CASE WHEN DATEPART(mm, GETDATE()) >= 7 OR
                    DATEPART(mm, GETDATE()) <= 2 THEN '上' WHEN DATEPART(mm, GETDATE()) >= 1 OR
                    DATEPART(mm, GETDATE()) <= 8 THEN '下' END = [View_Learn_Total_Score_new].[task_term]
            )
            SELECT dt.[Pid],dt.[Grade],dt.[StuName],dt.[current_word_count],dt.[total_word_count],dt.[score],dt.[Class],dt.[rank]
            FROM(
                SELECT [with].[Pid],[View_Student_now].[Grade], COALESCE([View_Student_now].[Pre_Class],[Teacher].[Class])[Class],[View_Student_now].[StuName],[current_word_count].[current_word_count],[total_word_count].[total_word_count],[View_Learn_Total_Score_new].[score],RANK() OVER( PARTITION BY [View_Student_now].[Grade] ORDER BY [View_Learn_Total_Score_new].[score] DESC) [rank]
                FROM [with]
                LEFT JOIN (
                    SELECT [Learn_Task_Word_Student].[Pid], [View_Learn_Task].[task_year], [View_Learn_Task].[task_term], COUNT(*) current_word_count
                    FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_word_id] = [Learn_Task_Word_Student].[task_word_id]
                    LEFT JOIN [Literacy].[dbo].[View_Learn_Task] ON [Learn_Task_Word].[task_id] = [View_Learn_Task].TaskId 
                    GROUP BY [Learn_Task_Word_Student].[Pid], [View_Learn_Task].[task_year], [View_Learn_Task].[task_term]
                )[current_word_count] ON [with].[Pid] = [current_word_count].[Pid] AND [with].[task_year] = [current_word_count].[task_year]
                    AND [with].[task_term] = [current_word_count].[task_term]
                LEFT JOIN (
                    SELECT [Learn_Task_Word_Student].[Pid], COUNT(*) total_word_count
                    FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_word_id] = [Learn_Task_Word_Student].[task_word_id]
                    LEFT JOIN [Literacy].[dbo].[View_Learn_Task] ON [Learn_Task_Word].[task_id] = [View_Learn_Task].TaskId 
                    GROUP BY [Learn_Task_Word_Student].[Pid]
                )[total_word_count] ON [with].[Pid] = [total_word_count].[Pid]
                LEFT JOIN [Literacy].[dbo].[View_Learn_Total_Score_new] ON [with].[Pid] = [View_Learn_Total_Score_new].[Pid] 
                    AND [View_Learn_Total_Score_new].[task_year] = [with].[task_year] 
                    AND [View_Learn_Total_Score_new].[task_term] = [with].[task_term] 
                LEFT JOIN [Literacy].[dbo].[View_Student_now] ON [View_Student_now].[Pid] = [with].[Pid]
                LEFT JOIN [Literacy].[dbo].[Teacher] ON [View_Student_now].[Tid] = [Teacher].[Tid] 
                WHERE [View_Student_now].[Sid] = :Sid
            )dt
            WHERE dt.[rank] = 1
            ORDER BY CASE dt.Grade 
                WHEN '一' THEN 1 
                WHEN '二' THEN 2
                WHEN '三' THEN 3
                WHEN '四' THEN 4
                WHEN '五' THEN 5
                WHEN '六' THEN 6
                WHEN '畢' THEN 7
                ELSE 999999 END
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_current_rank_group($data)
    {
        $values = [
            "Sid" => 0
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }

        $sql = "WITH [with] AS (
                SELECT [View_Learn_Total_Score_Group].[GroupId],[Learn_Teacher_Student].[Tid], [View_Learn_Total_Score_Group].[task_year],[View_Learn_Total_Score_Group].[task_term],[View_Learn_Total_Score_Group].[Grade],SUM([View_Learn_Total_Score_Group].[score])[score]
                FROM [Literacy].[dbo].[View_Learn_Total_Score_Group]
                LEFT JOIN (
                    SELECT [Learn_Teacher_Student].[GroupId], [Learn_Teacher_Student].[Tid] 
                    FROM [Literacy].[dbo].[Learn_Teacher_Student] 
                    GROUP BY [Learn_Teacher_Student].[GroupId], [Learn_Teacher_Student].[Tid]
                )[Learn_Teacher_Student] ON [Learn_Teacher_Student]. [GroupId] = [View_Learn_Total_Score_Group].[GroupId]
                WHERE DATEPART(yy,GETDATE())=[View_Learn_Total_Score_Group].[task_year]
                    AND CASE WHEN DATEPART(mm, GETDATE()) >= 7 OR
                    DATEPART(mm, GETDATE()) <= 2 THEN '上' WHEN DATEPART(mm, GETDATE()) >= 1 OR
                    DATEPART(mm, GETDATE()) <= 8 THEN '下' END = [View_Learn_Total_Score_Group].[task_term]
                GROUP BY [View_Learn_Total_Score_Group].[GroupId],[Learn_Teacher_Student].[Tid],[View_Learn_Total_Score_Group].[Grade],[View_Learn_Total_Score_Group].[task_year],[View_Learn_Total_Score_Group].[task_term]
            ), dt2 AS(
                SELECT [Learn_Group].[GroupName], [Teacher].[Grade], [Teacher].[Class],[task_count].task_count,finish_count.finish_count,
                    [with].[score],RANK() OVER ( PARTITION BY [Teacher].[Grade], [Teacher].[Class] ORDER BY [with].[score]) rank,
                    ROW_NUMBER () OVER (ORDER BY [with].[score]) row_number
                FROM [with]
                LEFT JOIN [Literacy].[dbo].[Learn_Group] ON [with].[GroupId] = [Learn_Group].[GroupId]
                LEFT JOIN (
                    SELECT [Learn_Group_Task].[GroupId],[View_Learn_Task].[task_year],[View_Learn_Task].[task_term],COUNT(*)task_count
                    FROM [Literacy].[dbo].[Learn_Group_Task]
                    LEFT JOIN [Literacy].[dbo].[View_Learn_Task] ON [View_Learn_Task].[TaskId] = [Learn_Group_Task].[TaskId]
                    GROUP BY [Learn_Group_Task].[GroupId],[View_Learn_Task].[task_year],[View_Learn_Task].[task_term]
                )[task_count] ON [with].[GroupId] = [task_count].[GroupId] AND [with].[task_year] = [task_count].[task_year]
                    AND [with].[task_term] = [task_count].[task_term] 
                LEFT JOIN(
                    SELECT [Learn_Task].[Tid],SUM(dt.finish_count) finish_count
                    FROM(
                        SELECT [Learn_Group_Task].[TaskId],
                            CASE WHEN COUNT(
                                CASE
                                    WHEN [Learn_Task_Word_Student].step IS NULL OR [Learn_Task_Word_Student].step != 9
                                    THEN 1
                                END
                            ) = 0 THEN 1 ELSE 0 END finish_count
                            
                        FROM [Literacy].[dbo].[Learn_Group_Task]
                        LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Group_Task].[GroupId] = [Learn_Teacher_Student].[GroupId]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Group_Task].[TaskId] = [Learn_Task_Word].[task_id] 
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                            AND [Learn_Teacher_Student].[Pid] = [Learn_Task_Word_Student].[Pid]
                        GROUP BY [Learn_Group_Task].[TaskId]
                    )dt
                    LEFT JOIN [Literacy].[dbo].[Learn_Task] ON dt.[TaskId] = [Learn_Task].[TaskId] 
                    WHERE [Learn_Task].[Tid] IS NOT NULL
                    GROUP BY [Learn_Task].[Tid]
                )finish_count ON finish_count.[Tid] = [with].[Tid]
                LEFT JOIN [Literacy].[dbo].[Teacher] ON [Teacher].[Tid] = [with].[Tid] 
                WHERE [Teacher].[Sid] = :Sid            
            )
            SELECT dt2.*
            FROM(
                SELECT dt2.[Grade],dt2.[Class],MIN(row_number)row_number
                FROM dt2
                WHERE dt2.rank = 1 
                GROUP BY dt2.[Grade],dt2.[Class]
            )dt
            LEFT JOIN dt2 ON dt2.row_number = dt.row_number 
            ORDER BY CASE dt2.Grade 
                WHEN '一' THEN 1 
                WHEN '二' THEN 2
                WHEN '三' THEN 3
                WHEN '四' THEN 4
                WHEN '五' THEN 5
                WHEN '六' THEN 6
                WHEN '畢' THEN 7
                ELSE 999999 END
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_report_teacher($data)
    {
        $values = [
            "Sid" => 0
        ];
        $string = "";
        $check = false;

        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }

        if (array_key_exists('grade', $data)) {
            $string .= " AND origin.Grade =:grade ";
            $values['grade'] = $data['grade'];
            $check = true;
        }
        if (array_key_exists('class', $data)) {
            $string .= " AND origin.Class =:class ";
            $values['class'] = $data['class'];
            $check = true;
        }
        if (array_key_exists('name', $data)) {
            $string .= " AND origin.TeacherName LIKE '%'+:name+'%' ";
            $values['name'] = $data['name'];
            $check = true;
        }
        if (array_key_exists('excel', $data)) {
            unset($data['excel']);
            $select = "SELECT origin.Grade+'年'+origin.Class+'班' '年級班級' ,origin.TeacherName '教師姓名'
                        , origin.stu_count '學生人數',tid_task.task_count '教師指派任務數'
                        , CAST(COALESCE(task_word_group.task_finish_count*100 / tid_task.task_count,0) AS varchar) +'%'+'('+CAST(COALESCE(task_word_group.task_finish_count,0)AS varchar)+'/'+CAST(COALESCE(tid_task.task_count,0) AS varchar)+')' '教師任務完成率(完成/總任務數)'
                        , COALESCE(task_word_total.task_word_total,0)'教師指派生字量',CAST( COALESCE( task_word_total.task_word_finish_total *100/ task_word_total.task_word_total ,0)AS varchar)+'%'+'('+CAST( COALESCE(task_word_total.task_word_finish_total,0) AS varchar)+'/'+CAST(COALESCE( task_word_total.task_word_total,0) AS varchar)+')' '教師指派生字量完成率(完成/總生字量)'  
            ";
        } else {
            $select = "SELECT origin.Sid, origin.Tid,origin.Grade,origin.Class,origin.TeacherName,origin.stu_count,tid_task.task_count, COALESCE(task_word_group.task_finish_count,0)task_finish_count, COALESCE(task_word_total.task_word_total,0)task_word_total,  COALESCE(task_word_group.task_finish_count*100 / tid_task.task_count,0) task_percentage, COALESCE(task_word_total.task_word_finish_total,0)task_word_finish_total, COALESCE( task_word_total.task_word_finish_total *100/ task_word_total.task_word_total ,0) word_percentage
            ";
        }
        $sql = "{$select}
              FROM (
                SELECT  [Literacy].[dbo].[Teacher].[Sid], [Literacy].[dbo].[Teacher].[Tid], [Literacy].[dbo].[Teacher].[Class], [Literacy].[dbo].[Teacher].[Grade],[dbo].[Teacher].[TeacherName], COUNT([Literacy].[dbo].[Student].[Tid]) stu_count
                FROM [Literacy].[dbo].[Teacher]
                LEFT JOIN [Literacy].[dbo].[Student] ON [Literacy].[dbo].[Teacher].[Tid] = [Literacy].[dbo].[Student].[Tid]
                GROUP BY [Literacy].[dbo].[Teacher].[Tid], [Literacy].[dbo].[Teacher].[Class], [Literacy].[dbo].[Teacher].[TeacherName] ,[Literacy].[dbo].[Teacher].[Grade], [Literacy].[dbo].[Teacher].[Sid]
              )origin
              LEFT JOIN (
                SELECT [Literacy].[dbo].[Teacher].[Tid], COUNT([Literacy].[dbo].[Learn_Task].[Tid]) task_count
                FROM [Literacy].[dbo].[Teacher]
                LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Literacy].[dbo].[Teacher].[Tid] = [Literacy].[dbo].[Learn_Task].[Tid]
                GROUP BY [Literacy].[dbo].[Teacher].[Tid], [Literacy].[dbo].[Teacher].[Class]
                )tid_task ON origin.[Tid] = tid_task.[Tid]
              LEFT JOIN(
                SELECT task_word.[Tid], COUNT(CASE WHEN task_word.[task_word_count] = 0 THEN 1 END) task_finish_count
                FROM (
                    SELECT [Literacy].[dbo].[Teacher].[Tid], [Literacy].[dbo].[Learn_task_word].[task_id], COUNT(CASE WHEN step!= 9 OR step IS NULL THEN 1 END) task_word_count
                    FROM [Literacy].[dbo].[Teacher]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Literacy].[dbo].[Teacher].[Tid] = [Literacy].[dbo].[Learn_Task].[Tid]
                    LEFT JOIN [Literacy].[dbo].[Learn_Group_Task] ON [Literacy].[dbo].[Learn_Task].[TaskId] = [Literacy].[dbo].[Learn_Group_Task].[Taskid]
                    LEFT JOIN [Literacy].[dbo].[Learn_task_word] ON [Literacy].[dbo].[Learn_Group_Task].[TaskId] = [Literacy].[dbo].[Learn_task_word].[task_id]
                    LEFT JOIN [Literacy].[dbo].[Learn_task_word_student] ON [Literacy].[dbo].[Learn_task_word].[task_word_id] = [Literacy].[dbo].[Learn_task_word_student].[task_word_id] 
                    WHERE [Literacy].[dbo].[Learn_task_word].[task_id] IS NOT NULL
                    GROUP BY [Literacy].[dbo].[Teacher].[Tid], [Literacy].[dbo].[Learn_task_word].[task_id]
                )task_word
                GROUP BY task_word.[Tid]
              )task_word_group ON origin.[Tid] = task_word_group.[Tid]
              LEFT JOIN (
                SELECT [Literacy].[dbo].[Teacher].[Tid], COUNT([Literacy].[dbo].[Learn_task_word].[task_word_id]) task_word_total,COUNT(CASE WHEN [Literacy].[dbo].[Learn_Task_Word_Student].[step] = 9 THEN 1 END)task_word_finish_total
                FROM [Literacy].[dbo].[Teacher]
                LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Literacy].[dbo].[Teacher].[Tid] = [Literacy].[dbo].[Learn_Task].[Tid]
                LEFT JOIN [Literacy].[dbo].[Learn_Group_Task] ON [Literacy].[dbo].[Learn_Task].[TaskId] = [Literacy].[dbo].[Learn_Group_Task].[Taskid]
                LEFT JOIN [Literacy].[dbo].[Learn_task_word] ON [Literacy].[dbo].[Learn_Group_Task].[TaskId] = [Literacy].[dbo].[Learn_task_word].[task_id]
                LEFT JOIN [Literacy].[dbo].[Learn_task_word_student] ON [Literacy].[dbo].[Learn_task_word].[task_word_id] = [Literacy].[dbo].[Learn_task_word_student].[task_word_id] 
                WHERE [Literacy].[dbo].[Learn_task_word].[task_id] IS NOT NULL
                GROUP BY [Literacy].[dbo].[Teacher].[Tid]
              )task_word_total ON origin.[Tid] = task_word_total.[Tid]
              WHERE origin.Sid=:Sid {$string}
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_current_rank_avg_word($data)
    {
        $values = [
            "Sid" => 0
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "WITH [with] AS (
                SELECT class_count.[Sid],class_count.[Grade],COUNT(*) class_count,COALESCE(SUM(word_count),0)word_count,COALESCE(SUM(student_count),0)student_count
                FROM(
                    SELECT [View_Student_now].[Sid],[View_Student_now].[Grade],[Teacher].[Class],COUNT(*) student_count,COALESCE(SUM(word_count.word_count),0)word_count
                    FROM [Literacy].[dbo].[View_Student_now]
                    LEFT JOIN [Literacy].[dbo].[Teacher] ON [Teacher].[Tid] = [View_Student_now].[Tid]
                    LEFT JOIN (
                        SELECT [Learn_Task_Word_Student].[Pid], 
                            COUNT( CASE WHEN  [Learn_Task_Word_Student].step = 9 THEN 1 END) word_count
                        FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                        GROUP BY [Learn_Task_Word_Student].[Pid] 
                    )word_count ON [View_Student_now].[Pid] = word_count.[Pid] 
                    GROUP BY [View_Student_now].[Sid],[View_Student_now].[Grade],[Teacher].[Class]
                )class_count
                WHERE class_count.[Sid] = :Sid
                GROUP BY class_count.[Sid],class_count.[Grade]
            )
            SELECT '班級數' [name]
                ,SUM( CASE WHEN Grade = '一' THEN class_count ELSE 0 END) '一年級'
                ,SUM( CASE WHEN Grade = '二' THEN class_count ELSE 0 END) '二年級'
                ,SUM( CASE WHEN Grade = '三' THEN class_count ELSE 0 END) '三年級'
                ,SUM( CASE WHEN Grade = '四' THEN class_count ELSE 0 END) '四年級'
                ,SUM( CASE WHEN Grade = '五' THEN class_count ELSE 0 END) '五年級'
                ,SUM( CASE WHEN Grade = '六' THEN class_count ELSE 0 END) '六年級'
            FROM [with]
            GROUP BY [with].[Sid]
            UNION ALL(
                SELECT '學生數' [name]
                    ,SUM( CASE WHEN Grade = '一' THEN student_count ELSE 0 END) '一年級'
                    ,SUM( CASE WHEN Grade = '二' THEN student_count ELSE 0 END) '二年級'
                    ,SUM( CASE WHEN Grade = '三' THEN student_count ELSE 0 END) '三年級'
                    ,SUM( CASE WHEN Grade = '四' THEN student_count ELSE 0 END) '四年級'
                    ,SUM( CASE WHEN Grade = '五' THEN student_count ELSE 0 END) '五年級'
                    ,SUM( CASE WHEN Grade = '六' THEN student_count ELSE 0 END) '六年級'
                FROM [with]
                GROUP BY [with].[Sid]
            )
            UNION ALL(
                SELECT '學習生字量平均數' [name]
                    ,SUM( CASE WHEN Grade = '一' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '一' THEN student_count ELSE 0 END),0), 1) '一年級'
                    ,SUM( CASE WHEN Grade = '二' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '二' THEN student_count ELSE 0 END),0), 1) '二年級'
                    ,SUM( CASE WHEN Grade = '三' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '三' THEN student_count ELSE 0 END),0), 1) '三年級'
                    ,SUM( CASE WHEN Grade = '四' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '四' THEN student_count ELSE 0 END),0), 1) '四年級'
                    ,SUM( CASE WHEN Grade = '五' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '五' THEN student_count ELSE 0 END),0), 1) '五年級'
                    ,SUM( CASE WHEN Grade = '六' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '六' THEN student_count ELSE 0 END),0), 1) '六年級'
                FROM [with]
                GROUP BY [with].[Sid]
            )
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_current_rank_avg_word_grades($data)
    {
        $values = [
            "Sid" => 0
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "WITH [with] AS (
                SELECT [View_Student_now].*,[Teacher].[Class],word_count.word_count 
                FROM [Literacy].[dbo].[View_Student_now]
                LEFT JOIN [Literacy].[dbo].[Teacher] ON [Teacher].[Tid] = [View_Student_now].[Tid]
                LEFT JOIN (
                    SELECT [Learn_Task_Word_Student].[Pid], 
                        COUNT( CASE WHEN  [Learn_Task_Word_Student].step = 9 THEN 1 END) word_count
                    FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                    GROUP BY [Learn_Task_Word_Student].[Pid] 
                )word_count ON [View_Student_now].[Pid] = word_count.[Pid] 
                WHERE [View_Student_now].[Sid] =:Sid AND [View_Student_now].[Grade] IS NOT NULL
            )
            SELECT *
            FROM(
                SELECT [with].[Sex]'性別',[with].[Grade]'年級',COUNT(*)'學生數'
                    ,CAST(COALESCE(MIN([with].word_count),0) AS varchar)+'-'+CAST(COALESCE(MAX([with].word_count),0) AS varchar) '學習生字量全距'
                    ,COALESCE(CAST(ROUND (STDEV ([with].word_count),2) AS varchar),'0.00') '標準差'
                    ,COALESCE(tmp.word_count,0) '中位數'
                    ,COALESCE(AVG([with].word_count),0) '學習生字量平均數'
                FROM [with]
                LEFT JOIN (
                    SELECT Sid,Sex,Grade,AVG(word_count)word_count
                    FROM(
                        SELECT[View_Student_now].[Sid],[View_Student_now].[Sex],[View_Student_now].[Grade],word_count.[word_count],
                        ROW_NUMBER() OVER (
                        PARTITION BY[View_Student_now].[Sid],[View_Student_now].[Sex],[View_Student_now].[Grade]
                        ORDER BY word_count.[word_count] ASC ) AS RowAsc,
                        ROW_NUMBER() OVER (
                        PARTITION BY [View_Student_now].[Sid],[View_Student_now].[Sex],[View_Student_now].[Grade]
                        ORDER BY word_count.[word_count] DESC ) AS RowDesc
                        FROM  [Literacy].[dbo].[View_Student_now]
                        LEFT JOIN (
                            SELECT [Learn_Task_Word_Student].[Pid], 
                            COUNT( CASE WHEN  [Learn_Task_Word_Student].step = 9 THEN 1 END) word_count
                            FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                            GROUP BY [Learn_Task_Word_Student].[Pid] 
                        )word_count ON [View_Student_now].[Pid]  = word_count.[Pid]
                        WHERE word_count.word_count IS NOT NULL
                    )x
                    WHERE RowAsc IN (RowDesc, RowDesc - 1, RowDesc + 1)
                    GROUP BY [Sid],[Sex],[Grade]
                )tmp ON tmp.[Sid]=[with].[Sid] AND tmp.[Sex]=[with].[Sex] AND tmp.[Grade]=[with].[Grade]
                GROUP BY [with].[Sid],[with].[Grade],[with].[Sex],tmp.word_count
                UNION ALL(
                    SELECT'全體',[with].[Grade]'年級',COUNT(*)'學生數'
                        ,CAST(COALESCE(MIN([with].word_count),0) AS varchar)+'-'+CAST(COALESCE(MAX([with].word_count),0) AS varchar) '學習生字量全距'
                        ,COALESCE(CAST(ROUND (STDEV ([with].word_count),2) AS varchar),'0.00')'標準差'
                        ,COALESCE(tmp.word_count,0) '中位數'
                        ,COALESCE(AVG([with].word_count),0) '學習生字量平均數'
                    FROM [with]
                    LEFT JOIN (
                        SELECT Sid,Grade,AVG(word_count)word_count
                        FROM(
                            SELECT[View_Student_now].[Sid],'全體'Sex,[View_Student_now].[Grade],word_count.[word_count],
                            ROW_NUMBER() OVER (
                            PARTITION BY[View_Student_now].[Sid],[View_Student_now].[Grade]
                            ORDER BY word_count.[word_count] ASC ) AS RowAsc,
                            ROW_NUMBER() OVER (
                            PARTITION BY [View_Student_now].[Sid],[View_Student_now].[Grade]
                            ORDER BY word_count.[word_count] DESC ) AS RowDesc
                            FROM  [Literacy].[dbo].[View_Student_now]
                            LEFT JOIN (
                                SELECT [Learn_Task_Word_Student].[Pid], 
                                COUNT( CASE WHEN  [Learn_Task_Word_Student].step = 9 THEN 1 END) word_count
                                FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                                GROUP BY [Learn_Task_Word_Student].[Pid] 
                            )word_count ON [View_Student_now].[Pid]  = word_count.[Pid]
                            WHERE word_count.word_count IS NOT NULL
                        )x
                        WHERE RowAsc IN (RowDesc, RowDesc - 1, RowDesc + 1)
                        GROUP BY [Sid],[Grade]
                    )tmp ON tmp.[Sid]=[with].[Sid] AND tmp.[Grade]=[with].[Grade]
                    GROUP BY [with].[Sid],[with].[Grade],tmp.word_count
                )
            )dt
            ORDER BY dt.年級,	
                CASE 
                    WHEN dt.性別 = '男' THEN 2
                    WHEN dt.性別 = '女' THEN 1
                    ELSE 0
                END DESC
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_report_student($data)
    {
        $values = [
            "Sid" => 0
        ];
        $string = "";
        $check = false;

        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        if (array_key_exists('StuName', $data)) {
            $string .= " AND dt.[StuName] LIKE '%'+:StuName+'%' ";
            $values['StuName'] = $data['StuName'];
            $check = true;
        }
        if (array_key_exists('grade', $data)) {
            $string .= " AND dt.[Grade] =:grade ";
            $values['grade'] = $data['grade'];
            $check = true;
        }
        if (array_key_exists('class', $data)) {
            $string .= " AND dt.[Class] =:class ";
            $values['class'] = $data['class'];
            $check = true;
        }
        // if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
        //     $string .= " AND dt.[AppendDate] >= :start_date AND dt.[AppendDate] < =dateadd(day,1,:end_date) AND";
        //     $values['start_date'] = $data['start_date'];
        //     $values['end_date'] = $data['end_date'];
        //     $check = true;
        // }
        if (array_key_exists('Year', $data)) {
            $string .= "AND dt.[Year]= :Year";
            $values['Year'] = $data['Year'];
            $check = true;
        }
        if (array_key_exists('Term', $data)) {
            $string .= " AND dt.[Term] = :Term";
            $values['Term'] = $data['Term'];
            $check = true;
        }
        if (array_key_exists('excel', $data)) {
            unset($data['excel']);
            $select ="SELECT CAST(COALESCE(dt.[Grade],'')AS varchar)+'年'+CAST(COALESCE(dt.[Class],'') AS varchar)+'班' '年級班級',dt.[StuNum] '座號',dt.StuName '學生姓名',dt.self_total '自主學習任務數' ,CAST(dt.self_percentage AS varchar) +'%'+'('+CAST(dt.self_finish AS varchar) +'/'+CAST(dt.self_total AS varchar)+')' '自主學習任務完成率(完成/總任務數)',dt.word_total '自主學習生字量',CAST(dt.word_percentage AS varchar)+'%'+'('+CAST(dt.word_finish AS varchar)+'/'+CAST(dt.word_total AS varchar)+')' '自主學習生字量完成率(完成/總生字量)',dt.learned_semester_count '本學期學習生字量', dt.learned_semester_count '本學期學習生字量',dt.learned_count '已學習生字量'";
        } else {
            $select = "SELECT *";
        }
        $sql = "{$select}
                FROM(
                    SELECT [Teacher].[Sid],[Teacher].[Tid],COALESCE([Teacher].[Grade],'')Grade,COALESCE([Teacher].[Class],'')Class,[Student].[Pid],[Student].[StuName],[Student].[StuNum],
                        COALESCE([Learn_Task_Word_Student].learned_count,0)learned_count,
                        COALESCE([Learn_Task_Word_Student].learned_semester_count,0)learned_semester_count,
                        COALESCE([Learn_Group_Task_self].[finish],0)[self_finish],
                        COALESCE([Learn_Group_Task_self].[total],0)[self_total],
                        COALESCE([Learn_Group_Task_self].[percentage],0)[self_percentage],
                        COALESCE([Learn_Word_Self].[word_total],0)[word_total],
                        COALESCE([Learn_Word_Self].[finish],0)[word_finish],
                        COALESCE([Learn_Word_Self].[percentage],0)[word_percentage],
                        [Learn_Word_Self].Term ,
                        [Student].Year
                    FROM [Literacy].[dbo].[Teacher]
                    LEFT JOIN [Literacy].[dbo].[Student] ON [Student].[Tid]=[Teacher].[Tid]
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
                    LEFT JOIN (
                        SELECT [Learn_Task].[Pid],COUNT(*) total,
                                COUNT(
                                CASE
                                    WHEN step = 9 THEN 1
                                END
                            )finish,
                            COUNT(
                                CASE
                                    WHEN step = 9 THEN 1
                                END
                            )*100/REPLACE(COUNT(*),0,1) percentage
                        FROM (
                            SELECT [Learn_Task].[TaskId],[Learn_Task].[Pid]
                            FROM [Literacy].[dbo].[Learn_Task]
                            WHERE [Learn_Task].[Pid] IS NOT NULL
                        )[Learn_Task]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task].[TaskId] = [Learn_Task_Word].[task_id]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[Pid] = [Learn_Task].[Pid]
                            AND [Learn_Task_Word].[task_word_id] = [Learn_Task_Word_Student].[task_word_id]
                        GROUP BY [Learn_Task].[Pid]
                    )[Learn_Group_Task_self] ON [Learn_Group_Task_self].[Pid] = [Student].[Pid]
                    LEFT JOIN(
                        SELECT [Learn_Task].[Pid],
                            COUNT([Learn_Task_Word_Student].[task_word_id])word_total,
                            COUNT(
                                CASE
                                    WHEN step = 9 THEN 1
                                END
                            )finish,
                            COUNT(
                                CASE
                                    WHEN step = 9 THEN 1
                                END
                            )*100/REPLACE(COUNT([Learn_Task_Word_Student].[task_word_id]),0,1) percentage
                            ,[Learn_Task].Term
                        FROM (
                        SELECT [Learn_Task].[TaskId],[Learn_Task].[Pid],[Learn_Task].Term 
                        FROM [Literacy].[dbo].[Learn_Task]
                        WHERE [Learn_Task].[Pid] IS NOT NULL
                        )[Learn_Task]
                        LEFT JOIN [Learn_Task_Word_Student] ON [Learn_Task_Word_Student].[Pid]=[Learn_Task].[Pid]
                        GROUP BY [Learn_Task].[Pid],[Learn_Task].Term
                    )[Learn_Word_Self] ON [Learn_Word_Self].[Pid]=[Student].[Pid]      
                        WHERE [Student].[StuName] IS NOT NULL
                )dt        
                WHERE dt.Sid=:Sid {$string}      
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_master_account_data($data)
    {
        $values = [
            "Sid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [SchoolList].[Sid]
                    , [SchoolList].[CityId]
                    , [SchoolList].[PostId]
                    , [SchoolList].[SchoolName]
                    , [SchoolList].[Contact_EMail_1]
                    , [SchoolList].[PassWD]
                    , [SchoolList].[PassWD_ChangeDate]
                    , [SchoolList].[Photo]
                FROM [Literacy].[dbo].[SchoolList]
                WHERE [SchoolList].[Sid] = :Sid
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function post_master_photo($data)
    {
        $values = [
            "Sid" => '',
            "photo" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[SchoolList] Set 
                [Photo] = :photo
                WHERE [Sid] = :Sid
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

    public function patch_learn_master_mail($data)
    {
        $values = [
            "Sid" => '',
            "mail" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[SchoolList] Set 
                [Contact_EMail_1] = :mail
                WHERE [Sid] = :Sid
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

    public function check_master_origin_passwd($data)
    {
        $values = [
            "Sid" => '',
            "passwd_old" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Sid] 
                FROM [Literacy].[dbo].[SchoolList] 
                WHERE [Sid] = :Sid
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

    public function patch_master_passwd($data)
    {
        $values = [
            "Sid" => '',
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

            $sql = "Update [SchoolList] Set 
                    [PassWD] = :passwd
                    , [PassWD_ChangeDate] = GETDATE()
                    WHERE [Sid] = :Sid
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
}
