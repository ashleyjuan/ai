<?php

use PHPMailer\PHPMailer\PHPMailer;
use Slim\Views\PhpRENDerer;
use Slim\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class student
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function get_student_data($data)
    {
        $values = [
            "Pid" => 0
        ];
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }

        $sql = "SELECT Student.Pid, Student.StuName, Student.StuNum, Student.SeatNum,
            Exam_Word_Score.Sid, Exam_Word_Score.Exam_Year + '學年度' + Exam_Word_Score.Exam_Term + '學期' + Exam_Word_Score.Exam_TKind AS Exam_TimePoint,
            Exam_Word_Score.LiteracyScore, Exam_Word_Score.Theta, Exam_Word_Score.PR_Value, Exam_Word_Score.StartTime, Exam_Word_Score.EndTime, Exam_Word_Score.AddTime, Exam_Word.Title AS WKind, Exam_Word_Score.ExamProgramKind,
            CASE WHEN Exam_Word_Score.Exam_Term = '上' AND Exam_Word_Score.Exam_TKind = '期初' THEN Average_1S
            WHEN Exam_Word_Score.Exam_Term = '上' AND Exam_Word_Score.Exam_TKind = '期末' THEN Average_1E
            WHEN Exam_Word_Score.Exam_Term = '下' AND Exam_Word_Score.Exam_TKind = '期初' THEN Average_2S
            WHEN Exam_Word_Score.Exam_Term = '下' AND Exam_Word_Score.Exam_TKind = '期末' THEN Average_2E
            ELSE NULL END AS Average ,
            CASE WHEN Exam_Word_Score.Exam_Term = '上' AND Exam_Word_Score.Exam_TKind = '期初' THEN Standard_1S
            WHEN Exam_Word_Score.Exam_Term = '上' AND Exam_Word_Score.Exam_TKind = '期末' THEN Standard_1E
            WHEN Exam_Word_Score.Exam_Term = '下' AND Exam_Word_Score.Exam_TKind = '期初' THEN Standard_2S
            WHEN Exam_Word_Score.Exam_Term = '下' AND Exam_Word_Score.Exam_TKind = '期末' THEN Standard_2E
            ELSE NULL END AS Standard ,
            CASE WHEN Exam_Word_Score.ExamProgramKind in ('A2','A3','A4','A5','A6','A7','A8','A09','A10','D1','D2','D3','D4','D5','D6') THEN CASE WHEN Exam_Word_Score.LiteracyScore <= 730 THEN '01' WHEN Exam_Word_Score.LiteracyScore >= 731 AND
            Exam_Word_Score.LiteracyScore <= 1210 THEN '02' WHEN Exam_Word_Score.LiteracyScore >= 1211 AND Exam_Word_Score.LiteracyScore <= 1300 THEN '03' WHEN Exam_Word_Score.LiteracyScore >= 1301 AND
            Exam_Word_Score.LiteracyScore <= 1560 THEN '04' WHEN Exam_Word_Score.LiteracyScore >= 1561 AND Exam_Word_Score.LiteracyScore <= 1870 THEN '05' WHEN Exam_Word_Score.LiteracyScore >= 1871 AND
            Exam_Word_Score.LiteracyScore <= 2290 THEN '0607' WHEN Exam_Word_Score.LiteracyScore >= 2291 AND Exam_Word_Score.LiteracyScore <= 2490 THEN '08' WHEN Exam_Word_Score.LiteracyScore >= 2491 AND
            Exam_Word_Score.LiteracyScore <= 2760 THEN '09' WHEN Exam_Word_Score.LiteracyScore >= 2761 AND Exam_Word_Score.LiteracyScore <= 2840 THEN '10' WHEN Exam_Word_Score.LiteracyScore >= 2841 AND
            Exam_Word_Score.LiteracyScore <= 3040 THEN '11' WHEN Exam_Word_Score.LiteracyScore >= 3041 THEN '12' ELSE NULL
            END ELSE CASE WHEN Exam_Word_Score.LiteracyScore <= 499 THEN '01' WHEN Exam_Word_Score.LiteracyScore >= 500 AND Exam_Word_Score.LiteracyScore <= 899 THEN '02' WHEN Exam_Word_Score.LiteracyScore >= 900 AND
            Exam_Word_Score.LiteracyScore <= 1129 THEN '03' WHEN Exam_Word_Score.LiteracyScore >= 1130 AND Exam_Word_Score.LiteracyScore <= 1449 THEN '04' WHEN Exam_Word_Score.LiteracyScore >= 1450 AND
            Exam_Word_Score.LiteracyScore <= 1999 THEN '05' WHEN Exam_Word_Score.LiteracyScore >= 2000 AND Exam_Word_Score.LiteracyScore <= 2249 THEN '06' WHEN Exam_Word_Score.LiteracyScore >= 2250 AND
            Exam_Word_Score.LiteracyScore <= 2599 THEN '07' WHEN Exam_Word_Score.LiteracyScore >= 2600 AND Exam_Word_Score.LiteracyScore <= 2899 THEN '08' WHEN Exam_Word_Score.LiteracyScore >= 2900 AND
            Exam_Word_Score.LiteracyScore <= 3099 THEN '09' WHEN Exam_Word_Score.LiteracyScore >= 3100 AND Exam_Word_Score.LiteracyScore <= 3299 THEN '10' WHEN Exam_Word_Score.LiteracyScore >= 3300 AND
            Exam_Word_Score.LiteracyScore <= 3499 THEN '11' WHEN Exam_Word_Score.LiteracyScore >= 3500 THEN '12' ELSE NULL END END AS ColorNum
            FROM Student INNER JOIN Exam_Word_Score ON Student.Pid = Exam_Word_Score.Pid 
            INNER JOIN Exam_Word ON Exam_Word_Score.Wid = Exam_Word.Wid
            WHERE Student.Pid = :Pid ORDER BY Exam_Word_Score.Sid DESC
        ";
        $sth = $this->container->db->prepare($sql);
        $sth->execute($values);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


}
