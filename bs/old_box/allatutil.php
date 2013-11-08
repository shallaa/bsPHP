<?php
    /******************************  Comment  *****************************
      File Name         : allatutil.php
      File Description  : Allat Script API Utility Function(Class)
      [ Notice ]
       이 파일은 NewAllatPay를 사용하기 위한 Utility Function을 구현한
      Source Code입니다. 이 파일에 내용을 임의로 수정하실 경우 기술지원을
      받으실 수 없음을 알려드립니다. 이 파일 내용에 문제가 있을 경우,
      아래 연락처로 문의 주시기 바랍니다.

      TEL       : 02-3783-9990
      EMAIL     : allatpay@allat.co.kr
      Homepage  : www.allatpay.com
     ***********  Copyright Allat Corp. All Right Reserved  **************/

  define(util_lang,"PHP");
  define(util_ver,"1.0.7.1");

  define(approval_uri,     "POST /servlet/AllatPay/pay/approval.jsp HTTP/1.0\r\n");
  define(sanction_uri,     "POST /servlet/AllatPay/pay/sanction.jsp HTTP/1.0\r\n");
  define(cancel_uri,       "POST /servlet/AllatPay/pay/cancel.jsp HTTP/1.0\r\n");
  define(cashreg_uri,      "POST /servlet/AllatPay/pay/cash_registry.jsp HTTP/1.0\r\n");
  define(cashapp_uri,      "POST /servlet/AllatPay/pay/cash_approval.jsp HTTP/1.0\r\n");
  define(cashcan_uri,      "POST /servlet/AllatPay/pay/cash_cancel.jsp HTTP/1.0\r\n");
  define(escrowchk_uri,    "POST /servlet/AllatPay/pay/escrow_check.jsp HTTP/1.0\r\n");
  define(escrowret_uri,    "POST /servlet/AllatPay/pay/escrow_return.jsp HTTP/1.0\r\n");
  define(escrowconfirm_uri,"POST /servlet/AllatPay/pay/escrow_confirm.jsp HTTP/1.0\r\n");
  define(certreg_uri,      "POST /servlet/AllatPay/pay/fix.jsp HTTP/1.0\r\n"); 
  define(certcancel_uri,   "POST /servlet/AllatPay/pay/fix_cancel.jsp HTTP/1.0\r\n");

  define(c2c_approval_uri,     "POST /servlet/AllatPay/pay/c2c_approval.jsp HTTP/1.0\r\n");
  define(c2c_cancel_uri,       "POST /servlet/AllatPay/pay/c2c_cancel.jsp HTTP/1.0\r\n");
  define(c2c_sellerreg_uri,    "POST /servlet/AllatPay/pay/seller_registry.jsp HTTP/1.0\r\n");
  define(c2c_productreg_uri,   "POST /servlet/AllatPay/pay/product_registry.jsp HTTP/1.0\r\n");
  define(c2c_buyerchg_uri,     "POST /servlet/AllatPay/pay/buyer_change.jsp HTTP/1.0\r\n");
  define(c2c_escrowchk_uri,    "POST /servlet/AllatPay/pay/c2c_escrow_check.jsp HTTP/1.0\r\n");
  define(c2c_escrowconfirm_uri,"POST /servlet/AllatPay/pay/c2c_escrow_confirm.jsp HTTP/1.0\r\n");
  define(c2c_esrejectcheck_uri,"POST /servlet/AllatPay/pay/c2c_reject_check.jsp HTTP/1.0\r\n");
  define(c2c_expressreg_uri,   "POST /servlet/AllatPay/pay/c2c_express_reg.jsp HTTP/1.0\r\n");

  define(allat_addr_ssl,"ssl://tx.allatpay.com" );
  define(allat_addr,"tx.allatpay.com");
  define(allat_host,"tx.allatpay.com");

  function ApprovalReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";
    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo( $at_data, allat_addr_ssl, approval_uri, allat_host, 443 );
    }else{
      $isEnc=checkEnc( $at_data );
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo( $at_data, allat_addr, approval_uri, allat_host, 80 );
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }

  function SanctionReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";
    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo( $at_data, allat_addr_ssl, sanction_uri, allat_host, 443 );
    }else{
      $isEnc=checkEnc( $at_data );
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo( $at_data, allat_addr, sanction_uri, allat_host, 80 );
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }

  function CancelReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";
    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo( $at_data, allat_addr_ssl, cancel_uri, allat_host, 443 );
    }else{
      $isEnc=checkEnc( $at_data );
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo( $at_data, allat_addr, cancel_uri, allat_host, 80 );
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }

  function CashRegReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,cashreg_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,cashreg_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }

  function CashAppReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,cashapp_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,cashapp_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }

  function CashCanReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,cashcan_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,cashcan_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }

  function EscrowChkReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,escrowchk_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,escrowchk_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }
  
  function EscrowRetReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,escrowret_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,escrowret_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }  
  
  function EscrowConfirmReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,escrowconfirm_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,escrowconfirm_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }  

  function CertRegReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,certreg_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,certreg_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }
  
  function CertCancelReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,certcancel_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,certcancel_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }      

  function C2CApprovalReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,c2c_approval_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,c2c_approval_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }

  function C2CCancellReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,c2c_cancel_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,c2c_cancel_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }

  function C2CSellerRegReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,c2c_sellerreg_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,c2c_sellerreg_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }
  
  function C2CProductRegReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,c2c_productreg_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,c2c_productreg_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }  

  function C2CBuyerChkReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,c2c_buyerchg_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,c2c_buyerchg_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }

  function C2CEscrowChkReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,c2c_escrowchk_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,c2c_escrowchk_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }

  function C2CEscrowConfirmReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,c2c_escrowconfirm_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,c2c_escrowconfirm_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }

  function C2CEsRejectCheckReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,c2c_esrejectcheck_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,c2c_esrejectcheck_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }

  function C2CExpressregReq($at_data,$ssl_flag){
    $ret_txt="reply_cd=0299\n";

    if( strcmp($ssl_flag,"SSL")==0 ){
      $ret_txt=SendRepo($at_data,allat_addr_ssl,c2c_expressreg_uri,allat_host,443);
    }else{
      $isEnc=checkEnc($at_data);
      if( $isEnc ){ //암호화 됨
        $ret_txt=SendRepo($at_data,allat_addr,c2c_expressreg_uri,allat_host,80);
      }else{
        return "reply_cd=0230\nreply_msg=암호화 오류\n";
      }
    }
    return $ret_txt;
  }

  function SendRepo($srp_data,$srp_addr,$srp_url,$srp_host,$srp_port){
    $ret_txt=SendReq($srp_data,$srp_addr,$srp_url,$srp_host,$srp_port);
    return $ret_txt;
  }

  function SendReq($req_data,$req_addr,$req_url,$req_host,$req_port){
    $resp_txt="reply_cd=0299\n";
    $dateNtime=date('YmdHis');
    $util_ver="&allat_opt_lang=".util_lang."&allat_opt_ver=".util_ver;
    $req_data=$req_data."&allat_apply_ymdhms=".$dateNtime;
    $send_data=$req_data.$util_ver;
    $at_sock = @fsockopen($req_addr,$req_port,$errno,$errstr);
    //warning message disable '@'
    if($at_sock){
      fwrite($at_sock, $req_url );
      fwrite($at_sock, "Host: ".$req_host.":".$req_port."\r\n" );
      fwrite($at_sock, "Content-type: application/x-www-form-urlencoded\r\n");
      fwrite($at_sock, "Content-length: ".strlen($send_data)."\r\n");
      fwrite($at_sock, "Accept: */*\r\n");
      fwrite($at_sock, "\r\n");
      fwrite($at_sock, $send_data."\r\n");
      fwrite($at_sock, "\r\n");
      $resp_txt=convertSock($at_sock);
    }else{
      $resp_txt="reply_cd=0212\n"."reply_msg=Socket Connect Error:".$errstr."\n";
    }
    return $resp_txt;
  }

  ///------------Get Return Value Function-------------
  function convertSock($csock){
    while(!feof($csock)){
      $headers=fgets($csock,4096);
      if($headers=="\r\n"){
        break;
      }
    }
    while(!feof($csock)){
      $bodys.=fgets($csock,4096);
    }
    $isError=getValue("reply_cd",$bodys);
    if($isError==""||$isError==null){
      $temp_msg=strip_tags($bodys);
      $re_msg=getValue("reply_msg",$bodys);
      $error_msg="reply_cd=0251\n"."reply_msg=".trim($re_msg).trim($temp_msg)."\n";
      return $error_msg;
    }else{
      return $bodys;
    }
  }

  ///------------Parse Return Value Function-------------
  function getValue($nameVal,$textVal){
      $temp = explode("\n",trim($textVal));
      for($i=0;$i<sizeof($temp);$i++){
          $retVal=explode("=",trim($temp[$i]));
          if( $retVal[0]== $nameVal ){
              $returnVal=$retVal[1];
          }
      }
      return $returnVal;
  }

  function checkEnc($srcstr){
    $posno=strpos($srcstr,"allat_enc_data=");

    if($posno === false){
        return false;
    }
    if(substr($srcstr,$posno+strlen("allat_enc_data=")+5,1)!="1"){
        return false;
    }
    return true;
  }

?>
