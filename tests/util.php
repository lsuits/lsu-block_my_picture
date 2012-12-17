<?php

    function setConfig(){
        
        
        
        set_config('ready_url', 'https://tt.lsu.edu/api/v2/json/kZabUZ6TZLcsYsCnV6KW/photos/recently_updated/%s', 'block_my_picture');
        set_config('update_url', 'https://tt.lsu.edu/api/v2/jpg/kZabUZ6TZLcsYsCnV6KW/photos/lsuid/%s/update', 'block_my_picture');
        set_config('webservice_url', 'https://tt.lsu.edu/api/v2/jpg/kZabUZ6TZLcsYsCnV6KW/photos/lsuid/%s?skip_place_holder=true', 'block_my_picture');
        set_config('fetch', 1, 'block_my_picture');
        set_config('cron_users', 10, 'block_my_picture');
    }
  
    
    function generateUsers($count,$with_pix, $bad_id=false){
        $users = array();
        for($i=0; $i<$count; $i++){
            $pix = $with_pix > 0 ? true : false;
            $users[] = generateUser(null, null, $pix, $bad_id);
            $with_pix--;
        }
        return $users;
    }
    
    function generateUser($username=null, $id=null, $has_pic=false, $bad_id=false){
        $users = array();
        
        $uname = $username ? $username : genStr(7, false);
        $idnum = $id ? $id : genLsuId();
        $idnum = $bad_id ? "123".genStr(4, true) : $idnum;
        $user = array(
            'username'  =>  $uname,
            'idnumber'  =>  $idnum,
            'picture'   =>  (int)$has_pic,
            'deleted'   =>  0
            );
        
        return $user;
    }
    
    function getUsersWithoutPix(){
        return array(

            array(
                'username'  =>  'cweeks5',
                'idnumber'  =>  '898346692'
            ),
            array(
                'username'  =>  'cweil2',
                'idnumber'  =>  '897445626'
            ),
            array(
                'username'  =>  'cweins1',
                'idnumber'  =>  '890403609'
            ),
            array(
                'username'  =>  'cweish1',
                'idnumber'  =>  '897550854'
            ),
            array(
                'username'  =>  'cwelc11',
                'idnumber'  =>  '890296640'
            )
        );
    }
    
    function getUsersWithPix(){
        return array(
            array(
                'username'  =>  'jamsulli',
                'idnumber'  =>  '892300444'
            ),
            array(
                'username'  =>  'jpeak5',
                'idnumber'  =>  '890775049'
            ),
            array(
                'username'  =>  'hkelly1',
                'idnumber'  =>  '895039221'
            ),
            array(
                'username'  =>  'gcole',
                'idnumber'  =>  '891223883'
            ),
            array(
                'username'  =>  'aaugui1',
                'idnumber'  =>  '893570546'
            ),
            array(
                'username'  =>  'aaust11',
                'idnumber'  =>  '895212274'
            )
        );
    }
    
    function genLsuId(){
        return mt_rand(890000000, 899999999);
    }
    
    function genStr($len=5, $num=false){
        $alpha = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $numer = "0123456789";
        $alphanumeric = $alpha.$numer;
        
        $bound = $num ? strlen($alpha) + strlen($numer) : strlen($alpha);
        $legal = $num ? $alpha.$numer : $alpha;
        $str = "";
        for($i=0; $i<$len; $i++){
            $str .= $legal[mt_rand(0, $bound-1)];
        }
        return $str;
    }
    
  
//    SANITY CHECK
//    $users = generateUsers(25, 10);
//
//    foreach($users as $user){
//        print_r($user);
//    }
    
?>
