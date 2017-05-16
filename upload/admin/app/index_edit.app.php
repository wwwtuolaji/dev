<?php

class Index_editApp extends BackendApp
{
    function index()
    {
        $open_qq_arr = array('0'=>'关闭','1'=>'开启');
        $index_model = m('index_show');

      if ($_POST) {
      	//dump($_POST);
        	$add =array();
        	$add['slide_img_url'] = implode('||',$_POST['slide_img_url']);
        	$add['slide_link'] =  implode('||',$_POST['slide_link']);
        	$add['open_qq'] = $_POST['open_qq'];
        	$add['qq_num'] = trim($_POST['qq_num']);
        	$add['small_banner'] = $_POST['small_banner'];
        	$add['small_banner_link'] = $_POST['small_banner_link'];
        	$add['qiye_banner_val']=implode('||', $_POST['qiye_banner_val']);
        	$add['qiye_banner_name'] = implode('||',$_POST['qiye_banner_name']);
        	$add['qiye_banner_link'] = implode('||',$_POST['qiye_banner_link']);
        	$add['zizhi_url'] = implode('||',$_POST['zizhi_url']);
        	$add['zizhi_link'] = implode('||',$_POST['zizhi_link']);
        	$add['zizhi_name'] = implode('||',$_POST['zizhi_name']);
        	$add['bottom_img_url'] = $_POST['bottom_img_url'];
		    $add['bottom_img_link'] = $_POST['bottom_img_link'];

		$find_arr= $index_model->find(1);
		if (empty($find_arr)) {
			$add['current_time'] = time();
			$result = $index_model->add($add);
			if ($result) {
				$this->show_message('添加成功' ,'返回','index.php?app=index_edit');
				return;
			}else{
				$this->show_message('添加失败');
				return;
			}
		}else{
			$result = $index_model->edit(1,$add);
			if ($result) {
				$this->show_message('添加成功' ,'返回','index.php?app=index_edit');
				return;
			}else{
				$this->show_message('添加失败');
				return;
			}
		}
        }
        $index_arr = $index_model->get(1);
        $result = $index_model->return_arr_val($index_arr);
        $this->assign('index_arr',$result);
        $this->assign('open_qq_arr',$open_qq_arr);
        $this->display("edit.index.html");
    }
    function add_address(){

        $leisure_mod = m('leisure');
        $province_arr = $leisure_mod->get_province();
        if(!empty($_POST)){
          //  dump($_POST);
            $add_arr['area_id'] = $_POST['area_id'];
            $add_arr['leisure_name'] = $_POST['leisure_name'];
            $add_arr['phone'] = $_POST['phone'];
            $add_arr['address_info'] = $_POST['address_info'];
            $add_arr['coordinate'] = $_POST['coordinate'];
            $add_arr['images'] = implode('||', $_POST['images']);
            $add_arr['slide_link'] = implode('||',$_POST['slide_link']);
            $add_arr['shop_name'] = $_POST['shop_name'];
            $add_arr['leisure_des'] = trim($_POST['leisure_des']);
            $result =  $leisure_mod ->add($add_arr);
            if ($result) {
               $this->show_message('添加成功');
               return;
            }else{
                $this->show_message('添加失败');
                return;
            }

        }
        $keys = array_keys($province_arr);
        $city = $leisure_mod->get_city($keys[0]);
        $this->assign('city',$city);
        $this->assign('province_arr',$province_arr);
        $this->display('tea_address.add.html');
    }
    function get_city(){
        if (empty($_POST['area_id'])) {
            $array  = array(
                            'code' =>1,
                            'message'=>'参数异常',
                            'data' =>'');
        }else{
            $leisure_mod = m('leisure');
            $citys = $leisure_mod ->get_city($_POST['area_id']);
            $array = array(
                            'code'=>0,
                            'message'=>'请求成功',
                            'data'=>$citys);

        }
        
        echo json_encode($array);
    }
    function leisure_list(){
        $leisure_mod = m('leisure');
        $leisure_arr = $leisure_mod ->find();
        foreach ($leisure_arr as $key => $leisure) {
            $leisure_arr[$key]['area_des_arr'] = $leisure_mod->get_area($leisure['area_id']);
        }
        //dump($leisure_arr);

        $this->assign('leisure_arr',$leisure_arr);
        $this->display('tea_address.view.html');
    }
    function edit_address(){
        $leisure_id=$_GET['leisure_id'];
        if (empty($leisure_id)) {
            $this->show_message("无效的删除地址");
            return;
        }
        $leisure_mod = m('leisure');
        $province_arr = $leisure_mod->get_province();
        if(!empty($_POST)){
          //  dump($_POST);
            $add_arr['area_id'] = $_POST['area_id'];
            $add_arr['leisure_name'] = $_POST['leisure_name'];
            $add_arr['phone'] = $_POST['phone'];
            $add_arr['address_info'] = $_POST['address_info'];
            $add_arr['coordinate'] = $_POST['coordinate'];
            $add_arr['images'] = implode('||', $_POST['images']);
            $add_arr['slide_link'] = implode('||',$_POST['slide_link']);
            $add_arr['leisure_des'] = trim($_POST['leisure_des']);
            $add_arr['shop_name'] = $_POST['shop_name'];
            $result =  $leisure_mod ->edit($leisure_id,$add_arr);
            if ($result!==false) {
               $this->show_message('修改成功');
               return;
            }else{
                $this->show_message('修改失败');
                return;
            }

        }
        $this->assign('leisure_id',$leisure_id);
        $leisure_arr = $leisure_mod ->get($leisure_id);
        $area_arr =$leisure_mod -> get_area($leisure_arr['area_id']);
        $leisure_arr['images']=explode("||", $leisure_arr['images']);
        $leisure_arr['slide_link']=explode("||", $leisure_arr['slide_link']);
        $this ->assign('leisure_arr',$leisure_arr);
        $keys = array_keys($province_arr);
        $city = $area_arr['city_arr'];
        $city_selected_arr=array('province'=>$area_arr['provinceID'],'city'=>$leisure_arr['area_id']);

        $this->assign('selected_arr',$city_selected_arr);
        $this->assign('city',$city);
        $this->assign('province_arr',$province_arr);
        $this->display('tea_address.edit.html');
    }
    function del_address(){
        $leisure_id=$_GET['leisure_id'];
        if (empty($leisure_id)) {
            $this->show_message("无效的删除地址");
            return;
        }
        $sql = "delete from ecm_leisure where id = $leisure_id";
        $db = db();
        $db ->query($sql);
        $this->show_message('删除成功');
    }
   

    
}

?>