<?php
class ParseClass {

	public function parse(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, SITE); 
		curl_setopt  ($ch, CURLOPT_HEADER, true);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$result = curl_exec($ch);
		
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		$header = substr($result, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		$body = substr($result, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		
		curl_close($ch);
		
		$this->loqSave($header);
		if ($httpCode=='200') {
			$this->domResult($body);
		} else {
			print $this->html->render('error.html',array('message' => 'Сервер неотвечает!!! Код ответа '.$httpCode));
		}
	}


	private function domResult($body){
		require_once("libs/simple_html_dom.php");
		$body = str_get_html($body);
		
		$test = $this->db->exec("INSERT INTO parse SET `date`=NOW()");
		$parentId = $this->db->lastInsertId(); 

		$insert = $this->db->prepare('INSERT INTO links (`parent_id`, `title`, `href`) VALUES (:parent_id, :title, :href)');
		foreach ($body->find(SELECTOR) as $el) {
			$insert->execute(array(
		   		'parent_id' => $parentId,
		   		'title'  => $el->plaintext,
		   		'href' => $el->href
		   		));	
		}
		$body->clear();
		unset($body);

		$this->show('table_ajax.html');
	}


	public function show($html = 'table.html' ){
		$lastUpdate = $this->db->query("SELECT * FROM parse ORDER BY date DESC")->fetch(PDO::FETCH_OBJ);
		
		$links = $this->db->query("SELECT * FROM links WHERE parent_id='".$lastUpdate->id."'")->fetchAll();
		echo ($this->html->render($html, 
			array(
				'links' => $links,
				'date'  => $lastUpdate->date
				)
			));

	}

	
	private function loqSave($header){
		file_put_contents(LOG_FILE, $header, FILE_APPEND | LOCK_EX);
	}
}