<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goodby\CSV\Import\Standard\Interpreter;
use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\LexerConfig;
use App\fb_post;
use DB;
use App\Console\Kernel;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        /* Step 1. - Running facebook group post parser and generate group_facebook_statuses.csv */
        //$command = escapeshellcmd('python /var/www/facebook-reddit-bot/facebook-page-post-scraper/get_fb_posts_fb_group.py');
        //$output = shell_exec($command);
       // echo $output;

        /* Step 2. - Parse CSV File Generated in by facebook scraper */
        $posts = array();
        $config = new LexerConfig();
        $lexer = new Lexer($config);
        $interpreter = new Interpreter();
        $interpreter->addObserver(function(array $row) use (&$posts) {
            $posts[] = array(
                'status_id' => intval($row[0]),
                'status_message'        => preg_replace('/[^[:alnum:][:space:]]/u', '', $row[1]),
                'status_author'        => preg_replace('/[^[:alnum:][:space:]]/u', '', $row[2]),
                'link_name'        => preg_replace('/[^[:alnum:][:space:]]/u', '', $row[3]),
                'status_type'        => $row[4],
                'status_link'        => $row[5],
                'status_published'        => date("Y-m-d H:i:s", strtotime($row[6])),
                'num_reactions'        => intval($row[7]),
                'num_comments'        => intval($row[8]),
                'num_shares'        => intval($row[9]),
                'num_likes'        => intval($row[10]),
                'num_loves'        => intval($row[11]),
                'num_wows'        => intval($row[12]),
                'num_hahas'        => intval($row[13]),
                'num_sads'        => intval($row[14]),
                'num_angrys'        => intval($row[15]),

            );
        });
        //$lexer->parse('group_facebook_statuses.csv', $interpreter);
        
        
        /* Step 3. - Loop parsed post data and insert into mysql database */
        $i = 0;
        foreach($posts as $post) {
            //$i++;
            if($i == 0) continue; //first row is just columns
             
            $existingPost = fb_post::where("status_link", $post["status_link"])->count();
            
            if(!$existingPost) {
                $fb_post = new fb_post;
                foreach($post as $column=>$val) $fb_post->$column = $val;
                $fb_post->save();
            }
            
        }
        
        /* Grab Existing Links from Database and show in view */
        //$pending_fb_posts = fb_post::where("reddit_status", "pending")->where("link_name", "!=", "")->get();
        
        
        /*
        $pending_fb_posts = fb_post::where("reddit_status", "pending")
            ->where("link_name", "!=", "")
            ->where("status_link", "NOT LIKE", "%facebook.com%")->paginate(3);    
        
        $submittedCount = 0;
        foreach($pending_fb_posts as $post) {
            
           $response = exec("python ../reddit_poster.py ".escapeshellarg($post->status_link)." ".escapeshellarg($post->link_name));
           if($response == "TRUE") {
                $post->reddit_status = "success";
                $post->save();
           }
        }
        
        die();
*/
        
            $filePath = "/var/www/facebook-reddit-bot/cron.log";
        file_put_contents($filePath, "test");
        
        return view('home', ["db"=>new DB]);
    }
    
    public function RedditLogin() {
        $username =  DB::table('config')->where("name", "=", "reddit_username")->first()->value;
        $password =  DB::table('config')->where("name", "=", "reddit_password")->first()->value;
        
        //extract data from the post
        //set POST variables
        $url = 'https://www.reddit.com/api/login/'.$username;
        $fields = array(
            'op' => "login",
            'desc' => "https://www.reddit.com/r/".$username."/submit",
            'user' => $username,
            'passwd' => $password,
            'api_type' => "json"
        );
        
        //url-ify the data for the POST
        $fields_string = null;
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');
        
        //open connection
        $ch = curl_init();
        
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Host: www.reddit.com',
            'User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64; rv:51.0) Gecko/20100101 Firefox/51.0',
            'Accept: application/json, text/javascript, */*; q=0.01',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'Referer: https://www.reddit.com/',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        ));
        
        //execute post
        $result = curl_exec($ch);
        
        //close connection
        curl_close($ch);
        
            $config = DB::table('config')->where("name", "reddit_cookie")->first();
            if(!$config)  DB::table('config')->insert(["name"=>"reddit_cookie", "value"=>$result]);
                else DB::table('config')->where("name", "reddit_cookie")->update(['value' => $result]);

    }
    
    
    public function redditImageUpload($image_name, $type) {
            $ch =  curl_init();

            $reddit_session = DB::table('config')->where("name", "=", "reddit_cookie")->first()->value;
            $cookie_session = json_decode($reddit_session);
            $cookie = $cookie_session->json->data->cookie;
            $modhash = $cookie_session->json->data->modhash;
            //$explode = explode(",", $cookie);
            //$cookie_session = end($explode);
            
            $url = 'https://www.reddit.com/api/image_upload_s3.json';

            //extract data from the post
            //set POST variables
            $fields = array(
                'filepath' => ltrim($image_name, "/"),
                'mimetype' => $type,
                'raw_json' => "1",
            );
                
            $result = exec('curl "https://www.reddit.com/api/image_upload_s3.json"  -H "Host: www.reddit.com" -H "User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64; rv:51.0) Gecko/20100101 Firefox/51.0" -H "Accept: application/json, text/javascript, */*; q=0.01" -H "Accept-Language: en-US,en;q=0.5" --compressed -H "Referer: https://www.reddit.com/" -H "Content-Type: application/x-www-form-urlencoded; charset=UTF-8" -H "X-Modhash: '.$modhash.'" -H "X-Requested-With: XMLHttpRequest" -H "Cookie: reddit_session='.urlencode($cookie).'; secure_session=1;" -H "Connection: keep-alive" --data "filepath='.ltrim($image_name, "/").'&mimetype='.$type.'&raw_json=1"');
            
           // echo "<b>Reddit API Pre-Upload: </b><br>".$result;
    
            $this->redditUploadData = json_decode($result);
        }
        
        
        public function redditAWSUpload() {
    
            $headers = array(
                    'Host: reddit-uploaded-media.s3-accelerate.amazonaws.com',
                    'User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64; rv:51.0) Gecko/20100101 Firefox/51.0',
                    'Accept: application/xml, text/xml, */*; q=0.01',
                    'Accept-Language: en-US,en;q=0.5',
                    'Accept-Encoding: gzip, deflate, br',
                    'Referer: https://www.reddit.com/',
                    'Origin: https://www.reddit.com/',
                    'Content-type: multipart/form-data',
                    'Connection: Keep-Alive'
            ); // cURL headers for file uploading
            
            $postfields1 = array("file" => "".$this->imageFile."", "filename" => $this->imgName);
            
            $postfields2 = Array();
            foreach($this->redditUploadData->fields as $field) $postfields2[$field->name] = $field->value;
            
            $fields = array_merge($postfields2, $postfields1);
        
            //url-ify the data for the POST
            //$fields_string = null;
            //foreach($fields as $key=>$val) { $fields_string .= $key.'='.$val.'&'; }
            //rtrim($fields_string, '&');
            
            $ch = curl_init();
            $options = array(
                CURLOPT_URL => "https:".$this->redditUploadData->action,
                CURLOPT_HEADER => true,
                CURLOPT_POST => 1,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => $fields,
                CURLOPT_INFILESIZE => strlen($this->imageFile),
                CURLOPT_RETURNTRANSFER => true
            ); // cURL options
            curl_setopt_array($ch, $options);
            $res = curl_exec($ch);
            if(!curl_errno($ch))
            {
                $info = curl_getinfo($ch);
                if ($info['http_code'] == 200)
                    $errmsg = "File uploaded successfully";
            }
            else
            {
                $errmsg = curl_error($ch);
            }
            curl_close($ch);
            
            $arr1 = explode("Location: ", $res);
            $arr2 = explode(" ", $arr1[1]);
            $location = explode("\n", $arr2[0]);
            $location = $location[0];
            return $location;
  
    }
    
    public function fbGroupParser() {
            $oldCount = DB::table('fb_posts')->count();
            
            /* Step 1. - Loop FB group ID pages and parse results */
            $group_ids =  DB::table('config')->where("name", "=", "fb_group_ids")->first()->value;

            foreach(explode("\n", $group_ids) as $group_id) {
                if(!intval($group_id)) continue;
                $app_id =  DB::table('config')->where("name", "=", "fb_app_id")->first()->value;
                $app_secret =  DB::table('config')->where("name", "=", "fb_app_secret")->first()->value;
                
                $response = exec("python /var/www/facebook-reddit-bot/facebook-page-post-scraper/get_fb_posts_fb_group.py ".escapeshellarg($app_id)." ".escapeshellarg($app_secret)." ".escapeshellarg($group_id));
$json = json_decode($response);
if($json["error"]) die(print_r($json));

                /* Step 2. - Loop resulting CSV from fb group page parse */
                $posts = array();
                 $config = new LexerConfig();
                 $lexer = new Lexer($config);
                 $interpreter = new Interpreter();
                 $interpreter->unstrict();
                 $interpreter->addObserver(function(array $row) use (&$posts) {
                     $posts[] = array(
                         'status_id' => intval($row[0]),
                         'status_message'        => preg_replace('/[^[:alnum:][:space:]]/u', '', $row[1]),
                         'status_author'        => preg_replace('/[^[:alnum:][:space:]]/u', '', $row[2]),
                         'link_name'        => preg_replace('/[^[:alnum:][:space:]]/u', '', $row[3]),
                         'status_type'        => $row[4],
                         'status_link'        => $row[5],
                         'status_published'        => date("Y-m-d H:i:s", strtotime($row[6])),
                         'num_reactions'        => intval($row[7]),
                         'num_comments'        => intval($row[8]),
                         'num_shares'        => intval($row[9]),
                         'num_likes'        => intval($row[10]),
                         'num_loves'        => intval($row[11]),
                         'num_wows'        => intval($row[12]),
                         'num_hahas'        => intval($row[13]),
                         'num_sads'        => intval($row[14]),
                         'num_angrys'        => intval($row[15]),
         
                     );
                 });
                 $lexer->parse('/var/www/facebook-reddit-bot/facebook-page-post-scraper/group_facebook_statuses.csv', $interpreter);

                 /* Step 3. - Loop parsed post data and insert into mysql database */
                 $i = 0;
                 //print_r($posts);
                 foreach($posts as $post) {
                     $i++;
                     if($i == 0) continue; //first row is just columns
                      
                     $existingPost = fb_post::where("status_link", $post["status_link"])->count();
                     
                     if(!$existingPost) {
                         $fb_post = new fb_post;
                         $fb_post->fb_group_id = $group_id;
                         foreach($post as $column=>$val) $fb_post->$column = $val;
                         $fb_post->save();
                     }
                     
                 }
            }
            $newCount = DB::table('fb_posts')->count();
            
            echo "New: ".($newCount - $oldCount)."'; Total in DB: ".$newCount;
    }
    
    
    
    public function fbPageParser() {
            $oldCount = DB::table('fb_posts')->count();

            /* Step 1. - Loop FB group ID pages and parse results */
            $page_ids =  DB::table('config')->where("name", "=", "fb_page_ids")->first()->value;
            foreach(explode("\n", $page_ids) as $page_id) {
                if(!intval($page_id)) continue;
                
                $app_id =  DB::table('config')->where("name", "=", "fb_app_id")->first()->value;
                $app_secret =  DB::table('config')->where("name", "=", "fb_app_secret")->first()->value;
                

                $response = exec("python /var/www/facebook-reddit-bot/facebook-page-post-scraper/get_fb_posts_fb_page.py ".escapeshellarg($app_id)." ".escapeshellarg($app_secret)." ".escapeshellarg($page_id));

$json = json_decode($response);
if($json["error"]) die(print_r($json));

                /* Step 2. - Loop resulting CSV from fb group page parse */
                $posts = array();
                 $config = new LexerConfig();
                 $lexer = new Lexer($config);
                 $interpreter = new Interpreter();
                 $interpreter->unstrict();
                 $interpreter->addObserver(function(array $row) use (&$posts) {
if(count($row) != 15) return;
                     $posts[] = array(
                         'status_id' => intval($row[0]),
                         'status_message'        => (preg_replace('/[^[:alnum:][:space:]]/u', '', $row[1])),
                         'link_name'        => preg_replace('/[^[:alnum:][:space:]]/u', '', (!isset($row[2]) ? " " : $row[2])),
                         'status_type'        => (!isset($row[3]) ? " " : $row[3]),
                         'status_link'        => (!isset($row[4]) ? " " : $row[4]),
                         'status_published'        => date("Y-m-d H:i:s", strtotime((!isset($row[5]) ? Date("Y-m-d H:i:s") : $row[5]))),
                         'num_reactions'        => intval((!isset($row[6]) ? " " : $row[6])),
                         'num_comments'        => intval((!isset($row[7]) ? " " : $row[7])),
                         'num_shares'        => intval((!isset($row[8]) ? " " : $row[8])),
                         'num_likes'        => intval((!isset($row[9]) ? " " : $row[9])),
                         'num_loves'        => intval((!isset($row[10]) ? " " : $row[10])),
                         'num_wows'        => intval((!isset($row[11]) ? " " : $row[11])),
                         'num_hahas'        => intval((!isset($row[12]) ? " " : $row[12])),
                         'num_sads'        => intval((!isset($row[13]) ? " " : $row[13])),
                         'num_angrys'        => intval((!isset($row[14]) ? " " : $row[14])),
         
                     );
                 });
                 $lexer->parse('/var/www/facebook-reddit-bot/facebook-page-post-scraper/page_facebook_statuses.csv', $interpreter);

            

                 /* Step 3. - Loop parsed post data and insert into mysql database */
                 $i = 0;
                 //print_r($posts);
                 foreach($posts as $post) {
                     $i++;
                     if($i == 0) continue; //first row is just columns
                      
                     $existingPost = fb_post::where("status_link", $post["status_link"])->count();
                     
                     if(!$existingPost) {
                         $fb_post = new fb_post;
                         $fb_post->fb_page_id = $page_id;
                         foreach($post as $column=>$val) $fb_post->$column = $val;
                         $fb_post->save();
                     }
                     
                 }
            }
            $newCount = DB::table('fb_posts')->count();
            
            echo "New: ".($newCount - $oldCount)."'; Total in DB: ".$newCount;
    }
    

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function pending()
    {
        
        /* Grab Existing Links from Database and show in view */
        //$pending_fb_posts = fb_post::where("reddit_status", "pending")->where("link_name", "!=", "")->get();
        $pending_fb_posts = fb_post::where("reddit_status", "=", "pending")
            ->where("link_name", "!=", "")
            ->where("status_link", "NOT LIKE", "%facebook.com%")->orderBy("created_at", "asc")->paginate(15);
            
            
        
        
        //print_r($posts);
                
        return view('pending', ["pending_fb_posts"=> $pending_fb_posts]);
    }
    
    
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function review()
    {
        
        /* Grab Existing Links from Database and show in view */
        //$pending_fb_posts = fb_post::where("reddit_status", "pending")->where("link_name", "!=", "")->get();
        $pending_fb_posts = fb_post::where("reddit_status", "=", "pending")
            ->where("link_name", "=", "")
            ->where("status_link", "LIKE", "%facebook.com%")->orderBy("created_at", "asc")->paginate(15);
            
                
        return view('review', ["pending_fb_posts"=> $pending_fb_posts]);
    }
    
    
       /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function history()
    {
        
        /* Grab Existing Links from Database and show in view */
        //$pending_fb_posts = fb_post::where("reddit_status", "pending")->where("link_name", "!=", "")->get();
        $pending_fb_posts = fb_post::where("reddit_status", "=", "success")
            ->where("link_name", "!=", "")
            ->where("status_link", "NOT LIKE", "%facebook.com%")->orderBy("updated_at", "desc")->orderby("id", "desc")->paginate(15);
        //print_r($posts);
                
        return view('history', ["pending_fb_posts"=> $pending_fb_posts]);
    }
    
    
       /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function config(Request $request)
    {
        
        $inputs = $input = $request->all();
        foreach($inputs as $key=>$val) {
            $config = DB::table('config')->where("name", $key)->first();
            if(!$config)  DB::table('config')->insert(["name"=>$key, "value"=>$val]);
                else DB::table('config')->where("name", $key)->update(['value' => $val]);
        }


    } 
    
    
    
}
