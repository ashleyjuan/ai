import React, { useEffect, useState } from "react"
import LayoutIsLogin from '../pages/layout_islogin';
import LearnLayoutIsLogin from '../pages/learn_layout_islogin';
import IslandLayoutIsLogin from '../pages/Island_layout_islogin';
import { Navigate, Outlet, useNavigate, useLocation } from "react-router-dom"
import { IeSquareFilled, LaptopOutlined, NotificationOutlined, UserOutlined } from '@ant-design/icons';
import axios from "axios";

const useAuth = () => {
    //get item from localstorage
    //後端api傳回身份管理
    let user

    const _user = localStorage.getItem("user")

    if (_user) {
        user = JSON.parse(_user)
    }
    if (user) {
        return {
            auth: true,
            role: user.role,
        }
    } else {
        return {
            auth: false,
            role: null,
        }
    }
}

//protected Route state

const ProtectedRoutes = (props) => {
    const { auth, role } = useAuth()
    const [nav, setNav] = useState([]);
    const [li, setLi] = useState([]);
    const navigate = useNavigate();

    function getItem(label, key, icon, children, disabled) {
        return {
            key,
            icon,
            children,
            label,
            disabled,
        };
    }
    useEffect(() => {
        if (role === 'admin' && props.roleRequired === 'admin' && props.topic == 'literacy') {
            setNav(
                [
                    getItem('管理者帳號管理', '/literacy/admin/SystemManager'),
                    getItem('管理者帳號動作紀錄', '/literacy/admin/SystemManager_Record'),
                    getItem('開啟前台首頁'),
                    getItem('管理員登出', '/'),
                ]
            )
            setLi([
                getItem('首頁管理', null, <UserOutlined />, [
                    getItem('---資訊區---', null, null, null, true),
                    getItem('（Ｏ）最新消息管理', '/literacy/admin/News'),
                    getItem('（Ｏ）字庫查詢編輯', '/literacy/admin/ExamWord'),
                    getItem('（Ｏ）資料下載編輯', '/literacy/admin/PAgreement'),
                    getItem('（Ｏ）聯絡我們編輯', '/literacy/admin/Contact'),
                    getItem('（Ｏ）傳遞喜閱手冊', '/literacy/admin/Manual'),
                    getItem('---區塊內容---', null, null, null, true),
                    getItem('（Ｏ）網站簡介編輯', '/literacy/admin/index'),
                    getItem('（Ｏ）彈跳視窗編輯', '/literacy/admin/ShowDialog'),
                ]),
                getItem('識字施測系統', null, <UserOutlined />, [
                    getItem('（Ｏ）識字施測情況報表', '/literacy/admin/ExamState'),
                    getItem('（Ｏ）識字施測結果報表', '/literacy/admin/Exam'),
                    getItem('（Ｏ）期間未施測報表', '/literacy/admin/Check_ExamState'),
                    getItem('－－－－－－－－－', null, null, null, true),
                    getItem('（Ｏ）識字施測題庫(A版)', '/literacy/admin/Exam_Word_EK3_C2'),
                    getItem('（Ｏ）識字施測題庫(B版,C版)', '/literacy/admin/Exam_Word'),
                    getItem('－－－－－－－－－', null, null, null, true),
                    getItem('批次分析識字量', '/literacy/admin/Exam_Literacy'),
                    getItem('施測參數設定', '/literacy/admin/Options'),
                    getItem('－－－－－－－－－', null, null, null, true),
                    getItem('線上識字量測驗-施測指導語', '/literacy/admin/ExamRM'),
                ]),
                getItem('學校資料與管理', null, <UserOutlined />, [
                    getItem('已登錄學校帳號管理', '/literacy/admin/SchoolMsg'),
                    getItem('（Ｏ）匯入上傳檔案列表', '/literacy/admin/UploadFile_View'),
                    getItem('（Ｏ）學校列表管理', '/literacy/admin/School'),
                    getItem('寄發通知信', '/literacy/admin/SendMail'),
                    getItem('全資料庫升年級', '/literacy/admin/School_Class_Up1Y'),
                    getItem('合併學生資料', '/literacy/admin/Student_merge'),
                ]),
                getItem('縣市資料與管理', null, <UserOutlined />, [
                    getItem('（Ｏ）縣市管理者帳號', '/literacy/admin/CityMsg'),
                ]),
                getItem('管理者管理', null, <UserOutlined />, [
                    getItem('（Ｏ）管理者帳號', '/literacy/admin/SystemManager'),
                    getItem('（Ｏ）管理者帳號紀錄', '/literacy/admin/SystemManager_record'),
                ]),
                // getItem('登出', '/', <UserOutlined />),

            ])
        } else if (role === 'admin' && props.roleRequired === 'admin' && props.topic == 'learn') {
            setNav(
                []
            )
            setLi([
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>首頁管理</span>, null, <UserOutlined />, [
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>---資訊區---</span>, null, null, null, true),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）最新消息管理</span>, '/learn/admin/News'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）字庫查詢編輯</span>, '/learn/admin/ExamWord'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）關於我們編輯</span>, '/learn/admin/AboutUs'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）聯繫我們編輯</span>, '/learn/admin/ContactUs'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）常見問題編輯</span>, '/learn/admin/CommonProblem'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）線上申請編輯</span>, '/learn/admin/OnlineApply'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）申請同意書編輯</span>, '/learn/admin/Apply'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>---區塊內容---</span>, null, null, null, true),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）網站簡介編輯</span>, ''),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）彈跳視窗編輯</span>, ''),
                ]),
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>學習任務管理</span>, null, <UserOutlined />, [
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>---報表區---</span>, null, null, null, true),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）學習任務報表</span>, '/learn/admin/CurrentMissionReport'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）學習任務資料查詢</span>, '/learn/admin/CurrentMissionSearch'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）學習生字量報表</span>, '/learn/admin/CurrentLearnReport'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）學習生字量資料查詢</span>, '/learn/admin/CurrentLearnSearch'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）老師常派字頻</span>, '/learn/admin/TeacherWordFrequency'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>---學習任務區---</span>, null, null, null, true),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）學習字庫編輯</span>, '/learn/admin/LearnWordWarehouse'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）各版本字庫編輯</span>, '/learn/admin/LearnWordWarehouseVersion'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）指導語編輯</span>, '/learn/admin/Slogans'),
                ]),
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>學校資料管理</span>, null, <UserOutlined />, [
                ]),
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>縣市資料管理</span>, null, <UserOutlined />, [
                ])
                // getItem('登出', '/', <UserOutlined />),
            ])
        } else if (role === 'city' && props.roleRequired === 'city' && props.topic == 'literacy') {
            setNav(
                []
            )
            setLi([
                getItem('（Ｏ）查詢區域施測情況', 'literacy//city/ExamState', <UserOutlined />),
                getItem('（Ｏ）查詢個別施測結果', 'literacy/city/ExamReport', <UserOutlined />),
                // getItem('登出', '/', <UserOutlined />),
            ])
        } else if (role === 'master' && props.roleRequired === 'master' && props.topic == 'literacy') {
            setNav(
                []
            )
            setLi([
                getItem('回首頁', '/literacy', <UserOutlined />),
                getItem('教師帳號管理', '/literacy/master/School_Teacher', <UserOutlined />),
                getItem('搜尋全校施測資料', '/literacy/master/School_Report', <UserOutlined />),
                getItem('觀看全校統計報表', '/literacy/master/School_Report2', <UserOutlined />),
                getItem('觀看班級施測報表', '/literacy/master/School_Report3', <UserOutlined />),
                getItem('校方資料更新', '/literacy/master/School_BasicDataMsg', <UserOutlined />),
                getItem('修改管理碼', '/literacy/master/School_ChangePasswd', <UserOutlined />),
                getItem(<a href={`${axios.defaults.baseURL}/api/sample?manual_id=15`}>管理手冊下載</a>, '/literacy/master/Download/15', <UserOutlined />),
                getItem(<a href={`${axios.defaults.baseURL}/api/sample?manual_id=16`}>施測手冊下載</a>, '/literacy/master/Download/16', <UserOutlined />),
                // getItem('登出', '/', <UserOutlined />),
            ])
        } else if (role === 'exam' && props.roleRequired === 'exam' && props.topic == 'literacy') {
            setNav(
                []
            )
            setLi([
                getItem('進行施測', '/literacy/exam/StudentList', <UserOutlined />),
                getItem('觀看報告', '/literacy/exam/Report', <UserOutlined />),
                getItem('班級施測報表', '/literacy/exam/Report3', <UserOutlined />),
                getItem('學生管理', '/literacy/exam/StudentMsg', <UserOutlined />),
                getItem('修改管理碼', '/literacy/exam/Usr_Passwd_Change', <UserOutlined />),
                getItem(<a href={`${axios.defaults.baseURL}/api/sample?manual_id=1`}>管理手冊下載</a>, '/literacy/master/Download/15', <UserOutlined />),
                // getItem('登出', '/', <UserOutlined />),
            ])
        } else if (role === 'student' && props.roleRequired === 'student' && props.topic == 'learn') {
            setNav(
                []
            )
            setLi([
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>任務管理</span>, '/learn/student/StudentMain', <UserOutlined />, [
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）開始我的任務</span>, '/learn/student/Mission'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>教師指派任務列表</span>, '/learn/student/TeacherAssign'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>自主學習任務列表</span>, '/learn/student/SelfLearn'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>自主學習任務指派</span>, '/learn/student/SelfLearnAssign'),
                ]),
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>學習成果</span>, '', <UserOutlined />, [
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>歷年成績</span>, '/learn/student/LearnHistoryScore'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>查詢歷史任務</span>, '/learn/student/SearchHistoryMission'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>查詢已學過的字</span>, '/learn/student/SearchLearnWord'),
                ]),
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>使用說明</span>, '/learn/student/LearnStudentUse', <UserOutlined />),
                // getItem(<span style={{fontSize:'30px'}}>登出</span>, '/', <UserOutlined />),
            ])
        } else if (role === 'student' && props.roleRequired === 'student' && props.topic == 'literacy') {
            setNav(
                []
            )
            setLi([
                // getItem('登出', '/', <UserOutlined />),

            ])
        } else if (role === 'master' && props.roleRequired === 'master' && props.topic == 'learn') {
            setNav(
                []
            )
            setLi([
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>即時報表</span>, '/learn/master/InstantReport'),
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>教師資料</span>, '/learn/master/TeacherInformation'),
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>教師查詢</span>, '/learn/master/TeacherSearch'),
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>學生查詢</span>, '/learn/master/StudentSearch'),
                // getItem('登出', '/', <UserOutlined />),
            ])
        } else if (role === 'city' && props.roleRequired === 'city' && props.topic == 'learn') {
            setNav(
                []
            )
            setLi([
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>即時報表</span>, '/learn/city/InstantReport'),
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>全市學習生字量情況</span>, '/learn/city/CityLearnWord'),
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>各區學習生字量情況</span>, '/learn/city/AreaLearnWord'),
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>各校學習生字量情況</span>, '/learn/city/SchoolLearnWord'),
                // getItem('登出', '/', <UserOutlined />),

            ])

        } else if (role === 'exam' && props.roleRequired === 'exam' && props.topic == 'learn') {
            setNav(
                []
            )
            setLi([
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>任務管理</span>, null, <UserOutlined />, [
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）學生資料</span>, '/learn/exam/Student_Management'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）建立群組</span>, '/learn/exam/Group_Management'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）指派任務</span>, '/learn/exam/AssignTask'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）任務列表</span>, '/learn/exam/TaskList'),
                ]),
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>成績管理</span>, null, <UserOutlined />, [
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）學生學習成績查詢</span>, '/learn/exam/Learn_Student_Score_Select'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）任務學習成果查詢</span>, '/learn/exam/Learn_Mission_Score_Select'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）小組學習成果查詢</span>, '/learn/exam/Learn_Group_Score_Select'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）生字學習成果查詢</span>, '/learn/exam/LearnResultSearch'),
                    getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>（Ｏ）比順下載記錄</span>, '/learn/exam/StrokeRecord'),
                ]),
                getItem(<span style={{ fontSize: '30px', backgroundColor: '#F7F7F7' }}>使用說明</span>, '/learn/exam/LearnExamUse', <UserOutlined />),
                // getItem('登出', '/', <UserOutlined />),
            ])
        } else if (role === 'admin' && props.roleRequired === 'admin' && props.topic == 'island') {
            setNav(
                []
            )
            setLi([
                getItem('最新消息管理', '/island/admin/News', <UserOutlined />),
                getItem('識字量測驗編輯', '/island/admin/LiteracyExam', <UserOutlined />),
                getItem('學習島嶼編輯', '/island/admin/Learn', <UserOutlined />),
                getItem('遊戲島嶼編輯', '/island/admin/GameIsland', <UserOutlined />),
                getItem('教學資源管理', '/', <UserOutlined />),
                getItem('學習資源管理', '/', <UserOutlined />),
                getItem('字庫查詢編輯', '/island/admin/ExamWord', <UserOutlined />),
                getItem('閱讀研討會管理', null, <UserOutlined />, [
                    getItem('研討會簡介編輯', '/island/admin/Summit'),
                    getItem('投稿須知編輯', '/island/admin/Contribute'),
                    getItem('報名系統編輯', '/island/admin/Enrollment'),
                    getItem('歷屆成果編輯', '/island/admin/Achievements'),
                    getItem('交通方式編輯', '/island/admin/Transportation'),
                ]),
                getItem('關於我們編輯', '/island/admin/AboutUs', <UserOutlined />),
                getItem('聯絡我們編輯', '/island/admin/ContactUs', <UserOutlined />),
                getItem('常見問題編輯', '/island/admin/CommonProblem', <UserOutlined />),

            ])
        } else {
            navigate('/literacy')
        }
    }, [role])

    //if the role required is there or not
    return auth ?
        props.topic === 'learn' ?
            (
                <LearnLayoutIsLogin
                    nav={nav}
                    li={li}
                    setLi={setLi}            >
                    <Outlet />
                </LearnLayoutIsLogin>
            ) :
            (
                props.topic === 'literacy' ?
                    (
                        <LayoutIsLogin
                            nav={nav}
                            li={li}
                            setLi={setLi}            >
                            <Outlet />
                        </LayoutIsLogin>
                    ) :
                    (
                        <IslandLayoutIsLogin
                            nav={nav}
                            li={li}
                            setLi={setLi}            >
                            <Outlet />
                        </IslandLayoutIsLogin>
                    )
            )
        : (
            <Navigate to="/" />
        )
}

export default ProtectedRoutes