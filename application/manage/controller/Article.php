<?php
namespace app\manage\controller;
use app\manage\controller\Conn;
use app\manage\model\Cate as Catemodel;
use app\manage\model\Article as Articlemodel;
use think\Db;
class Article extends Conn
{
    public function index()
    {
    	
		if(request()->isPost()){
			$data=input('post.');
			$info=Db::table('lizhili_article')
			->alias('a')
			->join('cate c','a.cateid = c.id','LEFT')
			->field('a.id,a.title,a.author,a.desc,a.pic,a.click,a.state,a.time,c.catename')
			->whereOr('c.catename','like','%'.$data['key'].'%')
			->order('id desc')
			->paginate(10);
			$this->assign('info',$info);
		}else{
			$info=Db::table('lizhili_article')
			->alias('a')
			->join('cate c','a.cateid = c.id','LEFT')
			->field('a.id,a.title,a.author,a.desc,a.pic,a.click,a.state,a.time,c.catename')
			->order('id desc')
			->paginate(10);
			$this->assign('info',$info);
		}
		
		$count1=db('article')->count();
		$this->assign('count1', $count1);
		$cate=new Catemodel();
		$datasort=$cate->tree();
		$this->assign('datasort',$datasort);
       	return $this->fetch();
    }
	public function ajax()
    {
    	$data=input('param.');
		if($data['type']=='article_del'){
			if(db('article')->delete($data['id'])){
				return 1;//修改成功返回1
			}else{
				return 0;
			}
		}
		if($data['type']=='article_all'){
			if(db('article')->delete($data['id'])){
				return 1;//修改成功返回1
			}else{
				return 0;
			}
		}
       
		return 0;
    }
	public function add()
    {
    	if(request()->isPost()){
    		$data=input('post.');
			$validate = new \app\manage\validate\Article;
			if(!$validate->check($data)){
				$this->error($validate->getError());
			}
			if(!isset($data['state'])){
				$data['state']=0;
			}else{
				$data['state']=1;
			}
			$data['time']=time();
			
			$file = request()->file('');
			
			
			if(isset($file['pic'])){
				$info = $file['pic']->move('uploads');
				$li=strtr($info->getSaveName()," \ "," / ");
				$data['pic']='/uploads/'.$li;
			}else{
				if(isset($data['ti'])){
					preg_match_all("/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/",$data["editorValue"],$arr);
					foreach($arr[1] as $k=>$v){
						$newarr=getimagesize(substr($v,1));
						if($newarr[0] >100 and $newarr[1] >100){
							$data['pic']=$v;
							break;
						}
					}
				}
			}
			if(input('desc')==''){
				$data['desc']=mb_substr(strip_tags(input('editorValue')),0,80);
			}
			
		

			if(db('article')->insert($data)){
				return '<script>alert("你好，添加成功了！");parent.location.reload()</script>';
			}else{
				$this->error('添加失败了');
			}
    	}
		$cate=new Catemodel();
		$datasort=$cate->tree1();
		$this->assign('datasort',$datasort);
      	return $this->fetch();
    }
	public function edit()
    {
    	if(request()->isPost()){
    		$data=input('post.');
			$validate = new \app\manage\validate\Article;
			if(!$validate->check($data)){
				$this->error($validate->getError());
			}
			
			if(!isset($data['state'])){
				$data['state']=0;
			}else{
				$data['state']=1;
			}
			$data['time']=time();
			if(input('desc')==''){
				$data['desc']=mb_substr(strip_tags(input('editorValue')),0,200);
			}
			
			
			$file = request()->file('');
			if(isset($file['pic'])){
				$info = $file['pic']->move('uploads');
				$li=strtr($info->getSaveName()," \ "," / ");
				$data['pic']='/uploads/'.$li;
			}else{
				if(isset($data['ti'])){
					preg_match_all("/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/",$data["editorValue"],$arr);
					foreach($arr[1] as $k=>$v){
						$newarr=getimagesize(substr($v,1));
						if($newarr[0] >100 and $newarr[1] >100){
							$data['pic']=$v;
							break;
						}
					}
				}
			}
			
			$article=new Articlemodel();
			$data1=$article->save($data,['id' => input('id')]);
			if($data1){
				return $this->success('修改成功', url('article/index'));
			}else{
				return $this->error('修改失败了');
			}
    	}
		
		$cid=input('id');
		$data=db('article')->where('id',$cid)->find();
		$this->assign('data',$data);
		$cate=new Catemodel();
		$datasort=$cate->tree1();
		$this->assign('datasort',$datasort);
       return $this->fetch();
    }
	
}