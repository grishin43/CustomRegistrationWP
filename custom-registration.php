<?php

/*
  Plugin Name: Custom Registration
  Plugin URI: https://mindsts.com/salon/careem/
  Description: Updates user rating based on number of posts.
  Version: 1.2
  Author: Grishin Vladislav
  Author URI: https://www.facebook.com/profile.php?id=100005491785841
 */


function custom_registration_function() {
    if ( isset( $_POST['submit'] ) ) {
        registration_validation(
            $_POST['username'],
            $_POST['password'],
            $_POST['password_confirm'],
            $_POST['email'],
            $_POST['fname'],
            $_POST['lname'],
            $_POST['nickname'],
            $_POST['website'],
            $_POST['code']

        );

        // sanitize user form input
        global $fullname, $password, $password_confirm, $email, $brand, $carnumber, $phonenumber, $website, $code, $pn;
        $code = $_POST['code'];
        $pn = $_POST['nickname'];
        $fullname    = sanitize_user( $_POST['username'] );
        $password    = esc_attr( $_POST['password'] );
        $password_confirm    = esc_attr( $_POST['password_confirm'] );
        $email       = sanitize_email( $_POST['email'] );
        $brand       = sanitize_text_field( $_POST['fname'] );
        $carnumber   = sanitize_text_field( $_POST['lname'] );
        $phonenumber = sanitize_text_field( $code. $pn );
        $website     = $_POST['website'];


        // call @function complete_registration to create the user
        // only when no WP_error is found
        complete_registration(
            $fullname,
            $password,
            $password_confirm,
            $email,
            $brand,
            $carnumber,
            $phonenumber,
            $website,
            $code
        );
    }

    registration_form(
	    $fullname,
	    $password,
        $password_confirm,
	    $email,
	    $website,
	    $brand,
	    $carnumber,
	    $phonenumber,
	    $code
    );
}

function registration_form( $fullname, $password, $password_confirm, $email, $website, $brand, $carnumber, $phonenumber,$code ) {

    $current_url='http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['SERVER_NAME'].'/login-logout/';
    echo '
    <style>
	div {
		margin-bottom:2px;
	}
	
	input{
		margin-bottom:4px;
	}
	</style>
	';

    echo '
    <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
	<div>
	<label for="username">الإسم: <strong style="color: red">*</strong></label>
	<input type="text" name="username" value="' . ( isset( $_POST['username'] ) ? $fullname : null ) . '">
	</div>
	
	<div>
	<label for="email">الإيميل: <strong style="color: red">*</strong></label>
	<input type="text" name="email" value="' . ( isset( $_POST['email'] ) ? $email : null ) . '">
	</div>
	
	<div>
	<label for="nickname">رقم الهاتف: <strong style="color: red">*</strong></label>
	<center><input type="text" name="nickname" style="width: 45%" value="' . trim( isset( $_POST['nickname'] ),['077','078','079'] ? $phonenumber : null ) . '">
	<select id="select_phone" name="code" >
	<option value=""></option>
    <option value="079"'. ( isset( $_POST['code']) ? $code : null ) .' >079</option>
    <option value="078"'. ( isset( $_POST['code']) ? $code : null ) .' >078</option>
    <option value="077"'. ( isset( $_POST['code']) ? $code : null ) .' >077</option>
    </select>
	<strong><label for="nickname">962+</label></strong></center>
	</div>

	<div>
	<label for="password">كلمة المرور <strong style="color: red">*</strong></label>
	<input type="password" name="password" value="' . ( isset( $_POST['password'] ) ? $password : null ) . '">
	</div>
	
	<div>
	<label for="password_confirm">تأكيد كلمة المرور <strong style="color: red">*</strong></label>
	<input type="password" name="password_confirm" value="' . ( isset( $_POST['password_confirm'] ) ? $password_confirm : null ) . '">
	</div>
	
	<div>
	<ul><label for="website">فئة المركبة: </label><strong style="color: red">*</strong></ul>
	<li><input type="radio" name="website" value="•	المميز "' . ( isset( $_POST['website'] ) ? $website : null ) . '>   المميز   </li>
	<li><input type="radio" name="website" value="•	تكسي أصفر "' . ( isset( $_POST['website'] ) ? $website : null ) . '>   تكسي أصفر   </li>
	<li><input type="radio" name="website" value="•	مركبة خاصة "' . ( isset( $_POST['website'] ) ? $website : null ) . '>   مركبة خاصة   </li>
	</div>
	
	<div>
	<label for="firstname">نوع المركبة: <strong style="color: red">*</strong></label>
	<input type="text" name="fname" value="' . ( isset( $_POST['fname'] ) ? $brand : null ) . '">
	</div>
	
	<div>
	<label for="lname">رقم لوحة المركبة: <strong style="color: red">*</strong></label>
	<input type="text" name="lname" value="' . ( isset( $_POST['lname'] ) ? $carnumber : null ) . '">
	</div>
	<div class="btn_submit">
	<input type="submit" name="submit" value="تسجيل"/>
	<button><a href="' . get_site_url() . '/login-logout/" >لدي حساب</a></button>
	</div>
	</form>
	';


}

function registration_validation( $fullname, $password, $password_confirm, $email, $brand, $carnumber, $phonenumber, $website, $code) {
    global $reg_errors,$wpdb;
    $reg_errors = new WP_Error;

    if ( empty( $fullname ) || empty( $password ) || empty( $email ) || empty( $brand ) || empty( $carnumber ) || empty( $phonenumber  ) || empty($website) ) {
        $reg_errors->add( 'field', 'الرجاء تعبئة خانات التسجيل المطلوبة' );
    }

    if ( strlen( $fullname ) < 2 && strlen( $fullname ) > 100) {
        $reg_errors->add( 'username_length', 'اسم المستخدم قصير، مطلوب حرفان على الاقل' );
    }

    if ( ! validate_username( $fullname ) ) {
        $reg_errors->add( 'username_invalid', 'عذراً، اسم المستخدم الذي ادخلته غير متاح' );
    }

    if ( strlen( $password ) < 4 && strlen( $password ) > 100 ) {
        $reg_errors->add( 'password', 'كلمة السر يجب ان تكون اكثر من اربعة خانات' );
    }

    if ( $password != $password_confirm ) {
        $reg_errors->add( 'password_confirm', 'Passwords in the fields are different' );
    }

    if ( ! is_email( $email ) ) {
        $reg_errors->add( 'email_invalid', 'الايميل المُدخل غير مستخدم' );
    }

    if ( strlen( $email ) <  1 && strlen( $email ) > 64) {
        $reg_errors->add( 'email_invalid', 'الايميل المُدخل غير مستخدم' );
    }

    if ($wpdb->query("SELECT user_email FROM wp_users WHERE user_email='".$email."'"))
    {
        $reg_errors->add( 'email_exist', 'Email is already use' );
    }

    if(!preg_match("/[0-9]$/i",$phonenumber) && strlen ($phonenumber) == 14){
        $reg_errors->add( 'phonenumber', 'رقم الموبايل المُدخل غير مستخدم' );
    }
    
    if( strlen($code) < 3){
        $reg_errors->add( 'phonenumber', 'رقم الموبايل المُدخل غير مستخدم' );
    }

    if( strlen( $brand ) < 2 && strlen( $brand ) > 100)
    {
        $reg_errors->add( 'brand', 'نوع المركبة مكتوبة بشكل خاطئ' );
    }

    if( strlen( $carnumber ) < 2 && strlen( $brand ) > 100)
    {
        $reg_errors->add( 'carnumber', 'رقم لوحة السيارة مكتوبة بشكل خاطئ' );
    }


    if ( is_wp_error( $reg_errors ) ) {

        foreach ( $reg_errors->get_error_messages() as $error ) {
            echo '<div>';
            echo '<strong>خطأ</strong>:';
            echo $error . '<br/>';

            echo '</div>';
        }
    }
}

function complete_registration() {
    global $reg_errors, $fullname, $password, $email, $brand, $carnumber, $phonenumber, $website, $code,$pn;

    if ( count( $reg_errors->get_error_messages() ) < 1 ) {
        $userdata      = array(
            'user_login'  => $fullname,
            'user_email'  => $email,
            'user_pass'   => $password,
            'user_url'    => $website,
            'first_name'  => $brand,
            'last_name'   => $carnumber,
            'nickname'    => '+962'.$phonenumber,
            'description' => get_the_title()
        );
		$user = wp_insert_user( $userdata );
        $current_url='http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['SERVER_NAME'].'/login-logout/';
        echo
        '<div id="overlay">
        <div class="popup" style="color: black;">
        
        <div class="pl-left razleft">  
                       
         <h2>اكتمل التسجيل</h2>
                   
         <p>شكراً للتسجيل! سيتم انشاء حسابك الخاص والموافقة عليه خلال ٢٤ ساعة، سيتم الاتصال بك هاتفياً من قبل فريق مايندز للتأكيد.
         </p>
			
         </div> 
        <button class="close" title="close" onclick="goto()"></button>
        <script>
        	function goto() {
          	window.location = " '.$current_url.'";
        	}
        	
		</script>
		</div>
        </div>';
        //echo '<script>window.location = "'.$current_url.'";</script>';

    }
}

// Register a new shortcode: [cr_custom_registration]
add_shortcode( 'cr_custom_registration', 'custom_registration_shortcode' );

// The callback function that will replace [book]
function custom_registration_shortcode() {
    ob_start();
    custom_registration_function();

    return ob_get_clean();
}


