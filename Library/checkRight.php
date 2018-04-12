<?php
/**
 * User: weipinglee
 * Date: 2016/3/1 0001
 * Time: 下午 3:08
 */
namespace Library;
use \Library\Session\Driver\Db;
use \Library\url;
use nainai\riskMgt\userRisk;
use nainai\sso\NNcas;
class checkRight{


    //session操作对象
    static private $sessObj = '';
	private $db_name = '';
	private $userModel = null;
    /**
     *
     */
    public function __construct($db=''){
        $sess = 'DB';//根据配置文件获取
       
		if($db)
			$this->db_name=$db;
        $expireTime = 7200;
		$this->userModel = $this->db_name ? new M($this->db_name.'.user') : new M('user');
		 $tableName = $this->db_name ? $this->db_name.'.user_session' : 'user_session';
        switch($sess){
            case 'DB' : {
                self::$sessObj = new Db($tableName,$expireTime);
            }
            break;

            default:{
                
            }
        }
    }


    /**
     * 登录后处理
     * @param array $data  用户登录数据
     */
    public function loginAfter($data){
        //设置session
        session_regenerate_id(true);
        session::clear('login');
        session::merge('login',array('user_id'=>$data['id']));
        session::merge('login',array('username'=>$data['username']));
        session::merge('login',array('mobile'=>$data['mobile']));
        Session::merge('login',array('pid'=>$data['pid']));
        session::merge('login',array('user_type'=>$data['type']));
        //session数据计入数据库
        $sessID = session_id();
        $sessData = session::get('login');
        self::$sessObj->gc();
        self::$sessObj->write($sessID,serialize($sessData));
        $userModel = $this->userModel;
        $ip=\Library\Client::getIP();
        $userModel->where(array('id'=>$data['id']))->data(array('session_id'=>$sessID,'login_ip'=>$ip,'login_time'=>date('Y-m-d H:i:s',time())))->update();
        $riskModel=new userRisk();
     //   $riskModel->checkUserAddress(['user_id'=>$data['id'],'ip'=>$ip]);
        //获取认证状态
        $this->getCert($data['id']);

    }

    private function getCert($id){
        $cert = new \nainai\cert\certificate();
        $certData = $cert->checkCert($id);
        Session::merge('login',array('cert'=>$certData));
        return $certData;
    }
    /**
     *验证是否登录:判断已登录条件：存在session['login']、session中user_id的用户session_id字段等于session_id()、session_id()未过期
     * @param object $obj 控制器实例
     * @return bool 是否登录
     */
    public function checkLogin($obj=null){
        $sessID = session_id();
        $sessLogin = session::get('login');
        $isLogin = false;

//        if($sessLogin==null){//if user don't have a local session,check whether he has a server-end login state
//            $ssoObj = new NNcas();
//            if($ssoObj->checkServerLogin()){//if user have login on global environment ,get user data and handle login.
//                $data = $ssoObj->getUser();
//                $this->loginAfter($data);
//                $sessID = session_id();
//                $sessLogin = session::get('login');
//            }
//        }
        //判断是否登录以及登录是否超时
        if($sessLogin!=null && isset($sessLogin['user_id']) && $sessID !=''){
            $userModel = $this->userModel;
            $login_sess = $userModel->where(array('id'=>$sessLogin['user_id']))->fields('session_id,cert_status, status,type')->getObj();

            if($login_sess['status'] == \nainai\user\User::NOMAL && $sessID == $login_sess['session_id'] && self::$sessObj->expire($sessID)){
                $isLogin = true;
                if($login_sess['cert_status']==1){//认证状态发生了变化
                    $userModel->where(array('id'=>$sessLogin['user_id']))->data(array('cert_status'=>0))->update();
                    $sessLogin['user_type'] = $login_sess['type'];

                    if ($sessLogin['pid'] == 0) {
                        $sessLogin['cert'] = $this->getCert($sessLogin['user_id']);
                    }else{
                        $sessLogin['cert'] = $this->getCert($sessLogin['pid']);
                    }
                    session::set('login',$sessLogin);
                }

            }

        }

        if($obj!==null){
            if($isLogin == false){  //if user  have neither a global session nor a local one,present a login form page
                if(isset($_GET['callback']))
                    $callBack = $_GET['callback'];
                else{
                    $controller = $obj->getRequest()->getControllerName();
                    $action     = $obj->getRequest()->getActionName();
                    $param = $obj->getRequest()->getParams();
                    $params = '';
                    if(!empty($param)){
                        $params = '?';
                        foreach($param as $key=>$val){
                            $params .= $key.'='.$val.'&';
                        }
                        $params = rtrim($params,'&');
                    }
                    $callBack = url::createUrl('/'.$controller.'/'.$action.$params);

                    if(!empty($_GET)){
                        $gets = '?';
                        foreach($_GET as $key => $val){
                            $gets .= $key .'='. $val.'&';
                        }
                        $gets = rtrim($gets,'&');
                        $callBack .= $gets;
                    }

                }

                if(IS_POST || IS_AJAX){
                    die(json::encode(tool::getSuccInfo(0,'请登录后再操作',url::createUrl('/login/login@user').'?callback='.$callBack)));
                }
                $obj->redirect(url::createUrl('/login/login@user').'?callback='.$callBack);
                exit;


            }
            else{//已登录则记录user_id
                foreach($sessLogin as $k=>$v){
                    $obj->$k = $v;
                }

            }
        }
        elseif($isLogin == false){
            $this->logOut();
        }

        return $isLogin;
    }

    

    /**
     * 登出
     */
    public function logOut(){
        $sessID = session_id();
        $sessLogin = session::get('login');

        if(isset($sessLogin['user_id'])){
            $userModel = $this->userModel;
            $userModel->where(array('id'=>$sessLogin['user_id']))->data(array('session_id'=>''))->update();
        }

         session::clear('login');
         return self::$sessObj->destroy($sessID);




    }

}