<?php
namespace hcgrzh\tree;
class Tree {
	/**
	* 生成树型结构所需要的2维数组
	* @var array
	*/
	public static $arr = [];

	/**
	* 生成树型结构所需修饰符号，可以换成图片
	* @var array
	*/
	public static $icon =['│','├','└'];
	public static $nbsp = "&nbsp;";
	public static $ret = '';
	public static $treedata='';
	public static $optiondata=[];/*下拉菜单其他数据<option $optionstr  value=''></option>*/
	public static $showname='';//额外显示的内容
	public static $name=null;
	public static $id=null;
	public static $pid=null;
	public static $isGetNextId=false;//如方法get_childall_data|get_child_all_level是否获取下一级所有id
	public static $isGetNextIdAll=false;//如方法get_childall_data|get_child_all_level是否获取所有下级id
	public static $isGetNextIdAllCurrentId=false;//获取所有下级id 返回是否包含当前id值
	public static $isGetUpperIdAll=false;//获取上级所有id 如方法get_childall_data|get_child_all_level
	/**
	* 构造函数，初始化类
	* @param array 2维数组，例如：
	* array(
	*      1 => array('id'=>'1','parentid'=>0,'name'=>'一级栏目一'),
	*      2 => array('id'=>'2','parentid'=>0,'name'=>'一级栏目二'),
	*      3 => array('id'=>'3','parentid'=>1,'name'=>'二级栏目一'),
	*      4 => array('id'=>'4','parentid'=>1,'name'=>'二级栏目二'),
	*      5 => array('id'=>'5','parentid'=>2,'name'=>'二级栏目三'),
	*      6 => array('id'=>'6','parentid'=>3,'name'=>'三级栏目一'),
	*      7 => array('id'=>'7','parentid'=>3,'name'=>'三级栏目二')
	*      )
	*/
	public static function init($arr=[],$pid="parentid",$id="id",$name='name'):bool{
		self::$arr = $arr;
	   	self::$ret = '';
	   	self::$pid=$pid;
	   	self::$id=$id;
	   	self::$name=$name;
	   	return is_array($arr);
	}
	/**
	 * 其他参数
	 * @param  [type] $config [description] option array('key'=>'value','key1'=>'value1');
	 * @param  string $type   [description]
	 * @return [type]         [description]
	 */
	public static function setconfing($config,$type='option'):void{
		if($type=='option'){/*下拉菜单其他数据<option key  value=''></option>*/
			self::$optiondata=$config;
		}
		if($type=='showname'){
			self::$showname=$config;
		}
	}
	/**
	* 得到子级数组
	* @param int
	* @return array
	* @level 方便其他调用层次使用
	*/
    public static function get_child_level($topid=0,$level=1):array{
        $newarr = [];
        if(is_array(self::$arr)){
            foreach(self::$arr as $id => $a){
                if($a[self::$pid] == $topid) {
                    $newarr[$id] = $a;
                    $newarr[$id]['level'] = $level;
                }
            }
        }
        return $newarr ? $newarr : [];
    }
    /**
	 * @param  integer $topid [description]
	 * @param  array   $sort  array('排序字段','desc|asc')
	 */
  	public static function get_childall_data($topid=0,$sort=[],$child=[]):array{
		foreach(self::$arr as $k => $v) {
			//获取当前下级id
			if(self::$isGetNextId===true){
				$child_id=self::get_child_id($v[self::$id]);
				$v['child_id']=implode('_',$child_id);
			}
			//获取下级所有子集的id
			if(self::$isGetNextIdAll===true){
				$child_idall=self::get_child_all($v[self::$id]);
				if(self::$isGetNextIdAllCurrentId===false){
					unset($child_idall[0]);//删除当前id
				}
				$child_idall=implode('_',$child_idall);
				$v['child_idall']=$child_idall;
			}
			//获取所有上级id
			if(self::$isGetUpperIdAll===true){
				//$v['supper_idll']=self::get_parentid_all($v[self::$id]);
				$v['supper_idll']=implode('_',self::get_parentid_all($v[self::$id]));
			}
			//获取下级内容处理
			if($v[self::$pid]==$topid){
				$child_arr=self::get_childall_data($v[self::$id],[]);
				$v['child']=$child_arr??[];
				if(!empty($sort) && !empty($v['child'])){
					$v['child']=self::data_sort($v['child'],$sort[0],$sort[1]);
					$v['child']=array_values($v['child']);
				}
				$child[]=$v;
			}
		}
		if(!empty($sort)){
			$child=self::data_sort($child,$sort[0],$sort[1]);
			$child=array_values($child);
		}
		return $child;
  	}
	//得到子级id 数组集合
  	public static function get_child_id($pid=0):array{
      	$child=array();
      	if(is_array(self::$arr)){
	  	    foreach(self::$arr as $k=>$v){
	  	        if($v[self::$pid] == $pid){
	  	            $child[]=$v[self::$id];
	  	        }
	  	    }
	  	}
      	return $child;
  	}
	/**
	 * [get_child_all description]获取所有下级id
	 * @param  [type]  $data  [description] 当前栏目id
	 * @param  integer $pid   [description]当前栏目id
	 * @param  array   $child [description]
	 * @return [type]         [description]
	 */
  	public static function get_child_all($pid=1,$child =[]):array{
      	$child[] = $pid;
      	if(is_array(self::$arr)){
	  	    foreach(self::$arr as $k=>$v){
	  	        if($v[self::$pid] == $pid){
	  	            $child = self::get_child_all($v[self::$id],$child);
	  	        }
	  	    }
  		}
      	return $child;
  	}
  	/**
	 * [getsortdataChild description]排序生成数组并获取下级栏目id 和下级所有栏目id
	 * @param  integer $topid [description]
	 * @param  array   $arr   [description]
	 * @param  array   $sort  array('排序字段','desc|asc')
	 * @return [type]         [description]
	 * @level	当前第几层次
	 */
    public static function get_child_all_level($topid=0,$sort=[],&$arr=[],$level=1):array{
        $child=self::get_child_level($topid,$level);
        if(!empty($sort)){
        	$child=self::data_sort($child,$sort[0],$sort[1]);
        	$child=array_values($child);
        }
        $level++;
        if(is_array($child)){
            foreach($child as $k=>$v){
				//获取当前下级id
				if(self::$isGetNextId===true){
					$child_id=self::get_child_id($v[self::$id]);
					$v['child_id']=implode('_',$child_id);
				}
				//获取下级所有子集的id
				if(self::$isGetNextIdAll===true){
					$child_idall=self::get_child_all($v[self::$id]);
					if(self::$isGetNextIdAllCurrentId===false){
						unset($child_idall[0]);//删除当前id
					}
					$child_idall=implode('_',$child_idall);
					$v['child_idall']=$child_idall;
				}
				//获取所有上级id
				if(self::$isGetUpperIdAll===true){
					//$v['supper_idll']=self::get_parentid_all($v[self::$id]);
					$v['supper_idll']=implode('_',self::get_parentid_all($v[self::$id]));
				}
                //如果存在模型
                $arr[]=$v;
                self::get_child_all_level($v[self::$id],$sort,$arr,$level);
            }
        }
        return $arr;
    }
    //数组排序.//排序比较时 要用数字；
    public static function data_sort($array=[],$keys,$type='asc'):array{
	   $keysvalue = $new_array = array();
	   //提取排序的列
	   foreach ($array as $k=>$v){
	       $keysvalue[$k] = $v[$keys];
	   }
	   if($type == 'asc'){
	       asort($keysvalue);//升序排列
	   }else{
	       arsort($keysvalue);//降序排列
	   }
	   //reset($keysvalue);
	   foreach ($keysvalue as $k=>$v){
	       $new_array[$k] = $array[$k];
	   }
	   return $new_array;
	}
  	/**
  	 * [get_parentid_all description]获取父级栏目id 集合
  	 * @param  [type] $curid [description] 当前id
  	 * @param  array  &$p    [description]
  	 * @return [type]        [description]
  	 */
  	public static function get_parentid_all($curid,&$p=[]):array{
  		if(is_array(self::$arr)){
  			foreach(self::$arr as $k=>$v){
  				if($curid==$v[self::$id]){
  					if($v[self::$pid]!=0){
  						$p[]=$v[self::$pid];
  						self::get_parentid_all($v[self::$pid],$p);
  					}
  				}
  			}
  		}
  		return $p;
  	}
	/*
	$topid 顶部id;  当前选择项 ；不能选择项 ，间歇； $noselectedpid=array('parentid',array(0,1));及栏目id 不能选择父id值为0和1的栏目
	 */
	public static function get_select($topid=0,$selectedid='',$noselectedpid=[],$adds='',$level=1):string{
		$number=1;
		$child=self::get_child_level($topid);
		$level++;
		if(is_array($child)){
			$total=count($child);
			foreach ($child as $key => $value) {
				$j=$k='';
				if($number==$total){
					$j.=self::$icon[2];//下级菜单只有最后一个
				}else{
					$j.=self::$icon[1];//下级菜单有多个
					$k = $adds ? self::$icon[0] : '';
				}
				$spacer = $adds ? $adds.$j : '';
				//默认选择项
				$selected=$selectedid==$value[self::$id]?'selected':'';
				//不能选择项
				$noselected='';
				if(!empty($noselectedpid)){
					$nokey=$noselectedpid[0];
					$novalue=$noselectedpid[1];
					if(in_array($value[$nokey],$novalue)){
						$noselected='disabled';
					}
				}
				$stroption='';
				if(isset(self::$optiondata)){
					foreach(self::$optiondata as $x=>$y){
						$stroption.=$x."=".$value[$y]." ";
					}
				}
				$showname='';
				if(isset(self::$showname) && self::$showname!=''){
					$showname="(".$value[self::$showname].")";
				}
				self::$ret.="<option ".$stroption." ".$noselected." ".$selected."  value='".$value[self::$id]."'>".$spacer.$value[self::$name].$showname."</option>";

				$nbsp = self::$nbsp;
				self::get_select($value[self::$id], $selectedid, $noselectedpid, $adds.$k.$nbsp,$level);
				$number++;
			}
		}
		return self::$ret;
	}
}
?>