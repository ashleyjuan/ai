<?php

use PHPMailer\PHPMailer\PHPMailer;

use Slim\Views\PhpRENDerer;
use Slim\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class admin
{
    protected $container;
    public function __construct($container)
    {
        global $container;
        $this->container = $container;
    }

    public function getAdminTest()
    {
        $sql = 'SELECT *
                FROM public.test';
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function cityLogin($data)
    {
        $values = [
            "passwd" => '',
            'cityid' => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT * 
                FROM [City] 
                WHERE [PassWD] COLLATE Chinese_PRC_CS_AS = :passwd
                AND [CityId] = :cityid
                ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($data);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $status = ["status" => "failed"];
        session_start();
        foreach ($result as $key => $value) {
            $_SESSION['CityName'] = $value['CityName'];
            $_SESSION['login'] = 0;
            $status = ["status" => "success"];
        }
        session_write_close();
        return $status;
    }

    public function patch_city($data)
    {
        $values = [
            "passwd" => '',
            'CityId' => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE City 
            Set PassWD = :passwd 
            WHERE CityId = :CityId";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            $status = ["status" => "failure"];
        }
        $status = ["status" => "success"];
        return $status;
    }

    public function getCitySite($data)
    {
        $sql = "SELECT [CityId], [CityName]
                FROM [Literacy].[dbo].[City] 
                ORDER BY [Literacy].[dbo].[City].[OrderBy]
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getArea($data)
    {
        $values = [
            "cityid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT DISTINCT [Literacy].[dbo].[School].Area
                FROM [Literacy].[dbo].[School] 
                WHERE [Literacy].[dbo].[School].City = :cityid
                ORDER BY [Literacy].[dbo].[School].Area
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getLevel()
    {
        $levelall = array(
            ['level' => "大學"],
            ['level' => "高中"],
            ['level' => "國中"],
            ['level' => "國小"]
        );
        $level_arr = array();
        foreach ($levelall as $id => $data_row) {
            array_push($level_arr, $data_row['level']);
        }
        $result = $level_arr;
        return $result;
    }

    public function get_school_city_area($data)
    {
        $values = [
            "sid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Sid], [CityId], [PostId], [SchoolName], [Class]
                FROM [Literacy].[dbo].[SchoolList] 
                WHERE [Sid] = :sid

        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);

        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getSchool($data)
    {
        $values = [
            "cityid" => '',
            "postid" => '',
            "class" => null
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (is_null($values['class'])) {
            $stmt_string = "";
            unset($values['class']);
        } else {
            $stmt_string = " AND [Class] = :class ";
        }

        $sql = "SELECT [Sid], [SchoolName]
                FROM [Literacy].[dbo].[SchoolList] 
                WHERE [CityId] = :cityid
                AND [PostId] = :postid
                {$stmt_string}
                ORDER BY [SchoolName]
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);

        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_year_learn($data)
    {
        $sql = "SELECT DISTINCT [Year]
                FROM [Literacy].[dbo].[Learn_Version]
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_term_learn($data)
    {
        $sql = "SELECT DISTINCT [Term]
                FROM [Literacy].[dbo].[Learn_Version]
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_grade_learn($data)
    {
        $sql = "SELECT DISTINCT [Grade]
                FROM [Literacy].[dbo].[Learn_Version]
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_version_learn($data)
    {
        $sql = "SELECT DISTINCT [Version]
                FROM [Literacy].[dbo].[Learn_Version]
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_lesson_learn($data)
    {
        $sql = "SELECT [Lesson]
                FROM [Literacy].[dbo].[Learn_Version]
                GROUP BY [Lesson]
                ORDER BY CASE [Lesson]
                WHEN '第一課' THEN 1
                WHEN '第二課' THEN 2
                WHEN '第三課' THEN 3
                WHEN '第四課' THEN 4
                WHEN '第五課' THEN 5
                WHEN '第六課' THEN 6
                WHEN '第七課' THEN 7
                WHEN '第八課' THEN 8
                ELSE 99999 END
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function studentLogin($data)
    {

        $values = [
            "idnumber" => 0,
            "birth" => '',
            "sid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT TOP 1 *
                FROM [Literacy].[dbo].[Student]
                WHERE [IDNumber] = :idnumber
                AND [Birth] = :birth
                AND [Sid] = :sid
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

    public function teacherLogin($data)
    {

        $values = [
            "grade" => '',
            "class" => '',
            "passwd" => '',
            "sid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Teacher].*, [SchoolList].[Used] AS School_Used 
                FROM [Literacy].[dbo].[SchoolList] 
                INNER JOIN [Teacher] ON [SchoolList].[Sid] = [Teacher].[Sid] 
                WHERE [Teacher].[Grade] = :grade 
                AND [Teacher].[Class] = :class 
                AND [Teacher].[PassWD] COLLATE Chinese_PRC_CS_AS = :passwd 
                AND [Teacher].[Sid] = :sid
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

    public function schoolLogin($data)
    {

        $values = [
            "sid" => '',
            "cityid" => '',
            "postid" => '',
            "class" => '',
            "passwd" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = 'SELECT [Sid], [SchoolName], [Audition]
                FROM [Literacy].[dbo].[SchoolList]
                WHERE [CityId] = :cityid 
                AND [PostId] = :postid 
                AND [Class] = :class 
                AND [PassWD] = :passwd
                AND [Sid] = :sid
                ORDER BY [SchoolName]
                ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $status = ["status" => "failed"];
        foreach ($result as $key => $value) {
            if ($value['Audition'] == 0) {
                return $status = [
                    "status" => "failed",
                    "message" => "尚未通過申請"
                ];
            }
        }
        session_start();
        foreach ($result as $key => $value) {
            $_SESSION['Sid'] = $value['Sid'];
            $_SESSION['Class'] = $value['Class'];
            $_SESSION['login'] = 0;
            $status = ["status" => "success"];
        }
        session_write_close();
        return $status;
    }

    public function adminLogin($data)
    {

        $values = [
            "accid" => '',
            "passwd" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT * 
                FROM [SystemAdmin] 
                WHERE [AccID] COLLATE Chinese_PRC_CS_AS = :accid 
                AND [PassWD] COLLATE Chinese_PRC_CS_AS = :passwd
                ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $status = ["status" => "failed"];
        session_start();
        foreach ($result as $key => $value) {
            $_SESSION['Uid'] = $value['UID'];
            $_SESSION['login'] = 0;
            $status = ["status" => "success"];
        }
        session_write_close();
        return $status;
    }

    public function getTeaData($data)
    {
        $values = [
            "sid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Literacy].[dbo].[Teacher].Tid, [Literacy].[dbo].[Teacher].Grade
                , [Literacy].[dbo].[Teacher].Class, [Literacy].[dbo].[Teacher].TeacherName
                , [Literacy].[dbo].[Teacher].TeacherMail, [Literacy].[dbo].[Teacher].PassWD
                , [Literacy].[dbo].[Teacher].[GraduationYear]
                , COUNT([Student].Sid) AS StuCount
                FROM [Literacy].[dbo].[Teacher]
                LEFT JOIN [Literacy].[dbo].[Student] ON [Literacy].[dbo].[Teacher].Sid = [Literacy].[dbo].[Student].Sid 
                AND [Literacy].[dbo].[Teacher].Tid = [Literacy].[dbo].[Student].Tid
                WHERE [Literacy].[dbo].[Teacher].Sid = :sid
                GROUP BY [Literacy].[dbo].[Teacher].Tid, [Literacy].[dbo].[Teacher].Grade
                , [Literacy].[dbo].[Teacher].Class, [Literacy].[dbo].[Teacher].TeacherName
                , [Literacy].[dbo].[Teacher].TeacherMail, [Literacy].[dbo].[Teacher].PassWD
                , [Literacy].[dbo].[Teacher].[GraduationYear]
                ORDER BY 
                    CASE [Literacy].[dbo].[Teacher].Grade 
                        WHEN '一' THEN 1 
                        WHEN '二' THEN 2
                        WHEN '三' THEN 3
                        WHEN '四' THEN 4
                        WHEN '五' THEN 5
                        WHEN '六' THEN 6
                        WHEN '畢' THEN 7
                        ELSE 999999 END, 
                        [Literacy].[dbo].[Teacher].Grade, [Literacy].[dbo].[Teacher].Class
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        session_start();
        $_SESSION['Tid'] = $value['tid'];
        session_write_close();
        return $result;
    }

    public function post_admin_import_student_data($data)
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
            return ["status" => "success"];
        } else {
            var_dump($sth->errorInfo());
            return ["status" => "failure"];
        }
    }

    public function getStuData($data)
    {
        $values = [
            "tid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT ROW_NUMBER() OVER(ORDER BY [Student].[Sid]) AS 'key'
                , [Literacy].[dbo].[Student].Pid, [Literacy].[dbo].[Student].Sid
                , [Literacy].[dbo].[Student].Tid, [Literacy].[dbo].[Student].Year
                , [Literacy].[dbo].[Student].StuName, [Literacy].[dbo].[Student].IDNumber
                , [Literacy].[dbo].[Student].StuNum, [Literacy].[dbo].[Student].SeatNum
                , [Literacy].[dbo].[Student].Birth, [Literacy].[dbo].[Student].Sex
                , [Literacy].[dbo].[Student].Parent_Edu, [Literacy].[dbo].[Student].Parent_Edu_M
                , [Literacy].[dbo].[Student].Parent_Job, [Literacy].[dbo].[Student].Parent_Job_M
                , [Literacy].[dbo].[Student].AddTime, [Literacy].[dbo].[Student].Pre_Grade
                , [Literacy].[dbo].[Student].Pre_Class,[Literacy].[dbo].[Student].GraduationYear
                , COUNT([Literacy].[dbo].[Exam_Word_Score].Sid) AS Count_Exam 
                FROM [Literacy].[dbo].[Student] 
                LEFT OUTER JOIN [Literacy].[dbo].[Exam_Word_Score] ON [Literacy].[dbo].[Student].Pid = [Literacy].[dbo].[Exam_Word_Score].Pid
                GROUP BY [Literacy].[dbo].[Student].Pid, [Literacy].[dbo].[Student].Sid
                , [Literacy].[dbo].[Student].Tid, [Literacy].[dbo].[Student].Year
                , [Literacy].[dbo].[Student].StuName, [Literacy].[dbo].[Student].IDNumber
                , [Literacy].[dbo].[Student].StuNum, [Literacy].[dbo].[Student].SeatNum
                , [Literacy].[dbo].[Student].Birth, [Literacy].[dbo].[Student].Sex
                , [Literacy].[dbo].[Student].Parent_Edu, [Literacy].[dbo].[Student].Parent_Edu_M
                , [Literacy].[dbo].[Student].Parent_Job, [Literacy].[dbo].[Student].Parent_Job_M
                , [Literacy].[dbo].[Student].AddTime, [Literacy].[dbo].[Student].Pre_Grade
                , [Literacy].[dbo].[Student].Pre_Class, [Literacy].[dbo].[Student].GraduationYear
                , CAST([Literacy].[dbo].[Student].SeatNum AS int)
                HAVING [Literacy].[dbo].[Student].Tid = :tid
                ORDER BY CAST([Literacy].[dbo].[Student].SeatNum AS int)
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_single_student_data($data)
    {
        $values = [
            "tid" => 0,
            "pid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Literacy].[dbo].[Student].Pid, [Literacy].[dbo].[Student].Sid
                , [Literacy].[dbo].[Student].Tid, [Literacy].[dbo].[Student].Year
                , [Literacy].[dbo].[Student].StuName, [Literacy].[dbo].[Student].IDNumber
                , [Literacy].[dbo].[Student].StuNum, [Literacy].[dbo].[Student].SeatNum
                , [Literacy].[dbo].[Student].Birth, [Literacy].[dbo].[Student].Sex
                , COUNT([Literacy].[dbo].[Exam_Word_Score].Sid) AS Count_Exam 
                FROM [Literacy].[dbo].[Student] 
                LEFT OUTER JOIN [Literacy].[dbo].[Exam_Word_Score] ON [Literacy].[dbo].[Student].Pid = [Literacy].[dbo].[Exam_Word_Score].Pid
                GROUP BY [Literacy].[dbo].[Student].Pid, [Literacy].[dbo].[Student].Sid
                , [Literacy].[dbo].[Student].Tid, [Literacy].[dbo].[Student].Year
                , [Literacy].[dbo].[Student].StuName, [Literacy].[dbo].[Student].IDNumber
                , [Literacy].[dbo].[Student].StuNum, [Literacy].[dbo].[Student].SeatNum
                , [Literacy].[dbo].[Student].Birth, [Literacy].[dbo].[Student].Sex
                , [Literacy].[dbo].[Student].Pre_Class, [Literacy].[dbo].[Student].GraduationYear
                , CAST([Literacy].[dbo].[Student].SeatNum AS int)
                HAVING [Literacy].[dbo].[Student].Tid = :tid
                AND [Literacy].[dbo].[Student].Pid = :pid
                ORDER BY CAST([Literacy].[dbo].[Student].SeatNum AS int)
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function transfer_school_teacher_student($data)
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
                "status" => "failure"
            ];
            return $status;
        }
        $status = [
            "status" => "success"
        ];
        return $status;
    }

    public function patch_single_student_transfer($data)
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
                "status" => "failure"
            ];
            return $status;
        }
        $status = [
            "status" => "success"
        ];
        return $status;
    }

    public function patch_single_student_data($data)
    {
        $values = [
            "year" => '',
            "stuname" => '',
            "idnumber" => '',
            "stunum" => '',
            "seatnum" => '',
            "birth" => '',
            "sex" => '',
            'pid' => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (strlen($data['idnumber']) != 6) {
            return [
                "status" => "failure",
                "message" => "請檢查身分證後六碼，是否超過或小於六位元"
            ];
        }

        $sql = "UPDATE [Literacy].[dbo].[Student] 
                Set [Year] = :year
                , [StuName] = :stuname
                , [IDNumber] = :idnumber
                , [StuNum] = :stunum
                , [SeatNum] = :seatnum
                , [Birth] = :birth
                , [Sex] = :sex
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

    public function patch_teacher_student_data($data)
    {
        if (strlen($data['idnumber']) != 6) {
            return [
                "status" => "failure",
                "message" => "請檢查身分證後六碼，是否超過或小於六位元"
            ];
        }

        $values = [
            "year" => '',
            "stuname" => '',
            "idnumber" => '',
            "stunum" => '',
            "seatnum" => '',
            "birth" => '',
            "sex" => '',
            'pid' => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }


        $sql = "UPDATE [Literacy].[dbo].[Student] 
                Set 
                , [Year] = :year
                , [StuName] = :stuname
                , [IDNumber] = :idnumber
                , [StuNum] = :stunum
                , [SeatNum] = :seatnum
                , [Birth] = :birth
                , [Sex] = :sex
                WHERE [Pid] = :pid
            ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            $status = [
                "status" => "failure"
            ];
            return $status;
        }
        $status = [
            "status" => "success"
        ];
        return $status;
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

    public function get_stuednt_transfer($data)
    {
        $values = [
            "sid" => '',
            "cur_page" => 1,
            "size" => 10
        ];

        $string = "";
        $order = "";

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $values['length'] = $values['cur_page'] * $values['size'];
        $values['start'] = $values['length'] - $values['size'];

        if (array_key_exists('pre_grade', $data)) {
            $string .= " AND [Pre_Grade] = :pre_grade";
            $order .= "[Pre_Grade], ";
            $values['pre_grade'] = $data['pre_grade'];
        }
        if (array_key_exists('pre_class', $data)) {
            $string .= " AND [Pre_Class] = :pre_class";
            $order .= "[Pre_Class], ";
            $values['pre_class'] = $data['pre_class'];
        }

        unset($values['cur_page']);
        unset($values['size']);


        $sql = "SELECT *
                FROM (
                    SELECT ROW_NUMBER() OVER(ORDER BY [Sid]) AS 'key'
                    , ROW_NUMBER() OVER(ORDER BY [Sid]) AS row_num
                    , *    
                    FROM [Literacy].[dbo].[Student]
                    WHERE [Sid] = :sid
                    AND [Tid] = '0'
                    AND [GraduationYear] = ''
                    {$string}
                ) AS selection
                WHERE selection.row_num > :start AND selection.row_num <= :length
                ORDER BY {$order}[SeatNum], [StuNum]
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_stuednt_transfer_count($data)
    {
        $values = [
            "sid" => ''
        ];

        $string = "";
        $order = "";

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('pre_grade', $data)) {
            $string .= " AND [Pre_Grade] = :pre_grade";
            $order .= "[Pre_Grade], ";
            $values['pre_grade'] = $data['pre_grade'];
        }
        if (array_key_exists('pre_class', $data)) {
            $string .= " AND [Pre_Class] = :pre_class";
            $order .= "[Pre_Class], ";
            $values['pre_class'] = $data['pre_class'];
        }

        $sql = "SELECT COUNT(*) AS count_data
                FROM (
                    SELECT 
                    ROW_NUMBER() OVER(ORDER BY [Sid]) AS row_num
                    , *    
                    FROM [Literacy].[dbo].[Student]
                    WHERE [Sid] = :sid
                    AND [Tid] = '0'
                    AND [GraduationYear] = ''
                    {$string}
                ) AS selection
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function delete_stuednt_transfer($data)
    {
        $values = [
            "pid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "Delete FROM [Student] 
                Where [Pid] = :pid
        ";

        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "info" => $stmt->errorInfo()
            ];
        }
        return [
            "status" => "success"
        ];
    }

    public function patch_stuednt_transfer($data)
    {
        $values = [
            "pid" => '',
            "sid" => '',
            "tid" => '',
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "Update [Student] Set 
                [Sid] = :sid,
                [Tid] = :tid
                Where [Pid] = :pid
        ";

        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "info" => $stmt->errorInfo()
            ];
        }
        return [
            "status" => "success"
        ];
    }

    public function getSchoolDataSchool($data)
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
            $values['sid2'] = $values['sid'];
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
            $values['sid2'] = $values['sid'];
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

    public function add_school($data)
    {
        $values = [
            "cityid" => '',
            "area" => '',
            "class" => '',
            "schoolid" => '',
            "schoolname" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "INSERT INTO [School] (City, Area, Class, SchoolID, SchoolName)
            VALUES (:cityid, :area, :class, :schoolid, :schoolname)
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "新增失敗，請聯絡網站管理人。"
            ];
        }
        return [
            "status" => "success",
            "message" => "新增成功"
        ];
    }

    public function getScore($data)
    {
        $values = [
            "tid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT *
                 ,(
                    CASE [Literacy].[dbo].[View_ScoreList].[ColorNum]
                        WHEN '01' THEN '白色' 
                        WHEN '02' THEN '黑色'
                        WHEN '03' THEN '紅色'
                        WHEN '04' THEN '橙色'
                        WHEN '05' THEN '黃色'
                        WHEN '06' THEN '綠色'
                        WHEN '0607' THEN '綠藍色'
                        WHEN '07' THEN '藍色'
                        WHEN '08' THEN '靛色'
                        WHEN '09' THEN '紫色'
                        WHEN '10' THEN '銅色'
                        WHEN '11' THEN '銀色'
                        WHEN '12' THEN '金色'
                         END
                    ) AS ColorNumName
                FROM [Literacy].[dbo].[View_ScoreList] 
                WHERE [Literacy].[dbo].[View_ScoreList].Tid = :tid
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function searchScore($data)
    {
        $values = [
            "tid" => 0
        ];
        $string = "";
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('wid', $data)) {
            $string .= " AND [Literacy].[dbo].[View_ScoreList].[Wid] = :wid";
            $values['wid'] = $data['wid'];
        }
        if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            $string .= "  AND addtime BETWEEN :start_date AND :end_date";
            $values['start_date'] = $data['start_date'];
            $values['end_date'] = $data['end_date'];
        }
        if (array_key_exists('exam_year', $data) && array_key_exists('exam_term', $data) && array_key_exists('exam_tkind', $data)) {
            $string .= " AND [Literacy].[dbo].[View_ScoreList].[Exam_TimePoint] = :exam_year+'學年度'+:exam_term+'學期'+:exam_tkind";
            $values['exam_year'] = $data['exam_year'];
            $values['exam_term'] = $data['exam_term'];
            $values['exam_tkind'] = $data['exam_tkind'];
        }

        $sql = "SELECT * , CONVERT (char,  [Literacy].[dbo].[View_ScoreList].AddTime, 111) AS addtime
                ,(
                    CASE [Literacy].[dbo].[View_ScoreList].[ColorNum]
                        WHEN '01' THEN '白色' 
                        WHEN '02' THEN '黑色'
                        WHEN '03' THEN '紅色'
                        WHEN '04' THEN '橙色'
                        WHEN '05' THEN '黃色'
                        WHEN '06' THEN '綠色'
                        WHEN '0607' THEN '綠藍色'
                        WHEN '07' THEN '藍色'
                        WHEN '08' THEN '靛色'
                        WHEN '09' THEN '紫色'
                        WHEN '10' THEN '銅色'
                        WHEN '11' THEN '銀色'
                        WHEN '12' THEN '金色'
                         END
                    ) AS ColorNumName
                FROM [Literacy].[dbo].[View_ScoreList]
                WHERE [Literacy].[dbo].[View_ScoreList].[Tid] = :tid
                {$string}
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_literacy_amount($data)
    {
        $values = [
            "check" => 'false',
            "cur_page" => 1,
            "size" => 10
        ];
        $string = "";

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $values['length'] = $values['cur_page'] * $values['size'];
        $values['start'] = $values['length'] - $values['size'];

        if ($values['check'] == 'true') {
            $string .= " WHERE [Literacy].[dbo].[Exam_Word_Score].[LiteracyScore] = '' OR [Literacy].[dbo].[Exam_Word_Score].[PR_Value] IS NULL AND [Literacy].[dbo].[Exam_Word].[Average_1S] <> '尚未建立' ";
            $values['check'] = $data['check'];
        }
        unset($values['check']);
        unset($values['cur_page']);
        unset($values['size']);

        $sql = "SELECT *
                FROM (	
                    SELECT 
                    ROW_NUMBER() OVER(ORDER BY [Literacy].[dbo].[Exam_Word_Score].[Sid]) AS row_num
                    , [Literacy].[dbo].[Exam_Word_Score].[Sid]
                    , [Literacy].[dbo].[Exam_Word_Score].[Score]
                    , [Literacy].[dbo].[Exam_Word_Score].[Exam_Term]
                    , [Literacy].[dbo].[Exam_Word_Score].[Exam_TKind]
                    , [Literacy].[dbo].[Exam_Word].* 
                    FROM [Literacy].[dbo].[Exam_Word_Score] 
                    INNER JOIN [Literacy].[dbo].[Exam_Word] 
                    ON [Literacy].[dbo].[Exam_Word_Score].[Wid] = [Literacy].[dbo].[Exam_Word].[Wid]
                    {$string}
                ) AS selection
                WHERE selection.row_num > :start AND selection.row_num <= :length
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        if ($sth->execute($values)) {
            $result = $sth->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } else {
            return [
                "status" => "failure"
            ];
        }
    }

    public function get_literacy_amount_count($data)
    {
        $values = [
            "check" => 'false'
        ];
        $string = "";

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if ($values['check'] == 'true') {
            $string .= " WHERE [Literacy].[dbo].[Exam_Word_Score].[LiteracyScore] = '' Or [Literacy].[dbo].[Exam_Word_Score].[PR_Value] IS NULL AND [Literacy].[dbo].[Exam_Word].[Average_1S] <> '尚未建立' ";
            $values['check'] = $data['check'];
        }

        $sql = "SELECT COUNT(*) AS count_data
                FROM (	
                    SELECT [Literacy].[dbo].[Exam_Word_Score].[Sid]
                    , [Literacy].[dbo].[Exam_Word_Score].[Score]
                    , [Literacy].[dbo].[Exam_Word_Score].[Exam_Term]
                    , [Literacy].[dbo].[Exam_Word_Score].[Exam_TKind]
                    , [Literacy].[dbo].[Exam_Word].* 
                    FROM [Literacy].[dbo].[Exam_Word_Score] 
                    INNER JOIN [Literacy].[dbo].[Exam_Word] 
                    ON [Literacy].[dbo].[Exam_Word_Score].[Wid] = [Literacy].[dbo].[Exam_Word].[Wid]
                    {$string}
                ) AS selection
            ";

        $sth = $this->container->db->prepare($sql);

        if ($sth->execute()) {
            $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }

    public function patch_literacyscore($data)
    {
        $sql = "UPDATE [Literacy].[dbo].[Exam_Word_Score] SET  
                [LiteracyScore] = ''
                WHERE ExamProgramKind in ('A','B')
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        return ["status" => "success"];
    }

    public function get_page_identify($data)
    {
        $values = [];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('tid', $data)) {
            $sql = "SELECT [Tid], [SchoolList].[SchoolName] + ' ' +[Grade]+'年'+[Teacher].[Class]+'班'+' ' + [TeacherName] title
                    , '老師' AS 'identity'
                    FROM [Literacy].[dbo].[Teacher]
                    LEFT JOIN [Literacy].[dbo].[SchoolList] ON [Literacy].[dbo].[Teacher].[Sid] = [Literacy].[dbo].[SchoolList].[Sid]
                    WHERE [Tid] = :tid
            ";
            $values['tid'] = $data['tid'];
        } else if (array_key_exists('sid', $data)) {
            $sql = "SELECT [Sid], [CityId]+' '+[PostId]+' '+[SchoolName] title
                    , '校方' AS 'identity'
                    FROM [Literacy].[dbo].[SchoolList] 
                    WHERE [Sid] = :sid
            ";
            $values['sid'] = $data['sid'];
        } else if (array_key_exists('uid', $data)) {
            $sql = "SELECT [AccID],[Name] title, '管理者' AS 'identity' 
                    FROM [Literacy].[dbo].[SystemAdmin]
                    WHERE [UID] = :uid
            ";
            $values['uid'] = $data['uid'];
        } else if (array_key_exists('pid', $data)) {
            $sql = "SELECT [Pid], [SchoolList].[SchoolName] + ' ' +[Grade]+'年'+[Teacher].[Class]+'班'+' '+ [StuName] title
                    , '學生' AS 'identity'
                    FROM [Literacy].[dbo].[Student]
                    LEFT JOIN [Literacy].[dbo].[Teacher] ON [Literacy].[dbo].[Student].[Tid] = [Literacy].[dbo].[Teacher].[Tid]
                    LEFT JOIN [Literacy].[dbo].[SchoolList] ON [Literacy].[dbo].[Teacher].[Sid] = [Literacy].[dbo].[SchoolList].[Sid]
                    WHERE [Pid] = :pid
            ";
            $values['pid'] = $data['pid'];
        } else if (array_key_exists('cityname', $data)) {
            $sql = "SELECT TOP 1000 [CityId],[CityName] title
                    ,'縣市' AS 'identity'
                    FROM [Literacy].[dbo].[City]
                    WHERE [CityName] = :cityname
            ";
            $values['cityname'] = $data['cityname'];
        }
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function logout_identify($data)
    {
        $values = [];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $flag = false;
        session_start();
        if (array_key_exists('Sid', $_SESSION)) {
            $_SESSION['Sid'] = null;
            $flag = true;
        } else if (array_key_exists('Tid', $_SESSION)) {
            $_SESSION['Tid'] = null;
            $flag = true;
        } else if (array_key_exists('Uid', $_SESSION)) {
            $_SESSION['Uid'] = null;
            $flag = true;
        } else if (array_key_exists('Pid', $_SESSION)) {
            $_SESSION['Pid'] = null;
            $flag = true;
        } else if (array_key_exists('CityName', $_SESSION)) {
            $_SESSION['CityName'] = null;
            $flag = true;
        }
        session_write_close();
        if ($flag) {
            return [
                "status" => "success"
            ];
        }
    }

    public function getStuReport($data)
    {
        $values = [
            "sid" => ''
        ];
        $string = "";
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            $string .= " AND (CONVERT(char, [AddTime], 111) Between :start_date AND :end_date";
            $values['start_date'] = $data['start_date'];
            $values['end_date'] = $data['end_date'];
        }
        if (array_key_exists('title', $data)) {
            $string .= " AND [Title] = :title";
            $values['title'] = $data['title'];
        }
        if (array_key_exists('stuname', $data)) {
            $string .= " AND [StuName] LIKE '%' + :stuname + '%'";
            $values['stuname'] = $data['stuname'];
        }
        if (array_key_exists('exam_year', $data)) {
            $string .= " AND [Exam_Year] = :exam_year";
            $values['exam_year'] = $data['exam_year'];
        }
        if (array_key_exists('exam_term', $data)) {
            $string .= " AND [Exam_Term] = :exam_term";
            $values['exam_term'] = $data['exam_term'];
        }
        if (array_key_exists('exam_tkind', $data)) {
            $string .= " AND [Exam_TKind] = :exam_tkind";
            $values['exam_tkind'] = $data['exam_tkind'];
        }
        if (array_key_exists('grade', $data)) {
            $string .= " AND [Grade] = :grade";
            $values['grade'] = $data['grade'];
        }
        if (array_key_exists('class', $data)) {
            $string .= " AND [Class] = :class";
            $values['class'] = $data['class'];
        }

        $sql = "SELECT * 
                , ( 
                    CASE 
                        WHEN [View_ScoreList].[ExamProgramKind] in ('A2','A3','A4','A5','A6','A7','A8','A09','A10') THEN 
                        CASE 
                            WHEN [View_ScoreList].[LiteracyScore] <= 730 THEN '白色' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 731 AND [View_ScoreList].[LiteracyScore] <= 1210 THEN '黑色'
                            WHEN [View_ScoreList].[LiteracyScore] >= 1211 AND [View_ScoreList].[LiteracyScore] <= 1300 THEN '紅色'
                            WHEN [View_ScoreList].[LiteracyScore] >= 1301 AND [View_ScoreList].[LiteracyScore] <= 1560 THEN '橙色' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 1561 AND [View_ScoreList].[LiteracyScore] <= 1870 THEN '黃色' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 1871 AND [View_ScoreList].[LiteracyScore] <= 2290 THEN '綠藍色'
                            WHEN [View_ScoreList].[LiteracyScore] >= 2291 AND [View_ScoreList].[LiteracyScore] <= 2490 THEN '靛色'
                            WHEN [View_ScoreList].[LiteracyScore] >= 2491 AND [View_ScoreList].[LiteracyScore] <= 2760 THEN '紫色'
                            WHEN [View_ScoreList].[LiteracyScore] >= 2761 AND [View_ScoreList].[LiteracyScore] <= 2840 THEN '銅色' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 2841 AND [View_ScoreList].[LiteracyScore] <= 3040 THEN '銀色' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 3041 THEN '金色' 
                            ELSE 
                                CASE [View_ScoreList].ColorNum
                                    WHEN '06' THEN '綠色'
                                    WHEN '07' THEN '藍色' 
                                    END 
                            END
                        END
                    ) AS ColorNumName
                FROM [Literacy].[dbo].[View_ScoreList]
                LEFT JOIN [Literacy].[dbo].[Student] ON [Literacy].[dbo].[Student].Pid = [Literacy].[dbo].[View_ScoreList].Pid
                WHERE [Student].[Sid] = :sid
                {$string}
                ORDER BY [View_ScoreList].[Sid] DESC
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_schoolMsg_excel($data)
    {
        $values = [];
        $string = "WHERE";
        $check = false;

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('cityid', $data)) {
            $string .= " [CityId] = :cityid AND";
            $values['cityid'] = $data['cityid'];
            $check = true;
        }
        if (array_key_exists('postid', $data)) {
            $string .= " [PostId] = :postid AND";
            $values['postid'] = $data['postid'];
            $check = true;
        }
        if (array_key_exists('schoolname', $data)) {
            $string .= " [SchoolName] LIKE '%' + :schoolname + '%' AND";
            $values['schoolname'] = $data['schoolname'];
            $check = true;
        }

        if ($check) {
            $string = rtrim($string, "AND");
        } else {
            $string = ltrim($string, "WHERE");
        }

        $sql = "SELECT * 
                FROM [Literacy].[dbo].[SchoolList]
                {$string}
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getSchoolMsg($data)
    {
        $values = [];
        $string = "WHERE";
        $check = false;

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('cityid', $data)) {
            $string .= " [CityId] = :cityid AND";
            $values['cityid'] = $data['cityid'];
            $check = true;
        }
        if (array_key_exists('postid', $data)) {
            $string .= " [PostId] = :postid AND";
            $values['postid'] = $data['postid'];
            $check = true;
        }
        if (array_key_exists('schoolname', $data)) {
            $string .= " [SchoolName] LIKE '%' + :schoolname + '%' AND";
            $values['schoolname'] = $data['schoolname'];
            $check = true;
        }

        if ($check) {
            $string = rtrim($string, "AND");
        } else {
            $string = ltrim($string, "WHERE");
        }

        $sql = "SELECT ROW_NUMBER() OVER(ORDER BY [Sid]) AS 'key'
                , * 
                FROM [Literacy].[dbo].[SchoolList]
                {$string}
                Order By [CityId], [PostId], [Class], [SchoolName]
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getStuReportExcel($data)
    {
        $values = [
            "sid" => ''
        ];
        $string = "";
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            $string .= " AND (CONVERT(char, [AddTime], 111) Between :start_date AND :end_date";
            $values['start_date'] = $data['start_date'];
            $values['end_date'] = $data['end_date'];
        }
        if (array_key_exists('title', $data)) {
            $string .= " AND [Title] = :title";
            $values['title'] = $data['title'];
        }
        if (array_key_exists('stuname', $data)) {
            $string .= " AND [StuName] LIKE '%' + :stuname + '%'";
            $values['stuname'] = $data['stuname'];
        }
        if (array_key_exists('exam_year', $data)) {
            $string .= " AND [Exam_Year] = :exam_year";
            $values['exam_year'] = $data['exam_year'];
        }
        if (array_key_exists('exam_term', $data)) {
            $string .= " AND [Exam_Term] = :exam_term";
            $values['exam_term'] = $data['exam_term'];
        }
        if (array_key_exists('exam_tkind', $data)) {
            $string .= " AND [Exam_TKind] = :exam_tkind";
            $values['exam_tkind'] = $data['exam_tkind'];
        }
        if (array_key_exists('grade', $data)) {
            $string .= " AND [Grade] = :grade";
            $values['grade'] = $data['grade'];
        }
        if (array_key_exists('class', $data)) {
            $string .= " AND [Class] = :class";
            $values['class'] = $data['class'];
        }

        $sql = "SELECT [Sid] AS 編號, [SchoolName] AS 學校名稱, [CityId] AS 學校縣巿, [PostId] AS 學校區域
                , CASE [Grade] 
                    WHEN '一' THEN '1' 
                    WHEN '二' THEN '2' 
                    WHEN '三' THEN '3' 
                    WHEN '四' THEN '4' 
                    WHEN '五' THEN '5' 
                    WHEN '六' THEN '6' 
                    END AS 年級
                , [Class] AS 班級, [Year] AS 入學學年度, [GraduationYear] AS 畢業學年度
                , [StuNum] AS 學號, [SeatNum] AS 座號, [StuName] AS 學生姓名, [Sex] AS 性別
                , [Title] AS 問卷種類, [LiteracyScore] AS 識字量 
                , CASE WHEN [ExamProgramKind] in ('A2','A3','A4','A5','A6','A7','A8','A09','A10') THEN 
                    CASE 
                        WHEN StartTime >= '2017/02/18' THEN 
                            CASE 
                                WHEN ColorNum ='01' THEN '白色' 
                                WHEN ColorNum ='02' THEN '黑色' 
                                WHEN ColorNum ='03' THEN '紅色' 
                                WHEN ColorNum ='04' THEN '橙色' 
                                WHEN ColorNum ='05' THEN '黃色' 
                                WHEN ColorNum ='0607' THEN '綠藍色'
                                WHEN ColorNum ='08' THEN '靛色' 
                                WHEN ColorNum ='09' THEN '紫色' 
                                WHEN ColorNum ='10' THEN '銅色' 
                                WHEN ColorNum ='11' THEN '銀色' 
                                WHEN ColorNum ='12' THEN '金色' 
                                ELSE NULL END 
                        ELSE NULL END 
                        ELSE 
                            CASE 
                                WHEN ColorNum ='01' THEN '白色' 
                                WHEN ColorNum ='02' THEN '黑色' 
                                WHEN ColorNum ='03' THEN '紅色' 
                                WHEN ColorNum ='04' THEN '橙色' 
                                WHEN ColorNum ='05' THEN '黃色' 
                                WHEN ColorNum ='06' THEN '綠色'
                                WHEN ColorNum ='07' THEN '藍色' 
                                WHEN ColorNum ='08' THEN '靛色' 
                                WHEN ColorNum ='09' THEN '紫色' 
                                WHEN ColorNum ='10' THEN '銅色' 
                                WHEN ColorNum ='11' THEN '銀色'
                                WHEN ColorNum ='12' THEN '金色' 
                                ELSE NULL END 
                        END AS 顏色 
                , [StartTime] AS 開始施測時間, [EndTime] AS 結束施測時間
                , [Exam_Grade] AS 施測時年級, [Exam_Class] AS 施測時班級, [Exam_TeacherName] AS 施測老師
                , [Exam_Year] AS 施測學年度, [Exam_Term] AS 施測學期, [Exam_TKind] AS 施測時間點
                , [ExamProgramKind] AS 施測版本
                FROM [Literacy].[dbo].[View_ScoreList]
                WHERE [School_sid] = :sid
                {$string}
                ORDER BY [Sid] DESC
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        // $result = array(
        //     [
        //         "Sid"=> "690038",
        //         "Year"=> "104",
        //         "StuName"=> "林○璟",
        //         "StuNum"=> "104039",
        //         "Sex"=> "男",
        //         "SchoolName"=> "市立美濃國小",
        //         "Grade"=> "畢",
        //         "Class"=> "2",
        //         "Title"=> "六年級甲版",
        //         "Score"=> "1110111110111111111111111011101111111111",
        //         "LiteracyScore"=> "3876",
        //         "LiteracyScore_3WV"=> "2355",
        //         "LiteracyScore_4WV"=> "2703",
        //         "StartTime"=> "2021/1/19 下午 01:31:56",
        //         "EndTime"=> "2021/1/19 下午 01:34:05",
        //         "AddTime"=> "2021-01-19 13:34:00",
        //         "SeatNum"=> "09",
        //         "School_Sid2"=> "11",
        //         "School_Sid"=> "11",
        //         "CityId"=> "高雄市",
        //         "PostId"=> "美濃區",
        //         "Parent_Edu"=> "",
        //         "Parent_Edu_M"=> null,
        //         "Parent_Job"=> "",
        //         "Parent_Job_M"=> null,
        //         "ColorNum"=> "12",
        //         "Pre_Grade"=> "六",
        //         "Pre_Class"=> "2",
        //         "GraduationYear"=> "109",
        //         "Exam_Grade"=> "六",
        //         "Exam_Class"=> "2",
        //         "Exam_TeacherName"=> "童暐茹",
        //         "Exam_Year"=> "109",
        //         "Exam_Term"=> "上",
        //         "Exam_TKind"=> "期末",
        //         "Pid"=> "177996",
        //         "PR_Value"=> "8.6277318826511504E-2",
        //         "FromWid"=> null,
        //         "ExamRM"=> "償諜洩鴦摧鴻狹模睹嘲依濛瑩貫嶇姍疙銳研眈",
        //         "ExamProgramKind"=> "A2",
        //         "FromWidTitle"=> null,
        //         "Theta"=> "1.75",
        //         "Z_Value"=> "1.75",
        //         "LiteracyScore_2WV"=> "2558",
        //         "LiteracyScore_Theta"=> "3876",
        //         "Exam_TimePoint"=> "109學年度上學期期末"
        //         ]
        //     );
        return $result;
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

    public function get_exam_transfer_stuent($data)
    {
        $values = [
            "sid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT * 
                FROM [Student] 
                WHERE Tid = :tid 
                ORDER BY CAST([SeatNum] AS int)
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getCityExamState($data)
    {
        $values = [
            "cityname" => '',
            "exam_year" => '110',
            "exam_term" => '下',
            "exam_tkind" => '期末'
        ];
        $string = "";
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('postid', $data)) {
            $string .= " AND [SchoolList].[PostId] = :postid";
            $values['postid'] = $data['postid'];
        }
        if (array_key_exists('schoolname', $data)) {
            $string .= " AND [SchoolList].[SchoolName] = :schoolname";
            $values['schoolname'] = $data['schoolname'];
        }

        $sql = "SELECT [SchoolList].[CityId], [SchoolList].[PostId], [SchoolList].[SchoolID]
                , [SchoolList].[SchoolName], [Teacher].[Grade], [Teacher].[Class], 
                COUNT(*) AS StuNum, COUNT( [View_RptPara_Max_Exam_Word_Score].Pid ) AS ExamNum
                FROM [Literacy].[dbo].[SchoolList] 
                INNER JOIN [Teacher] ON [SchoolList].[Sid] = [Teacher].[Sid]
                INNER JOIN [Student] ON [Teacher].[Tid] = [Student].[Tid] 
                LEFT OUTER JOIN [View_RptPara_Max_Exam_Word_Score] ON [Student].[Pid] = [View_RptPara_Max_Exam_Word_Score].[Pid] 
                AND [View_RptPara_Max_Exam_Word_Score].[Exam_Year] = :exam_year
                AND [View_RptPara_Max_Exam_Word_Score].[Exam_Term] = :exam_term
                AND [View_RptPara_Max_Exam_Word_Score].[Exam_TKind] = :exam_tkind
                WHERE [SchoolList].[CityId] = :cityname
                {$string}
                GROUP BY [SchoolList].[CityId], [SchoolList].[PostId], [SchoolList].[SchoolID]
                , [SchoolList].[SchoolName], [Teacher].[Grade], [Teacher].[Class]
                ORDER BY [SchoolList].[CityId], [SchoolList].[PostId], [SchoolList].[SchoolID]
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_school_applyfile_name($data)
    {
        $values = [
            "sid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [ApplyFiles]
                FROM [Literacy].[dbo].[SchoolList]
                WHERE [Sid] = :sid
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchColumn(0);
        return $result;
    }

    public function getCityExamReport($data)
    {
        $values = [];
        $string = "WHERE";
        $check = false;
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('cityid', $data)) {
            $string .= " [CityId] = :cityid AND";
            $values['cityid'] = $data['cityid'];
            $check = true;
        }
        if (array_key_exists('postid', $data)) {
            $string .= " [PostId] = :postid AND";
            $values['postid'] = $data['postid'];
            $check = true;
        }
        if (array_key_exists('schoolname', $data)) {
            $string .= " [SchoolName] = :schoolname AND";
            $values['schoolname'] = $data['schoolname'];
            $check = true;
        }
        if (array_key_exists('title', $data)) {
            $string .= " [Title] = :title AND";
            $values['title'] = $data['title'];
            $check = true;
        }
        if (array_key_exists('stuname', $data)) {
            $string .= " [StuName] LIKE '%' + :stuname + '%' AND";
            $values['stuname'] = $data['stuname'];
            $check = true;
        }
        if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            $string .= " CONVERT(char, [AddTime], 111) Between :start_date AND :end_date AND";
            $values['start_date'] = $data['start_date'];
            $values['end_date'] = $data['end_date'];
            $check = true;
        }

        if ($check) {
            $string = rtrim($string, "AND");
        } else {
            $string = ltrim($string, "WHERE");
        }

        $sql = "SELECT * 
                ,(
                    CASE [Literacy].[dbo].[View_ScoreList].[ColorNum]
                        WHEN '01' THEN '白色' 
                        WHEN '02' THEN '黑色'
                        WHEN '03' THEN '紅色'
                        WHEN '04' THEN '橙色'
                        WHEN '05' THEN '黃色'
                        WHEN '06' THEN '綠色'
                        WHEN '0607' THEN '綠藍色'
                        WHEN '07' THEN '藍色'
                        WHEN '08' THEN '靛色'
                        WHEN '09' THEN '紫色'
                        WHEN '10' THEN '銅色'
                        WHEN '11' THEN '銀色'
                        WHEN '12' THEN '金色'
                        ELSE '999999' END
                    ) AS ColorNumName
                FROM [Literacy].[dbo].[View_ScoreList]
                {$string}
                ORDER BY [Sid] DESC
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        // var_dump($sth->errorInfo());
        // exit(0);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getStuReportView($data)
    {
        $values = [
            "pid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Student].[Pid], [Student].[StuName], [Student].[StuNum], [Student].[SeatNum], [Exam_Word_Score].[Sid]
                , ([Exam_Word_Score].[Exam_Year] + '學年度' + [Exam_Word_Score].[Exam_Term] + '學期' + [Exam_Word_Score].[Exam_TKind]) AS Exam_TimePoint
                , [Exam_Word_Score].[LiteracyScore], [Exam_Word_Score].[Theta], [Exam_Word_Score].[PR_Value]
                , [Exam_Word_Score].[StartTime], [Exam_Word_Score].[EndTime], [Exam_Word_Score].[AddTime]
                , [Exam_Word].[Title] AS WKind, [Exam_Word_Score].[ExamProgramKind]
                , CASE 
                    WHEN [Exam_Word_Score].[Exam_Term] = '上' AND [Exam_Word_Score].[Exam_TKind] = '期初' THEN Average_1S
                    WHEN [Exam_Word_Score].[Exam_Term] = '上' AND [Exam_Word_Score].[Exam_TKind] = '期末' THEN Average_1E
                    WHEN [Exam_Word_Score].[Exam_Term] = '下' AND [Exam_Word_Score].[Exam_TKind] = '期初' THEN Average_2S
                    WHEN [Exam_Word_Score].[Exam_Term] = '下' AND [Exam_Word_Score].[Exam_TKind] = '期末' THEN Average_2E
                    ELSE NULL END AS Average
                , CASE 
                    WHEN [Exam_Word_Score].[Exam_Term] = '上' AND [Exam_Word_Score].[Exam_TKind] = '期初' THEN Standard_1S
                    WHEN [Exam_Word_Score].[Exam_Term] = '上' AND [Exam_Word_Score].[Exam_TKind] = '期末' THEN Standard_1E
                    WHEN [Exam_Word_Score].[Exam_Term] = '下' AND [Exam_Word_Score].[Exam_TKind] = '期初' THEN Standard_2S
                    WHEN [Exam_Word_Score].[Exam_Term] = '下' AND [Exam_Word_Score].[Exam_TKind] = '期末' THEN Standard_2E
                    ELSE NULL END AS Standard
                , ( 
                    CASE 
                        WHEN [Exam_Word_Score].[ExamProgramKind] in ('A2','A3','A4','A5','A6','A7','A8','A09','A10') THEN 
                        CASE 
                            WHEN [Exam_Word_Score].[LiteracyScore] <= 730 THEN '01' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 731 AND [Exam_Word_Score].[LiteracyScore] <= 1210 THEN '02' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 1211 AND [Exam_Word_Score].[LiteracyScore] <= 1300 THEN '03' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 1301 AND [Exam_Word_Score].[LiteracyScore] <= 1560 THEN '04' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 1561 AND [Exam_Word_Score].[LiteracyScore] <= 1870 THEN '05' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 1871 AND [Exam_Word_Score].[LiteracyScore] <= 2290 THEN '0607' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 2291 AND [Exam_Word_Score].[LiteracyScore] <= 2490 THEN '08' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 2491 AND [Exam_Word_Score].[LiteracyScore] <= 2760 THEN '09' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 2761 AND [Exam_Word_Score].[LiteracyScore] <= 2840 THEN '10' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 2841 AND [Exam_Word_Score].[LiteracyScore] <= 3040 THEN '11' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 3041 THEN '12' 
                            ELSE NULL END 
                            ELSE CASE 
                                WHEN [Exam_Word_Score].[LiteracyScore] <= 499 THEN '01' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 500 AND [Exam_Word_Score].[LiteracyScore] <= 899 THEN '02' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 900 AND [Exam_Word_Score].[LiteracyScore] <= 1129 THEN '03' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 1130 AND [Exam_Word_Score].[LiteracyScore] <= 1449 THEN '04' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 1450 AND [Exam_Word_Score].[LiteracyScore] <= 1999 THEN '05' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2000 AND [Exam_Word_Score].[LiteracyScore] <= 2249 THEN '06' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2250 AND [Exam_Word_Score].[LiteracyScore] <= 2599 THEN '07' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2600 AND [Exam_Word_Score].[LiteracyScore] <= 2899 THEN '08' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2900 AND [Exam_Word_Score].[LiteracyScore] <= 3099 THEN '09' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 3100 AND [Exam_Word_Score].[LiteracyScore] <= 3299 THEN '10' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 3300 AND [Exam_Word_Score].[LiteracyScore] <= 3499 THEN '11' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 3500 THEN '12' 
                                ELSE NULL 
                                END 
                            END 
                        )AS ColorNum
                        , (
                            CASE 
                                WHEN [Exam_Word_Score].[LiteracyScore] <= 730 THEN '白色' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 731 AND [Exam_Word_Score].[LiteracyScore] <= 1210 THEN '黑色'
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 1211 AND [Exam_Word_Score].[LiteracyScore] <= 1300 THEN '紅色'
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 1301 AND [Exam_Word_Score].[LiteracyScore] <= 1560 THEN '橙色' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 1561 AND [Exam_Word_Score].[LiteracyScore] <= 1870 THEN '黃色' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 1871 AND [Exam_Word_Score].[LiteracyScore] <= 2290 THEN '綠藍色'
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2291 AND [Exam_Word_Score].[LiteracyScore] <= 2490 THEN '靛色'
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2491 AND [Exam_Word_Score].[LiteracyScore] <= 2760 THEN '紫色'
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2761 AND [Exam_Word_Score].[LiteracyScore] <= 2840 THEN '銅色' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2841 AND [Exam_Word_Score].[LiteracyScore] <= 3040 THEN '銀色' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 3041 THEN '金色' 
                                ELSE 
                                    CASE ColorNum
                                        WHEN '06' THEN '綠色'
                                        WHEN '07' THEN '藍色' 
                                        END 
                                END
                    
                            ) AS ColorNumName
                FROM [Literacy].[dbo].[Student] 
                INNER JOIN [Literacy].[dbo].[Exam_Word_Score] ON [Literacy].[dbo].[Student].[Pid] = [Literacy].[dbo].[Exam_Word_Score].[Pid] 
                INNER JOIN [Literacy].[dbo].[Exam_Word] ON [Literacy].[dbo].[Exam_Word_Score].[Wid] = [Literacy].[dbo].Exam_Word.[Wid]
                WHERE [Student].[Pid] = :pid
                ORDER BY [Exam_Word_Score].[Sid] DESC
        
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_certificate_choose($data)
    {
        $values = [
            "sid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Literacy].[dbo].[Exam_Word_Score].[Sid]
                , [Literacy].[dbo].[Exam_Word_Score].[Pid]
                , [Literacy].[dbo].[Exam_Word_Score].[Tid]
                , [Literacy].[dbo].[Exam_Word_Score].[EndTime]
                , [Literacy].[dbo].[Exam_Word_Score].[LiteracyScore]
                , [Literacy].[dbo].[Exam_Word_Score].[Exam_TeacherName]
                , [Literacy].[dbo].[SchoolList].[CityId]
                , [Literacy].[dbo].[SchoolList].[SchoolName]
                , [Literacy].[dbo].[Student].[StuName]
                , [Literacy].[dbo].[Teacher].[Grade] 
                , [Literacy].[dbo].[Teacher].[Class] 
                , [Literacy].[dbo].[Teacher].[TeacherName]
                , [Literacy].[dbo].[Exam_Word_Score].[Exam_Grade]
                , [Literacy].[dbo].[Exam_Word_Score].[Exam_Class]
                , [Literacy].[dbo].[Exam_Word_Score].[ExamProgramKind]
                , ( 
                    CASE 
                        WHEN [Exam_Word_Score].[ExamProgramKind] in ('A2','A3','A4','A5','A6','A7','A8','A09','A10') THEN 
                        CASE 
                            WHEN [Exam_Word_Score].[LiteracyScore] <= 730 THEN '01' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 731 AND [Exam_Word_Score].[LiteracyScore] <= 1210 THEN '02' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 1211 AND [Exam_Word_Score].[LiteracyScore] <= 1300 THEN '03' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 1301 AND [Exam_Word_Score].[LiteracyScore] <= 1560 THEN '04' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 1561 AND [Exam_Word_Score].[LiteracyScore] <= 1870 THEN '05' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 1871 AND [Exam_Word_Score].[LiteracyScore] <= 2290 THEN '0607' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 2291 AND [Exam_Word_Score].[LiteracyScore] <= 2490 THEN '08' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 2491 AND [Exam_Word_Score].[LiteracyScore] <= 2760 THEN '09' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 2761 AND [Exam_Word_Score].[LiteracyScore] <= 2840 THEN '10' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 2841 AND [Exam_Word_Score].[LiteracyScore] <= 3040 THEN '11' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 3041 THEN '12' 
                            ELSE NULL END 
                            ELSE CASE 
                                WHEN [Exam_Word_Score].[LiteracyScore] <= 499 THEN '01' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 500 AND [Exam_Word_Score].[LiteracyScore] <= 899 THEN '02' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 900 AND [Exam_Word_Score].[LiteracyScore] <= 1129 THEN '03' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 1130 AND [Exam_Word_Score].[LiteracyScore] <= 1449 THEN '04' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 1450 AND [Exam_Word_Score].[LiteracyScore] <= 1999 THEN '05' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2000 AND [Exam_Word_Score].[LiteracyScore] <= 2249 THEN '06' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2250 AND [Exam_Word_Score].[LiteracyScore] <= 2599 THEN '07' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2600 AND [Exam_Word_Score].[LiteracyScore] <= 2899 THEN '08' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2900 AND [Exam_Word_Score].[LiteracyScore] <= 3099 THEN '09' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 3100 AND [Exam_Word_Score].[LiteracyScore] <= 3299 THEN '10' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 3300 AND [Exam_Word_Score].[LiteracyScore] <= 3499 THEN '11' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 3500 THEN '12' 
                                ELSE NULL 
                                END 
                            END 
                        )AS ColorNum
                , ( 
                    CASE 
                        WHEN [Exam_Word_Score].[LiteracyScore] <= 730 THEN '白色' 
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 731 AND [Exam_Word_Score].[LiteracyScore] <= 1210 THEN '黑色'
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 1211 AND [Exam_Word_Score].[LiteracyScore] <= 1300 THEN '紅色'
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 1301 AND [Exam_Word_Score].[LiteracyScore] <= 1560 THEN '橙色' 
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 1561 AND [Exam_Word_Score].[LiteracyScore] <= 1870 THEN '黃色' 
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 1871 AND [Exam_Word_Score].[LiteracyScore] <= 2290 THEN '綠藍色'
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 2291 AND [Exam_Word_Score].[LiteracyScore] <= 2490 THEN '靛色'
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 2491 AND [Exam_Word_Score].[LiteracyScore] <= 2760 THEN '紫色'
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 2761 AND [Exam_Word_Score].[LiteracyScore] <= 2840 THEN '銅色' 
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 2841 AND [Exam_Word_Score].[LiteracyScore] <= 3040 THEN '銀色' 
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 3041 THEN '金色' 
                        ELSE 
                            CASE ColorNum
                                WHEN '06' THEN '綠色'
                                WHEN '07' THEN '藍色' 
                                END 
                        END
                    ) AS ColorNumName
                FROM [Literacy].[dbo].[Exam_Word_Score]
                INNER JOIN [Literacy].[dbo].[Student] ON [Literacy].[dbo].[Exam_Word_Score].[Pid] = [Literacy].[dbo].[Student].[Pid]
                INNER JOIN [Literacy].[dbo].[Teacher] ON [Literacy].[dbo].[Student].[Tid] = [Literacy].[dbo].[Teacher].[Tid]
                INNER JOIN [Literacy].[dbo].[SchoolList] ON [Literacy].[dbo].[Exam_Word_Score].[Exam_Sid] = [Literacy].[dbo].[SchoolList].[Sid]
                WHERE [Exam_Word_Score].[Sid] = :sid
                AND [Exam_Word_Score].[Pid] = :pid
                Order By [Literacy].[dbo].[Exam_Word_Score].[Sid] DESC
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_certificate($data)
    {
        $values = [
            "pid" => ''
            // "sid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT TOP 1 [Literacy].[dbo].[Exam_Word_Score].[Sid]
                , [Literacy].[dbo].[Exam_Word_Score].[Pid]
                , [Literacy].[dbo].[Exam_Word_Score].[Tid]
                , [Literacy].[dbo].[Exam_Word_Score].[EndTime]
                , [Literacy].[dbo].[Exam_Word_Score].[Exam_TeacherName]
                , [Literacy].[dbo].[Exam_Word_Score].[LiteracyScore]
                , [Literacy].[dbo].[SchoolList].[SchoolName]
                , [Literacy].[dbo].[SchoolList].[CityId]
                , [Literacy].[dbo].[Student].[StuName]
                , [Literacy].[dbo].[Teacher].[Grade] 
                , [Literacy].[dbo].[Teacher].[Class] 
                , [Literacy].[dbo].[Teacher].[TeacherName]
                , [Literacy].[dbo].[Exam_Word_Score].[Exam_Grade]
                , [Literacy].[dbo].[Exam_Word_Score].[Exam_Class]
                , [Literacy].[dbo].[Exam_Word_Score].[ExamProgramKind]
                , ( 
                    CASE 
                        WHEN [Exam_Word_Score].[ExamProgramKind] in ('A2','A3','A4','A5','A6','A7','A8','A09','A10') THEN 
                        CASE 
                            WHEN [Exam_Word_Score].[LiteracyScore] <= 730 THEN '01' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 731 AND [Exam_Word_Score].[LiteracyScore] <= 1210 THEN '02' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 1211 AND [Exam_Word_Score].[LiteracyScore] <= 1300 THEN '03' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 1301 AND [Exam_Word_Score].[LiteracyScore] <= 1560 THEN '04' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 1561 AND [Exam_Word_Score].[LiteracyScore] <= 1870 THEN '05' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 1871 AND [Exam_Word_Score].[LiteracyScore] <= 2290 THEN '0607' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 2291 AND [Exam_Word_Score].[LiteracyScore] <= 2490 THEN '08' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 2491 AND [Exam_Word_Score].[LiteracyScore] <= 2760 THEN '09' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 2761 AND [Exam_Word_Score].[LiteracyScore] <= 2840 THEN '10' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 2841 AND [Exam_Word_Score].[LiteracyScore] <= 3040 THEN '11' 
                            WHEN [Exam_Word_Score].[LiteracyScore] >= 3041 THEN '12' 
                            ELSE NULL END 
                            ELSE CASE 
                                WHEN [Exam_Word_Score].[LiteracyScore] <= 499 THEN '01' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 500 AND [Exam_Word_Score].[LiteracyScore] <= 899 THEN '02' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 900 AND [Exam_Word_Score].[LiteracyScore] <= 1129 THEN '03' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 1130 AND [Exam_Word_Score].[LiteracyScore] <= 1449 THEN '04' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 1450 AND [Exam_Word_Score].[LiteracyScore] <= 1999 THEN '05' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2000 AND [Exam_Word_Score].[LiteracyScore] <= 2249 THEN '06' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2250 AND [Exam_Word_Score].[LiteracyScore] <= 2599 THEN '07' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2600 AND [Exam_Word_Score].[LiteracyScore] <= 2899 THEN '08' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2900 AND [Exam_Word_Score].[LiteracyScore] <= 3099 THEN '09' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 3100 AND [Exam_Word_Score].[LiteracyScore] <= 3299 THEN '10' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 3300 AND [Exam_Word_Score].[LiteracyScore] <= 3499 THEN '11' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 3500 THEN '12' 
                                ELSE NULL 
                                END 
                            END 
                        )AS ColorNum
                , ( 
                    CASE 
                        WHEN [Exam_Word_Score].[LiteracyScore] <= 730 THEN '白色' 
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 731 AND [Exam_Word_Score].[LiteracyScore] <= 1210 THEN '黑色'
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 1211 AND [Exam_Word_Score].[LiteracyScore] <= 1300 THEN '紅色'
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 1301 AND [Exam_Word_Score].[LiteracyScore] <= 1560 THEN '橙色' 
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 1561 AND [Exam_Word_Score].[LiteracyScore] <= 1870 THEN '黃色' 
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 1871 AND [Exam_Word_Score].[LiteracyScore] <= 2290 THEN '綠藍色'
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 2291 AND [Exam_Word_Score].[LiteracyScore] <= 2490 THEN '靛色'
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 2491 AND [Exam_Word_Score].[LiteracyScore] <= 2760 THEN '紫色'
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 2761 AND [Exam_Word_Score].[LiteracyScore] <= 2840 THEN '銅色' 
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 2841 AND [Exam_Word_Score].[LiteracyScore] <= 3040 THEN '銀色' 
                        WHEN [Exam_Word_Score].[LiteracyScore] >= 3041 THEN '金色' 
                        ELSE 
                            CASE ColorNum
                                WHEN '06' THEN '綠色'
                                WHEN '07' THEN '藍色' 
                                END 
                        END
                    ) AS ColorNumName
                FROM [Literacy].[dbo].[Exam_Word_Score]
                INNER JOIN [Literacy].[dbo].[Student] ON [Literacy].[dbo].[Exam_Word_Score].[Pid] = [Literacy].[dbo].[Student].[Pid]
                INNER JOIN [Literacy].[dbo].[Teacher] ON [Literacy].[dbo].[Student].[Tid] = [Literacy].[dbo].[Teacher].[Tid]
                INNER JOIN [Literacy].[dbo].[SchoolList] ON [Literacy].[dbo].[Exam_Word_Score].[Exam_Sid] = [Literacy].[dbo].[SchoolList].[Sid]
                WHERE [Exam_Word_Score].[Pid] = :pid
                -- AND [SchoolList].[Sid] = :sid
                ORDER BY [Literacy].[dbo].[Exam_Word_Score].[Sid] DESC
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


    public function getElementPic($data)
    {
        $values = [
            "sid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Class] 
                FROM [Literacy].[dbo].[SchoolList]
                WHERE [Sid] = :sid
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function sampleFile($data)
    {
        $values = [
            "manual_id" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Manual_name]
                FROM [Literacy].[dbo].[Manual]
                WHERE [Manual_id] = :manual_id
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getFormKind()
    {
        // $sql = "SELECT [Literacy].[dbo].[Exam_Word].[Wid] ,[Literacy].[dbo].[Exam_Word].[Grade]
        //         ,[Literacy].[dbo].[Exam_Word].[Title] ,[Literacy].[dbo].[Exam_Word].[Words]
        //         ,[Literacy].[dbo].[Exam_Word].[Average] ,[Literacy].[dbo].[Exam_Word].[Standard]
        //         ,[Literacy].[dbo].[Exam_Word].[Average_1S] ,[Literacy].[dbo].[Exam_Word].[Standard_1S]
        //         ,[Literacy].[dbo].[Exam_Word].[Average_1E] ,[Literacy].[dbo].[Exam_Word].[Standard_1E]
        //         ,[Literacy].[dbo].[Exam_Word].[Average_2S] ,[Literacy].[dbo].[Exam_Word].[Standard_2S]
        //         ,[Literacy].[dbo].[Exam_Word].[Average_2E] ,[Literacy].[dbo].[Exam_Word].[Standard_2E]
        //         ,[Literacy].[dbo].[Exam_Word].[Show] ,[Literacy].[dbo].[Exam_Word].[AddTime]
        //     FROM [Literacy].[dbo].[Exam_Word]
        //     ORDER BY Grade, Wid    
        // ";
        $sql = "SELECT [Wid], [Grade], [Title], [Words], [Average], [Standard]
                , [Average_1S], [Standard_1S], [Average_1E], [Standard_1E], [Average_2S]
                , [Standard_2S], [Average_2E], [Standard_2E], [Show], [AddTime]
            FROM [Literacy].[dbo].[Exam_Word]
            ORDER BY [Wid]
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getUp1Y()
    {
        $sql = "SELECT * 
                FROM [Literacy].[dbo].[Options]
                WHERE [OptionItem] = 'Up1Y'
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function check_up_1Y()
    {
        $sql = "SELECT [OptionValues] 
                FROM [Literacy].[dbo].[Options]
                WHERE [OptionItem] = 'Up1Y'
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchColumn(0);
        return $result;
    }

    public function patch_up_1Y($data)
    {
        $values = [
            "year" => '',
            'date' => '',
            "month" => '',
            "graduationyear" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        for ($i = 1; $i < 9; $i++) {
            $values['year_' . $i] = $data['year'];
        }
        $values['month_1'] = $data['month'];
        $values['date_1'] = $data['date'];

        // var_dump(count($values));
        // exit(0);

        $sql = "UPDATE [Student] SET
                [Pre_Grade] = [Teacher].[Grade]
                , [Pre_Class] = [Teacher].[Class]
                , [Pre_SeatNum] = [SeatNum] 
                FROM [Student] 
                INNER JOIN [Teacher] ON [Student].[Tid] = [Teacher].[Tid]
                WHERE [Teacher].[Grade] <> '畢'

                UPDATE [Student] Set [Tid] = '0' 
                WHERE [Pre_Grade] = '' AND  [Pre_Class] = ''

                UPDATE [Teacher] Set [Grade] = '畢'
                , [GraduationYear] = :graduationyear
                FROM [Teacher] 
                INNER JOIN [SchoolList] ON [Teacher].[Sid] = [SchoolList].[Sid] 
                WHERE [Grade] ='六' 
                AND (LEFT([SchoolList].[UpInfo], 4) <> :year_1)

                UPDATE [Teacher] Set [Grade] = '六' 
                FROM [Teacher] 
                INNER JOIN [SchoolList] ON [Teacher].[Sid] = [SchoolList].[Sid] 
                WHERE [Grade] ='五' 
                AND (LEFT([SchoolList].[UpInfo], 4) <> :year_2)

                UPDATE [Teacher] Set [Grade] = '五' 
                FROM [Teacher] 
                INNER JOIN [SchoolList] ON [Teacher].[Sid] = [SchoolList].[Sid] 
                WHERE [Grade] ='四' 
                AND (LEFT([SchoolList].[UpInfo], 4) <> :year_3)

                UPDATE [Teacher] Set [Grade] = '四' 
                FROM [Teacher] 
                INNER JOIN [SchoolList] ON [Teacher].[Sid] = [SchoolList].[Sid] 
                WHERE [Grade] ='三' 
                AND (LEFT([SchoolList].[UpInfo], 4) <> :year_4)

                UPDATE [Teacher] Set [Grade] = '三' 
                FROM [Teacher] 
                INNER JOIN [SchoolList] ON [Teacher].[Sid] = [SchoolList].[Sid] 
                WHERE [Grade] ='二' 
                AND (LEFT([SchoolList].[UpInfo], 4) <> :year_5)

                UPDATE [Teacher] Set [Grade] = '二' 
                FROM [Teacher] 
                INNER JOIN [SchoolList] ON [Teacher].[Sid] = [SchoolList].[Sid] 
                WHERE [Grade] ='一' 
                AND (LEFT([SchoolList].[UpInfo], 4) <> :year_6)

                UPDATE [Options] SET [OptionValues] = :year_7 + '/' + :month_1 + '/' + :date_1
                WHERE (OptionItem = 'Up1Y')

                UPDATE [SchoolList] SET 
                [UpInfo] = :year + '/' + :month + '/' + :date + '_全資料庫升年級' 
                WHERE (LEFT([SchoolList].[UpInfo], 4) <> :year_8)
            ";

        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "message" => "升級失敗!",
                "error" => $stmt->errorInfo()
            ];
        }
        return [
            "status" => "success",
            "message" => "全資料庫已將年級全數升1年級(六年級則升畢年級)"
        ];
    }

    public function check_class_up_1Y($data)
    {
        $values = [
            "sid" => ''
        ];

        // foreach (array_key($values) as $key) {
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [UpInfo] 
                FROM [Literacy].[dbo].[SchoolList]
                WHERE [Sid] = :sid
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchColumn(0);
        return $result;
    }

    public function patch_class_up_1Y($data)
    {
        $values = [
            "sid" => '',
            "year" => '',
            "month" => '',
            "date" => '',
            "graduationyear" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        for ($i = 1; $i < 9; $i++) {
            $values['sid' . $i] = $data['sid'];
        }

        $sql = "UPDATE [Student] SET
                [Pre_Grade] = [Teacher].[Grade]
                , [Pre_Class] = [Teacher].[Class]
                , [Pre_SeatNum] = [SeatNum] 
                FROM [Student] 
                INNER JOIN [Teacher] ON [Student].[Tid] = [Teacher].[Tid]
                WHERE [Teacher].[Grade] <> '畢'
                AND Student.Sid = :sid

                UPDATE [Student] Set [Tid] = '0' 
                WHERE [Pre_Grade] = '' AND  [Pre_Class] = ''
                AND Student.Sid = :sid1

                UPDATE Teacher Set Grade = '畢', GraduationYear = :graduationyear 
                WHERE Grade ='六' AND Sid = :sid2

                UPDATE Teacher Set Grade = '六' WHERE Grade ='五' AND Sid = :sid3

                UPDATE Teacher Set Grade = '五' WHERE Grade ='四' AND Sid = :sid4

                UPDATE Teacher Set Grade = '四' WHERE Grade ='三' AND Sid = :sid5

                UPDATE Teacher Set Grade = '三' WHERE Grade ='二' AND Sid = :sid6

                UPDATE Teacher Set Grade = '二' WHERE Grade ='一' AND Sid = :sid7

                UPDATE [SchoolList] SET 
                [UpInfo] = :year + '/' + :month + '/' + :date + '_單校升年級' 
                WHERE Sid = :sid8
            ";
        $stmt = $this->container->db->prepare($sql);
        $stmt->execute($values);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "message" => $stmt->errorInfo()
                // "message" => "更新失敗!"
            ];
        }
        return [
            "status" => "success",
            "message" => "已將此校之年級升１年級（六年級則升畢年級）"
        ];
    }

    public function getExamDate()
    {
        $examdateall = array(
            ['examdate' => "上"],
            ['examdate' => "下"]
        );
        $examdate_arr = array();
        foreach ($examdateall as $id => $data_row) {
            array_push($examdate_arr, $data_row['examdate']);
        }
        $result = $examdate_arr;
        return $result;
    }

    public function getTerm()
    {
        $examdateall = array(
            ['term' => "上"],
            ['term' => "下"]
        );
        $examdate_arr = array();
        foreach ($examdateall as $id => $data_row) {
            array_push($examdate_arr, $data_row['term']);
        }
        $result = $examdate_arr;
        return $result;
    }

    public function getSemster()
    {
        $semsterall = array(
            ['semster' => "期初"],
            ['semster' => "期末"]
        );
        $semster_arr = array();
        foreach ($semsterall as $id => $data_row) {
            array_push($semster_arr, $data_row['semster']);
        }
        $result = $semster_arr;
        return $result;
    }

    public function getClass()
    {
        $classall = array(
            ['class' => "測試用班"],
            ['class' => "1"],
            ['class' => "2"],
            ['class' => "3"],
            ['class' => "4"],
            ['class' => "5"],
            ['class' => "6"],
            ['class' => "7"],
            ['class' => "8"],
            ['class' => "9"],
            ['class' => "10"],
            ['class' => "11"],
            ['class' => "12"],
            ['class' => "13"],
            ['class' => "14"],
            ['class' => "15"],
            ['class' => "16"],
            ['class' => "17"],
            ['class' => "18"],
            ['class' => "19"],
            ['class' => "20"],
            ['class' => "資優班"],
            ['class' => "資源班"],
            ['class' => "補救教學班"],
            ['class' => "音樂班"],
            ['class' => "體育班"],
            ['class' => "其他"]

        );
        $class_arr = array();
        foreach ($classall as $id => $data_row) {
            array_push($class_arr, $data_row['class']);
        }
        $result = $class_arr;
        return $result;
    }

    public function getYear()
    {
        $yearall = array(
            ['year' => "100"],
            ['year' => "101"],
            ['year' => "102"],
            ['year' => "103"],
            ['year' => "104"],
            ['year' => "105"],
            ['year' => "106"],
            ['year' => "107"],
            ['year' => "108"],
            ['year' => "109"],
            ['year' => "110"],
            ['year' => "111"],
            ['year' => "112"]
        );
        $year_arr = array();
        foreach ($yearall as $id => $data_row) {
            array_push($year_arr, $data_row['year']);
        }
        $result = $year_arr;
        return $result;
    }

    public function getGrade()
    {
        $gradeall = array(
            ['grade' => "一"],
            ['grade' => "二"],
            ['grade' => "三"],
            ['grade' => "四"],
            ['grade' => "五"],
            ['grade' => "六"],
            ['grade' => "畢"],
        );
        $grade_arr = array();
        foreach ($gradeall as $id => $data_row) {
            array_push($grade_arr, $data_row['grade']);
        }
        $result = $grade_arr;
        return $result;
    }

    public function get_new_class($data)
    {
        $values = [
            "sid" => ''
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        // foreach ($values as $key => $value) {
        //     if (array_key_exists($key, $data)) {
        //         $values[$key] = $data[$key];
        //     }
        // }

        $sql = "SELECT [Tid], [Grade] + '年' + [Class] + '班' + ':' + [TeacherName] AS [TeacherClass]
                FROM [Literacy].[dbo].[Teacher]
                WHERE [Sid] = :sid
                ORDER BY [Grade], [Class]
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function patch_admin_school_teacher_msg($data)
    {
        $values = [
            "tid" => '',
            "grade" => '',
            "class" => '',
            "teachername" => '',
            "teachermail" => '',
            "passwd" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
            if ($values['grade'] == "畢") {
                $values['graduationyear'] = date("Y") - 1911;
                $string = ", [GraduationYear] = :graduationyear";
            }
        }

        $sql = "Update [Teacher] Set 
                [Grade] = :grade
                , [Class] = :class
                , [TeacherName] = :teachername
                , [TeacherMail] = :teachermail
                , [PassWD] = :passwd
                , [PassWD_ChangeDate] = GETDATE()
                {$string}
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

    public function getClassGender($data)
    {
        $values = [
            "exam_sid" => '',
            "exam_year" => '',
            "exam_term" => '',
            "exam_tkind" => '',
            "grade" => '',
            "class" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
                $values["{$key}1"] = $data[$key];
                $values["{$key}2"] = $data[$key];
                if ($key === "exam_sid") {
                    $values[$key] = intval($data[$key]);
                    $values["{$key}1"] = intval($data[$key]);
                    $values["{$key}2"] = intval($data[$key]);
                }
            }
            if (array_key_exists('grade', $data)) {
                $values['grade3'] = $values['grade'];
            }
            if (array_key_exists('class', $data)) {
                $values['class3'] = $values['class'];
            }
        }
        // var_dump($values);
        // exit(0);

        $sql = "SELECT Grade, Class, SUM(StuNum) StuNum
                , SUM(LiteracyScore_Max) LiteracyScore_Max, SUM(LiteracyScore_Min) LiteracyScore_Min
                , SUM(LiteracyScore_AVG) LiteracyScore_AVG, SUM(LiteracyScore_STDEV) LiteracyScore_STDEV
                , Sex
                FROM(
                    SELECT [Literacy].[dbo].[Exam_Word_Score].Exam_Grade AS Grade
                    , [Literacy].[dbo].[Exam_Word_Score].Exam_Class AS Class
                    , COUNT(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].Sid)) AS StuNum
                    , MAX(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_Max
                    , MIN(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_Min
                    , AVG(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_AVG
                    , STDEV(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_STDEV
                    , ([Literacy].[dbo].[Student].Sex) AS Sex
                    FROM [Literacy].[dbo].[Exam_Word_Score]
                    INNER JOIN [Literacy].[dbo].[Teacher] ON [Literacy].[dbo].[Exam_Word_Score].Tid = [Literacy].[dbo].[Teacher].Tid
                    INNER JOIN [Literacy].[dbo].[Student] ON [Literacy].[dbo].[Exam_Word_Score].Pid = [Literacy].[dbo].[Student].Pid
                    GROUP BY [Literacy].[dbo].[Exam_Word_Score].Exam_Grade, [Literacy].[dbo].[Exam_Word_Score].Exam_Class
                        , [Literacy].[dbo].[Exam_Word_Score].Exam_Sid, [Literacy].[dbo].[Exam_Word_Score].Exam_Year
                        , [Literacy].[dbo].[Exam_Word_Score].Exam_Term, [Literacy].[dbo].[Exam_Word_Score].Exam_TKind
                        , [Literacy].[dbo].[Student].Sex
                    HAVING [Literacy].[dbo].[Exam_Word_Score].Exam_Sid = :exam_sid
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Year = :exam_year
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Term = :exam_term
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = :exam_tkind
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Grade = :grade
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Class = :class  
                    AND [Literacy].[dbo].[Student].Sex = '男'
                            
                    UNION ALL
                
                    SELECT [Literacy].[dbo].[Exam_Word_Score].Exam_Grade AS Grade
                    , [Literacy].[dbo].[Exam_Word_Score].Exam_Class AS Class
                    , COUNT(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].Sid)) AS StuNum
                    , MAX(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_Max
                    , MIN(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_Min
                    , AVG(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_AVG
                    , STDEV(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_STDEV
                    , ([Literacy].[dbo].[Student].Sex) AS Sex
                    FROM [Literacy].[dbo].[Exam_Word_Score]
                    INNER JOIN [Literacy].[dbo].[Teacher] ON [Literacy].[dbo].[Exam_Word_Score].Tid = [Literacy].[dbo].[Teacher].Tid
                    INNER JOIN [Literacy].[dbo].[Student] ON [Literacy].[dbo].[Exam_Word_Score].Pid = [Literacy].[dbo].[Student].Pid
                    GROUP BY [Literacy].[dbo].[Exam_Word_Score].Exam_Grade, [Literacy].[dbo].[Exam_Word_Score].Exam_Class
                    , [Literacy].[dbo].[Exam_Word_Score].Exam_Sid, [Literacy].[dbo].[Exam_Word_Score].Exam_Year
                    , [Literacy].[dbo].[Exam_Word_Score].Exam_Term, [Literacy].[dbo].[Exam_Word_Score].Exam_TKind
                    , [Literacy].[dbo].[Student].Sex
                    
                    HAVING [Literacy].[dbo].[Exam_Word_Score].Exam_Sid = :exam_sid1
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Year = :exam_year1
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Term = :exam_term1
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = :exam_tkind1
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Grade = :grade1
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Class = :class1
                    AND [Literacy].[dbo].[Student].Sex = '女'
                            
                    UNION ALL
                
                    SELECT [Literacy].[dbo].[Exam_Word_Score].Exam_Grade AS Grade
                    , [Literacy].[dbo].[Exam_Word_Score].Exam_Class AS Class
                    , COUNT(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].Sid)) AS StuNum
                    , MAX(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_Max
                    , MIN(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_Min
                    , AVG(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_AVG
                    , STDEV(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_STDEV
                    , '全體'
                    FROM [Literacy].[dbo].[Exam_Word_Score]
                    INNER JOIN [Literacy].[dbo].[Teacher] ON [Literacy].[dbo].[Exam_Word_Score].Tid = [Literacy].[dbo].[Teacher].Tid
                    INNER JOIN [Literacy].[dbo].[Student] ON [Literacy].[dbo].[Exam_Word_Score].Pid = [Literacy].[dbo].[Student].Pid
                    GROUP BY [Literacy].[dbo].[Exam_Word_Score].Exam_Grade, [Literacy].[dbo].[Exam_Word_Score].Exam_Class
                        , [Literacy].[dbo].[Exam_Word_Score].Exam_Sid, [Literacy].[dbo].[Exam_Word_Score].Exam_Year
                        , [Literacy].[dbo].[Exam_Word_Score].Exam_Term, [Literacy].[dbo].[Exam_Word_Score].Exam_TKind
                    
                    HAVING [Literacy].[dbo].[Exam_Word_Score].Exam_Sid = :exam_sid2
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Year = :exam_year2
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Term = :exam_term2
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = :exam_tkind2
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Grade = :grade2
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Class = :class2 
                    UNION ALL(
                        SELECT *
                        FROM (
                            SELECT :grade3 Grade
                        )dt
                        CROSS JOIN(
                            SELECT :class3 Class
                        )dd
                        CROSS JOIN(
                            SELECT 0 AS StuNum
                            , 0 AS LiteracyScore_Max
                            , 0 AS LiteracyScore_Min
                            , 0 AS LiteracyScore_AVG
                            , 0 AS LiteracyScore_STDEV
                        )df
                        CROSS JOIN(
                            SELECT '男' Sex
                            UNION ALL(
                                SELECT '女' Sex
                            )
                            UNION ALL(
                                SELECT '全體' Sex
                            )
                        )dg
                    )
                )db
                GROUP BY Grade, Class, Sex  
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_exam_report3($data)
    {
        $values = [
            "sid" => '',
            "exam_sid" => '',
            "exam_year" => '',
            "exam_term" => '',
            "exam_tkind" => '',
            "grade" => '',
            "class" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT * 
                , ( 
                    CASE 
                        WHEN [View_ScoreList].[ExamProgramKind] in ('A2','A3','A4','A5','A6','A7','A8','A09','A10') THEN 
                        CASE 
                            WHEN [View_ScoreList].[LiteracyScore] <= 730 THEN '01' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 731 AND [View_ScoreList].[LiteracyScore] <= 1210 THEN '02' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 1211 AND [View_ScoreList].[LiteracyScore] <= 1300 THEN '03' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 1301 AND [View_ScoreList].[LiteracyScore] <= 1560 THEN '04' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 1561 AND [View_ScoreList].[LiteracyScore] <= 1870 THEN '05' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 1871 AND [View_ScoreList].[LiteracyScore] <= 2290 THEN '0607' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 2291 AND [View_ScoreList].[LiteracyScore] <= 2490 THEN '08' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 2491 AND [View_ScoreList].[LiteracyScore] <= 2760 THEN '09' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 2761 AND [View_ScoreList].[LiteracyScore] <= 2840 THEN '10' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 2841 AND [View_ScoreList].[LiteracyScore] <= 3040 THEN '11' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 3041 THEN '12' 
                            ELSE NULL END 
                            ELSE CASE 
                                WHEN [View_ScoreList].[LiteracyScore] <= 499 THEN '01' 
                                WHEN [View_ScoreList].[LiteracyScore] >= 500 AND [View_ScoreList].[LiteracyScore] <= 899 THEN '02' 
                                WHEN [View_ScoreList].[LiteracyScore] >= 900 AND [View_ScoreList].[LiteracyScore] <= 1129 THEN '03' 
                                WHEN [View_ScoreList].[LiteracyScore] >= 1130 AND [View_ScoreList].[LiteracyScore] <= 1449 THEN '04' 
                                WHEN [View_ScoreList].[LiteracyScore] >= 1450 AND [View_ScoreList].[LiteracyScore] <= 1999 THEN '05' 
                                WHEN [View_ScoreList].[LiteracyScore] >= 2000 AND [View_ScoreList].[LiteracyScore] <= 2249 THEN '06' 
                                WHEN [View_ScoreList].[LiteracyScore] >= 2250 AND [View_ScoreList].[LiteracyScore] <= 2599 THEN '07' 
                                WHEN [View_ScoreList].[LiteracyScore] >= 2600 AND [View_ScoreList].[LiteracyScore] <= 2899 THEN '08' 
                                WHEN [View_ScoreList].[LiteracyScore] >= 2900 AND [View_ScoreList].[LiteracyScore] <= 3099 THEN '09' 
                                WHEN [View_ScoreList].[LiteracyScore] >= 3100 AND [View_ScoreList].[LiteracyScore] <= 3299 THEN '10' 
                                WHEN [View_ScoreList].[LiteracyScore] >= 3300 AND [View_ScoreList].[LiteracyScore] <= 3499 THEN '11' 
                                WHEN [View_ScoreList].[LiteracyScore] >= 3500 THEN '12' 
                                ELSE NULL 
                                END 
                            END 
                        )AS ColorNum
                , ( 
                    CASE 
                        WHEN [View_ScoreList].[ExamProgramKind] in ('A2','A3','A4','A5','A6','A7','A8','A09','A10') THEN 
                        CASE 
                            WHEN [View_ScoreList].[LiteracyScore] <= 731 THEN '白色' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 731 AND [View_ScoreList].[LiteracyScore] <= 1210 THEN '黑色'
                            WHEN [View_ScoreList].[LiteracyScore] >= 1211 AND [View_ScoreList].[LiteracyScore] <= 1300 THEN '紅色'
                            WHEN [View_ScoreList].[LiteracyScore] >= 1301 AND [View_ScoreList].[LiteracyScore] <= 1560 THEN '橙色' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 1561 AND [View_ScoreList].[LiteracyScore] <= 1870 THEN '黃色' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 1871 AND [View_ScoreList].[LiteracyScore] <= 2290 THEN '綠藍色'
                            WHEN [View_ScoreList].[LiteracyScore] >= 2291 AND [View_ScoreList].[LiteracyScore] <= 2490 THEN '靛色'
                            WHEN [View_ScoreList].[LiteracyScore] >= 2491 AND [View_ScoreList].[LiteracyScore] <= 2760 THEN '紫色'
                            WHEN [View_ScoreList].[LiteracyScore] >= 2761 AND [View_ScoreList].[LiteracyScore] <= 2840 THEN '銅色' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 2841 AND [View_ScoreList].[LiteracyScore] <= 3040 THEN '銀色' 
                            WHEN [View_ScoreList].[LiteracyScore] >= 3041 THEN '金色' 
                            ELSE 
                                CASE ColorNum
                                    WHEN '06' THEN '綠色'
                                    WHEN '07' THEN '藍色' 
                                    END 
                            END
                        END
                        ) AS ColorNumName
                FROM View_ScoreList
                WHERE [School_Sid] = :sid
                AND [Exam_Year] = :exam_year
                AND [Exam_Term] = :exam_term
                AND [Exam_TKind] = :exam_tkind
                AND [Exam_TKind] = :exam_tkind
                AND [Grade] = :grade
                AND [Class] = :class
                ORDER BY [StuNum], [SeatNum]
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getSchoolclassAmount($data)
    {
        $values = [
            "exam_sid" => '',
            "exam_year" => '',
            "exam_term" => '',
            "exam_tkind" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
                $values["{$key}1"] = $data[$key];
                $values["{$key}2"] = $data[$key];
                if ($key === "exam_sid") {
                    $values[$key] = intval($data[$key]);
                    $values["{$key}1"] = intval($data[$key]);
                    $values["{$key}2"] = intval($data[$key]);
                }
            }
        }

        $sql = "SELECT Grade, SUM(StuNum) StuNum
                , SUM(LiteracyScore_Max) LiteracyScore_Max, SUM(LiteracyScore_Min) LiteracyScore_Min
                , SUM(LiteracyScore_AVG) LiteracyScore_AVG, SUM(LiteracyScore_STDEV) LiteracyScore_STDEV
                , Sex
                FROM(
                    SELECT [Literacy].[dbo].[Exam_Word_Score].Exam_Grade AS Grade
                    , COUNT(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].Sid)) AS StuNum
                    , MAX(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_Max
                    , MIN(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_Min
                    , AVG(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_AVG
                    , STDEV(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_STDEV
                    , ([Literacy].[dbo].[Student].Sex) AS Sex
                    FROM [Literacy].[dbo].[Exam_Word_Score]
                    INNER JOIN [Literacy].[dbo].[Teacher] ON [Literacy].[dbo].[Exam_Word_Score].Tid = [Literacy].[dbo].[Teacher].Tid
                    INNER JOIN [Literacy].[dbo].[Student] ON [Literacy].[dbo].[Exam_Word_Score].Pid = [Literacy].[dbo].[Student].Pid
                    GROUP BY [Literacy].[dbo].[Exam_Word_Score].Exam_Grade
                        , [Literacy].[dbo].[Exam_Word_Score].Exam_Sid, [Literacy].[dbo].[Exam_Word_Score].Exam_Year
                        , [Literacy].[dbo].[Exam_Word_Score].Exam_Term, [Literacy].[dbo].[Exam_Word_Score].Exam_TKind
                        , [Literacy].[dbo].[Student].Sex
                    HAVING [Literacy].[dbo].[Exam_Word_Score].Exam_Sid = :exam_sid
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Year = :exam_year
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Term = :exam_term
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = :exam_tkind
                    AND [Literacy].[dbo].[Student].Sex = '男'
                            
                    UNION ALL
                
                    SELECT [Literacy].[dbo].[Exam_Word_Score].Exam_Grade AS Grade
                    , COUNT(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].Sid)) AS StuNum
                    , MAX(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_Max
                    , MIN(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_Min
                    , AVG(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_AVG
                    , STDEV(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_STDEV
                    , ([Literacy].[dbo].[Student].Sex) AS Sex
                    FROM [Literacy].[dbo].[Exam_Word_Score]
                    INNER JOIN [Literacy].[dbo].[Teacher] ON [Literacy].[dbo].[Exam_Word_Score].Tid = [Literacy].[dbo].[Teacher].Tid
                    INNER JOIN [Literacy].[dbo].[Student] ON [Literacy].[dbo].[Exam_Word_Score].Pid = [Literacy].[dbo].[Student].Pid
                    GROUP BY [Literacy].[dbo].[Exam_Word_Score].Exam_Grade
                    , [Literacy].[dbo].[Exam_Word_Score].Exam_Sid, [Literacy].[dbo].[Exam_Word_Score].Exam_Year
                    , [Literacy].[dbo].[Exam_Word_Score].Exam_Term, [Literacy].[dbo].[Exam_Word_Score].Exam_TKind
                    , [Literacy].[dbo].[Student].Sex
                    
                    HAVING [Literacy].[dbo].[Exam_Word_Score].Exam_Sid = :exam_sid1
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Year = :exam_year1
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Term = :exam_term1
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = :exam_tkind1
                    AND [Literacy].[dbo].[Student].Sex = '女'
                            
                    UNION ALL
                
                    SELECT [Literacy].[dbo].[Exam_Word_Score].Exam_Grade AS Grade
                    , COUNT(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].Sid)) AS StuNum
                    , MAX(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_Max
                    , MIN(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_Min
                    , AVG(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_AVG
                    , STDEV(CONVERT(int, [Literacy].[dbo].[Exam_Word_Score].LiteracyScore)) AS LiteracyScore_STDEV
                    , '全體'
                    FROM [Literacy].[dbo].[Exam_Word_Score]
                    INNER JOIN [Literacy].[dbo].[Teacher] ON [Literacy].[dbo].[Exam_Word_Score].Tid = [Literacy].[dbo].[Teacher].Tid
                    INNER JOIN [Literacy].[dbo].[Student] ON [Literacy].[dbo].[Exam_Word_Score].Pid = [Literacy].[dbo].[Student].Pid
                    GROUP BY [Literacy].[dbo].[Exam_Word_Score].Exam_Grade
                        , [Literacy].[dbo].[Exam_Word_Score].Exam_Sid, [Literacy].[dbo].[Exam_Word_Score].Exam_Year
                        , [Literacy].[dbo].[Exam_Word_Score].Exam_Term, [Literacy].[dbo].[Exam_Word_Score].Exam_TKind
                    
                    HAVING [Literacy].[dbo].[Exam_Word_Score].Exam_Sid = :exam_sid2
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Year = :exam_year2
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Term = :exam_term2
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = :exam_tkind2
                    UNION ALL(
                        SELECT *
                        FROM (
                            SELECT '一' Grade
                            UNION ALL(
                                SELECT '二' Grade
                            )
                            UNION ALL(
                                SELECT '三' Grade
                            )
                            UNION ALL(
                                SELECT '四' Grade
                            )
                            UNION ALL(
                                SELECT '五' Grade
                            )
                            UNION ALL(
                                SELECT '六' Grade
                            )
                        )dt
                        CROSS JOIN(
                            SELECT 0 AS StuNum
                            , 0 AS LiteracyScore_Max
                            , 0 AS LiteracyScore_Min
                            , 0 AS LiteracyScore_AVG
                            , 0 AS LiteracyScore_STDEV
                        )df
                        CROSS JOIN(
                            SELECT '男' Sex
                            UNION ALL(
                                SELECT '女' Sex
                            )
                            UNION ALL(
                                SELECT '全體' Sex
                            )
                        )dg
                    )
                )db
                GROUP BY [Grade], [Sex]  
                ORDER BY CASE [Grade]
                            WHEN '一' THEN 1
                            WHEN '二' THEN 2
                            WHEN '三' THEN 3
                            WHEN '四' THEN 4
                            WHEN '五' THEN 5
                            WHEN '六' THEN 6
                            ELSE 99999 END
                    ,CASE [Sex] 
                        WHEN '男' THEN 1
                        WHEN '女' THEN 2
                        ELSE 99999 END
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getSchoolAmountReport($data)
    {
        // 國小
        $values = [
            "exam_sid" => '',
            "exam_year" => '',
            "exam_term" => '',
            "exam_tkind" => '',
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $string = "COUNT( CASE WHEN [Exam_Word_Score].[Exam_Grade] = ";
        $score = " AND [Exam_Word_Score].LiteracyScore >= ";
        $then = " THEN 1 END ";

        if ($values['exam_term'] === '上') {
            $values['stardard1'] = 0;
            $values['stardard2'] = 1211;
            $values['stardard3'] = 1561;
            $values['stardard4'] = 1871;
            $values['stardard5'] = 2491;
            $values['stardard6'] = 2841;
        } else if ($values['exam_term'] === '下') {
            $values['stardard1'] = 731;
            $values['stardard2'] = 1301;
            $values['stardard3'] = 1871;
            $values['stardard4'] = 2291;
            $values['stardard5'] = 2761;
            $values['stardard6'] = 3041;
        }

        $sql = "WITH 
                db AS 
                ( 
                    SELECT 
                     {$string}'一'{$score}CONVERT(int, :stardard1) {$then}) 'yes1'
                    ,{$string}'二'{$score}CONVERT(int, :stardard2) {$then}) 'yes2'
                    ,{$string}'三'{$score}CONVERT(int, :stardard3) {$then}) 'yes3'
                    ,{$string}'四'{$score}CONVERT(int, :stardard4) {$then}) 'yes4'
                    ,{$string}'五'{$score}CONVERT(int, :stardard5) {$then}) 'yes5'
                    ,{$string}'六'{$score}CONVERT(int, :stardard6) {$then}) 'yes6'
                    
                    ,{$string}'一' {$then}) 'a'
                    ,{$string}'二' {$then}) 'b'
                    ,{$string}'三' {$then}) 'c'
                    ,{$string}'四' {$then}) 'd'
                    ,{$string}'五' {$then}) 'e'
                    ,{$string}'六' {$then}) 'f'

                    FROM [Literacy].[dbo].[Exam_Word_Score] 
                    INNER JOIN [Literacy].[dbo].[Teacher] ON [Literacy].[dbo].[Exam_Word_Score].Tid = [Literacy].[dbo].[Teacher].Tid 
                    AND [Literacy].[dbo].[Teacher].Class <> '測試用班'
                    GROUP BY [Exam_Word_Score].Exam_Sid, [Exam_Word_Score].Exam_Year
                    , [Exam_Word_Score].Exam_Term, [Exam_Word_Score].Exam_TKind
                    HAVING [Literacy].[dbo].[Exam_Word_Score].Exam_Sid = :exam_sid
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Year = :exam_year
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_Term = :exam_term
                    AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = :exam_tkind
                )
                
                SELECT *
                FROM (
                    SELECT PROJECT, One, Two, Three, Four, Five, Six
                    FROM (
                        SELECT '應填答人數(A)' PROJECT
                        UNION ALL(
                            SELECT '未填答人數(B)' PROJECT
                        )
                        UNION ALL(
                            SELECT '施測率百分比= (C) / (A)' PROJECT
                        )
                    )dt
                    CROSS JOIN(
                        SELECT ' ' AS ONE
                        , ' ' AS TWO
                        , ' ' AS THREE
                        , ' ' AS FOUR
                        , ' ' AS FIVE
                        , ' ' AS SIX
                        )df
                    UNION ALL
                
                    SELECT PROJECT
                        , CAST(SUM(One) AS varchar) One, CAST(SUM(Two) AS varchar) Two
                        , CAST(SUM(Three) AS varchar) Three
                        , CAST(SUM(Four) AS varchar) Four, CAST(SUM(Five) AS varchar) Five
                        , CAST(SUM(Six) AS varchar) Six
                    FROM (
                        SELECT '實際填答人數(C)' AS PROJECT
                        ,a One
                        ,b Two
                        ,c Three
                        ,d Four
                        ,e Five
                        ,f Six
                    FROM db
                    UNION ALL 
                    SELECT '未達標人數(E)' AS PROJECT
                    ,(a - yes1) One
                    ,(b - yes2) Two
                    ,(c - yes3) Three
                    ,(d - yes4) Four
                    ,(e - yes5) Five
                    ,(f - yes6) Six
                    FROM db
                    UNION ALL 
                    SELECT '達標人數(D)' AS PROJECT
                    ,yes1 One
                    ,yes2 Two
                    ,yes3 Three
                    ,yes4 Four
                    ,yes5 Five
                    ,yes6 Six
                    FROM db
                    UNION ALL 
                    SELECT '達標率百分比(F) = (D) / (C)' AS PROJECT
                    ,(CASE WHEN a = 0 THEN 0 ELSE yes1 / a END) One
                    ,(CASE WHEN b = 0 THEN 0 ELSE yes2 / b END) Two
                    ,(CASE WHEN c = 0 THEN 0 ELSE yes3 / c END) Three
                    ,(CASE WHEN d = 0 THEN 0 ELSE yes4 / d END) Four
                    ,(CASE WHEN e = 0 THEN 0 ELSE yes5 / e END) Five
                    ,(CASE WHEN f = 0 THEN 0 ELSE yes6 / f END) Six
                    FROM db 
                            
                    UNION ALL(
                    SELECT *
                    FROM (
                        SELECT '實際填答人數(C)' PROJECT
                        UNION ALL(
                        SELECT '未達標人數(E)' PROJECT
                        )
                        UNION ALL(
                        SELECT '達標人數(D)' PROJECT
                        )
                        UNION ALL(
                        SELECT '達標率百分比(F) = (D) / (C)' PROJECT
                        )
                    )dt
                    CROSS JOIN(
                        SELECT '0' AS One
                        , '0' AS Two
                        , '0' AS Three
                        , '0' AS Four
                        , '0' AS Five
                        , '0' AS Six
                    )df
                )
                )dg
                GROUP BY dg.PROJECT
                )dh
                GROUP BY dh.PROJECT, dh.One, dh.TwO, dh.Three, dh.Four, dh.Five, dh.Six
                ORDER BY CASE PROJECT 
                WHEN '應填答人數(A)' THEN 1
                WHEN '未填答人數(B)' THEN 2
                WHEN '實際填答人數(C)' THEN 3
                WHEN '施測率百分比= (C) / (A)' THEN 4
                WHEN '達標人數(D)' THEN 5
                WHEN '未達標人數(E)' THEN 6
                WHEN '達標率百分比(F) = (D) / (C)' THEN 7
                ELSE 9999 END
            ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


    public function getSingleClassAmount($data)
    {
        $values = [
            "tid" => '',
            "admin" => true
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $stmt_string = [
            "select" => ",(
                    CASE [Literacy].[dbo].[View_Score_Class_New].[ExamProgramKind]
                        WHEN 'A' THEN '能力適性版(A)' 
                        WHEN 'A2' THEN '能力適性版(A2)'
                        WHEN 'A3' THEN '能力適性版(A3)'
                        WHEN 'A4' THEN '能力適性版(A4)'
                        WHEN 'A5' THEN '能力適性版(A5)'
                        WHEN 'A6' THEN '能力適性版(A6)'
                        WHEN 'A7' THEN '能力適性版(A7)'
                        WHEN 'A8' THEN '能力適性版(A8)'
                        WHEN 'A09' THEN '能力適性版(A09)'
                        WHEN 'A10' THEN '能力適性版(A10)'
                        WHEN 'B' THEN '年級適性版(B)'
                        WHEN 'C' THEN '年級版(C)'
                        WHEN 'D' THEN '全部施測版(D)'
                        ELSE '999999' END
                    ) AS ExamProgramKindName
                ,(
                    CASE [Literacy].[dbo].[View_Score_Class_New].[ColorNum]
                        WHEN '01' THEN '白色' 
                        WHEN '02' THEN '黑色'
                        WHEN '03' THEN '紅色'
                        WHEN '04' THEN '橙色'
                        WHEN '05' THEN '黃色'
                        WHEN '06' THEN '綠色'
                        WHEN '0607' THEN '綠藍色'
                        WHEN '07' THEN '藍色'
                        WHEN '08' THEN '靛色'
                        WHEN '09' THEN '紫色'
                        WHEN '10' THEN '銅色'
                        WHEN '11' THEN '銀色'
                        WHEN '12' THEN '金色'
                        ELSE '999999' END
                    ) AS ColorNumName",
            "order" => ""
        ];
        if (!$values['admin']) {
            $stmt_string = [
                "select" => "",
                "order" => " ORDER BY CAST(SeatNum AS int)"
            ];
        }
        unset($values['admin']);

        $sql = "SELECT *
                {$stmt_string['select']}
                FROM [Literacy].[dbo].[View_Score_Class_New]
                WHERE [Tid] = :tid
                {$stmt_string['order']}
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getSingleClass($data)
    {
        $values = [
            "tid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Grade], [Class]
                FROM [Literacy].[dbo].[Teacher]
                WHERE [Tid] = :tid
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getExamReport($data)
    {
        $values = [
            "exam_year" => '110',
            "exam_term" => '下',
            "exam_tkind" => '期初'
        ];
        $string = "WHERE";
        $check = false;
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('cityid', $data)) {
            $string .= " [Literacy].[dbo].[SchoolList].CityId = :cityid AND";
            $values['cityid'] = $data['cityid'];
            $check = true;
        }
        if (array_key_exists('postid', $data)) {
            $string .= " [Literacy].[dbo].[SchoolList].PostId = :postid AND";
            $values['postid'] = $data['postid'];
            $check = true;
        }
        if (array_key_exists('schoolname', $data)) {
            $string .= " [Literacy].[dbo].[SchoolList].SchoolName = :schoolname AND";
            $values['schoolname'] = $data['schoolname'];
            $check = true;
        }
        if ($check) {
            $string = rtrim($string, "AND");
        } else {
            $string = ltrim($string, "WHERE");
        }


        $sql = "SELECT [Literacy].[dbo].[SchoolList].CityId, [Literacy].[dbo].[SchoolList].PostId
                , [Literacy].[dbo].[SchoolList].SchoolID, [Literacy].[dbo].[SchoolList].SchoolName
                , [Literacy].[dbo].[Teacher].Grade, [Literacy].[dbo].[Teacher].Class
		        , COUNT(*) AS StuNum, COUNT([Literacy].[dbo].[Exam_Word_Score].Pid) AS ExamNum
		        FROM [Literacy].[dbo].[SchoolList] 
                INNER JOIN [Literacy].[dbo].[Teacher] ON [Literacy].[dbo].[SchoolList].Sid = [Literacy].[dbo].[Teacher].Sid 
                INNER JOIN [Literacy].[dbo].[Student] ON [Literacy].[dbo].[Teacher].Tid = [Literacy].[dbo].[Student].Tid 
                LEFT OUTER JOIN [Literacy].[dbo].[Exam_Word_Score] 
                ON [Literacy].[dbo].[Student].Pid = [Literacy].[dbo].[Exam_Word_Score].Pid
                AND [Literacy].[dbo].[Exam_Word_Score].Exam_Year = :exam_year
                AND [Literacy].[dbo].[Exam_Word_Score].Exam_Term = :exam_term
                AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = :exam_tkind
                {$string}
                GROUP BY [Literacy].[dbo].[SchoolList].CityId, [Literacy].[dbo].[SchoolList].PostId
                , [Literacy].[dbo].[SchoolList].SchoolID, [Literacy].[dbo].[SchoolList].SchoolName
                , [Literacy].[dbo].[Teacher].Grade, [Literacy].[dbo].[Teacher].Class
		        ORDER BY [Literacy].[dbo].[SchoolList].CityId
                , [Literacy].[dbo].[SchoolList].PostId, [Literacy].[dbo].[SchoolList].SchoolID
                , CASE [Literacy].[dbo].[Teacher].Grade 
                        WHEN '一' THEN 1 
                        WHEN '二' THEN 2
                        WHEN '三' THEN 3
                        WHEN '四' THEN 4
                        WHEN '五' THEN 5
                        WHEN '六' THEN 6
                        WHEN '畢' THEN 7
                        ELSE 999999 END 
                , [Literacy].[dbo].[Teacher].Class
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function delete_exam($data)
    {
        $values = [
            "Sid" => []
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $stmt_string = implode(",", array_map(function ($index) {
            return ":Sid_{$index}";
        }, array_keys($values['Sid'])));
        $stmt_array = array_reduce(
            array_map(
                function ($value, $index) {
                    return ["Sid_{$index}" => $value];
                },
                $values['Sid'],
                array_keys($values['Sid'])
            ),
            function ($oll, $tmp) {
                return array_merge($oll, $tmp);
            },
            []
        );
        if (count($values['Sid']) !== 0) {
            $sql = "DELETE FROM [Exam_Word_Score] 
                    WHERE CONVERT(nvarchar, [Exam_Word_Score].[Sid]) 
                    IN ($stmt_string)
            ";

            $sth = $this->container->db->prepare($sql);
            if ($sth->execute($stmt_array))
                return [
                    "status" => "success"
                ];

            return [
                "status" => "failure",
                "info" => $sth->errorInfo()
            ];
        }
        return [
            "status" => "failure"
        ];
    }

    public function getExamResult($data)
    {
        $values = [
            "cur_page" => 1,
            "size" => 10
        ];

        $string = "WHERE";
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

        if (array_key_exists('cityid', $data)) {
            $string .= " [CityId] = :cityid AND";
            $values['cityid'] = $data['cityid'];
            $check = true;
        }
        if (array_key_exists('postid', $data)) {
            $string .= " [PostId] = :postid AND";
            $values['postid'] = $data['postid'];
            $check = true;
        }
        if (array_key_exists('schoolname', $data)) {
            $string .= " [SchoolName] = :schoolname AND";
            $values['schoolname'] = $data['schoolname'];
            $check = true;
        }
        if (array_key_exists('title', $data)) {
            $string .= " [Title] = :title AND";
            $values['title'] = $data['title'];
            $check = true;
        }
        if (array_key_exists('stuname', $data)) {
            $string .= " [StuName] LIKE '%' + :stuname + '%' AND";
            $values['stuname'] = $data['stuname'];
            $check = true;
        }

        if (array_key_exists('test_version', $data)) {
            $string .= " [ExamProgramKind] like '%'+:test_version+'%' AND";
            $values['test_version'] = $values['test_version'];
            $check = true;
        }

        if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            $string .= " (CONVERT(char, [AddTime], 111) Between :start_date AND :end_date AND";
            $values['start_date'] = $data['start_date'];
            $values['end_date'] = $data['end_date'];
            $check = true;
        }

        if ($check) {
            $string = rtrim($string, "AND");
        } else {
            $string = ltrim($string, "WHERE");
        }

        // ,(
        //     CASE [Literacy].[dbo].[View_ScoreList].[ExamProgramKind]
        //         WHEN 'A' THEN '能力適性版(A)' 
        //         WHEN 'A2' THEN '能力適性版(A2)'
        //         WHEN 'A3' THEN '能力適性版(A3)'
        //         WHEN 'A4' THEN '能力適性版(A4)'
        //         WHEN 'A5' THEN '能力適性版(A5)'
        //         WHEN 'A6' THEN '能力適性版(A6)'
        //         WHEN 'A7' THEN '能力適性版(A7)'
        //         WHEN 'A8' THEN '能力適性版(A8)'
        //         WHEN 'A09' THEN '能力適性版(A09)'
        //         WHEN 'A10' THEN '能力適性版(A10)'
        //         WHEN 'B' THEN '年級適性版(B)'
        //         WHEN 'C' THEN '年級版(C)'
        //         ELSE '999999' END
        //     ) AS ExamProgramKindName

        $sql = "SELECT *
                FROM (
                    SELECT *,
                      ROW_NUMBER() OVER(ORDER BY [Sid]) AS 'key'
                    , ROW_NUMBER() OVER(ORDER BY [Sid]) AS row_num
                    ,(
                        CASE [Literacy].[dbo].[View_ScoreList].[ColorNum]
                            WHEN '01' THEN '白色' 
                            WHEN '02' THEN '黑色'
                            WHEN '03' THEN '紅色'
                            WHEN '04' THEN '橙色'
                            WHEN '05' THEN '黃色'
                            WHEN '06' THEN '綠色'
                            WHEN '0607' THEN '綠藍色'
                            WHEN '07' THEN '藍色'
                            WHEN '08' THEN '靛色'
                            WHEN '09' THEN '紫色'
                            WHEN '10' THEN '銅色'
                            WHEN '11' THEN '銀色'
                            WHEN '12' THEN '金色'
                            ELSE '999999' END
                        ) AS ColorNumName
                    FROM [Literacy].[dbo].[View_ScoreList] 
                    {$string}
                    ) AS selection
                    WHERE selection.row_num > :start AND selection.row_num <= :length
                    ORDER BY [Sid] DESC
           ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getExamResult_excel($data)
    {
        $values = [];
        $string = "WHERE";
        $check = false;
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('cityid', $data)) {
            $string .= " [CityId] = :cityid AND";
            $values['cityid'] = $data['cityid'];
            $check = true;
        }
        if (array_key_exists('postid', $data)) {
            $string .= " [PostId] = :postid AND";
            $values['postid'] = $data['postid'];
            $check = true;
        }
        if (array_key_exists('schoolname', $data)) {
            $string .= " [SchoolName] = :schoolname AND";
            $values['schoolname'] = $data['schoolname'];
            $check = true;
        }
        if (array_key_exists('title', $data)) {
            $string .= " [Title] = :title AND";
            $values['title'] = $data['title'];
            $check = true;
        }
        if (array_key_exists('stuname', $data)) {
            $string .= " [StuName] LIKE '%' + :stuname + '%' AND";
            $values['stuname'] = $data['stuname'];
            $check = true;
        }

        if (array_key_exists('test_version', $data)) {
            $string .= " [ExamProgramKind] like '%'+:test_version+'%' AND";
            $values['test_version'] = $values['test_version'];
            $check = true;
        }

        if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            $string .= " (CONVERT(char, [AddTime], 111) Between :start_date AND :end_date AND";
            $values['start_date'] = $data['start_date'];
            $values['end_date'] = $data['end_date'];
            $check = true;
        }

        if ($check) {
            $string = rtrim($string, "AND");
        } else {
            $string = ltrim($string, "WHERE");
        }

        // ,(
        //     CASE [Literacy].[dbo].[View_ScoreList].[ExamProgramKind]
        //         WHEN 'A' THEN '能力適性版(A)' 
        //         WHEN 'A2' THEN '能力適性版(A2)'
        //         WHEN 'A3' THEN '能力適性版(A3)'
        //         WHEN 'A4' THEN '能力適性版(A4)'
        //         WHEN 'A5' THEN '能力適性版(A5)'
        //         WHEN 'A6' THEN '能力適性版(A6)'
        //         WHEN 'A7' THEN '能力適性版(A7)'
        //         WHEN 'A8' THEN '能力適性版(A8)'
        //         WHEN 'A09' THEN '能力適性版(A09)'
        //         WHEN 'A10' THEN '能力適性版(A10)'
        //         WHEN 'B' THEN '年級適性版(B)'
        //         WHEN 'C' THEN '年級版(C)'
        //         ELSE '999999' END
        //     ) AS ExamProgramKindName

        $sql = "SELECT *
                FROM (
                    SELECT *,
                      ROW_NUMBER() OVER(ORDER BY [Sid]) AS 'key'
                    , ROW_NUMBER() OVER(ORDER BY [Sid]) AS row_num
                    ,(
                        CASE [Literacy].[dbo].[View_ScoreList].[ColorNum]
                            WHEN '01' THEN '白色' 
                            WHEN '02' THEN '黑色'
                            WHEN '03' THEN '紅色'
                            WHEN '04' THEN '橙色'
                            WHEN '05' THEN '黃色'
                            WHEN '06' THEN '綠色'
                            WHEN '0607' THEN '綠藍色'
                            WHEN '07' THEN '藍色'
                            WHEN '08' THEN '靛色'
                            WHEN '09' THEN '紫色'
                            WHEN '10' THEN '銅色'
                            WHEN '11' THEN '銀色'
                            WHEN '12' THEN '金色'
                            ELSE '999999' END
                        ) AS ColorNumName
                    FROM [Literacy].[dbo].[View_ScoreList] 
                    {$string}
                    ) AS selection
                    WHERE selection.row_num > :start AND selection.row_num <= :length
                    ORDER BY [Sid] DESC
           ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getExamResult_count($data)
    {
        $values = [];
        $string = "WHERE";
        $check = false;
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('cityid', $data)) {
            $string .= " [CityId] = :cityid AND";
            $values['cityid'] = $data['cityid'];
            $check = true;
        }
        if (array_key_exists('postid', $data)) {
            $string .= " [PostId] = :postid AND";
            $values['postid'] = $data['postid'];
            $check = true;
        }
        if (array_key_exists('schoolname', $data)) {
            $string .= " [SchoolName] = :schoolname AND";
            $values['schoolname'] = $data['schoolname'];
            $check = true;
        }
        if (array_key_exists('title', $data)) {
            $string .= " [Title] = :title AND";
            $values['title'] = $data['title'];
            $check = true;
        }
        if (array_key_exists('stuname', $data)) {
            $string .= " [StuName] LIKE '%' + :stuname + '%' AND";
            $values['stuname'] = $data['stuname'];
            $check = true;
        }
        if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            $string .= " (CONVERT(char, [AddTime], 111) Between :start_date AND :end_date AND";
            $values['start_date'] = $data['start_date'];
            $values['end_date'] = $data['end_date'];
            $check = true;
        }

        if ($check) {
            $string = rtrim($string, "AND");
        } else {
            $string = ltrim($string, "WHERE");
        }

        // ,(
        //     CASE [Literacy].[dbo].[View_ScoreList].[ExamProgramKind]
        //         WHEN 'A' THEN '能力適性版(A)' 
        //         WHEN 'A2' THEN '能力適性版(A2)'
        //         WHEN 'A3' THEN '能力適性版(A3)'
        //         WHEN 'A4' THEN '能力適性版(A4)'
        //         WHEN 'A5' THEN '能力適性版(A5)'
        //         WHEN 'A6' THEN '能力適性版(A6)'
        //         WHEN 'A7' THEN '能力適性版(A7)'
        //         WHEN 'A8' THEN '能力適性版(A8)'
        //         WHEN 'A09' THEN '能力適性版(A09)'
        //         WHEN 'A10' THEN '能力適性版(A10)'
        //         WHEN 'B' THEN '年級適性版(B)'
        //         WHEN 'C' THEN '年級版(C)'
        //         ELSE '999999' END
        //     ) AS ExamProgramKindName

        $sql = "SELECT COUNT(*) AS count_data
                FROM (
                    SELECT *
                    ,(
                        CASE [Literacy].[dbo].[View_ScoreList].[ColorNum]
                            WHEN '01' THEN '白色' 
                            WHEN '02' THEN '黑色'
                            WHEN '03' THEN '紅色'
                            WHEN '04' THEN '橙色'
                            WHEN '05' THEN '黃色'
                            WHEN '06' THEN '綠色'
                            WHEN '0607' THEN '綠藍色'
                            WHEN '07' THEN '藍色'
                            WHEN '08' THEN '靛色'
                            WHEN '09' THEN '紫色'
                            WHEN '10' THEN '銅色'
                            WHEN '11' THEN '銀色'
                            WHEN '12' THEN '金色'
                            ELSE '999999' END
                        ) AS ColorNumName
                    FROM [Literacy].[dbo].[View_ScoreList] 
                    {$string}
                ) AS selection
           ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getNoExamReport($data)
    {
        $values = [
            "start_date" => date('Y-m-d'),
            "end_date" => date('Y-m-d'),
        ];
        $string = "";
        $string2 = "";
        $check = false;
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            $values['start_date'] = $data['start_date'];
            $values['end_date'] = $data['end_date'];
        }

        if (array_key_exists('cityid', $data)) {
            $string .= "[Literacy].[dbo].[SchoolList].CityId = :cityid AND ";
            $string2 .= "[Literacy].[dbo].[SchoolList].CityId, ";
            $values['cityid'] = $data['cityid'];
            $check = true;
        }
        if (array_key_exists('postid', $data)) {
            $string .= "[Literacy].[dbo].[SchoolList].PostId = :postid AND ";
            $string2 .= "[Literacy].[dbo].[SchoolList].PostId, ";
            $values['postid'] = $data['postid'];
            $check = true;
        }
        if (array_key_exists('schoolname', $data)) {
            $string .= "[Literacy].[dbo].[SchoolList].SchoolName = :schoolname AND ";
            $string2 .= "[Literacy].[dbo].[SchoolList].SchoolName, ";
            $values['schoolname'] = $data['schoolname'];
            $check = true;
        }
        if ($check) {
            $string = "HAVING " . $string;
            $string2 = "ORDER BY " . $string2;


            $string2 = rtrim($string2, ", ");
            $string .= "MAX([Literacy].[dbo].[Exam_Word_Score].AddTime) is NULL";
        }
        $sql = "SELECT [Literacy].[dbo].[SchoolList].Sid AS School_Sid, [Literacy].[dbo].[SchoolList].CityId
                , [Literacy].[dbo].[SchoolList].PostId, [Literacy].[dbo].[SchoolList].SchoolName
                , [Literacy].[dbo].[Teacher].Grade, [Literacy].[dbo].[Teacher].Class, [Literacy].[dbo].[Teacher].TeacherName
                , [Literacy].[dbo].[Student].StuName, [Literacy].[dbo].[Student].StuNum, [Literacy].[dbo].[Student].SeatNum 
                , MAX([Literacy].[dbo].[Exam_Word_Score].AddTime) AS AddTime
                FROM [Literacy].[dbo].[SchoolList] 
                INNER JOIN [Literacy].[dbo].[Student] ON [Literacy].[dbo].[SchoolList].Sid = [Literacy].[dbo].[Student].Sid
                LEFT OUTER JOIN [Literacy].[dbo].[Exam_Word_Score] ON [Literacy].[dbo].[Student].Pid = [Literacy].[dbo].[Exam_Word_Score].Pid
                AND (CONVERT(char,  [Literacy].[dbo].[Exam_Word_Score].AddTime, 111) Between :start_date AND :end_date)
                LEFT OUTER JOIN [Literacy].[dbo].[Teacher] ON [Literacy].[dbo].[Student].Tid = [Literacy].[dbo].[Teacher].Tid
                GROUP BY [Literacy].[dbo].[SchoolList].Sid, [Literacy].[dbo].[SchoolList].CityId
                , [Literacy].[dbo].[SchoolList].PostId, [Literacy].[dbo].[SchoolList].SchoolName
                , [Literacy].[dbo].[Teacher].Grade, [Literacy].[dbo].[Teacher].Class, [Literacy].[dbo].[Teacher].TeacherName
                , [Literacy].[dbo].[Student].StuName, [Literacy].[dbo].[Student].StuNum, [Literacy].[dbo].[Student].SeatNum
                {$string}
                {$string2}
                
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getExamYear($data)
    {
        $sql = "SELECT * 
                FROM [Literacy].[dbo].[Options] 
                WHERE [Literacy].[dbo].[Options].OptionItem = 'Report_Year'
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getExamTerm($data)
    {
        $sql = "SELECT *  
                FROM [Literacy].[dbo].[Options] 
                WHERE [Literacy].[dbo].[Options].OptionItem = 'Report_Term'
            ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getExamTkind($data)
    {
        $sql = "SELECT * 
                FROM [Literacy].[dbo].[Options] 
                WHERE [Literacy].[dbo].[Options].OptionItem = 'Report_TKind'
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getExamRM($data)
    {
        $sql = "SELECT [PageItem], [PageHtml]
                FROM [Literacy].[dbo].[Page] 
                WHERE [Literacy].[dbo].[Page].[PageId] = 'ExamRM'
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getPage($data)
    {
        $values = [
            "pageid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [PageItem], [PageHtml] 
                FROM [Literacy].[dbo].[Page] 
                WHERE [Literacy].[dbo].[Page].[PageId] = :pageid
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_page_island($data)
    {
        $values = [
            "pageid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [PageItem], [PageHtml] 
                FROM [Island_Page]
                WHERE [Island_Page].[PageId] = :pageid
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_page_learn($data)
    {
        $values = [
            "PageId" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [PageItem], [PageHtml] 
                FROM [Literacy].[dbo].[Learn_Page]
                WHERE [Learn_Page].[PageId] = :PageId
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_page_list_learn($data)
    {

        $sql = "SELECT DISTINCT [Pid], [PageId], [PageItem]
                FROM [Literacy].[dbo].[Learn_Page]
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_word_library($data)
    {
        $values = [
            "cur_page" => 1,
            "size" => 10
        ];

        $string = "";
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('word', $data)) {
            if (!empty($data['word'])) {
                $string .= "WHERE [Learn_Word_Romanization].[word] LIKE '%' + :word + '%'";
                $values['word'] = $data['word'];
            }
        }
        $values['length'] = $values['cur_page'] * $values['size'];
        $values['start'] = $values['length'] - $values['size'];
        unset($values['cur_page']);
        unset($values['size']);

        $sql = "SELECT *
                FROM (
                    SELECT ROW_NUMBER() OVER ( ORDER BY [Learn_Word_Romanization].[nid] ) AS row_num
                    , [Learn_Word_Romanization].[nid]
                    , [Learn_Word_Romanization].[word]
                    , [Learn_Word_Romanization].[romanization]
                    , [Learn_Word_Romanization].[bopomo]
                    , [Learn_Word_Romanization].[stroke]
                    , [Learn_Word_Romanization].[part]
                    , [Learn_Word_Romanization].[part_stroke],
                    STUFF((
                        SELECT [Learn_Sentence].[sentence_id]
                            , [Learn_Sentence].[color]
                            , [Learn_Sentence].[level]
                            , [Learn_Sentence].[sentence]
                            , [Learn_Sentence].[choose]
                            , [Learn_Sentence].[ans]
                        FROM [Literacy].[dbo].[Learn_Word_Sentence]
                        LEFT JOIN [Literacy].[dbo].[Learn_Sentence] ON [Learn_Word_Sentence].[sentence_id] = [Learn_Sentence].[sentence_id]
                        WHERE [Learn_Word_Romanization].[nid] = [Learn_Word_Sentence].[nid]
                    FOR XML PATH),1,0,''
                    )[Learn_Sentence_agg],
                    STUFF((
                        SELECT [nid], [word], [bopomo], [choose]
                        FROM [Literacy].[dbo].[Learn_Listenword]
                        WHERE [Learn_Listenword].[nid] = [Learn_Word_Romanization].[nid]
                    FOR XML PATH),1,0,''
                    )[Learn_Listenword_agg]
                    FROM [Literacy].[dbo].[Learn_Word_Romanization]
                    {$string}
                ) AS selection
                WHERE selection.row_num > :start AND selection.row_num <= :length
                ORDER BY CAST(selection.[nid] AS int)
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $key_result => $value) {
            $tmpvalue = $value['Learn_Sentence_agg'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue</a>");
            if ($tmpvalue == "") {
                $result[$key_result]['Learn_Sentence_agg'] = $tmpArrs;
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
            $result[$key_result]['Learn_Sentence_agg'] = $tmpArrs;
            Endquotation:
            /*  */
            $tmpvalue = $value['Learn_Listenword_agg'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue</a>");
            if ($tmpvalue == "") {
                $result[$key_result]['Learn_Listenword_agg'] = $tmpArrs;
                goto Endquotation2;
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
            $result[$key_result]['Learn_Listenword_agg'] = $tmpArrs;
            Endquotation2:
        }
        return $result;
    }

    public function get_word_library_dropdown($data)
    {
        $values = [];

        $string = "";
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('word', $data)) {
            if (!empty($data['word'])) {
                $string .= "WHERE [Learn_Word_Romanization].[word] LIKE '%' + :word + '%'";
                $values['word'] = $data['word'];
            }
        }

        $sql = "SELECT ROW_NUMBER() OVER ( ORDER BY [Learn_Word_Romanization].[nid] ) AS row_num
                , [Learn_Word_Romanization].[nid]
                , [Learn_Word_Romanization].[word]
                , [Learn_Word_Romanization].[romanization]
                , [Learn_Word_Romanization].[bopomo]
                , [Learn_Word_Romanization].[stroke]
                , [Learn_Word_Romanization].[part]
                , [Learn_Word_Romanization].[part_stroke],
                STUFF((
                    SELECT [Learn_Sentence].[sentence_id]
                        , [Learn_Sentence].[color]
                        , [Learn_Sentence].[level]
                        , [Learn_Sentence].[sentence]
                        , [Learn_Sentence].[choose]
                        , [Learn_Sentence].[ans]
                    FROM [Literacy].[dbo].[Learn_Word_Sentence]
                    LEFT JOIN [Literacy].[dbo].[Learn_Sentence] ON [Learn_Word_Sentence].[sentence_id] = [Learn_Sentence].[sentence_id]
                    WHERE [Learn_Word_Romanization].[nid] = [Learn_Word_Sentence].[nid]
                FOR XML PATH),1,0,''
                )[Learn_Sentence_agg],
                STUFF((
                    SELECT [nid], [word], [bopomo], [choose]
                    FROM [Literacy].[dbo].[Learn_Listenword]
                    WHERE [Learn_Listenword].[nid] = [Learn_Word_Romanization].[nid]
                FOR XML PATH),1,0,''
                )[Learn_Listenword_agg]
                FROM [Literacy].[dbo].[Learn_Word_Romanization]
                {$string}
                ORDER BY [Learn_Word_Romanization].[nid]
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $key_result => $value) {
            $tmpvalue = $value['Learn_Sentence_agg'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue</a>");
            if ($tmpvalue == "") {
                $result[$key_result]['Learn_Sentence_agg'] = $tmpArrs;
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
            $result[$key_result]['Learn_Sentence_agg'] = $tmpArrs;
            Endquotation:
            /*  */
            $tmpvalue = $value['Learn_Listenword_agg'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue</a>");
            if ($tmpvalue == "") {
                $result[$key_result]['Learn_Listenword_agg'] = $tmpArrs;
                goto Endquotation2;
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
            $result[$key_result]['Learn_Listenword_agg'] = $tmpArrs;
            Endquotation2:
        }
        return $result;
    }


    public function get_word_stroke($data)
    {
        $values = [
            "nid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [stroke]
                FROM [Literacy].[dbo].[Learn_Word_Romanization]
                WHERE [Learn_Word_Romanization].[nid] = :nid
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function delete_word_library($data)
    {
        $values = [
            "nid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "DELETE FROM [Literacy].[dbo].[Learn_Word_Romanization]
                WHERE [nid] = :nid
        ";

        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "message" => "刪除失敗",
                "info" => $stmt->errorInfo()
            ];
        }
        return [
            "status" => "success",
            "message" => "刪除成功"
        ];
    }

    public function get_version_library($data)
    {
        $values = [
            "cur_page" => 1,
            "size" => 10
        ];

        $string = "";
        $string_word = "";
        $check = false;

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('Year', $data)) {
            $string .= " AND [Learn_Version].[Year] = :Year";
            $values['Year'] = $data['Year'];
            $check = true;
        }
        if (array_key_exists('Grade', $data)) {
            $string .= " AND [Learn_Version].[Grade] = :Grade";
            $values['Grade'] = $data['Grade'];
            $check = true;
        }
        if (array_key_exists('Version', $data)) {
            $string .= " AND [Learn_Version].[Version] = :Version";
            $values['Version'] = $data['Version'];
            $check = true;
        }
        if (array_key_exists('Lesson', $data)) {
            $string .= " AND [Learn_Version].[Lesson] = :Lesson";
            $values['Lesson'] = $data['Lesson'];
            $check = true;
        }
        if (array_key_exists('Term', $data)) {
            $string .= " AND [Learn_Version].[Term] = :Term";
            $values['Term'] = $data['Term'];
            $check = true;
        }
        if (array_key_exists('Word', $data)) {
            $string_word .= " WHERE selection.List LIKE '%'+ :Word +'%' ";
            $values['Word'] = $data['Word'];
        }

        if ($check) {
            $string = "WHERE " . ltrim($string, ' AND');
        }

        $values['length'] = $values['cur_page'] * $values['size'];
        $values['start'] = $values['length'] - $values['size'];

        unset($values['cur_page']);
        unset($values['size']);
        $with = "WITH selection AS (
                    					SELECT *
					FROM(
                        SELECT ROW_NUMBER() OVER ( ORDER BY [Learn_Version].[Lid] ) AS row_num
                        , [Learn_Version].[Lid]
                        , [Learn_Version].[Version]
                        , [Learn_Version].[Year]
                        , [Learn_Version].[Grade]
                        , [Learn_Version].[Term]
                        , [Learn_Version].[Lesson]
                        , [Learn_Version].[LessonName]
                        , STUFF( 
                            (
                                SELECT ',' + CAST([Learn_Word_Romanization].[word] AS VARCHAR(MAX))
                                FROM [Literacy].[dbo].[Learn_Version_Word] 
                                LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Version_Word].[nid] = [Learn_Word_Romanization].[nid]
                                WHERE [Learn_Version_Word].[Lid] = [Learn_Version].[Lid]
                                FOR XML PATH ('')
                            ), 1, 1, ''
                        ) AS List
                        , STUFF( 
                            (
                                SELECT [Learn_Word_Romanization].[nid], [Learn_Word_Romanization].[word]
                                FROM [Literacy].[dbo].[Learn_Version_Word] dt
                                LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON dt.[nid] = [Learn_Word_Romanization].[nid]
                                WHERE dt.[Lid] = [Learn_Version].[Lid]
                                FOR XML RAW, ELEMENTS
                            ), 1, 0, ''
                        ) AS nid
                        FROM [Literacy].[dbo].[Learn_Version] 
                        {$string}
                        )dt
                        {$string_word}
                    ) 
        ";
        $sql = $with . "
                SELECT *
                FROM selection
                WHERE selection.row_num > :start AND selection.row_num <= :length
                ORDER BY [Year] DESC
				, CASE [Grade] 
					WHEN '一' THEN '1' 
					WHEN '二' THEN '2' 
					WHEN '三' THEN '3' 
					WHEN '四' THEN '4' 
					WHEN '五' THEN '5' 
					WHEN '六' THEN '6' 
				END
				, CASE [Term] WHEN '上' THEN 0 ELSE 1 END
                , [Version]
				, CASE [Lesson] 
					WHEN '第一課' THEN 1
					WHEN '第二課' THEN 2
					WHEN '第三課' THEN 3
					WHEN '第四課' THEN 4
					WHEN '第五課' THEN 5
					WHEN '第六課' THEN 6
					WHEN '第七課' THEN 7
					WHEN '第八課' THEN 8
					WHEN '第九課' THEN 9
					WHEN '第十課' THEN 10
					WHEN '第十一課' THEN 11
					WHEN '第十二課' THEN 12
					WHEN '第十三課' THEN 13
					WHEN '第十四課' THEN 14
					WHEN '第十五課' THEN 15
					WHEN '第十六課' THEN 16
					WHEN '第十七課' THEN 17
					WHEN '第十八課' THEN 18
					WHEN '第十九課' THEN 19
					WHEN '第二十課' THEN 20
				END
        ";
        // var_dump($sql);
        // exit(0);
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result['data'] = $sth->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result['data'] as $key_result => $value) {
            $tmpvalue = $value['nid'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue</a>");
            if ($tmpvalue == "") {
                $result['data'][$key_result]['nid'] = $tmpArrs;
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
            $result['data'][$key_result]['nid'] = $tmpArrs;
            Endquotation:
        }
        /*  */
        unset($values['start']);
        unset($values['length']);
        $sql = $with . "SELECT COUNT(*) count
            FROM selection
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result['total'] = $sth->fetchColumn(0);
        return $result;
    }

    public function get_word_library_count($data)
    {
        $values = [];

        $string = "";
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('word', $data)) {
            if (!empty($data['word'])) {
                $string .= "WHERE [Learn_Word_Romanization].[word] LIKE '%' + :word + '%'";
                $values['word'] = $data['word'];
            }
        }

        $sql = "SELECT COUNT(DISTINCT [nid]) AS count_data
                FROM (
                    SELECT [Learn_Word_Romanization].[nid], [Learn_Word_Romanization].[word]
                    , [Learn_Word_Romanization].[romanization]
                    , [Learn_Word_Romanization].[bopomo]
                    , [Learn_Word_Romanization].[stroke]
                    , [Learn_Word_Romanization].[part]
                    , [Learn_Word_Romanization].[part_stroke]
                    , [Learn_Sentence].[sentence_id]
                    , [Learn_Sentence].[color]
                    , [Learn_Sentence].[level]
                    , [Learn_Sentence].[sentence]
                    , [Learn_Sentence].[choose]
                    , [Learn_Sentence].[ans]
                    FROM [Literacy].[dbo].[Learn_Word_Romanization]
                    LEFT JOIN [Literacy].[dbo].[Learn_Word_Sentence] ON [Learn_Word_Romanization].[nid] = [Learn_Word_Sentence].[nid]
                    LEFT JOIN [Literacy].[dbo].[Learn_Sentence] ON [Learn_Word_Sentence].[sentence_id] = [Learn_Sentence].[sentence_id]
                    {$string}
                ) AS selection
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_version_library_count($data)
    {
        $values = [
            "cur_page" => 1,
            "size" => 10
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $values['length'] = $values['cur_page'] * $values['size'];
        $values['start'] = $values['length'] - $values['size'];

        $sql = "SELECT COUNT(*) AS count_data 
                FROM (
                    SELECT [Learn_Version].[Lid]
                    , [Learn_Version].[Version]
                    , [Learn_Version].[Year]
                    , [Learn_Version].[Grade]
                    , [Learn_Version].[Term]
                    , [Learn_Version].[Lesson]
                    , [Learn_Version].[LessonName]
                    FROM [Literacy].[dbo].[Learn_Version]
                    LEFT JOIN [Literacy].[dbo].[Learn_Version_Word] ON [Learn_Version].[Lid] = [Learn_Version_Word].[nid]
                    LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Word_Sentence].[nid] = [Learn_Word_Romanization].[nid]
                ) AS selection
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_manager_data($data)
    {
        $values = [
            "uid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [UID], [Name], [EMail], [Photo]
                FROM [SystemAdmin]
                WHERE [SystemAdmin].[UID] = :uid
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getManagerRecord($data)
    {
        $values = [];
        $string = "WHERE";
        $check = false;
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('ipAddress', $data)) {
            $string .= " [IP] LIKE '%' + :ipAddress + '%' AND";
            $values['ipAddress'] = $data['ipAddress'];
            $check = true;
        }
        if (array_key_exists('keyword', $data)) {
            $string .= " ([Page] LIKE '%' + :keyword + '%') OR ([Name] LIKE '%' + :keyword1+ '%') OR ([SQLCmd] LIKE '%' + :keyword3 + '%') AND";
            $check = true;
            $values['keyword1'] = $data['keyword'];
            $values['keyword'] = $data['keyword'];
            $values['keyword3'] = $data['keyword'];
        }
        if ($check) {
            $string = rtrim($string, "AND");
        } else {
            $string = ltrim($string, "WHERE");
        }


        $sql = "SELECT * 
                FROM [Literacy].[dbo].[View_Record] 
                {$string} 
                ORDER BY [RRNid] DESC
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_surf_people($data)
    {
        $sql = "SELECT COUNT([RRNid]) amount
                FROM [Literacy].[dbo].[View_Record] 
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function delete_news($data)
    {
        $values = [
            "Nid" => null
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (is_null($values['Nid'])) {
            return [
                "status" => "failure",
            ];
        }
        $sql = "DELETE FROM [News] 
            WHERE [Nid] = :Nid
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "info" => $stmt->errorInfo()
            ];
        }
        return [
            "status" => "success"
        ];
    }

    public function delete_news_island($data)
    {
        $values = [
            "Nid" => null
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (is_null($values['Nid'])) {
            return [
                "status" => "failure",
            ];
        }
        $sql = "DELETE FROM [Portal].[dbo].[News]
                WHERE [Nid] = :Nid
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "info" => $stmt->errorInfo()
            ];
        }
        return [
            "status" => "success",
            "message" => "刪除成功"
        ];
    }

    public function delete_problem_island($data)
    {
        $values = [
            "Pid" => null
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (is_null($values['Pid'])) {
            return [
                "status" => "failure"
            ];
        }
        $sql = "DELETE FROM [Island_Problem]
                WHERE [Pid] = :Pid
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "info" => $stmt->errorInfo()
            ];
        }
        return [
            "status" => "success",
            "message" => "刪除成功"
        ];
    }

    public function delete_achievement_island($data)
    {
        $values = [
            "Aid" => null
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (is_null($values['Aid'])) {
            return [
                "status" => "failure"
            ];
        }
        $sql = "DELETE FROM [Island_Achievement]
                WHERE [Aid] = :Aid
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "info" => $stmt->errorInfo()
            ];
        }
        return [
            "status" => "success",
            "message" => "刪除成功"
        ];
    }

    public function delete_school($data)
    {
        $values = [
            "sid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "Delete FROM [School] 
                Where [Sid] = :sid
        ";

        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "info" => $stmt->errorInfo()
            ];
        }
        return [
            "status" => "success",
            "message" => "刪除成功"
        ];
    }

    public function delete_admin_school($data)
    {
        $values = [
            "passwd_md5" => '',
            "sid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "DELETE FROM [SchoolList] 
                WHERE [Sid] = :sid
                AND [PassWD_MD5] = :passwd_md5
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "info" => $stmt->errorInfo()
            ];
        }
        return [
            "status" => "success",
            "message" => "刪除成功"
        ];
    }

    public function post_basic_datamsg($data)
    {
        $values = [
            'CityId' => ' ',
            'PostId' => ' ',
            'Class' => ' ',
            'SchoolName' => ' ',
            'Used' => 0,
            'UpInfo' => ' ',
            'txt_Principal' => ' ',
            'txt_Senate' => ' ',
            'txt_Contact' => ' ',
            'txt_Contact_Titles' => ' ',
            'txt_Contact_Phone' => ' ',
            'txt_Contact_EMail_1' => ' ',
            'txt_Contact_EMail_2' => ' ',
            'txt_School_C_1' => ' ',
            'txt_School_C_2' => ' ',
            'txt_School_C_3' => ' ',
            'txt_School_C_4' => ' ',
            'txt_School_C_5' => ' ',
            'txt_School_C_6' => ' ',
            'txt_School_C_Other' => ' ',
            'txt_School_C_Total' => ' ',
            'txt_School_S_1' => ' ',
            'txt_School_S_2' => ' ',
            'txt_School_S_3' => ' ',
            'txt_School_S_4' => ' ',
            'txt_School_S_5' => ' ',
            'txt_School_S_6' => ' ',
            'txt_School_S_Other' => ' ',
            'txt_School_S_Total' => ' ',
            'txt_Exam_C_1' => ' ',
            'txt_Exam_C_2' => ' ',
            'txt_Exam_C_3' => ' ',
            'txt_Exam_C_4' => ' ',
            'txt_Exam_C_5' => ' ',
            'txt_Exam_C_6' => ' ',
            'txt_Exam_C_Other' => ' ',
            'txt_Exam_C_Total' => ' ',
            'txt_Exam_S_1' => ' ',
            'txt_Exam_S_2' => ' ',
            'txt_Exam_S_3' => ' ',
            'txt_Exam_S_4' => ' ',
            'txt_Exam_S_5' => ' ',
            'txt_Exam_S_6' => ' ',
            'txt_Exam_S_Other' => ' ',
            'txt_Exam_S_Total' => ' '
        ];


        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }


        $sql = "INSERT INTO SchoolList (
                    [CityId], [PostId], [Class]
                    , [SchoolName], [Used], [UpInfo],
                    [Principal], [Senate], 
                    [Contact], [Contact_Titles], [Contact_Phone], 
                    [Contact_EMail_1], 
                    [Contact_EMail_2],
                    [School_C_1], [School_C_2], [School_C_3], [School_C_4], [School_C_5], [School_C_6], [School_C_Other], [School_C_Total], 
                    [School_S_1], [School_S_2], [School_S_3], [School_S_4], [School_S_5], [School_S_6], [School_S_Other], [School_S_Total], 
                    [Exam_C_1], [Exam_C_2], [Exam_C_3], [Exam_C_4], [Exam_C_5], [Exam_C_6], [Exam_C_Other], [Exam_C_Total], 
                    [Exam_S_1], [Exam_S_2], [Exam_S_3], [Exam_S_4], [Exam_S_5], [Exam_S_6], [Exam_S_Other], [Exam_S_Total]
                ) VALUES (
                    :CityId,
                    :PostId,
                    :Class,
                    :SchoolName,
                    :Used,
                    :UpInfo,
                    :txt_Principal,
                    :txt_Senate,
                    :txt_Contact,
                    :txt_Contact_Titles,
                    :txt_Contact_Phone,
                    :txt_Contact_EMail_1,
                    :txt_Contact_EMail_2,
                    :txt_School_C_1, :txt_School_C_2, :txt_School_C_3, :txt_School_C_4, :txt_School_C_5, :txt_School_C_6, :txt_School_C_Other, :txt_School_C_Total, 
                    :txt_School_S_1, :txt_School_S_2, :txt_School_S_3, :txt_School_S_4, :txt_School_S_5, :txt_School_S_6, :txt_School_S_Other, :txt_School_S_Total, 
                    :txt_Exam_C_1, :txt_Exam_C_2, :txt_Exam_C_3, :txt_Exam_C_4, :txt_Exam_C_5, :txt_Exam_C_6, :txt_Exam_C_Other, :txt_Exam_C_Total, 
                    :txt_Exam_S_1, :txt_Exam_S_2, :txt_Exam_S_3, :txt_Exam_S_4, :txt_Exam_S_5, :txt_Exam_S_6, :txt_Exam_S_Other, :txt_Exam_S_Total 
                )
        ";
        $sth = $this->container->db->prepare($sql);
        if ($sth->execute($values)) {
            return ["status" => "success"];
        } else {
            return [
                "status" => "failure",
                "message" => $sth->errorInFo()
            ];
        }
    }

    public function post_deleted_school($data)
    {
        $values = [
            'Sid' => '',
            'CityId' => '',
            'PostId' => '',
            'Class' => '',
            'SchoolID' => '',
            'SchoolName' => '',
            'Used' => '',
            'txt_Principal' => '',
            'txt_Senate' => '',
            'txt_Contact' => '',
            'txt_Contact_Titles' => '',
            'txt_Contact_Phone' => '',
            'txt_Contact_EMail_1' => '',
            'UpInfo' => ' ',
            'Contact_EMail_2' => ' ',
            'School_C_1' => ' ',
            'School_C_2' => ' ',
            'School_C_3' => ' ',
            'School_C_4' => ' ',
            'School_C_5' => ' ',
            'School_C_6' => ' ',
            'School_C_Other' => ' ',
            'School_C_Total' => ' ',
            'School_S_1' => ' ',
            'School_S_2' => ' ',
            'School_S_3' => ' ',
            'School_S_4' => ' ',
            'School_S_5' => ' ',
            'School_S_6' => ' ',
            'School_S_Other' => ' ',
            'School_S_Total' => ' ',
            'Exam_C_1' => ' ',
            'Exam_C_2' => ' ',
            'Exam_C_3' => ' ',
            'Exam_C_4' => ' ',
            'Exam_C_5' => ' ',
            'Exam_C_6' => ' ',
            'Exam_C_Other' => ' ',
            'Exam_C_Total' => ' ',
            'Exam_S_1' => ' ',
            'Exam_S_2' => ' ',
            'Exam_S_3' => ' ',
            'Exam_S_4' => ' ',
            'Exam_S_5' => ' ',
            'Exam_S_6' => ' ',
            'Exam_S_Other' => ' ',
            'Exam_S_Total' => ' '
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "INSERT INTO [SchoolList_Del] (
                    [Sid], [CityId], [PostId], [Class], [SchoolID], [SchoolName], [Used], 
                    [Principal], [Senate], 
                    [Contact], [Contact_Titles], [Contact_Phone], 
                    [Contact_EMail_1], [UpInfo],
                    [Contact_EMail_2],
                    [School_C_1], [School_C_2], [School_C_3], [School_C_4], [School_C_5], [School_C_6], [School_C_Other], [School_C_Total], 
                    [School_S_1], [School_S_2], [School_S_3], [School_S_4], [School_S_5], [School_S_6], [School_S_Other], [School_S_Total], 
                    [Exam_C_1], [Exam_C_2], [Exam_C_3], [Exam_C_4], [Exam_C_5], [Exam_C_6], [Exam_C_Other], [Exam_C_Total], 
                    [Exam_S_1], [Exam_S_2], [Exam_S_3], [Exam_S_4], [Exam_S_5], [Exam_S_6], [Exam_S_Other], [Exam_S_Total]
                ) VALUES (
                    :Sid 
                    :CityId,
                    :PostId,
                    :Class,
                    :SchoolID,
                    :SchoolName,
                    :Used,
                    :txt_Principal,
                    :txt_Senate,
                    :txt_Contact,
                    :txt_Contact_Titles,
                    :txt_Contact_Phone,
                    :txt_Contact_EMail_1,
                    :UpInfo,
                    :Contact_EMail_2,
                    :School_C_1, :School_C_2, :School_C_3, :School_C_4, :School_C_5, :School_C_6, :School_C_Other, :School_C_Total, 
                    :School_S_1, :School_S_2, :School_S_3, :School_S_4, :School_S_5, :School_S_6, :School_S_Other, :School_S_Total, 
                    :Exam_C_1, :Exam_C_2, :Exam_C_3, :Exam_C_4, :Exam_C_5, :Exam_C_6, :Exam_C_Other, :Exam_C_Total, 
                    :Exam_S_1, :Exam_S_2, :Exam_S_3, :Exam_S_4, :Exam_S_5, :Exam_S_6, :Exam_S_Other, :Exam_S_Total 
                )
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        return ["status" => "success"];
    }

    public function post_news($data)
    {
        $values = [
            "NewsClass" => '',
            "Title" => '',
            "Contents" => '',
            "AddTime" => '',
            "Counter" => 0,
            "Nid" => null
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (is_null($values['Nid'])) {
            unset($values['Nid']);
            $sql = "INSERT INTO News (ShowDialog,NewsClass, Title, Contents, AddTime, Counter) 
                VALUES (0,:NewsClass,:Title,:Contents,:AddTime, :Counter)";
        } else {
            $sql = "UPDATE News 
                SET NewsClass = :NewsClass,  Title = :Title
                , Contents = :Contents, AddTime = :AddTime, Counter = :Counter
                WHERE Nid = :Nid
            ";
        }
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "info" => $stmt->errorInfo()
            ];
        }
        return [
            "status" => "success"
        ];
    }

    public function post_word($data)
    {
        $values = [
            "word" => '',
            "romanization" => '',
            "bopomo" => '',
            "stroke" => 0,
            "part_stroke" => 0,
            "part" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "INSERT INTO [Literacy].[dbo].[Learn_Word_Romanization]
                    (
                        [word]
                        ,[romanization]
                        ,[bopomo]
                        ,[stroke]
                        ,[part]
                        ,[part_stroke]
                        ,[created_at]
                        ,[updated_at]
                    )
                VALUES 
                    (
                        :word
                        , :romanization
                        , :bopomo
                        , :stroke
                        , :part
                        , :part_stroke
                        , GETDATE()
                        , GETDATE()
                    )
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "新增失敗"
            ];
        }
        return [
            "status" => "success",
            "message" => "新增成功"
        ];
    }

    public function post_word_excel($data)
    {
        $student = new student($this->container);

        $word_insert = $this->post_word($data);
        if ($word_insert['status'] === 'failure') {
            return $word_insert;
        }

        $nid = $student->get_word_romanization_word($data);
        $data['nid'] = $nid['nid'];
        $identify_insert = $this->post_identify($data);
        if ($identify_insert['status'] === 'failure') {
            return $identify_insert;
        }

        foreach ($data['Learn_Sentence_agg'] as $key => $value) {
            $sentence_insert = $this->post_sentence($data['Learn_Sentence_agg'][$key]);
            if ($sentence_insert['status'] === 'failure') {
                return $sentence_insert;
            }

            $sid = $student->get_sentence_id($data['Learn_Sentence_agg'][$key]);
            $data['Learn_Sentence_agg'][$key]['nid'] = $nid['nid'];
            $data['Learn_Sentence_agg'][$key]['sentence_id'] = $sid['sentence_id'];
            $word_sentence_insert = $this->post_word_sentence($data['Learn_Sentence_agg'][$key]);
            if ($word_sentence_insert['status'] === 'failure') {
                return $word_sentence_insert;
            }
        }
        return [
            "status" => "success",
            "message" => "新增成功"
        ];
    }

    public function post_version_word_excel($data)
    {
        $student = new student($this->container);

        $wordlist['wordlist'] = str_split($data['wordlist'], 3);
        unset($data['wordlist']);

        $post_result = $this->post_version_word($data);
        if ($post_result['status'] === 'failure') {
            return $post_result;
        }

        foreach ($wordlist['wordlist'] as $key => $value) {
            $word['word'] = $wordlist['wordlist'][$key];
            $nid = $student->get_word_nid($word);
            $lesson_word = ['Lid' => $post_result['Lid']];
            $lesson_word['nid'] = $nid['nid'];
            $lesson_word['sequence'] = (int)$key;
            $result = $this->insert_version_word($lesson_word);
            if ($result['status'] === 'failure') {
                return [
                    "status" => "failure",
                    "message" => "新增失敗2"
                ];
            }
        }

        return [
            "status" => "success",
            "message" => "新增成功"
        ];
    }

    public function patch_word($data)
    {
        $values = [
            "nid" => '',
            "word" => '',
            "romanization" => '',
            "bopomo" => '',
            "stroke" => 0,
            "part_stroke" => 0,
            "part" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[Learn_Word_Romanization]
                SET [word] = :word
                    ,[romanization] = :romanization
                    ,[bopomo] = :bopomo
                    ,[stroke] = :stroke
                    ,[part] = :part
                    ,[part_stroke] = :part_stroke
                    ,[updated_at] = GETDATE()
                WHERE [nid] = :nid
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
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

    public function insert_version_word($data)
    {
        $values = [
            "Lid" => 0,
            "nid" => 0,
            "sequence" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "INSERT INTO [Literacy].[dbo].[Learn_Version_Word]
                    (
                        [Lid]
                        ,[nid]
                        ,[sequence]
                    )
                VALUES 
                    (
                        :Lid
                        , :nid
                        , :sequence
                    )
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "修改失敗，請聯絡網站管理人。"
            ];
        }
        return [
            "status" => "success",
            "message" => "修改成功"
        ];
    }

    public function delete_version_word($data)
    {
        $values = [
            "Lid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "DELETE FROM [Literacy].[dbo].[Learn_Version_Word]
                WHERE [Lid] = :Lid
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "刪除失敗"
            ];
        }
        return [
            "status" => "success",
            "message" => "刪除成功"
        ];
    }

    public function delete_version($data)
    {
        $values = [
            "Lid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "DELETE FROM [Literacy].[dbo].[Learn_Version]
                WHERE [Lid] = :Lid
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "刪除失敗"
            ];
        }
        return [
            "status" => "success",
            "message" => "刪除成功"
        ];
    }

    public function post_version_word($data)
    {
        $values = [
            "Version" => null,
            "Grade" => null,
            "Year" => null,
            "Term" => null,
            "Lesson" => null,
            "LessonName" => null
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        foreach ($values as $key => $value) {
            if (is_null($value)) {
                return [
                    "status" => "failure",
                    "message" => "新增失敗，請聯絡網站管理人。",
                ];
            }
        }

        $sql = "INSERT INTO [Literacy].[dbo].[Learn_Version] ([Version], [Year], [Grade], [Term], [Lesson], [LessonName])
            VALUES(:Version, :Year, :Grade, :Term, :Lesson, :LessonName)
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "新增失敗",
                "error" => $sth->errorInfo()
            ];
        }
        return [
            "status" => "success",
            "message" => "新增成功",
            "Lid" => $this->container->db->lastInsertId()
        ];
    }

    public function patch_version_word($data)
    {
        $values = [
            "Lid" => 0,
            "LessonName" => '',
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[Learn_Version] SET 
                [LessonName] = :LessonName
                WHERE [Lid] = :Lid
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "修改失敗，請聯絡網站管理人。",
                "errprInfo" => $sth->errorInfo()
            ];
        }
        return [
            "status" => "success",
            "message" => "修改成功"
        ];
    }

    public function delete_identify($data)
    {
        $values = [
            "nid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "DELETE FROM [Literacy].[dbo].[Learn_Listenword]
                WHERE [Literacy].[dbo].[Learn_Listenword].[nid] = :nid
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "生字刪除失敗"
            ];
        }
        return [
            "status" => "success",
            "message" => "生字刪除成功"
        ];
    }

    public function post_identify($data)
    {
        $values = [
            "nid" => '',
            "word" => '',
            "bopomo" => '',
            "choose" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "INSERT INTO [Literacy].[dbo].[Learn_Listenword]
                    (
                        [nid]
                        ,[word]
                        ,[bopomo]
                        ,[choose]
                        ,[created_at]
                        ,[updated_at]
                    )
                VALUES 
                    (
                        :nid
                        , :word
                        , :bopomo
                        , :choose
                        , GETDATE()
                        , GETDATE()
                    )
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "生字新增失敗"
            ];
        }
        return [
            "status" => "success",
            "message" => "生字新增成功"
        ];
    }

    public function patch_identify($data)
    {
        $values = [
            "nid" => '',
            "word" => '',
            "bopomo" => '',
            "choose" => '',
            "sound" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[Learn_Listenword]
                SET  [word] = :word
                    ,[bopomo] = :bopomo
                    ,[choose] = :choose
                    ,[sound] = :sound
                    ,[updated_at] = GETDATE()
                WHERE [nid] = :nid
                
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "生字修改失敗"
            ];
        }
        return [
            "status" => "success",
            "message" => "修改成功"
        ];
    }

    public function delete_sentence($data)
    {
        $values = [
            "sentence_id" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "DELETE FROM [Literacy].[dbo].[Learn_Sentence]
                WHERE [Literacy].[dbo].[Learn_Sentence].[sentence_id] = :sentence_id
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "例句刪除失敗"
            ];
        }
        return [
            "status" => "success",
            "message" => "例句刪除成功"
        ];
    }

    public function post_sentence($data)
    {
        $values = [
            "sentence" => '',
            "ans" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "INSERT INTO [Literacy].[dbo].[Learn_Sentence]
                    (
                        [sentence]
                        ,[ans]
                        ,[created_at]
                        ,[updated_at]
                    )
                VALUES 
                    (
                        :sentence
                        , :ans
                        , GETDATE()
                        , GETDATE()
                    )
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "例句新增失敗，請聯絡網站管理人。"
            ];
        }
        return [
            "status" => "success",
            "message" => "新增成功"
        ];
    }

    public function patch_sentence($data)
    {
        $values = [
            "sentence_id" => '',
            "sentence" => '',
            "ans" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[Learn_Sentence]
                SET [sentence] = :sentence
                    ,[ans] = :ans
                    ,[updated_at] = GETDATE()
                WHERE [sentence_id] = :sentence_id
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "例句修改失敗，請聯絡網站管理人。"
            ];
        }
        return [
            "status" => "success",
            "message" => "修改成功"
        ];
    }

    public function delete_word_sentence($data)
    {
        $values = [
            "nid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "DELETE FROM [Literacy].[dbo].[Learn_Word_Sentence]
                WHERE [Literacy].[dbo].[Learn_Word_Sentence].[nid] = :nid
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "句子刪除失敗"
            ];
        }
        return [
            "status" => "success",
            "message" => "句子刪除成功"
        ];
    }

    public function post_word_sentence($data)
    {
        $values = [
            "sentence_id" => '',
            "nid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "INSERT INTO [Literacy].[dbo].[Learn_Word_Sentence]
                    (
                        [nid]
                        ,[sentence_id]
                    )
                VALUES 
                    (
                        :nid
                        , :sentence_id
                    )
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "句子新增失敗，請聯絡網站管理人。"
            ];
        }
        return [
            "status" => "success",
            "message" => "新增成功"
        ];
    }

    public function post_news_island($data)
    {
        $values = [
            "NewsClass" => '',
            "Title" => '',
            "Contents" => '',
            "AddTime" => '',
            "Counter" => 0,
            "Nid" => null
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (is_null($values['Nid'])) {
            unset($values['Nid']);
            $sql = "INSERT INTO [Portal].[dbo].[News] (ShowDialog,NewsClass, Title, Contents, AddTime, Counter) 
                VALUES (0,:NewsClass,:Title,:Contents,:AddTime, :Counter)";
        } else {
            $sql = "UPDATE [Portal].[dbo].[News]
                SET NewsClass = :NewsClass,  Title = :Title
                , Contents = :Contents, AddTime = :AddTime, Counter = :Counter
                WHERE Nid = :Nid
            ";
        }
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "info" => $stmt->errorInfo()
            ];
        }
        if (array_key_exists('Nid', $values)) {
            return [
                "status" => "success",
                "message" => "修改成功"
            ];
        } else {
            return [
                "status" => "success",
                "message" => "新增成功"
            ];
        }
    }

    public function post_problem_island($data)
    {
        $values = [
            "Title" => '',
            "Contents" => '',
            "Counter" => 0,
            "Pid" => null
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (is_null($values['Pid'])) {
            unset($values['Pid']);
            $sql = "INSERT INTO [Island_Problem] ([Title]
                    ,[Contents]
                    ,[Counter]
                    ,[Addtime]) 
                VALUES (:Title,:Contents,:Counter,GETDATE())";
        } else {
            unset($values['Counter']);
            $sql = "UPDATE [Island_Problem]
                    SET Title = :Title
                    , Contents = :Contents
                    WHERE Pid = :Pid
            ";
        }
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "info" => $stmt->errorInfo()
            ];
        }
        if (array_key_exists('Pid', $values)) {
            return [
                "status" => "success",
                "message" => "修改成功"
            ];
        } else {
            return [
                "status" => "success",
                "message" => "新增成功"
            ];
        }
    }

    public function post_achievement_island($data)
    {
        $values = [
            "Title" => '',
            "Contents" => '',
            "Photo" => null,
            "Aid" => null
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (is_null($values['Aid'])) {
            if (empty($values['Photo'])) {
                unset($values['Photo']);
                $string1 = '';
                $string = 'VALUES (:Title, :Contents, GETDATE())';
            } else {
                $string1 = ',[Photo]';
                $string = 'VALUES (:Title, :Contents , :Photo, GETDATE())';
            }
            unset($values['Aid']);
            $sql = "INSERT INTO [Literacy].[dbo].[Island_Achievement] (
                    [Title]
                    ,[Contents]
                    {$string1}
                    ,[AddTime])
                    {$string}
                ";
        } else {
            unset($values['Photo']);
            $sql = "UPDATE [Island_Achievement]
                    SET Title = :Title
                    , Contents = :Contents
                    WHERE Aid = :Aid
            ";
        }
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "info" => $stmt->errorInfo()
            ];
        }
        if (array_key_exists('Aid', $values)) {
            return [
                "status" => "success",
                "message" => "修改成功"
            ];
        } else {
            return [
                "status" => "success",
                "message" => "新增成功"
            ];
        }
    }

    public function patch_news_click($data)
    {
        $values = [
            "Nid" => '',
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
                if ($key == 'Nid') {
                    $values['Nid1'] = $data[$key];
                }
            }
        }

        $sql = "Update [News] Set 
                [Counter] = (
                    (SELECT [Counter] FROM [Literacy].[dbo].[News] WHERE [Nid] = :Nid)+1
                    )
                Where [Nid] = :Nid1
        ";

        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "info" => $stmt->errorInfo()
            ];
        }
        return [
            "status" => "success"
        ];
    }

    public function getNewList($data)
    {
        $values = [
            "NewsClass" => '全部',
            'Title' => '',
            'Nid' => null
        ];
        $stmt_string = [];
        $stmt_array = [];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if ($values['NewsClass'] != '全部') {
            $stmt_string['NewsClass'] = " AND [NewsClass] = :NewsClass";
            $stmt_array['NewsClass'] = $values['NewsClass'];
        }
        if (!empty($values['Title'])) {
            $stmt_string['Title'] = " AND [Title] like '%'+:Title+'%'";
            $stmt_array['Title'] = $values['Title'];
        }

        if (!is_null($values['Nid'])) {
            $stmt_string['Content'] = ",[Contents]";
            $stmt_string['Nid'] .= "AND [Literacy].[dbo].[News].[Nid] = :Nid ";
            $stmt_array['Nid'] = $values['Nid'];
        }
        $top = "";
        if (array_key_exists('three', $data)) {
            $top = "TOP 3";
        }
        $sql = "SELECT {$top} [Nid]
                    ,[ShowDialog]
                    ,[NewsClass]
                    ,[Title]
                    ,[FilePart]
                    ,[FileLink]
                    ,[Counter]
                    {$stmt_string['Content']}
                    ,convert(varchar, [AddTime], 102)[AddTime]
                FROM [Literacy].[dbo].[News]
                WHERE (datediff(d,Getdate(), AddTime) <= 0)
                {$stmt_string['NewsClass']}
                {$stmt_string['Title']}
                {$stmt_string['Nid']}
                ORDER BY [Literacy].[dbo].[News].[AddTime] DESC
        ";

        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($stmt_array)) {
            return [
                'status' => 'failure',
                'message' => $sth->errorInfo()
            ];
        }
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function patch_page($data)
    {
        $values = [
            "Title" => '',
            "PageHtml" => '',
            "PageId" => 0,
        ];
        foreach ($values as $key => $value) {
            $values[$key] = $data[$key];
        }
        $sql = "UPDATE Page 
            SET PageItem = :Title, PageHtml = :PageHtml 
            WHERE PageId = :PageId
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure"
            ];
        }
    }

    public function patch_page_island($data)
    {
        $values = [
            "PageItem" => '',
            "PageHtml" => '',
            "PageId" => 0,
        ];
        foreach ($values as $key => $value) {
            $values[$key] = $data[$key];
        }
        $sql = "UPDATE [Island_Page]
            SET PageItem = :PageItem, PageHtml = :PageHtml 
            WHERE PageId = :PageId
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure"
            ];
        }
        return [
            "status" => "success",
            "message" => "修改成功"
        ];
    }

    public function patch_page_learn($data)
    {
        $values = [
            "PageItem" => '',
            "PageHtml" => '',
            "PageId" => '',
        ];
        foreach ($values as $key => $value) {
            $values[$key] = $data[$key];
        }
        $sql = "UPDATE [Learn_Page]
            SET PageItem = :PageItem, PageHtml = :PageHtml 
            WHERE PageId = :PageId
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure"
            ];
        }
        return [
            "status" => "success",
            "message" => "修改成功"
        ];
    }

    public function getSystemManager($data)
    {
        $sql = "SELECT [UID], [Name], [AccID], [PassWD], [EMail], [PLevel], 
                (
                    CASE [Literacy].[dbo].[SystemAdmin].[Plevel]
                        WHEN '9' THEN '最大管理者' 
                        WHEN '1' THEN '助理'
                        ELSE '999999' END
                        ) AS Level
                FROM [Literacy].[dbo].[SystemAdmin]
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function post_SystemManager_account($data)
    {
        $values = [
            "name" => '',
            "email" => '',
            "aceid" => '',
            "passwd" => '',
            "plevel" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "INSERT INTO [Literacy].[dbo].[SystemAdmin] (
                [Name], [EMail], [AccID]
                , [PassWD], [PLevel]) 
                VALUES (
                    :name, :email, :aceid
                    , :passwd, :plevel
                    )
            ";
        $sth = $this->container->db->prepare($sql);
        if ($sth->execute($values)) {
            $status = [
                "status" => "success",
                "message" => "新增成功"
            ];
        } else {
            $status = [
                "status" => "failure",
                "message" => "新增失敗，請聯絡網站管理人。"

            ];
        }
        return $status;
    }

    public function patch_SystemManager_account($data)
    {
        $values = [
            "uid" => '',
            "name" => '',
            "aceid" => '',
            "passwd" => '',
            "plevel" => '',
            "email" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[SystemAdmin] Set 
                [Name] = :name, [AccID] = :aceid,
                [PassWD] = :passwd, [PLevel] = :plevel,
                [EMail] = :email
                WHERE [UID] = :uid
                ";
        $sth = $this->container->db->prepare($sql);
        if ($sth->execute($values)) {
            $status = ["status" => "success"];
        } else {
            $status = ["status" => "failure"];
        }
        return $status;
        // var_dump($sth->errorInfo());
    }

    public function patch_system_manager_photo($data)
    {
        $values = [
            "uid" => '',
            "photo" => null
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[SystemAdmin] Set 
                [Photo] = :photo
                WHERE [UID] = :uid
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
                "message" => "修改失敗",
                "info" => $sth->errorInfo()
            ];
        }
        return $status;
    }

    public function get_system_manager_photo($data)
    {
        $values = [
            "uid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [Photo] 
                FROM [Literacy].[dbo].[SystemAdmin]
                WHERE [UID] = :uid
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function post_system_manager_photo($data)
    {
        $values = [
            "uid" => '',
            "photo" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[SystemAdmin] Set 
                [Photo] = :photo
                WHERE [UID] = :uid
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

    public function patch_system_manager_data($data)
    {
        $values = [
            "uid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $string = "";
        $flag = false;
        if (array_key_exists('name', $data)) {
            $values['name'] = $data['name'];
            $string .= "[Name] = :name,";
            $flag = true;
        }
        if (array_key_exists('email', $data)) {
            $values['email'] = $data['email'];
            $string .= " [EMail] = :email,";
            $flag = true;
        }

        if ($flag) {
            $string = rtrim($string, ',');
        }

        $sql = "UPDATE [SystemAdmin] Set 
                {$string}
                WHERE [UID] = :uid
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

    public function delete_SystemManager_account($data)
    {
        $values = [
            "uid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "Delete FROM [SystemAdmin] 
                Where [UID] = :uid
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        if ($sth->execute($values)) {
            return [
                "status" => "success",
                "message" => "刪除成功"
            ];
        }
        return [
            "status" => "failed",
            "message" => "刪除失敗"
        ];
    }

    public function delete_school_teacher_student_account($data)
    {
        $values = [
            "pid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "Delete FROM [Student] 
                Where [Pid] = :pid
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        if ($sth->execute($values)) {
            return [
                "status" => "success",
                "message" => "刪除成功"
            ];
        }
        return [
            "status" => "failed",
            "message" => "刪除失敗"
        ];
    }

    public function getCityAccount($data)
    {
        $values = [];
        $string = "";
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('cityid', $data)) {
            $string = " SELECT [CityId], [CityName], [PassWD]
                        FROM [Literacy].[dbo].[City] 
                        WHERE [CityId] = :cityid
                        ORDER BY [OrderBy] ";
            $values['cityid'] = $data['cityid'];
        } else {
            $string = " SELECT *
                        FROM [Literacy].[dbo].[City] 
                        ORDER BY [OrderBy] ";
        }
        $sql = "{$string}
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function patch_city_account($data)
    {
        $values = [
            'passwd' => '',
            'cityid' => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "UPDATE [Literacy].[dbo].[City] SET  
                [PassWD] = :passwd
                WHERE CityId = :cityid
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        return ["status" => "success"];
    }

    public function getCitySchool($data)
    {
        $values = [
            "city" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT * 
                FROM [Literacy].[dbo].[School] 
                WHERE [City] = :city
                Order By [Area], [Class], [SchoolName]
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getStuExamRecord($data)
    {
        $values = [
            "pid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Literacy].[dbo].[Student].Pid, [Literacy].[dbo].[Student].StuName
                , [Literacy].[dbo].[Student].StuNum, [Literacy].[dbo].[Student].SeatNum
                , [Literacy].[dbo].[Exam_Word_Score].Sid, [Literacy].[dbo].[Exam_Word_Score].Exam_Year 
                + '學年度' + [Literacy].[dbo].[Exam_Word_Score].Exam_Term + '學期' + [Literacy].[dbo].[Exam_Word_Score].Exam_TKind AS Exam_TimePoint 
		        , [Literacy].[dbo].[Exam_Word_Score].LiteracyScore, [Literacy].[dbo].[Exam_Word_Score].Theta
                , [Literacy].[dbo].[Exam_Word_Score].PR_Value, [Literacy].[dbo].[Exam_Word_Score].StartTime
                , [Literacy].[dbo].[Exam_Word_Score].EndTime, [Literacy].[dbo].[Exam_Word_Score].AddTime
                , Exam_Word.Title AS WKind, [Literacy].[dbo].[Exam_Word_Score].ExamProgramKind
		        , CASE 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].Exam_Term = '上' 
                        AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = '期初' THEN Average_1S 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].Exam_Term = '上' 
                        AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = '期末' THEN Average_1E 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].Exam_Term = '下' 
                        AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = '期初' THEN Average_2S 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].Exam_Term = '下' 
                        AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = '期末' THEN Average_2E
		            ELSE NULL END AS Average
		        , CASE 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].Exam_Term = '上' 
                        AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = '期初' THEN Standard_1S 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].Exam_Term = '上' 
                        AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = '期末' THEN Standard_1E 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].Exam_Term = '下' 
                        AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = '期初' THEN Standard_2S 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].Exam_Term = '下' 
                        AND [Literacy].[dbo].[Exam_Word_Score].Exam_TKind = '期末' THEN Standard_2E
                    ELSE NULL END AS Standard
		        , CASE WHEN [Literacy].[dbo].[Exam_Word_Score].ExamProgramKind in ('A2','A3','A4','A5','A6','A7','A8','A09','A10') 
                    THEN 
                    CASE WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 730 THEN '01' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 731 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 1210 THEN '02' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 1211 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 1300 THEN '03' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 1301 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 1560 THEN '04' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 1561 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 1870 THEN '05' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 1871 
                        AND  [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 2290 THEN '0607' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 2291 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 2490 THEN '08' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 2491 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 2760 THEN '09' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 2761 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 2840 THEN '10' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 2841 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 3040 THEN '11' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 3041 THEN '12' 
                    ELSE NULL END 
                ELSE CASE WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 499 THEN '01' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 500 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 899 THEN '02' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 900 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 1129 THEN '03' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 1130 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 1449 THEN '04' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 1450 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 1999 THEN '05' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 2000
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 2249 THEN '06' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 2250 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 2599 THEN '07' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 2600 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 2899 THEN '08' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 2900 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 3099 THEN '09' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 3100 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 3299 THEN '10' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 3300 
                        AND [Literacy].[dbo].[Exam_Word_Score].LiteracyScore <= 3499 THEN '11' 
                    WHEN [Literacy].[dbo].[Exam_Word_Score].LiteracyScore >= 3500 THEN '12' 
                    ELSE NULL END 
                    END AS ColorNum
                    , ( 
                        CASE 
                            WHEN [Exam_Word_Score].[ExamProgramKind] in ('A2','A3','A4','A5','A6','A7','A8','A09','A10') THEN 
                            CASE 
                                WHEN [Exam_Word_Score].[LiteracyScore] <= 730 THEN '白色' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 731 AND [Exam_Word_Score].[LiteracyScore] <= 1210 THEN '黑色'
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 1211 AND [Exam_Word_Score].[LiteracyScore] <= 1300 THEN '紅色'
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 1301 AND [Exam_Word_Score].[LiteracyScore] <= 1560 THEN '橙色' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 1561 AND [Exam_Word_Score].[LiteracyScore] <= 1870 THEN '黃色' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 1871 AND [Exam_Word_Score].[LiteracyScore] <= 2290 THEN '綠藍色'
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2291 AND [Exam_Word_Score].[LiteracyScore] <= 2490 THEN '靛色'
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2491 AND [Exam_Word_Score].[LiteracyScore] <= 2760 THEN '紫色'
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2761 AND [Exam_Word_Score].[LiteracyScore] <= 2840 THEN '銅色' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 2841 AND [Exam_Word_Score].[LiteracyScore] <= 3040 THEN '銀色' 
                                WHEN [Exam_Word_Score].[LiteracyScore] >= 3041 THEN '金色' 
                                ELSE 
                                    CASE ColorNum
                                        WHEN '06' THEN '綠色'
                                        WHEN '07' THEN '藍色' 
                                        END 
                                END
                            END
                        ) AS ColorNumName
		        FROM [Literacy].[dbo].[Student] 
                INNER JOIN [Literacy].[dbo].[Exam_Word_Score] ON [Literacy].[dbo].[Student].Pid = [Literacy].[dbo].[Exam_Word_Score].Pid 
		        INNER JOIN [Literacy].[dbo].[Exam_Word] ON [Literacy].[dbo].[Exam_Word_Score].Wid = [Literacy].[dbo].[Exam_Word].Wid
		        WHERE [Literacy].[dbo].[Student].Pid = :pid
                ORDER BY [Literacy].[dbo].[Exam_Word_Score].Sid DESC
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


    public function getSchoolName($data)
    {
        $values = [
            "sid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Literacy].[dbo].[SchoolList].CityId, [Literacy].[dbo].[SchoolList].SchoolName
                , [Literacy].[dbo].[SchoolList].Class
                FROM [Literacy].[dbo].[SchoolList] 
                WHERE [Literacy].[dbo].[SchoolList].Sid = :sid
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_event_logging($data)
    {
        $values = [];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [RRNid], [method], [arguments]
                    , [get_query_params], [get_parsed_body], [Session]
                    , [AddTime], [response], [description]
                    , [ip], [uri]
                FROM [Literacy].[dbo].[Event_Logging]
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_event_logging_excel($data)
    {
        $values = [];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [RRNid], [method], [arguments]
                    , [get_query_params], [get_parsed_body], [Session]
                    , [AddTime], [response], [description]
                    , [ip], [uri]
                FROM [Literacy].[dbo].[Event_Logging]
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getTeaName($data)
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
                FROM [Literacy].[dbo].[Teacher] 
                WHERE [Literacy].[dbo].[Teacher].[Tid] = :tid
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_admin_tea_name($data)
    {
        $values = [
            "tid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Sid], [Tid], [TeacherName]
                , [Grade], [Class]
                FROM [Literacy].[dbo].[Teacher] 
                WHERE [Literacy].[dbo].[Teacher].[Tid] = :tid
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function insertfile($data)
    {
        $values = [
            "file_name" => '',
            "upload_name" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "INSERT INTO [Literacy].[dbo].[File] ([File_name], [Upload_people], [Upload_name], [Upload_time]) 
                VALUES (:file_name, 'yeeda', :upload_name, GETDATE())
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getLibraryA($data)
    {
        $values = [];
        $string = "WHERE";
        $check = false;
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('grade', $data) && $data['grade'] !== 'all') {
            $string .= " [Grade] = :grade AND";
            $values['grade'] = $data['grade'];
            $check = true;
        }
        if (array_key_exists('scoring', $data) && $data['scoring'] !== 'all') {
            $string .= " [Scoring] = :scoring AND";
            $values['scoring'] = $data['scoring'];
            $check = true;
        }
        if (array_key_exists('word', $data) && $data['word'] === 'common') {
            $string .= " [ThreeWord_C] = 'Y' AND";
            $check = true;
        }
        if (array_key_exists('word', $data) && $data['word'] === 'normal') {
            $string .= " [ThreeWord] = 'Y' AND";
            $check = true;
        }
        if (array_key_exists('Show_N_A', $data) && $data['Show_N_A'] === 'Y') {
            $string .= " (LEFT(Scoring, 3) <> 'N_A') AND";
            $check = true;
        }

        if ($check) {
            $string = rtrim($string, "AND");
        } else {
            $string = ltrim($string, "WHERE");
        }

        $sql = "SELECT * 
                FROM [Literacy].[dbo].[Exam_Word_CV_2]
                {$string}
                ORDER BY [Grade] ASC, [Scoring] ASC, [ItemId] ASC
           ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getLibraryBC($data)
    {
        $values = [
            "Wid" => null
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $stmt_array = [];
        $stmt_string = "";
        if (!is_null($values['Wid'])) {
            $stmt_array = $values;
            $stmt_string = "WHERE [Wid] = :Wid";
        }
        $sql = "SELECT * 
                FROM [Literacy].[dbo].[Exam_Word] 
                {$stmt_string}
                ORDER BY [Grade] DESC, [Title]
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($stmt_array);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function patchExamWord($data)
    {
        $values = [
            "Words" => '',
            "Show" => '',
            "Average_1S" => '',
            "Standard_1S" => '',
            "Average_1E" => '',
            "Standard_1E" => '',
            "Average_2S" => '',
            "Standard_2S" => '',
            "Average_2E" => '',
            "Standard_2E" => '',
            "Wid" => '',
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = trim($data[$key]);
            }
        }
        $sql = "UPDATE Exam_Word 
            SET Words = :Words
		    , Show = :Show
            , Average_1S =:Average_1S
            , Standard_1S =:Standard_1S
            , Average_1E =:Average_1E
            , Standard_1E =:Standard_1E
            , Average_2S =:Average_2S
            , Standard_2S =:Standard_2S
            , Average_2E =:Average_2E
            , Standard_2E =:Standard_2E
            WHERE Wid =:Wid
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return ["status" => "failure", "message" => "資料更新有誤！"];
        }
        return ["status" => "success", "message" => "資料已更新！"];
    }

    public function get_school_passwd_md5($data)
    {
        $values = [
            "sid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [PassWD_MD5]
                FROM [Literacy].[dbo].[SchoolList]
                WHERE [Sid] = :sid 
        ";

        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return [
                "message" => "尚無密碼"
            ];
        } else {
            return $result;
        }
    }

    public function check_school_passwd($data)
    {
        $values = [
            "passwd" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT COUNT(*) 
                FROM [Literacy].[dbo].[LowPwList]
                WHERE [Passwd] = :passwd 
        ";

        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return [
                "status" => "failed",
                "message" => "經資安弱密碼字典比對,此管理碼為弱密碼,請設定其它強度管理碼。"
            ];
        } else {
            return ["status" => "success"];
        }
    }

    public function get_teacher_student_count($data)
    {
        $values = [
            "tid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT COUNT(*) AS count_data
                FROM (
                    SELECT 
                    [Pid], [Year]
                    , [StuName], [Sex], [IDNumber]
                    , [StuNum], [SeatNum], [Birth]
                    , [Pre_Grade], [Pre_Class]
                    , [Pre_SeatNum], [GraduationYear]
                    
                    FROM [Literacy].[dbo].[Student]
                    WHERE [Tid] = :tid
                    ) AS selection
        ";

        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_teacher_student($data)
    {
        $values = [
            "tid" => 0,
            "cur_page" => 1,
            "size" => 10
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $values['length'] = $values['cur_page'] * $values['size'];
        $values['start'] = $values['length'] - $values['size'];

        unset($values['cur_page']);
        unset($values['size']);

        $sql = "SELECT *
                FROM (
                    SELECT 
                    ROW_NUMBER() OVER (
                                    ORDER BY [Pid]
                                    ) AS 'key',
                    ROW_NUMBER() OVER (
                                    ORDER BY [Pid]
                                    ) AS row_num
                    , [Pid], [Year]
                    , [StuName], [Sex], [IDNumber]
                    , [StuNum], [SeatNum], [Birth]
                    , [Pre_Grade], [Pre_Class]
                    , [Pre_SeatNum], [GraduationYear]
                    
                    FROM [Literacy].[dbo].[Student]
                    WHERE [Tid] = :tid
                    ) AS selection
                WHERE selection.row_num > :start 
                AND selection.row_num <= :length
                ORDER BY CAST([SeatNum] AS int)
        ";

        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_admin_school_teacher($data)
    {
        $values = [
            "sid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Teacher].[Tid], [Teacher].[Sid]
                , [Teacher].[TeacherName], [Teacher].[TeacherMail]
                , [Teacher].[GraduationYear], [Teacher].[Grade]
                , [Teacher].[Class], [Teacher].[PassWD], [Teacher].[Used]
                , [OpenSet]
                , [Teacher].[AddTime]
                , COUNT([Student].[Pid]) AS [StuNum] 
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

    public function delete_admin_school_teacher($data)
    {

        $values = [
            "tid" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "Delete FROM [Teacher] 
                Where [Tid] = :tid
        ";

        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "message" => "刪除班級失敗",
                "info" => $stmt->errorInfo()
            ];
        }
        return [
            "status" => "success",
            "message" => "已刪除班級"
        ];
    }

    public function get_pid_data($data)
    {
        $values = [];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        // var_dump($data['pid1']);
        // exit(0);

        if (array_key_exists('pid1', $data)) {
            $values['pid'] = $data['pid1'];
        } else if (array_key_exists('pid2', $data)) {
            $values['pid'] = $data['pid2'];
        } else if (array_key_exists('pid3', $data)) {
            if ($data['pid3'] == '') {
                return array([
                    "status" => "failure",
                    "message" => "(未輸入Pid)"
                ]);
            }
            $values['pid'] = $data['pid3'];
        }
        // var_dump($values);
        // exit(0);

        $sql = "SELECT [Pid], ([StuName]+' ['+[SchoolName]+'-'+[Grade]+'年'+[Class]+'班'
                + '] - (身份證後6碼:'+[IDNumber]+')/(學號:'+[StuNum]+') / (座號:'+[StuNum]
                + ') / (生日:' +[Birth] + ')') AS data
                FROM [View_StudentList] 
                WHERE [Pid] = :pid
                ORDER BY [Pid]
        ";

        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) == 0) {
            return array([
                "status" => "failure",
                "message" => "查無Pid = {$values['pid']} 學生資料"
            ]);
        }
        return $result;
    }

    public function check_pid($data)
    {
        $values = [];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (array_key_exists('pid1', $data)) {
            $values['pid'] = $data['pid1'];
        } else if (array_key_exists('pid2', $data)) {
            $values['pid'] = $data['pid2'];
        } else if (array_key_exists('pid3', $data)) {
            $values['pid'] = $data['pid3'];
        }

        $sql = "SELECT COUNT(*)
                FROM [Literacy].[dbo].[View_StudentList] 
                WHERE [Pid] = :pid
        ";

        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetchColumn(0);
        if ($result != 0) {
            return [
                "status" => "failure",
                "message" => "查無Pid = {$values['pid']} 學生資料"
            ];
        }
        return [
            "status" => "success"
        ];
    }

    public function merge_pid($data)
    {
        $values = [
            'check_pid' => ''
        ];

        $string = "";
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (array_key_exists('pid1', $data)) {
            $values['pid1'] = $data['pid1'];
            $string .= ":pid1, ";
        }
        if (array_key_exists('pid2', $data)) {
            $values['pid2'] = $data['pid2'];
            $string .= ":pid2, ";
        }
        if (array_key_exists('pid3', $data)) {
            $values['pid3'] = $data['pid3'];
            $string .= ":pid3, ";
        }

        $string = rtrim($string, ', ');


        $sql = "UPDATE [Literacy].[dbo].[Exam_Word_Score] SET  
                [Pid] = :check_pid
                WHERE [Pid] in ({$string})
        ";

        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "更新失敗，請聯絡網站管理人。"
            ];
        }
        return [
            "status" => "success",
            "message" => "已合併至 (Pid = {$values['check_pid']}) 成功 , 並已刪除其他資料。"
        ];
    }

    public function delete_pid($data)
    {
        $values = [];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $string = "";
        if (array_key_exists('pid1', $data)) {
            $values['pid1'] = $data['pid1'];
            $string .= ":pid1, ";
        }
        if (array_key_exists('pid2', $data)) {
            $values['pid2'] = $data['pid2'];
            $string .= ":pid2, ";
        }
        if (array_key_exists('pid3', $data)) {
            $values['pid3'] = $data['pid3'];
            $string .= ":pid3, ";
        }
        $string = rtrim($string, ', ');


        $sql = "Delete FROM [Student] 
                Where [Pid] in ( {$string} )
        ";

        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "info" => $stmt->errorInfo()
            ];
        }
        return [
            "status" => "success"
        ];
    }

    public function check_add_admin_school_teacher($data)
    {
        $values = [
            "grade" => 0,
            "class" => 0,
            "sid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Sid]
                FROM [Literacy].[dbo].[Teacher] 
                WHERE [Sid] = :sid
                AND [Grade] = :grade
                AND [class] = :class
        ";

        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        // var_dump(empty($result));
        // exit(0);
        if (!empty($result)) {
            return [
                "status" => "failure",
                "message" => "此班級「{$values['grade']}年{$values['class']}班」已存在，請確認此班級是否已存在，避免新增多筆重覆班級資料。"
            ];
        }
        return [
            "status" => "success"
        ];
    }

    public function check_admin_school_teacher_student($data)
    {
        $values = [
            "stunum" => 0,
            "tid" => 0,
            "sid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT COUNT(*)
                FROM [Literacy].[dbo].[Student] 
                WHERE [StuNum] = :stunum
                AND [Sid] = :sid
                AND [Tid] = :tid
        ";

        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetchColumn(0);
        if ($result != 0) {
            return [
                "status" => "failure",
                "message" => "學號「{$values['StuNum']}」已存在，請確認此學生是否已存在，避免新增多筆重覆學生資料。"
            ];
        }
        return [
            "status" => "success"
        ];
    }

    public function check_delete_admin_school_teacher($data)
    {
        $values = [
            "tid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }


        $sql = "SELECT COUNT(*)
                FROM [Literacy].[dbo].[Student] 
                WHERE [Tid] = :tid
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchColumn(0);

        if ($result != 0) {
            return [
                "status" => "failure",
                "message" => "此班級裡已有學生資料,不可刪除。(需至該班級內清除所有學生資料後,方可刪除)"
            ];
        }
        return [
            "status" => "success"
        ];
    }

    public function post_import_admin_tea_data($data)
    {
        $values = [
            "Grade" => '',
            "Class" => '',
            "TeacherName" => '',
            "TeacherMail" => '',
            "PassWD" => '',
            "GraduationYear" => null,
            "Sid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if ($values['Grade'] == "畢") {
            $sql = "INSERT INTO [Teacher] ([Sid], [Grade], [Class]
                        , [TeacherName], [TeacherMail], [PassWD]
                        , [PassWD_ChangeDate], [GraduationYear]
                        )
                VALUES (:Sid, :Grade, :Class, :TeacherName
                    , :TeacherMail, :PassWD, GETDATE(), :GraduationYear)
            ";
        } else {
            $values["GraduationYear"] = $data["GraduationYear"];
            $sql = "INSERT INTO [Teacher] ([Sid], [Grade], [Class], [TeacherName]
                    , [TeacherMail], [PassWD], [PassWD_ChangeDate], [GraduationYear]
                    )
                    VALUES (:Sid, :Grade, :Class, :TeacherName
                        , :TeacherMail, :PassWD, GETDATE(), :GraduationYear)";
        }

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

    public function check_patch_admin_import_transfer_student_data($data)
    {
        $values = [
            'sid' => '',
            'grade' => '',
            'class' => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT TOP 1 [Tid]
                FROM [Literacy].[dbo].[Teacher] 
                WHERE [Sid] = :sid
                AND [Grade] = :grade
                AND [Class] = :class
        ";

        $sth = $this->container->db->prepare($sql);
        if ($sth->execute($values)) {
            return ["status" => "success"];
        } else {
            var_dump($sth->errorInfo());
            return ["status" => "failure"];
        }
    }

    public function find_patch_admin_import_transfer_student_data($data)
    {
        $values = [
            'sid' => '',
            'tid' => '',
            'stuname' => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT TOP 1 [Pid]
                FROM [Literacy].[dbo].[Student] 
                WHERE [Sid] = :sid
                AND [Tid] = :tid
                AND [StuName] = :stuname
        ";

        $sth = $this->container->db->prepare($sql);
        if ($sth->execute($values)) {
            return ["status" => "success"];
        } else {
            var_dump($sth->errorInfo());
            return ["status" => "failure"];
        }
    }

    public function patch_admin_import_transfer_student_data($data)
    {
        $values = [
            'tid' => '',
            'pid' => '',
            'year' => '',
            'seatnum' => '',
            'pre_grade' => '',
            'pre_class' => ''
        ];

        $sql = "UPDATE [Student] SET
                     [Tid]  = :tid
                    , [Year]  = :year
                    , [SeatNum]  = :seatnum
                    , [Pre_Grade]  = :pre_grade
                    , [Pre_Class]  = :pre_class
                WHERE [Pid] = :pid
        ";

        $sth = $this->container->db->prepare($sql);
        if ($sth->execute($values)) {
            return ["status" => "success"];
        } else {
            var_dump($sth->errorInfo());
            return ["status" => "failure"];
        }
    }

    public function post_admin_school_teacher($data)
    {
        $values = [
            "grade" => '',
            "class" => '',
            "teachername" => '',
            "teachermail" => '',
            "passwd" => '',
            "openset" => 1,
            "graduationyear" => null,
            "sid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "INSERT INTO [Teacher] (
                    [Sid], [Grade], [Class], [TeacherName], 
                    [TeacherMail], [PassWD], [OpenSet],
                    [PassWD_ChangeDate], [GraduationYear] )
                VALUES (:sid, :grade, :class, :teachername, 
                    :teachermail, :passwd, :openset, 
                    GETDATE(), :graduationyear )";
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

    public function post_admin_school_teacher_student($data)
    {
        $values = [
            "sex" => '',
            "birth" => '',
            "seatnum" => '',
            "stunum" => '',
            "idnumber" => '',
            "stuname" => '',
            "year" => '',
            "tid" => '',
            "sid" => 0
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
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            return [
                "status" => "failure",
                "message" => "新增失敗，請聯絡網站管理人。"
            ];
        }
        return [
            "status" => "success",
            "message" => "新增成功"
        ];
    }

    public function get_admin_school_teacher_data($data)
    {
        $values = [
            "tid" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [Tid], [Sid]
                , [TeacherName], [TeacherMail]
                , [GraduationYear], [Grade]
                , [Class], [PassWD], [Used]
                , ( CASE [Teacher].[OpenSet]
                    WHEN '1' THEN '關閉' 
                    WHEN '0' THEN '開放' 
                    END
                    ) AS 學生自行施測開關
                , [AddTime] 
                FROM [Literacy].[dbo].[Teacher] 
                WHERE [Tid] = :tid
        ";

        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function transfer_admin_school_teacher($data)
    {
        $values = [
            'grade' => '',
            'class' => '',
            'tid' => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "Update [Student] Set [Tid] = 0, 
                [Pre_Grade] = :grade, [Pre_Class] = :class
                Where [Tid] = :tid
        ";

        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure"
            ];
        }
        return [
            "status" => "success",
            "message" => "已轉出"
        ];
    }

    public function patch_admin_school_teacher($data)
    {
        $values = [
            'grade' => '',
            'class' => '',
            'teacher_name' => '',
            'teacher_mail' => '',
            'passwd' => '',
            'openset' => '',
            'graduationyear' => null,
            'tid' => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "UPDATE [Literacy].[dbo].[Teacher] SET  
                [Grade] = :grade, [Class] = :class, 
                [TeacherName] = :teacher_name, 
                [TeacherMail] = :teacher_mail, 
                [PassWD] = :passwd, [OpenSet] = :openset,
                [GraduationYear] = :graduationyear
                WHERE [Tid] = :tid
        ";

        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            return [
                "status" => "failure",
                "message" => "更新失敗，請聯絡網站管理人。"
            ];
        }
        return [
            "status" => "success",
            "message" => "更新成功"
        ];
    }

    public function patch_teacher_student_id($data)
    {
        if (strlen($data['idnumber']) != 6) {
            return [
                "status" => "failure",
                "message" => "請檢查身分證後六碼，是否超過或小於六位元"
            ];
        }

        $values = [
            'pid' => '',
            'idnumber' => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Student] Set
                [IDNumber] = :idnumber
                WHERE [Pid] = :pid
        ";

        $sth = $this->container->db->prepare($sql);
        if ($sth->execute($values)) {
            return [
                "status" => "success",
                "message" => "更新成功"
            ];
        } else {
            return [
                "status" => "failure",
                "message" => "更新失敗"
            ];
        }
    }

    public function patch_basic_datamsg($data)
    {
        $values = [
            'Sid' => '',
            'CityId' => ' ',
            'PostId' => ' ',
            'Class' => ' ',
            'SchoolName' => ' ',
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
                [CityId] = :CityId, [PostId] = :PostId, 
                [SchoolName] = :SchoolName, [Class] = :Class, 
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
        $sth->execute($values);
        return ["status" => "success"];
    }

    public function patch_school_passwd($data)
    {
        $values = [
            'sid' => '',
            'passwd' => '',
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $values['passwd_md5'] = md5($values['passwd']);

        $sql = "UPDATE [SchoolList] Set
                [PassWD_MD5] = :passwd_md5
                , [PassWD] = :passwd
                WHERE [Sid] = :sid
        ";
        $sth = $this->container->db->prepare($sql);
        if ($sth->execute($values)) {
            return ["status" => "已更新管理碼，並寄發通知信成功"];
        } else {
            return ["status" => "更新失敗，請聯絡網站管理人。"];
        }
    }

    public function getWordEdit($data)
    {
        $values = [
            "Wid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT * 
                FROM [Literacy].[dbo].[Exam_Word] 
                WHERE [Wid] = :Wid
                ORDER BY [Grade] DESC, [Title]
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getCommonWord($data)
    {
        $sql = "SELECT [Grade], [WordQuestion]
                FROM [Literacy].[dbo].[Exam_Word_CV_2]
                WHERE [ThreeWord] = 'Y'
                AND [Grade] = '1'
                
                UNION ALL
                
                SELECT [Grade], [WordQuestion]
                FROM [Literacy].[dbo].[Exam_Word_CV_2]
                WHERE [ThreeWord] = 'Y'
                AND [Grade] = '2'
                
                UNION ALL
                
                SELECT [Grade], [WordQuestion]
                FROM [Literacy].[dbo].[Exam_Word_CV_2]
                WHERE [ThreeWord] = 'Y'
                AND [Grade] = '3'
                
                UNION ALL
                
                SELECT [Grade], [WordQuestion]
                FROM [Literacy].[dbo].[Exam_Word_CV_2]
                WHERE [ThreeWord] = 'Y'
                AND [Grade] = '4'
                
                UNION ALL
                
                SELECT [Grade], [WordQuestion]
                FROM [Literacy].[dbo].[Exam_Word_CV_2]
                WHERE [ThreeWord] = 'Y'
                AND [Grade] = '5'
                
                UNION ALL
                
                SELECT [Grade], [WordQuestion]
                FROM [Literacy].[dbo].[Exam_Word_CV_2]
                WHERE [ThreeWord] = 'Y'
                AND [Grade] = '6'
                
                UNION ALL
                
                SELECT [Grade], [WordQuestion]
                FROM [Literacy].[dbo].[Exam_Word_CV_2]
                WHERE [ThreeWord_C] = 'Y'
                
        ";
        $stmt = $this->container->db->prepare($sql);
        if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (isset($result)) {
                foreach ($result as $key_result => $value) {
                    $tmpvalue = $value['Word'];
                    $tmpArrs = [];
                    $xml = simplexml_load_string("<a>$tmpvalue</a>");
                    if ($tmpvalue == "") {
                        $result[$key_result]['Word'] = $tmpArrs;
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
                    $result[$key_result]['Word'] = $tmpArrs;
                    Endquotation:
                }
            }
            return $result;
        } else {
            return [
                'status' => 'failure',
                'error_info' => $stmt->errorInfo()
            ];
        }
    }

    public function getCommonWordCount($data)
    {
        $sql = "SELECT [Grade], COUNT_BIG([WordQuestion]) AS WordCount
                , SUM ( 
                    CASE 
                        WHEN [ThreeWord] = 'Y' THEN 1 
                        ELSE 0 END
                        ) AS ThreeWord_Y_Count 
                FROM [Literacy].[dbo].[Exam_Word_CV_2]
                GROUP BY [Grade]
                ORDER BY [Grade]
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getfileList($data)
    {
        $values = [
            "cur_page" => 1,
            "size" => 10
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $values['length'] = $values['cur_page'] * $values['size'];
        $values['start'] = $values['length'] - $values['size'];

        unset($values['cur_page']);
        unset($values['size']);

        $sql = "SELECT *
                FROM (
                    SELECT ROW_NUMBER() OVER(ORDER BY [File_id]) AS 'key'
                    , ROW_NUMBER() OVER(ORDER BY [File_id]) AS row_num
                    , [File_id], [Upload_name]
                    FROM [Literacy].[dbo].[File]
                ) AS selection
                WHERE selection.row_num > :start
                AND selection.row_num <= :length
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_file_count($data)
    {

        $sql = "SELECT COUNT(*) AS count_data
                FROM (
                    SELECT ROW_NUMBER() OVER(ORDER BY [File_id]) AS row_num
                    , [File_id], [Upload_name]
                    FROM [Literacy].[dbo].[File]
                ) AS selection
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function delete_uploadfile($data)
    {
        $values = [
            "file_id" => 0,
            "upload_name" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "DELETE [Literacy].[dbo].[File]
                WHERE [File_id] = :file_id
                AND [Upload_name] = :upload_name
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        if ($sth->execute($values)) {
            return [
                "status" => "success",
                "message" => "刪除成功"
            ];
        }
        return [
            "status" => "failed",
            "message" => "刪除失敗"
        ];
    }

    public function getAccountAmount($data)
    {
        if ($data['identity'] === 'student') {
            $sql = "SELECT COUNT([Pid]) AS StuNum 
                    FROM  [Literacy].[dbo].[Student]
            ";
        } else if ($data['identity'] === 'teacher') {
            $sql = "SELECT COUNT([Tid]) AS TeaNum 
                    FROM  [Literacy].[dbo].[Teacher]
            ";
        } else if ($data['identity'] === 'school') {
            $sql = "SELECT COUNT([Sid]) AS SchNum 
                    FROM  [Literacy].[dbo].[SchoolList]
            ";
        }

        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function upload_editor_file($data)
    {
        $uploadedFiles = $data['files']['files'];
        foreach ($uploadedFiles as $uploadedFile) {
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = $this->moveUploaded_edit_file($this->container->upload_file_directory . 'News', $uploadedFile);
                var_dump($filename);
                exit(0);
                $result = array(
                    'status' => 'success',
                    'file_name' => $filename
                );
            } else {
                $result = array(
                    'status' => 'failed'
                );
            }
            return $result;
        }
    }

    public function moveUploaded_edit_file($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        return $filename;
    }

    public function upload_file($data)
    {
        $uploadedFiles = $data['files'];
        $uploadedFile = $uploadedFiles['inputFile'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $this->moveUploadedFile($this->container->upload_file_apply_directory, $uploadedFile);
            $result = array(
                'status' => 'success',
                'file_name' => 'UploadFile/ApplyFiles/' . $filename
            );
        } else {
            $result = array(
                'status' => 'failed'
            );
        }
        return $result;
    }

    public function upload_manager_photo($data)
    {
        $uploadedFiles = $data['files'];
        $uploadedFile = $uploadedFiles['inputFile'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $this->moveUploadedFile($this->container->upload_directory, $uploadedFile);
            $result = array(
                'status' => 'success',
                'file_name' => $filename
            );
        } else {
            $result = array(
                'status' => 'failed',
                'message' => '上傳圖片失敗'
            );
        }
        return $result;
    }

    public function upload_word_mp3($data)
    {
        // $uploadedFiles = $data['files'];
        // $uploadedFile = $uploadedFiles['inputFile'];
        $uploadedFiles = $data->getUploadedFiles();
        $uploadedFile = $uploadedFiles['inputFile'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $this->moveUploadedFile($this->container->upload_mp3_directory, $uploadedFile);
            $result = array(
                'status' => 'success',
                'file_name' => 'mp3/' . $filename
            );
        } else {
            $result = array(
                'status' => 'failed'
            );
        }
        return $result;
    }

    public function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        return $filename;
    }

    public function patch_apply_file($data)
    {
        $values = [
            "sid" => '',
            "name" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [SchoolList] 
                Set [ApplyFiles] = :name
                WHERE [Sid] = :sid
        ";
        $stmt = $this->container->db->prepare($sql);
        if (!$stmt->execute($values)) {
            $status = ["status" => "failure"];
        }
        $status = ["status" => "success"];
        return $status;
    }

    public function getFile($data)
    {
        $values = [
            "file_id" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [File_name], [Upload_name]
                FROM [Literacy].[dbo].[File]
                WHERE [File_id] = :file_id
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_online($data)
    {
        $values = [];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT COUNT(*) people_count
                FROM(
                    SELECT   [RRNid]
                            ,[UID]
                            ,[Page]
                            ,[SQLCmd]
                            ,[IP]
                            ,[AddTime]
                            ,[Session]   
                            ,X.Y.value('(Pid)[1]', 'VARCHAR(MAX)') as Pid
                            ,X.Y.value('(Tid)[1]', 'VARCHAR(MAX)') as Tid
                            ,X.Y.value('(Sid)[1]', 'VARCHAR(MAX)') as Sid
                            ,X.Y.value('(CityName)[1]', 'VARCHAR(MAX)') as CityName
                            ,X.Y.value('(Uid)[1]', 'VARCHAR(MAX)') as Uid
                    FROM [Literacy].[dbo].[Admin_Online_Record]
                    OUTER APPLY [Admin_Online_Record].[Session].nodes('SESSION') as X(Y) 
                )dt
                WHERE DATEDIFF(MINUTE, dt.[AddTime], GETDATE()) < 60
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function drawPic()
    {

        function getCode($num, $w, $h)
        {
            $code = "";
            for ($i = 0; $i < $num; $i++) {
                $code .= rand(0, 9);
            }
            session_start();
            $_SESSION["codeCheck"] = $code;
            session_write_close();

            header("Content-type: image/PNG");
            $im = imagecreate($w, $h);
            $black = imagecolorallocate($im, 0, 0, 0);
            $gray = imagecolorallocate($im, 200, 200, 200);
            $bgcolor = imagecolorallocate($im, 255, 255, 255);

            imagefill($im, 0, 0, $gray);

            imagerectangle($im, 0, 0, $w - 1, $h - 1, $black);

            $strx = rand(3, 30);
            for ($i = 0; $i < $num; $i++) {
                $strpos = rand(1, 5);
                imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);
                $strx += rand(6, 15);
            }
            imagepng($im);
            imagedestroy($im);
        }
        getCode(4, 100, 40);
    }

    public function checkCode($verify)
    {

        if ($verify == $_SESSION["codeCheck"]) {
            $result = [
                "status" => "success",
                "message" => "驗證碼正確",
                "codeCheck" => $_SESSION["codeCheck"]
            ];
        } else {
            $result = [
                "status" => "failed",
                "message" => "驗證碼錯誤",
                "codeCheck" => $_SESSION["codeCheck"]
            ];
        }
        return $result;
    }

    public function check_password($data)
    {

        if ($data['passwd'] == null) {
            return [
                "code" => 0,
                "status" => "failure",
                "message" => "密碼不能為空"
            ];
        }

        if (strlen($data['passwd']) < 8 || strlen($data['passwd']) > 20) {  //必須大於6個字元
            return [
                "code" => 1,
                "status" => "failure",
                "message" => "密碼必須大於8字元，或不得超過20個字元"
            ];
        }
        if (preg_match("/^\d*$/", $data['passwd'])) {
            return [
                "code" => 2,
                "status" => "failure",
                "message" => "密碼不能全是數字，請包含數字，字母大小寫或者特殊字元"
            ];
        }
        if (preg_match("/^[a-zA-Z \s]+$/", $data['passwd'])) {
            return [
                'code' => 3,
                'status' => 'failure',
                'message' => '密碼不能全是字母，請包含數字，字母大小寫或者特殊字元'
            ];
        }
        if (preg_match("/^[a-zA-Z0-9 \s]+$/", $data['passwd'])) {
            return [
                "code" => 4,
                "status" => "failure",
                "message" => "請包含特殊字元"
            ];
        }
        if (preg_match("/^[0-9!@#$% \s]+$/", $data['passwd'])) {
            return [
                "code" => 5,
                "status" => "failure",
                "message" => "請包含字母大小寫"
            ];
        }
        if (preg_match("/^[a-zA-Z!@#$% \s]+$/", $data['passwd'])) {
            return [
                "code" => 6,
                "status" => "failure",
                "message" => "請包含數字"
            ];
        }
        return [
            "code" => 0,
            "status" => "success",
            "message" => "密碼複雜度通過驗證"
        ];
    }

    public function get_mail_count()
    {
        $sql = "SELECT '共' AS Name , SUM (CountNum) AS CountNum
                FROM(
                    SELECT  'School' AS Name  
                        ,COUNT(
                        *
                        ) AS CountNum
                    FROM (
                        SELECT DISTINCT Contact_EMail_1 
                    FROM SchoolList 
                    WHERE Contact_EMail_1 <> '' 
                    AND Contact_EMail_1 is not null 
                    AND Contact_EMail_1 LIKE '%_@__%.__%'
                        ) AS a
                        
                    UNION ALL
                        
                    SELECT 'Teacher' AS Name 
                        ,COUNT(
                            *
                            ) AS CountNum
                    FROM (
                        SELECT DISTINCT Teacher.TeacherMail 
                        FROM Teacher 
                        INNER JOIN SchoolList ON Teacher.Sid = SchoolList.Sid 
                        WHERE Teacher.TeacherMail <> ''
                        AND Teacher.TeacherMail is not null
                        AND TeacherMail LIKE '%_@__%.__%'
                        ) AS b
                        ) AS c
                UNION ALL
                
                SELECT  'School' AS Name  
                        ,COUNT(
                        *
                        ) AS CountNum
                    FROM (
                        SELECT DISTINCT Contact_EMail_1 
                    FROM SchoolList 
                    WHERE Contact_EMail_1 <> '' 
                    AND Contact_EMail_1 is not null 
                    AND Contact_EMail_1 LIKE '%_@__%.__%'
                        ) AS a
                        
                    UNION ALL
                        
                    SELECT 'Teacher' AS Name 
                        ,COUNT(
                            *
                            ) AS CountNum
                    FROM (
                        SELECT DISTINCT Teacher.TeacherMail 
                        FROM Teacher 
                        INNER JOIN SchoolList ON Teacher.Sid = SchoolList.Sid 
                        WHERE Teacher.TeacherMail <> ''
                        AND Teacher.TeacherMail is not null
                        AND TeacherMail LIKE '%_@__%.__%'
                        ) AS b
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_all_school_mail($data)
    {
        $sql = "SELECT DISTINCT [Contact_EMail_1] 
                FROM [SchoolList] 
                WHERE [Contact_EMail_1] <> '' 
                AND [Contact_EMail_1] is not null 
                AND [Contact_EMail_1] LIKE '%_@__%.__%'
            ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getSendMailAccount($data)
    {
        $values = [
            "city_id" => '',
            "s_id" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            } else {
                unset($values[$key]);
            }
        }

        $condition = "";
        $condition_teacher = "";

        switch ($data['choose']) {
            case 'one_school':
                $condition = " AND ([CityId] = :CityId) AND ([Sid] = ':Sid')";
                $condition_teacher = " AND (SchoolList.CityId = :CityId) AND (Teacher.Sid = :Sid)";
                break;
            case 'county_school':
                $condition = " AND ([CityId] = ':CityId')";
                $condition_teacher = " AND (SchoolList.CityId = :CityId)";
                break;
            case 'all_school':
                $condition = "";
                $condition_teacher = "";
                break;
        }
        $schooSql = "SELECT DISTINCT Contact_EMail_1 
                    FROM SchoolList 
                    WHERE (Contact_EMail_1 <> '') AND (Contact_EMail_1 is not null) 
                        AND (Contact_EMail_1 LIKE '%_@__%.__%') $condition                
                    ";

        $sth = $this->container->db->prepare($schooSql);
        $sth->execute($values);
        $result['school'] = $sth->fetchAll(PDO::FETCH_ASSOC);

        if ($data["has_teacher"]) {
            $teacherSql = "SELECT DISTINCT Teacher.TeacherMail 
                    FROM Teacher 
                    INNER JOIN SchoolList ON Teacher.Sid = SchoolList.Sid 
                    WHERE (Teacher.TeacherMail <> '') AND (Teacher.TeacherMail is not null) 
                        AND (TeacherMail LIKE '%_@__%.__%') $condition_teacher                
                    ";

            $sth = $this->container->db->prepare($teacherSql);
            $sth->execute($values);
            $result['teacher'] = $sth->fetchAll(PDO::FETCH_ASSOC);
        }

        return $result;
    }

    public function admin_SendMail($data)
    {
        $account = $data['account'];
        $subject = $data['subject'];
        $body = $data['body'];

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
            ];
        } catch (Exception $e) {
            return [
                "message" => $mail->ErrorInfo,
                "status" => "fail",
            ];
        }
    }

    public function insert_logging($data)
    {
        // $sql = "SELECT MAX([RRNid])+1
        //         FROM [Literacy].[dbo].[Admin_Online_Record]";
        // $stmt = $this->container->db->prepare($sql);
        // if (!$stmt->execute()) {
        //     return ["status" => "failed"];
        // }
        // $result = $stmt->fetchColumn(0);
        // $data["rrnid"] = $result;

        // $sql = "INSERT INTO [Literacy].[dbo].[Admin_Online_Record]
        //         ([Name], [UID], [Page], [SQLCmd], [IP], [AddTime])
        //         VALUES (:account, :uid, :uri, :description, :ip, GETDATE())
        // ";
        unset($data['account']);
        
        $sql = "INSERT INTO [Literacy].[dbo].[Admin_Online_Record]
                ([UID], [Page], [SQLCmd], [IP], [AddTime], [Session])
                VALUES (:uid, :uri, :description, :ip, GETDATE(), :session)
        ";

        $stmt = $this->container->db->prepare($sql);

        $result = [
            "status" => "success"
        ];

        if (!$stmt->execute($data)) {
            $result = [
                "status" => "failed",
                "message" => $stmt->errorInfo()
            ];
        }
        // var_dump($result);
        // exit(0);
        return $result;
    }


    public function insert_logging_identify($data)
    {
        
        // $sql = "INSERT INTO [Literacy].[dbo].[Admin_Online_Record]
        //         ([Name], [UID], [Page], [SQLCmd], [IP], [AddTime])
        //         VALUES (:account, :uid, :uri, :description, :ip, GETDATE())
        // ";
        $sql = "INSERT INTO [Literacy].[dbo].[Event_Logging]
                (
                    [method]
                    , [arguments]
                    , [get_query_params]
                    , [get_parsed_body]
                    , [Session]
                    , [AddTime]
                    , [response]
                    , [description]
                    , [ip]
                    , [uri])
                VALUES (
                    :method, :arguments
                    , :get_query_params
                    , :get_parsed_body
                    , :session, GETDATE()
                    , :response, :description
                    , :ip, :uri)
        ";

        $stmt = $this->container->db->prepare($sql);

        $result = [
            "status" => "success"
        ];

        if (!$stmt->execute($data)) {
            $result = [
                "status" => "failed",
                "message" => $stmt->errorInfo()
            ];
        }
        // var_dump($result);
        // exit(0);
        return $result;
    }

    public function get_event_log($data)
    {
        $values = [];
        $Value = [
            'current_page' => 1,
            'size' => 10
        ];
        //Changing current page.
        foreach ($data as $key => $value) {
            $Value[$key] = $value;
        }
        $length = $Value['size'] * $Value['current_page'];
        $start = $length - $Value['size'];

        $conditions = "";
        if (array_key_exists('ip', $data)) {
            if ($data["ip"] !== "" && $data["ip"] !== null) {
                $conditions .= "WHERE [IP] LIKE '%' + :ip + '%'";
                $values["ip"] = $data['ip'];
            }
        }
        if (array_key_exists('text', $data)) {
            if ($data["text"] !== "" && $data["text"] !== null) {
                $conditions .= ($conditions === "")
                    ? "WHERE ([Name] LIKE '%' + :text1 + '%' OR 
                        [Page] LIKE '%' + :text2 + '%' OR 
                        [SQLCmd] LIKE '%' + :text3 + '%')"
                    : " AND ([Name] LIKE '%' + :text1 + '%' OR 
                        [Page] LIKE '%' + :text2 + '%' OR 
                        [SQLCmd] LIKE '%' + :text3 + '%')";
                $values["text1"] = $data['text'];
                $values["text2"] = $data['text'];
                $values["text3"] = $data['text'];
            }
        }

        //The method of implementing server-side by sql.
        //Select the total datas in current page, then select the top 5(for example) result outside the sql inside.
        $sql = "SELECT *
                FROM
                (
                    SELECT TOP {$length} ROW_NUMBER() OVER(ORDER BY [Literacy].[dbo].[View_Record].[AddTime] DESC) AS row_number, 
                        CASE WHEN [Name] IS NULL THEN '-' ELSE [Name] END AS Name,
                        CASE WHEN [RRNid] IS NULL THEN 0 ELSE [RRNid] END AS RRNid,
                        -- CASE WHEN [UID] IS NULL THEN 0 ELSE [UID] END AS UID,
                        CASE WHEN [Page] IS NULL THEN '-' ELSE [Page] END AS Page,
                        CASE WHEN [SQLCmd] IS NULL THEN '-' ELSE [SQLCmd] END AS SQLCmd,
                        CASE WHEN [AddTime] IS NULL THEN '-' ELSE [AddTime] END AS AddTime,
                        CASE WHEN [IP] IS NULL THEN '-' ELSE [IP] END AS IP
                    FROM [Literacy].[dbo].[View_Record]
                    {$conditions}
                    GROUP BY [Name],[RRNid],[UID],[Page],[SQLCmd],[IP],[AddTime]
                ) AS selection
                WHERE selection.row_number > {$start}
        ";
        $stmt = $this->container->db->prepare($sql);
        $stmt->execute($values);
        $result['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //The method of implementing server-side by sql.
        //Select the total datas in current page, then select the top 5(for example) result outside the sql inside.
        $sql = "SELECT COUNT(*) total
                FROM
                (
                    SELECT ROW_NUMBER() OVER(ORDER BY [Literacy].[dbo].[View_Record].[AddTime] DESC) AS row_number
                    , [Name],[RRNid],[UID],[Page],[SQLCmd],[IP],[AddTime]
                    FROM [Literacy].[dbo].[View_Record]
                    {$conditions}
                    GROUP BY [Name],[RRNid],[UID],[Page],[SQLCmd],[IP],[AddTime]
                    -- ORDER BY [Literacy].[dbo].[View_Record].[AddTime] DESC
                )AS count";
        $stmt = $this->container->db->prepare($sql);
        $stmt->execute($values);
        $result['total'] = $stmt->fetchColumn(0);
        return $result;
    }

    public function getExcel($data, $break = false)
    {
        $response = $data['response'];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $rowArray = [];

        $row_count = 1;
        foreach ($data['data'] as $index => $row) {
            if ($index === 0) {
                $rowArray[] = array_keys($row);
            }
            array_push($rowArray, array_values($row));
            $row_count++;
        }

        $spreadsheet->getActiveSheet()
            ->fromArray(
                $rowArray,   // The data to set
                NULL,        // Array values with this value will not be set
                'A1'         // Top left coordinate of the worksheet range where
                //    we want to set these values (default is A1)
            );
        $spreadsheet->getActiveSheet()->getStyle("A1:H{$row_count}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        if ($break) {
            $spreadsheet->getActiveSheet()->getStyle("E2:H{$row_count}")->getAlignment()->setWrapText(true);
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $response->withHeader('Content-Disposition', "attachment; filename={$data['name']}報表.xlsx");
        return $response;
    }

    public function get_exam_color($data)
    {
        $request = $data['request'];
        $response = $data['response'];
        $args = $data['args'];
        $ColorNum = $args['ColorNum'];
        $filepath = $this->container->upload_directory . DIRECTORY_SEPARATOR . 'color' . DIRECTORY_SEPARATOR . $ColorNum . '.jpg';
        $source = imagecreatefromjpeg($filepath);
        imagealphablending($source, false);
        imagesavealpha($source, true);
        imagepng($source);
        imagedestroy($source);
        $response = $response->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Type', 'image/png')
            ->withHeader('Content-Disposition', 'attachment;filename="' . $ColorNum . '.png' . '"')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public');
        return $response;
    }

    function insert_question($data)
    {
        $values = [
            "Grade" => "",
            "Scoring" => "",
            "WordQuestion" => "", //前端要先檢查
            "PL_A" => 0.00,
            "PL_B" => 0.00,
            "PL_C" => 0.00,
            "LiteracyScore" => 0,
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $flag = 0;
        $error_column = [];
        if (trim($values["WordQuestion"]) === "") {
            $error_column[] = "WordQuestion";
            $flag = 1;
        } else {
            if (strlen($values["WordQuestion"]) > 1) $values["WordQuestion"] = mb_substr($values["WordQuestion"], 0, 1);
        }
        if (trim($values["PL_A"]) === "") {
            $error_column[] = "PL_A";
            $flag = 1;
        }
        if (trim($values["PL_B"]) === "") {
            $error_column[] = "PL_B";
            $flag = 1;
        }
        if (trim($values["PL_C"]) === "") {
            $error_column[] = "PL_C";
            $flag = 1;
        }
        if ($flag !== 0) {
            return [
                "error_column" => $error_column,
                "message" => "尚有欄位未填寫，請檢查後再新增。"
            ];
        }

        $sql = "INSERT INTO [Literacy].[dbo].[Exam_Word_CV_2] ([Grade], [Scoring], [WordQuestion], [PL_A], [PL_B], [PL_C], [LiteracyScore],[Score])
                VALUES (:Grade, :Scoring, :WordQuestion, :PL_A, :PL_B, :PL_C, :LiteracyScore, 0)";
        $sth = $this->container->db->prepare($sql);
        if ($sth->execute($values)) {
            return [
                "status" => "success",
                "message" => "已新增({$values["Scoring"]})題目-{$values["Grade"]}「{$values["WordQuestion"]}」{$values["PL_A"]},{$values["PL_B"]},{$values["PL_C"]}"
            ];
        }
        return [
            "status" => "failed",
            "message" => "新增失敗"
        ];

        // GetMsg("已新增(" + rbl_Scoring.SelectedItem.Text + ")題目-" + ddl_Grade_Add.SelectedValue.ToString() + "「" + txt_WordQuestion_Add.Text + "」" + txt_PL_A_Add.Text + "," + txt_PL_B_Add.Text + "," + txt_PL_C_Add.Text + "");
        // txt_WordQuestion_Add.Text = "";
        // txt_PL_A_Add.Text = "0.00";
        // txt_PL_B_Add.Text = "0.00";
        // txt_PL_C_Add.Text = "0.00";
    }

    function score_yes($data)
    {
        $sql = "UPDATE Exam_Word_CV_2 SET Scoring = 'Y'
                WHERE ItemId = :ItemId";
        $sth = $this->container->db->prepare($sql);
        $sth->bindValue(':ItemId', $data['ItemId'], PDO::PARAM_INT);
        if ($sth->execute()) {
            return ["status" => "success"];
        }
        return ["status" => "failed"];
    }
    function scoring_n_d($data)
    {
        $sql = "UPDATE Exam_Word_CV_2 SET Scoring = :Scoring
                WHERE ItemId = :ItemId";
        $sth = $this->container->db->prepare($sql);
        $sth->bindValue(':ItemId', $data['ItemId'], PDO::PARAM_INT);
        $sth->bindValue(':Scoring', $data['Scoring'], PDO::PARAM_STR);
        if ($sth->execute()) {
            return ["status" => "success"];
        }
        return ["status" => "failed"];
    }
    function delete_question($data)
    {
        $sql = "DELETE FROM Exam_Word_CV_2 WHERE ItemId = :ItemId";
        $sth = $this->container->db->prepare($sql);
        $sth->bindValue(':ItemId', $data['ItemId'], PDO::PARAM_INT);
        if ($sth->execute()) {
            return ["status" => "刪除成功"];
        }
        return ["status" => "刪除失敗"];
    }
    function get_three_words($data)
    {
        $sql = "SELECT Grade, COUNT_BIG(WordQuestion) AS WordCount, 
                SUM(CASE WHEN ThreeWord = 'Y' THEN 1 ELSE 0 END) AS ThreeWord_Y_Count 
            FROM Exam_Word_CV_2 
            GROUP BY Grade 
            ORDER BY Grade";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute()) {
            return ["status" => "failure"];
        }
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }
    function patch_three_words($data)
    {
        $sql = "UPDATE Exam_Word_CV_2 SET ThreeWord = :three_words
                WHERE ItemId = :ItemId";
        $sth = $this->container->db->prepare($sql);
        $sth->bindValue(':ItemId', $data['ItemId'], PDO::PARAM_INT);
        $sth->bindValue(':three_words', $data['three_words'], PDO::PARAM_STR);

        if ($sth->execute()) {
            return ["status" => "設定成功"];
        }
        return ["status" => "設定失敗"];
    }
    function patch_same_three_words($data)
    {
        $sql = "UPDATE Exam_Word_CV_2 SET ThreeWord_C = :same_three_words
                WHERE ItemId = :ItemId";
        $sth = $this->container->db->prepare($sql);
        $sth->bindValue(':ItemId', $data['ItemId'], PDO::PARAM_INT);
        $sth->bindValue(':same_three_words', $data['same_three_words'], PDO::PARAM_STR);
        if ($sth->execute()) {
            return ["status" => "設定成功"];
        }
        return ["status" => "設定失敗"];
    }

    function get_three_words_detail($data)
    {
        $sql = "SELECT Grade,1 [Order],
                STUFF((
                    SELECT WordQuestion
                    FROM [Literacy].[dbo].[Exam_Word_CV_2] t 
                    WHERE ThreeWord = 'Y' AND t.Grade = [Exam_Word_CV_2].Grade
                    Order By Grade, ThetaMax ASC
                    FOR XML PATH),1,0,''
                )WordQuestion
            FROM [Literacy].[dbo].[Exam_Word_CV_2] 
            WHERE ThreeWord = 'Y' AND Grade != 'C'
            GROUP By Grade
            UNION ALL(
                SELECT 'C',0,
                    STUFF((
                        SELECT WordQuestion,*
                        FROM [Literacy].[dbo].[Exam_Word_CV_2]
                        WHERE ThreeWord_C = 'Y'
                        Order By Grade, ThetaMax ASC
                        FOR XML PATH),1,0,''
                    )WordQuestion
            )
            ORDER BY [Order],[Grade]
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute()) {
            return ["status" => "failure"];
        }
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $key_result => $value) {
            $tmpvalue = $value['WordQuestion'];
            $tmpArrs = [];
            $xml = simplexml_load_string("<a>$tmpvalue</a>");
            if ($tmpvalue == "") {
                $result[$key_result]['WordQuestion'] = $tmpArrs;
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
                $tmpArrs[] = array_shift($tmpArr);
            }
            $result[$key_result]['WordQuestion'] = implode('、', $tmpArrs);
            Endquotation:
        }
        return $result;

        /*  */

        // string RoleStr = "";
        // if (Operators.CompareString(ddl_Grade.SelectedValue, "全部", TextCompare: false) != 0)
        // {
        // 	RoleStr = RoleStr + " AND Grade = '" + ddl_Grade.SelectedValue.ToString() + "' ";
        // }
        // if (Operators.CompareString(ddl_Scoring.SelectedValue, "全部", TextCompare: false) != 0)
        // {
        // 	RoleStr = RoleStr + " AND Scoring = '" + ddl_Scoring.SelectedValue.ToString() + "' ";
        // }
        // if (Operators.CompareString(ddl_SelectKind.SelectedValue, "全部", TextCompare: false) != 0)
        // {
        // 	string left = ddl_SelectKind.SelectedValue.ToString();
        // 	if (Operators.CompareString(left, "A", TextCompare: false) == 0)
        // 	{
        // 		RoleStr += " AND ThreeWord_C = 'Y' ";
        // 	}
        // 	else if (Operators.CompareString(left, "B", TextCompare: false) == 0)
        // 	{
        // 		RoleStr += " AND ThreeWord = 'Y' ";
        // 	}
        // }
        // if (!cb_Show_N_A.Checked)
        // {
        // 	RoleStr += " AND (LEFT(Scoring, 3) <> 'N_A') ";
        // }
        // RoleStr = " WHERE 1=1 " + RoleStr;
        // SqlDS_ExamDB.SelectCommand = "SELECT * FROM Exam_Word_CV_2 " + RoleStr + " ORDER BY Grade ASC, Scoring ASC, ThetaMax ASC";
        // SqlDS_ExamDB.DataBind();
        // string ConnectionString = ConfigurationManager.AppSettings["SQLDSN"];
        // SqlConnection myConnection = new SqlConnection(ConnectionString);
        // myConnection.Open();
        // $sql = "SELECT * FROM Exam_Word_CV_2 WHERE ThreeWord = 'Y' Order By Grade, ThetaMax ASC";
        // SqlDataAdapter myCommand_ThreeWord = new SqlDataAdapter(CommandText_ThreeWord, myConnection);
        // DataTable ds_ThreeWord = new DataTable();
        // myCommand_ThreeWord.Fill(ds_ThreeWord);
        // lbl_ThreeWord_1.Text = "";
        // lbl_ThreeWord_2.Text = "";
        // lbl_ThreeWord_3.Text = "";
        // lbl_ThreeWord_4.Text = "";
        // lbl_ThreeWord_5.Text = "";
        // lbl_ThreeWord_6.Text = "";
        // checked
        // {
        // 	if (ds_ThreeWord.Rows.Count > 0)
        // 	{
        // 		int num = ds_ThreeWord.Rows.Count - 1;
        // 		int j = 0;
        // 		while (true)
        // 		{
        // 			int num2 = j;
        // 			int num3 = num;
        // 			if (num2 > num3)
        // 			{
        // 				break;
        // 			}
        // 			object left2 = ds_ThreeWord.Rows[j]["Grade"];
        // 			if (Operators.ConditionalCompareObjectEqual(left2, "1", TextCompare: false))
        // 			{
        // 				Label label = lbl_ThreeWord_1;
        // 				label.Text = Conversions.ToString(Operators.AddObject(label.Text, Operators.ConcatenateObject(ds_ThreeWord.Rows[j]["WordQuestion"], "、")));
        // 			}
        // 			else if (Operators.ConditionalCompareObjectEqual(left2, "2", TextCompare: false))
        // 			{
        // 				Label label = lbl_ThreeWord_2;
        // 				label.Text = Conversions.ToString(Operators.AddObject(label.Text, Operators.ConcatenateObject(ds_ThreeWord.Rows[j]["WordQuestion"], "、")));
        // 			}
        // 			else if (Operators.ConditionalCompareObjectEqual(left2, "3", TextCompare: false))
        // 			{
        // 				Label label = lbl_ThreeWord_3;
        // 				label.Text = Conversions.ToString(Operators.AddObject(label.Text, Operators.ConcatenateObject(ds_ThreeWord.Rows[j]["WordQuestion"], "、")));
        // 			}
        // 			else if (Operators.ConditionalCompareObjectEqual(left2, "4", TextCompare: false))
        // 			{
        // 				Label label = lbl_ThreeWord_4;
        // 				label.Text = Conversions.ToString(Operators.AddObject(label.Text, Operators.ConcatenateObject(ds_ThreeWord.Rows[j]["WordQuestion"], "、")));
        // 			}
        // 			else if (Operators.ConditionalCompareObjectEqual(left2, "5", TextCompare: false))
        // 			{
        // 				Label label = lbl_ThreeWord_5;
        // 				label.Text = Conversions.ToString(Operators.AddObject(label.Text, Operators.ConcatenateObject(ds_ThreeWord.Rows[j]["WordQuestion"], "、")));
        // 			}
        // 			else if (Operators.ConditionalCompareObjectEqual(left2, "6", TextCompare: false))
        // 			{
        // 				Label label = lbl_ThreeWord_6;
        // 				label.Text = Conversions.ToString(Operators.AddObject(label.Text, Operators.ConcatenateObject(ds_ThreeWord.Rows[j]["WordQuestion"], "、")));
        // 			}
        // 			j++;
        // 		}
        // 		if (Operators.CompareString(lbl_ThreeWord_1.Text, "", TextCompare: false) != 0)
        // 		{
        // 			lbl_ThreeWord_1.Text = Strings.Left(lbl_ThreeWord_1.Text, Strings.Len(lbl_ThreeWord_1.Text) - 1);
        // 		}
        // 		if (Operators.CompareString(lbl_ThreeWord_2.Text, "", TextCompare: false) != 0)
        // 		{
        // 			lbl_ThreeWord_2.Text = Strings.Left(lbl_ThreeWord_2.Text, Strings.Len(lbl_ThreeWord_2.Text) - 1);
        // 		}
        // 		if (Operators.CompareString(lbl_ThreeWord_3.Text, "", TextCompare: false) != 0)
        // 		{
        // 			lbl_ThreeWord_3.Text = Strings.Left(lbl_ThreeWord_3.Text, Strings.Len(lbl_ThreeWord_3.Text) - 1);
        // 		}
        // 		if (Operators.CompareString(lbl_ThreeWord_4.Text, "", TextCompare: false) != 0)
        // 		{
        // 			lbl_ThreeWord_4.Text = Strings.Left(lbl_ThreeWord_4.Text, Strings.Len(lbl_ThreeWord_4.Text) - 1);
        // 		}
        // 		if (Operators.CompareString(lbl_ThreeWord_5.Text, "", TextCompare: false) != 0)
        // 		{
        // 			lbl_ThreeWord_5.Text = Strings.Left(lbl_ThreeWord_5.Text, Strings.Len(lbl_ThreeWord_5.Text) - 1);
        // 		}
        // 		if (Operators.CompareString(lbl_ThreeWord_6.Text, "", TextCompare: false) != 0)
        // 		{
        // 			lbl_ThreeWord_6.Text = Strings.Left(lbl_ThreeWord_6.Text, Strings.Len(lbl_ThreeWord_6.Text) - 1);
        // 		}
        // 	}
        // 	string CommandText_ThreeWord_C = "SELECT * FROM Exam_Word_CV_2 WHERE ThreeWord_C = 'Y' Order By Grade, ThetaMax ASC";
        // 	SqlDataAdapter myCommand_ThreeWord_C = new SqlDataAdapter(CommandText_ThreeWord_C, myConnection);
        // 	DataTable ds_ThreeWord_C = new DataTable();
        // 	myCommand_ThreeWord_C.Fill(ds_ThreeWord_C);
        // 	myConnection.Close();
        // 	lbl_ThreeWord_C.Text = "";
        // 	if (ds_ThreeWord_C.Rows.Count <= 0)
        // 	{
        // 		return;
        // 	}
        // 	int num4 = ds_ThreeWord_C.Rows.Count - 1;
        // 	int i = 0;
        // 	while (true)
        // 	{
        // 		int num5 = i;
        // 		int num3 = num4;
        // 		if (num5 > num3)
        // 		{
        // 			break;
        // 		}
        // 		Label label = lbl_ThreeWord_C;
        // 		label.Text = Conversions.ToString(Operators.AddObject(label.Text, Operators.ConcatenateObject(ds_ThreeWord_C.Rows[i]["WordQuestion"], "、")));
        // 		i++;
        // 	}
        // 	lbl_ThreeWord_C.Text = Strings.Left(lbl_ThreeWord_C.Text, Strings.Len(lbl_ThreeWord_C.Text) - 1);
        // }
        /*  */
    }

    function update_3PL_and_literacy_score($data)
    {
        foreach ($data as $key => $value) {
            if (trim($value["PL_A"]) === "" || trim($value["PL_A"]) === "0" || trim($value["PL_B"]) === "" || trim($value["PL_B"]) === "0" || trim($value["PL_C"]) === "" || trim($value["PL_C"]) === "0" || trim($value["LiteracyScore"]) === "" || trim($value["LiteracyScore"]) === "0") {
                unset($data[$key]);
            }
        }
        // return $data;

        $statement = "";
        $values = [];
        $result = [];
        $count = 0;
        $first = 0;
        foreach ($data as $key => $value) {
            $statement .= "(:ItemId_{$key}, :PL_A_{$key}, :PL_B_{$key}, :PL_C_{$key}, :LiteracyScore_{$key}),";
            // if($first === 0) {
            //     $statement .= "SELECT :ItemId_{$key} AS ItemId, :PL_A_{$key} AS PL_A, :PL_B_{$key} AS PL_B, :PL_C_{$key} AS PL_C, :LiteracyScore_{$key} AS LiteracyScore UNION ALL ";
            //     $first = 1;
            // }
            // else{
            //     $statement .= "SELECT :ItemId_{$key}, :PL_A_{$key}, :PL_B_{$key}, :PL_C_{$key}, :LiteracyScore_{$key} UNION ALL ";
            // }
            // UPDATE students s
            // JOIN (
            //     SELECT 1 as id, 5 as new_score1, 8 as new_score2
            //     UNION ALL
            //     SELECT 2, 10, 8
            //     UNION ALL
            //     SELECT 3, 8, 3
            //     UNION ALL
            //     SELECT 4, 10, 7
            // ) vals ON s.id = vals.id
            // SET score1 = new_score1, score2 = new_score2;

            $temp_column = [
                "ItemId" => 0,
                "PL_A" => 0,
                "PL_B" => 0,
                "PL_C" => 0,
                "LiteracyScore" => 0,
            ];
            foreach ($temp_column as $temp_key => $temp_value) {
                $temp_column[$temp_key . "_{$key}"] = $temp_value;
                if (array_key_exists($temp_key, $value)) {
                    $temp_column[$temp_key . "_{$key}"] = $value[$temp_key];
                }
                unset($temp_column[$temp_key]);
            }
            $values = array_merge($temp_column, $values);
            $count++;

            if ($count === 50) {
                // $statement = rtrim($statement, "UNION ALL ");
                // $sql = "UPDATE Exam_Word_CV_2 exam
                //         JOIN (
                //             {$statement}
                //         ) change ON exam.ItemId = change.ItemId
                //         SET PL_A = change.PL_A, PL_B = change.PL_B, PL_C = change.PL_C, LiteracyScore = change.LiteracyScore";
                $statement = rtrim($statement, ",");
                $sql = "UPDATE Exam_Word_CV_2
                        SET PL_A = CAST(change.PL_A AS FLOAT), PL_B = CAST(change.PL_B AS FLOAT), PL_C = CAST(change.PL_C AS FLOAT), LiteracyScore = CAST(change.LiteracyScore AS INTEGER)
                        FROM 
                        (
                            VALUES {$statement}
                        ) AS change(ItemId, PL_A, PL_B, PL_C, LiteracyScore) 
                        WHERE CAST(change.ItemId AS INTEGER) = Exam_Word_CV_2.ItemId";
                $sth = $this->container->db->prepare($sql);
                if ($sth->execute($values)) {
                    $statement = "";
                    $values = [];
                    $count = 0;
                    $first = 0;
                } else {
                    var_dump($sth->errorInfo());
                    array_push($result, ["status" => "failed"]);
                    return $result;
                }
            }
        }
        if ($count !== 0) {
            // $statement = rtrim($statement, "UNION ALL ");
            // $sql = "UPDATE Exam_Word_CV_2 exam
            //         JOIN (
            //             {$statement}
            //         ) change ON exam.ItemId = change.ItemId
            //         SET PL_A = change.PL_A, PL_B = change.PL_B, PL_C = change.PL_C, LiteracyScore = change.LiteracyScore";
            $statement = rtrim($statement, ",");
            $sql = "UPDATE Exam_Word_CV_2
                    SET PL_A = CAST(change.PL_A AS FLOAT), PL_B = CAST(change.PL_B AS FLOAT), PL_C = CAST(change.PL_C AS FLOAT), LiteracyScore = CAST(change.LiteracyScore AS INTEGER)
                    FROM 
                    (
                        VALUES {$statement}
                    ) AS change(ItemId, PL_A, PL_B, PL_C, LiteracyScore) 
                    WHERE CAST(change.ItemId AS INTEGER) = Exam_Word_CV_2.ItemId";
            $sth = $this->container->db->prepare($sql);
            if ($sth->execute($values)) {
                $statement = "";
                $values = [];
                $count = 0;
                $first = 0;
            } else {
                var_dump($sth->errorInfo());
                array_push($result, ["status" => "failed"]);
                return $result;
            }
        }
        return $result;
    }
    function reset_counter()
    {
        $sql = "UPDATE Exam_Word_CV_2 SET Counter = 0";
        $sth = $this->container->db->prepare($sql);
        if ($sth->execute()) {
            return ["status" => "成功歸零"];
        }
        return ["status" => "歸零失敗"];
    }

    function get_school_msg_list($data)
    {
        $values = [
            "city_id" => 'AND CityId = :city_id ',
            "post_id" => 'AND PostId = :post_id ',
            "school_name" => "AND SchoolName LIKE :school_name"
        ];

        $condition = "";

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $condition .= $value;
                if ($key == "school_name") {
                    $values["school_name"] = "%" . $data[$key] . "%";
                } else {
                    $values[$key] = $data[$key];
                }
            } else {
                unset($values[$key]);
            }
        }

        if (count($values) != 0) {
            $condition = "WHERE " . ltrim($condition, "AND");
        }

        $sql = "SELECT * 
                FROM SchoolList 
                $condition
                Order By [CityId], [PostId], [Class], [SchoolName]";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    function get_school_msg_upinfo($data)
    {
        $values = [
            "sid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [UpInfo]
                FROM [SchoolList] 
                WHERE [Sid] = :sid
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function admin_go_school($data)
    {
        $values = [
            "sid" => ''
        ];

        $condition = "";

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = 'SELECT [Sid], [SchoolName], [Class]
                FROM [Literacy].[dbo].[SchoolList]
                WHERE [Sid] = :sid 
                ';
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $status = ["status" => "failed"];
        foreach ($result as $key => $value) {
            $_SESSION['Sid'] = $value['Sid'];
            $_SESSION['Class'] = $value['Class'];
            $_SESSION['login'] = 0;
            $status = ["status" => "success"];
        }
        return $status;
    }

    function patch_school_list($data)
    {
        $condition = "";

        $values = [
            "Used" => "Used = :Used,",
            "ExamPower" => "ExamPower = :ExamPower,",
            "MsgRM" => "MsgRM = :MsgRM,",
            "MasterRM" => "MasterRM = :MasterRM,",
            "ExamProgramKind" => "ExamProgramKind = :ExamProgramKind,"
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $condition .= $value;
                $values[$key] = $data[$key];
            } else {
                unset($values[$key]);
            }
        }

        $condition = rtrim($condition, ",");
        $values['s_id'] = $data['s_id'];

        $sql = "UPDATE SchoolList 
                SET $condition
                WHERE Sid = :s_id";


        $sth = $this->container->db->prepare($sql);
        if ($sth->execute($values)) {
            return ["status" => "設定成功"];
        }
        return ["status" => "設定失敗"];
    }

    function get_admin_option_datas($data)
    {
        if (array_key_exists('OptionItem', $data)) {

            $sql = "SELECT * 
            FROM Options 
            WHERE (OptionItem = :OptionItem)";

            $sth = $this->container->db->prepare($sql);
            $sth->bindValue(':OptionItem', $data['OptionItem'], PDO::PARAM_STR);

            $sth->execute();
            $result = $sth->fetchAll(PDO::FETCH_ASSOC);

            return $result;
        } else {
            return [
                "status" => "failed"
            ];
        }
    }

    function patch_admin_options($data)
    {
        if ($data['option'] == 'Exam') {

            $sql = "UPDATE Options 
                    SET State=:State, OItemDateS=:OItemDateS, OItemDateE=:OItemDateE
                    WHERE (OptionItem = 'Exam')";

            $sth = $this->container->db->prepare($sql);
            $sth->bindValue(':State', $data['State'], PDO::PARAM_STR);
            $sth->bindValue(':OItemDateS', $data['OItemDateS'], PDO::PARAM_STR);
            $sth->bindValue(':OItemDateE', $data['OItemDateE'], PDO::PARAM_STR);

            if ($sth->execute()) {
                return ["status" => "設定成功"];
            }
            return ["status" => "設定失敗"];
        } else {
            $sql = "UPDATE Options 
                    SET OptionValues = :OptionValues 
                    WHERE (OptionItem = :option)";

            $sth = $this->container->db->prepare($sql);
            $sth->bindValue(':option', $data['option'], PDO::PARAM_STR);
            $sth->bindValue(':OptionValues', $data['OptionValues'], PDO::PARAM_STR);
            if ($sth->execute()) {
                return ["status" => "設定成功"];
            }
            return ["status" => "設定失敗"];
        }
    }

    public function apply_pdf($data)
    {

        $values = [
            "txt_Principal" => '',
            "SchoolName" => '',
            "txt_Senate" => '',
            "txt_Contact" => '',
            "txt_Contact_Titles" => '',
            "txt_Contact_Phone" => '',
            "txt_Contact_EMail_1" => '',
            "verification" => '',
            'School_C_1' => 0,
            'School_C_2' => 0,
            'School_C_3' => 0,
            'School_C_4' => 0,
            'School_C_5' => 0,
            'School_C_6' => 0,
            'School_C_Other' => 0,
            'School_C_Total' => 0,
            'School_S_1' => 0,
            'School_S_2' => 0,
            'School_S_3' => 0,
            'School_S_4' => 0,
            'School_S_5' => 0,
            'School_S_6' => 0,
            'School_S_Other' => 0,
            'School_S_Total' => 0,
            'Exam_C_1' => '',
            'Exam_C_2' => '',
            'Exam_C_3' => '',
            'Exam_C_4' => '',
            'Exam_C_5' => '',
            'Exam_C_6' => '',
            'Exam_C_Other' => '',
            'Exam_C_Total' => '',
            'Exam_S_1' => '',
            'Exam_S_2' => '',
            'Exam_S_3' => '',
            'Exam_S_4' => '',
            'Exam_S_5' => '',
            'Exam_S_6' => '',
            'Exam_S_Other' => '',
            'Exam_S_Total' => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_RIGHT);

        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        $font = TCPDF_FONTS::addTTFfont(__DIR__ . DIRECTORY_SEPARATOR . "/fonts/droidsansfallback.ttf", "TrueTypeUnicode", "", 96);
        $pdf->SetFont($font, '', 14, '', true);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        // set text shadow effect
        $pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

        // Set some content to print
        $html = <<<EOD
        <!DOCTYPE html>
        <html>
        <head>
        <style>
        table, th, td {
        border: 1px solid black;
        }
        </style>
        </head>
        <body>

        <h1>施測申請書</h1>


        <p>本校_________{$values['SchoolName']}__________(以下簡稱甲方)</p>
        <p>擬向國立高雄師範大學「閱讀評量與教學中心」(以下簡稱乙方)申請乙方所開發的識字量施測平台系統。</p>
        <p>申請學校:______{$values['SchoolName']}_______</p>
        <p>連 絡 人:_______{$values['txt_Contact']}_____</p>
        <p>職   稱:______{$values['txt_Contact_Titles']}_______</p>
        <p>連絡電話:(公)____{$values['txt_Contact_Phone']}_____ (手機)________________</p>
        <p>Email(1):________{$values['txt_Contact_EMail_1']}______ Email(2):</p>
        <br/>
        <h3>學校班級及人數:</h3>
        <table border="2" style="width:100%; font-size:13pt;">
        <tr>
            <th style="width:11%"></th>
            <th style="width:11%">一年級</th>
            <th style="width:11%">二年級</th>
            <th style="width:11%">三年級</th>
            <th style="width:11%">四年級</th>
            <th style="width:11%">五年級</th>
            <th style="width:11%">六年級</th>
            <th style="width:12%">其他班級</th>
            <th style="width:11%">合計</th>
        </tr>
        <tr>
            <th style="width:11%"> 班級: </th>
            <th style="width:11%">{$values['School_C_1']}</th>
            <th style="width:11%">{$values['School_C_2']}</th>
            <th style="width:11%">{$values['School_C_3']}</th>
            <th style="width:11%">{$values['School_C_4']}</th>
            <th style="width:11%">{$values['School_C_5']}</th>
            <th style="width:11%">{$values['School_C_6']}</th>
            <th style="width:12%">{$values['School_C_Other']}</th>
            <th style="width:11%">{$values['School_C_Total']}</th>
        </tr>
        <tr>
            <th style="width:11%"> 人數: </th>
            <th style="width:11%">{$values['School_S_1']}</th>
            <th style="width:11%">{$values['School_S_2']}</th>
            <th style="width:11%">{$values['School_S_3']}</th>
            <th style="width:11%">{$values['School_S_4']}</th>
            <th style="width:11%">{$values['School_S_5']}</th>
            <th style="width:11%">{$values['School_S_6']}</th>
            <th style="width:12%">{$values['School_S_Other']}</th>
            <th style="width:11%">{$values['School_S_Total']}</th>
        </tr>
        </table>
        <h3>預訂施測班級及人數:</h3>

        <table border="2" style="width:100%; font-size:13pt;">
            <tr>
            <th style="width:11%"></th>
            <th style="width:11%">一年級</th>
            <th style="width:11%">二年級</th>
            <th style="width:11%">三年級</th>
            <th style="width:11%">四年級</th>
            <th style="width:11%">五年級</th>
            <th style="width:11%">六年級</th>
            <th style="width:12%">其他班級</th>
            <th style="width:11%">合計</th>
        </tr>
        <tr>
            <th style="width:11%"> 班級: </th>
            <th style="width:11%">{$values['Exam_C_1']}</th>
            <th style="width:11%">{$values['Exam_C_2']}</th>
            <th style="width:11%">{$values['Exam_C_3']}</th>
            <th style="width:11%">{$values['Exam_C_4']}</th>
            <th style="width:11%">{$values['Exam_C_5']}</th>
            <th style="width:11%">{$values['Exam_C_6']}</th>
            <th style="width:12%">{$values['Exam_C_Other']}</th>
            <th style="width:11%">{$values['Exam_C_Total']}</th>
        </tr>
        <tr>
            <th style="width:11%"> 人數: </th>
            <th style="width:11%">{$values['Exam_S_1']}</th>
            <th style="width:11%">{$values['Exam_S_2']}</th>
            <th style="width:11%">{$values['Exam_S_3']}</th>
            <th style="width:11%">{$values['Exam_S_4']}</th>
            <th style="width:11%">{$values['Exam_S_5']}</th>
            <th style="width:11%">{$values['Exam_S_6']}</th>
            <th style="width:12%">{$values['Exam_S_Other']}</th>
            <th style="width:11%">{$values['Exam_S_Total']}</th>
        </tr>
        </table>
            <br/>
            <h3>※家長同意書:</h3>
            <div style="font-size:12pt;"> 請至識字量網站( <a href="http://pair.nknu.edu.tw/literacy/Default.aspx" target="_blank" class="redlink" >http://pair.nknu.edu.tw/literacy/Default.aspx </a> )頁面，點選左側的 </div>
            <div style="font-size:12pt;">【資訊區-資料下載】: 第二點「家長同意書下載」。</div>
            <div style="text-align:center; font-size:15pt;"><h5>※上述甲方所寫資料一切屬實，受測學生家長所填寫的家長同意書交由甲方收執保存；如有不實願負法律責任※</h5></div>

        </body>
        </html>
        EOD;

        // Print text using writeHTMLCell()
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        // ---------------------------------------------------------

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        $result = base64_encode($pdf->Output("{$values['SchoolName']}-施測申請書.pdf", 'S'));
        return [
            'status' => 'success',
            'message' => '匯出成功',
            'file' => $result
        ];
    }
    public function get_learn_word_library_excel($data)
    {
        $values = [
            'word' => null
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $query = "";
        if (!is_null($values['word'])) {
            $query = "WHERE [Learn_Word_Romanization].[word] = :word";
        } else {
            unset($values['word']);
        }
        $sql = "WITH sentence_each AS (
                SELECT [Learn_Sentence].[ans],[Learn_Word_Sentence].[nid]
                , ROW_NUMBER() OVER (PARTITION BY [Learn_Word_Sentence].[nid] ORDER BY [Learn_Word_Sentence].[sentence_id] ASC) row_number
                FROM [Literacy].[dbo].[Learn_Word_Sentence]
                LEFT JOIN [Literacy].[dbo].[Learn_Sentence] ON [Learn_Word_Sentence].[sentence_id] = [Learn_Sentence].[sentence_id]
            ),example AS (
                SELECT [Learn_Sentence].[sentence],[Learn_Word_Sentence].[nid] ,ROW_NUMBER() OVER (PARTITION BY [Learn_Word_Sentence].[nid] ORDER BY [Learn_Word_Sentence].[sentence_id] ASC) row_number
                FROM [Literacy].[dbo].[Learn_Word_Sentence]
                LEFT JOIN [Literacy].[dbo].[Learn_Sentence] ON [Learn_Word_Sentence].[sentence_id] = [Learn_Sentence].[sentence_id]
            )
            SELECT [Learn_Word_Romanization].[word] 生字
            , [Learn_Word_Romanization].[bopomo] 注音
            , [Learn_Word_Romanization].[romanization] 漢語拼音
            , [Learn_Word_Romanization].[part] 部首
            , STUFF((
                SELECT CAST([Learn_Word_Romanization].[part_stroke] AS varchar)
                FROM [Literacy].[dbo].[Learn_Listenword]
                WHERE [Learn_Listenword].[nid] = [Learn_Word_Romanization].[nid]
            FOR XML PATH(''),TYPE).value('(./text())[1]','VARCHAR(MAX)')
            ,1,0,'') 部首外筆畫
            , STUFF((
                SELECT CAST([Learn_Word_Romanization].[stroke] AS varchar)
                FROM [Literacy].[dbo].[Learn_Listenword]
                WHERE [Learn_Listenword].[nid] = [Learn_Word_Romanization].[nid]
            FOR XML PATH(''),TYPE).value('(./text())[1]','VARCHAR(MAX)')
            ,1,0,'') 總筆畫
            , STUFF((
                SELECT CAST([Learn_Word_Romanization].[stroke]-[Learn_Word_Romanization].[part_stroke] AS varchar)
                FROM [Literacy].[dbo].[Learn_Listenword]
                WHERE [Learn_Listenword].[nid] = [Learn_Word_Romanization].[nid]
            FOR XML PATH(''),TYPE).value('(./text())[1]','VARCHAR(MAX)')
            ,1,0,'') 部首筆畫
            , STUFF((
                SELECT CAST(convert(varchar, [choose])  AS VARCHAR(MAX))
                FROM [Literacy].[dbo].[Learn_Listenword]
                WHERE [Learn_Listenword].[nid] = [Learn_Word_Romanization].[nid]
            FOR XML PATH(''),TYPE).value('(./text())[1]','VARCHAR(MAX)')
            ,1,0,'') 聽音辨字
            ,(
                SELECT sentence_each.[ans]
                FROM sentence_each
                WHERE [Learn_Word_Romanization].[nid] = sentence_each.[nid] AND sentence_each.row_number=1
            ) 詞彙1
            ,(
                SELECT sentence_each.[ans]
                FROM sentence_each
                WHERE [Learn_Word_Romanization].[nid] = sentence_each.[nid] AND sentence_each.row_number=2
            ) 詞彙2
            ,(
                SELECT sentence_each.[ans]
                FROM sentence_each
                WHERE [Learn_Word_Romanization].[nid] = sentence_each.[nid] AND sentence_each.row_number=3
            ) 詞彙3
            , (
                SELECT example.sentence 
                FROM example
                WHERE [Learn_Word_Romanization].[nid] = example.[nid] AND example.row_number=1
            ) 例句1
            , (
                SELECT example.sentence 
                FROM example
                WHERE [Learn_Word_Romanization].[nid] = example.[nid] AND example.row_number=2
            ) 例句2
            , (
                SELECT example.sentence 
                FROM example
                WHERE [Learn_Word_Romanization].[nid] = example.[nid] AND example.row_number=3
            ) 例句3
            FROM [Literacy].[dbo].[Learn_Word_Romanization]
            {$query}
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ['status' => 'failure'];
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_version_library_excel($data)
    {

        $values = [];

        $string = "";
        $string_word = "";
        $check = false;

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (array_key_exists('Year', $data)) {
            $string .= " AND [Learn_Version].[Year] = :Year";
            $values['Year'] = $data['Year'];
            $check = true;
        }
        if (array_key_exists('Grade', $data)) {
            $string .= " AND [Learn_Version].[Grade] = :Grade";
            $values['Grade'] = $data['Grade'];
            $check = true;
        }
        if (array_key_exists('Version', $data)) {
            $string .= " AND [Learn_Version].[Version] = :Version";
            $values['Version'] = $data['Version'];
            $check = true;
        }
        if (array_key_exists('Lesson', $data)) {
            $string .= " AND [Learn_Version].[Lesson] = :Lesson";
            $values['Lesson'] = $data['Lesson'];
            $check = true;
        }
        if (array_key_exists('Term', $data)) {
            $string .= " AND [Learn_Version].[Term] = :Term";
            $values['Term'] = $data['Term'];
            $check = true;
        }
        if (array_key_exists('Word', $data)) {
            $string_word .= " WHERE dt.List LIKE '%'+ :Word +'%' ";
            $values['Word'] = $data['Word'];
        }

        if ($check) {
            $string = "WHERE " . ltrim($string, ' AND');
        }

        $sql = "SELECT *
                    FROM(
                        SELECT ROW_NUMBER() OVER ( ORDER BY [Learn_Version].[Lid] ) AS '序號'
                        , [Learn_Version].[Version] '版本'
                        , [Learn_Version].[Year] '年度'
                        , [Learn_Version].[Grade] '年級'
                        , [Learn_Version].[Term] '學期'
                        , [Learn_Version].[Lesson] '第幾課'
                        , [Learn_Version].[LessonName] '課程名稱'
                        , STUFF( 
                            (
                                SELECT ',' + CAST([Learn_Word_Romanization].[word] AS VARCHAR(MAX))
                                FROM [Literacy].[dbo].[Learn_Version_Word] 
                                LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Version_Word].[nid] = [Learn_Word_Romanization].[nid]
                                WHERE [Learn_Version_Word].[Lid] = [Learn_Version].[Lid]
                                FOR XML PATH ('')
                            ), 1, 1, ''
                        ) AS '該課生字'
                        FROM [Literacy].[dbo].[Learn_Version] 
                        {$string}
                        )dt
                        {$string_word}
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result['data'] = $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_color($data)
    {
        $values = [];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }

        $sql = "SELECT [color_id]
                ,[color_name]
                ,[color_score]
                ,[color_img]
            FROM [Literacy].[dbo].[Learn_Color]
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ['status' => 'failure'];
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_word_version($data)
    {
        $values = [];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "SELECT [Learn_Version].[Version]
        FROM [Literacy].[dbo].[Learn_Version]
        GROUP BY [Learn_Version].[Version]
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ['status' => 'failure'];
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_word_grade($data)
    {
        $values = [];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "SELECT [Learn_Version].[Grade]
                    ,[Learn_Version].[Term]
                    , [Learn_Version].[Grade]+'年級('+[Learn_Version].[Term]+')' text
                FROM [Literacy].[dbo].[Learn_Version]
                GROUP BY [Learn_Version].[Grade],[Learn_Version].[Term] 
                ORDER BY CASE [Grade]
                    WHEN '一' THEN 1
                    WHEN '二' THEN 2
                    WHEN '三' THEN 3
                    WHEN '四' THEN 4
                    WHEN '五' THEN 5
                    WHEN '六' THEN 6
                    WHEN '七' THEN 7
                    WHEN '八' THEN 8
                    WHEN '九' THEN 9
                    ELSE 99999 END,
                    CASE [Term]
                    WHEN '上' THEN 1
                    WHEN '下' THEN 2
                    ELSE 99999 END
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ['status' => 'failure'];
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_word_unit($data)
    {
        $values = [];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "SELECT [Learn_Version].[Lesson] 
                FROM [Literacy].[dbo].[Learn_Version]
                GROUP BY [Learn_Version].[Lesson] 
                ORDER BY 
                    CASE SUBSTRING([Learn_Version].[Lesson],2,1)
                        WHEN '一' THEN 1
                        WHEN '二' THEN 2
                        WHEN '三' THEN 3
                        WHEN '四' THEN 4
                        WHEN '五' THEN 5
                        WHEN '六' THEN 6
                        WHEN '七' THEN 7
                        WHEN '八' THEN 8
                        WHEN '九' THEN 9
                        WHEN '十' THEN 
							CASE SUBSTRING([Learn_Version].[Lesson],3,1)
								WHEN '課' THEN 10
								WHEN '一' THEN 11
								WHEN '二' THEN 12
								WHEN '三' THEN 13
								WHEN '四' THEN 14
								WHEN '五' THEN 15
								WHEN '六' THEN 16
							END
                    END
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ['status' => 'failure'];
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_word_part($data)
    {
        $values = [];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "SELECT [Learn_Word_Romanization].[part]
            FROM [Literacy].[dbo].[Learn_Word_Romanization]
            GROUP BY [Learn_Word_Romanization].[part] 
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ['status' => 'failure'];
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_word_part_stroke($data)
    {
        $values = [];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "SELECT [Learn_Word_Romanization].[part_stroke]
                FROM [Literacy].[dbo].[Learn_Word_Romanization]
                GROUP BY [Learn_Word_Romanization].[part_stroke]
                ORDER BY [part_stroke]
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ['status' => 'failure'];
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $index => $row) {
            foreach ($row as $key => $value) {
                $result[$index]['text'] = $this->numToWord($value) . '劃';
            }
        }
        return $result;
    }

    public function get_learn_report_task_detail_teacher($data)
    {
        $values = [
            "Tid" => 0
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "SELECT SUM(COALESCE([group].[group_task],0)) [教師指派任務數],
                SUM(COALESCE([group].[group_finish],0)) [教師指派任務完成數], 
                COALESCE(SUM(COALESCE([group].[group_finish],0))/NULLIF(SUM(COALESCE([group].[group_task],0)),0)*100,0) [教師指派任務完成率],
                SUM(COALESCE([self].self_task,0)) [自主學習任務數],
                SUM(COALESCE([self].self_finish,0)) [自主學習任務完成數],
                COALESCE(SUM(COALESCE([self].self_finish,0))/NULLIF(SUM(COALESCE([self].self_task,0)),0)*100,0) [自主學習任務完成率],
                [Student].[Year] [入學年],
                [Student].[StuNum] [學號],
                [Student].[SeatNum] [座號],
                [Student].[StuName] [姓名]
            FROM [Literacy].[dbo].[Student]
            LEFT JOIN(
                SELECT dt.Pid,COUNT(*)group_task,COUNT(CASE WHEN dt.is_finish = 1 THEN 1 END) group_finish
                FROM(
                    SELECT [Learn_Group_Task].[TaskId],[Learn_Teacher_Student].[Pid],
                        CASE WHEN COUNT(CASE WHEN step IS NULL OR step != 9 THEN 1 END)>0 THEN 0 ELSE 1 END is_finish
                    FROM [Literacy].[dbo].[Learn_Group_Task] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task].[GroupId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON 
                        [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                        AND [Learn_Task_Word_Student].[Pid] = [Learn_Teacher_Student].[Pid]
                    GROUP BY [Learn_Group_Task].[TaskId],[Learn_Teacher_Student].[Pid]
                )dt
                GROUP BY dt.Pid
            )[group] ON [group].[Pid] = [Student].[Pid]
            LEFT JOIN(
                SELECT dt.Pid,COUNT(*)self_task,COUNT(CASE WHEN dt.is_finish = 1 THEN 1 END) self_finish
                FROM(
                    SELECT [Learn_Task].[TaskId],[Learn_Task].[Pid],
                        CASE WHEN COUNT(CASE WHEN step IS NULL OR step != 9 THEN 1 END)>0 THEN 0 ELSE 1 END is_finish
                    FROM [Literacy].[dbo].[Learn_Task] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON 
                        [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                        AND [Learn_Task_Word_Student].[Pid] = [Learn_Task].[Pid]
                    GROUP BY [Learn_Task].[TaskId],[Learn_Task].[Pid]
                )dt
                GROUP BY dt.Pid
            )[self] ON [self].[Pid] = [Student].[Pid]
            INNER JOIN [Literacy].[dbo].[Teacher] ON [Student].[Tid] = [Teacher].[Tid]
            INNER JOIN [Literacy].[dbo].[SchoolList] ON [SchoolList].[Sid] = [Student].[Sid]
            WHERE [Teacher].[Tid] = :Tid
            GROUP BY [Student].[StuName],[Student].[SeatNum],[Student].[StuNum],[Student].[Pid],[Student].[Year],[Teacher].[Grade],[Teacher].[Class],[Student].[Tid]
            ORDER BY [Student].[Year],[Teacher].[Grade],[Teacher].[Class]
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ['status' => 'failure'];
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_report_task_detail($data)
    {
        $filter_string = "";
        $values = [
            'CityId' => null,
            'PostId' => null,
            'Sid' => null,
            'SchoolName' => null,
            'Grade' => null,
            'Class' => null,
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }

        if (is_null($values['CityId']))
            unset($values['CityId']);
        else {
            $filter_string .= " AND [Literacy].[dbo].[SchoolList].[CityId] = :CityId";
        }

        if (is_null($values['PostId']))
            unset($values['PostId']);
        else {
            $filter_string .= " AND [Literacy].[dbo].[SchoolList].[PostId] = :PostId";
        }

        if (is_null($values['Sid']))
            unset($values['Sid']);
        else {
            $filter_string .= " AND [Literacy].[dbo].[SchoolList].[Sid] = :Sid";
        }

        if (is_null($values['SchoolName']))
            unset($values['SchoolName']);
        else {
            $filter_string .= " AND [Literacy].[dbo].[SchoolList].[SchoolName] LIKE '%' + :SchoolName + '%'";
        }

        if (is_null($values['Grade']))
            unset($values['Grade']);
        else {
            $filter_string .= " AND [Literacy].[dbo].[Teacher].[Grade] = :Grade";
        }

        if (is_null($values['Class']))
            unset($values['Class']);
        else {
            $filter_string .= " AND [Literacy].[dbo].[Teacher].[Class] = :Class";
        }

        $sql = "SELECT SUM(COALESCE([group].[group_task],0)) [教師指派任務數],
                SUM(COALESCE([group].[group_finish],0)) [教師指派任務完成數], 
                COALESCE(SUM(COALESCE([group].[group_finish],0))/NULLIF(SUM(COALESCE([group].[group_task],0)),0)*100,0) [教師指派任務完成率],
                SUM(COALESCE([self].self_task,0)) [自主學習任務數],
                SUM(COALESCE([self].self_finish,0)) [自主學習任務完成數],
                COALESCE(SUM(COALESCE([self].self_finish,0))/NULLIF(SUM(COALESCE([self].self_task,0)),0)*100,0) [自主學習任務完成率],
                [Student].[Year] [入學年],
                [Teacher].[Grade]+'年'+[Teacher].[Class]+'班' [班級],
                [SchoolList].[CityId] [縣市],
                [SchoolList].[PostId] [區域],
                [SchoolList].[SchoolName] [學校],
                COUNT(*) [學生數],
                [Student].[Tid]
            FROM [Literacy].[dbo].[Student]
            LEFT JOIN(
                SELECT dt.Pid,COUNT(*)group_task,COUNT(CASE WHEN dt.is_finish = 1 THEN 1 END) group_finish
                FROM(
                    SELECT [Learn_Group_Task].[TaskId],[Learn_Teacher_Student].[Pid],
                        CASE WHEN COUNT(CASE WHEN step IS NULL OR step != 9 THEN 1 END)>0 THEN 0 ELSE 1 END is_finish
                    FROM [Literacy].[dbo].[Learn_Group_Task] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task].[GroupId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON 
                        [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                        AND [Learn_Task_Word_Student].[Pid] = [Learn_Teacher_Student].[Pid]
                    GROUP BY [Learn_Group_Task].[TaskId],[Learn_Teacher_Student].[Pid]
                )dt
                GROUP BY dt.Pid
            )[group] ON [group].[Pid] = [Student].[Pid]
            LEFT JOIN(
                SELECT dt.Pid,COUNT(*)self_task,COUNT(CASE WHEN dt.is_finish = 1 THEN 1 END) self_finish
                FROM(
                    SELECT [Learn_Task].[TaskId],[Learn_Task].[Pid],
                        CASE WHEN COUNT(CASE WHEN step IS NULL OR step != 9 THEN 1 END)>0 THEN 0 ELSE 1 END is_finish
                    FROM [Literacy].[dbo].[Learn_Task] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON 
                        [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                        AND [Learn_Task_Word_Student].[Pid] = [Learn_Task].[Pid]
                    GROUP BY [Learn_Task].[TaskId],[Learn_Task].[Pid]
                )dt
                GROUP BY dt.Pid
            )[self] ON [self].[Pid] = [Student].[Pid]
            INNER JOIN [Literacy].[dbo].[Teacher] ON [Student].[Tid] = [Teacher].[Tid]
            INNER JOIN [Literacy].[dbo].[SchoolList] ON [SchoolList].[Sid] = [Student].[Sid]
            WHERE 1 = 1 {$filter_string}
            GROUP BY [Student].[Tid],[Student].[Year],[Teacher].[Grade],[Teacher].[Class],[SchoolList].[CityId],[SchoolList].[PostId],[SchoolList].[SchoolName]
            ORDER BY [SchoolList].[CityId] DESC,[SchoolList].[PostId],[SchoolList].[SchoolName],[Student].[Year],[Teacher].[Grade],[Teacher].[Class]
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ['status' => 'failure'];
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_report_task_word($data)
    {
        $filter_string = "";
        $values = [
            'CityId' => null,
            'PostId' => null,
            'Sid' => null,
            'SchoolName' => null,
            'Grade' => null,
            'Class' => null,
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }

        if (is_null($values['CityId']))
            unset($values['CityId']);
        else {
            $filter_string .= " AND [Literacy].[dbo].[SchoolList].[CityId] = :CityId";
        }

        if (is_null($values['PostId']))
            unset($values['PostId']);
        else {
            $filter_string .= " AND [Literacy].[dbo].[SchoolList].[PostId] = :PostId";
        }

        if (is_null($values['Sid']))
            unset($values['Sid']);
        else {
            $filter_string .= " AND [Literacy].[dbo].[SchoolList].[Sid] = :Sid";
        }

        if (is_null($values['SchoolName']))
            unset($values['SchoolName']);
        else {
            $filter_string .= " AND [Literacy].[dbo].[SchoolList].[SchoolName] LIKE '%' + :SchoolName + '%'";
        }

        if (is_null($values['Grade']))
            unset($values['Grade']);
        else {
            $filter_string .= " AND [Literacy].[dbo].[Teacher].[Grade] = :Grade";
        }

        if (is_null($values['Class']))
            unset($values['Class']);
        else {
            $filter_string .= " AND [Literacy].[dbo].[Teacher].[Class] = :Class";
        }

        $sql = "SELECT (SUM(COALESCE([group].[group_finish],0))+SUM(COALESCE([self].self_finish,0)))/COUNT(*) [學習生字量平均數],
                CAST(MIN(COALESCE([group].[group_finish],0))+MIN(COALESCE([self].self_finish,0)) AS varchar(MAX))+'-'+CAST(MAX(COALESCE([group].[group_finish],0))+MAX(COALESCE([self].self_finish,0)) AS varchar(MAX)) [學習生字量全距],
                SUM(COALESCE([group].[group_word],0)) [教師指派生字量],
                SUM(COALESCE([group].[group_finish],0)) [教師指派生字完成量], 
                SUM(COALESCE([self].self_word,0)) [自主學習生字量],
                SUM(COALESCE([self].self_finish,0)) [自主學習生字完成量],
                [Student].[Year] [入學年],
                [Teacher].[Grade]+'年'+[Teacher].[Class]+'班' [班級],
                [SchoolList].[CityId] [縣市],
                [SchoolList].[PostId] [區域],
                [SchoolList].[SchoolName] [學校],
                COUNT(*) [學生數],
                [Student].[Tid]
            FROM [Literacy].[dbo].[Student]
            LEFT JOIN(
                SELECT dt.Pid,COUNT(*)group_word,COUNT(CASE WHEN dt.is_finish = 1 THEN 1 END) group_finish
                FROM(
                    SELECT [Learn_Group_Task].[TaskId],[Learn_Teacher_Student].[Pid],[Learn_Task_Word].[task_word_id],
                        CASE WHEN COUNT(CASE WHEN step IS NULL OR step != 9 THEN 1 END)>0 THEN 0 ELSE 1 END is_finish
                    FROM [Literacy].[dbo].[Learn_Group_Task] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task].[GroupId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON 
                        [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                        AND [Learn_Task_Word_Student].[Pid] = [Learn_Teacher_Student].[Pid]
                    GROUP BY [Learn_Group_Task].[TaskId],[Learn_Teacher_Student].[Pid],[Learn_Task_Word].[task_word_id]
                )dt
                GROUP BY dt.Pid
            )[group] ON [group].[Pid] = [Student].[Pid]
            LEFT JOIN(
                SELECT dt.Pid,COUNT(*)self_word,COUNT(CASE WHEN dt.is_finish = 1 THEN 1 END) self_finish
                FROM(
                    SELECT [Learn_Task].[TaskId],[Learn_Task].[Pid],[Learn_Task_Word].[task_word_id],
                        CASE WHEN COUNT(CASE WHEN step IS NULL OR step != 9 THEN 1 END)>0 THEN 0 ELSE 1 END is_finish
                    FROM [Literacy].[dbo].[Learn_Task] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON 
                        [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                        AND [Learn_Task_Word_Student].[Pid] = [Learn_Task].[Pid]
                    GROUP BY [Learn_Task].[TaskId],[Learn_Task_Word].[task_word_id],[Learn_Task].[Pid]
                )dt
                GROUP BY dt.Pid
            )[self] ON [self].[Pid] = [Student].[Pid]
            INNER JOIN [Literacy].[dbo].[Teacher] ON [Student].[Tid] = [Teacher].[Tid]
            INNER JOIN [Literacy].[dbo].[SchoolList] ON [SchoolList].[Sid] = [Student].[Sid]
            WHERE 1 = 1 {$filter_string}
            GROUP BY [Student].[Tid],[Student].[Year],[Teacher].[Grade],[Teacher].[Class],[SchoolList].[CityId],[SchoolList].[PostId],[SchoolList].[SchoolName]
            ORDER BY [SchoolList].[CityId] DESC,[SchoolList].[PostId],[SchoolList].[SchoolName],[Student].[Year],[Teacher].[Grade],[Teacher].[Class]
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ['status' => 'failure'];
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_report_task_word_teacher($data)
    {
        $values = [
            "Tid" => 0
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "SELECT [Student].[Year] [入學年],
                [Student].[StuNum] [學號],
                [Student].[Pid],
                [Student].[SeatNum] [座號],
                [Student].[StuName] [姓名],
                COALESCE(all_group_finish+all_self_finish,0)[已學習生字量],
                COALESCE(current_group_finish+current_self_finish,0)[本學期學習生字量],
                COALESCE(score.score,0) [個人積分]
            FROM [Literacy].[dbo].[Student]
            LEFT JOIN(
                SELECT dt.Pid,COUNT(CASE WHEN dt.all_is_finish = 1 THEN 1 END) all_group_finish,COUNT(CASE WHEN dt.current_is_finish = 1 THEN 1 END) current_group_finish
                FROM(
                    SELECT [Learn_Group_Task].[TaskId],[Learn_Teacher_Student].[Pid],[Learn_Task_Word].[task_word_id],
                        CASE WHEN COUNT(CASE WHEN step IS NULL OR step != 9 THEN 1 END)>0 THEN 0 ELSE 1 END all_is_finish,
                        CASE WHEN COUNT(CASE WHEN step IS NULL OR step != 9 AND CASE WHEN DATEPART(mm, [Learn_Task].[ApplyDate]) >= 7 OR
                                DATEPART(mm, GETDATE()) <= 2 THEN '上' WHEN DATEPART(mm, GETDATE()) >= 1 OR
                                DATEPART(mm, GETDATE()) <= 8 THEN '下' END = [Learn_Task].[task_term]
                            AND [Learn_Task].now_year = [Learn_Task].task_year
                        THEN 1 END)>0 THEN 0 ELSE 1 END current_is_finish
                    FROM [Literacy].[dbo].[Learn_Group_Task] 
                    LEFT JOIN [Literacy].[dbo].[View_Learn_Task] [Learn_Task] ON [Learn_Group_Task].[TaskId] = [Learn_Task].[TaskId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task].[GroupId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON 
                        [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                        AND [Learn_Task_Word_Student].[Pid] = [Learn_Teacher_Student].[Pid]
                    GROUP BY [Learn_Group_Task].[TaskId],[Learn_Teacher_Student].[Pid],[Learn_Task_Word].[task_word_id]
                )dt
                GROUP BY dt.Pid
            )[group] ON [group].[Pid] = [Student].[Pid]
            LEFT JOIN(
                SELECT dt.Pid,COUNT(CASE WHEN dt.all_is_finish = 1 THEN 1 END) all_self_finish,COUNT(CASE WHEN dt.current_is_finish = 1 THEN 1 END) current_self_finish
                FROM(
                    SELECT [Learn_Task].[TaskId],[Learn_Task].[Pid],[Learn_Task_Word].[task_word_id],
                        CASE WHEN COUNT(CASE WHEN step IS NULL OR step != 9 THEN 1 END)>0 THEN 0 ELSE 1 END all_is_finish,
                        CASE WHEN COUNT(CASE WHEN step IS NULL OR step != 9 
                            AND CASE WHEN DATEPART(mm, [Learn_Task].[ApplyDate]) >= 7 OR
                                DATEPART(mm, GETDATE()) <= 2 THEN '上' WHEN DATEPART(mm, GETDATE()) >= 1 OR
                                DATEPART(mm, GETDATE()) <= 8 THEN '下' END = [Learn_Task].[task_term]
                            AND [Learn_Task].now_year = [Learn_Task].task_year
                        THEN 1 END)>0 THEN 0 ELSE 1 END current_is_finish
                    FROM [Literacy].[dbo].[View_Learn_Task] [Learn_Task] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON 
                        [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                        AND [Learn_Task_Word_Student].[Pid] = [Learn_Task].[Pid]
                    GROUP BY [Learn_Task].[TaskId],[Learn_Task].[Pid],[Learn_Task_Word].[task_word_id]
                )dt
                GROUP BY dt.Pid
            )[self] ON [self].[Pid] = [Student].[Pid]
            LEFT JOIN (
                SELECT [Pid]
                ,SUM([score]) score
                FROM [Literacy].[dbo].[View_Learn_Total_Score_new]
                GROUP BY [Pid]
            )score ON [Student].[Pid] = score.[Pid] 
            WHERE [Student].[Tid] = :Tid
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ['status' => 'failure'];
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_report_word_frequency($data)
    {
        $values = [
            'word' => null
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $query = "";
        if (is_null($values['word']))
            unset($values['word']);
        else {
            $query = "WHERE dt.[word] = :word";
        }
        $sql = "SELECT dt.nid,dt.rank [名次],dt.word [生字],dt.count [指派次數]
            FROM(
                SELECT [Learn_Task_Word].[nid],COUNT(*) count,[Learn_Word_Romanization].[word],REPLACE(REPLACE(STR(RANK() OVER ( ORDER BY COUNT(*) DESC),2),' ','0'),'*',RANK() OVER ( ORDER BY COUNT(*) DESC)) rank
                FROM [Literacy].[dbo].[Learn_Task_Word]
                INNER JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Task_Word].[nid] = [Learn_Word_Romanization].[nid] 
                GROUP BY [Learn_Task_Word].[nid],[Learn_Word_Romanization].[word]
            )dt
            {$query}
        
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ['status' => 'failure'];
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_report_summary($data)
    {
        $values = [];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "WITH [task] AS (
                SELECT *
                FROM [Literacy].[dbo].[View_Learn_Task]
                WHERE [View_Learn_Task].[task_year] = [View_Learn_Task].[now_year]
                    AND [View_Learn_Task].[task_term] = CASE WHEN DATEPART(mm, GETDATE()) >= 7 OR
                        DATEPART(mm, GETDATE()) <= 2 THEN '上' WHEN DATEPART(mm, GETDATE()) >= 1 OR
                        DATEPART(mm, GETDATE()) <= 8 THEN '下' END
            ),[group] AS (
                SELECT dt.Pid,COUNT(*)group_task,COUNT(CASE WHEN dt.is_finish = 1 THEN 1 END) group_finish
                FROM(
                    SELECT [Learn_Group_Task].[TaskId],[Learn_Teacher_Student].[Pid],
                        CASE WHEN COUNT(CASE WHEN step IS NULL OR step != 9 THEN 1 END)>0 THEN 0 ELSE 1 END is_finish
                    FROM [Literacy].[dbo].[Learn_Group_Task] 
                    INNER JOIN [task] ON [Learn_Group_Task].[TaskId] = [task].[TaskId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Teacher_Student] ON [Learn_Teacher_Student].[GroupId] = [Learn_Group_Task].[GroupId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Group_Task].[TaskId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON 
                        [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                        AND [Learn_Task_Word_Student].[Pid] = [Learn_Teacher_Student].[Pid]
                    GROUP BY [Learn_Group_Task].[TaskId],[Learn_Teacher_Student].[Pid]
                )dt
                GROUP BY dt.Pid
            ),[self] AS (
                SELECT dt.Pid,COUNT(*)self_task,COUNT(CASE WHEN dt.is_finish = 1 THEN 1 END) self_finish
                FROM(
                    SELECT [Learn_Task].[TaskId],[Learn_Task].[Pid],
                        CASE WHEN COUNT(CASE WHEN step IS NULL OR step != 9 THEN 1 END)>0 THEN 0 ELSE 1 END is_finish
                    FROM [task] [Learn_Task] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON 
                        [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                        AND [Learn_Task_Word_Student].[Pid] = [Learn_Task].[Pid]
                    GROUP BY [Learn_Task].[TaskId],[Learn_Task].[Pid]
                )dt
                GROUP BY dt.Pid
            ),[word_count] AS (
                SELECT [Learn_Task_Word_Student].[Pid], COUNT(*) word_count
                FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_word_id] = [Learn_Task_Word_Student].[task_word_id]
                INNER JOIN [task] [View_Learn_Task] ON [Learn_Task_Word].[task_id] = [View_Learn_Task].TaskId 
                GROUP BY [Learn_Task_Word_Student].[Pid]
            ),assign_group_word_count AS (
                SELECT COUNT(DISTINCT [Learn_Task_Word].[nid] ) assign_group_word_count
                FROM [Literacy].[dbo].[Learn_Task_Word]
                INNER JOIN [task] [View_Learn_Task] ON [Learn_Task_Word].[task_id] = [View_Learn_Task].TaskId 
                WHERE [View_Learn_Task].Pid  IS NULL
            ),assign_self_word_count AS (
                SELECT COUNT(DISTINCT [Learn_Task_Word].[nid] ) assign_self_word_count
                FROM [Literacy].[dbo].[Learn_Task_Word]
                INNER JOIN [task] [View_Learn_Task] ON [Learn_Task_Word].[task_id] = [View_Learn_Task].TaskId 
                WHERE [View_Learn_Task].Tid  IS NULL
            )
            
                
                
            SELECT SUM(COALESCE([group].[group_task],0)) [教師指派任務數],
                SUM(COALESCE([group].[group_finish],0)) [教師指派任務完成數], 
                COALESCE(SUM(COALESCE([group].[group_finish],0))/NULLIF(SUM(COALESCE([group].[group_task],0)),0)*100,0) [教師指派任務完成率],
                SUM(COALESCE([self].self_task,0)) [自主學習任務數],
                SUM(COALESCE([self].self_finish,0)) [自主學習任務完成數],
                COALESCE(SUM(COALESCE([self].self_finish,0))/NULLIF(SUM(COALESCE([self].self_task,0)),0)*100,0) [自主學習任務完成率],
                COUNT(DISTINCT [SchoolList].[SchoolName]) [本學期使用學校數],
                COUNT(*) [本學期使用學生數],
                COUNT(DISTINCT [Student].[Tid])[本學期使用教師數],
                COUNT(DISTINCT [Student].[Tid])+COUNT(*) [本學期使用總人數],
                SUM([word_count])/COUNT(*) [本學期學習生字量平均數],
                CAST(MIN([word_count]) AS varchar(MAX)) +'-'+ CAST(MAX([word_count]) AS varchar(MAX)) [本學期學習生字量全距],
                SUM(assign_group_word_count.assign_group_word_count) [本學期教師總指派字數],
                SUM(assign_self_word_count.assign_self_word_count) [本學期學生總指派字數]
            FROM (
                SELECT *
                FROM [Literacy].[dbo].[Student]
                WHERE [Pid] IN (
                    SELECT Pid
                    FROM [group]
                ) OR [Pid] IN (
                    SELECT Pid
                    FROM [self]
                )
            )[Student]
            LEFT JOIN [group] ON [group].[Pid] = [Student].[Pid]
            LEFT JOIN [self] ON [self].[Pid] = [Student].[Pid]
            LEFT JOIN [word_count] ON [word_count].[Pid] = [Student].[Pid]
            INNER JOIN [Literacy].[dbo].[Teacher] ON [Student].[Tid] = [Teacher].[Tid]
            INNER JOIN [Literacy].[dbo].[SchoolList] ON [SchoolList].[Sid] = [Student].[Sid]
            CROSS JOIN assign_self_word_count
            CROSS JOIN assign_group_word_count
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ['status' => 'failure'];
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_report_word_teacher($data)
    {
        $values = [
            'nid' => null
        ];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "SELECT [SchoolList].[CityId] [縣市],[SchoolList].[PostId] [區域],[SchoolList].[SchoolName] [學校],[Teacher].[Grade]+'年'+[Teacher].[Class]+'班' [班級],[Teacher].[TeacherName] [老師] ,dt.word [生字]   ,dt.count [指派次數]
            FROM(
                SELECT [Learn_Task_Word].[nid],[Learn_Task].[TId],COUNT(*) count,[Learn_Word_Romanization].[word],REPLACE(REPLACE(STR(RANK() OVER ( ORDER BY COUNT(*) DESC),2),' ','0'),'*',RANK() OVER ( ORDER BY COUNT(*) DESC)) rank
                FROM [Literacy].[dbo].[Learn_Task_Word]
                INNER JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Task_Word].[nid] = [Learn_Word_Romanization].[nid] 
                LEFT JOIN [Literacy].[dbo].[Learn_Task] ON [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId]
                GROUP BY [Learn_Task_Word].[nid],[Learn_Word_Romanization].[word],[Learn_Task].[TId]
            )dt
            INNER JOIN [Literacy].[dbo].[Teacher] ON [Teacher].[Tid] = dt.[TId]
            INNER JOIN [Literacy].[dbo].[SchoolList] ON [SchoolList].[Sid] = [Teacher].[Sid]
            WHERE dt.[nid] = :nid
            ORDER BY dt.count DESC
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ['status' => 'failure'];
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_report_word_avg_grade($data)
    {
        $values = [];
        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "WITH [with] AS (
                SELECT CASE WHEN [Teacher].[Grade] = '一' AND [SchoolList].[Class]='國中' THEN '七' 
                    WHEN [Teacher].[Grade] = '二' AND [SchoolList].[Class]='國中' THEN '八'
                    WHEN [Teacher].[Grade] = '三' AND [SchoolList].[Class]='國中' THEN '九'
                    ELSE [Teacher].[Grade] END [Grade],COUNT(*) total_word_count,COUNT(CASE WHEN [View_Learn_Task].[task_year] = [View_Learn_Task].[now_year] 
                    AND [View_Learn_Task].[task_term] = CASE WHEN DATEPART(mm, GETDATE()) >= 7 OR
                    DATEPART(mm, GETDATE()) <= 2 THEN '上' WHEN DATEPART(mm, GETDATE()) >= 1 OR
                    DATEPART(mm, GETDATE()) <= 8 THEN '下' END THEN 1 END) current_word_count,
                    COUNT(DISTINCT [Student].[Pid]) student_count
                FROM [Literacy].[dbo].[View_Learn_Task]
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [View_Learn_Task].[TaskId] = [Learn_Task_Word].[task_id]
                LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON [Learn_Task_Word].[task_word_id] = [Learn_Task_Word_Student].[task_word_id]
                LEFT JOIN [Literacy].[dbo].[Student] ON [Student].[Pid] = [Learn_Task_Word_Student].[Pid]
                LEFT JOIN [Literacy].[dbo].[Teacher] ON [Teacher].[Tid] = [Student].[Tid]
                LEFT JOIN [Literacy].[dbo].[SchoolList] ON [SchoolList].[Sid] = [Student].[Sid]
                WHERE [Learn_Task_Word_Student].[step] = 9
                GROUP BY CASE WHEN [Teacher].[Grade] = '一' AND [SchoolList].[Class]='國中' THEN '七' 
                    WHEN [Teacher].[Grade] = '二' AND [SchoolList].[Class]='國中' THEN '八'
                    WHEN [Teacher].[Grade] = '三' AND [SchoolList].[Class]='國中' THEN '九'
                    ELSE [Teacher].[Grade] END
            )
            SELECT [type],SUM(word) [word]
            FROM(
                SELECT '一年級已學習生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                FROM [with] 
                WHERE [Grade] = '一'
                UNION ALL(
                    SELECT '一年級本學期生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '一'
                )
                UNION ALL(
                    SELECT '二年級已學習生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '二'
                )
                UNION ALL(
                    SELECT '二年級本學期生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '二'
                )
                UNION ALL(
                    SELECT '三年級已學習生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '三'
                )
                UNION ALL(
                    SELECT '三年級本學期生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '三'
                )
                UNION ALL(
                    SELECT '四年級已學習生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '四'
                )
                UNION ALL(
                    SELECT '四年級本學期生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '四'
                )
                UNION ALL(
                    SELECT '五年級已學習生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '五'
                )
                UNION ALL(
                    SELECT '五年級本學期生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '五'
                )
                UNION ALL(
                    SELECT '六年級已學習生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '六'
                )
                UNION ALL(
                    SELECT '六年級本學期生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '六'
                )
                UNION ALL(
                    SELECT '七年級已學習生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '七'
                )
                UNION ALL(
                    SELECT '七年級本學期生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '七'
                )
                UNION ALL(
                    SELECT '八年級已學習生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '八'
                )
                UNION ALL(
                    SELECT '八年級本學期生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '八'
                )
                UNION ALL(
                    SELECT '九年級已學習生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '九'
                )
                UNION ALL(
                    SELECT '九年級本學期生字量' [type],total_word_count/ISNULL(NULLIF(student_count,0),1) [word]
                    FROM [with] 
                    WHERE [Grade] = '九'
                )
                UNION ALL(
                    SELECT '一年級已學習生字量' [type],0 [word]
                    UNION ALL(
                        SELECT '一年級本學期生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '二年級已學習生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '二年級本學期生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '三年級已學習生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '三年級本學期生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '四年級已學習生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '四年級本學期生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '五年級已學習生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '五年級本學期生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '六年級已學習生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '六年級本學期生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '七年級已學習生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '七年級本學期生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '八年級已學習生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '八年級本學期生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '九年級已學習生字量' [type],0 [word]
                    )
                    UNION ALL(
                        SELECT '九年級本學期生字量' [type],0 [word]
                    )
                )
            )dt
            GROUP BY dt.type
            ORDER BY CASE 
                WHEN dt.type LIKE '%一%' THEN 1
                WHEN dt.type LIKE '%二%' THEN 2
                WHEN dt.type LIKE '%三%' THEN 3
                WHEN dt.type LIKE '%四%' THEN 4
                WHEN dt.type LIKE '%五%' THEN 5
                WHEN dt.type LIKE '%六%' THEN 6
                WHEN dt.type LIKE '%七%' THEN 7
                WHEN dt.type LIKE '%八%' THEN 8
                WHEN dt.type LIKE '%九%' THEN 9
                ELSE 99999 END
            ASC
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) return ['status' => 'failure'];
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    private function numToWord($num)
    {
        $chiNum = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
        $chiUni = array('', '十', '百', '千', '萬', '億', '十', '百', '千');
        $chiStr = '';
        $num_str = (string)$num;
        $count = strlen($num_str);
        $last_flag = true; //上一個 是否為0
        $zero_flag = true; //是否第一個
        $temp_num = null; //臨時數字
        $chiStr = ''; //拼接結果
        if ($count == 2) { //兩位數
            $temp_num = $num_str[0];
            $chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num] . $chiUni[1];
            $temp_num = $num_str[1];
            $chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num];
        } else if ($count > 2) {
            $index = 0;
            for ($i = $count - 1; $i >= 0; $i--) {
                $temp_num = $num_str[$i];
                if ($temp_num == 0) {
                    if (!$zero_flag && !$last_flag) {
                        $chiStr = $chiNum[$temp_num] . $chiStr;
                        $last_flag = true;
                    }
                } else {
                    $chiStr = $chiNum[$temp_num] . $chiUni[$index % 9] . $chiStr;
                    $zero_flag = false;
                    $last_flag = false;
                }
                $index++;
            }
        } else {
            $chiStr = $chiNum[$num_str[0]];
        }
        return $chiStr;
    }
}
