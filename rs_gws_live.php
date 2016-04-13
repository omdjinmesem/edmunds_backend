<?php

function rs_gws_live() {
  global $wpdb;
  //API KEY
  $key = 'kklsallasbn78asasadn';
  
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
        //echo $judul.'<hr/>'.$all_img.'</hr>'.$tanggal.'<hr/>'.$paragrafs.'<hr/>'.$keywords.'<hr/>'.$u;
        //exit();
        $saveit = $wpdb->query( $wpdb->prepare("INSERT INTO wp_gdata
          (gtitle,gimgs,gdate,gpar,gkwd,turl,gstatus)
          VALUES (%s,%s,%s,%s,%s,%s,%s)", 
          $judul,$all_img,$tanggal,$paragrafs,$keywords,$u,'grabbed'
        ));
        
        if($saveit !== false) {
          $status = true;
        } else {
          $status = false;

        }
        
      } elseif($type=='grab_chk') {
          $datachk = $wpdb->get_var( "SELECT COUNT(gid) FROM wp_gdata WHERE turl = '".$data."' " );
          if($datachk>0){
            $status = false;
          } else {
            $status = true;
          }
      } elseif($type=='graball') {
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
      } elseif($type=='apdetgrab') {
        $apdet = $wpdb->query("UPDATE wp_gurl SET gstatus = 'grabbed' WHERE turl = '".base64_decode($data)."' ");
        if($apdet!==false) {
          $status = true;
        } else {
          $status = false;
        }
      } elseif($type=='apdetskip') {
        $apdet = $wpdb->query("UPDATE wp_gurl SET gstatus = 'skipped' WHERE turl = '".$data."' ");
        if($apdet !== false) {
          $status = true;
        } else {
          $status = false;
        }
      } elseif($type=='pubstats') {
        $grabbed = $wpdb->get_var( "SELECT COUNT(gid) FROM wp_gurl WHERE gstatus='grabbed' " );
        $published = $wpdb->get_var( "SELECT COUNT(gid) FROM wp_gurl WHERE gstatus='publish' " );
        $gdata =  $wpdb->get_row( "SELECT * FROM wp_gdata WHERE gstatus = 'grabbed' LIMIT 1; " );
        $datasend = array(
          'grabbed' => $grabbed,
          'published' => $published,
          'gdata' => $gdata
        );
        echo json_encode($datasend);
        exit;
      } elseif($type=='multypublish') {
        $gdata = json_decode(base64_decode($data));
        
        //exit();
        $gid = $gdata->gid;
        $gtitle = $gdata->gtitle;
        $gimgs = $gdata->gimgs;
        $gdate = $gdata->gdate;
        $gpar = $gdata->gpar;
        $gkwd = $gdata->gkwd;
        $turl = $gdata->turl;

        // Konstruksi judul attachment
        $xkwd = explode(', ', $gkwd);
        $ximgs  = explode('[(.Y.)]', $gimgs);
        $gimgs = '';
        foreach($ximgs as $im){
          $imsrc = $im;
          $imtitle = str_replace(array('.jpg','jpeg','.gif','.png','_','-','+'),' ', basename($im));

          shuffle($xkwd);
        
          $imtitle = explode(' ns ', $imtitle);
          $imtitle = $imtitle[0] . ' ' . $xkwd[0] . ' ' . $xkwd[1] . ' ' . $xkwd[2] . ' ' . $xkwd[3];
          // unikkan kata yang dobel dobel
          $imtitle = implode(' ',array_unique(explode(' ', $imtitle)));

          $gimgs .= $imsrc.'[{|}]'.$imtitle.'[(.Y.)]';
          $i++;
        }
        $gimgs .= '';
        $gimgs = substr($gimgs, 0, -7);

        $gpar   = str_replace('[(.Y.)]',"\n\n", str_replace(array('[(.Y.)][(.Y.)]','[(.Y.)][(.Y.)][(.Y.)]','[(.Y.)][(.Y.)][(.Y.)][(.Y.)]','[(.Y.)][(.Y.)][(.Y.)][(.Y.)][(.Y.)]'),'[(.Y.)]',$gpar));
        
        $gpar = g13_rw($gpar).'[{|}]';
        $author = mt_rand(1,10);
        $basedate = date('Y-m-d H:i:s');
        #$basedate = $gdate; // sesuai tanggal aslinya.
        /* $plusday  = mt_rand(1,365); */
        $plusday  = 0; 
        $plushour = 0;
        $plusmin  = mt_rand(1,59);
        $plussec  = mt_rand(1,59); 
        $newdate  = date('Y-m-d H:i:s', strtotime('+'.$plusday.' day +'.$plushour.' hour +'.$plusmin.' minutes +'.$plussec.' seconds',''.strtotime($basedate).''));

        $my_post = array(
          'post_title'    => $gtitle,
          'post_name'    => sanitize_title_with_dashes($gtitle),
          'post_content'  => substr($gpar,0,-5),
          'post_excerpt'  => $gimgs,
          'post_status'   => 'draft',
          'post_author'   => $author,
          'post_date'         => $newdate,
          'post_date_gmt'     => $newdate,
          'post_modified'     => $newdate,
          'post_modified_gmt' => $newdate,
          'pinged'   => base64_encode($turl),
          'post_category' => array( 1 )
        );

        // Insert the post into the database
        $posid = wp_insert_post( $my_post );
        if(!empty($posid)) {
          $status = true;
        } else {
          $status = false;
        }
        /*echo $gid.'<hr/>';
        echo $gtitle.'<hr/>';
        echo $gimgs.'<hr/>';
        echo $gdate.'<hr/>';
        echo $gpar.'<hr/>';
        echo $gkwd.'<hr/>';
        echo $turl.'';
        exit();*/
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
  } elseif(isset($_POST['sedot'])) {
    
  } else {
    //echo 'z0nk';exit();
  }
}

if(!function_exists('pr')){
  function pr($arr){
    echo '<pre>';
    print_r($arr);
    echo '</pre>';
  }
}

function g13_rw($s) {
  $char = array("'",'"','-','—','_','+','*','(',')','{','}','<','>','[',']',':','=','%','~','$','|');
  $cent = array('&#8217;','&#8221;','&#8208;','&#8211;','&#95;','&#43;','&#42;','&#40;','&#41;','&#123;','&#125;','&#60;','&#62;','&#91;','&#93;','&#58;','&#61;','&#37;','&#8764;','&#36;','&#124;');
  $s = str_replace($char, $cent, $s);

  /*replace kata negatif*/
  $negatif = array('are not','can not','cannot','could not','did not','does not','do not','had not','has not','have not','is not','must not','shall not','should not','were not','will not','would not');
  $cent2 = array("aren&#8217;t","can&#8217;t","can&#8217;t","couldn&#8217;t","didn&#8217;t","doesn&#8217;t","don&#8217;t","hadn&#8217;t","hasn&#8217;t","haven&#8217;t","isn&#8217;t","mustn&#8217;t","shan&#8217;t","shouldn&#8217;t","weren&#8217;t","won&#8217;t","wouldn&#8217;t");
  $s = str_replace($negatif,$cent2,$s);
  /*replace prokem*/
  $prokem = array('want to','going to','kind of','sort of','got you','what are you','what have you','what do you','watch you','how do you do');
  $cent3 = array('wanna','gonna','kinda','sorta','gotcha','whatcha','whatcha','whatcha','watcha','howdy');
  $s = str_replace($prokem,$cent3,$s);


  $sambung = array('also','with','plus','together with','or','in&#8208;conjunction with');
  $xs = explode(' and ', $s);
  $i = 0;
  $ss = '';
  foreach($xs as $x) {
    if($i==(count($xs)-1)) {
      $ks = '';
    } else {
      if($i == count($sambung)) {
        $i=0;//reset counter
      } else {}
      $ks = ' '.$sambung[$i].' ';
    }
    $ss .= $x.$ks;
    $i++;
  }
  $s = $ss;
  return $s;
}
?>