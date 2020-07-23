<?php
namespace hcgrzh\tree;
class Tree {
	/**
	* 生成树型结构所需要的2维数组
	* @var array
	*/
	public $arr = array();

	/**
	* 生成树型结构所需修饰符号，可以换成图片
	* @var array
	*/
	public $icon = array('│','├','└');
	public $nbsp = "&nbsp;";
	public $ret = '';
	public $treedata='';
	public $optiondata=array();/*下拉菜单其他数据<option $optionstr  value=''></option>*/
	public $showname='';//额外显示的内容
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
	public function  init($arr=array(),$pid="parentid",$id="id",$name='name'){
		$this->arr = $arr;
	   	$this->ret = '';
	   	$this->pid=$pid;
	   	$this->id=$id;
	   	$this->name=$name;
	   	return is_array($arr);
	}
	/**
	 * 其他参数
	 * @param  [type] $config [description] option array('key'=>'value','key1'=>'value1');
	 * @param  string $type   [description]
	 * @return [type]         [description]
	 */
	public function setconfing($config,$type='option'){
		if($type=='option'){/*下拉菜单其他数据<option key  value=''></option>*/
			$this->optiondata=$config;
		}
		if($type=='showname'){
			$this->showname=$config;
		}

	}
	/**
	* 得到子级数组
	* @param int
	* @return array
	*/
	public function get_child($topid=0){
		$a = $newarr = array();
		if(is_array($this->arr)){
			foreach($this->arr as $id => $a){
				if($a[$this->pid] == $topid) {
				    $newarr[$id] = $a;
                }
			}
		}
		return $newarr ? $newarr : false;
	}
    public function get_child_level($topid=0,$level=1){
        $a = $newarr = array();
        if(is_array($this->arr)){
            foreach($this->arr as $id => $a){
                if($a[$this->pid] == $topid) {
                    $newarr[$id] = $a;
                    $newarr[$id]['level'] = $level;
                }
            }
        }
        return $newarr ? $newarr : false;
    }
	//得到子级id 数组集合
  	public function get_child_id($pid=0){
      	$child=array();
      	if(is_array($this->arr)){
	  	    foreach($this->arr as $k=>$v){
	  	        if($v[$this->pid] == $pid){
	  	            $child[]=$v[$this->id];
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
  	public function get_child_all($pid=1,$child = array()){
      	$child[] = $pid;
      	if(is_array($this->arr)){
	  	    foreach($this->arr as $k=>$v){
	  	        if($v[$this->pid] == $pid){
	  	            $child = $this->get_child_all($v[$this->id],$child);
	  	        }
	  	    }
  		}
      	return $child;
  	}
  	/**
  	 * [get_parentid_all description]获取父级栏目id 集合
  	 * @param  [type] $colid [description] 当前id
  	 * @param  array  &$p    [description]
  	 * @return [type]        [description]
  	 */
  	public function get_parentid_all($colid,&$p=array()){
  		if(is_array($this->arr)){
  			foreach($this->arr as $k=>$v){
  				if($colid==$v[$this->id]){
  					if($v[$this->pid]!=0){
  						$p[]=$v[$this->pid];
  						$this->get_parentid_all($v[$this->pid],$p);
  					}
  				}
  			}
  		}
  		return $p;
  	}
	/*
	$topid 顶部id;  当前选择项 ；不能选择项 ，间歇； $noselectedpid=array('parentid',array(0,1));及栏目id 不能选择父id值为0和1的栏目
	 */
	public function get_select($topid,$selectedid='',$noselectedpid=array(),$adds=''){
		$number=1;
		$child=$this->get_child($topid);
		if(is_array($child)){
			$total=count($child);
			foreach ($child as $key => $value) {
				$j=$k='';
				if($number==$total){
					$j.=$this->icon[2];//下级菜单只有最后一个
				}else{
					$j.=$this->icon[1];//下级菜单有多个
					$k = $adds ? $this->icon[0] : '';
				}

				$spacer = $adds ? $adds.$j : '';
				//默认选择项
				$selected=$selectedid==$value[$this->id]?'selected':'';
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
				if(isset($this->optiondata)){
					foreach($this->optiondata as $x=>$y){
						$stroption.=$x."=".$value[$y]." ";
					}
				}
				$showname='';
				if(isset($this->showname) && $this->showname!=''){
					$showname="(".$value[$this->showname].")";		
				}
				$this->ret.="<option ".$stroption." ".$noselected." ".$selected."  value='".$value[$this->id]."'>".$spacer.$value[$this->name].$showname."</option>";

				$nbsp = $this->nbsp;
				$this->get_select($value[$this->id], $selectedid, $noselectedpid, $adds.$k.$nbsp);
				$number++;
			}
		}
		return $this->ret;
	}
	public function get_treedata($topid,$adds='',&$arrnew=array()){
		$number=1;
		$child=$this->get_child($topid);
		if(is_array($child)){
			$total=count($child);
			foreach ($child as $key => $value) {
				$j=$k='';
				if($number==$total){
					$j.=$this->icon[2];//下级菜单只有最后一个
				}else{
					$j.=$this->icon[1];//下级菜单有多个
					$k = $adds ? $this->icon[0] : '';
				}

				$spacer = $adds ? $adds.$j : '';
				$value['showname']=$spacer.$value[$this->name];
				$value['level_level']=$number;
				$arrnew[]=$value;
				$nbsp = $this->nbsp;
				$this->get_treedata($value[$this->id], $adds.$k.$nbsp,$arrnew);
				$number++;
			}
		}
		return $arrnew;
	}
	//排序生成数组
	public function getsortdata($topid=0,&$arr=array()){
		$child=$this->get_child($topid);
		if(is_array($child)){
			foreach($child as $k=>$v){
				$arr[]=$v;
				$this->getsortdata($v[$this->id],$arr);
			}
		}
		return $arr;
	}
	/**
	 * [getsortdataChild description]排序生成数组并获取下级栏目id 和下级所有栏目id
	 * @param  integer $topid [description]
	 * @param  array   $arr   [description]
	 * @return [type]         [description]
	 */
    public function getsortdatalevel($topid=0,&$arr=array(),$level=1){
        $child=$this->get_child_level($topid,$level);
        $level++;
        if(is_array($child)){
            foreach($child as $k=>$v){
                $child_new=$this->get_child_id($v[$this->id]);

                $v['child']=implode('_',$child_new);
                //获取下级所有子集的id
                $child_all=$this->get_child_all($v[$this->id]);
                unset($child_all[0]);//删除当前id
                $child_all=implode('_',$child_all);
                $v['child_all']=$child_all;
                //如果存在模型
                $arr[]=$v;
                $this->getsortdatalevel($v[$this->id],$arr,$level);
            }
        }
        return $arr;
    }
}
?>