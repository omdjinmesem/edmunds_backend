<?php
/*
PROSES : yeyeye
- Crawl
- Single grab
- Grab all
==============
- Publish Flow
- Attachment flow
*/
// 00. ADD OPTION
add_option('last_crawlurl', '', '', 'yes');
add_option('last_pub_url', '', '', 'yes');
add_option('apstatus', '', '', 'yes');

/* 01. TEXT DOMAIN*/
add_action('init', 'omj_grabdomain');
function omj_grabdomain() {
  load_plugin_textdomain('omj', false, 'omj');
}

//02. ICON PATH ------------------------------------------------------------------------
function omj_iconpath($dir) {
  $themepath = get_bloginfo('template_directory');
  $iconpath = $themepath.'/'.$dir;
  return $iconpath;
}

//03. BACKEND MENU
add_action('admin_menu','omj_grabmenu');
function omj_grabmenu(){
  /* 03.1 TOP MENU ------------------- */
  if(function_exists('add_menu_page')) {
    $grabtopmenu = add_menu_page(
      __('OMJ Grab Page', 'omj'), //Page Title, textdomain
      __('OMJ Grab', 'omj'), //Menu Label, textdomain
      'manage_options',  //Privilege
      'omj-grab',      //Slug
      'omj_grab_page', //Function Page <--
      omj_iconpath('grab/cool.png'), // 16x16
      '3' // posisi
    );
    add_action("admin_print_scripts-$grabtopmenu", 'omj_grab_style');
  } else {}
  /* 03.1 End of TOP MENU ------------------- */
  /* 03.2 SUB MENU ------------------- */
  if(function_exists('add_submenu_page')) {
    //01. CRAWL
    $omgcrawl = add_submenu_page(
      'omj-grab', //slug dari Top Menu
      __('OMJ Crawl Page', 'omj'), //Page Title, textdomain
      __('OMJ Crawl', 'omj'), //Menu Label, textdomain
      'manage_options',  //Privilege
      'omj-crawl',      //Slug
      'omj_crawl_page'  //Function Page
    );
    add_action("admin_print_scripts-$omgcrawl", 'omj_grab_style');

    //02. GRAB ALL
    $omgcrawl = add_submenu_page(
      'omj-grab', //slug dari Top Menu
      __('OMJ Grab All Page', 'omj'), //Page Title, textdomain
      __('OMJ Grab All', 'omj'), //Menu Label, textdomain
      'manage_options',  //Privilege
      'omj-graball',      //Slug
      'omj_graball_page'  //Function Page
    );
    add_action("admin_print_scripts-$omgcrawl", 'omj_grab_style');

    //03. PUBLISH FLOW
    $omgcrawl = add_submenu_page(
      'omj-grab', //slug dari Top Menu
      __('OMJ Publish Flow Page', 'omj'), //Page Title, textdomain
      __('OMJ Publish', 'omj'), //Menu Label, textdomain
      'manage_options',  //Privilege
      'omj-publishflow',      //Slug
      'omj_publishflow_page'  //Function Page
    );
    add_action("admin_print_scripts-$omgcrawl", 'omj_grab_style');

    //04. ATTACHMENT FLOW
    $omgcrawl = add_submenu_page(
      'omj-grab', //slug dari Top Menu
      __('OMJ Attachment Flow Page', 'omj'), //Page Title, textdomain
      __('OMJ Attachment', 'omj'), //Menu Label, textdomain
      'manage_options',  //Privilege
      'omj-attachmenflow',      //Slug
      'omj_attachmenflow_page'  //Function Page
    );
    add_action("admin_print_scripts-$omgcrawl", 'omj_grab_style');

    //05 //Sandbox
    $jskrol = add_submenu_page(
      'omj-grab', //slug dari Top Menu
      __('Sandbox', 'rg'), //Page Title, textdomain
      __('SANDBOX', 'rg'), //Menu Label, textdomain
      'manage_options',  //Privilege
      'omg_sandbox',      //Slug
      'omg_sandbox_page'  //Function Page
    );
    add_action("admin_print_scripts-$jskrol", 'omj_grab_style');
  } else {}
  /* 03.2 End of SUB MENU ------------------- */

}
//03. End of BACKEND MENU

/* GRAB PAGE ------------------------ */
function omj_grab_page()
{
  global $wpdb;
  $gurl = get_option('siteurl').'/wp-admin/admin.php?page=omj-grab';
  $gaurl = get_option('siteurl').'/wp-admin/admin.php?page=omj-graball';
  $base = 'http://neneners.ml/';
  echo '<div class="wrap">
  <h2>OMJ Grab</h2>
  <div class="inside">';
  /* 02. HANDLER */
  if(isset($_POST['gsubmit'])) {
    $issubmit = true;
    $u = $_POST['u'];
    $transit = false;
  } elseif(isset($_REQUEST['gsubmit'])) {
    $issubmit = true;
    $u = base64_decode($_REQUEST['u']);
    $transit = true;
  } else {
    $issubmit = false;
    $u = '';
    $transit = false;
  }
  /* 03. KONFIRMASI*/
  echo '<p>Target url : '.$u.'</p>';
  if($transit) {
    echo '<a href="'.$gaurl.'" class="button r">YAMETEH SENPAI..!</a>';
  } else {}
  /* 01. FORM */
  echo '<form action="'.$gurl.'" method="post">
    <input type="url" name="u" value="'.$u.'" required />
    <input type="submit" name="gsubmit" value="Grab It!" class="button-primary"/>
  </form>';

  /* 04. EXECUTE */
  if($issubmit) {
    ini_set('display_errors', 0);
    echo '<div class="res">';
    // INCLUDE USER AGENT
    include(TEMPLATEPATH.'/grab/ua.php');
    ini_set('user_agent',$theua);
    $opts    = array('http'=>array('method'=>"GET",'header'=>"Accept-language: en-us,en\r\n"));
    $context = stream_context_create($opts);
    $grabit  = file_get_contents($u,false,$context);

    if($grabit === FALSE){
      if($transit){
        $blurl = $gaurl.'&grsubmit=true';
      }else{
        sleep(3);
        echo '<form action="'.$gurl.'" method="post" id="gs">
          <input type="hidden" name="u" value="'.$u.'" required />
          <input type="hidden" name="gsubmit" value="Grab It!" class="button-primary"/>
        </form>';
        echo '<script>document.forms["gs"].submit();</script>';
        exit();
      }
      echo '<h2 class="rd">ERROR file_get_contents, Koneksi ambyar!</h2>';
      echo 'Sedang mencoba lagi..';
      echo '<meta http-equiv="refresh" content="3; url='.$blurl.'" />';
      exit();
    }else{
      
    } //end $grabbit === false
    /* MINIFIKASI */
    $grabit = str_replace(array("\n","\r","\t"),'',$grabit);
    $grabit = str_replace(array('      ','     ','    ','   ','  '),' ',$grabit);
    $grabit = str_replace('> <','><',$grabit);

    #$grabit = str_replace('> <','><',str_replace(array('      ','     ','    ','   ','  '),' ',str_replace(array("\n","\r","\t"),'',$grabit)));

    /* Kill Script */
    $grabit = str_replace('<script','<!--<script',$grabit);
    $grabit = str_replace('/script>','/script>-->',$grabit);


    /*DO TITLE -----------------*/
    // PEMBUKA : <h1 class="title header-1"
    // PENUTUP : </h1><hr class="loose-dotted"/>
    
    $dotitle = $grabit; // 01. Clone
    $dotitle = str_replace('<h1 class="title header-1"','<code><h1 class="title header-1"',$dotitle); // 02. Buka
    $dotitle = str_replace('</h1><hr class="loose-dotted"/>','</h1></code><hr class="loose-dotted"/>',$dotitle); // 02. Tutup

    $domtitle = new domDocument();

    @$domtitle->loadHTML($dotitle);
    $codetitle = $domtitle->getElementsByTagName('code');
    
    
    foreach($codetitle as $ttl) {
      $judul = $ttl->nodeValue;
      #echo $judul;
    }
    

    /* PR : Ambil isi dari class "byline" */
    
    // PEMBUKA : <p class="byline"
    // PENUTUP : </p><div class="social-toolbar tid"
    $dobyline = $grabit;
    $dobyline = str_replace('<p class="byline">', '<p class="byline"><code>', $dobyline);
    $dobyline = str_replace('</p><div class="social-toolbar tid"', '</code></p><div class="social-toolbar tid"', $dobyline);
    
    $dombyline = new domDocument();
    @$dombyline->loadHTML($dobyline);
    $codebyline = $dombyline->getElementsByTagName('code');
    $array = array();
    foreach($codebyline as $p){
      $line = $p->nodeValue;
    }
    $x1 = explode('|',str_replace(array(' - by',', ',': '),'|',$line));
    $tanggal = substr($x1[1],0,10);
    $tanggal = date('Y-m-d H:i:s',strtotime($tanggal));
    $nama = $x1[2];
    $jabatan = $x1[3];
    
    /*DO PARAGRAFs -----------------*/
    //PEMBUKA : <div class="content grid-138"><div class="article loose-spacing">
    //PENUTUP : </div><div class="article-footer">
    $dopar = $grabit;
    #echo $dopar;
    $dopar = str_replace('<div class="content grid-138"><div class="article loose-spacing">','<div class="content grid-138"><div class="article loose-spacing"><code>',$dopar);
    $dopar = str_replace('</div><div class="article-footer">','</code></div><div class="article-footer">',$dopar);
    #echo $dopar;
    $dompar = new domDocument();
    @$dompar->loadHTML($dopar);
    $codepar = $dompar->getElementsByTagName('code');
    $paragrafs = '';
    foreach($codepar as $code) {
      $listpar = $code->getElementsByTagName('p');
      foreach($listpar as $par) {
        $paragrafs .= $par->nodeValue.'[(.Y.)]';
      }
    }
    $paragrafs .= '';
    $paragrafs = substr($paragrafs, 0, -7);
    /* DO Gallery */
    $url_json = str_replace(array('http://www.edmunds.com','.html'),array('http://www.edmunds.com/editorial/rest/damrepository/findbycontent/?path=','&fullscreen=1920&isarticle=true&cdDate=null'),$u);
    $jsonimg = file_get_contents($url_json,false,$context);
    $arr_json = json_decode($jsonimg);
    $all_img = '';
    if(isset($arr_json->photos) && !empty($arr_json->photos)){
      foreach($arr_json->photos as $photos) {
        $all_img .= $photos->fullscreen.'[(.Y.)]';
      }
      $all_img .= '';
      $all_img = substr($all_img, 0, -7);
    }
      
    /* DO Keywords */
    $dokwd = $grabit;
    $dokwd = str_replace('<meta name="news_keywords"', '<preketek name="news_keywords"', $dokwd);
    $domkwd = new domDocument();
    @$domkwd->loadHTML($dokwd);
    $preketek = $domkwd->getElementsByTagName('preketek');
    foreach($preketek as $metakwd) {
      $keywords = $metakwd->getAttribute('content');
    }

    /* Data Empty Check */
    if(empty($paragrafs) || empty($all_img)) {
      $apdet = $wpdb->query("UPDATE wp_gurl SET gstatus = 'skipped' WHERE turl = '".$u."' ");
      if($apdet !== false) {
        echo '<p class="rd">Paragraf ada yang empty, atau gambar ada yang kosong. Skip dulu.</p>';
        echo '<meta http-equiv="refresh" content="3; url='.$gaurl.'&grsubmit=true" />';
      } else {
        echo '<p class="rd">Update status skipped gagal untuk url :'.$u.'</p>';
      }
      exit();
    } else {}
    /* DATA EXISTENCE CHECK db SERVER */
    $datachk = rs_gws_local($base, $u, 'grab_chk');
    if($datachk == 'z0nk'){
      echo '<p class="rd">Data sudah ada</p>';
    }else{
      /* SAVE DATA LIVE */
      $datalive = array(
        "t" => $judul,
        "i" => $all_img,
        "d" => $tanggal,
        "p" => $paragrafs,
        "k" => $keywords,
        "u" => $u
        );
      $jdata = base64_encode(json_encode($datalive));
      $saveit = rs_gws_local($base,$jdata,'grab');
      if($saveit=='yH4') {
        echo '<p class="gr">Data sukses tersimpan.</p>';
        /*Update Status*/
        $apdet = rs_gws_local($base,base64_encode($u),'apdetgrab');
        if($apdet=='yH4') {
          // transisi di sini
          // cek transisi.
          if($transit){
            $reload = mt_rand(1,2); //jika live, ganti jadi 5,15
            $requrl = $gaurl.'&grsubmit=true';
            
            echo '<style>#jload {width:100%;height:5px; padding:0; margin:10px auto; background:#ddd;position:relative;}.expand {width:100%;height:5px; margin:0; background:#390; position:absolute;box-shadow:0 1px 0 0 rgba(255,255,255,1.0); -moz-animation:fullexpand '.$reload.'s ease-out;-webkit-animation:fullexpand '.$reload.'s ease-out;}@-moz-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}@-webkit-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}</style>';
            echo '<div id="lw"><div id="jload"><span class="expand"></span></div></div>';
            echo '<meta http-equiv="refresh" content="'.$reload.'; url='.$requrl.'" />';
            
          } else {}
        } else {
          echo '<p class="rd">Update status gurl gagal!</p>';
        }

      } else {
        echo '<p class="rd">Gagal insert ke table wp_gdata..!</p>';
      }
    } 
    echo '</div>';
  } else {}
  
  echo '</div>
  </div>';
}
/* End of GRAB PAGE ------------------------ */

/* CRAWL PAGE ------------------------------ */
function omj_crawl_page()
{
  global $wpdb;
  $crawlurl = get_option('siteurl').'/wp-admin/admin.php?page=omj-crawl';
  $last_crawlurl = get_option('last_crawlurl');
  $base = 'http://neneners.ml/';
  echo '<div class="wrap">
  <h2>OMJ Crawl</h2>
  <div class="inside">';
  /* PR
  CRAWL
  1. Target : http://www.edmunds.com/car-news/sitemap.html
  2. Bikin Form
  3. Ambil attribute href (50 Url)
   */
  /* 02. HANDLER */
  if(isset($_POST['csubmit'])) {
    $issubmit = true;
    $u = $_POST['u'];
  } elseif(isset($_REQUEST['rsubmit'])) {
    $issubmit = true;
    $u = base64_decode($_REQUEST['u']);
  } else {
    $issubmit = false;
    $u = '';
  }
  /* 03. KONFIRMASI*/
  echo '<p>Target url : '.(empty($u)?'http://www.edmunds.com/car-news/sitemap-pg1.html':$u).'</p>';
  /* 01. FORM */
  echo '<form action="'.$crawlurl.'" method="post">
    <input type="url" name="u" value="'.$u.'" required />
    <input type="submit" name="csubmit" value="Crawl It!" class="button-primary"/>
    <a href="'.$crawlurl.'" class="button">Stop</a>
    <a href="'.$last_crawlurl.'" class="button-primary">Resume</a>
  </form>';
  /* 04. EXECUTE */
  if($issubmit){
    echo '<div class="res">';
    // INCLUDE USER AGENT
    include(TEMPLATEPATH.'/grab/ua.php');
    ini_set('user_agent',$theua);
    $opts    = array('http'=>array('method'=>"GET",'header'=>"Accept-language: en-us,en\r\n"));
    $context = stream_context_create($opts);
    $grabit  = file_get_contents($u,false,$context);
    /* MINIFIKASI */
    $grabit = str_replace('> <','><',str_replace(array('      ','     ','    ','   ','  '),' ',str_replace(array("\n","\r","\t"),'',$grabit)));
    /* Kill Script */
    $grabit = str_replace('<script','<!--<script',$grabit);
    $grabit = str_replace('/script>','/script>-->',$grabit);
    $dolinks = $grabit;
    $dolinks = str_replace('<div class="content gutter-top-3">','<div class="content gutter-top-3"><code>',$dolinks); // 02. Buka
    $dolinks = str_replace('<div class="yui-dt-paginator yui-pg-container">','</code><div class="yui-dt-paginator yui-pg-container">',$dolinks); // 02. Tutup

    $domlinks = new domDocument();
    @$domlinks->loadHTML($dolinks);
    $codelinks = $domlinks->getElementsByTagName('code');
    foreach($codelinks as $nodecode) {
      $nodelinks = $nodecode->getElementsByTagName('a');
      foreach($nodelinks as $ndlink) {
        $linkUrl = 'http://www.edmunds.com' . $ndlink->getAttribute('href');
        /* Data existance check */
        $datachk = rs_gws_local($base, base64_encode($linkUrl), 'krol_chk');
        if($datachk == 'z0nk'){
          echo '<span class="rd">Link : ' . $linkUrl . ' already exists.</span><br/>';
        }else{
          /* SAVE DATABASE LIVE */
          $savit = rs_gws_local($base, base64_encode($linkUrl), 'krol');
          if($savit == 'yH4'){
            echo '<span class="gr">Link : ' . $linkUrl . ' successfully inserterd</span><br/>';
          } else {
            $err .= "1";
            echo '<span class="rd">Link : ' . $linkUrl . ' gagal bro :(</span><br/>';
          } // end if savit
        } // end datachk
      }
    }
    //exit;
    #echo $grabit;
    if(empty($err)){
      /* TRANSISI */
      if(strpos($grabit,'<a class="yui-pg-next"')!==false) {
        if(strpos($u,'-pg')!==false) {
          $hal = str_replace(array('http://www.edmunds.com/car-news/sitemap-pg','.html'),'',$u);
        } else {
          $hal = 1;
        }
        $nexturl = 'http://www.edmunds.com/car-news/sitemap-pg'.($hal+1).'.html';
        $reload = mt_rand(1,3); //jika live, ganti jadi 5,15
        $requrl = $crawlurl.'&rsubmit=true&u='.base64_encode($nexturl);
        update_option('last_crawlurl',$requrl);
        echo '<style>#jload {width:100%;height:5px; padding:0; margin:10px auto; background:#ddd;position:relative;}.expand {width:100%;height:5px; margin:0; background:#390; position:absolute;box-shadow:0 1px 0 0 rgba(255,255,255,1.0); -moz-animation:fullexpand '.$reload.'s ease-out;-webkit-animation:fullexpand '.$reload.'s ease-out;}@-moz-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}@-webkit-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}</style>';
        echo '<div id="lw"><div id="jload"><span class="expand"></span></div></div>';
        echo '<meta http-equiv="refresh" content="'.$reload.'; url='.$requrl.'" />';
      } else {
        echo '<h2 class="gr">Crawling finish!</h2>';
        
      }
    }else{
      echo '<p class="rd">Ngulang, karena ada yg gagal insert.</p>';
      $nexturl = 'http://www.edmunds.com/car-news/sitemap-pg'.($hal).'.html';
      $reload = mt_rand(1,2); //jika live, ganti jadi 5,15
      $requrl = $crawlurl.'&rsubmit=true&u='.base64_encode($nexturl);
      echo '<style>#jload {width:100%;height:5px; padding:0; margin:10px auto; background:#ddd;position:relative;}.expand {width:100%;height:5px; margin:0; background:#390; position:absolute;box-shadow:0 1px 0 0 rgba(255,255,255,1.0); -moz-animation:fullexpand '.$reload.'s ease-out;-webkit-animation:fullexpand '.$reload.'s ease-out;}@-moz-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}@-webkit-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}</style>';
      echo '<div id="lw"><div id="jload"><span class="expand"></span></div></div>';
      echo '<meta http-equiv="refresh" content="'.$reload.'; url='.$requrl.'" />';
    }
      
    echo '</div>'; //penutup div .res
  } else {}

  echo '</div>
</div>';
}
/* End of CRAWL PAGE ------------------------------ */

/* GRAB ALL PAGE ------------------------------ */
function omj_graball_page(){
  global $wpdb;
  $base = 'http://neneners.ml/';
  $grall = get_option('siteurl').'/wp-admin/admin.php?page=omj-graball';
  echo '<div class="wrap">
  <h2>Grab All</h2>
  <div class="inside">';
  /*01. Statistik */
  $res_stats = json_decode(rs_gws_local($base,'','graball'));

  $alltarget = $res_stats->alltarget;
  $allgrabbed = $res_stats->allgrabbed;
  $allskipped = $res_stats->allskipped;
  $turl = $res_stats->turl;
  
  echo '<p><label>All Target</label> : '.number_format($alltarget).'<br/>';
  echo '<label>All Grabbed</label> : '.number_format($allgrabbed).'<br/>';
  echo '<label>All Skipped</label> : '.number_format($allskipped).'<br/>';
  echo '<label>Remaining</label> : '.number_format($alltarget-$allgrabbed-$allskipped).'<br/>';
  echo '<label>Currently Processing</label> : '.$turl.'</p>';
  /*END of Statistik*/

  if(empty($turl)) {
    echo '<h3 class="gr">No more turl!</h3>';
  } else {
    /* EXECUTE di sini */
    if(isset($_REQUEST['grsubmit'])) {
      $issubmit = true;
    } else {
      $issubmit = false;
    }
    if($issubmit) {
      /*Transisi ke singlegrab di sini.*/
      /* NEXT : 
      - Tombol pause
      - benerin proses loading sing metune ning nduwur.
      */
      $requrl = get_option('siteurl').'/wp-admin/admin.php?page=omj-grab&gsubmit=true&u='.base64_encode($turl).'';
      echo '<p>Grabbing now...</p>';
      echo '<meta http-equiv="refresh" content="1; url='.$requrl.'" />';
    } else {
      echo '<p><a class="button" href="'.$grall.'&grsubmit=true">Grabb All!</a></p>';
    }
  }
  echo '</div>
</div>';
}
/* End of GRAB ALL PAGE ------------------------------ */


/* PUBLISH FLOW PAGE ------------------------------ */
function omj_publishflow_page(){
  global $wpdb;
  $base = 'http://neneners.ml/';
  $puburl = get_option('siteurl').'/wp-admin/admin.php?page=omj-publishflow';
  echo '<div class="wrap">
  <h2>Publish Flow</h2>
  <div class="inside">';
  // handler Auto Publish Setting
  if(isset($_REQUEST['apset'])) {
    $du = $_REQUEST['du'];
    if($du=='yes') {
      $apval = 'yes';
    } else {
      $apval = '';
    }
    update_option('apstatus',$apval);
    echo '<p>Updating Auto Publish Status...</p>';
    echo '<meta http-equiv="refresh" content="2; url='.$puburl.'" />';
    exit;
  } else {}
  //pubstats
  $pubstats = json_decode(rs_gws_local($base,'','pubstats'));

  $grabbed = $pubstats->grabbed;
  $published = $pubstats->published;
  $gdata =  base64_encode(json_encode($pubstats->gdata));
  /*pr($gdata);
  exit();*/

  $apstatus = get_option('apstatus');
  $baselist = array(
    'http://neneners.ml/',
    'http://sedot.neneners.ml/'
    //'http://oldcars.website/',
    //'http://iza.fbretro.com/',
    //'http://media.fbretro.com/'
    );
  foreach($baselist as $base2) {
    $responpub = rs_gws_local($base2,$gdata,'multypublish');
    echo $base2.'<br/>';
    echo $responpub;
    echo '<hr/>====================3';
    /*if($responpub=='yH4') {
      echo '<p class="gr">Publish ke '.$base2.' sukses!</p>';
    } else {
      echo '<p class="rd">Publish ke '.$base2.' gagal!</p>';
    }*/
    echo '<hr/>';
  }
  /*
  Catatan :
  - Semua site yang butuh dipublishkan harus siap dan di-list-kan
  - Bikinkan gws local untuk publish (handling nya mirip kaya di grab)
  ================
  LIVE :
  - Konstruksi title post
  - Konstruksi title attachments
  - Penambahan keyword
  - Publishing

  NEXT :
  - Bikin standalone untuk pencarian keyword per target.

  */
  echo '</div>
</div>';
}
/* End of PUBLISH FLOW PAGE ------------------------------ */

/* ATTACHMENT FLOW PAGE ------------------------------ */
function omj_attachmenflow_page(){
  global $wpdb;
  ini_set('display_errors', 1);
  $aturl = get_option('siteurl').'/wp-admin/admin.php?page=omj-attachmenflow';
  $last_pub_url = get_option('last_pub_url');
  echo '<div class="wrap">
  <h2>Attachment Flow</h2>
  <div class="inside">';

  // handler Auto Publish Setting
  if(isset($_REQUEST['apset'])) {
    $du = $_REQUEST['du'];
    if($du=='yes') {
      $apval = 'yes';
    } else {
      $apval = '';
    }
    update_option('apstatus',$apval);
    echo '<p>Updating Auto Publish Status...</p>';
    echo '<meta http-equiv="refresh" content="1; url='.$aturl.'" />';
    exit;
  } else {}

  /* 01. HANDLER */
  if(isset($_REQUEST['atflow'])) {
    $pos = get_post($_REQUEST['posid']);
    //http://localhost/intra13/wp-admin/admin.php?page=omj-attachmenflow&atflow=true&posid=14&atidx=0
    $posid = $_REQUEST['posid'];
    $ptitle = $pos->post_title;
    $pdate = $pos->post_date;
    $turl = base64_decode($pos->pinged);
    $tags = get_the_tags($posid);
    $n=1;
    $kwd = '';
    foreach($tags as $tag) {
      if($n>15) {
        break;
      } else {
        $kwd .= $tag->name.' ';
      }
      $n++;
    }
    $kwd .= '';
    $kwd = substr($kwd,0,-1);

    $xatt = explode('[(.Y.)]', $pos->post_excerpt);
    $atidx = $_REQUEST['atidx'];
    $att = explode('[{|}]',$xatt[$atidx]);
    $atitle = $att[1];
    $atsrc = $att[0];

    // END OF ATTACHMENT FLOW
    if($atidx>(count($xatt)-1)) {
      $wpdb->query("UPDATE $wpdb->posts SET 
        post_status = 'publish', 
        post_excerpt = '' 
        WHERE ID = $posid");
      $wpdb->query("UPDATE wp_gdata SET 
        gstatus = 'publish'
        WHERE turl = '".$turl."' ");
      $wpdb->query("UPDATE wp_gurl SET 
        gstatus = 'publish'
        WHERE turl = '".$turl."' ");
      update_option('last_pub_url','');
      //transisi ke Publish flow
      $puburl = get_option('siteurl').'/wp-admin/admin.php?page=omj-publishflow';
      echo '<meta http-equiv="refresh" content="2; url='.$puburl.'" />';
      exit;
    } else {}
    $requrl = $aturl.'&atflow=true&posid='.$posid.'&atidx='.($atidx);
    update_option('last_pub_url', $requrl);
    $chlimit = 170; // limit karakter untuk filename, exclude tambahan untuk thumbnailnya -nnnnxnnnn 
    $exts = array('jpg','jpeg','png','gif');
    $ext = end(explode('.', basename($atsrc)));
    if(in_array(strtolower($ext), $exts)) {
      $ext = 'jpg';
    } else {}
    $mtype = 'image/'.$ext;
    $newfile = substr(sanitize_title_with_dashes(remove_accents(implode(' ',array_unique(explode(' ', $atitle.' '.$kwd))))),0,$chlimit).'.'.$ext;

    $updir = wp_upload_dir();
    $guid = $updir['url'].'/'.$newfile;
    $filename = $updir['path'].'/'.$newfile;

    echo '<p>Statistik:</p>';
    echo '<p><label>Attachments of </label> : '.$ptitle.'<br/>
    <label>Title</label> : '.$atitle.'<br/>
    <label>URL </label> : '.$atsrc.'<br/>
    <label>SLUG </label> : '.$newfile.' -> '.strlen($newfile).'<br/>
    <label>Extension </label> : '.$ext.'<br/>
    <label>Curently processing</label> : '.($atidx+1).' of '.count($xatt).'</p>';

    /* MANIPULASI GAMBAR TERJADI DI SINI */
    $sizes = array('1280x1024','1280x800','1280x768','1280x720','1360x768','1366x768','1440x900','1680x1050','1920x1080','1920x1200','2560x1440','2560x1600','3840x2160');
    $atsrcf = 'http://swiftype2.imgix.net/'.$atsrc.'?flip=h&vib=20';
    $savim = js_getimages($newfile, $atsrcf, 'http://edmunds.com');
    if(!$savim) {
      echo '<p class="rd">Gagal menyimpan gambar.</p>';
      echo '<a class="button" href="'.$aturl.'&apset=true&du=no">Deactivate Auto Publish</a>';
      echo '<meta http-equiv="refresh" content="'.mt_rand(3,5).'; url='.$aturl.'&atflow=true&posid='.$posid.'&atidx='.$atidx.'" />';
    } else {
      $basedate = $pdate; // sesuai tanggal postnya (parent).
      $plusday  = 0; 
      $plushour = 0;
      $plusmin  = mt_rand(1,10);
      $plussec  = mt_rand(1,59);
      $newdate  = date('Y-m-d H:i:s', strtotime('+'.$plusday.' day +'.$plushour.' hour +'.$plusmin.' minutes +'.$plussec.' seconds',''.strtotime($basedate).''));

      $attachment = array(
        'guid'           => $guid, 
        'post_mime_type' => $mtype,
        'post_title'     => $atitle,
        'post_content'   => '',
        'post_date'         => $newdate,
        'post_date_gmt'     => $newdate,
        'post_modified'     => $newdate,
        'post_modified_gmt' => $newdate,
        'post_status'    => 'inherit'
      );

      
      // Insert the attachment.
      $attach_id = wp_insert_attachment( $attachment, $filename, $posid );

      // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
      require_once( ABSPATH . 'wp-admin/includes/image.php' );

      // Generate the metadata for the attachment, and update the database record.
      $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
      wp_update_attachment_metadata( $attach_id, $attach_data );
      if(empty($attach_id)) {
        $ngg = '1';
        echo '<p class="rd">Insert attachment gagal!</p>';
        echo '<a class="button" href="'.$aturl.'&apset=true&du=no">Deactivate Auto Publish</a>';
        $wpdb->show_errors();
        $wpdb->print_error();
      } else {
        echo '<p class="gr">Insert attachment berhasil..</p>';
        //create multisize images
        $ngg = '';
        foreach($sizes as $size) {
          $xs = explode('x', $size);
          $sw = $xs[0];
          $sh = $xs[1];
          $msrc = 'http://hackpad.imgix.net/'.$atsrc.'?flip=h&vib=100&w='.$sw.'&h='.$sh.'&fit=crop';

          $nfile = str_replace('.'.$ext,'-'.$size.'.'.$ext,$newfile);
          $nsavim = js_getimages($nfile, $msrc, 'http://edmunds.com');
          if(!$nsavim) {
            echo '<p class="rd">Save image size '.$size.' gagal.</p>';
            $ngg .= '1';
          }
        }
      }
      if(!empty($ngg)) {
        print '<p class="rd">Ada proses yang gagal!</p>';
        //skip aja.
        $requrl = $aturl.'&atflow=true&posid='.$posid.'&atidx='.($atidx+1);
        #update_option('last_pub_url', $requrl);
        echo '<a class="button" href="'.$aturl.'&apset=true&du=no">Deactivate Auto Publish</a>';
        echo '<meta http-equiv="refresh" content="'.mt_rand(3,5).'; url='.$requrl.'" />';
      } else {
        print '<p class="gr">Lanjut attachment selanjutnya..</p>';
        $requrl = $aturl.'&atflow=true&posid='.$posid.'&atidx='.($atidx+1);
        #update_option('last_pub_url', $requrl);
        echo '<a class="button" href="'.$aturl.'&apset=true&du=no">Deactivate Auto Publish</a>';
        echo '<meta http-equiv="refresh" content="'.mt_rand(3,5).'; url='.$requrl.'" />';
      }
    }
  } else {
    if(!empty($last_pub_url)) {
      echo '<a class="button-primary" href="'.$last_pub_url.'">RESUME</a>';
    } else {
      echo '<p class="rd">Mau ngapain, mz..??</p>';
    }
    echo '<a class="button" href="'.$aturl.'&apset=true&du=no">Deactivate Auto Publish</a>';
  }
  echo '</div>
</div>';
}
/* End of ATTACHMENT FLOW PAGE ------------------------------ */

// START Sandbox  -----------------------------
function omg_sandbox_page()
{
  echo '<div class="wrap">
  <h2>Sandbox</h2>
  <div class="inside">';
  $base = 'http://webbiz99.com/';
  $data = 'test data';
  $type = 'krol';
  rs_gws_local($base, $data, $type);



  /* 
    Jika 1 kata maka exit 
    jika kata lebih dari 8 maka no js_ks
  $gkwd = 'Car Lambhorgini, Alphard';
  $xkwd = explode(', ', $gkwd);
  $kwds = '';
  foreach($xkwd as $kw){
    $r1 = js_ks($kw);
    $kwds .= implode(',', $r1) . ', ';
    foreach($r1 as $kw1) {
      $kwds .= implode(',', js_ks($kw1)) . ', ';
    }
  }
  $kwds .= '';
  $kwds = substr($kwds, 0, -1);
  $kwds = implode(', ', array_unique(explode(', ', $kwds)));
  echo $kwds;
  */

  echo '</div>
</div>';
}
//05. SANDBOX PAGE ----------------------------------------------------------------------------------

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
  /*print count($char).'-'.count($cent).'<br/>';
  $master = array('1'=>'a','2'=>'b','3'=>'c');
  echo 'Master : ';pr($master);print '<hr/>';
  $angka = array_keys($master);
  echo 'Angka : ';pr($angka);print '<hr/>';
  $huruf = array_values($master);
  echo 'Huruf : ';pr($huruf);print '<hr/>';*/
  return $s;
}

/* START GRAB STYLE*/
function omj_grab_style(){
  echo '<style type="text/css">
.gr {color:#390;}
.rd {color:#900;}
hr {height:1px; border:0 none; background:#ddd; color:#ddd;}
.inside {background:#f9f9f9; border:1px solid #ddd; width:97%; padding:15px;-moz-border-radius:3px;-webit-border-radius:3px;-khtml-border-radius:3px; border-radius:3px; margin-top:10px;}
textarea,input[type="text"]{width:600px;}
label{float:left;width:130px;}
select{width:150px;}
form,p {margin:0 0 15px 0;}
#lw {position:absolute; width:100%; height:5px; top:0;}
.res {position:relative; padding-top:30px;}
.r {float:right;}
#ap{overflow:hidden;padding:0;height:0;}
</style>';
}

/* JS KS
** Simple Keyword Suggestion
** v1:02 23:45 02/02/2016 
*/
function js_ks($s) {
  $q = array();
  include('ua.php');
  ini_set('user_agent',$theua); $d = file_get_contents('http://suggestqueries.google.com/complete/search?output=firefox&client=firefox&hl=en-US&q='.urlencode($s),false,stream_context_create(array('http'=>array('method'=>"GET",'header'=>"Accept-language: en-us,en\r\n"))));
  if (($d = json_decode($d,true)) !== null) {$q = $d[1];}
  return $q;
}

/*
Parameter
  - baseurl : berisi website live
  - data : isi yang akan dikirimkan
  - type : pembeda antara proses satu dengan yang lain
*/
function rs_gws_local($base, $data, $type) {
  $key = 'kklsallasbn78asasadn';
  if($type=='grab' || $type=='multypublish') {
    $qurl = $base.'?sedot=true&key='.$key.'&type='.$type;
    $datasend = array(
      'data' => $data
      );
    include(TEMPLATEPATH.'/grab/ua.php');
    ini_set('user_agent',$theua);
    $opts    = array(
      'http'=>array(
        'method'=>"POST",
        'header'=>"Content-type: application/x-www-form-urlencoded",
        'content' => http_build_query($datasend)
      )
    );
    $context = stream_context_create($opts);
    $sendit  = file_get_contents($qurl,false,$context);

  } else {
    $qurl = $base.'?sedot=true&key='.$key.'&type='.$type.'&data='.$data;

    include(TEMPLATEPATH.'/grab/ua.php');
    ini_set('user_agent',$theua);
    $opts    = array('http'=>array('method'=>"GET",'header'=>"Accept-language: en-us,en\r\n"));
    $context = stream_context_create($opts);
    $sendit  = file_get_contents($qurl,false,$context);
  }
  return $sendit;
}

/* CONTEKAN -------------------------
echo '<div class="wrap">
  <h2>Nama Halaman</h2>
  <div class="inside">';
$baselist = array(
    'http://neneners.ml/',
    'http://iza.fbretro.com/',
    'http://media.fbretro.com/',
    'http://ping.fiwe.co/',
    'http://oldcars.website/',
    'http://carsphoto.website/'
    );
  echo '</div>
</div>';


########### kw suggestion #############
  $gkwd = '2017 Honda Ridgeline, Truck-Bed Audio System, Super Bowl Commercial';
  $xkwd = explode(', ', $gkwd);
  $kwds = '';
  foreach($xkwd as $kw) {
    $r1 = js_ks($kw);
    $kwds .= implode(',',$r1).',';
    #echo implode(',',$r1).'<hr/>';
    foreach($r1 as $kw1) {
      $kwds .= implode(',',js_ks($kw1)).',';
      #echo implode(',',$r2).'<hr/>';
    }
  }
  $kwds .= '';
  $kwds = substr($kwds,0,-1);
  $kwds = implode(',',array_unique(explode(',',$kwds)));

  echo $kwds.'<hr/>';
-------------------------------------- */
if(!function_exists('pr')){
  function pr($arr){
    echo '<pre>';
    print_r($arr);
    echo '</pre>';
  }
}

/* JS Get Images v3.1b
** Created: 1:23 19/06/2015
$f : string. Nama file jadi. 
$u : string. url asli image.
$r : string. url referrer
*/
function js_getimages($f,$u,$r) {
  $updir = wp_upload_dir();
  $d = $updir['path'];
  include('ua.php');
  //Set Headers
  $hdrs = array(
    "HTTP_FORWARDED: ".$_SERVER['REMOTE_ADDR']."",
    "HTTP_X_FORWARDED_FOR: ".$_SERVER['REMOTE_ADDR']."",
    "HTTP_CLIENT_IP: ".$_SERVER['REMOTE_ADDR']."",
    "HTTP_VIA: ".$_SERVER['REMOTE_ADDR']."",
    "HTTP_XROXY_CONNECTION: ".$_SERVER['REMOTE_ADDR']."",
    "HTTP_PROXY_CONNECTION: ".$_SERVER['REMOTE_ADDR']."",
    "Accept: text/xml,text/html,application/xhtml+xml,application/xml;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5", 
    "Accept-Language: en-us,en", 
    "Cache-Control: max-age=0", 
    "Connection: keep-alive", 
    "Keep-Alive: 300", 
    "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7", 
    "Pragma: "
  );
  //Init Curl
  $c = curl_init();
  //Set Curl Options
  curl_setopt($c, CURLOPT_URL, $u);
  curl_setopt($c, CURLOPT_USERAGENT, $theua);
  curl_setopt($c, CURLOPT_HTTPHEADER, $hdrs);
  if(!empty($r)) {
    curl_setopt($c, CURLOPT_REFERER, $r);
  }
  curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 3);
  //Execute & Return
  $g = curl_exec($c);
  if(curl_errno($c)){
    echo 'Curl error: ' . curl_error($c);
  }
  //Close Curl
  curl_close($c);
  if(empty($g) || strpos($g,'<meta ') !== false || strpos($g,'<html') !== false || strpos($g,'<!DOCTYPE ') !== false || strpos($g,'<!doctype ') !== false) {
    
    return false;
  } else {
    if (false === $g){
      return false;
    } else {
      $put = file_put_contents($d.'/'.$f,$g);
      if($put !== false) {
        return true;
      } else {
        return false;
      }
    }
  }
}
?>
