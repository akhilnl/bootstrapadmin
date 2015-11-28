<?php

class DbClass {
	
	public static $con;
	
	protected $hostName = 'localhost';
	
	protected $userName = 'root';
	
	protected $passWord = '';
	
	protected $dbName = 'bootstrapadmin';
	
	function __construct(){
		
		$this->dbConnect();
	}
	
	public function dbConnect(){
		
		self::$con = mysqli_connect($this->hostName,$this->userName,$this->passWord,$this->dbName);
			
		if(!self::$con)
		mysqli_error(self::$con);
	
		
	
	}
	
	public function cleanValues($value) {
		
		$value = trim($value);
		
		$value = mysqli_escape_string(self::$con,$value);
		
		return $value;
	}
	
	public function globalInsert($tableName,$paramArray = array(),$valuesArray=array(),$last_insert_id = false,$multi=false){
		
		if($multi == true) {
			
			$q = 'INSERT INTO '.$tableName.' ('.implode(',',$paramArray).') VALUES '.implode(',',$valuesArray).'';
		
		}else {
			
			$q = 'INSERT INTO '.$tableName.' ('.implode(',',$paramArray).') VALUES ("'.implode('","',$valuesArray).'")';
		}
				
		if(!mysqli_query(self::$con,$q)){
			
						
			return array('error_num'=>mysqli_errno(self::$con),'error_msg'=>mysqli_error(self::$con),'query'=>$q);
			
			
		}
		
		if($last_insert_id == true)
		return mysqli_insert_id(self::$con);
	}
	
	
	public function globalSelect($tableName,$paramArray = array(),$whereCond ='',$numRows = false,$values = false){
		
		if(empty($paramArray)) {
			
			$q = 'SELECT * FROM '.$tableName.' '.$whereCond;
		
		}else {
		
			$q = 'SELECT '.implode(',',$paramArray).' FROM '.$tableName.' '.$whereCond;
		}
		
		$result = mysqli_query(self::$con,$q);
		
		
		if(!$result){
			
			printf("Errormessage: %s\n", mysqli_error(self::$con));
			
			exit;
		}else {
			
			
			if($values == true){
				
				while($row = mysqli_fetch_array($result)) {
					
					$valuesArray[] = $row[0];
				}
				
				return $valuesArray;
			}
			
			if($numRows == true) {
				
				return mysqli_num_rows($result);
			
			}else {
				
				$resultArray = array();
				
				$cnt = 0;		
				
				while($row = mysqli_fetch_object($result)) {
					
					foreach($row as $field => $value) {
						
						$resultArray[$cnt][$field] =  $value; 
					}
				    
					$cnt++;
				}
				
				return $resultArray;
			}
    		
			mysqli_free_result($result);
		}
		
	}
	
	public function globalUpdate($tableName,$updateString,$whereCond=''){
		
		$q = 'UPDATE '.$tableName.' SET '.$updateString.' ' .$whereCond.'';
		
		if(!mysqli_query(self::$con,$q)){
			
			return array('error_num'=>mysqli_errno(self::$con),'error_msg'=>mysqli_error(self::$con),'query'=>$q);
		}else {
			
			return mysqli_affected_rows(self::$con);
		}
	}
	
	public function globalDelete($tableName, $whereCond ){
		
		$q = 'DELETE FROM '.$tableName.$whereCond.'';
		
		if(!mysqli_query(self::$con,$q)){
			
			return array('error_num'=>mysqli_errno(self::$con),'error_msg'=>mysqli_error(self::$con),'query'=>$q);
		}else {
			
			return 1;
		}
		
	}

    public function sendEmail($email,$type,$emailParams = array(), $attach = ''){
		
		$subjects = array('resetpass'=>'Change password for Fairmind',
						  'resetpasssuccess'=>'Clickcard password has been reset successfully',
						  'welcome'=>'Welcome to FairMind' ,
						  'contact' => 'Fairmind - Contact',
						  'friendRequest' => 'Fairmind Friend Request');	
		
				
		require_once('emailer/class.phpmailer.php');
		
		// optional, gets called from within class.phpmailer.php if not already loaded
		require_once("emailer/class.smtp.php"); 

		$mail  = new PHPMailer();
		
		if(!isset($emailParams['name']))
		$emailParams['name'] = '';
		
		try {
		  
		  //$mail->AddReplyTo("info@fairmind.com","Fairmind");
		  $mail->AddAddress($email, $emailParams['name']);
		  $mail->SetFrom("noreply@fairmind.co","Fairmind");
		  $mail->Subject = $subjects[$type];
		  $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
		  $mail->MsgHTML($this->getMsgBody($email,$type,$emailParams));
		  //$mail->AddAttachment('images/phpmailer.gif');      // attachment
		  //$mail->AddAttachment('images/phpmailer_mini.gif'); // attachment
		  $mail->Send();
		  //echo "Message Sent OK<p></p>\n";
		} catch (phpmailerException $e) {
		  //echo $e->errorMessage(); //Pretty error messages from PHPMailer
		} catch (Exception $e) {
		 // echo $e->getMessage(); //Boring error messages from anything else!
		}
		

		/*$body  = $this->getMsgBody($email,$type,$qr_url);
		
		$body  = eregi_replace("[\]",'',$body);

		// telling the class to use SMTP
		$mail->IsSMTP(); 

		//$mail->SMTPDebug  = 2;                  
		
		$mail->Host = 'smtp.rush2exams.com';

		$mail->port = 587;

		$mail->SMTPAuth = true;

		$mail->Username = 'info@rush2exams.com';

		$mail->Password = 'rush2exams22';

		//$mail->Host       = "smtp.gmail.com"; // sets the SMTP server
		//$mail->Port       = 25;                    // set the SMTP port for the GMAIL server
		//$mail->Username   = "abhilash@morbits.net"; // SMTP account username
		//$mail->Password   = "@bhilash1";        // SMTP account password

		$mail->SetFrom('info@fairmind.com', 'Fairmind');

		//$mail->AddReplyTo("info@clickcards.com","Clickcards");

			
		$mail->Subject    = $subjects[$type];
		// optional, comment out and test
		$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; 

		$mail->MsgHTML($body);

		$address = $email;

		$mail->AddAddress($address);
		
		if($attach != '')
		
		$mail->AddAttachment($attach);      // attachment
		//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

		if(!$mail->Send()) {
		  //echo "Mailer Error: " . $mail->ErrorInfo;
		
		} else {
		
		 //echo "Message sent!";
		}*/
		
		
	}
	
	
	public function getMsgBody($email,$type,$emailParams){
		
		if($type == 'resetpass') {
		
			$tocken = uniqid();
			
			$email_enc = base64_encode($email);
			
			$dor	= date('y-m-d H:i:s');
			
			$paramArray = array('email','tocken','reset_date');
				
			$valuesArray = array($email,$tocken,$dor);  
		
			$res = $this->globalInsert('fm_password_reset',$paramArray,$valuesArray);
			
			/*echo "<pre>";
			
			print_r($res);	*/	
			
			$url = 'http://fairmind.co/resetpass.php?userId='.$email_enc.'&resetToken='.$tocken;
		
			$msgBody = '<html>
						<body>
						<div style="width: 580px; margin: 0 auto;">
						  <div style="font-size: 16px; padding-left: 12px;padding-bottom:10px;padding-top:10px; color:#e5353f;font-weight:bold" align="center"> <span style="float:middle"><img src="http://fairmind.co/cust-imgs/fm_email.png"/></span> </div>
						  <div style="padding: 13px 13px 13px 13px; border: 1px solid #adadad; border-width: 1px 0; margin-bottom: 18px;">
							<h2 style="margin: 0 0 13px 0px; padding: 0; font-family: Arial, Helvetica, sans-serif; color: #231f20; font-size: 14px;">Hello '.$email.'</h2>
							You can reset your password for Fairmind by clicking this link:<br/>
							<br/>
							<b> <a href="'.$url.'">'.$url.'</a> </b> <br />
							<br />
							If you didn\'t request the reset link, you can ignore it.<br/>
							<br/>
							<br/>
							<span style="color:#e5353f">The Fairmind Team</span> </div>
						</div>
						</div>
						</body>
						</html>';
					
				
		
		}else if($type == 'resetpasssuccess'){
			
			/*$msgBody = '<html>
							<body>
								<div style="width: 580px; margin: 0 auto;">
  									<div style="font-size: 16px; padding-left: 12px;padding-bottom:10px;padding-top:10px; color:#e5353f;font-weight:bold" align="left">Password Reset Confirmation
  										<span style="float:right">Clickcard Logo</span>
  									</div>
  								<div>
									<div style="padding: 13px 13px 13px 13px; border: 1px solid #adadad; border-width: 1px 0; margin-bottom: 18px;">     
										<h2 style="margin: 0 0 13px 0px; padding: 0; font-family: Arial, Helvetica, sans-serif; color: #231f20; font-size: 14px;">Hi,</h2>
										Your password has been successfully changed!<br/><br/>
										You can now login to your account normally via our Control Panel using your shiny new password.
										<br /><br />
										In the unlikely case you did not request and commit this password change - please contact us immediately at security@clickcard.com and provide us the email address you use for logging in to your account.
										<br/><br/>
										 Always feel free to contact us,
										<br/>
										<span style="color:#e5353f">The Clickcard Team</span>
									</div>
  								</div>
							</div>
						</body>
					</html>';*/
			
		}else if($type == 'welcome'){
			
			
			$msgBody = '<html>
							<body>
							<div style="width: 580px; margin: 0 auto;">
							  <div style="font-size: 16px; padding-left: 12px;padding-bottom:10px;padding-top:10px; color:#e5353f;font-weight:bold" align="center">
								<span style="float:middle"><img src="http://fairmind.co/cust-imgs/fm_email.png"/></span>
							   </div>
							  <div>
								<div style="padding: 13px 13px 13px 13px; border: 1px solid #adadad; border-width: 1px 0; margin-bottom: 18px;">
								  <h2 style="margin: 0 0 13px 0px; padding: 0; font-family: Arial, Helvetica, sans-serif; color: #231f20; font-size: 14px;">
								  Hello '.$emailParams['name'].' </h2>
								  Thank you so much for registering with Fairmind<br/>
								  <br/>
								  With Fairmind, you can describe and evaluate your friends and check how they describe and evaluate you.<br/>
								  <br/>
								  Your fairmind credentails are given below <br/>
								  <br/>
								  username: '.$email.' <br/>
								  password :'.$emailParams['password'].' <br/>
								  <br/>
								  Have a good time on Fairmind and enough! <br/><br/>
								  And don\'t forget  \'Don\'t judge, just describe\'<br/>
								  <br/>
								  <br/>
								  <span style="color:#e5353f">The FairMind Team</span> </div>
							  </div>
							</div>
							</body>
							</html>';
		
		}else if($type == 'contact'){
			
			
			$msgBody = '<html>
						<body>
						<div style="width: 580px; margin: 0 auto;">
						  <div style="font-size: 16px; padding-left: 12px;padding-bottom:10px;padding-top:10px; color:#e5353f;font-weight:bold" align="center"> <span style="float:middle"><img src="http://fairmind.co/cust-imgs/fm_email.png"/></span> </div>
						  <div>
							<div style="padding: 13px 13px 13px 13px; border: 1px solid #adadad; border-width: 1px 0; margin-bottom: 18px;">
							  <h2 style="margin: 0 0 13px 0px; padding: 0; font-family: Arial, Helvetica, sans-serif; color: #231f20; font-size: 14px;"> Hello Fairmind Team </h2>
							 You have a Query<br/>
							  <br/>
							  name : '.$emailParams['name'].' <br/>
							  <br/>
							  Email : '.$emailParams['email'].' <br/>
							  <br/>
							  Phone :'.$emailParams['phone'].' <br/>
							  <br/>
							  Query : '.$emailParams['comments'].' <br/>
							  <br/>
							  <br/>    
						  </div>
						</div>
						</body>
						</html>';
		}else if($type == 'friendRequest'){
			
			$lnk = 'http://fairmind.co/login.php?rdl=req&userId='.$emailParams['myfriendemail'];
			
			$msgBody = '<html>
						<body>
						<div style="width: 580px; margin: 0 auto;">
						<div style="font-size: 16px; padding-left: 12px;padding-bottom:10px;padding-top:10px; color:#e5353f;font-weight:bold" align="center"> <span style="float:middle"><img src="http://fairmind.co/cust-imgs/fm_email.png"/></span> </div>
						<div>
						  <div style="padding: 13px 13px 13px 13px; border: 1px solid #adadad; border-width: 1px 0; margin-bottom: 18px;">
							<h2 style="margin: 0 0 13px 0px; padding: 0; font-family: Arial, Helvetica, sans-serif; color: #231f20; font-size: 14px;"> Hello '.$emailParams['myfriendname'].' </h2>
							'.$emailParams['myname'].' wants to be friends with you on Fairmind.<br/>
							<br/>
							Click the following lick to accept or reject this user as your friend:<br/>
							<br/>
							'.$lnk.'<br/>
							<br/>
							<br/>
							Sincerely,<br/>
							Fairmind Team<br/>
						  </div>
						</div>
						</body>
						</html>';
		}
		return $msgBody;	
	}
  
	
	public function checkResetPassword($user_email,$reset_tocken) {
		
		
		
		$user_email = base64_decode($user_email);
		
		$paramArray_0 = array('email');
		
		$whereCond_0 = 'WHERE email="'.$user_email.'" AND tocken= "'.$reset_tocken.'" AND status = 1';
		
		$num = $this->globalSelect('password_reset',$paramArray_0,$whereCond_0,true);
		
		return $num;
	}
	
	public function deleteDir($id,$type) {
    		
		 echo $dirPath  = dirname(__FILE__).'/'.$type.'/'.$id;
		
		
		if (is_dir($dirPath) && file_exists($dirPath)) {
			//throw new InvalidArgumentException("$dirPath must be a directory");
			echo "here";
			
			if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
				$dirPath .= '/';
			}
			$files = glob($dirPath . '*', GLOB_MARK);
			
			foreach ($files as $file) {
				
				if (is_dir($file)) {
					self::deleteDir($file);
				} else {
					unlink($file);
				}
			}
			echo $dirPath;
			
			rmdir($dirPath);
			exit;
		}
		
	}
	
	public function pagination($query,$per_page=10,$page=1,$url='?'){  
		
		
		$query = "SELECT COUNT(*) as `num` FROM {$query}";
		$row = mysqli_fetch_array(mysqli_query(self::$con,$query));
		$total = $row['num'];
		$adjacents = "2";
		  
		$prevlabel = "&lsaquo; Prev";
		$nextlabel = "Next &rsaquo;";
		$lastlabel = "Last &rsaquo;&rsaquo;";
		  
		$page = ($page == 0 ? 1 : $page); 
		$start = ($page - 1) * $per_page;                              
		  
		$prev = $page - 1;                         
		$next = $page + 1;
		  
		$lastpage = ceil($total/$per_page);
		  
		$lpm1 = $lastpage - 1; // //last page minus 1
		
			 
		$pagination = "";
		if($lastpage > 1){  
			$pagination .= "<div class='pager wow fadeInUp animated' style='visibility: visible;'>";
			//$pagination .= "<li class='page_info'>Page {$page} of {$lastpage}</li>";
				  
				if ($page > 1) $pagination.= "<a href='{$url}page={$prev}'>{$prevlabel}</a>";
				  
			if ($lastpage < 7 + ($adjacents * 2)){  
				for ($counter = 1; $counter <= $lastpage; $counter++){
					if ($counter == $page)
						$pagination.= "<a class='current'>{$counter}</a>";
					else
						$pagination.= "<a href='{$url}page={$counter}'>{$counter}</a>";                   
				}
			  
			} elseif($lastpage > 5 + ($adjacents * 2)){
				  
				if($page < 1 + ($adjacents * 2)) {
					  
					for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++){
						if ($counter == $page)
							$pagination.= "<a class='current'>{$counter}</a>";
						else
							$pagination.= "<a href='{$url}page={$counter}'>{$counter}</a>";                   
					}
					$pagination.= "<li class='dot'>...</li>";
					$pagination.= "<a href='{$url}page={$lpm1}'>{$lpm1}</a>";
					$pagination.= "<a href='{$url}page={$lastpage}'>{$lastpage}</a>"; 
						  
				} elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
					  
					$pagination.= "<a href='{$url}page=1'>1</a>";
					$pagination.= "<a href='{$url}page=2'>2</a>";
					$pagination.= "<li class='dot'>...</li>";
					for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
						if ($counter == $page)
							$pagination.= "<a class='current'>{$counter}</a>";
						else
							$pagination.= "<a href='{$url}page={$counter}'>{$counter}</a>";                   
					}
					$pagination.= "<li class='dot'>..</li>";
					$pagination.= "<a href='{$url}page={$lpm1}'>{$lpm1}</a>";
					$pagination.= "<a href='{$url}page={$lastpage}'>{$lastpage}</a>";     
					  
				} else {
					  
					$pagination.= "<a href='{$url}page=1'>1</a>";
					$pagination.= "<a href='{$url}page=2'>2</a>";
					$pagination.= "<li class='dot'>..</li>";
					for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
						if ($counter == $page)
							$pagination.= "<a class='current'>{$counter}</a>";
						else
							$pagination.= "<a href='{$url}page={$counter}'>{$counter}</a>";                   
					}
				}
			}
			  
				if ($page < $counter - 1) {
					$pagination.= "<a href='{$url}page={$next}'>{$nextlabel}</a>";
					$pagination.= "<a href='{$url}page=$lastpage'>{$lastlabel}</a>";
				}
			  
			$pagination.= "</div>";       
	
		}
		  
		return $pagination;
	}
	
	//specific functions
	
	public function getStarts($activeStar = 0){
		
		$stars = '';
		
				
		if($activeStar == 1) 
			$activeStar = 5;
		elseif($activeStar == 2) 
			$activeStar = 4;
		elseif($activeStar == 3) 
			$activeStar = 3;
		elseif($activeStar == 4) 
			$activeStar = 2;
		elseif($activeStar == 5) 
			$activeStar = 1;
		else
			$activeStar = 6;	
							
				
			
		for($i= 1 ;$i<= 5;$i++) {
		
			if($activeStar > $i ) {
				$stars .= '<span>&#9734;</span>';
			}else {
				$stars .= '<span class="starfilled">&#9733;</span>';
			}
		
		}
		
		return $stars;
	}
	
	public function generateRandomString($length = 6) {
	    
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    
	    $charactersLength = strlen($characters);
	    
	    $randomString = '';
	    
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    
	    return $randomString;
	}
	
	public function setAverages($userId) {
		
		$sqlLook = 'SELECT `fm_describes`.friend_id,
						avg(fm_describe_looks.clothing) as clothing,
						avg(fm_describe_looks.body) as body ,
						avg(fm_describe_looks.charisma) as charisma,
						avg(fm_describe_looks.hairstyle) as hairstyle 
				FROM `fm_describes` 
				JOIN fm_describe_looks ON fm_describe_looks.describe_id = fm_describes.describe_id AND fm_describes.status = 1 AND`fm_describes`.friend_id ='.$userId;
			
		if($result = mysqli_query(self::$con,$sqlLook)){
			
				$resultArray = array();
				
				$cnt = 0;		
				
				while($row = mysqli_fetch_object($result)) {
					
					foreach($row as $field => $value) {
						
						$resultArray[$cnt][$field] =  $value; 
					}
				    
					$cnt++;
				}
				
		
			
		}
		$sqlPers = 'SELECT `fm_describes`.friend_id,
						avg(fm_describes_personality.brainy) as brainy,
						avg(fm_describes_personality.friendly) as friendly ,
						avg(fm_describes_personality.funny) as funny,
						avg(fm_describes_personality.trusty) as trusty 
				FROM `fm_describes` 
				JOIN fm_describes_personality ON fm_describes_personality.describe_id = fm_describes.describe_id AND fm_describes.status = 1 AND`fm_describes`.friend_id ='.$userId;
			
		
		if($result1 = mysqli_query(self::$con,$sqlPers)){
			
				$resultArray1 = array();
				
				$cnt = 0;		
				
				while($row1 = mysqli_fetch_object($result1)) {
					
					foreach($row1 as $field1 => $value1) {
						
						$resultArray1[$cnt][$field1] =  $value1; 
					}
				    
					$cnt++;
				}
				
		
			
		}
		
				
		$friendly	= (int)round($resultArray1[0]['friendly']);
		$brainy  	=  (int)round($resultArray1[0]['brainy']);
		$funny 		= (int)round($resultArray1[0]['funny']);
		$trusty     =  (int)round($resultArray1[0]['trusty']);
		$clothing   = (int)round($resultArray[0]['clothing']);
		$hairstyle  =  (int)round($resultArray[0]['hairstyle']);
		$body 		= (int)round($resultArray[0]['body']);
		$charisma   =  (int)round($resultArray[0]['charisma']);
				
		$paramArray = array('user_id','friendly','brainy','funny','trusty','clothing','hairstyle','body','charisma');
		
		
		$sqlAVG = '
		INSERT INTO fm_user_avgs VALUES ('.$userId.','.$friendly.','.$brainy.','.$funny.','.$trusty.','.$clothing.','.$hairstyle.','.$body.','.$charisma.') ON DUPLICATE KEY UPDATE user_id='.$userId.',friendly='.$friendly.', brainy='.$brainy.',funny='.$funny.',trusty='.$trusty.',clothing='.$clothing.',hairstyle='.$hairstyle.',body='.$body.',charisma='.$charisma.'';
				   	   
			   
				   
		if(!mysqli_query(self::$con,$sqlAVG)){
			
			echo $sqlAVG;
			exit;
		}
			
			//echo $sqlAVG;
		//exit;	
	}
	

	
}