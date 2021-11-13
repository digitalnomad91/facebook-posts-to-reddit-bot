@extends('layouts.app')

@section('title', 'Home - ParserBot')

@section('content') <div class="container">

<a href="javascript:;" class="btn btn-primary" id="parse_groups">
    Parse FB Groups
</a>
<a href="#" class="btn btn-primary" id="parse_pages">
    Parse FB Pages
</a>

<script type="text/javascript">
$(document).ready(function() {
    $("#parse_groups").click(function() {
        $(this).html("<i class='fa fa-spinner fa-spin'></i> Please Wait...").attr("disabled", true).addClass("disabled");
        
        $.ajax({
        url: "/fb/parse/groups",
        context: this,
        success: function(data) {
            alert(data);
            $(this).html("Parse FB Groups").removeAttr("disabled").removeClass("disabled");
        }
        });
    });

    $("#parse_pages").click(function() {
            $(this).html("<i class='fa fa-spinner fa-spin'></i> Please Wait...").attr("disabled", true).addClass("disabled");
            $.ajax({
            url: "/fb/parse/pages",
            context: this,
            success: function(data) {
                alert(data);
                $(this).html("Parse FB Pages").removeAttr("disabled").removeClass("disabled");
            }
        });
    });
    
})
    
    
</script>    

    <div class="row" style="margin-top: 15px;">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">ParserBot Config</div>
                
                <div class="panel-body">
                
                <form action="/">
                   <h5 style="margin-top: 0px; font-weight: 800;">Facebook Config</h5>
                        <div class="form-group">
                          <label for="exampleInputEmail1">App ID</label>
                          <input type="text" name="fb_app_id" class="form-control" id="exampleInputEmail1" placeholder="App ID" value="@php echo DB::table('config')->where("name", "=", "fb_app_id")->first()->value @endphp">
                        </div>
                        <div class="form-group">
                          <label for="exampleInputPassword1">App Secret</label>
                          <input type="text" name="fb_app_secret" class="form-control" id="exampleInputPassword1" placeholder="App Secret" value="@php echo DB::table('config')->where("name", "=", "fb_app_secret")->first()->value 
@endphp">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Group ID's</label>
                            <textarea name="fb_group_ids" class="form-control">@php echo DB::table('config')->where("name", "=", "fb_group_ids")->first()->value @endphp</textarea>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Page ID's</label>
                            <textarea name="fb_page_ids" class="form-control">@php echo DB::table('config')->where("name", "=", "fb_page_ids")->first()->value @endphp</textarea>
                        </div>
                
                   <h5 style="font-weight: 800;">Reddit Config</h5>
                      <div class="form-group">
                        <label for="exampleInputEmail1">Subreddit</label>
                        <input type="text" name="reddit_subreddit" class="form-control" value="@php echo DB::table('config')->where("name", "=", "reddit_subreddit")->first()->value @endphp" placeholder="Subreddit">
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail1">Username</label>
                        <input type="text" name="reddit_username" class="form-control" value="@php echo DB::table('config')->where("name", "=", "reddit_username")->first()->value @endphp" placeholder="Username">
                      </div>
                      <div class="form-group">
                        <label for="exampleInputPassword1">Password</label>
                        <input type="password" name="reddit_password" class="form-control" value="@php echo DB::table('config')->where("name", "=", "reddit_password")->first()->value @endphp" placeholder="Password">
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail1">Client ID</label>
                        <input type="text" class="form-control" name="reddit_client_id" value="@php echo DB::table('config')->where("name", "=", "reddit_client_id")->first()->value @endphp" placeholder="Client ID">
                      </div>
                      <div class="form-group">
                        <label for="exampleInputPassword1">Client Secret</label>
                        <input type="password" class="form-control" name="reddit_client_secret" value="@php echo DB::table('config')->where("name", "=", "reddit_client_secret")->first()->value @endphp" placeholder="Client 
Secret">
                      </div>
                        
                        
                      <button type="submit" class="btn btn-default">Submit</button>
                        </form>
                       
                </div>
            </div>
            
            <script type="text/javascript">
            $(document).ready(function() {
                $("form").submit(function() {
                    $.ajax({
                        url: "/config?"+$(this).serialize(),
                        success: function() {
                            alert("Config updated!");
                            
                        }
                    })
                   return false
                });
            });
                
            </script>
    
        </div>
    </div> </div> @endsection
