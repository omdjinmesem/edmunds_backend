<?php 
//00 add_option
add_option('last_crawlurl', '', '', 'yes');
add_option('last_pub_url', '', '', 'yes');

//01. TEXT DOMAIN
add_action('init', 'js_grabdomain');

function js_grabdomain() 
{
  load_plugin_textdomain('rg', false, 'rg');
}

//02. ICON PATH ------------------------------------------------------------------------
function ic0npath($dir) {
  $themepath = get_bloginfo('template_directory');
  $iconpath = $themepath.'/'.$dir;
  return $iconpath;
}

//03. BACKEND MENU ------------------------------------------------------------------------
add_action('admin_menu','rg_grabmenu');
function rg_grabmenu()
{
  /* 03.1 TOP MENU ---------------- */
  if(function_exists('add_menu_page')) 
  {
    $grabtopmenu = add_menu_page(
      __('Grab Karepmu Page', 'rg'), //Page Title, textdomain
      __('Jupuk Siji', 'rg'), //Menu Label, textdomain
      'manage_options',  //Privilege
      'js-grab',      //Slug
      'js_grab_page', //Function Page <--
      ic0npath('core/icons/bird.png'), // 16x16
      '1' // posisi
    );
    add_action("admin_print_scripts-$grabtopmenu", 'grab_style');
  } else {}
  /* 03.2 END of TOP MENU ---------------- */

  /* 03.02 Sub-Menu */

  if(function_exists('add_submenu_page'))
  {
    //01. CRAWL
    $jskrol = add_submenu_page(
      'js-grab', //slug dari Top Menu
      __('Halaman Penggerayang', 'rg'), //Page Title, textdomain
      __('Penggerayang', 'rg'), //Menu Label, textdomain
      'manage_options',  //Privilege
      'rg_crawl',      //Slug
      'rg_crawl_page'  //Function Page
    );
    add_action("admin_print_scripts-$jskrol", 'grab_style');

    $jskrol = add_submenu_page(
      'js-grab', //slug dari Top Menu
      __('Jupuk Kabeh', 'rg'), //Page Title, textdomain
      __('Jupuk Kabeh', 'rg'), //Menu Label, textdomain
      'manage_options',  //Privilege
      'rg_grab_all',      //Slug
      'rg_grab_all_page'  //Function Page
    );
    add_action("admin_print_scripts-$jskrol", 'grab_style');

    $jskrol = add_submenu_page(
      'js-grab', //slug dari Top Menu
      __('Publish Flow', 'rg'), //Page Title, textdomain
      __('Publish Flow', 'rg'), //Menu Label, textdomain
      'manage_options',  //Privilege
      'rg_publish_flow',      //Slug
      'rg_publish_flow_page'  //Function Page
    );
    add_action("admin_print_scripts-$jskrol", 'grab_style');

    $jskrol = add_submenu_page(
      'js-grab', //slug dari Top Menu
      __('Attachment Flow', 'rg'), //Page Title, textdomain
      __('Attachment Flow', 'rg'), //Menu Label, textdomain
      'manage_options',  //Privilege
      'rg_attachment',      //Slug
      'rg_attachment_page'  //Function Page
    );
    add_action("admin_print_scripts-$jskrol", 'grab_style');

    $jskrol = add_submenu_page(
      'js-grab', //slug dari Top Menu
      __('Sandbox', 'rg'), //Page Title, textdomain
      __('Sandbox', 'rg'), //Menu Label, textdomain
      'manage_options',  //Privilege
      'rg_sandbox',      //Slug
      'rg_sandbox_page'  //Function Page
    );
    add_action("admin_print_scripts-$jskrol", 'grab_style');
  }



  /* 03.02 END of SubMenu */

}

//04. END of BACKEND MENU ------------------------------------------------------------------------

function js_grab_page()
{
  $gurl = get_option('siteurl').'/wp-admin/admin.php?page=js-grab';
  $gaurl = get_option('siteurl').'/wp-admin/admin.php?page=rg_grab_all';

  echo '<div class="wrap">
  <h2>Halaman Utama</h2>
  <div class="inside">';
 
  /* 02. HANDLER */
  if(isset($_POST['gsubmit'])) {
    $issubmit = true;
    $u        = $_POST['u'];
    $transit  = false;
  } elseif(isset($_REQUEST['gsubmit'])) {
    $issubmit = true;
    $u        = base64_decode($_REQUEST['u']);
    $transit  = true;
  } else {
    $issubmit = false;
    $u        = 'http://www.edmunds.com/car-news/acura-precision-concept-sets-future-design-direction-2016-detroit-auto-show.html';
    $transit  = false;
  }

  /* 03. KONFIRMASI */
  if($transit){
    echo '<a href="'.get_option('siteurl').'/wp-admin/admin.php?page=rg_publish_flow" class="button-primary r">Stop</a>';
  }
  if($issubmit)
    echo '<p class="gr">TARGET URL ' . $u .'</p>';


  /* 01. FORM 
    - Action : Dimana datanya dikirim
    - Method : Ada 3 Tipe
      - POST (mengirim)
      - GET (meminta)
      ----------------- (in/by/form a program)
      - REQUEST (minta)
      ----------------- (in URL)
  */

  echo '
  <form action="'.$gurl.'" method="POST">
    <input type="url" name="u" value="'.$u.'" required />
    <input type="submit" name="gsubmit" value="Grab" class="button-primary"/>
  </form>';

  /* 05. EXECUTE */

  if($issubmit) {
    ini_set('display_errors',0);
    echo '<div class="res">';
    /*LOAD USER AGENT */
    include TEMPLATEPATH . '/core/ua.php';

    ini_set('user_agent',$theua);
    $opts    = array('http'=>array('method'=>"GET",'header'=>"Accept-language: en-us,en\r\n"));
    $context = stream_context_create($opts);
    $grabit  = file_get_contents($u,false,$context);

    // ERROR HANDLING
    if($grabit === false) {
      
      echo '<p class="rd">Gagal bro, nunggu 3 detik sik</p>';

      if($transit){
        $blurl = $gaurl . '&grsubmit=true';
      }else{
        // $blurl = $gurl . '&gsubmit=true&u='.base64_encode($u).'';
        echo '
          <form action="'.$gurl.'" method="POST" id="gs">
            <input type="hidden" name="u" value="'.$u.'" required />
            <input type="hidden" name="gsubmit" value="Grab" class="button-primary"/>
          </form>';
        // ob_flush();
        sleep(3);

        echo '<script>document.forms["gs"].submit()</script>';
        exit;
      }

      
      $reload = 3;
      echo '<style>#jload {width:100%;height:5px; padding:0; margin:10px auto; background:#ddd;position:relative;}.expand {width:100%;height:5px; margin:0; background:#390; position:absolute;box-shadow:0 1px 0 0 rgba(255,255,255,1.0); -moz-animation:fullexpand '.$reload.'s ease-out;-webkit-animation:fullexpand '.$reload.'s ease-out;}@-moz-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}@-webkit-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}</style>';
      echo '<div id="lw"><div id="jload"><span class="expand"></span></div></div>';

      echo '<meta http-equiv="refresh" content="'.$reload.'; url='.$blurl.'" />';
      exit();
    } else {}

    // menggunakan str_replace lebih ringan daripada preg___ yang akan memakan memori lebih banyak
    
    /* MINIFIKASI */
    $grabit = str_replace(array("\n","\r","\t"),'',$grabit);
    $grabit = str_replace(array('      ','     ','    ','   ','  '),' ',$grabit);
    $grabit = str_replace('> <','><',$grabit);
    /* Kill Script */
    $grabit = str_replace('<script','<!--<script',$grabit);
    $grabit = str_replace('/script>','/script>-->',$grabit);

    // script minifikasi dan kill script dalam 1 line
    // $grabit = str_replace('> <','><',str_replace(array('      ','     ','    ','   ','  '),' ',str_replace(array("\n","\r","\t"),'',$grabit)));

    /* DO TITLE ----------- */
    // PEMBUKA : <h1 class="title header-1"> // jika pembuka tidak uniq maka abillah hal yang ada di belakangnya
    // PENUTUP : </h1><hr class="loose-dotted"/> // jika penutup tidak uniq, maka ambillah hal yang ada di depannya
    // 
    // str_replace(apa yang di ganti, penggantinya, objeknya)
    $do_title = $grabit; // 01. clone
    $do_title = str_replace('<h1 class="title header-1">','<code><h1 class="title header-1">',$do_title); // 02. sisipkan kode pembuka 
    $do_title = str_replace('</h1><hr class="loose-dotted"/>','</h1></code><hr class="loose-dotted"/>',$do_title); // 03. sisipkan kode penutup 

    /* PROSES DOM*/
    $dom_title = new DOMDocument();
    @$dom_title->loadHTML($do_title);
    $code_title = $dom_title->getElementsByTagName('code');
    foreach($code_title as $tpl){
      $judul = $tpl->nodeValue;
      // echo $judul . '<hr/>';

    }

    /* PR : DO LINE */

    $do_line = $grabit;
    $do_line = str_replace('<p class="byline">', '<p class="byline"><code>', $do_line);
    $do_line = str_replace('</p><div class="social-toolbar tid"', '</code></p><div class="social-toolbar tid"', $do_line);

    /* PROSES DOM */
    $dom_line = new DOMDocument();
    @$dom_line->loadHTML($do_line);
    $code_line = $dom_line->getElementsByTagName('code');
    foreach($code_line as $tpl){
      $line = $tpl->nodeValue;
      // echo $line . '<hr/>';
    }

    /* Proses Pemisahan String : Cara 1 */

    # $line = Published: 01/12/2016  - byJason Kavanagh, Engineering Editor
    $x1 = explode(', ', $line);
    $jabatan = $x1[1]; // Engineering Editor 
    $x2 = explode(' - by',$x1[0]);
    $jeneng = $x2[1]; // Jason Kavanagh
    $tanggal = str_replace('Published: ','',$x2[0]); // 01/12/2016

    // echo 'Jabatan : ' . $jabatan . ', Nama : ' . $jeneng . ', Tanggal : ' . $tanggal . '<hr/>';

    /* Proses Pemisahan String : Cara 2 */

    list($x, $tgl, $name, $as) = explode('|', str_replace(array('Published: ',' - by',', ',': '), '|', $line));
    $tgl = substr($tgl, 0, 10);

    // echo "$x $tgl $name $as <hr/>";


    /* Ambil Gambar Cara 1 */
    // pembuka : <div class="loading"><img
    // penutup : alt="loading" itemprop="image"/>

    /*
    
    $do_gambar = $grabit;
    $do_gambar = str_replace('<div class="loading"><img', '<div class="loading"><ganteng><img', $do_gambar);
    $do_gambar = str_replace('alt="loading" itemprop="image"/>', 'alt="loading" itemprop="image"/></ganteng>', $do_gambar);

    $dom_gambar = new DOMDocument();
    @$dom_gambar->loadHTML($do_gambar);
    $code_gambar = $dom_gambar->getElementsByTagName('ganteng');
    foreach($code_gambar as $tpl){
      $gambar = $tpl->getElementsByTagName('img');
      foreach($gambar as $img){
        $img_src = $img->getAttribute('src');
        echo $img_src;
      }
    }

    */

    /* Ambil Attribute Cara 2 */
    
    $do_gambar = $grabit;
    $do_gambar = str_replace('<div class="loading"><img', '<div class="loading"><susu', $do_gambar);

    $dom_gambar = new DOMDocument();
    @$dom_gambar->loadHTML($do_gambar);
    $code_gambar = $dom_gambar->getElementsByTagName('susu');
    foreach($code_gambar as $tpl){
      $img_src = $tpl->getAttribute('src');
      // echo $img_src . '<hr/>';
    }

    // echo '<img src="'.str_replace('_600','_1600', $img_src).'"/>'; // menampilkan gambar dan mengubah ke ukuran full

    // http://www.edmunds.com/editorial/rest/damrepository/findbycontent/?path=/car-news/acura-precision-concept-sets-future-design-direction-2016-detroit-auto-show&thumbsize=98&photosize=600&fullscreen=1920&isarticle=true
  
    /*
      PR 
       1. Ambil Value Attribute href dari link 'View more Consumer Car News articles'
       2. Ambil semua paragraf di artikel 
     */
    
   /* 01. Ambil Value href */

   // Depan : <div class="more"><p><a
   // Belakang : articles</a>

    $do_link = $grabit;
    $do_link = str_replace('<div class="more"><p><a', '<div class="more"><p><peni', $do_link);
    $do_link = str_replace('articles</a>', 'articles</peni>', $do_link);

    $dom_link = new DOMDocument();
    @$dom_link->loadHTML($do_link);
    $code_link = $dom_link->getElementsByTagName('peni');
    foreach($code_link as $tpl){
      $link_href = $tpl->getAttribute('href');
      // echo $link_href . '<hr/>';
    }

    /* 02. Ambil Paragraf */

    // Depan :  <div class="content grid-138"><div class="article loose-spacing">
    // Belakang : </div><div class="article-footer">

    $do_par = $grabit;
    $do_par = str_replace('<div class="content grid-138"><div class="article loose-spacing">', '<div class="content grid-138"><dut class="article loose-spacing">', $do_par);
    $do_par = str_replace('</div><div class="article-footer">', '</dut><div class="article-footer">', $do_par);

    $all_p = '';

    $dom_par = new DOMDocument();
    @$dom_par->loadHTML($do_par);
    $code_par = $dom_par->getElementsByTagName('dut');
    foreach($code_par as $par){
      $code_par2 = $par->getElementsByTagName('p');
      foreach($code_par2 as $p){
        //$p_langkap .= '<p>' . $p->nodeValue . '</p>';
        //$paragraf[] = $p->nodeValue;
        $all_p .= $p->nodeValue . '[(.Y.)]';
      }
    }
    $all_p = substr($all_p, 0, -7); 

    //echo $p_langkap;

    /* DO GALLERY */
    $url_json = str_replace(array('http://www.edmunds.com','.html'),array('http://www.edmunds.com/editorial/rest/damrepository/findbycontent/?path=','&fullscreen=1600&isarticle=true&cdDate=null'),$u);
    $jsonimg = file_get_contents($url_json,false,$context);
    $arr_json = json_decode($jsonimg);
    $all_g = '';
    foreach($arr_json->photos as $photo) {
      $galleries[] = $photo->fullscreen;
      $all_g .= $photo->fullscreen . '[(.Y.)]';
    }
    $all_g = substr($all_g, 0, -7); 

    //echo implode('<br/>', $galleries) . '<br/>';

    $do_kwd = $grabit;
    $do_kwd = str_replace('<meta name="news_keywords"', '<susu name="news_keywords"', $do_par);

    $dom_kwd = new DOMDocument();
    @$dom_kwd->loadHTML($do_kwd);
    $code_kwd = $dom_kwd->getElementsByTagName('susu');
    foreach($code_kwd as $kwd){
      $keyword = $kwd->getAttribute('content');
    }

    // echo $keyword;

    /* 
      PR 
      - Review Ulang apa saja yang belum kita bawa
      x- Cari tahu halaman edmuns yang menampilkan semua category
    */
    

    // echo $grabit; 
    // die();
    
    /*
    
    Judul : $judul
    All Images : $all_g
    Tanggal : $tgl
    Paragraph : $all_p
    Keywords : $keyword
     */
    
    // DATA EMPTY CHECK
    if(empty($all_p) || empty($all_g)){
      $apdet = $wpdb->query("UPDATE wp_gurl SET gstatus = 'skipped' WHERE turl = '".$u."' ");
      if($apdet !== false){
        echo '<p class="gr">Paragraf atau Image Error, skip sik lek</p>';
        $reload = 5;
        echo '<style>#jload {width:100%;height:5px; padding:0; margin:10px auto; background:#ddd;position:relative;}.expand {width:100%;height:5px; margin:0; background:#390; position:absolute;box-shadow:0 1px 0 0 rgba(255,255,255,1.0); -moz-animation:fullexpand '.$reload.'s ease-out;-webkit-animation:fullexpand '.$reload.'s ease-out;}@-moz-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}@-webkit-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}</style>';
        echo '<div id="lw"><div id="jload"><span class="expand"></span></div></div>';

        echo '<meta http-equiv="refresh" content="'.$reload.'; url='.$gaurl.'&grsubmit=true" />';

      } else{
        echo '<p class="gr">Skipped gagal bro, refresh wae</p>';
      }
      exit;
    } else {}

    // DATA EXSISTEN CHECK
    global $wpdb;

    $datachk = $wpdb->get_var( "SELECT COUNT(gid) FROM wp_gdata WHERE turl = '".$u."' " );

    if($datachk>0){
      echo '<p class="rd">Data sudah ada</p>';
    }else{
      $tgl = substr($tgl, 0,10);
      $tgl = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $tgl)));

      $saved = $wpdb->query( $wpdb->prepare("INSERT INTO wp_gdata
        (gtitle,gimgs,gdate,gpar,gkwd,turl,gstatus)
        VALUES (%s,%s,%s,%s,%s,%s,%s)", 
        $judul,$all_g,$tgl,$all_p,$keyword,$u,'grabbed'
      ));

      if($saved !== false)
      {
        // update status
        $apdet = $wpdb->query("UPDATE wp_gurl SET gstatus = 'grabbed' WHERE turl = '".$u."' ");

        if($apdet !== false){
          if($transit){
            $reload = mt_rand(3,5); // 5,15
            $requrl = $gaurl . '&grsubmit=true';
            
            echo '<style>#jload {width:100%;height:5px; padding:0; margin:10px auto; background:#ddd;position:relative;}.expand {width:100%;height:5px; margin:0; background:#390; position:absolute;box-shadow:0 1px 0 0 rgba(255,255,255,1.0); -moz-animation:fullexpand '.$reload.'s ease-out;-webkit-animation:fullexpand '.$reload.'s ease-out;}@-moz-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}@-webkit-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}</style>';
            echo '<div id="lw"><div id="jload"><span class="expand"></span></div></div>';
            
            echo '<meta http-equiv="refresh" content="'.$reload.'; url='.$requrl.'" />';
          }else{}
        }else{
          echo '<p class="rd">Data gagal tersimpan bro</p>';
          $wpdb->show_errors();
          $wpdb->print_error();

        } // end apdet

        echo '<p class="gr">Data berhasil tersimpan bro</p>';
      }
      else
      {
        echo '<p class="rd">Data gagal tersimpan bro</p>';
        $wpdb->show_errors();
        $wpdb->print_error();
      }

      echo '</div>';
    }
    /*
      NEXT 
      --------------------------
      - Data Existance Check
      - Fix Formating Date
     */





  }
  

  echo '</div>
</div>';

}

// FUNCTION JS CRAWL ----------------------------------------------------------------------------

function rg_crawl_page()
{
  $gurl = get_option('siteurl').'/wp-admin/admin.php?page=rg_crawl';
  $last_crawlurl = get_option('last_crawlurl');

  echo '<div class="wrap">
  <h2>Halaman Penggerayang</h2>
  <div class="inside">';

  /* 02. HANDLER */
  if(isset($_POST['gsubmit'])) {
    $issubmit = true;
    $u        = $_POST['u'];
  } elseif (isset($_REQUEST['rsubmit'])) {
    $issubmit = true;
    $u        = base64_decode($_REQUEST['u']);
  } else {
    $issubmit = false;
    $u        = 'http://www.edmunds.com/car-news/sitemap.html';
  }

  /* 03. KONFIRMASI */
  if($issubmit)
    echo '<p class="gr">TARGET URL ' . $u .'</p>';
    
  echo '
  <form action="'.$gurl.'" method="POST">
    <input type="url" name="u" value="'.$u.'" required />
    <input type="submit" name="gsubmit" value="Grab" class="button-primary"/>
    <a href="'.$gurl.'" class="button">Pause</a>
    <a href="'.$last_crawlurl.'" class="button-primary">Resume</a>
  </form>';

  if($issubmit)
  {
    echo '<div class="res">';
    include TEMPLATEPATH . '/core/ua.php';

    ini_set('user_agent',$theua);
    $opts    = array('http'=>array('method'=>"GET",'header'=>"Accept-language: en-us,en\r\n"));
    $context = stream_context_create($opts);
    $grabit  = file_get_contents($u,false,$context);

    // menggunakan str_replace lebih ringan daripada preg___ yang akan memakan memori lebih banyak
    
    /* MINIFIKASI */
    $grabit = str_replace(array("\n","\r","\t"),'',$grabit);
    $grabit = str_replace(array('      ','     ','    ','   ','  '),' ',$grabit);
    $grabit = str_replace('> <','><',$grabit);
    /* Kill Script */
    $grabit = str_replace('<script','<!--<script',$grabit);
    $grabit = str_replace('/script>','/script>-->',$grabit);

    // echo $grabit;

    $do_sitemap = $grabit;
    $do_sitemap = str_replace('<div class="content gutter-top-3">', '<ra class="content gutter-top-3">', $do_sitemap);
    $do_sitemap = str_replace('</div></div></div></div></div></div></div>', '</div></ra></div></div></div></div></div>', $do_sitemap);

    $dom_sitemap  = new DOMDocument();
    @$dom_sitemap->loadHTML($do_sitemap);
    $code_sitemap = $dom_sitemap->getElementsByTagName('ra');
    $err = '';

    foreach($code_sitemap as $sitemaps){
      $sub_sitemaps = $sitemaps->getElementsByTagName('a');
      foreach($sub_sitemaps as $key => $sub_sitemap){
        $link = $sub_sitemap->getAttribute('href');
        if(strpos($link, 'sitemap') === false){
          
          $linkUrl = 'http://www.edmunds.com' . $link;
          // echo $linkUrl . '<br/>';
          /*
            NEXT
            1. save database
            2. data exsistance check crawling dan single grab
           */
          
          /* DATA EXISTACE CHECK */
          global $wpdb;
          $datachk = $wpdb->get_var( "SELECT COUNT(gid) FROM wp_gurl WHERE turl = '".$linkUrl."' " );

          if($datachk > 0){
            // $err .= "1";
            echo '<span class="rd">Link : ' . $linkUrl . ' wes ono :p</span><br/>';
            $wpdb->show_errors();
            $wpdb->print_error();
          }else{
            /* SAVE DATABSE */
            $savit = $wpdb->query($wpdb->prepare("INSERT INTO wp_gurl
              ( turl, gstatus )
              VALUES ( %s, %s )", 
              $linkUrl, ''
            ));

            if($savit){
              echo '<span class="gr">Link : ' . $linkUrl . ' successfully inserterd</span><br/>';
            }
            else {
              $err .= "1";
              echo '<span class="rd">Link : ' . $linkUrl . ' gagal bro :(</span><br/>';
              $wpdb->show_errors();
              $wpdb->print_error();
            } // end if savit
          } // end datachk
        } // end if sitemap
      } // end sub foreach

      $err .= '';
      // $link_href = $tpl->getAttribute('href');
      // echo $link_href . '<hr/>';
      

      /* TRANSISI */
      if(empty($err)){
        if(strpos($grabit, '<a class="yui-pg-next"') !== false){
          // transisi disini
          // definisi base url
          echo $u . '<br>';

          // 86400/5

          if(strpos($u, '-pg') !== false){
            $hal = str_replace(array('http://www.edmunds.com/car-news/sitemap-pg','.html'), '', $u);
          }else{
            $hal = 1;
          }
          $nexturl = 'http://www.edmunds.com/car-news/sitemap-pg' . ($hal+1) . '.html';

          $reload = mt_rand(3,5); // 5,15
          $requrl = $gurl.'&rsubmit=true&u='.base64_encode($nexturl);
          update_option('last_crawlurl', $requrl);
          echo $nexturl;
          echo '<style>#jload {width:100%;height:5px; padding:0; margin:10px auto; background:#ddd;position:relative;}.expand {width:100%;height:5px; margin:0; background:#390; position:absolute;box-shadow:0 1px 0 0 rgba(255,255,255,1.0); -moz-animation:fullexpand '.$reload.'s ease-out;-webkit-animation:fullexpand '.$reload.'s ease-out;}@-moz-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}@-webkit-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}</style>';
          echo '<div id="lw"><div id="jload"><span class="expand"></span></div></div>';

          echo '<meta http-equiv="refresh" content="'.$reload.'; url='.$requrl.'" />';
        }else{
          echo '<h2 class"gr">Rampung bro</h2>';
        }
      }else{
        echo '<p class="rd">Ada yang gagal insert, ulang dulu bro</p>';

        $nexturl = 'http://www.edmunds.com/car-news/sitemap-pg' . $hal . '.html';
        $reload = mt_rand(3,5); // 5,15
        $requrl = $gurl.'&rsubmit=true&u='.base64_encode($nexturl);        
        echo $nexturl;
        echo '<style>#jload {width:100%;height:5px; padding:0; margin:10px auto; background:#ddd;position:relative;}.expand {width:100%;height:5px; margin:0; background:#390; position:absolute;box-shadow:0 1px 0 0 rgba(255,255,255,1.0); -moz-animation:fullexpand '.$reload.'s ease-out;-webkit-animation:fullexpand '.$reload.'s ease-out;}@-moz-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}@-webkit-keyframes fullexpand {0% { width:0px;}100%{ width:100%;}}</style>';
        echo '<div id="lw"><div id="jload"><span class="expand"></span></div></div>';
        echo '<meta http-equiv="refresh" content="'.$reload.'; url='.$requrl.'" />';

      } // end err
    } // end foreach code sitemap

    echo '</div>';
  }else{}
    /*
      UA
      filgetcon
       grab biasa -> clone -> buka tutup -> foreach

     */
  echo '</div>
</div>';

}

// GRAB ALL PAGE -----------------------------
function rg_grab_all_page()
{
  global $wpdb;
  $grall = get_option('siteurl') . '/wp-admin/admin.php?page=rg_grab_all';
  echo '<div class="wrap">
  <h2>Jupuk Kabeh</h2>
  <div class="inside">';

  // statistik
  $alltarget = $wpdb->get_var("SELECT count(gid) FROM wp_gurl");
  $allgrabbed = $wpdb->get_var("SELECT count(gid) FROM wp_gurl WHERE gstatus = 'grabbed' ");
  $allskipped = $wpdb->get_var("SELECT count(gid) FROM wp_gurl WHERE gstatus = 'skipped' ");
  $turl = $wpdb->get_var("SELECT turl FROM wp_gurl WHERE gstatus = '' LIMIT 1");

  echo '<p><label>All Target </label> : '.number_format($alltarget).'<br/>';
  echo '<label>All Grabbed </label> : '.number_format($allgrabbed).'<br/>';
  echo '<label>All Skipped </label> : '.number_format($allskipped).'</br>';
  echo '<label>Remaining </label> : '.number_format($alltarget-$allgrabbed-$allskipped).'<br/>';
  echo '<label>Current process </label> : '.$turl.'</p>';

  /* ENDED */
  if(empty($turl)) {
    echo '<h2 class="gr">Wes rampung bro, grab wes rampung</h2>';
  } else {
    /* EXECUTE */
    if(isset($_REQUEST['grsubmit'])) {
      $issubmit = true;
    } else {
      $issubmit = false;
    }

    if($issubmit) {
      //  transisi ke single grab disini
      //  10 feb
      //  tambah tombol pause
      //  membenarkan loading transisi

      $requrl = get_option('siteurl') . '/wp-admin/admin.php?page=js-grab&gsubmit=true&u='.base64_encode($turl).'';

      echo '<p>Grabbing now ... </p>';

      echo '<meta http-equiv="refresh" content="1; url='.$requrl.'" />';

    } else {
      echo '<p><a class="button" href="'.$grall.'&grsubmit=true">Grab All</a></p>';
    }
  }


  echo '</div>
</div>';
}

// PUBLISH FLOW -----------------------------
function rg_publish_flow_page()
{
  global $wpdb;
  $puburl = get_option('site_url') . '/wp-admin/admin.php?page=rg_publish_flow';

  echo '<div class="wrap">
  <h2>Alur Publish</h2>
  <div class="inside">';

  /* STATISTIC */

  $grabbed = $wpdb->get_var( "SELECT COUNT(gid) FROM wp_gurl WHERE gstatus = 'grabbed'; ");
  $published = $wpdb->get_var( "SELECT COUNT(gid) FROM wp_gurl WHERE gstatus = 'publish'; ");
  $remaining = $grabbed - $published;
  

  echo '<p><label>Available Data </label> : '.number_format($grabbed).'<br/>
           <label>Published </label> : '.number_format($published).' <br/>
           <label>Remaining </label> : '.number_format($remaining).'</p>';

  /* FORM */

  echo '<form method="POST" action="'.$puburl.'" >';
  $gdata =  $wpdb->get_row( "SELECT * FROM wp_gdata WHERE gstatus = 'grabbed' LIMIT 1; " );

  $gid    = $gdata->gid;
  $gtitle = $gdata->gtitle;
  $ximgs  = explode('[(.Y.)]', $gdata->gimgs);
  $gdate  = $gdata->gdate;
  $gpar   = str_replace('[(.Y.)]',"\n\n", str_replace(array('[(.Y.)][(.Y.)]','[(.Y.)][(.Y.)][(.Y.)]','[(.Y.)][(.Y.)][(.Y.)][(.Y.)]','[(.Y.)][(.Y.)][(.Y.)][(.Y.)][(.Y.)]'),'[(.Y.)]',$gdata->gpar));
  $gkwd   = $gdata->gkwd;
  $turl   = $gdata->turl;

  $xkwd = explode(', ', $gkwd);

  /* MENDAPATKAN TAG */
  if(count($xkwd) < 8) {
    $kwds = '';
    foreach($xkwd as $kw){
      $xk1 = explode(' ', $kw);
      if(count($xk1) < 2) {} else { // jika cuma 1 kata maka do nothing
        $r1 = js_ks($kw);
        $kwds .= implode(', ', $r1) . ', ';
        foreach($r1 as $kw1) {
          $xk2 = explode(' ', $kw1);
          if(count($xk2) < 2) {} else { // jika cuma 1 kata maka do nothing
            $kwds .= implode(', ', js_ks($kw1)) . ', ';
          }
        }
      }
    }
    $kwds .= '';

    $kwds = $gkwd . ', ' . substr($kwds, 0, -2);
    $kwds = implode(', ', array_unique(explode(', ', $kwds)));
    // UPDATE GKWD
    $apdet = $wpdb->query("UPDATE wp_gdata SET gkwd = '".$kwds."' WHERE gid = ".$gid." ");

    if($apdet !== false){
      echo '<p class="gr">Tambahan tag berhasil bro</p>';
      echo '<meta http-equiv="refresh" content="100; url='.$puburl.'" />';
    } else {
      echo '<p class="rd">Tambahan tag gagal bro</p>';
      $wpdb->show_errors();
      $wpdb->print_error();
    }
    exit;

  } else {}

  /**/

  echo '<p><label>URL</label>: '.$turl.'</p>';
  echo '<p><label>Title</label>: <input type="text" name="gtitle" value="'.$gtitle.'" /></p>';
  echo '<p><label>Date</label>: <input type="text" name="gdate" value="'.$gdate.'" /></p>';
  echo '<p><label>Tags</label>: <input type="text" name="gkwd" value="'.$gkwd.'" /></p>';

  echo '<p><label>Content</label>: <textarea name="gpar" rows="10">'.$gpar.'</textarea></p>';


  foreach($ximgs as $im){
    if(strpos($im, '[{|}]')) {
      $xim = explode('[{|}]', $im);
      $imsrc = $xim[0];
      $tbsrc = str_replace('_1600', '_98', $xim[0]);
      $imtitle = $xim[1];
    } else {
      $imsrc = $im;
      $tbsrc = str_replace('_1600', '_98', $im);
      $imtitle = str_replace(array('.jpg','jpeg','.gif','.png','_','-','+'),' ', basename($im));

      shuffle($xkwd);
    
      $imtitle = explode(' ns ', $imtitle);
      $imtitle = $imtitle[0] . ' ' . $xkwd[0] . ' ' . $xkwd[1] . ' ' . $xkwd[2] . ' ' . $xkwd[3];

      // unikkan kata yang dobel dobel
      $imtitle = implode(' ',array_unique(explode(' ', $imtitle)));
    
    }
    
    /* PR 
     1. toyota ns 20116 1600 -> hilangkan dari ns ke 1600 [ok]
     2. Masukkan tags sebagai tambahan title gambar
     */
    echo '<p>';
    echo '<label><img src="'.$tbsrc.'" width="98" height="65"></label>';
    echo '<input type="text" name="imtitle" value="'.$imtitle.'"> <br/>';
    echo '<input type="text" name="imsrc" value="'.$imsrc.'">';
    echo '</p><hr/>';
  }

  echo '<p>
          <input type="submit" name="esubmit" value="Save Changes" class="button" />
          <input type="submit" name="psubmit" value="Publish" class="button-primary" />
        </p>';

  echo '</form>';

  echo '</div>
</div>';
}

// Attachment FLOW -----------------------------
function rg_attachment_page()
{
  global $wpdb;
  $datas = $wpdb->get_results("SELECT * FROM wp_gdata");

  echo '<div class="wrap">
  <h2>Attachment Flow</h2>
  <div class="inside">';

  foreach($datas as $data)
  {
    $xkwd = explode(',',$data->gkwd);
    echo $data->gkwd . ' (' . count($xkwd) . ')</br>';
  }

  echo '</div>
</div>';
}

// Sandbox  -----------------------------
function rg_sandbox_page()
{
  echo '<div class="wrap">
  <h2>Sandbox</h2>
  <div class="inside">';

  /* 
    Jika 1 kata maka exit 
    jika kata lebih dari 8 maka no js_ks
  */

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

  echo '</div>
</div>';
}

//05. SANDBOX PAGE ----------------------------------------------------------------------------------

/* GRAB STYLE */
function grab_style()
{
  echo '<style type="text/css">
.gr {color:#390;}
.rd {color:#900;}
hr {height:1px; border:0 none; background:#ddd; color:#ddd;}
.inside {background:#f9f9f9; border:1px solid #ddd; width:97%; padding:15px;-moz-border-radius:3px;-webit-border-radius:3px;-khtml-border-radius:3px; border-radius:3px; margin-top:10px;}
textarea, input[type="text"] {width:600px;}
label{float:left;width:130px;}
select{width:150px;}
form,p {margin:0 0 15px 0;}
#lw {position:absolute; width:100%; height:5px; top:0;}
.res {position:relative; padding-top:30px;}
.r {float:right}
</style>';
}

/* JS KS
** Simple Keyword Suggestion
** v1:02 23:45 02/02/2016 
*/
function js_ks($s) {
  $q = array();

  // $ua = 'Mozilla/5.0 (Windows NT 6.1; rv:43.0) Gecko/20100101 Firefox/43.0|Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1|Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0'; $xua = explode('|',$ua); shuffle($xua); $theua = $xua[0]; ini_set('user_agent',$theua); 
  include ('ua.php');
  $d = file_get_contents('http://suggestqueries.google.com/complete/search?output=firefox&client=firefox&hl=en-US&q='.urlencode($s),false,stream_context_create(array('http'=>array('method'=>"GET",'header'=>"Accept-language: en-us,en\r\n"))));
  if (($d = json_decode($d,true)) !== null) {$q = $d[1];}
  return $q;
}

/*
  Single Grab -> Main

  Crawl
  
  Grab All
  --------------
  Publish Flow
  Attachment Flow

 */







/* CONTEKAN -------------------------

echo '<div class="wrap">
  <h2>Nama Halaman</h2>
  <div class="inside">';

  echo '</div>
</div>';

-------------------------------------- */


/*
  Chrome
  FireFox
  Safari
  -------
  Opera
  Internet Explorer


 */


/*


 */
?>