<?php

use PHPMailer\PHPMailer\PHPMailer;
use Slim\Views\PhpRENDerer;
use Slim\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class city
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function get_report_current_rank_person($data)
    {
        $values = [
            "CityId" => ''
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
            SELECT [Teacher].[Class],[SchoolList].SchoolName,[SchoolList].CityId CityName ,[SchoolList].[PostId] PostName ,dt.[Pid],dt.[Grade],dt.[StuName],dt.[current_word_count],dt.[total_word_count],dt.[score],dt.[Class],dt.[rank]
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
                LEFT JOIN [Literacy].[dbo].[SchoolList] ON [Teacher].[Sid] = [SchoolList].[Sid] 
                WHERE [SchoolList].[CityId] = :CityId
            )dt
            LEFT JOIN [Literacy].[dbo].[Student] ON dt.Pid = [Student].[Pid]
            LEFT JOIN [Literacy].[dbo].[Teacher] ON [Student].Tid =  Teacher.Tid
            LEFT JOIN [Literacy].[dbo].[SchoolList] ON [Student].[Sid] = [SchoolList].[Sid] 
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

    public function get_report_current_rank_group($data)
    {
        $values = [
            "CityId" => ''
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
                SELECT [Learn_Group].[GroupName],SchoolList.[PostId],SchoolList.SchoolName , [Teacher].[Grade], [Teacher].[Class],[task_count].task_count,finish_count.finish_count,
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
                LEFT JOIN [Literacy].[dbo].SchoolList ON SchoolList.[Sid] = [Teacher].Sid
                WHERE SchoolList.[CityId] = :CityId
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

    public function get_report_current_rank_avg_word($data)
    {
        $values = [
            "CityId" => ''
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "WITH [with] AS (
                    SELECT [SchoolList].[CityId],class_count.[Grade],COUNT(*) class_count,COALESCE(SUM(word_count),0)word_count,COALESCE(SUM(student_count),0)student_count
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
                    LEFT JOIN [Literacy].[dbo].[SchoolList] ON class_count.[Sid] = [SchoolList].[Sid]
                    WHERE [SchoolList].[CityId] = :CityId
                    GROUP BY [SchoolList].[CityId],class_count.[Grade]
                )
                SELECT '班級數' [name]
                    ,SUM( CASE WHEN Grade = '一' THEN class_count ELSE 0 END) '一年級'
                    ,SUM( CASE WHEN Grade = '二' THEN class_count ELSE 0 END) '二年級'
                    ,SUM( CASE WHEN Grade = '三' THEN class_count ELSE 0 END) '三年級'
                    ,SUM( CASE WHEN Grade = '四' THEN class_count ELSE 0 END) '四年級'
                    ,SUM( CASE WHEN Grade = '五' THEN class_count ELSE 0 END) '五年級'
                    ,SUM( CASE WHEN Grade = '六' THEN class_count ELSE 0 END) '六年級'
                FROM [with]
                GROUP BY [with].[CityId] 
                UNION ALL(
                    SELECT '學生數' [name]
                        ,SUM( CASE WHEN Grade = '一' THEN student_count ELSE 0 END) '一年級'
                        ,SUM( CASE WHEN Grade = '二' THEN student_count ELSE 0 END) '二年級'
                        ,SUM( CASE WHEN Grade = '三' THEN student_count ELSE 0 END) '三年級'
                        ,SUM( CASE WHEN Grade = '四' THEN student_count ELSE 0 END) '四年級'
                        ,SUM( CASE WHEN Grade = '五' THEN student_count ELSE 0 END) '五年級'
                        ,SUM( CASE WHEN Grade = '六' THEN student_count ELSE 0 END) '六年級'
                    FROM [with]
                    GROUP BY [with].[CityId] 
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
                    GROUP BY [with].[CityId] 
                )
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_report_current_school_avg_word_grades($data)
    {
        $values = [
            "Sid" => ''
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "WITH [with] AS (
                SELECT [View_Student_now].*,[Teacher].[Class],word_count.word_count,[SchoolList].[SchoolName]
                FROM [Literacy].[dbo].[View_Student_now]
                LEFT JOIN [Literacy].[dbo].[Teacher] ON [Teacher].[Tid] = [View_Student_now].[Tid]
                LEFT JOIN (
                    SELECT [Learn_Task_Word_Student].[Pid], 
                        COUNT( CASE WHEN  [Learn_Task_Word_Student].step = 9 THEN 1 END) word_count
                    FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                    GROUP BY [Learn_Task_Word_Student].[Pid] 
                )word_count ON [View_Student_now].[Pid] = word_count.[Pid] 
                LEFT JOIN [Literacy].[dbo].[SchoolList] ON [Teacher].[Sid] = [SchoolList].[Sid]
                WHERE [SchoolList].[Sid] =:Sid AND [View_Student_now].[Grade] IS NOT NULL
            )
            SELECT *
            FROM(
                SELECT [with].[SchoolName],[with].[Sex] [性別],[with].[Grade] [年級],COUNT(*) [學生數]
                ,CAST(COALESCE(MIN([with].word_count),0)AS varchar)+'-'+CAST(COALESCE(MAX([with].word_count),0) AS varchar) '學習生字量全距'
                ,COALESCE(CAST(ROUND (STDEV ([with].word_count),2) AS varchar),'0.00') '標準差'
                ,COALESCE(AVG([with].word_count),0) [學習生字量平均數]
                FROM [with]
                GROUP BY [with].[SchoolName],[with].[Sex],[with].[Grade]
                UNION ALL(
                    SELECT [with].[SchoolName],'全體' [性別],[with].[Grade] [年級],COUNT(*) [學生數]
                    ,CAST(COALESCE(MIN([with].word_count),0)AS varchar)+'-'+CAST(COALESCE(MAX([with].word_count),0) AS varchar) '學習生字量全距'
                    ,COALESCE(CAST(ROUND (STDEV ([with].word_count),2) AS varchar),'0.00') '標準差'
                    ,COALESCE(AVG([with].word_count),0) [學習生字量平均數]
                    FROM [with]
                    GROUP BY [with].[SchoolName],[with].[Grade]
                )
            )dt
            ORDER BY CASE dt.[年級]
                    WHEN '一' THEN 9
                    WHEN '二' THEN 8
                    WHEN '三' THEN 7
                    WHEN '四' THEN 6
                    WHEN '五' THEN 5
                    WHEN '六' THEN 4
                    WHEN '七' THEN 3
                    WHEN '八' THEN 2
                    WHEN '九' THEN 1
                    ELSE 0 
                END DESC,
                CASE 
                    WHEN dt.[性別] = '男' THEN 2
                    WHEN dt.[性別] = '女' THEN 1
                    ELSE 0
                END DESC
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_report_current_rank_avg_word_grades($data)
    {
        $values = [
            "CityId" => ''
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $sql = "WITH [with] AS (
                SELECT [View_Student_now].*,[Teacher].[Class],word_count.word_count,[SchoolList].[CityId]
                FROM [Literacy].[dbo].[View_Student_now]
                LEFT JOIN [Literacy].[dbo].[Teacher] ON [Teacher].[Tid] = [View_Student_now].[Tid]
                LEFT JOIN (
                    SELECT [Learn_Task_Word_Student].[Pid], 
                        COUNT( CASE WHEN  [Learn_Task_Word_Student].step = 9 THEN 1 END) word_count
                    FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                    GROUP BY [Learn_Task_Word_Student].[Pid] 
                )word_count ON [View_Student_now].[Pid] = word_count.[Pid] 
                LEFT JOIN [Literacy].[dbo].[SchoolList] ON [Teacher].[Sid] = [SchoolList].[Sid]
                WHERE [SchoolList].[CityId] =:CityId  AND [View_Student_now].[Grade] IS NOT NULL
            )
            SELECT *
            FROM(
                SELECT [with].[Grade] '年級',[with].[Sex] '性別',COUNT(*) '學生數'
                    ,CAST(COALESCE(MIN([with].word_count),0)AS varchar)+'-'+CAST(COALESCE(MAX([with].word_count),0) AS varchar) '學習生字量全距'
                    ,COALESCE(AVG([with].word_count),0) '學習生字量平均數'
                    ,COALESCE(CAST(ROUND (STDEV ([with].word_count),2) AS varchar),'0.00')'標準差'
                FROM [with]
                GROUP BY [with].[Sex],[with].[Grade]
                UNION ALL(
                    SELECT [with].[Grade], '全體',COUNT(*) '學生數'
                    ,CAST(COALESCE(MIN([with].word_count),0)AS varchar)+'-'+CAST(COALESCE(MAX([with].word_count),0) AS varchar) '學習生字量全距'
                    ,COALESCE(AVG([with].word_count),0) '學習生字量平均數'
                    ,COALESCE(CAST(ROUND (STDEV ([with].word_count),2) AS varchar),'0.00') '標準差'
                    FROM [with]
                    GROUP BY [with].[CityId],[with].[Grade]
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

    public function get_report_current_school_word($data)
    {
        $values = [
            "CityId" => null,
            "Sid" => null
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $stmt_string = ['query' => "", 'select' => '', 'group' => ''];
        if (is_null($values['CityId'])) {
            unset($values['CityId']);
            $stmt_string = ['query' => "WHERE [SchoolList].[Sid] = :Sid", 'select' => ',[SchoolList].[SchoolName]', 'group' => ',[SchoolList].[SchoolName]'];
        }
        if (is_null($values['Sid'])) {
            unset($values['Sid']);
            $stmt_string['query'] = "WHERE [SchoolList].[CityId] = :CityId";
        }
        if (!is_null($values['Sid']) && !is_null($values['CityId'])) {
            unset($values['Sid']);
            unset($values['CityId']);
            $stmt_string['query'] = "WHERE [SchoolList].[PostId] IS NOT NULL AND [SchoolList].[PostId] != ''";
        }
        $sql = "WITH [with] AS (
                SELECT [SchoolList].[PostId],[SchoolList].[SchoolName],[SchoolList].[Class],[SchoolList].[Sid],class_count.[Grade],COUNT(*) class_count,COALESCE(SUM(word_count),0)word_count,COALESCE(SUM(student_count),0)student_count
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
                LEFT JOIN [Literacy].[dbo].[SchoolList] ON class_count.[Sid] = [SchoolList].[Sid]
                {$stmt_string['query']}
                GROUP BY [SchoolList].[PostId],[SchoolList].[SchoolName],[SchoolList].[Class],[SchoolList].[Sid],class_count.[Grade]
            )
            
            SELECT [with].[PostId] [區域],[with].[SchoolName] [學校],[with].[Sid]
            ,SUM( CASE WHEN Grade = '一' AND Class='國小' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '一' THEN student_count ELSE 0 END),0), 1) '一年級'
            ,SUM( CASE WHEN Grade = '二' AND Class='國小' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '二' THEN student_count ELSE 0 END),0), 1) '二年級'
            ,SUM( CASE WHEN Grade = '三' AND Class='國小' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '三' THEN student_count ELSE 0 END),0), 1) '三年級'
            ,SUM( CASE WHEN Grade = '四' AND Class='國小' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '四' THEN student_count ELSE 0 END),0), 1) '四年級'
            ,SUM( CASE WHEN Grade = '五' AND Class='國小' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '五' THEN student_count ELSE 0 END),0), 1) '五年級'
            ,SUM( CASE WHEN Grade = '六' AND Class='國小' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '六' THEN student_count ELSE 0 END),0), 1) '六年級'
            ,SUM( CASE WHEN Grade = '一' AND Class='國中' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '一' THEN student_count ELSE 0 END),0), 1) '七年級'
            ,SUM( CASE WHEN Grade = '二' AND Class='國中' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '二' THEN student_count ELSE 0 END),0), 1) '八年級'
            ,SUM( CASE WHEN Grade = '三' AND Class='國中' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '三' THEN student_count ELSE 0 END),0), 1) '九年級'
            FROM [with]
            GROUP BY [with].[PostId],[with].[SchoolName],[with].[Sid]
            ORDER BY [with].[PostId],[with].[SchoolName] 
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_report_current_school_word_avg($data)
    {
        $values = [
            "CityId" => null,
            "Sid" => null
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }
        $stmt_string = ['query' => ""];
        if (is_null($values['CityId'])) {
            unset($values['CityId']);
            $stmt_string = ['query' => "WHERE [SchoolList].[Sid] = :Sid"];
        }
        if (is_null($values['Sid'])) {
            unset($values['Sid']);
            $stmt_string['query'] = "WHERE [SchoolList].[CityId] = :CityId";
        }
        $sql = "WITH [with] AS (
                SELECT [SchoolList].[CityId],[SchoolList].[Class],class_count.[Grade],COUNT(*) class_count,
                    COALESCE(SUM(word_count),0)word_count,COALESCE(SUM(student_count),0)student_count,
                    COALESCE(SUM(current_word_count),0)current_word_count{$stmt_string['select']}
                FROM(
                    SELECT [View_Student_now].[Sid],[View_Student_now].[Grade],[Teacher].[Class],
                        COUNT(*) student_count,
                        COALESCE(SUM(word_count.word_count),0)word_count,
                        COALESCE(SUM(word_count.current_word_count),0)current_word_count
                    FROM [Literacy].[dbo].[View_Student_now]
                    LEFT JOIN [Literacy].[dbo].[Teacher] ON [Teacher].[Tid] = [View_Student_now].[Tid] 
                    LEFT JOIN (
                        SELECT [Learn_Task_Word_Student].[Pid], 
                            COUNT( CASE WHEN  [Learn_Task_Word_Student].step = 9 THEN 1 END) word_count, 
                            COUNT( CASE WHEN  [Learn_Task_Word_Student].step = 9 
                                AND [View_Learn_Task].[task_year] = [View_Learn_Task].[now_year] 
                                AND CASE WHEN DATEPART(mm, GETDATE()) >= 7 OR
                                        DATEPART(mm, GETDATE()) <= 2 THEN '上' WHEN DATEPART(mm, GETDATE()) >= 1 OR
                                        DATEPART(mm, GETDATE()) <= 8 THEN '下' END = [View_Learn_Task].[task_term]
                            THEN 1 END) current_word_count
                        FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                        LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_word_id] = [Learn_Task_Word_Student].[task_word_id] 
                        LEFT JOIN [Literacy].[dbo].[View_Learn_Task] ON [Learn_Task_Word].[task_id] = [View_Learn_Task].[TaskId] 
                        GROUP BY [Learn_Task_Word_Student].[Pid] 
                    )word_count ON [View_Student_now].[Pid] = word_count.[Pid] 
                    GROUP BY [View_Student_now].[Sid],[View_Student_now].[Grade],[Teacher].[Class]
                )class_count
                LEFT JOIN [Literacy].[dbo].[SchoolList] ON class_count.[Sid] = [SchoolList].[Sid]
                {$stmt_string['query']}
                GROUP BY [SchoolList].[CityId],class_count.[Grade],[SchoolList].[Class]
            )
            
            SELECT SUM( CASE WHEN Grade = '一' AND Class='國小' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '一' AND Class='國小' THEN student_count ELSE 0 END),0), 1)[word], '一年級已學習生字量'[type]
            FROM [with]
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '一' AND Class='國小' THEN current_word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '一' AND Class='國小' THEN student_count ELSE 0 END),0), 1)[word], '一年級本學期生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '二' AND Class='國小' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '二' AND Class='國小' THEN student_count ELSE 0 END),0), 1)[word], '二年級已學習生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '二' AND Class='國小' THEN current_word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '二' AND Class='國小' THEN student_count ELSE 0 END),0), 1)[word], '二年級本學期生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '三' AND Class='國小' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '三' AND Class='國小' THEN student_count ELSE 0 END),0), 1)[word], '三年級已學習生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '三' AND Class='國小' THEN current_word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '三' AND Class='國小' THEN student_count ELSE 0 END),0), 1)[word], '三年級本學期生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '四' AND Class='國小' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '四' AND Class='國小' THEN student_count ELSE 0 END),0), 1)[word], '四年級已學習生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '四' AND Class='國小' THEN current_word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '四' AND Class='國小' THEN student_count ELSE 0 END),0), 1)[word], '四年級本學期生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '五' AND Class='國小' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '五' AND Class='國小' THEN student_count ELSE 0 END),0), 1)[word], '五年級已學習生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '五' AND Class='國小' THEN current_word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '五' AND Class='國小' THEN student_count ELSE 0 END),0), 1)[word], '五年級本學期生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '六' AND Class='國小' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '六' AND Class='國小' THEN student_count ELSE 0 END),0), 1)[word], '六年級已學習生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '六' AND Class='國小' THEN current_word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '六' AND Class='國小' THEN student_count ELSE 0 END),0), 1)[word], '六年級本學期生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '七' AND Class='國中' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '一' AND Class='國中' THEN student_count ELSE 0 END),0), 1)[word], '七年級已學習生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '七' AND Class='國中' THEN current_word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '一' AND Class='國中' THEN student_count ELSE 0 END),0), 1)[word], '七年級本學期生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '八' AND Class='國中' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '二' AND Class='國中' THEN student_count ELSE 0 END),0), 1)[word], '八年級已學習生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '八' AND Class='國中' THEN current_word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '二' AND Class='國中' THEN student_count ELSE 0 END),0), 1)[word], '八年級本學期生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '九' AND Class='國中' THEN word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '三' AND Class='國中' THEN student_count ELSE 0 END),0), 1)[word], '九年級已學習生字量'[type]
                FROM [with]
            )
            UNION ALL(
                SELECT SUM( CASE WHEN Grade = '九' AND Class='國中' THEN current_word_count ELSE 0 END)/ISNULL(NULLIF(SUM( CASE WHEN Grade = '三' AND Class='國中' THEN student_count ELSE 0 END),0), 1)[word], '九年級本學期生字量'[type]
                FROM [with]
            )
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_report_area_rank_avg_word_grades($data)
    {
        $values = [
            "CityId" => '',
            "CityId1" => ''
        ];

        foreach ($values as $key => $value) {
            array_key_exists($key, $data) && $values[$key] = $data[$key];
        }

        $string = "WHERE";
        $area = "";
        $grade = "";
        $check = false;

        if (array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            $string = " [View_Learn_Task].[EndDate] BETWEEN :start_date AND :end_date AND";
            $check = true;
        } else {
            $month = date("n");
            $year = date("Y");
            if ($month <= 12 && 8 <= $month || $month == 1) {
                $years = date("Y") + 1;
                $string = " [View_Learn_Task].[EndDate] BETWEEN '$year'+'-8-1' AND '$years'+'-1-31' AND";
                $check = true;
            } else {
                $string = " [View_Learn_Task].[EndDate] BETWEEN '$year'+'-2-1' AND $year+'-7-31' AND";
                $check = true;
            }
        }

        if (array_key_exists('year', $data)) {
            $string .= " [View_Learn_Task].[task_year] = :year AND";
            $values['year'] = $data['year'];
            $check = true;
        }
        if (array_key_exists('term', $data)) {
            $string .= " [View_Learn_Task].[task_term] = :term AND";
            $values['term'] = $data['term'];
            $check = true;
        }

        if ($check) {
            $string = rtrim($string, "AND");
        } else {
            $string = ltrim($string, "WHERE");
        }


        if (array_key_exists('area', $data)) {
            $area .= "WHERE [with].[PostId] = :area";
            $values['area'] = $data['area'];
        }

        if (array_key_exists('grade', $data)) {
            $grade .= "AND [Teacher].[Grade] = :grade";
            $values['grade'] = $data['grade'];
        }

        $sql = "WITH [with] AS (
                    SELECT [View_Student_now].*,[Teacher].[Class],word_count.word_count,[SchoolList].[CityId],[SchoolList].[PostId]
                    FROM [Literacy].[dbo].[View_Student_now]
                    LEFT JOIN [Literacy].[dbo].[Teacher] ON [Teacher].[Tid] = [View_Student_now].[Tid]
                    LEFT JOIN (
                    SELECT [Learn_Task_Word_Student].[Pid], 
                        COUNT( CASE WHEN  [Learn_Task_Word_Student].step = 9 THEN 1 END) word_count
                        FROM [Literacy].[dbo].[Learn_Task_Word_Student]
                        LEFT JOIN  [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id] 
                        LEFT JOIN [Literacy].[dbo].[View_Learn_Task] ON [Learn_Task_Word].[task_id] = [View_Learn_Task].[TaskId] 
                        --WHERE [View_Learn_Task].[task_year] = 2022 
                        --AND [View_Learn_Task].[task_term] = '下'
                        {$string}
                        GROUP BY [Learn_Task_Word_Student].[Pid]
                    )word_count ON [View_Student_now].[Pid] = word_count.[Pid] 
                    LEFT JOIN [Literacy].[dbo].[SchoolList] ON [Teacher].[Sid] = [SchoolList].[Sid]
                    WHERE [SchoolList].[CityId] = :CityId AND [View_Student_now].[Grade] IS NOT NULL
                    {$grade}
                )
        
                SELECT [with].[PostId], dt.school_count, COUNT(*) student_count
                    ,COALESCE(SUM([with].word_count),0)word_count
                    ,COALESCE(MIN([with].word_count),0) min_word_count
                    ,COALESCE(MAX([with].word_count),0) max_word_count
                    ,COALESCE(STDEV([with].word_count),0) stdev_word_count
                    ,COALESCE(AVG([with].word_count),0) avg_word_count
                FROM [with]
                LEFT JOIN (
                    SELECT [PostId], COUNT([Sid]) school_count
                    FROM [Literacy].[dbo].[SchoolList]
                    WHERE [SchoolList].[CityId] = :CityId1
                    GROUP BY [PostId]
                )dt ON dt.[PostId] = [with].[PostId]
                {$area}
                GROUP BY [with].[PostId], dt.school_count 
    
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function get_learn_city_account_data($data)
    {
        $values = [
            "CityId" => ''
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [City].[CityId]
                    , [City].[CityName]+'政府' CityName
                    , [City].[PassWD]
                    , [City].[CityEmail]
                    , [City].[Photo]
                    , [City].[UpdateTime]
                FROM [Literacy].[dbo].[City]
                WHERE [CityName] = :CityId
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function get_learn_city_account($data)
    {
        $values = [
            "CityId" => 0
        ];

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        $sql = "SELECT [City].[CityName]
                    , [City].[CityEmail]
                FROM [Literacy].[dbo].[City]
                WHERE [CityName] = :CityId
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function post_city_photo($data)
    {
        $values = [
            "CityId" => '',
            "photo" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[City] Set 
                [Photo] = :photo
                WHERE [CityName] = :CityId
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

    public function patch_learn_city_mail($data)
    {
        $values = [
            "CityId" => '',
            "mail" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "UPDATE [Literacy].[dbo].[City] Set 
                [CityEMail] = :mail
                WHERE [CityName] = :CityId

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

    public function patch_city_passwd($data)
    {
        $values = [
            "CityId" => '',
            "passwd" => '',
            "passwd_again" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        if (!($values['passwd'] == $values['passwd_again'])) {
            return [
                "status" => "failure",
                "message" => "請檢查「確認密碼」是否有誤!"
            ];
        } else {
            unset($values['passwd_again']);

            $sql = "Update [Literacy].[dbo].[City] Set 
                    [PassWD] = :passwd
                    , [UpdateTime] = GETDATE()
                    WHERE [CityName] = :CityId
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

    public function check_city_origin_passwd($data)
    {
        $values = [
            "CityId" => '',
            "passwd_old" => ''
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT [CityId]
                FROM [Literacy].[dbo].[City] 
                WHERE [CityName] = :CityId
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

    public function get_learn_city_report_summary($data)
    {
        $values = [
            "CityId" => '',
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT COUNT(DISTINCT dt.Pid) '本學期學習學生數',COUNT(DISTINCT dt.Sid) '本學期學習學校數',
                CAST(COALESCE(MIN(current_is_finish),0)AS varchar(MAX))+'-'+CAST(COALESCE(MAX(current_is_finish),0)AS varchar(MAX)) '本學期學習生字量全距',
                COALESCE(AVG(current_is_finish),0) '本學期學習生字量平均數'
            FROM (
                SELECT dt.[Pid],dt.[Sid],SUM(dt.current_is_finish)current_is_finish
                FROM(
                    SELECT [Learn_Task].[Pid],[Student].[Sid],[Learn_Task_Word].[task_word_id],
                        CASE WHEN COUNT(CASE WHEN step IS NULL OR step = 9 THEN 1 END)>0 THEN 0 ELSE 1 END current_is_finish
                    FROM [Literacy].[dbo].[View_Learn_Task] [Learn_Task] 
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word] ON [Learn_Task_Word].[task_id] = [Learn_Task].[TaskId]
                    LEFT JOIN [Literacy].[dbo].[Learn_Task_Word_Student] ON 
                        [Learn_Task_Word_Student].[task_word_id] = [Learn_Task_Word].[task_word_id]
                        AND [Learn_Task_Word_Student].[Pid] = [Learn_Task].[Pid]
                    LEFT JOIN [Literacy].[dbo].[Student] ON [Student].[Pid] = [Learn_Task].[Pid]
                    LEFT JOIN [Literacy].[dbo].[SchoolList] ON [Student].[Sid] = [SchoolList].[Sid]
                    WHERE CASE WHEN DATEPART(mm, [Learn_Task].[ApplyDate]) >= 7 OR
                            DATEPART(mm, GETDATE()) <= 2 THEN '上' WHEN DATEPART(mm, GETDATE()) >= 1 OR
                            DATEPART(mm, GETDATE()) <= 8 THEN '下' END = [Learn_Task].[task_term]
                        AND [Learn_Task].now_year = [Learn_Task].task_year
                        AND [SchoolList].[CityId] = :CityId
                    GROUP BY [Learn_Task].[TaskId],[Student].[Sid],[Learn_Task].[Pid],[Learn_Task_Word].[task_word_id]
                )dt
                GROUP BY dt.[Pid],dt.[Sid]
            )dt
        ";
        $sth = $this->container->db->prepare($sql);
        if (!$sth->execute($values)) {
            $status = [
                "status" => "failure"
            ];
        }
        return $sth->fetch(PDO::FETCH_ASSOC);
    }
}
