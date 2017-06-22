<?php

namespace Home\Controller;
use Think\Controller;

class DataSearchController extends Controller
{

    private $user;

    public function __construct(){
        parent::__construct();
        $this->user = session('user');
        if(!$this->user){
            $this->display('Login/login');
        }
        $this->assign('user',$this->user);
        $this->assign('action','dataSearch');
    }

    public function fundList(){
        $this->assign('actionUrl','fundList');

        $this->display('DataSearch/fundList');
    }

    public function position(){ // 持仓
        $this->assign('actionUrl','position');

        $this->display('DataSearch/position');
    }

    public function recharge(){ //充值
        $this->assign('actionUrl','recharge');

        $this->display('DataSearch/recharge');
    }

    //交易明细
    public function transaction(){
        $this->assign('actionUrl','transaction');

        $this->display('DataSearch/transaction');
    }

    //成交明细
    public function success(){
        $this->assign('actionUrl','success');

        $this->display('DataSearch/success');
    }

    //成交明细汇总
    public function success_total(){
        $this->assign('actionUrl','success_total');

        $this->display('DataSearch/success_total');
    }


    public function getUserInfo(){
        $map = array();
        $user_info = M('star_userinfo');

        $pageNum = isset($_POST['pageNum'])?$_POST['pageNum']:5;
        $page = isset($_POST['page'])?$_POST['page']:1;
        $count = $user_info->where($map)->count();// 查询满足要求的总记录数

        $list = $user_info->where($map)->page($page,$pageNum)->select();//获取分页数据
        foreach($list as $l){
            $userIds[] = $l['uid'];
        }

        $whereSellUids['sell_uid']  =  array('in',$userIds); // sell_type   buy_type
        $whereBuyUids['sell_type'] = $whereSellUids['sell_type'] =  2 ;// 卖方完成
        $whereBuyUids['sell_type'] = $whereSellUids['buy_type'] =  2 ;// 买方完成


        $sellRows = M('star_orderlist')->where($whereSellUids)->select(); //卖方   order_price  order_num


        $whereBuyUids['buy_uid']  =  array('in',$userIds);
        $buyRows = M('star_orderlist')->where($whereBuyUids)->select(); //买方


        $sellPriceSum = array();

        foreach ($sellRows as $s){
            //
            $sellPrice =  $s['order_num']* $s['order_price'];

            $sellOrderPrice[$s['sell_uid']]['order_price'][] = $sellPrice;
            $sellPriceSum[$s['sell_uid']] = $s;
            $sellPriceSum[$s['sell_uid']]['order_price'] = $sellOrderPrice[$s['sell_uid']]['order_price'];

        }


        foreach ($sellPriceSum as $id=>$sellArr){
            $sellPriceSum[$id]['order_sum_price'] = array_sum($sellArr['order_price']);
            unset($sellPriceSum[$id]['order_price']);
        }

        $buyPriceSum = array();

        foreach ($buyRows as $b){
            $buyPrice =  $b['order_num']* $b['order_price'];

            $buyOrderPrice[$b['buy_uid']]['order_price'][] = $buyPrice;
            $buyPriceSum[$b['buy_uid']] = $b;
            $buyPriceSum[$b['buy_uid']]['order_price'] = $buyOrderPrice[$b['buy_uid']]['order_price'];
        }


        foreach ($buyPriceSum as $id=>$buyArr){
            $buyPriceSum[$id]['order_sum_price'] = array_sum($buyArr['order_price']);
            unset($buyPriceSum[$id]['order_price']);
        }

        // 用户的余额
        $whereBalance['uid']  =  array('in',$userIds);
        $balaceArr = array();

        foreach (M('user_balance')->where($whereBalance)->select() as $bArr){
            $balaceArr[$bArr['uid']] = $bArr;
        }

        //
        //冻结资金

        $wheredongUids['buy_id']  =  array('in',$userIds); // 必须是 买家
        $dongUids['sell_type']    =  array('neq',2) ;// 卖方不完成
        $dongUids['buy_type']     =   2 ;// 买方完成
        $freezeRows = M('star_orderlist')->where($dongUids)->select(); //买方

        $freezePriceSum = array();

        foreach ($freezeRows as $f){
            $freezePrice =  $f['order_num']* $f['order_price'];

            //  dump($freezePrice);

            $freezeOrderPrice[$f['buy_uid']]['order_price'][] = $freezePrice;
            $freezePriceSum[$f['buy_uid']] = $f;
            $freezePriceSum[$f['buy_uid']]['order_price'] = $freezeOrderPrice[$f['buy_uid']]['order_price'];
        }


        foreach ($freezePriceSum as $id=>$freeArr){
            $freePriceSum[$id]['order_sum_price'] = array_sum($freeArr['order_price']);
            unset($freePriceSum[$id]['order_price']);
        }

        // 总资产
        $whereTotalUids['sell_uid']  =  array('in',$userIds); // 必须是 卖家
        // $whereTotalUids['sell_type'] =   array('neq',2) ;// 卖方不完成
        // $whereTotalUids['buy_type']  =  array('neq',2) ;// 买方完成
        $whereTotalUids['_string'] = 'sell_type <> 2 OR buy_type <> 2';

        $where = array();
        $totalRows = M('star_orderlist')->where($whereTotalUids)->select(); //买方

        foreach ($totalRows as $t){
            $totalPrice =  $t['order_num']* $t['order_price'];
            $totalOrderPrice[$t['sell_uid']]['order_price'][] = $totalPrice;

            $totalOrderPriceArr[$t['sell_uid']] = $t;
            $totalOrderPriceArr[$t['sell_uid']]['order_price'] = $totalOrderPrice[$t['sell_uid']]['order_price'];
        }


        foreach ($totalOrderPriceArr as $id=>$totalArr){
            $totalSellArr[$id]['order_sum_price'] = array_sum($totalArr['order_price']);
            // unset($sellPriceSum[$id]['order_price']);
        }



        foreach ($list as $key=>$l){
            $listAll[$key] = $l;
            $listAll[$key]['sell_info'] = $sellPriceSum[$l['uid']];// 卖家  ：收入
            $listAll[$key]['buy_info']  = $buyPriceSum[$l['uid']];//买家 ：支出

            $listAll[$key]['total_info'] = $totalSellArr[$l['uid']]; // 卖家时,没成功的为资产

            $listAll[$key]['balance_info'] = $balaceArr[$l['uid']]; // 余额资金

            $listAll[$key]['freeze_info'] = $freePriceSum[$l['uid']]; // 可用资金
        }


        $data['totalPages'] = $count;
        $data['pageNum'] =$pageNum;
        $data['page'] = $page;
        $data['totalPages'] = ceil($count/$pageNum);
        $data['list'] = $listAll;

        $this->ajaxReturn($data);
    }

    public function  getUserOrderinfo(){
        $map = array();
        $user_info = M('star_userinfo');

        $pageNum = isset($_POST['pageNum'])?$_POST['pageNum']:5;
        $page = isset($_POST['page'])?$_POST['page']:1;
        $count = $user_info->where($map)->count();// 查询满足要求的总记录数

        $list = $user_info->where($map)->page($page,$pageNum)->select();//获取分页数据

        $userIds = array();

        foreach($list as $l){
            $userIds[]   = $l['uid'];
            $memberIds[] = $l['memberId']; // 机构
            $agentSubIds[] = $l['agentId']; // 经纪人

            //$agentIds[] = $l['agentIdSub']; 区域经纪人
        }

        $memberIds = array_filter(array_unique($memberIds));
        $agentSubIds = array_filter(array_unique($agentSubIds));

        $whereMemberIds['memberid'] = array('in',$memberIds);
        $whereAgentSubIds['id'] = array('in',$agentSubIds);

        $memberRows = $this->getMemberNmae($whereMemberIds);
        // $agentRows  = $this->getAgentName($whereAgentIds);
        $agentSubRows  = $this->getAgentSubName($whereAgentSubIds);

        foreach ($memberRows as $m){
            $memberData[$m['memberid']]['name'] = $m['name'];
        }


        foreach ($agentSubRows as $a){
            $agentSubData[$a['id']]['nickname'] = $a['nickname'];
        }



        $whereOrderUids['buy_uid']  =  array('in',$userIds); //买方uids
        $whereOrderUids['sell_type'] =  2 ;// 卖方完成
        $whereOrderUids['buy_type'] =  2 ;// 买方完成

        $finishedbuyRows = $this->getBuyRows($whereOrderUids);

        $whereunfinishedUids['buy_uid']  =  array('in',$userIds);
        $whereunfinishedUids['_string'] = 'sell_type <> 2 OR buy_type <> 2';
        $unfinishedBuyRows = $this->getBuyRows($whereunfinishedUids);


        foreach ($list as $l){
            $lists[$l['uid']] = $l;

            $lists[$l['uid']]['member'] = $memberData[$l['memberId']];

            $lists[$l['uid']]['agent_sub'] = $agentSubData[$l['agentId']];

            $lists[$l['uid']]['finished_buy_price'] = $finishedbuyRows[$l['uid']];
            //$lists[$l['uid']]['finished_buy_price']['nums'] = count($finishedbuyRows[$l['uid']]);
            //$lists[$l['uid']]['starcode'] = $finishedbuyRows[$l['uid']]['starcode'];

            foreach ($finishedbuyRows[$l['uid']] as $fd){
                $lists[$l['uid']]['finished_buy_price'][$fd['starcode']]['un_order_num'] =  $unfinishedBuyRows[$l['uid']][$fd['starcode']]['order_num'];
            }

            // $lists[$l['uid']]['unfinished_buy_price']= $unfinishedBuyRows[$l['uid']];
            //$lists[$l['uid']]['unfinished_buy_price']['nums'] = count($unfinishedBuyRows[$l['uid']]);
        }

        $data['totalPages'] = $count;
        $data['pageNum'] =$pageNum;
        $data['page'] = $page;
        $data['totalPages'] = ceil($count/$pageNum);
        $data['list'] = $lists;

        $this->ajaxReturn($data);

    }

    //充值
    public function getRechargeInfo(){
        $map = array();
        $user_info = M('star_userinfo');

        $pageNum = isset($_POST['pageNum'])?$_POST['pageNum']:5;
        $page = isset($_POST['page'])?$_POST['page']:1;
        $count = $user_info->where($map)->count();// 查询满足要求的总记录数

        $list = $user_info->where($map)->page($page,$pageNum)->select();//获取分页数据

        foreach ($list as $l){
            $userUids[] = $l['uid'];
            $memberIds[] = $l['memberId']; // 机构
            $agentSubIds[] = $l['agentId']; // 经纪人
        }


        $memberIds = array_filter(array_unique($memberIds));
        $agentSubIds = array_filter(array_unique($agentSubIds));

        $whereMemberIds['memberid'] = array('in',$memberIds);
        $whereAgentSubIds['id'] = array('in',$agentSubIds);

        $memberRows = $this->getMemberNmae($whereMemberIds);
        // $agentRows  = $this->getAgentName($whereAgentIds);
        $agentSubRows  = $this->getAgentSubName($whereAgentSubIds);

        foreach ($memberRows as $m){
            $memberData[$m['memberid']]['name'] = $m['name'];
        }


        foreach ($agentSubRows as $a){
            $agentSubData[$a['id']]['nickname'] = $a['nickname'];
        }


        $recharge_info = M('recharge_info');

        $whereIds['uid'] = array('in',$userUids);

        $whereIds['status'] = 1;

        $rechargeRows = $recharge_info->field('sum(amount) as amount_sum,uid,depositType')->where($whereIds)->group('uid')->select();

        //充值类型 1:微信 2:银行卡
        $depositArr = array('未知','微信','银行卡');

        foreach ($rechargeRows as $w){
            $w['deposit_name'] = $depositArr[$w['depositType']];
            $rechargeData[$w['uid']] = $w;

        }

        foreach ($list as $l) {
            $lists[$l['uid']] = $l;

            $lists[$l['uid']]['member'] = $memberData[$l['memberId']];

            $lists[$l['uid']]['agent_sub'] = $agentSubData[$l['agentId']];

            $lists[$l['uid']]['recharge'] = $rechargeData[$w['uid']];
        }

        $data['totalPages'] = $count;
        $data['pageNum'] =$pageNum;
        $data['page'] = $page;
        $data['totalPages'] = ceil($count/$pageNum);
        $data['list'] = $lists;

        $this->ajaxReturn($data);
    }

    //交易  失败的订单

    public function getTransactionInfo(){

        $star_orderlist = M('star_orderlist');

        $whereOreder['order_type'] = -1;

        $pageNum = isset($_POST['pageNum'])?$_POST['pageNum']:5;
        $page = isset($_POST['page'])?$_POST['page']:1;

        $count = $star_orderlist->where($whereOreder)->count();// 查询满足要求的总记录数


        $list = $star_orderlist->where($whereOreder)->page($page,$pageNum)->select();

        foreach ($list as $l){
            $buyUid[] = $l['buy_uid'];
            $sellUid[] = $l['sell_uid'];
        }


        $uids = array_merge($sellUid,$buyUid);
        $uids = array_filter(array_unique($uids));

        $map = array();
        $user_info = M('star_userinfo');

        $map['uid'] = array('in',$uids);

        $userRows = $user_info->where($map)->select();//获取分页数据

        foreach ($userRows as $u){
            $userUids[] = $u['uid'];
            $memberIds[] = $u['memberId']; // 机构
            $agentSubIds[] = $u['agentId']; // 经纪人

            $userInfo[$u['uid']] = $u;
        }


        $memberIds = array_filter(array_unique($memberIds));
        $agentSubIds = array_filter(array_unique($agentSubIds));

        $whereMemberIds['memberid'] = array('in',$memberIds);
        $whereAgentSubIds['id'] = array('in',$agentSubIds);

        $memberRows = $this->getMemberNmae($whereMemberIds);
        // $agentRows  = $this->getAgentName($whereAgentIds);
        $agentSubRows  = $this->getAgentSubName($whereAgentSubIds);

        foreach ($memberRows as $m){
            $memberData[$m['memberid']]['name'] = $m['name'];
        }

        foreach ($agentSubRows as $a){
            $agentSubData[$a['id']]['nickname'] = $a['nickname'];
        }


        foreach ($list as $l){
                $lists[$l['id']] = $l;
                $sellUid = $l['sell_uid'];
                $buyUid  = $l['sell_uid'];

                $lists[$l['id']]['sell_name'] = isset($userInfo[$sellUid]['nickname'])?$userInfo[$sellUid]['nickname']:'';
                $lists[$l['id']]['sell_phone'] = isset($userInfo[$sellUid]['phoneNum'])?$userInfo[$sellUid]['phoneNum']:'';

                $lists[$l['id']]['buy_name'] = isset($userInfo[$buyUid]['nickname'])?$userInfo[$buyUid]['nickname']:'';
                $lists[$l['id']]['buy_phone'] = isset($userInfo[$buyUid]['phoneNum'])?$userInfo[$buyUid]['phoneNum']:'';

                $lMemberId = $userInfo[$buyUid]['memberId'];
                $lagentId = $userInfo[$buyUid]['agentId'];

                $lists[$l['id']]['member'] = $memberData[$lMemberId];

                $lists[$l['id']]['agent_sub'] = $agentSubData[$lagentId];
        }


        $data['totalPages'] = $count;
        $data['pageNum'] =$pageNum;
        $data['page'] = $page;
        $data['totalPages'] = ceil($count/$pageNum);
        $data['list'] = $lists;

        $this->ajaxReturn($data);

    }

    public function getSuccessInfo(){
        $star_orderlist = M('star_orderlist');

        $whereOreder['order_type'] = 2;

        $pageNum = isset($_POST['pageNum'])?$_POST['pageNum']:5;
        $page = isset($_POST['page'])?$_POST['page']:1;

        $count = $star_orderlist->where($whereOreder)->count();// 查询满足要求的总记录数


        $list = $star_orderlist->where($whereOreder)->page($page,$pageNum)->select();

        foreach ($list as $l){
            $buyUid[] = $l['buy_uid'];
            $sellUid[] = $l['sell_uid'];
        }


        $uids = array_merge($sellUid,$buyUid);
        $uids = array_filter(array_unique($uids));

        $map = array();
        $user_info = M('star_userinfo');

        $map['uid'] = array('in',$uids);

        $userRows = $user_info->where($map)->select();//获取分页数据

        foreach ($userRows as $u){
            $userUids[] = $u['uid'];
            $memberIds[] = $u['memberId']; // 机构
            $agentSubIds[] = $u['agentId']; // 经纪人

            $userInfo[$u['uid']] = $u;
        }


        $memberIds = array_filter(array_unique($memberIds));
        $agentSubIds = array_filter(array_unique($agentSubIds));

        $whereMemberIds['memberid'] = array('in',$memberIds);
        $whereAgentSubIds['id'] = array('in',$agentSubIds);

        $memberRows = $this->getMemberNmae($whereMemberIds);
        // $agentRows  = $this->getAgentName($whereAgentIds);
        $agentSubRows  = $this->getAgentSubName($whereAgentSubIds);

        foreach ($memberRows as $m){
            $memberData[$m['memberid']]['name'] = $m['name'];
        }

        foreach ($agentSubRows as $a){
            $agentSubData[$a['id']]['nickname'] = $a['nickname'];
        }


        foreach ($list as $l){
            $lists[$l['id']] = $l;
            $sellUid = $l['sell_uid'];
            $buyUid  = $l['sell_uid'];

            $lists[$l['id']]['sell_name'] = isset($userInfo[$sellUid]['nickname'])?$userInfo[$sellUid]['nickname']:'';
            $lists[$l['id']]['sell_phone'] = isset($userInfo[$sellUid]['phoneNum'])?$userInfo[$sellUid]['phoneNum']:'';

            $lists[$l['id']]['buy_name'] = isset($userInfo[$buyUid]['nickname'])?$userInfo[$buyUid]['nickname']:'';
            $lists[$l['id']]['buy_phone'] = isset($userInfo[$buyUid]['phoneNum'])?$userInfo[$buyUid]['phoneNum']:'';

            $lMemberId = $userInfo[$buyUid]['memberId'];
            $lagentId = $userInfo[$buyUid]['agentId'];



            $lists[$l['id']]['member'] = $memberData[$lMemberId];

            $lists[$l['id']]['agent_sub'] = $agentSubData[$lagentId];

            $lists[$l['id']]['close_time'] = date('Y-m-d H:i:s',$l['close_time']);
        }


        $data['totalPages'] = $count;
        $data['pageNum'] =$pageNum;
        $data['page'] = $page;
        $data['totalPages'] = ceil($count/$pageNum);
        $data['list'] = $lists;

        $this->ajaxReturn($data);


    }

    private function getBuyRows($where){

        $orederRows = M('star_orderlist')->field(' sum(order_num) as nums ,starcode,buy_uid ')->where($where)->group('starcode')->select(); //买方

        // dump($orederRows);

        foreach ($orederRows as $o){
            $rows[$o['buy_uid']][$o['starcode']]['order_num'] = $o['nums'];
            $rows[$o['buy_uid']][$o['starcode']]['starcode'] = $o['starcode'];
        }


        // $rowArr[$o['buy_uid']]['order_num'] = array_sum($rows[$o['buy_uid']]['order_num']);
        // $rowArr[$o['buy_uid']]['starcode'] = $rows[$o['buy_uid']]['starcode'];

        return $rows;
    }

    private function getMemberNmae($where){
        $member_info = M('member_info');
        $memberInfo = $member_info->where($where)->select();

        return $memberInfo;
    }


    private function getAgentName($where){
        $agent_info = M('agent_info');
        return $agent_info->where($where)->select();
    }

    private function getAgentSubName(){
        $agent_info = M('agentsub_info');
        return $agent_info->where($where)->select();
    }

    private function userInfoUids(){
        $user_info = M('star_userinfo');

        $pageNum = isset($_POST['pageNum'])?$_POST['pageNum']:5;
        $page = isset($_POST['page'])?$_POST['page']:1;
        $count = $user_info->where($map)->count();// 查询满足要求的总记录数

        $list = $user_info->where($map)->page($page,$pageNum)->select();//获取分页数据

        $userIds = array();

        foreach($list as $l){
            $userIds[] = $l['uid'];
        }

        return $userIds;
    }

}