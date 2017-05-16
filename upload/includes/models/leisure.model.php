<?php

/* 商品规格 goodsspec */
class LeisureModel extends BaseModel
{
    var $table  = 'leisure';
    var $prikey = 'id';
    var $_name  = 'leisure';

    /**
     * [get_province 获取省份]
     * @return [type] [description]
     */
    function get_province(){
        $sql = "select provinceID,province from ecm_province";
        $province_arr =  $this->db->getall($sql);
        $save_arr = array();
        foreach ($province_arr as $key => $province) {
            $save_arr[$province['provinceID']] = $province['province'];
        }
        return $save_arr;
    }
    function get_city($fatherID){
       /* $fatherID =110000;*/
        $sql= "select * from ecm_city where fatherID = $fatherID";
        $city_arr = $this->db->getall($sql);
        $city_count = count($city_arr);
        //小于3个都认为是直辖市，直辖市只获取市辖区，地级市和县不显示
        $save_arr = array();
        if ($city_count<=3) {
            $area_fatherID = $city_arr[0]['cityID'];
            $sql = "select * from ecm_area where fatherID = $area_fatherID";
            $area_arr = $this->db->getall($sql);
            foreach ($area_arr as $key => $area) {
                $save_arr[$area['areaID']] = $area['area'];
            }
        }else{
            foreach ($city_arr as $key => $city) {
                $save_arr[$city['cityID']] = $city['city'];
            }
        }
        
        return $save_arr;
    }
    function get_city_enterprise($fatherID){
       /* $fatherID =110000;*/
        $sql= "select * from ecm_city where fatherID = $fatherID";
        $city_arr = $this->db->getall($sql);
        $city_count = count($city_arr);
        //小于3个都认为是直辖市，直辖市只获取市辖区，地级市和县不显示
        $save_arr = array();
        if ($city_count<=3) {
            $area_fatherID = $city_arr[0]['cityID'];
            $sql = "select provinceID,province from ecm_province where provinceID= $fatherID";
            $province = $this->db->getrow($sql);
            $save_arr[$province['provinceID']] = $province['province'];
            
            
        }else{
            foreach ($city_arr as $key => $city) {
                $save_arr[$city['cityID']] = $city['city'];
            }
            
        }
        
        return $save_arr;
    }
     /**
     * [get_area 获取当前的省市]
     * @param  [type] $area_id [区域id]
     * @return [type]          [description]
     */
    function get_area($area_id){
        $sql = "select * from ecm_area where areaID =$area_id";
        $area = $this->db->getrow($sql);
        $area_arr = array();
        if (empty($area)) {
            $sql = "select a.*,b.* from ecm_city as a inner join ecm_province as b on a.fatherID = b.provinceID where cityID =$area_id";
            $city_row = $this->db->getrow($sql);
            $area_arr['city'] = $city_row['city'];
            $area_arr['province'] = $city_row['province'];
            $area_arr['provinceID'] = $city_row['provinceID'];
            if (!empty($city_row['provinceID'])) {
                $area_arr['city_arr'] = $this->get_city($city_row['provinceID']);
            }
            
            //return $area_arr;
        }else{
            $sql = "select b.* from ecm_city as a inner join ecm_province as b on a.fatherID = b.provinceID where a.cityID ={$area['fatherID']}";
            $city_row_re = $this->db->getrow($sql);
            $area_arr['city'] = $area['area'];
            $area_arr['provinceID'] = $city_row_re['provinceID'];
            $area_arr['province'] = $city_row_re['province'];
            if (!empty($city_row_re['provinceID'])) {
             $area_arr['city_arr'] = $this->get_city($city_row_re['provinceID']);
            }
        }

       return $area_arr;
    }
    /**
     * [get_shop_arr description]
     * @param  [type] $area_id [description]
     * @return [type]          [description]
     */
    function get_shop_arr($area_id){
        $sql="select * from ecm_leisure where area_id=$area_id";
        $leisure = $this->db->getall($sql);
        return $leisure;

    }
    
    /**
     * [get_enterprise 获取region_name等于content_cur的所有子类，并返回]
     * @param  [type] $content_cur [description]
     * @return [type]              [description]
     */
    function get_enterprise($content_cur){
        $sql="select region_id from ecm_region where region_name = '$content_cur'";
        $region_id = $this->db->getone($sql);
        if (empty($region_id)) {
            return '';
        }
        $getall="select * from ecm_region";
        $all_arr = $this->db->getall($getall);
        $arr= $this->_tree($all_arr,$region_id);
        $region_ids = $this->get_column($arr,'region_id');
        //获取当前的内容文件
        $region_ids=implode($region_ids, ',');
        //查找store商铺的信息
  
        return $region_ids;


    }
    protected function _tree($productCategorys, $parentId = 0)
    {
        static $productCategoryTrees = array();
        foreach($productCategorys as $productCategory) {
            if ($productCategory['parent_id'] == $parentId || $productCategory['region_id']==$parentId) {
                $productCategoryTrees[] = $productCategory;
                $this->_tree($productCategorys, $productCategory['id']);
            }
        }
        return $productCategoryTrees;
    }
    function get_column($arr,$column){
        $save = array();
        foreach ($arr as $key => $value) {
            $save[] = $value[$column];
        }
        return $save;
    }
    

}

?>