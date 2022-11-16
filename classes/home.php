<?php

use Slim\Views\PhpRenderer;

class home
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getSchoolID($data)
    {
        $values = [
            "schoolid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT COUNT([SchoolID]) AS Count
                FROM [Literacy].[dbo].[SchoolList]
                WHERE [SchoolID] = :schoolid
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchColumn(0);
        return $result;
    }

    public function get_learn_student_rank($data)
    {
        $values = [];

        $string = '';
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (array_key_exists('Pid', $data)) {
            $values['Pid'] = $data['Pid'];
            $string .= "OR dt.[Pid] = :Pid";
        }

        $sql = "SELECT *
                FROM (
                    SELECT ROW_NUMBER() OVER (ORDER BY [View_Learn_Total_Score_new].[score] DESC ) row_number
                        , RANK () OVER (ORDER BY [View_Learn_Total_Score_new].[score] DESC) rank
                        , [View_Learn_Total_Score_new].[Pid]
                        , [SchoolList].[CityId], [SchoolList].[PostId], [SchoolList].[SchoolName]
                        , (
                            CASE WHEN [View_Learn_Total_Score_new].[Grade] = 1 THEN '一'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 2 THEN '二'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 3 THEN '三'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 4 THEN '四'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 5 THEN '五'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 6 THEN '六'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 7 THEN '七'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 8 THEN '八'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 9 THEN '九'
                                ELSE '畢' END
                            ) Grade
                        , [Teacher].[Class]
                        , [Student].[StuName], [View_Learn_Total_Score_new].[score]
                    FROM [Literacy].[dbo].[View_Learn_Total_Score_new]
                    LEFT JOIN [Literacy].[dbo].[Student] ON [Student].[Pid] = [View_Learn_Total_Score_new].[Pid]
                    LEFT JOIN [Literacy].[dbo].[Teacher] ON [Teacher].[Tid] = [Student].[Tid]
                    LEFT JOIN [Literacy].[dbo].[SchoolList] ON [SchoolList].[Sid] = [Teacher].[Sid]
                ) dt
                WHERE dt.row_number < 6
                {$string}
                ORDER BY dt.[score] DESC
        
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchALL(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_student_today_rank($data)
    {
        $values = [];

        $string = '';
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (array_key_exists('Pid', $data)) {
            $values['Pid'] = $data['Pid'];
            $string .= "OR dt.[Pid] = :Pid";
        }

        $sql = "SELECT *
                FROM (
                    SELECT ROW_NUMBER() OVER (ORDER BY [View_Learn_Total_Score_new].[score] DESC ) row_number
                        , RANK () OVER (ORDER BY [View_Learn_Total_Score_new].[score] DESC) rank
                        , [View_Learn_Total_Score_new].[Pid]
                        , [SchoolList].[CityId], [SchoolList].[PostId], [SchoolList].[SchoolName]
                        , (
                            CASE WHEN [View_Learn_Total_Score_new].[Grade] = 1 THEN '一'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 2 THEN '二'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 3 THEN '三'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 4 THEN '四'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 5 THEN '五'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 6 THEN '六'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 7 THEN '七'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 8 THEN '八'
                                WHEN [View_Learn_Total_Score_new].[Grade] = 9 THEN '九'
                                ELSE '畢' END
                            ) Grade
                        , [Teacher].[Class]
                        , [Student].[StuName]
                        , COALESCE ([View_Learn_Total_Score_new].[score] - before_score.score , 0)score
                    FROM [Literacy].[dbo].[View_Learn_Total_Score_new]
                    LEFT JOIN [Literacy].[dbo].[Student] ON [Student].[Pid] = [View_Learn_Total_Score_new].[Pid]
                    LEFT JOIN [Literacy].[dbo].[Teacher] ON [Teacher].[Tid] = [Student].[Tid]
                    LEFT JOIN [Literacy].[dbo].[SchoolList] ON [SchoolList].[Sid] = [Teacher].[Sid]
                    LEFT JOIN (
                        SELECT Pid2 AS Pid, Grade2 AS Grade, task_year, task_term, GraduationYear2 AS GraduationYear
                            , (((COALESCE (SUM(CASE WHEN dt.[Pid] IS NULL THEN 5 * count ELSE 0 END), 0) 
                            + COALESCE (SUM(CASE WHEN dt.[Tid] IS NULL THEN 5 * count END), 0) - COUNT(CASE WHEN dt.[Tid] IS NULL AND count = 2 THEN 1 END)) 
                            - COUNT(CASE WHEN dt.[Tid] IS NULL AND count = 3 THEN 1 END) * 2) - COUNT(CASE WHEN dt.[Tid] IS NULL AND count = 4 THEN 1 END) * 3) 
                            - COUNT(CASE WHEN dt.[Tid] IS NULL AND count > 4 THEN 1 END) * 4 AS score
                        FROM (
                            SELECT Pid2, Grade2, task_year, task_term, GraduationYear2
                            , Pid, Tid, nid, COUNT(*) AS count
                            FROM(
                                SELECT dbo.Learn_Task_Word.nid, dbo.Learn_Task_Word_Student.Pid AS Pid2
                                    , Student.GraduationYear2, 6 - Student.GraduationYear2 + Learn_Task.task_year AS Grade2
                                    , Learn_Task.task_year, Learn_Task.task_term, Learn_Task.now_year, Learn_Task.TaskId
                                    , Learn_Task.Tid, Learn_Task.Pid, Learn_Task.TaskName, Learn_Task.Highlight
                                    , Learn_Task.Grade, Learn_Task.Term, Learn_Task.ApplyDate, Learn_Task.EndDate
                                    , Learn_Task.ExpireDate, Learn_Task.AppendDate
                                FROM dbo.View_Learn_Task AS Learn_Task 
                                LEFT OUTER JOIN dbo.Learn_Task_Word ON dbo.Learn_Task_Word.task_id = Learn_Task.TaskId 
                                INNER JOIN dbo.Learn_Task_Word_Student ON dbo.Learn_Task_Word.task_word_id = dbo.Learn_Task_Word_Student.task_word_id 
                                LEFT OUTER JOIN (
                                    SELECT Student_1.Pid, Student_1.Sid, Student_1.Tid, Student_1.Year
                                        , Student_1.StuName, Student_1.IDNumber, Student_1.StuNum, Student_1.SeatNum
                                        , Student_1.Birth, Student_1.Sex, Student_1.Parent_Edu, Student_1.Parent_Edu_M
                                        , Student_1.Parent_Job, Student_1.Parent_Job_M, Student_1.AddTime, Student_1.Pre_Grade
                                        ,Student_1.Pre_Class, Student_1.Pre_SeatNum, Student_1.GraduationYear, Student_1.ClassIDs
                                        , Student_1.ColorNum, Student_1.Theta, Student_1.LiteracyScore, Student_1.StartTime
                                        , Student_1.EndTime, Student_1.Z_Value, Student_1.PR_Value, Student_1.ExamProgramKind
                                        , Student_1.Wid, Student_1.Exam_Year, Student_1.Exam_Term, Student_1.Exam_TKind
                                        , Student_1.StuDelFlag, Student_1.chk_IDNum5, Student_1.chk_BirthMMDD, Student_1.Flag_6ID4Bir
                                        , Student_1.Flag_5ID, Student_1.Flag_6IDBirth, dbo.Teacher.Grade
                                        , CASE WHEN [Teacher].[Grade] = '一' THEN DATEPART(yy, GETDATE()) + 5 
                                            WHEN [Teacher].[Grade] = '二' THEN DATEPART(yy, GETDATE()) + 4 
                                            WHEN [Teacher].[Grade] = '三' THEN DATEPART(yy, GETDATE())  + 3 
                                            WHEN [Teacher].[Grade] = '四' THEN DATEPART(yy, GETDATE()) + 2 
                                            WHEN [Teacher].[Grade] = '五' THEN DATEPART(yy, GETDATE()) + 1 
                                            WHEN [Teacher].[Grade] = '六' THEN DATEPART(yy, GETDATE()) 
                                            WHEN [Teacher].[Grade] = '畢' THEN REPLACE([Teacher].[GraduationYear], 'OOO', DATEPART(yy, GETDATE() - 1911)) + 1911 
                                            END AS GraduationYear2
                                FROM dbo.Student AS Student_1 
                                LEFT OUTER JOIN dbo.Teacher ON dbo.Teacher.Tid = Student_1.Tid) AS Student ON dbo.Learn_Task_Word_Student.Pid = Student.Pid
                                WHERE (Student.GraduationYear2 - Learn_Task.task_year < 6) 
                                AND (dbo.Learn_Task_Word_Student.step = 9)
                                AND (DATEDIFF(dd,Learn_Task_Word_Student.AppendTime,GETDATE()) < 0 OR Learn_Task_Word_Student.AppendTime IS NULL)
                                ) AS dt_1
                                GROUP BY Pid2, Grade2, task_year, task_term, GraduationYear2, Pid, Tid, nid
                        ) AS dt
                        GROUP BY Pid2, Grade2, task_year, task_term, GraduationYear2
                    ) before_score ON before_score.Pid = [View_Learn_Total_Score_new].[Pid]
                ) dt
                WHERE dt.row_number < 6
                {$string}
                ORDER BY dt.[score] DESC
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchALL(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_group_id($data)
    {
        $values = [
            "Pid" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [GroupId]
                FROM [Literacy].[dbo].[Learn_Teacher_Student]
                WHERE [Pid] = :Pid
    ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchColumn(0);
        return $result;
    }
    public function get_learn_group_rank($data)
    {
        $values = [];

        $string = '';
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (array_key_exists('GroupId', $data)) {
            $values['GroupId'] = $data['GroupId'];
            $string .= "OR dt.GroupId = :GroupId";
        }

        $sql = "SELECT *
                FROM (
                    SELECT ROW_NUMBER() OVER (ORDER BY inside_table.[score] DESC ) row_number
                            , RANK () OVER (ORDER BY inside_table.[score] DESC) rank
                            , inside_table.[CityId], inside_table.[PostId], inside_table.[SchoolName]
                            , inside_table.[Grade], inside_table.[Class]
                            , inside_table.[GroupName]
                            , (CASE WHEN inside_table.[score] IS NULL THEN 0 ELSE inside_table.[score] END) score
                            , inside_table.[GroupId]
                    FROM (
                        SELECT [SchoolList].[CityId], [SchoolList].[PostId], [SchoolList].[SchoolName]
                                , [Teacher].[Grade], [Teacher].[Class]
                                , [Learn_Group].[GroupId]
                                , [Learn_Group].[GroupName]
                                , teacher_assign.[score]
                                , teacher_assign.[GroupId] AS acd
                        FROM [Literacy].[dbo].[Learn_Teacher_Student]
                        LEFT JOIN [Literacy].[dbo].[Teacher] ON [Teacher].[Tid] = [Learn_Teacher_Student].[Tid]
                        LEFT JOIN [Literacy].[dbo].[Learn_Group] ON [Learn_Group].[GroupId] = [Learn_Teacher_Student].[GroupId]
                        LEFT JOIN [Literacy].[dbo].[SchoolList] ON [SchoolList].[Sid] = [Teacher].[Sid]
                        LEFT JOIN (
                              SELECT *
							  FROM [Literacy].[dbo].[View_Learn_Total_Score_Group]
                            ) AS teacher_assign ON teacher_assign.[GroupId] = [Learn_Group].[GroupId]
                        GROUP BY [SchoolList].[CityId], [SchoolList].[PostId], [SchoolList].[SchoolName]
                                , [Teacher].[Grade], [Teacher].[Class]
                                , [Learn_Group].[GroupName]
                                , teacher_assign.[score]
                                , teacher_assign.[GroupId]
                                , [Learn_Group].[GroupId]
                    ) inside_table
                ) dt
                WHERE dt.row_number < 6
                {$string}
                ORDER BY dt.[score] DESC
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchALL(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_group_today_rank($data)
    {
        $values = [];

        $string = '';
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (array_key_exists('GroupId', $data)) {
            $values['GroupId'] = $data['GroupId'];
            $string .= "OR dt.GroupId = :GroupId";
        }

        $sql = "WITH student_task_unfinish_assign AS (
                    SELECT Tid, GroupId, TaskId AS task_id, SUM(unfinish) AS unfinish
                        , COUNT(step) AS total, MAX([MaxAppendTime]) [MaxAppendTime]
                    FROM(
                        SELECT dbo.Learn_Teacher_Student.Tid, dbo.Learn_Teacher_Student.GroupId
                            , dbo.Learn_Group_Task.TaskId, dbo.Learn_Task_Word.task_word_id
                            , dbo.Learn_Task_Word_Student.Pid AS task_word_pid, COALESCE (dbo.Learn_Task_Word_Student.step, 1) AS step
                            , COALESCE ((CASE WHEN [Learn_Task_Word_Student].step != 9 OR [Learn_Task_Word_Student].step IS NULL THEN 1 END), 0) AS unfinish
                            , MAX(Learn_Task_Word_Student.AppendTime) [MaxAppendTime]
                        FROM dbo.Learn_Teacher_Student 
                        LEFT OUTER JOIN dbo.Learn_Group_Task ON dbo.Learn_Group_Task.GroupId = dbo.Learn_Teacher_Student.GroupId 
                        LEFT OUTER JOIN dbo.Learn_Task_Word ON dbo.Learn_Task_Word.task_id = dbo.Learn_Group_Task.TaskId 
                        LEFT OUTER JOIN dbo.Learn_Task_Word_Student ON dbo.Learn_Task_Word_Student.task_word_id = dbo.Learn_Task_Word.task_word_id 
                        AND dbo.Learn_Task_Word_Student.Pid = dbo.Learn_Teacher_Student.Pid
                        WHERE (dbo.Learn_Group_Task.TaskId IS NOT NULL)
                        GROUP BY dbo.Learn_Teacher_Student.Tid, dbo.Learn_Teacher_Student.GroupId, dbo.Learn_Group_Task.TaskId
                            , dbo.Learn_Task_Word.task_word_id,dbo.Learn_Task_Word_Student.Pid, dbo.Learn_Task_Word_Student.step
                    ) AS total_step
                    GROUP BY Tid, GroupId, TaskId
                )
                SELECT *
                FROM (
                    SELECT ROW_NUMBER() OVER (ORDER BY inside_table.[score] DESC ) row_number
                            , RANK () OVER (ORDER BY inside_table.[score] DESC) rank
                            , inside_table.[CityId], inside_table.[PostId], inside_table.[SchoolName]
                            , inside_table.[Grade], inside_table.[Class]
                            , inside_table.[GroupName]
                            , (CASE WHEN inside_table.[score] IS NULL THEN 0 ELSE inside_table.[score] END) score
                            , inside_table.[GroupId]
                    FROM (
                        SELECT [SchoolList].[CityId], [SchoolList].[PostId], [SchoolList].[SchoolName]
                                , [Teacher].[Grade], [Teacher].[Class]
                                , [Learn_Group].[GroupId]
                                , [Learn_Group].[GroupName]
                                , (teacher_assign.[score] - teacher_assign_before.[score]) [score]
                                , teacher_assign.[GroupId] AS acd
                        FROM [Literacy].[dbo].[Learn_Teacher_Student]
                        LEFT JOIN [Literacy].[dbo].[Teacher] ON [Teacher].[Tid] = [Learn_Teacher_Student].[Tid]
                        LEFT JOIN [Literacy].[dbo].[Learn_Group] ON [Learn_Group].[GroupId] = [Learn_Teacher_Student].[GroupId]
                        LEFT JOIN [Literacy].[dbo].[SchoolList] ON [SchoolList].[Sid] = [Teacher].[Sid]
                        LEFT JOIN (
                            SELECT *
                            FROM [Literacy].[dbo].[View_Learn_Total_Score_Group]
                            ) AS teacher_assign ON teacher_assign.[GroupId] = [Learn_Group].[GroupId]
                        LEFT JOIN (
                            SELECT Learn_Teacher_Student_2.Pid, dbo.View_Learn_Task.task_year, dbo.View_Learn_Task.task_term
                                , Learn_Teacher_Student_2.GroupId, Student.GraduationYear
                                , 6 - Student.GraduationYear + dbo.View_Learn_Task.task_year AS Grade, group_score.score
                            FROM dbo.View_Learn_Task 
                            INNER JOIN dbo.Learn_Group_Task AS Learn_Group_Task_2 ON dbo.View_Learn_Task.TaskId = Learn_Group_Task_2.TaskId 
                            LEFT OUTER JOIN dbo.Learn_Teacher_Student AS Learn_Teacher_Student_2 ON Learn_Group_Task_2.GroupId = Learn_Teacher_Student_2.GroupId 
                            LEFT OUTER JOIN dbo.Learn_Task_Word AS Learn_Task_Word_1 ON Learn_Group_Task_2.TaskId = Learn_Task_Word_1.task_id 
                            LEFT OUTER JOIN dbo.Learn_Task_Word_Student AS Learn_Task_Word_Student_1 ON Learn_Task_Word_Student_1.Pid = Learn_Teacher_Student_2.Pid 
                            AND Learn_Task_Word_1.task_word_id = Learn_Task_Word_Student_1.task_word_id 
                            LEFT OUTER JOIN (
                                SELECT Student_1.Pid, Student_1.Sid, Student_1.Tid, Student_1.Year, Student_1.StuName
                                    , Student_1.IDNumber, Student_1.StuNum, Student_1.SeatNum, Student_1.Birth, Student_1.Sex
                                    , Student_1.Parent_Edu, Student_1.Parent_Edu_M, Student_1.Parent_Job, Student_1.Parent_Job_M
                                    , Student_1.AddTime, Student_1.Pre_Grade, Student_1.Pre_Class, Student_1.Pre_SeatNum
                                    , Student_1.ClassIDs, Student_1.ColorNum, Student_1.Theta, Student_1.LiteracyScore, Student_1.StartTime
                                    , Student_1.EndTime, Student_1.Z_Value, Student_1.PR_Value, Student_1.ExamProgramKind, Student_1.Wid
                                    , Student_1.Exam_Year, Student_1.Exam_Term, Student_1.Exam_TKind, Student_1.StuDelFlag, Student_1.chk_IDNum5
                                    , Student_1.chk_BirthMMDD, Student_1.Flag_6ID4Bir, Student_1.Flag_5ID, Student_1.Flag_6IDBirth, dbo.Teacher.Grade
                                    , CASE WHEN [Teacher].[Grade] = '一' THEN DATEPART(yy, GETDATE()) + 5 
                                        WHEN [Teacher].[Grade] = '二' THEN DATEPART(yy, GETDATE()) + 4 
                                        WHEN [Teacher].[Grade] = '三' THEN DATEPART(yy, GETDATE()) + 3 
                                        WHEN [Teacher].[Grade] = '四' THEN DATEPART(yy, GETDATE()) + 2 
                                        WHEN [Teacher].[Grade] = '五' THEN DATEPART(yy, GETDATE()) + 1 
                                        WHEN [Teacher].[Grade] = '六' THEN DATEPART(yy, GETDATE()) 
                                        WHEN [Teacher].[Grade] = '畢' THEN REPLACE([Teacher].[GraduationYear], 'OOO', DATEPART(yy, GETDATE() - 1911)) + 1911 
                                        END AS GraduationYear
                                FROM dbo.Student AS Student_1 
                                LEFT OUTER JOIN dbo.Teacher ON dbo.Teacher.Tid = Student_1.Tid) AS Student ON Learn_Teacher_Student_2.Pid = Student.Pid 
                                LEFT OUTER JOIN (
                                    SELECT GroupId, GroupName, class, SUM(CASE WHEN [status] = 'finish' THEN 10 ELSE 0 END) AS score
                                    FROM(
                                        SELECT dt.GroupId, dt.GroupName, dt.TaskId, dt.TaskName, Teacher_1.Grade + '年' + Teacher_1.Class + '班' AS class
                                            , CASE WHEN dt.[finish] = dt.[total_assign] THEN 'finish' 
                                                WHEN dt.[finish] != dt.[total_assign] AND GETDATE() BETWEEN dt.[ApplyDate] AND dt.[EndDate] THEN 'doing' 
                                                WHEN (CONVERT(bigint, datediff(day, GETDATE(), [dt].[ApplyDate])) * 24 * 60 * 60) - (datediff(second, dateadd(day, datediff(day, 0, GETDATE()),0)
                                                , GETDATE())) + (datediff(second, dateadd(day, datediff(day, 0, [dt].[ApplyDate]), 0), [dt].[ApplyDate])) > 0 THEN 'unstart' 
                                                ELSE 'unfinish' END AS status
                                            ,student_task_unfinish_assign_3.[MaxAppendTime]	
                                        FROM (
                                            SELECT Learn_Task.GroupId, Learn_Task.GroupName, Learn_Task.TaskId, Learn_Task.TaskName
                                            , Learn_Task.ApplyDate, Learn_Task.EndDate, Learn_Task.Tid
                                            , COUNT(CASE WHEN [Learn_Task_Word_self].unfinish = 0 THEN 1 END) AS finish, COUNT(*) AS total_assign
                                            FROM(
                                                SELECT Learn_Group_Task_1.GroupId, dbo.Learn_Group.GroupName
                                                    , Learn_Task_1.TaskId, Learn_Task_1.TaskName, Learn_Task_1.ApplyDate
                                                    , Learn_Task_1.EndDate, Learn_Teacher_Student_1.Pid, Learn_Task_1.Tid
                                                FROM dbo.Learn_Task AS Learn_Task_1 
                                                LEFT OUTER JOIN dbo.Learn_Group_Task AS Learn_Group_Task_1 ON Learn_Group_Task_1.TaskId = Learn_Task_1.TaskId 
                                                LEFT OUTER JOIN dbo.Learn_Group ON dbo.Learn_Group.GroupId = Learn_Group_Task_1.GroupId 
                                                INNER JOIN dbo.Learn_Teacher_Student AS Learn_Teacher_Student_1 ON Learn_Teacher_Student_1.GroupId = Learn_Group_Task_1.GroupId
                                                GROUP BY Learn_Group_Task_1.GroupId, dbo.Learn_Group.GroupName, Learn_Task_1.TaskId, Learn_Task_1.TaskName
                                                , Learn_Task_1.ApplyDate, Learn_Task_1.EndDate, Learn_Teacher_Student_1.Pid, Learn_Task_1.Tid) AS Learn_Task 
                                                LEFT OUTER JOIN (
                                                    SELECT Tid, GroupId, task_id, unfinish, total
                                                    FROM student_task_unfinish_assign AS student_task_unfinish_assign_2
                                                ) AS Learn_Task_Word_self ON Learn_Task_Word_self.task_id = Learn_Task.TaskId AND Learn_Task_Word_self.GroupId = Learn_Task.GroupId
                                                GROUP BY Learn_Task.GroupId, Learn_Task.GroupName, Learn_Task.TaskId
                                                    , Learn_Task.TaskName, Learn_Task.ApplyDate, Learn_Task.EndDate, Learn_Task.GroupName, Learn_Task.Tid
                                        ) AS dt 
                                        LEFT OUTER JOIN (
                                            SELECT task_id, GroupId, unfinish, total, [MaxAppendTime]
                                            FROM student_task_unfinish_assign AS student_task_unfinish_assign_1
                                            WHERE (DATEDIFF(dd,student_task_unfinish_assign_1.[MaxAppendTime],GETDATE()) < 0 
                                                OR student_task_unfinish_assign_1.[MaxAppendTime] IS NULL)
                                        ) AS student_task_unfinish_assign_3 ON student_task_unfinish_assign_3.task_id = dt.TaskId AND student_task_unfinish_assign_3.GroupId = dt.GroupId 
                                        LEFT OUTER JOIN dbo.Teacher AS Teacher_1 ON Teacher_1.Tid = dt.Tid) AS tmp
                                        GROUP BY GroupId, GroupName, class
                                ) AS group_score ON group_score.GroupId = Learn_Teacher_Student_2.GroupId
                            WHERE (Student.GraduationYear - dbo.View_Learn_Task.task_year < 6)
                            GROUP BY dbo.View_Learn_Task.task_year, dbo.View_Learn_Task.task_term, Learn_Teacher_Student_2.GroupId
                                , Student.GraduationYear, group_score.score, Learn_Teacher_Student_2.Pid
                        ) teacher_assign_before ON teacher_assign_before.GroupId = teacher_assign.GroupId
                        GROUP BY [SchoolList].[CityId], [SchoolList].[PostId], [SchoolList].[SchoolName]
                                , [Teacher].[Grade], [Teacher].[Class]
                                , [Learn_Group].[GroupName]
                                , teacher_assign.[score]
                                , teacher_assign_before.[score]
                                , teacher_assign.[GroupId]
                                , [Learn_Group].[GroupId]
                    ) inside_table
                ) dt
                WHERE dt.row_number < 6
                {$string}
                ORDER BY dt.[score] DESC
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchALL(PDO::FETCH_ASSOC);
        return $result;
    }


    public function post_online_apply_school($data)
    {
        $values = [
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

        $error = [];
        $success = [];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $Flag = 0;
        if (trim($values['txt_Principal']) == "") {
            $error[] = 'txt_Principal';
            $Flag = 1;
        } else {
            $success[] = 'txt_Principal';
        }
        if (trim($values['txt_Senate']) == "") {
            $error[] = 'txt_Senate';
            $Flag = 1;
        } else {
            $success[] = 'txt_Senate';
        }
        if (trim($values['txt_Contact']) == "") {
            $error[] = 'txt_Contact';
            $Flag = 1;
        } else {
            $success[] = 'txt_Contact';
        }
        if (trim($values['txt_Contact_Titles']) == "") {
            $error[] = 'txt_Contact_Titles';
            $Flag = 1;
        } else {
            $success[] = 'txt_Contact_Titles';
        }
        if (trim($values['txt_Contact_Phone']) == "") {
            $error[] = 'txt_Contact_Phone';
            $Flag = 1;
        } else {
            $success[] = 'txt_Contact_Phone';
        }
        if (trim($values['txt_Contact_EMail_1']) == "") {
            $error[] = 'txt_Contact_EMail_1';
            $Flag = 1;
        } else {
            $success[] = 'txt_Contact_EMail_1';
        }
        if ($values['ddl_Class'] == "高中" || $values['ddl_Class'] == "大學") {
            if (trim($values['txt_School']) == "") {
                $error[] = 'txt_School';
                $Flag = 1;
            } else {
                $success[] = 'txt_School';
            }
        }
        if ($Flag != 0) {
            return [
                "status" => 'failure',
                "message" => "尚有題目未回答或內容錯誤，請檢查後進行下一部分。",
                "success" => $success,
                "error" => $error
            ];
        }
        if ($values['check_school'] > 0) {
            return [
                "status" => 'failure',
                "message" => "您所選之學校已有申請資料，可詢問校內校務人員或中心人員詢問學號帳號相關資料。",
                "success" => $success,
                "error" => $error
            ];
        }
        unset($values["check_school"]);

        $sql = "INSERT INTO SchoolList (
                    [CityId], [PostId], [Class], [SchoolID], [SchoolName], [Used], 
                    [Principal], [Senate], 
                    [Contact], [Contact_Titles], [Contact_Phone], 
                    [Contact_EMail_1], [UpInfo],
                    [Contact_EMail_2],
                    [School_C_1], [School_C_2], [School_C_3], [School_C_4], [School_C_5], [School_C_6], [School_C_Other], [School_C_Total], 
                    [School_S_1], [School_S_2], [School_S_3], [School_S_4], [School_S_5], [School_S_6], [School_S_Other], [School_S_Total], 
                    [Exam_C_1], [Exam_C_2], [Exam_C_3], [Exam_C_4], [Exam_C_5], [Exam_C_6], [Exam_C_Other], [Exam_C_Total], 
                    [Exam_S_1], [Exam_S_2], [Exam_S_3], [Exam_S_4], [Exam_S_5], [Exam_S_6], [Exam_S_Other], [Exam_S_Total]
                ) VALUES ( 
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
        // return $values;
        // $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return ["status" => "success"];
    }

    public function post_set_password($data)
    {
        $values = [
            "sid" => '',
            "email" => '',
            'passwd' => '',
            'ApplyFiles' => '',
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[SchoolList] 
                Set [ApplyFiles] = :ApplyFiles
                , [Contact_EMail_1] = :email
                , [PassWD] = :passwd
                WHERE [Sid] = :sid
            ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            $status = [
                "status" => "failure",
                "message" => "上傳失敗"
            ];
            return $status;
        }
        $status = [
            "status" => "success",
            "message" => "上傳成功，請注意郵件內通知信件"
        ];
        return $status;
    }


    public function get_online_apply_school_city_list($data)
    {
        $sql = "SELECT [CityName] 
                FROM [Literacy].[dbo].[City] 
                ORDER BY [OrderBy]";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_online_apply_school_area_list($data)
    {
        $values = [
            "CityName" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT DISTINCT [Area] 
                FROM [Literacy].[dbo].[School] 
                WHERE ([City] = :CityName) 
                ORDER BY [Area]
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function patch_island_news_click($data)
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

        $sql = "Update [Literacy].[dbo].[News] Set 
                [News].[Counter] = (
                    (SELECT [Counter] FROM [Literacy].[dbo].[News] WHERE [Nid] = :Nid)+1
                    )
                Where [News].[Nid] = :Nid1
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
    
    public function patch_learn_news_click($data)
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

        $sql = "Update [Learn].[dbo].[News] Set 
                [News].[Counter] = (
                    (SELECT [Counter] FROM [Learn].[dbo].[News] WHERE [Nid] = :Nid)+1
                    )
                Where [News].[Nid] = :Nid1
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

    public function getNewList_island($data)
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
            $stmt_string['Nid'] .= "AND [Portal].[dbo].[News].[Nid] = :Nid ";
            $stmt_array['Nid'] = $values['Nid'];
        }
        $top = "";
        if (array_key_exists('three', $data)) {
            $top = "TOP 5";
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
                FROM [Portal].[dbo].[News]
                WHERE (datediff(d,Getdate(), AddTime) <= 0)
                {$stmt_string['NewsClass']}
                {$stmt_string['Title']}
                {$stmt_string['Nid']}
                ORDER BY [Portal].[dbo].[News].[AddTime] DESC
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

    public function get_problem_count_island($data)
    {

        $sql = "SELECT COUNT([Pid]) 'Count'
                FROM [Literacy].[dbo].[Island_Problem]
        ";

        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_problem_island($data)
    {
        $values = [
            'Title' => '',
            'Pid' => null
        ];
        $stmt_string = [];
        $stmt_array = [];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (!empty($values['Title'])) {
            $stmt_string['Title'] = " AND [Title] like '%'+:Title+'%'";
            $stmt_array['Title'] = $values['Title'];
        }

        $sql = "SELECT [Pid]
                    ,[Title]
                    ,[Counter]
                    ,[Contents]
                    ,convert(varchar, [AddTime], 102)[AddTime]
                FROM [Island_Problem]
                WHERE (datediff(d,Getdate(), AddTime) <= 0)
                {$stmt_string['Title']}
                ORDER BY [Island_Problem].[AddTime] DESC
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

    public function get_achievement_island($data)
    {
        $values = [
            'Aid' => null
        ];
        $string = '';

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (!is_null($values['Aid'])) {
            $string = "WHERE [Literacy].[dbo].[Achievement_island].[Aid] = :Aid ";
            $values['Aid'] = $data['Aid'];
        }

        $sql = "SELECT [Aid]
                ,[Title]
                ,[Contents]
                ,[Photo]
                ,[AddTime]
                FROM [Literacy].[dbo].[Achievement_island]
                {$string}
        ";
        // var_dump($sql);
        // exit(0);
        $sth = $this->container->db->prepare($sql);
        if (!is_null($values['Aid'])) {
            $sth->execute($values);
        }
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getNewList_learn($data)
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
            $stmt_string['Nid'] .= "AND [Learn].[dbo].[News].[Nid] = :Nid ";
            $stmt_array['Nid'] = $values['Nid'];
        }
        $top = "";
        if (array_key_exists('three', $data)) {
            $top = "TOP 5";
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
                FROM [Learn].[dbo].[News]
                WHERE (datediff(d,Getdate(), AddTime) <= 0)
                {$stmt_string['NewsClass']}
                {$stmt_string['Title']}
                {$stmt_string['Nid']}
                ORDER BY [Learn].[dbo].[News].[AddTime] DESC
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

    public function get_online_exam_people($data)
    {

        $sql = "SELECT SUM(
                            CASE WHEN 
                            DATEDIFF(Minute, [AddTime],  GETDATE()) <= 60 THEN 1
                            ELSE 0 END 
                        ) people
                FROM [Literacy].[dbo].[Exam_Word_Score]
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_COLUMN);
        return $result;
    }
    public function get_online_apply_school_school_list($data)
    {
        $values = [
            "CityName" => '',
            'Area' => '',
            'Class' => '',
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [Sid], [SchoolID], [SchoolName] 
                FROM [School] WHERE ([City] = :CityName) 
                AND ([Area] = :Area) 
                AND ([Class] = :Class) 
                ORDER BY [SchoolName]
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_user_role($data)
    {
        $user_role = [];
        if (array_key_exists('StuUserInfo', $data)) {
            $user_role[] = 'student';
        }
        if (array_key_exists('SchUserInfo', $data)) {
            $user_role[] = 'school';
            if (!array_key_exists('Login', $data)) {
                goto schoolCheck;
            }
            if (!array_key_exists('MasterSchool', $data['Login'])) {
                goto schoolCheck;
            }
            if ($data['Login']['MasterSchool'] === 'Master') {
                $user_role[] = 'master';
            }
        }
        schoolCheck:
        if (array_key_exists('CityUserInfo', $data)) {
            $user_role[] = 'city';
        }
        if (array_key_exists('AdminUserInfo', $data)) {
            $user_role[] = 'admin';
        }
        return [
            'user_role' => $user_role
        ];
    }
    public function set_user_role($data)
    {
        $values = [
            'user_role' => []
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $data = $values['user_role'];
        session_start();
        if (in_array('student', $data))
            $_SESSION['StuUserInfo'] = [];
        if (in_array('school', $data))
            $_SESSION['SchUserInfo'] = [];
        if (in_array('master', $data)) {
            $_SESSION['SchUserInfo'] = [];
            $_SESSION['Login'] = [
                'MasterSchool' => 'Master'
            ];
        }
        if (in_array('city', $data))
            $_SESSION['CityUserInfo'] = [];
        if (in_array('admin', $data))
            $_SESSION['AdminUserInfo'] = [];
        session_write_close();
        return [
            'status' => 'success'
        ];
    }

    public function get_learn_word_learned($data)
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
                    SELECT ROW_NUMBER() OVER(ORDER BY dt.learn_times DESC) AS row_num 
						, RANK() OVER(ORDER BY dt.learn_times DESC) AS 'rank'
                        , [Learn_Word_Romanization].[nid]
                        
                        , [Learn_Word_Romanization].[word] 
                        , [Learn_Word_Romanization].[part] 
                        , [Learn_Word_Romanization].[stroke]  
                        , dt.learn_times
                    FROM (
                        SELECT [nid],COUNT([nid]) learn_times
                        FROM [Literacy].[dbo].[Learn_Task_Word]
                        GROUP BY [Learn_Task_Word].[nid] 
                        )dt
                    LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Word_Romanization].[nid] = dt.[nid] 
                    
                    ) AS selection
                WHERE selection.row_num > :start 
                AND selection.row_num <= :length
                ORDER BY selection.learn_times DESC
        ";

        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_word_learned_count($data)
    {
        $values = [];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT COUNT(*) AS 'count'
                FROM (
                    SELECT RANK() OVER(ORDER BY dt.learn_times DESC) AS row_num
                        , [Learn_Word_Romanization].[nid]
                        , [Learn_Word_Romanization].[word] 
                        , [Learn_Word_Romanization].[part] 
                        , [Learn_Word_Romanization].[stroke] 
                        , dt.learn_times
                    FROM (
                        SELECT [nid],COUNT([nid]) learn_times
                        FROM [Literacy].[dbo].[Learn_Task_Word]
                        GROUP BY [Learn_Task_Word].[nid] 
                        )dt
                    LEFT JOIN [Literacy].[dbo].[Learn_Word_Romanization] ON [Learn_Word_Romanization].[nid] = dt.[nid] 
                ) AS selection
        ";

        $sth = $this->container->db->prepare($sql);
        $result = $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}
