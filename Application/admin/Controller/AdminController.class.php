<?php
/*
 *create by roy
 */
namespace admin\Controller;
use admin\Common\YuController;

class AdminController extends YuController {
	public function index(){
		//echo 111;
		//$nav_model = \admin\Model\NavModel();
		$nav_model = D("Nav");
		$nav = $nav_model->order("n_sort")->select();
		//var_dump($nav);
		$this->assign('nav',$nav);
		$view = $this->display("index");
	}

	public function helloworld(){
		//$this->display("login");
		echo 111;
	}

	public function add_nav($page=1){
		$nav_model = D("Nav");
		$url = "/index.php/admin/Admin/add_nav";

		$rowNum = $nav_model->count();
		$pageSize = 12;
		$pages = ceil($rowNum/$pageSize);
		if($page > $pages)
			$page = $pages;
		if($page < 1)
			$page = 1;
		$curPage = $page ?: 1;
		$offset = ($curPage-1)*$pageSize;
		$nav_list = $nav_model->order("n_path")->limit($offset, $pageSize)->select();
		$pagination = $this->getPagination($curPage, $pages, $url);
		$nav_class = $nav_model->order("n_path")->select();
		$this->assign('page',$page);
		$this->assign('nav_class',$nav_class);
		$this->assign('nav_list',$nav_list);
		$this->assign('pagination',$pagination);
		$content = $this->fetch("nav_list");
		$this->show($content);
	}

	public function edit_nav($page=1, $n_id=0){
		$nav_model = D("Nav");
		$url = "/index.php/admin/Admin/add_nav";

		$rowNum = $nav_model->count();
		$pageSize = 12;
		$pages = ceil($rowNum/$pageSize);
		if($page > $pages)
			$page = $pages;
		if($page < 1)
			$page = 1;
		$curPage = $page ?: 1;
		$offset = ($curPage-1)*$pageSize;
		$curPage = $page ?: 1;
		$nav_list = $nav_model->order("n_path")->limit($offset, $pageSize)->select();
		$pagination = $this->getPagination($curPage, $pages, $url);
		$nav = $nav_model->where("n_id = ".$n_id)->find();
		$nav_class = $nav_model->order("n_path")->select();
		$this->assign('page',$page);
		$this->assign('nav_class',$nav_class);
		$this->assign('nav_list',$nav_list);
		$this->assign('nav',$nav);
		$this->assign('nav_list',$nav_list);
		$this->assign('pagination',$pagination);
		$content = $this->fetch("nav_list");
		$this->show($content);
	}

	public function nav_save($page=1){
		$post = $_POST;
		if($post){
			$n_id = $post["n_id"];
			unset($post["submit"]);
			unset($post["n_id"]);
			$path = $post["n_path"];
			$edit_path = $post["edit_path"];
			unset($post["edit_path"]);
			$nav_model = D("Nav");
			if($n_id){
				if(strlen($edit_path)==6 and substr($edit_path, 0, 4)==$path or strlen($edit_path)==4){
					unset($post["n_path"]);
				}
				else{
					$s_path = $nav_model->query("select (case count(0) when 0 then 10 else max(cast(substr(n_path,length(".$path.")+1,2) as signed))+1 end) as current_path from admin_nav where left(n_path,length(".$path."))='".$path."' and n_path <> '".$path."'");
					$post["n_path"] .= $s_path[0]["current_path"];
				}
				if($id=$nav_model->where("n_id = ".$n_id)->save($post)){
					$type = "success";
					$infomation = "修改成功!";
				}
				else{
					$type = "error";
					$infomation = "修改失败!";
				}
			}
			else{
				$s_path = $nav_model->query("select (case count(0) when 0 then 10 else max(cast(substr(n_path,length(".$path.")+1,2) as signed))+1 end) as current_path from admin_nav where left(n_path,length(".$path."))='".$path."' and n_path <> '".$path."'");
				$post["n_path"] .= $s_path[0]["current_path"];
				if($id=$nav_model->data($post)->add()){
					$type = "success";
					$infomation = "添加成功!";
				}
				else{
					$type = "error";
					$infomation = "添加失败!";
				}
			}

			$json["info"] = $this->getInfomation($type, $infomation);
			$json["value"] = $post["n_path"] ;
			$json["url"] = "/index.php/admin/Admin/add_nav/page/".$page;
			$json["path"] = "last_1";
			$json["nav"] = $this->getNavHtml();
			echo json_encode($json);
		}
	}

	public function delete($page=1, $n_id = 0){
		$nav_model = D("Nav");
		//$nav = $nav_model->where("n_id = ".$n_id)->find();
		//if($nav_model->where("n_path like '".$nav["n_path"]."%'")->delete()){
		if($nav_model->where("n_id = ".$n_id)->delete()){
			$type = "success";
			$infomation = "删除成功!";
		}
		else{
			$type = "error";
			$infomation = "删除失败!";
		}
		$json["info"] = $this->getInfomation($type, $infomation);
		$json["url"] = "/index.php/admin/Admin/add_nav/page/".$page;
		$json["path"] = "last_1";
		$json["nav"] = $this->getNavHtml();
		echo json_encode($json);
	}

	public function batch($page=1){
		$post = $_POST;
		$nav_model = D("Nav");
		if($post["choose"]=='delete'){
			unset($post["choose"]);
			foreach($post as $key=>$value){
				$n_ids[] = $key;
			}
			$delete_num = count($n_ids);
			if($nav = $nav_model->where("n_id in (".implode(",",$n_ids).")")->delete()){
				$type = "success";
				$infomation = "删除成功".$delete_num."条!";
			}
			else{
				$type = "error";
				$infomation = "删除失败!";
			}
		}
		$json["info"] = $this->getInfomation($type, $infomation);
		$json["url"] = "/index.php/admin/Admin/add_nav/page/".$page;
		$json["path"] = "last_1";
		$json["nav"] = $this->getNavHtml();
		echo json_encode($json);
	}
}
