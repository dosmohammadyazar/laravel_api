<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use Redirect;
use Session;
use App\Requests;
use App\Notification;
use App\RecentActivity;
use App\Timesheet;
use App\RequestDetails;
use App\Meeting;
use App\MeetingUser;
use App\MeetingVideo;
use App\User;
use Crypt;
use Auth;
use URL;



use OpenTok\OpenTok;
use OpenTok\MediaMode;
 use OpenTok\ArchiveMode;
use OpenTok\Role;
 use OpenTok\Archive;
use OpenTok\OutputMode;



class MeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
       if(Auth::user()->hasRole('Admin')):
        $meeting = Meeting::orderBy('id','DESC')->get();
       else:
         $meeting = Meeting::join('meeting_user', 'meeting.id', '=', 'meeting_user.meeting_id')->where('meeting_user.meeting_user',Auth::id())->select('*','meeting.id as id')->get();

       endif;

        return view('meeting.meetingList',compact('meeting'));


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        if(Auth::user()->hasRole('Account Manager') || Auth::user()->hasRole('Admin')):
         $adminUser = User::whereHas('roles', function($q){
                $q->where('name', 'Admin');
                })->where('id','!=',Auth::user()->id)->orderBy('id', 'DESC')->get();

         $accountManager = User::whereHas('roles', function($q){
                $q->where('name', 'Account Manager');
                })->where('id','!=',Auth::user()->id)->orderBy('id', 'DESC')->get();

          $teamUser = User::whereHas('roles', function($q){
                $q->where('name', 'User');
                })->orderBy('id', 'DESC')->get();
       if(Auth::user()->hasRole('Admin')):
          $clientUser = User::whereHas('roles', function($q){
                $q->where('name', 'Client');
                })->orderBy('id', 'DESC')->get();
       else:
          $clientUser = User::join('request', 'users.id', '=', 'request.client_id')->where('request.pm_id',Auth::id())
               ->select('*','users.id as id')->groupBy('request.client_id')->get();

       endif;



          return view('meeting.create',compact('adminUser','accountManager','teamUser','clientUser'));
    elseif(Auth::user()->hasRole('Client')):
        $adminUser = User::whereHas('roles', function($q){
            $q->where('name', 'Admin');
            })->where('id','!=',Auth::user()->id)->orderBy('id', 'DESC')->get();


        $accountManager =  User::join('request', 'users.id', '=', 'request.pm_id')->where('request.client_id',Auth::id())
        ->select('*','users.id as id')->groupBy('users.id')->get();

        return view('meeting.create',compact('adminUser','accountManager'));
    endif;
    }


    public function meeting_call($id)
    {
        $meetId = Crypt::decrypt($id);
        $meeting = Meeting::findOrfail($meetId);




        if($meeting->date == date('Y-m-d')):
            $token = $meeting->token;
            $sessionId = $meeting->session_id;
            $apiKey = '47315981';
             return view('meeting.videoCall',compact('sessionId','token','apiKey','meetId'));
        else:
            $token = $meeting->token;
            $sessionId = $meeting->session_id;
            $apiKey = '47315981';
            //  return view('meeting.videoCall',compact('sessionId','token','apiKey','meetId'));
             $error = "Your video call date and current date doesnot match , Please try again ! ";
             return view('meeting.videCallError',compact('error'));
        endif;

            // $token = $meeting->token;
            // $sessionId = $meeting->session_id;
            // $apiKey = '47315981';
             return view('meeting.videoCall',compact('sessionId','token','apiKey','meetId'));



    }





    public function meeting_video_list($id){
        $apiKey = '47315981';
        $apiSecretKey = '5b1236f3646780754f32b8198f6fde4ac9738b04';
        $meetId = Crypt::decrypt($id);
        $meeting = Meeting::findOrfail($meetId);
        $opentok = new OpenTok($apiKey, $apiSecretKey);
        // $archiveId = '3abccc09-8b14-44ae-9088-4174c0ebbb67';
        // $arChive = $opentok->getArchive($archiveId);
        //


        // print_r($opentok->getArchive('2011a027-4b18-4edd-aa4a-b53446d7b519'));

        // die;

        $dateEnd = strtotime($meeting->date);
        $dateEnd = strtotime("+3 day", $dateEnd);
        $newDateEnd = date('Y-m-d', $dateEnd);

        $meetingVideo = MeetingVideo::where('meeting_id',$meetId)->get();
     $vidoe_status = 0;
     $VideoArr = array();
        if(!empty($meetingVideo)):
            foreach($meetingVideo as $meetingVideoVal):

              $arChive = $opentok->getArchive( $meetingVideoVal->video_id);
               if($arChive->status=='available'):

                 $VideoArr[] = array(
                    'videoUrl'=>$arChive->url,
                    'videoName'=>'Video section',

                );
                $vidoe_status = 1;

               endif;
                // echo"<pre>";
                //     print_r($arChive->url);
                //      print_r($arChive);
                // echo"</pre>";



            endforeach;

         else:

             $VideoArr[] = array(
                    'videoUrl'=>'No',
                    'videoName'=>'No',

                );

        endif;



       return view('meeting.videCallList',compact('arChive','vidoe_status','VideoArr','newDateEnd'));





    }

    public function meeting_video_start(Request $request){
        $meeting = Meeting::findOrfail($request->meetingID);
    	$sessionId = $request->sessionId;
    	$apiKey = '47315981';
		$apiSecretKey = '5b1236f3646780754f32b8198f6fde4ac9738b04';
		$opentok = new OpenTok($apiKey, $apiSecretKey);

	   $archiveMode = $opentok->startArchive($sessionId, array(
		'name' => $meeting->title,
		'hasAudio' => true,
		'hasVideo' => true,
		'outputMode' => OutputMode::COMPOSED,
		// 'resolution' => '1280x720' ,


     ));

       $MeetingVideo = new MeetingVideo;
       $MeetingVideo->meeting_id = $request->meetingID;
       $MeetingVideo->video_id = $archiveMode->id;
       $MeetingVideo->user_id = Auth::user()->id;
       $MeetingVideo->video_status = 0;
       $MeetingVideo->save();


    return response()->json(['success'=>'Video recording started','archiveId'=>$archiveMode->id,'status'=>1]);

    // $archiveId = $archiveMode->id;

    // echo  $archiveId;



    }

     public function meeting_video_stop(Request $request){
        $apiKey = '47315981';
        $apiSecretKey = '5b1236f3646780754f32b8198f6fde4ac9738b04';
        $opentok = new OpenTok($apiKey, $apiSecretKey);
        $archiveId = $request->archiveId;

        $meeting = Meeting::findOrfail($request->meetingID);


        $dbArchice = MeetingVideo::where([['meeting_id',$request->meetingID]])->orderBy('id', 'DESC')->first();

        $MeetingVideo= MeetingVideo::findOrfail($dbArchice->id);
        $MeetingVideo->video_status = 1;
        $MeetingVideo->save();
        $opentok->stopArchive($dbArchice->video_id);

        return response()->json(['success'=>'Video recording stoped','status'=>1]);

    }




    public function meeting_view($id){


        $meetId = Crypt::decrypt($id);

       if(isset($_GET['not_id'])):


         Notification::where('id',Crypt::decrypt($_GET['not_id']))->delete();
       endif;
        $meeting = Meeting::findOrfail($meetId);

        $UserCheck = MeetingUser::where([['meeting_id',$meetId],['meeting_user',Auth::user()->id]])->get();

        // $allEditUser = MeetingUser::where('meeting_id',$meetId)->get();

        $allEditUser = User::join('meeting_user', 'users.id', '=', 'meeting_user.meeting_user')->where('meeting_user.meeting_id',$meetId)->select('*','users.id as id')->groupBy('users.id')->get();




         return view('meeting.single_meeting',compact('meeting','allEditUser','UserCheck'));
    }






    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

            $validator = Validator::make($request->all(), [
            'title'=>'required',
            'time'=>'required',
            'date'=>'required',

        ]);


        if ($validator->passes()){


            $apiKey = '47315981';
            $apiSecretKey = '5b1236f3646780754f32b8198f6fde4ac9738b04';
            $opentok = new OpenTok($apiKey, $apiSecretKey);

            $session = $opentok->createSession(array(
                'mediaMode' => MediaMode::ROUTED
            ));

            $sessionId = $session->getSessionId();
            $token = $session->generateToken(array(
            'role'       => Role::MODERATOR,
            'expireTime' => time()+(7 * 24 * 60 * 60), // in one week
            'data'       => 'name='.$request->title.'',
            'initialLayoutClassList' => array('focus')
            ));


//    echo $token.'<br>';
//   echo $sessionId.'<br>';

// die;


    // $token="T1==cGFydG5lcl9pZD00NzMxNTk4MSZzaWc9NDVjNTA0NjZkNTliMjc4MjM3MGVhOWM1ZmE4MGMyMDc3NGJmYzhiOTpzZXNzaW9uX2lkPTJfTVg0ME56TXhOVGs0TVg1LU1UWXpNalEzT1RJeU1ESXpPSDVoYVhCRWRYWmxaMkZGZFhCcFRFNWFkelJLYzBjeE1YZC1mZyZjcmVhdGVfdGltZT0xNjMyNDc5MjIwJnJvbGU9bW9kZXJhdG9yJm5vbmNlPTE2MzI0NzkyMjAuMzMxMzc1MTA3MTgzJmV4cGlyZV90aW1lPTE2MzMwODQwMjAmY29ubmVjdGlvbl9kYXRhPW5hbWUlM0Rkd2R3ZHdkd2QmaW5pdGlhbF9sYXlvdXRfY2xhc3NfbGlzdD1mb2N1cw==";

    // $sessionId="2_MX40NzMxNTk4MX5-MTYzMjQ3OTIyMDIzOH5haXBEdXZlZ2FFdXBpTE5adzRKc0cxMXd-fg";









          if(empty($request->admin_user) && empty($request->accountmanger) && empty($request->team_user)):

            //  return Redirect::back()->with('errorUser','Please choose atleast one user');

             return Redirect::back()->withErrors($validator)->withInput()->with('errorUser','Please choose atleast one user');

          endif;

            $meeting = new Meeting;
            $meeting->title = $request->title;
            $meeting->date = $request->date;
            $meeting->user_id =  Auth::user()->id;
            $meeting->meeting_created_date = date('Y-m-d');
            $meeting->time = $request->time;
            $meeting->session_id = $sessionId;
            $meeting->token = $token;
            $meeting->save();


            $MeetingUserLogUser = new MeetingUser;
            $MeetingUserLogUser->meeting_id = $meeting->id;
            $MeetingUserLogUser->meeting_user = Auth::user()->id;
            if(Auth::user()->hasRole('Admin')):
                 $MeetingUserLogUser->user_status_id = 1;

            elseif(Auth::user()->hasRole('Account Manager')):
                $MeetingUserLogUser->user_status_id = 2;

            elseif(Auth::user()->hasRole('Client')):
                $MeetingUserLogUser->user_status_id = 3;

            else:
                $MeetingUserLogUser->user_status_id = 4;

            endif;
            $MeetingUserLogUser->save();



            $NotcontLogUser = "You have meeting on ".date("m-d-Y", strtotime($request->date)). "at ".$request->time."!";
            $notificationLogUser = new Notification();
            $notificationLogUser->not_from = Auth::user()->id;
            $notificationLogUser->not_to = Auth::user()->id;
            $notificationLogUser->cont_id = $meeting->id;
            $notificationLogUser->con_status = 1;
            $notificationLogUser->content = $NotcontLogUser;
            $notificationLogUser->save();


          if(!empty($request->admin_user)): foreach($request->admin_user as $admin_userVal):

            $MeetingUserAdmin = new MeetingUser;
            $MeetingUserAdmin->meeting_id = $meeting->id;
            $MeetingUserAdmin->user_status_id = 1;
            $MeetingUserAdmin->meeting_user = $admin_userVal;
            $MeetingUserAdmin->save();


            $NotcontAdmin = "You have meeting on ".date("m-d-Y", strtotime($request->date)). "at ".$request->time."!";
            $notificationAdmin = new Notification();
            $notificationAdmin->not_from = Auth::user()->id;
            $notificationAdmin->not_to = $admin_userVal;
            $notificationAdmin->cont_id = $meeting->id;
            $notificationAdmin->con_status = 1;
            $notificationAdmin->content = $NotcontAdmin;
            $notificationAdmin->save();
          endforeach; endif;

           if(!empty($request->accountmanger)): foreach($request->accountmanger as $accountmangerVal):

            $MeetingUserACM = new MeetingUser;
            $MeetingUserACM->meeting_id = $meeting->id;
            $MeetingUserACM->meeting_user = $accountmangerVal;
            $MeetingUserACM->user_status_id = 2;
            $MeetingUserACM->save();


            $NotcontACM = "You have meeting on ".date("m-d-Y", strtotime($request->date)). "at ".$request->time."!";
            $notificationACM = new Notification();
            $notificationACM->not_from = Auth::user()->id;
            $notificationACM->not_to = $accountmangerVal;
            $notificationACM->cont_id = $meeting->id;
            $notificationACM->con_status = 1;
            $notificationACM->content = $NotcontACM;
            $notificationACM->save();
          endforeach; endif;


           if(!empty($request->team_user)): foreach($request->team_user as $team_userVal):

            $MeetingUserTeam = new MeetingUser;
            $MeetingUserTeam->meeting_id = $meeting->id;
            $MeetingUserTeam->user_status_id = 4;
            $MeetingUserTeam->meeting_user = $team_userVal;
            $MeetingUserTeam->save();


            $NotcontTeam = "You have meeting on ".date("m-d-Y", strtotime($request->date)). "at ".$request->time."!";
            $notificationTeam = new Notification();
            $notificationTeam->not_from = Auth::user()->id;
            $notificationTeam->not_to = $team_userVal;
            $notificationTeam->cont_id = $meeting->id;
            $notificationTeam->con_status = 1;
            $notificationTeam->content = $NotcontTeam;
            $notificationTeam->save();
          endforeach; endif;

           if(!empty($request->client_user)): foreach($request->client_user as $client_userVal):

            $MeetingUserClient = new MeetingUser;
            $MeetingUserClient->meeting_id = $meeting->id;
            $MeetingUserClient->meeting_user = $client_userVal;
            $MeetingUserClient->user_status_id = 3;
            $MeetingUserClient->save();


            $NotcontClient = "You have meeting on ".date("m-d-Y", strtotime($request->date)). "at ".$request->time."!";
            $notificationClient = new Notification();
            $notificationClient->not_from = Auth::user()->id;
            $notificationClient->not_to = $client_userVal;
            $notificationClient->cont_id = $meeting->id;
            $notificationClient->con_status = 1;
            $notificationClient->content = $NotcontClient;
            $notificationClient->save();
          endforeach; endif;





            $cont = "You have created meeting video ".ucfirst($request->title)."on ".date("m-d-Y", strtotime($request->date)). "at ".$request->time."!";
            $activites = new RecentActivity;
            $activites->user_id = Auth::id();
            $activites->content = $cont;
            $activites->save();



             return redirect()->route('meeting.index')
                ->with('flash_message',
                 'Metting successfully added.');


         }else{
             return Redirect::back()->withErrors($validator)->withInput()->with('errormessage','Please fill the required field');
         }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $meetId = Crypt::decrypt($id);
        $meeting = Meeting::findOrfail($meetId);
        $allEdit = MeetingUser::where('meeting_id',$meetId)->get();

    if(Auth::user()->hasRole('Account Manager') || Auth::user()->hasRole('Admin')):
         $adminUser = User::whereHas('roles', function($q){
                $q->where('name', 'Admin');
                })->where('id','!=',Auth::user()->id)->orderBy('id', 'DESC')->get();

         $accountManager = User::whereHas('roles', function($q){
                $q->where('name', 'Account Manager');
                })->where('id','!=',Auth::user()->id)->orderBy('id', 'DESC')->get();

          $teamUser = User::whereHas('roles', function($q){
                $q->where('name', 'User');
                })->orderBy('id', 'DESC')->get();

       if(Auth::user()->hasRole('Admin')):
          $clientUser = User::whereHas('roles', function($q){
                $q->where('name', 'Client');
                })->orderBy('id', 'DESC')->get();
       else:
          $clientUser = User::join('request', 'users.id', '=', 'request.client_id')->where('request.pm_id',Auth::id())
               ->select('*','users.id as id')->groupBy('request.client_id')->get();

       endif;


    elseif(Auth::user()->hasRole('Client')):
        $adminUser = User::whereHas('roles', function($q){
            $q->where('name', 'Admin');
            })->where('id','!=',Auth::user()->id)->orderBy('id', 'DESC')->get();


        $accountManager =  User::join('request', 'users.id', '=', 'request.pm_id')->where('request.client_id',Auth::id())
        ->select('*','users.id as id')->groupBy('users.id')->get();

        return view('meeting.edit',compact('meeting','adminUser','accountManager','allEdit'));

    endif;


        return view('meeting.edit',compact('meeting','adminUser','accountManager','teamUser','clientUser','allEdit'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'title'=>'required',
            'time'=>'required',
            'date'=>'required',

        ]);
        if ($validator->passes()){



             if(empty($request->admin_user) && empty($request->accountmanger) && empty($request->team_user)):

                return Redirect::back()->with('errorUser','Please choose atleast one user');

             endif;

            $meeting =Meeting::findOrfail($id);
            $meeting->title = $request->title;
            $meeting->date = $request->date;
            // $meeting->user_id =  Auth::user()->id;
            // $meeting->meeting_created_date = date('Y-m-d');
            $meeting->time = $request->time;
            $meeting->save();


            $allEdit = MeetingUser::where('meeting_id',$id)->get();

            $oldAdminUser = MeetingUser::where([['meeting_id',$id],['user_status_id',1],['meeting_user','!=',Auth::user()->id]])->pluck('meeting_user')->toArray();
            $newAdminUser = array_map('intval',$request->admin_user);
            $adminUserOne = array_diff($oldAdminUser, $newAdminUser); // get remove user from select box
            $adminUserTwo = array_diff($newAdminUser, $oldAdminUser); // get new user from select box


            $oldacmUser = MeetingUser::where([['meeting_id',$id],['user_status_id',2],['meeting_user','!=',Auth::user()->id]])->pluck('meeting_user')->toArray();
            $newacmUser = array_map('intval',$request->accountmanger);
            $acmUserOne = array_diff($oldacmUser, $newacmUser); // get remove user from select box
            $acmUserTwo = array_diff($newacmUser, $oldacmUser); // get new user from select box

            $oldClientuser = MeetingUser::where([['meeting_id',$id],['user_status_id',3],['meeting_user','!=',Auth::user()->id]])->pluck('meeting_user')->toArray();
            $newClentUser = array_map('intval',$request->client_user);
            $ClentUserOne = array_diff($oldClientuser, $newClentUser); // get remove user from select box
            $ClentUserTwo = array_diff($newClentUser, $oldClientuser); // get new user from select box


            $oldTeamUser = MeetingUser::where([['meeting_id',$id],['user_status_id',4],['meeting_user','!=',Auth::user()->id]])->pluck('meeting_user')->toArray();
            $newTeamUser = array_map('intval',$request->team_user);
            $TeamUserOne = array_diff($oldTeamUser, $newTeamUser); // get remove user from select box
            $TeamUserTwo = array_diff($newTeamUser, $oldTeamUser); // get new user from select box




    // -----------------------------Notificcation send for user removed from meeting start------------------------------
            if(!empty($adminUserOne)):
                foreach($adminUserOne as $adminUserOneVal):
                    MeetingUser::where('meeting_user',$adminUserOneVal)->delete();
                    Notification::where([['not_to',$adminUserOneVal],['cont_id',$id]])->delete();

                    $NotcontAdmin = "Your meeting have removed on ".date("m-d-Y", strtotime($meeting->date)). "at ".$meeting->time."!";
                    $notificationAdmin = new Notification();
                    $notificationAdmin->not_from = Auth::user()->id;
                    $notificationAdmin->not_to = $adminUserOneVal;
                    $notificationAdmin->cont_id = $id;
                    $notificationAdmin->con_status = 1;
                    $notificationAdmin->content = $NotcontAdmin;
                    $notificationAdmin->save();

                endforeach;

            endif;


            if(!empty($acmUserOne)):
                foreach($acmUserOne as $acmUserOneVal):
                     MeetingUser::where('meeting_user',$acmUserOneVal)->delete();
                     Notification::where([['not_to',$acmUserOneVal],['cont_id',$id]])->delete();

                    $NotcontAdmin = "Your meeting have removed on ".date("m-d-Y", strtotime($meeting->date)). "at ".$meeting->time."!";
                    $notificationAdmin = new Notification();
                    $notificationAdmin->not_from = Auth::user()->id;
                    $notificationAdmin->not_to = $acmUserOneVal;
                    $notificationAdmin->cont_id = $id;
                    $notificationAdmin->con_status = 1;
                    $notificationAdmin->content = $NotcontAdmin;
                    $notificationAdmin->save();

                endforeach;

            endif;

             if(!empty($ClentUserOne)):
                foreach($ClentUserOne as $ClentUserOneVal):

                    MeetingUser::where('meeting_user',$ClentUserOneVal)->delete();
                    Notification::where([['not_to',$ClentUserOneVal],['cont_id',$id]])->delete();

                    $NotcontAdmin = "Your meeting have removed on ".date("m-d-Y", strtotime($meeting->date)). "at ".$meeting->time."!";
                    $notificationAdmin = new Notification();
                    $notificationAdmin->not_from = Auth::user()->id;
                    $notificationAdmin->not_to = $ClentUserOneVal;
                    $notificationAdmin->cont_id = $id;
                    $notificationAdmin->con_status = 1;
                    $notificationAdmin->content = $NotcontAdmin;
                    $notificationAdmin->save();

                endforeach;

            endif;

             if(!empty($TeamUserOne)):
                foreach($TeamUserOne as $TeamUserOneVal):
                     MeetingUser::where('meeting_user',$TeamUserOneVal)->delete();
                     Notification::where([['not_to',$TeamUserOneVal],['cont_id',$id]])->delete();

                    $NotcontAdmin = "Your meeting have removed on ".date("m-d-Y", strtotime($meeting->date)). "at ".$meeting->time."!";
                    $notificationAdmin = new Notification();
                    $notificationAdmin->not_from = Auth::user()->id;
                    $notificationAdmin->not_to = $TeamUserOneVal;
                    $notificationAdmin->cont_id = $id;
                    $notificationAdmin->con_status = 1;
                    $notificationAdmin->content = $NotcontAdmin;
                    $notificationAdmin->save();

                endforeach;

            endif;

 // -----------------------------Notificcation send for user removed from meeting end------------------------------


 // -----------------------------Notificcation send for user created from meeting start------------------------------
            if(!empty($adminUserTwo)):
                foreach($adminUserTwo as $adminUserTwoVal):

                    $MeetingUserAdmin = new MeetingUser;
                    $MeetingUserAdmin->meeting_id = $id;
                    $MeetingUserAdmin->user_status_id = 1;
                    $MeetingUserAdmin->meeting_user = $adminUserTwoVal;
                    $MeetingUserAdmin->save();

                    $NotcontAdmin = "Your have meeting on ".date("m-d-Y", strtotime($request->date)). "at ".$request->time."!";
                    $notificationAdmin = new Notification();
                    $notificationAdmin->not_from = Auth::user()->id;
                    $notificationAdmin->not_to = $adminUserTwoVal;
                    $notificationAdmin->cont_id = $id;
                    $notificationAdmin->con_status = 1;
                    $notificationAdmin->content = $NotcontAdmin;
                    $notificationAdmin->save();




                endforeach;

            endif;


            if(!empty($acmUserTwo)):
                foreach($acmUserTwo as $acmUserTwoVal):

                    $MeetingUserACM = new MeetingUser;
                    $MeetingUserACM->meeting_id = $id;
                    $MeetingUserACM->meeting_user = $acmUserTwoVal;
                    $MeetingUserACM->user_status_id = 2;
                    $MeetingUserACM->save();

                    $NotcontAdmin = "Your have meeting on ".date("m-d-Y", strtotime($request->date)). "at ".$request->time."!";
                    $notificationAdmin = new Notification();
                    $notificationAdmin->not_from = Auth::user()->id;
                    $notificationAdmin->not_to = $acmUserTwoVal;
                    $notificationAdmin->cont_id = $id;
                    $notificationAdmin->con_status = 1;
                    $notificationAdmin->content = $NotcontAdmin;
                    $notificationAdmin->save();



                endforeach;

            endif;

             if(!empty($ClentUserTwo)):
                foreach($ClentUserTwo as $ClentUserTwoVal):

                    $MeetingUserClient = new MeetingUser;
                    $MeetingUserClient->meeting_id = $id;
                    $MeetingUserClient->meeting_user = $ClentUserTwoVal;
                    $MeetingUserClient->user_status_id = 3;
                    $MeetingUserClient->save();

                    $NotcontAdmin = "Your have meeting on ".date("m-d-Y", strtotime($request->date)). "at ".$request->time."!";
                    $notificationAdmin = new Notification();
                    $notificationAdmin->not_from = Auth::user()->id;
                    $notificationAdmin->not_to = $ClentUserTwoVal;
                    $notificationAdmin->cont_id = $id;
                    $notificationAdmin->con_status = 1;
                    $notificationAdmin->content = $NotcontAdmin;
                    $notificationAdmin->save();

                endforeach;

            endif;

             if(!empty($TeamUserTwo)):
                foreach($TeamUserTwo as $TeamUserTwoVal):

                    $MeetingUserTeam = new MeetingUser;
                    $MeetingUserTeam->meeting_id = $id;
                    $MeetingUserTeam->user_status_id = 4;
                    $MeetingUserTeam->meeting_user = $TeamUserTwoVal;
                    $MeetingUserTeam->save();

                    $NotcontAdmin = "Your have meeting on ".date("m-d-Y", strtotime($request->date)). "at ".$request->time."!";
                    $notificationAdmin = new Notification();
                    $notificationAdmin->not_from = Auth::user()->id;
                    $notificationAdmin->not_to = $TeamUserTwoVal;
                    $notificationAdmin->cont_id = $id;
                    $notificationAdmin->con_status = 1;
                    $notificationAdmin->content = $NotcontAdmin;
                    $notificationAdmin->save();

                endforeach;

            endif;

 // -----------------------------Notificcation send for user created from meeting end------------------------------


 // -----------------------------Already check user available start------------------------------------

       if($meeting->date != $request->date || $meeting->time != $request->time):

        if(!empty($request->admin_user)): foreach($request->admin_user as $admin_userVal):

             Notification::where([['not_to',$admin_userVal],['cont_id',$id]])->delete();
            $NotcontAdmin = "Your meeting have updated on ".date("m-d-Y", strtotime($meeting->date)). "at ".$meeting->time."to ".date("m-d-Y", strtotime($request->date)). "at ".$request->time."!";

            $notificationAdmin = new Notification();
            $notificationAdmin->not_from = Auth::user()->id;
            $notificationAdmin->not_to = $admin_userVal;
            $notificationAdmin->cont_id = $id;
            $notificationAdmin->con_status = 1;
            $notificationAdmin->content = $NotcontAdmin;
            $notificationAdmin->save();

          endforeach; endif;

           if(!empty($request->accountmanger)): foreach($request->accountmanger as $accountmangerVal):


            Notification::where([['not_to',$accountmangerVal],['cont_id',$id]])->delete();

            $NotcontACM = "Your meeting have updated on ".date("m-d-Y", strtotime($meeting->date)). "at ".$meeting->time."to ".date("m-d-Y", strtotime($request->date)). "at ".$request->time."!";
            $notificationACM = new Notification();
            $notificationACM->not_from = Auth::user()->id;
            $notificationACM->not_to = $accountmangerVal;
            $notificationACM->cont_id = $id;
            $notificationACM->con_status = 1;
            $notificationACM->content = $NotcontACM;
            $notificationACM->save();
          endforeach; endif;


           if(!empty($request->team_user)): foreach($request->team_user as $team_userVal):

            Notification::where([['not_to',$team_userVal],['cont_id',$id]])->delete();
            $NotcontTeam =  "Your meeting have updated on ".date("m-d-Y", strtotime($meeting->date)). "at ".$meeting->time."to ".date("m-d-Y", strtotime($request->date)). "at ".$request->time."!";
            $notificationTeam = new Notification();
            $notificationTeam->not_from = Auth::user()->id;
            $notificationTeam->not_to = $team_userVal;
            $notificationTeam->cont_id = $id;
            $notificationTeam->con_status = 1;
            $notificationTeam->content = $NotcontTeam;
            $notificationTeam->save();
          endforeach; endif;

           if(!empty($request->client_user)): foreach($request->client_user as $client_userVal):

            Notification::where([['not_to',$client_userVal],['cont_id',$id]])->delete();
            $NotcontClient =  "Your meeting have updated on ".date("m-d-Y", strtotime($meeting->date)). "at ".$meeting->time."to ".date("m-d-Y", strtotime($request->date)). "at ".$request->time."!";
            $notificationClient = new Notification();
            $notificationClient->not_from = Auth::user()->id;
            $notificationClient->not_to = $client_userVal;
            $notificationClient->cont_id = $id;
            $notificationClient->con_status = 1;
            $notificationClient->content = $NotcontClient;
            $notificationClient->save();
          endforeach; endif;

      endif;



 // -----------------------------Already check user available end------------------------------




             return redirect()->route('meeting.index')
                ->with('flash_message',
                 'Meeting successfully updated.');


         }else{
             return Redirect::back()->withErrors($validator)->withInput()->with('errormessage','Please fill the required field');
         }
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $meeting = Meeting::findOrfail($id);
        $cont = "You have deleted the meeting ";
        $activites = new RecentActivity;
        $activites->user_id = Auth::id();
        $activites->content = $cont;
        $activites->save();

        Notification::where([['cont_id',$id],['con_status',1]])->delete();
        MeetingUser::where('meeting_id',$id)->delete();
        MeetingVideo::where('meeting_id',$id)->delete();

        meeting::findOrfail($id)->delete();
        return redirect()->route('meeting.index')
               ->with('flash_message',
               'meeting successfully deleted.');
    }
}
