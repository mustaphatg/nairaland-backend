<?php
namespace App\Services;
use App\Services\ScrapeRequest;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class Utility {


	function __construct() {
		$this->scrapeRequest = new ScrapeRequest();
	}


	public function homePage() {
		$crawl = $this->scrapeRequest->get("");
		$links = $crawl->filter(".featured.w a");

		$results = [];

		foreach ($links as $node) {

			$data = [
				"href" => $node->getAttribute("href"),
				"text" => $node->nodeValue,
			];

			$results[] = $data;
		}
		
		return $results;
	}
	
	
	
	public function category($name){
		$crawl = $this->scrapeRequest->get($name);
		$links = $crawl->filter(".featured.w a");

		$results = [];

		foreach ($links as $node) {

			$data = [
				"href" => $node->getAttribute("href"),
				"text" => $node->nodeValue,
			];

			$results[] = $data;
		}
		
		return $results;
    }
	
	
	
	public function section(){
		$crawl = new Crawler(file_get_contents("sub.html"));
		$table = $crawl->filter("table.boards");
		
		$tr = $table->filter("tr");
		$links = [];
		
		// general
			$ge = $tr->eq(1);
			$ge_links = $ge->filter("a");
			
			$links["general"] = [];
			
			foreach($ge_links as $gl){
				$v = new \stdClass();
				$v->href = $gl->getAttribute("href");
				$v->title = $gl->getAttribute("title");
				$v->text = $gl->nodeValue;
				
				$links["general"][] = $v;
			}
		
		// entertainment
			$ge = $tr->eq(2);
			$ge_links = $ge->filter("a");
			
			$links["entertainment"] = [];
			
			foreach($ge_links as $gl){
				$v = new \stdClass();
				$v->href = $gl->getAttribute("href");
				$v->title = $gl->getAttribute("title");
				$v->text = $gl->nodeValue;
				
				$links["entertainment"][] = $v;
			}
			
		
		// technology
			$ge = $tr->eq(3);
			$ge_links = $ge->filter("a");
			
			$links["science"] = [];
			
			foreach($ge_links as $gl){
				$v = new \stdClass();
				$v->href = $gl->getAttribute("href");
				$v->title = $gl->getAttribute("title");
				$v->text = $gl->nodeValue;
				
				$links["science"][] = $v;
			}
			
		
	    return $links;
		
    }
    
    
    
    
    
	
	private function formatReply($user, $post){
		$reply = new \stdClass();
		
		// user
			$re = new Crawler($user);
			Log::debug($re->html());
			$user = $re->filter(".user");
			
			if($user->count() > 0 ){
				$reply->user =  $user->text();
				$time = $re->filter("span.s");
				$reply->time = $time->text();
			}else{
				// the end
				return null;
			}

		
		// body
			$bd = $post;
			$bq = $bd->filter(".narrow");
			$reply->body = $bq->html();
		
		return $reply;
	}
	
	
	public function topic($slug){
		$crawl = $this->scrapeRequest->get($slug);
		
		//$crawl = new Crawler(file_get_contents("to.html"));
		
		$table = $crawl->filter("table[summary=posts]");      
		
		$object = new \stdClass();
		
		if($table->count() > 0){
			$tr = $table->filter("tr");
			
			// get topic and creator
				$topic = $tr->eq(0);
				
				// title 
					$links = $topic->filter("a");
					$title = $links->eq(3);
					$object->title = $title->text();
					
				// creator 
				$creator = $links->eq(4);
				$object->creator = $creator->text();
			
			
			// post body
				$body = $tr->eq(1);
				$post = $body->filter(".narrow");
				$object->body = $post->html();
			
			// post images
				$img = $body->filter("img");
				$img_array = [];
				
				if($img->count() > 0){
					foreach($img as $el){
						$img_array[] = $el->getAttribute("src");     
					}
				}
				// assign image
				$object->images = $img_array;
				
				
			//replies
			$replies = [];
				foreach($tr as $key => $value){
					
					if($key == 1 || $key ==0){
						continue;
					}
					
					if(($key % 2) != 0){
						continue;
					}
					
					Log::debug($key);
					$current_row = $value;
					$next_row = $tr->eq($key + 1);
						
					$reply = $this->formatReply($current_row , $next_row );      		
					
					if($reply != null){
						$replies[] = $reply;
					}
				}

			
			//assign replies
			$object->replies = $replies;
				
			return $object;
		}
		else
		{
			echo 88;
		}
		
    }
	
	
	

}


?>