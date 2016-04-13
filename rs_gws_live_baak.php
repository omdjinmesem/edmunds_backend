<?php

function rs_gws_live() {
  global $wpdb;
  //API KEY
  $key = 'kklsallasbn78asdn';
  
  if(isset($_REQUEST['sedot'])) {
    $rkey = $_REQUEST['key'];
    $type = $_REQUEST['type'];
    $data = $_REQUEST['data'];
    if($rkey == $key) {
      /*
      1. pengecekan type : akan membedakan action yang akan terjadi.
      2. action terjadi, memproses data.
      3. status action tersebut yang kita jawabkan
      
      */
      // krol save database
      if($type=='krol') {
        $savit = $wpdb->query($wpdb->prepare("INSERT INTO wp_gurl
          ( turl, gstatus )
          VALUES ( %s, %s )", 
          base64_decode($data), ''
        ));

        if($savit){
          $status = true;
        } else {
          $status = false;
        } // end if savit
      
      // krol existence check
      } elseif($type=='krol_chk') {
        $datachk = $wpdb->get_var( "SELECT COUNT(gid) FROM wp_gurl WHERE turl = '".base64_decode($data)."' " );
        if($datachk > 0) {
          $status = false;
        } else {
          $status = true;
        }

      // single grab save DB
      } elseif($type=='grab') {
        $data = json_decode(base64_decode($data));
        $judul = $data->t;
        $all_img = $data->i;
        $tanggal = $data->d;
        $paragrafs = $data->p;
        $keywords = $data->k;
        $u = $data->u;
        /*echo $judul.'<hr/>'.$all_img.'</hr>'.$tanggal.'<hr/>'.$paragrafs.'<hr/>'.$keywords.'<hr/>'.$u;
        exit();*/
        $saveit = $wpdb->query( $wpdb->prepare("INSERT INTO wp_gdata
          (gtitle,gimgs,gdate,gpar,gkwd,turl,gstatus)
          VALUES (%s,%s,%s,%s,%s,%s,%s)", 
          $judul,$all_img,$tanggal,$paragrafs,$keywords,$u,'grabbed'
        ));
        if($savit !== false) {
          $status = true;
        } else {
          $status = false;
        }

      } elseif($type=='grab_chk') {
        $datachk = $wpdb->get_var( "SELECT COUNT(gid) FROM wp_gdata WHERE turl = '".base64_decode($data)."' " );
        if($datachk>0){
          $status = false;
        }else{
          $status = true;
        }
      } elseif($type=='grab_update') {
        $data = json_decode(base64_decode($data));
        $gstatus = $data->gstatus;
        $turl = $data->turl;
        $apdet = $wpdb->query("UPDATE wp_gurl SET gstatus = '".$gstatus."' WHERE turl = '".$turl."' ");
        #echo "UPDATE wp_gurl SET gstatus = '".$gstatus."' WHERE turl = '".$turl."' ";exit();
        if($apdet) {
          $status = true;
        } else {
          $status = false;
        }
      } elseif($type=='graball_stats') {
        $alltarget = $wpdb->get_var( "SELECT COUNT(gid) FROM wp_gurl" );
        $allgrabbed = $wpdb->get_var( "SELECT COUNT(gid) FROM wp_gurl WHERE gstatus='grabbed' " );
        $allskipped = $wpdb->get_var( "SELECT COUNT(gid) FROM wp_gurl WHERE gstatus='skipped' " );
        $turl = $wpdb->get_var( "SELECT turl FROM wp_gurl WHERE gstatus='' LIMIT 1 " );
        $datasend = array(
          'alltarget'=>$alltarget, 
          'allgrabbed'=>$allgrabbed, 
          'allskipped'=>$allskipped,
          'turl'=>$turl
        );
        echo json_encode($datasend);
        exit();
      } elseif($type=='graball') {

      } else {
        $status = false;
      }
      
      if($status) {
        echo 'yH4';
      } else {
        echo 'z0nk';
      }
    } else {
      echo 'z0nk';
    }
    exit;
  } else {}
}

?>