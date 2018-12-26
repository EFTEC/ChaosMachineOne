<?php
class LoremIpsum {
	

	public static function generatetext($startLorem='Lorem ipsum dolor ',$array=[],$paragraph=false,$nWords=40) {
		
		if($startLorem!=='') {
			$counter=3;
			$u=false;
			$txt=$startLorem;
		} else {
			$u=true;
			$counter=0;
			$txt='';
		}
		$c=count($array);
		
		for($i=$counter;$i<$nWords;$i++) {
			$r=rand(0,$c-1);
			$newWord=$array[$r];
			$newWord=($u)?ucfirst($newWord):$newWord;
			$txt.=$newWord;
			$r2=rand(0,6);
			$u=false;
			switch ($r2) {
				case 0:
					$txt.='. ';
					$u=true;
					break;
				case 1:
					$txt.=', ';
					break;	
				default:
					$txt.=' ';
			}
		}
		return $txt;
		
	}
	
}