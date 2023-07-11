<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\User;
use App\Models\UserBlocks;
use App\Models\ServicePost;
use App\Models\ServicePackagesCustom;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ChatController extends Controller
{

    protected Message $message;

    /**
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        
        $chat_users = DB::select(DB::raw('SELECT DISTINCT conversation_id FROM messages WHERE user_id = '.$user_id));

        $query = '
                SELECT c.user_id, MAX(cnt) AS cnt, users.username AS username 
                FROM (
                    SELECT a.user_id, b.cnt 
                    FROM (
                        SELECT `user_id` 
                        FROM `messages` 
                        WHERE `conversation_id` = '.$user_id.'
                        AND `user_id` != '.$user_id.'
                        GROUP BY (user_id)
                    ) AS a
                    LEFT JOIN (
                        SELECT `user_id`, COUNT(*) AS cnt 
                        FROM `messages` 
                        WHERE `conversation_id` = '.$user_id.'
                        AND `user_id` != '.$user_id.'
                        AND `is_seen` = 0 
                        GROUP BY (user_id)
                    ) AS b ON a.user_id = b.user_id
                    UNION ALL
                    SELECT `conversation_id` AS user_id, "0" AS cnt 
                    FROM `messages` 
                    WHERE `user_id` = '.$user_id.'
                    AND `conversation_id` != '.$user_id.'
                    GROUP BY `conversation_id`
                ) AS c
                INNER JOIN `users` ON c.user_id = users.id
                LEFT JOIN (
                    SELECT DISTINCT `user_id`, `conversation_id`, `is_archived`, `is_deleted`
                    FROM `messages`
                    WHERE `user_id` = '.$user_id.'
                ) AS d ON c.user_id = d.conversation_id 
                WHERE users.id != '.$user_id.'
                AND d.is_archived = 0
                AND d.is_deleted = 0
                GROUP BY c.user_id
                ';

        $side_info = DB::select(DB::raw($query));

        foreach($side_info as $info) {
            $user_detail_info = User::find($info->user_id);
            $infoUpload = $user_detail_info->uploads;
            $info->avatar_url = $infoUpload->getImageOptimizedFullName(100,100);
        }

        return view('chat.index', compact('chat_users', 'side_info'));
    }

    public function create(Request $request)
        {
            $seller = $request->seller;
            $user_id = Auth::user()->id;

            if($seller){
                $is_connected = Message::where(['user_id' =>$user_id,'dest_id' => $seller])->count();
                if($is_connected == 0){
                    $message = new Message;
                    $message->user_id = $user_id;
                    $message->dest_id = $seller;
                    $message->save();
                }
            }

            $chat_content = DB::select(DB::raw('SELECT * FROM `messages` WHERE (user_id='.$user_id.' AND dest_id='.$seller.') OR (user_id='.$seller.' AND dest_id='.$user_id.');'));


            $side_info = DB::select(DB::raw('SELECT a.*, u.`first_name`,u.`last_name`,u.`id` FROM (SELECT m.* FROM messages m WHERE id IN (SELECT MAX(id) FROM messages GROUP BY dest_id) AND user_id = '.$user_id.') AS a LEFT JOIN users u ON a.dest_id = u.id  ORDER BY a.created_at DESC'));

            return view('chat.create', compact('side_info','chat_content','seller','user_id'));
        }

    public function contentFetchByClientId(Request $request){
        $client_id = $request->client_id;
        $user_id = Auth::user()->id;
        $chat_content = Message::where(['user_id'=>$user_id,'conversation_id'=>$client_id])
                                    ->orWhere(['user_id'=>$client_id,'conversation_id'=>$user_id])
                                    ->get();
        $chat_content = DB::select(DB::raw('SELECT * FROM `messages` WHERE (user_id='.$user_id.' AND conversation_id='.$client_id.') OR (user_id='.$client_id.' AND conversation_id='.$user_id.');'));
        return response()->json([
            'result'        => true,
            'chat_content'  => $chat_content,
        ]);
    }

    public function filter(Request $request){
        $filter = $request->filter;
        $user_id = Auth::user()->id;
        if(strlen($filter)==0){
            $side_info = DB::select(DB::raw('SELECT a.*, u.`first_name`,u.`last_name` FROM (SELECT m.* FROM messages m WHERE id IN (SELECT MAX(id) FROM messages GROUP BY dest_id) AND user_id = '.$user_id.') AS a LEFT JOIN users u ON a.dest_id = u.id   ORDER BY a.created_at DESC'));
            return view('chat.create', compact('side_info'));
        }else if(strlen($filter)!=0) {
            $side_info = DB::select(DB::raw('SELECT a.*, u.`first_name`,u.`last_name` FROM (SELECT m.* FROM messages m WHERE id IN (SELECT MAX(id) FROM messages GROUP BY dest_id) AND user_id = '.$user_id.') AS a LEFT JOIN users u ON a.dest_id = u.id  WHERE u.`first_name`=".$filter" OR u.`last_name`=".$filter" ORDER BY a.created_at DESC'));
            return view('chat.create', compact('side_info'));
        }

    }

    public function getNameById($user_id){
       return  $user_name = User::where('id',$user_id)->get('first_name');
    }


    public function create_chat_room(Request $request,$conversation_id) {

        $user = Auth::user();
        $user_id = $user->id;
        $real_id = User::where(['username' => $conversation_id])
                        ->get('id')
                        ->first();
        $conversation_id = $real_id->id;

        if ($user_id == $conversation_id)
        {
            return abort(404);
        }
        $is_created_chat_room = Message::where(['user_id' => $user_id, 'conversation_id' => $conversation_id])
                                        ->groupBy('user_id')
                                        ->count();
        $this->message->seenAll($conversation_id,$user_id);

        if($is_created_chat_room == 0){
            $message = new Message;
            $message->user_id = $user_id;
            $message->conversation_id = $conversation_id;
            $message->save();
        }

        // $query = '
        //     SELECT   c.user_id , max(cnt) as cnt, users.username as username from (
        //     SELECT a.user_id,b.cnt FROM
        //                 (SELECT `user_id` FROM `messages` WHERE `conversation_id` = '.$user_id.' and user_id != '.$user_id.'
        //     GROUP BY (user_id))as a
        //                 LEFT JOIN
        //                 (SELECT `user_id`, COUNT(*)
        //                 as cnt FROM `messages` WHERE `conversation_id` = '.$user_id.' and user_id != '.$user_id.'

        //                 and is_seen=0  GROUP BY (user_id)
        //                 )as b
        //                 on a.user_id = b.user_id
        //             UNION ALL
        //             SELECT `conversation_id` as user_id,"0" as cnt FROM `messages` WHERE `user_id` = '.$user_id.'
        //             and conversation_id!= '.$user_id.' GROUP By `conversation_id`
        //             )as c, users  where c.user_id = users.id GROUP BY c.user_id
        // '
        // ;
        $query = '
                SELECT c.user_id, MAX(cnt) AS cnt, users.username AS username 
                FROM (
                    SELECT a.user_id, b.cnt 
                    FROM (
                        SELECT `user_id` 
                        FROM `messages` 
                        WHERE `conversation_id` = '.$user_id.'
                        AND `user_id` != '.$user_id.'
                        GROUP BY (user_id)
                    ) AS a
                    LEFT JOIN (
                        SELECT `user_id`, COUNT(*) AS cnt 
                        FROM `messages` 
                        WHERE `conversation_id` = '.$user_id.'
                        AND `user_id` != '.$user_id.'
                        AND `is_seen` = 0 
                        GROUP BY (user_id)
                    ) AS b ON a.user_id = b.user_id
                    UNION ALL
                    SELECT `conversation_id` AS user_id, "0" AS cnt 
                    FROM `messages` 
                    WHERE `user_id` = '.$user_id.'
                    AND `conversation_id` != '.$user_id.'
                    GROUP BY `conversation_id`
                ) AS c
                INNER JOIN `users` ON c.user_id = users.id
                LEFT JOIN (
                    SELECT DISTINCT `user_id`, `conversation_id`, `is_archived`, `is_deleted`
                    FROM `messages`
                    WHERE `user_id` = '.$user_id.'
                ) AS d ON c.user_id = d.conversation_id 
                WHERE users.id != '.$user_id.'
                AND d.is_archived = 0
                AND d.is_deleted = 0
                GROUP BY c.user_id
                ';

        $services = ServicePost::where('user_id', $user_id)->get();

        $side_info = DB::select(DB::raw($query));
        foreach($side_info as $info) {
            $user_detail_info = User::find($info->user_id);
            $infoUpload = $user_detail_info->uploads;
            $info->avatar_url = $infoUpload->getImageOptimizedFullName(100,100);
        }

        $chat_content = DB::select(DB::raw('SELECT * FROM `messages` WHERE (user_id='.$user_id.' AND conversation_id='.$conversation_id.') OR (user_id='.$conversation_id.' AND conversation_id='.$user_id.'); '));

        $block = UserBlocks::where(['blocked_user_id' => $user_id, 'user_id'=>$conversation_id])->first();
        $isBlocked = $block ? true : false;

        return view('chat.create', compact('side_info','chat_content','conversation_id','user_id', 'services', 'isBlocked'));
    }

    public function message_log(Request $request)
    {
        $data = $request->data;

        if (isset($data['chat_msg'])) {
            $message = trim($data['chat_msg']);

            if (!empty($message)) {
                $message_log = new Message;
                $message_log->user_id = $data['user_id'];
                $message_log->conversation_id = $data['conversation_id'];
                $message_log->message = $message;
                $message_log->save();

                $chatMessArr = explode(":", $message);
                $isUploadFile = isset($chatMessArr[0]) && $chatMessArr[0] == "upload_ids" && isset($chatMessArr[1]);
                $file = "";
                if ($isUploadFile) {
                    $file = Upload::find($chatMessArr[1]);
                }
                $user = User::find($data['user_id']);

                return response()->json([
                    'result' => true,
                    'message_log' => $message_log,
                    "upload_file" => $isUploadFile && $file ? $file->getFileFullPath() : '',
                    "file" => $file,
                    "link_download" => $isUploadFile && $file ? route('download_file', base64_encode($file->id)) : "",
                    "user" => [
                        "full_name" => $user->full_name,
                        'image_url' => $user->image_url
                    ]
                ]);
            }
        }
    }


    public function getChatInFormationBy(Request $request)
    {

        $file = Upload::find($request->file_id);
        $user = User::find($request->user_id);
        $conversation = User::find($request->conversation_id);

        return response()->json([
            "file" => $file,
            "user"=>[
                "username" => $user->username,
                'image_url' => $user->image_url
            ],
            "conversation"=>[
                "username" => $conversation->username,
                'image_url' => $conversation->image_url
            ],
            "link_download" => $request->file_id ? route('download_file',base64_encode($request->file_id)) :"",
            'path'=> $request->file_id ? $file->getFileFullPath() :""
        ]);
    }

    public function getOfferInFormationBy(Request $request) {

        $package = ServicePackagesCustom::find($request->package_id);
        $user = User::find($request->user_id);
        $conversation = User::find($request->conversation_id);

        return response()->json([
            "package" => $package,
            "user"=>[
                "username" => $user->username,
                'image_url' => $user->image_url
            ],
            "conversation"=>[
                "username" => $conversation->username,
                'image_url' => $conversation->image_url
            ],
        ]);
    }

    public function getConInFormationBy(Request $request) {

        $user = User::find($request->user_id);
        $conversation = User::find($request->conversation_id);

        return response()->json([
            "user"=>[
                "username" => $user->username,
                'image_url' => $user->image_url
            ],
            "conversation"=>[
                "username" => $conversation->username,
                'image_url' => $conversation->image_url
            ],
        ]);
    }

    public function message_delete(Request $request)
    {
        $message = Message::find($request->data['id']);
        $message->is_message_deleted = 1;
        $message->save();

        return response()->json(true); 
    }

    public function chat_archive(Request $request)
    {
        $user_id = Auth::user()->id;
        $conversation_id = $request->conversation_id;

        $messages = Message::where(['user_id'=>$user_id, 'conversation_id'=>$conversation_id])->get();
        foreach($messages as $message) {
            $message->is_archived = 1;
            $message->save();
        }

        return response()->json(true);
    }

    public function chat_delete(Request $request)
    {
        $user_id = Auth::user()->id;
        $conversation_id = $request->conversation_id;

        $messages = Message::where(['user_id'=>$user_id, 'conversation_id'=>$conversation_id])->get();
        foreach($messages as $message) {
            $message->is_deleted = 1;
            $message->save();
        }

        return response()->json(true);
    }

    public function check_status(Request $request)
    {
        $user_id = Auth::user()->id;
        $conversation_id = $request->conversation_id;

        $messages = Message::where(['user_id'=>$user_id, 'conversation_id'=>$conversation_id])->get();
        foreach($messages as $message) {
            if ($message->is_archived == 1 || $message->is_deleted == 1) {
                $message->is_archived = 0;
                $message->is_deleted = 0;
                $message->save();
            }
        }

        $conv = User::find($request->conversation_id);
        $avatarUpload = $conv->uploads;
        $avatar_url = $avatarUpload->getImageOptimizedFullName(100,100);

        return response()->json(['avatar_url'=>$avatar_url]);
    }

    public function block_user(Request $request)
    {
        $user_id = Auth::user()->id;
        $blocked_user_id = $request->data['conversation_id'];

        $userBlocks = new UserBlocks;
        $userBlocks->user_id = $user_id;
        $userBlocks->blocked_user_id = $blocked_user_id;
        $userBlocks->save();

        $messages = Message::where(['user_id'=>$user_id, 'conversation_id'=>$blocked_user_id])->get();
        foreach($messages as $message) {
            $message->is_deleted = 1;
            $message->save();
        }

        return response()->json(true);
    }

    public function search_user(Request $request)
    {
        $user_id = Auth::user()->id;
        $status = $request->status;
        $convs = [];

        switch ($status) {
            case 'all_conv':
                $messages = Message::where(['user_id'=>$user_id, 'is_archived'=>0, 'is_deleted'=>0])->select('conversation_id')->distinct()->get();
                foreach($messages as $message) {
                    $user = User::find($message->conversation_id);
                    $avatarUpload = $user->uploads;
                    $user->avatar_url = $avatarUpload->getImageOptimizedFullName(100,100);

                    array_push($convs, $user);
                }

                break;
            case 'archive_show':
                $messages = Message::where(['user_id'=>$user_id, 'is_archived'=>1])->select('conversation_id')->distinct()->get();
                foreach($messages as $message) {
                    $user = User::find($message->conversation_id);
                    $avatarUpload = $user->uploads;
                    $user->avatar_url = $avatarUpload->getImageOptimizedFullName(100,100);

                    array_push($convs, $user);
                }

                break;
            case 'delete_show':
                $messages = Message::where(['user_id'=>$user_id, 'is_deleted'=>1])->select('conversation_id')->distinct()->get();
                foreach($messages as $message) {
                    $user = User::find($message->conversation_id);
                    $avatarUpload = $user->uploads;
                    $user->avatar_url = $avatarUpload->getImageOptimizedFullName(100,100);
                    
                    array_push($convs, $user);
                }

                break;
            case 'blocked_show':
                $blockeds = Auth::user()->blockedUsers;
                foreach($blockeds as $blocked) {
                    $user = User::find($blocked->id);
                    $avatarUpload = $user->uploads;
                    $user->avatar_url = $avatarUpload->getImageOptimizedFullName(100,100);
                    
                    array_push($convs, $user);
                }

                break;
        }

        return response()->json($convs);
    }
}
