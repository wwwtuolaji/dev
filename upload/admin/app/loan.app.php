<?php

class LoanApp extends BackendApp
{
    function index()
    {
        $model_loan = m('loan');
        $loan = $model_loan->find();
     
        foreach ($loan as $key => $value) {
            $loan[$key]['apply_time_des'] = date('Y-m-d H:i:s',$value['apply_time']);
            //贷款期0.三个月以下；1,3-6个月；2，6-12个月
            switch ($value['duration']) {
                case '0':
                    $loan[$key]['duration_des'] = '三个月以下';
                    break;
                 case '1':
                    $loan[$key]['duration_des'] = '3-6个月';
                    break;
                 case '2':
                    $loan[$key]['duration_des'] = '6-12个月';
                    break;    
                default:
                    $loan[$key]['duration_des'] = '未知';
                    break;
            }
            
        }
        $this->assign('loan',$loan);
        $this->display('loan.index.html');
    }

    function set_comments(){
        if (empty($_POST['comments'])||empty($_POST['loan_id'])) {
            $arr = array('code'=>1,
                        'message'=>'参数异常',
                        'data'=>'');
        }else{
           $edit= array('comments'=>$_POST['comments']);
            $model_loan = m('loan');
            $model_loan->edit($_POST['loan_id'],$edit); 
            $arr = array('code'=>0,
                        'message'=>'添加成功',
                        'data'=>'');
        }
        echo json_encode($arr);
    }
       
    function del(){
        if (empty($_POST['loan_id'])) {
             $arr = array('code'=>1,
                        'message'=>'参数异常',
                        'data'=>'');
        }else{
            $sql = "delete from ecm_loan where loan_id = {$_POST['loan_id']}";
            $db = db();
            $db->query($sql); 
            $arr = array('code'=>0,
                        'message'=>'删除成功',
                        'data'=>'');

        }
         echo json_encode($arr);
       
    }
    
}

?>