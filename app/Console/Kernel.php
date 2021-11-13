<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\fb_post;
use DB;

use App\Http\Controllers\HomeController;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        
        /* Process Pending Reddit Links */
        $schedule->call(function () {
            
            
            $pending_fb_posts = fb_post::where("reddit_status", "=", "pending")
                ->where("link_name", "!=", "")
                ->where("status_link", "NOT LIKE", "%facebook.com%")->orderBy("created_at", "asc")->orderBy("id", "desc")->limit(3)->get();    
            
            $submittedCount = 0;
            foreach($pending_fb_posts as $post) {
                $post->reddit_status = "in-queue";
                $post->save();
                
                $reddit_id =  DB::table('config')->where("name", "=", "reddit_client_id")->first()->value;
                $reddit_secret =  DB::table('config')->where("name", "=", "reddit_client_secret")->first()->value;
                $reddit_username =  DB::table('config')->where("name", "=", "reddit_username")->first()->value;
                $reddit_password =  DB::table('config')->where("name", "=", "reddit_password")->first()->value;
                $subreddit =  DB::table('config')->where("name", "=", "reddit_subreddit")->first()->value;
                
               
                if(isImage($post->status_link)) {
                    $homeController = new HomeController;
                    
                    /* Reddit Image Upload API */
                    $homeController->RedditLogin();
                    $homeController->imageFile = $image = file_get_contents($post->status_link);
                    
                    $imageTypeArray = array
                    (
                        0=>'UNKNOWN',
                        1=>'GIF',
                        2=>'JPEG',
                        3=>'PNG',
                        4=>'SWF',
                        5=>'PSD',
                        6=>'BMP',
                        7=>'TIFF_II',
                        8=>'TIFF_MM',
                        9=>'JPC',
                        10=>'JP2',
                        11=>'JPX',
                        12=>'JB2',
                        13=>'SWC',
                        14=>'IFF',
                        15=>'WBMP',
                        16=>'XBM',
                        17=>'ICO',
                        18=>'COUNT' 
                    );
                    
                    $size = getimagesizefromstring($image);
                    $homeController->imageType = $type = "image/".strtolower($imageTypeArray[$size[2]]);
                    $parse_url = parse_url($post->status_link);
                    $homeController->imgName = $imgName = $parse_url["path"];
                    
                    $homeController->redditImageUpload($imgName, $type);
                    
                    $awsUpload = $homeController->redditAWSUpload();
                    $post->status_link = $awsUpload;

                }
            
                
               $response = exec("python /var/www/facebook-reddit-bot/reddit_poster.py ".escapeshellarg($post->status_link)." ".escapeshellarg($post->link_name)." ".escapeshellarg($reddit_id)." ".escapeshellarg($reddit_secret)." ".escapeshellarg($reddit_username)." ".escapeshellarg($reddit_password)." ".escapeshellarg($subreddit));
               if($response == "TRUE") {
                    $post->reddit_status = "success";
                    $post->save();
               }
               //sleep(rand(0, 960)); //sleep for a bit to add some variance between post times
            }
            
        })->everyMinute();
        
        /* Parse FB Group IDs for new links */
        $schedule->call(function () {
            $homeController = new HomeController;
            $homeController->fbGroupParser();
            $homeController->fbPageParser();
            
            //file_put_contents($filePath, "test");
        })->twiceDaily();
        
        

        /* Parse FB Page ID's for new links */
        
        
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}


  function isImage( $url )
  {
    $pos = strrpos( $url, ".");
    if ($pos === false)
      return false;
    $ext = strtolower(trim(substr( $url, $pos)));
    $imgExts = array(".gif", ".jpg", ".jpeg", ".png", ".tiff", ".tif"); // this is far from complete but that's always going to be the case...
    if ( in_array($ext, $imgExts) )
      return true;
    return false;
  }
