<?

/************************ Настройки системы ************************/
$api_id 		= 'E51DAE8A-0988-C303-2445-5A8FBC1A49C8';
$my_phone 		= '79787388110';
$sys_email 		= 'order@nolte-crimea.ru'; 	// Системный почтовый ящик, от имени которого идет отправка почты
$my_email	 	= 'k323077@mail.ru';   		// Личный почтовый ящик, на который приходят сообщения
/*******************************************************************/

ini_set('display_errors','Off');
error_reporting('E_ALL');

$action_type = isset($_POST['action_type'])?$_POST['action_type']:null;

switch ($action_type) {
	
	// Отправка заказа/обращения
	case 'send-order':
	
		$name 		= $_POST['name'];	
		$tel 		= $_POST['tel'];
		$email 		= $_POST['email'];
		$address 	= $_POST['address'];
		//$file 		= $_POST['file'];
		$agree 		= $_POST['agree'];
		$url 		= $_POST['url'];	
		
		$uploaddir = './uploads';
    
		// cоздадим папку если её нет
		if( ! is_dir( $uploaddir ) ) mkdir( $uploaddir, 0777 );

		$files      = $_FILES; // полученные файлы
		$done_files = array();

		// переместим файлы из временной директории в указанную
		foreach( $files as $file ){
			$file_name = cyrillic_translit( $file['name'] );

			if( move_uploaded_file( $file['tmp_name'], "$uploaddir/$file_name" ) ){
				$done_files[] = realpath( "$uploaddir/$file_name" );
			}
		}
		
		$subject = 'Обращение с сайта nolte-crimea.ru';	
			
		$message = '
			<html>
				<head>
					<title>'.$subject.'</title>
				</head>
				<body>
					<p>Имя: '.$name.'</p>
					<p>Телефон: '.$tel.'</p>
					<p>E-mail: '.$email.'</p>
					<p>Адрес: '.$address.'</p>
					<p>Страница: '.$url.'</p>
				</body>
			</html>';	
	
		$path = current($done_files);
		
		// Отправка письма
		send_mail($my_email, $sys_email, $subject, $message, $path);
		unlink($path);
		
		// Отправка sms
		$body = file_get_contents("https://sms.ru/sms/send?api_id=".$api_id."&to=".$my_phone."&msg=".urlencode("Обращение с сайта nolte-crimea.ru")."&json=1");

		$json_data = array('name'=>$name, 'tel'=>$tel, 'email'=>$email, 
						   'address'=>$address, 'file'=>$file, 'agree'=>$agree);
		echo json_encode($json_data);	
			
		break;
		
	// Отправка заказа/обращения из главной страницы
	case 'send-order-main':
		$name 		= $_POST['name'];	
		$tel 		= $_POST['tel'];
		$email 		= $_POST['email'];
		$url 		= $_POST['url'];
		
		$subject = 'Обращение с сайта nolte-crimea.ru';	
			
		$message = '
			<html>
				<head>
					<title>'.$subject.'</title>
				</head>
				<body>
					<p>Имя: '.$name.'</p>
					<p>Телефон: '.$tel.'</p>
					<p>E-mail: '.$email.'</p>
					<p>Адрес: '.$address.'</p>
					<p>Страница: '.$url.'</p>
				</body>
			</html>';	
	
		$path = "";
		
		// Отправка письма
		send_mail($my_email, $sys_email, $subject, $message, $path);
		unlink($path);
		
		// Отправка sms
		$body = file_get_contents("https://sms.ru/sms/send?api_id=".$api_id."&to=".$my_phone."&msg=".urlencode("Обращение с сайта nolte-crimea.ru")."&json=1");

		$json_data = array('name'=>$name, 'tel'=>$tel, 'email'=>$email, 
						   'address'=>$address, 'file'=>$file, 'agree'=>$agree);
		echo json_encode($json_data);
		
		break;
	
	// По умолчанию
	default:
		break;
} // Конец Switch ($action_type)



function send_mail($mail_to, $mail_from, $thema, $msg, $path) { 
	// Вспомогательная функция для отправки почтового сообщения с вложением
	// Параметры - адрес получателя, тема письма, текст письма, путь к загруженному файлу
	if ($path) {  
		$fp = fopen($path,"rb");   
		if (!$fp) { 
			print "Cannot open file"; 
			exit(); 
		}   
		$file = fread($fp, filesize($path));   
		fclose($fp);   
	}  
	
	$name = basename($path); // в этой переменной надо сформировать имя файла (без пути)  
	$EOL = "\r\n"; // ограничитель строк, некоторые почтовые сервера требуют \n - подобрать опытным путём
	$boundary     = "--".md5(uniqid(time()));  // любая строка, которой не будет ниже в потоке данных.  
	$headers    = "MIME-Version: 1.0;$EOL";   
	$headers   .= "Content-Type: multipart/mixed; boundary=\"$boundary\"$EOL";  
	$headers   .= "From: $mail_from";  
	$multipart  = "--$boundary$EOL";
	$multipart .= "------------".$bondary."\nContent-Type:text/html; charset=utf-8\n";
	$multipart .= "Content-Transfer-Encoding: 8bit\n\n$msg\n\n";
	$multipart .= $EOL; // раздел между заголовками и телом html-части 
	$multipart .=  "$EOL--$boundary$EOL";   
	$multipart .= "Content-Type: application/octet-stream; name=\"$name\"$EOL";   
	$multipart .= "Content-Transfer-Encoding: base64$EOL";   
	$multipart .= "Content-Disposition: attachment; filename=\"$name\"$EOL";   
	$multipart .= $EOL; // раздел между заголовками и телом прикрепленного файла 
	$multipart .= chunk_split(base64_encode($file));   
	$multipart .= "$EOL--$boundary--$EOL";   
	if (!mail($mail_to, $thema, $multipart, $headers)) { //если не письмо не отправлено
		return false;           
	}  
	else { // если письмо отправлено
		return true;  
	}  
	exit;  
}


## Транслитирация кирилических символов
function cyrillic_translit( $title ){
    $iso9_table = array(
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Ѓ' => 'G',
        'Ґ' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Є' => 'YE',
        'Ж' => 'ZH', 'З' => 'Z', 'Ѕ' => 'Z', 'И' => 'I', 'Й' => 'J',
        'Ј' => 'J', 'І' => 'I', 'Ї' => 'YI', 'К' => 'K', 'Ќ' => 'K',
        'Л' => 'L', 'Љ' => 'L', 'М' => 'M', 'Н' => 'N', 'Њ' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
        'У' => 'U', 'Ў' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'TS',
        'Ч' => 'CH', 'Џ' => 'DH', 'Ш' => 'SH', 'Щ' => 'SHH', 'Ъ' => '',
        'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'ѓ' => 'g',
        'ґ' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'є' => 'ye',
        'ж' => 'zh', 'з' => 'z', 'ѕ' => 'z', 'и' => 'i', 'й' => 'j',
        'ј' => 'j', 'і' => 'i', 'ї' => 'yi', 'к' => 'k', 'ќ' => 'k',
        'л' => 'l', 'љ' => 'l', 'м' => 'm', 'н' => 'n', 'њ' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ў' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
        'ч' => 'ch', 'џ' => 'dh', 'ш' => 'sh', 'щ' => 'shh', 'ъ' => '',
        'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
    );

    $name = strtr( $title, $iso9_table );
    $name = preg_replace('~[^A-Za-z0-9\'_\-\.]~', '-', $name );
    $name = preg_replace('~\-+~', '-', $name ); // --- на -
    $name = preg_replace('~^-+|-+$~', '', $name ); // кил - на концах

    return $name;
}

function send_mail_without($mail_to, $mail_from, $thema, $msg) { 
	$headers = "Content-type: text/html; charset=utf-8\r\n";
	$headers .= "From: $mail_from\r\n";
	mail($mail_to, $thema, $msg, $headers);	
}

?>