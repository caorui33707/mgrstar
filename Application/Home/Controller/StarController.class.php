<?php

namespace Home\Controller;

/**
 * 轮播列表
 * @date finished at 2017-6-9
 *
 * Class StarController
 * @package Home\Controller
 */
class StarController extends CTController
{
    //软删除
    const DELETE_TRUE = 1;
    const DELETE_FALSE = 0;

//const UPLOADSDIR = '\.' .DIRECTORY_SEPARATOR. 'Public'. DIRECTORY_SEPARATOR;             // ./Public/uploads/carousel/
//const STARDIR = 'uploads' . DIRECTORY_SEPARATOR . 'carousel' . DIRECTORY_SEPARATOR;     //  uploads/carousel/

	const UPLOADSDIR = "Public/uploads/";
	const STARDIR = "carousel/";

	public function __construct()
    {
        parent::__construct();
        $this->assign('title', '轮播列表');

    }

    //模板显示
    public function carousel()
    {

        $model = M('star_bannerlist');
        $count = $model->where("`delete_flag` = ".self::DELETE_FALSE)->count('id');

        $this->assign('count', $count);
        $this->display('star/carousel');
    }

    /**
     * 添加明星轮播图
     */
    public function addCarousel()
    {
        //接收过滤提交数据
        $starname = I('post.starname', '', 'strip_tags');
        $starname = trim($starname);

        $pic_url = I('post.pic_url', '', 'strip_tags');
        $pic_url = trim($pic_url);

        $local_pic = I('post.local_pic', '', 'strip_tags');
        $local_pic = trim($local_pic);

        $starcode = (int)$_POST['starcode'];
        $sort = (int)$_POST['sort'];

        if ($sort >= 5) {
            $return = array(
                'code' => -2,
                'message' => '排序不能大于5'
            );
            return $this->ajaxReturn($return);
        }
        //唯一性判断
        $model = M('star_bannerlist');
        $map = array();

        $map['sort'] =  $sort;
        $map['delete_flag'] = self::DELETE_FALSE;

        $count = $model->where($map)->count('id');
        if ($count) {
            $return = array(
                'code' => -2,
                'message' => '已有该排序'
            );
            return $this->ajaxReturn($return);
        }

        //唯一性判断
        $model = M('star_bannerlist');
        $count = $model->where("`delete_flag` = ".self::DELETE_FALSE)->count('id');
        if ($count > 4) {
            $return = array(
                'code' => -2,
                'message' => '已有固定Banner，请先删除其他！'
            );
            return $this->ajaxReturn($return);
        }

        //非空提醒
        if (empty($starname) || empty($starcode)) {
            $return = array(
                'code' => -2,
                'message' => '请输入正确的明星名称和明星ID！'
            );
            return $this->ajaxReturn($return);
        }

        $isExist = (int)$model->where("`starname` = '{$starname}' AND `delete_flag` = ".self::DELETE_FALSE)->count('id');
        if ($isExist) {
            $return = array(
                'code' => -2,
                'message' => '该明星信息已存在！'
            );
            return $this->ajaxReturn($return);
        }

        //数据入库
        $model->starname = $starname;
        $model->starcode = $starcode;
        $model->pic_url = $pic_url;
        $model->local_pic = $local_pic;
        $model->sort = $sort;
        $model->add_time = time();
        $bool = ($model->add()) ? 0 : 1;

        //结果返回
        $return = array(
            'code' => $bool,
            'message' => 'success',
        );
        return $this->ajaxReturn($return);
    }

    /**
     * 接收的明星姓名查询明星对应信息
     */
    public function getStarInfo()
    {
        $model = M('star_starbrief');
        $starname = I('post.starname', '', 'strip_tags');
        $starname = trim($starname);

        $item = $model->where("`name` = '{$starname}' AND status = 0 ")->find();// 默认 明星上线的才添加

        //$arr['star_code'] = '';
       // $arr['star_name'] = "未找到明星 -{$starname}";
        if (count($item) > 0) {
            $arr['star_code'] = $item['code'];
            $arr['star_name'] = $item['name'];
        }else{
            $return = array(
                'code' => -2,
                'message' => "未找到明星 -{$starname}"
            );
            return $this->ajaxReturn($return); exit();
        }

        return $this->ajaxReturn($arr);
    }

    /**
     * 图片上传
     */
    public function uploadFile()
    {
        $ret['file'] = '';
        $dir = './' . self::UPLOADSDIR . self::STARDIR;

        file_exists($dir) || (mkdir($dir, 0777, true) && chmod($dir, 0777));

        $hostUrl = 'http://'.$_SERVER['HTTP_HOST'];
        if (!is_array($_FILES['myfile']['name'])) {
            $path = pathinfo($_FILES['myfile']['name']);
			$fileName = date('ymdhis') . uniqid() . '.' . $path['extension'];
            move_uploaded_file($_FILES['myfile']['tmp_name'], $dir . $fileName);
            $ret['file'] =  $hostUrl . '/' . self::UPLOADSDIR . self::STARDIR . $fileName;
            $ret['local'] = $fileName;
        }

        echo json_encode($ret);
    }

    /**
     * 编辑信息
     * todo 设置图片的大小
     */
    public function editCarousel()
    {
        $id = (int)$_POST['id'];
        if (!$id) {
            $return = array(
                'code' => -2,
                'message' => '未找到要更新的数据'
            );
            return $this->ajaxReturn($return);
        }

        $sort = (int)$_POST['sort'];
        if ($sort >= 5) {
            $return = array(
                'code' => -2,
                'message' => '排序不能大于5'
            );
            return $this->ajaxReturn($return);
        }
        //唯一性判断
        $model = M('star_bannerlist');


        $bool = 1;
        $item = $model->where("`id` = '{$id}'")->find();

        $pic_url = I('post.pic_url', '', 'strip_tags');
        $pic_url = trim($pic_url);
        if (!empty($pic_url)) {
            $model->pic_url = $pic_url;
        }

        $local_pic = I('post.local_pic', '', 'strip_tags');
        $local_pic = trim($local_pic);

        if (!empty($local_pic)) {
            $model->local_pic = $local_pic;
        }

        if (count($item) > 0) {

            $model->id = $id;
           // $model->sort = $sort;
            $model->modify_time = time();

            if ($model->save()) {
                $bool = 0;

                (!empty($pic_url) && $item['pic_url'] != $pic_url) ? @unlink('./' . self::UPLOADSDIR . self::STARDIR . $item['local_pic']) : '';
            }
        }

        //结果返回
        $return = array(
            'code' => $bool,
            'message' => '修改成功！',
        );
        return $this->ajaxReturn($return);
    }

    /**
     * 软删除
     */
    public function delCarousel()
    {
        //获取提交过来的ID值并进行分割 in 查询
        $ids = implode(',', $_POST['ids']);
        $model = M('star_bannerlist');
        $list = $model->where(array('id'=>array('in', $ids)))->select();

        //已查到的存在的数据
        $idArr = array();
        foreach ($list as $item) {
            $idArr[] = $item['id'];
        }
        $idIn = implode(',', $idArr);

        //数据更新
        $data = array(
            'delete_flag' => self::DELETE_TRUE,
            'modify_time' => time()
        );
        $bool = ($model->where(array('id'=>array('in', $idIn)))->save($data)) ? 0 : 1;

        //结果返回
        $return = array(
            'code' => $bool,
            'message' => 'success',
        );
        return $this->ajaxReturn($return);
    }

    /**
     * 轮播列表
     * @todo 搜索
     */
    public function searchCarousel()
    {
        $carousel = M('star_bannerlist');
        $pageNum = I('post.pageNum', 5, 'intval');
        $page = I('post.page', 1, 'intval');
        $map = array('delete_flag' => self::DELETE_FALSE);

        $count = $carousel->where($map)->count();// 查询满足要求的总记录数
        $list = $carousel->where($map)->page($page, $pageNum)->order('sort desc')->select();//获取分页数据

        new \Think\Page($count, $pageNum);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $data['totalPages'] = $count;
        $data['pageNum'] = $pageNum;
        $data['page'] = $page;
        $data['totalPages'] = ceil($count / $pageNum);
        $data['list'] = $list;

        $this->ajaxReturn($data);
    }
}
