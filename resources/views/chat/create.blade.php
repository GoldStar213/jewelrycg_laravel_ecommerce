<x-chat-layout page-title="Messages | Jewelry CG">
    <meta name="_token" content="{{csrf_token()}}"/>
    <link rel="stylesheet" href="{{ asset('dropzone/css/dropzone.css') }}">
    @section('css')
        <link rel="stylesheet" href="//use.fontawesome.com/releases/v5.0.7/css/all.css">
        <style>
            .text-overflow-1{
                display: -webkit-box !important;
                overflow: hidden;
                text-overflow: ellipsis;
                -webkit-line-clamp: 1;
                -webkit-box-orient: vertical;
                width: 100%;
            }
            .dropzone {
                border-radius: 0;
                overflow: hidden;
                background: transparent;
                display: flex;
                border: none !important;
                width: 100%;
                flex-direction: column;
                min-height: unset !important;
            }

            .dropzone-items {
                margin-bottom: 10px;
            }

            .dropzone-panel {
                display: grid;
                grid-template-columns: auto max(14%, 100px) max(14%, 100px);
            }

            .dropzone .dz-preview {
                margin: 0;
            }

            .dz-image img {
                width: 100%;
            }

            .list-group > a:hover {
                background: #f0f2f5
            }

            .dropzone .dz-message {
                display: none;
            }

            .data > span {
                margin-right: 10px;
            }


            .chat-online {
                color: #34ce57
            }

            .chat-offline {
                color: #e4606d
            }

            .chat-messages {
                display: flex;
                flex-direction: column;
                max-height: 60vh;
                overflow-y: scroll;
            }

            .chat-message-left,
            .chat-message-right {
                display: flex;
                flex-shrink: 0
            }

            .chat-message-left {
                margin-right: auto
            }

            .chat-message-right {
                flex-direction: row-reverse;
                margin-left: auto
            }

/*
            .flex-grow-0 {
                flex-grow: 0 !important;
            }
*/
            .border-top {
                border-top: 1px solid #dee2e6 !important;
            }

            .border-right {
                border-right: 1px solid #dee2e6 !important;
            }

            .float-right {
                float: right !important;
            }

            .list-group-item {
                position: relative;
                display: block;
                padding: 0.75rem 1.25rem;
                background-color: #fff;
                border: 1px solid rgba(0, 0, 0, .125);
            }

            .wm-200px {
                /*max-width: 200px;*/
            }

            .dropzone.dropzone-queue .dropzone-item {
                display: flex;
                align-items: center;
                margin-top: 0.75rem;
                border-radius: 0.65rem;
                padding: 0.5rem 1rem;
                background-color: #f5f8fa;
            }

            .dropzone.dropzone-queue .dropzone-item .dropzone-file {
                flex-grow: 1;
            }

            .dropzone.dropzone-queue .dropzone-item .dropzone-file .dropzone-filename {
                font-size: .9rem;
                font-weight: 500;
                color: #7e8299;
                text-overflow: ellipsis;
                margin-right: 0.5rem;
            }

            .dropzone.dropzone-queue .dropzone-item .dropzone-file .dropzone-error {
                margin-top: 0.25rem;
                font-size: .9rem;
                font-weight: 400;
                color: #f1416c;
                text-overflow: ellipsis;
            }

            .dropzone.dropzone-queue .dropzone-item .dropzone-toolbar {
                margin-left: 1rem;
                display: flex;
                flex-wrap: nowrap;
            }

            .dropzone.dropzone-queue .dropzone-item .dropzone-toolbar .dropzone-cancel, .dropzone.dropzone-queue .dropzone-item .dropzone-toolbar .dropzone-delete, .dropzone.dropzone-queue .dropzone-item .dropzone-toolbar .dropzone-start {
                height: 25px;
                width: 25px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: color .2s ease, background-color .2s ease;
            }

            .dropzone.dropzone-queue .dropzone-item .dropzone-progress {
                width: 15%;
            }

            .d-link-side {
                display: none;
            }

            @media (max-width: 992px) {
                .d-link-side {
                    display: block;
                    margin-right: 15px;
                    font-size: 20px;
                    color: #828b95;
                }
                .d-link-side:hover {
                    cursor: pointer;
                    color: black;
                }
            }


            .msg-box .msg-action {
                visibility: hidden;
            }

            .msg-box:hover .msg-action {
                visibility: visible;
                cursor: pointer;
            }

            .disabled-upload {
                pointer-events: none;
                cursor: default;
            }
        </style>
    @endsection
<section class="messages bg-white">
    <div class="container">
        @php
            function users_name($id){
                return \App\Models\User::where('id',$id)->get();
            }

            function getChatMessage($message){
                $chatMessArr = explode(":",$message);
                $isUploadFile = isset($chatMessArr[0]) && $chatMessArr[0] == "upload_ids" && isset($chatMessArr[1]);
                $file = "";
                if ($isUploadFile)
                {
                    $file = \App\Models\Upload::find($chatMessArr[1]);
                }

                return [
                        "upload_file" => $isUploadFile && $file ? $file->getFileFullPath() :'',
                        "file" => $file,
                        "link_download" => $isUploadFile && $file ?  route('download_file',base64_encode($file->id)) : "",
                ];
            }

            function getChatOffer($message) {
                $chatMessArr = explode(":",$message);
                $packages = \App\Models\ServicePackagesCustom::all();
                $length = count($packages);
                $isOffer = isset($chatMessArr[0]) && $chatMessArr[0] == "service_packages_custom_id" && isset($chatMessArr[1]);

                $package = [];
                if ($isOffer)
                {
                    $package = \App\Models\ServicePackagesCustom::where('id', $chatMessArr[1])->get()[0];
                    $length = $package->id;
                }

                return [
                    "content" => $package,
                    "length" => $length,
                ];
            }

            function getOnlineStatus($last_activity) {
                $now = new DateTime();
                $last = new DateTime($last_activity);
                $interval = $now->diff($last);

                $result = '';
                $status = false;

                if ($interval->y > 0) {
                    $result = $interval->y . ' years ago';
                } else if ($interval->m > 0) {
                    $result = $interval->m . ' months ago';
                } else if ($interval->d > 0) {
                    $result = $interval->d . ' days ago';
                } else if ($interval->h > 0) {
                    $result = $interval->h . ' hours ago';
                } else if ($interval->i > 30) {
                    $result = $interval->i . ' minutes ago';
                } else {
                    $status = true;
                }

                return [
                    "result" => $result,
                    "status" => $status,
                ];
            }

            function getMsgId() {
                $msgs = \App\Models\Message::all();
                 
                return $msgs[count($msgs)-1]->id;
            }

        @endphp

        <input type="hidden" id="user_name" value="{{auth()->user()->username}}"/>
        <input type="hidden" id="seller" value="{{$conversation_id}}"/>

                <div class="row border g-0">
                    <div class="col-12 col-lg-5 col-xl-3">
                        <div id="chat-sidebar" class="d-none d-lg-block">
                        <div class="px-3 d-none d-md-block">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <input type="text" class="form-control my-3" placeholder="Search..." id="search-user">
                                </div>
                            </div>
                            
                            <div class="dropdown">
                                    <button class="btn border btn-lg px-3 dropdown-toggle text-success text-left" id="filter_text" data-bs-toggle="dropdown" aria-expanded="false">
                                        All Conversations
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end p-2">
                                        <li><button class="dropdown-item border-bottom p-2" onclick="showUser('all_conv')">All Conversations</button></li>
                                        <li><button class="dropdown-item p-2" type="button" onclick="showUser('archive_show')">Archived</button></li>
                                        <li><button class="dropdown-item p-2" type="button" onclick="showUser('delete_show')">Deleted</button></li>
                                        <li><button class="dropdown-item p-2" type="button" onclick="showUser('blocked_show')">Blocked</button></li>
                                    </ul>
                                </div>
                        </div>
                        <div class="chat-sidebar-scroll">
                        @foreach($side_info as $info)
                            <a href="{{route('create_chat_room', ['conversation_id'=>$info->username])}}" class="d-none d-md-block list-group-item list-group-item-action border-0 filterDiscussions all unread single {{$conversation_id== $info->user_id ?"active":""}}"
                               data-toggle="list" role="tab" data-id="{{$info->username}}">
                                <div class="badge bg-success float-right">
                                    <span>{{$info->cnt > 0 ? $info->cnt :  0}}</span>
                                </div>
                                <div class="d-flex align-items-start">
                                    <img
                                        src="{{optional(optional(users_name($info->user_id)->first())->uploads)->getImageOptimizedFullName(100,100)}}"
                                        data-toggle="tooltip" data-placement="top" title="Janette"
                                        alt="avatar"
                                        class="rounded mr-1 border" alt="Vanessa Tucker" width="40" height="40">
                                    <div class="flex-grow-1 ml-10px">
                                        {{optional(users_name($info->user_id)->first())->username}}
                                        @if (getOnlineStatus(optional(users_name($info->user_id)->first())->last_activity)["status"])
                                            <div class="small"><span class="fas fa-circle chat-online"></span> Online</div>
                                        @else
                                            <div class="small"><span class="fas fa-circle chat-offline"></span> Offline</div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                            <input type="hidden" name="client_id" id="client_id"
                                   value="{{$info->user_id}}"/>
                        @endforeach
                        </div>
                        <hr class="d-block d-lg-none mt-1 mb-0">
                        </div>
                    </div>

                    <div class="col-12 col-lg-7 col-xl-9">
                    <div id="chat-sidebar" class="chat-content border-left h-100">
                        <div class="py-2 px-4 border-bottom d-lg-block">
                            <div class="d-flex align-items-center py-1">
                                <a href="{{ route('chat.index') }}" class="d-link-side"><i class="bi bi-arrow-left"></i></a>
                                <div class="position-relative">
                                    <img
                                        src="{{optional(optional(users_name($conversation_id)->first())->uploads)->getImageOptimizedFullName(100,100)}}"
                                        data-toggle="tooltip" data-placement="top" title="Keith" alt="avatar"
                                        class="rounded mr-1 border" width="40" height="40">
                                </div>

                                <div class="flex-grow-1 px-2">
                                    <strong>{{optional(users_name($conversation_id)->first())->username}}</strong>
                                    @if (getOnlineStatus(optional(users_name($conversation_id)->first())->last_activity)["status"])
                                        <div class="small"><em> Online</em></div>
                                    @else
                                        <div class="small text-muted"><em>Last seen: {{ getOnlineStatus(optional(users_name($conversation_id)->first())->last_activity)["result"] }}</em></div>
                                    @endif
                                </div>
                                <div>
                                    <button class="btn btn-primary btn-lg mr-1 px-3 d-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round"
                                             class="feather feather-phone feather-lg">
                                            <path
                                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>
                                    </button>
                                    <button class="btn btn-info btn-lg mr-1 px-3 d-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round"
                                             class="feather feather-video feather-lg">
                                            <polygon points="23 7 16 12 23 17 23 7"></polygon>
                                            <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                                        </svg>
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-light border btn-lg px-3" data-bs-toggle="dropdown" aria-expanded="false">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-more-horizontal feather-lg">
                                                <circle cx="12" cy="12" r="1"></circle>
                                                <circle cx="19" cy="12" r="1"></circle>
                                                <circle cx="5" cy="12" r="1"></circle>
                                            </svg>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><button class="dropdown-item" type="button" id="archive">Archive</button></li>
                                            <li><button class="dropdown-item" type="button" id="block_user">Block User</button></li>
                                            <li><button class="dropdown-item" type="button" id="delete_chat">Delete</button></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="position-relative">
                            <div class="chat-messages p-3">

                                <div class="content" id="content">
                                    <div class="container" id="chat-container">
                                        <div class="col-md-12" id="chat-content">
                                            @foreach($chat_content as $content)
                                                @if($content->message != null)
                                                    @if(Auth::id() == $content->user_id)
                                                        <div class="chat-message-right pb-4">
                                                            <div class="ml-10px">
                                                                <img
                                                                    src="{{Auth::user()->uploads->getImageOptimizedFullName(100,100)}}"
                                                                    class="rounded mr-1 border" alt="Chris Wood"
                                                                    width="40" height="40">
                                                                <div
                                                                    class="text-muted small text-nowrap mt-2">{{date('g:i a',strtotime($content->created_at))}}</div>
                                                            </div>
                                                            <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3 msg-box">
                                                                <div class="fw-700 mb-1">
                                                                    Me 
                                                                    <i class="bi bi-chevron-down msg-action" msg-id="{{ $content->id }}"></i>
                                                                </div>
                                                                @if(getChatMessage($content->message)["file"])
                                                                    <div class="msg-displayed-content" msg-displayed-id="{{$content->id}}">
                                                                        @if ($content->is_message_deleted != 1)
                                                                            @if(getChatMessage($content->message)["file"]->type =="image")
                                                                                <img src="{{getChatMessage($content->message)["upload_file"]}}" width="200" height="auto" />
                                                                            @else
                                                                                <p  class="text-overflow-1"
                                                                                    title="{{getChatMessage($content->message)["file"]->file_original_name.".".getChatMessage($content->message)["file"]->extension}}">
                                                                                    {{getChatMessage($content->message)["file"]->file_original_name.".".getChatMessage($content->message)["file"]->extension}}
                                                                                </p>
                                                                            @endif
                                                                            <a   href="{{getChatMessage($content->message)["link_download"]}}" class="w-100 d-block"><i class="bi bi-download"></i></a>
                                                                        @else
                                                                            <em>You deleted this message.</em>
                                                                        @endif
                                                                    </div>
                                                                @elseif(getChatOffer($content->message)["content"] != null && isset(getChatOffer($content->message)["content"]->id))
                                                                    <div><i class="fs-15">Here's your Custom Offer</i></div>
                                                                    <div class="offer-container mt-2">
                                                                        <input type="hidden" value="{{ getChatOffer($content->message)["content"]->id }}" id="customPackageWithDrawId" />
                                                                        <div class="fw-500 fs-15 offer-title">
                                                                            {{ getChatOffer($content->message)["content"]->name }}
                                                                            <div class="text-primary">Price: ${{ number_format(getChatOffer($content->message)["content"]->price/100, 2) }}</div>
                                                                        </div>
                                                                        <div class="pl-10px pr-10px">
                                                                            <div class="offer-job">{{ getChatOffer($content->message)["content"]->description }}</div>
                                                                            <div class="offer-details">
                                                                                <div class="mb-1 fw-500 fs-15">Your offer includes</div>
                                                                                <div>
                                                                                    <span>{{ getChatOffer($content->message)["content"]->revisions }} Revision </span>
                                                                                    <span>{{ getChatOffer($content->message)["content"]->delivery_time }} 
                                                                                    {{ getChatOffer($content->message)["content"]->delivery_time > 1 ? ' days ' : ' day '}}
                                                                                        Delivery
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="offter-btn text-right">
                                                                                @if (getChatOffer($content->message)["content"]->status == 3)
                                                                                    <button
                                                                                        button-id="{{ getChatOffer($content->message)["content"]->id }}"
                                                                                        class="btn btn-secondary"
                                                                                        onclick="withdraw('{{ getChatOffer($content->message)["content"]->id }}')"
                                                                                        disabled
                                                                                        style="background-color: #c7cbcf"
                                                                                    >
                                                                                        Offer Withdrawn
                                                                                    </button>
                                                                                @elseif (getChatOffer($content->message)["content"]->status == 2)
                                                                                    <button
                                                                                        button-id="{{ getChatOffer($content->message)["content"]->id }}"
                                                                                        class="btn btn-secondary"
                                                                                        onclick="withdraw('{{ getChatOffer($content->message)["content"]->id }}')"
                                                                                        disabled
                                                                                        style="background-color: #c7cbcf"
                                                                                    >
                                                                                        Offer Not Accepted
                                                                                    </button>
                                                                                @elseif (getChatOffer($content->message)["content"]->status == 1)
                                                                                    <button
                                                                                        button-id="{{ getChatOffer($content->message)["content"]->id }}"
                                                                                        class="btn btn-success"
                                                                                        onclick="withdraw('{{ getChatOffer($content->message)["content"]->id }}')"
                                                                                        disabled
                                                                                    >
                                                                                        Accepted
                                                                                    </button>
                                                                                @elseif(getChatOffer($content->message)["content"]->status == 0)
                                                                                    <button
                                                                                        button-id="{{ getChatOffer($content->message)["content"]->id }}"
                                                                                        class="btn btn-secondary"
                                                                                        onclick="withdraw('{{ getChatOffer($content->message)["content"]->id }}')"
                                                                                    >
                                                                                        Withdraw Offer
                                                                                    </button>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                <div class="msg-displayed-content" msg-displayed-id="{{$content->id}}">
                                                                    @if ($content->is_message_deleted != 1) {{$content->message}}
                                                                    @else <em>You deleted this message.</em>
                                                                    @endif
                                                                </div>
                                                                @endif
                                                            </div>
                                                            <div class="msg-action-group d-none" action-group-id="{{$content->id}}">
                                                                <button onclick="deleteMsg('{{$content->id}}')" class="btn btn-outline-secondary">Delete</button>
                                                            </div>
                                                        </div>
                                                    @else

                                                        <div class="chat-message-left pb-4">
                                                            <div class="mr-10px">
                                                                <img
                                                                    src="{{optional(optional(users_name($content->user_id)->first())->uploads)->getImageOptimizedFullName(100,100)}}"
                                                                    class="rounded mr-1 border" alt="Sharon Lessman"
                                                                    width="40" height="40">
                                                                <div
                                                                    class="text-muted small text-nowrap mt-2">{{date('g:i A',strtotime($content->created_at))}}</div>
                                                            </div>
                                                            <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
                                                                <div class="fw-700 mb-1">{{users_name($content->user_id)->first()->username}}</div>
                                                                @if(getChatMessage($content->message)["file"])
                                                                    <div class="msg-conversation-id" msg-conversation-id="{{$content->id}}">
                                                                        @if ($content->is_message_deleted != 1)
                                                                            @if(getChatMessage($content->message)["file"]->type =="image")
                                                                                <img src="{{getChatMessage($content->message)["upload_file"]}}" width="200" height="auto" />
                                                                            @else
                                                                                <p  class="text-overflow-1"
                                                                                    title="{{getChatMessage($content->message)["file"]->file_original_name.".".getChatMessage($content->message)["file"]->extension}}">
                                                                                    {{getChatMessage($content->message)["file"]->file_original_name.".".getChatMessage($content->message)["file"]->extension}}
                                                                                </p>
                                                                            @endif
                                                                            <a href="{{getChatMessage($content->message)["link_download"]}}" class="w-100 d-block"><i class="bi bi-download"></i></a>
                                                                        @else <em> This message has been removed by the user.</em>
                                                                        @endif
                                                                    </div>
                                                                @elseif(getChatOffer($content->message)["content"] != null && isset(getChatOffer($content->message)["content"]->id))
                                                                    <div><i class="fs-15">Here's your Custom Offer</i></div>
                                                                    <div class="offer-container mt-2">
                                                                        <input type="hidden" value="{{ getChatOffer($content->message)["content"]->id }}" id="customPackageWithDrawId" />
                                                                        <div class="fw-500 fs-15 offer-title">
                                                                            {{ getChatOffer($content->message)["content"]->name }} <br>
                                                                            <span class="text-primary">Price: ${{ number_format(getChatOffer($content->message)["content"]->price/100, 2) }}</span>
                                                                        </div>
                                                                        <div class="pl-10px pr-10px">
                                                                            <div class="offer-job">{{ getChatOffer($content->message)["content"]->description }}</div>
                                                                            <div class="offer-details">
                                                                                <div class="mb-1 fw-500 fs-15">Your offer includes</div>
                                                                                <div>
                                                                                    <span>{{ getChatOffer($content->message)["content"]->delivery_time }} 
                                                                                    {{ getChatOffer($content->message)["content"]->delivery_time > 1 ? ' days ' : ' day '}}
                                                                                        Delivery
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="offter-btn text-right">
                                                                                @if (getChatOffer($content->message)["content"]->status == 3)
                                                                                    <button
                                                                                        button-id="{{ getChatOffer($content->message)["content"]->id }}"
                                                                                        class="btn btn-secondary"
                                                                                        onclick="withdraw('{{ getChatOffer($content->message)["content"]->id }}')"
                                                                                        disabled
                                                                                        style="background-color: #c7cbcf"
                                                                                    >
                                                                                        Offer Withdrawn
                                                                                    </button>
                                                                                @elseif (getChatOffer($content->message)["content"]->status == 2)
                                                                                    <button
                                                                                        button-id="{{ getChatOffer($content->message)["content"]->id }}"
                                                                                        class="btn btn-secondary"
                                                                                        onclick="withdraw('{{ getChatOffer($content->message)["content"]->id }}')"
                                                                                        disabled
                                                                                        style="background-color: #c7cbcf"
                                                                                    >
                                                                                        Offer Not Accepted
                                                                                    </button>
                                                                                @elseif (getChatOffer($content->message)["content"]->status == 1)
                                                                                    <button
                                                                                        button-id="{{ getChatOffer($content->message)["content"]->id }}"
                                                                                        class="btn btn-success"
                                                                                        onclick="withdraw('{{ getChatOffer($content->message)["content"]->id }}')"
                                                                                        disabled
                                                                                    >
                                                                                        Accepted
                                                                                    </button>
                                                                                @elseif(getChatOffer($content->message)["content"]->status == 0)
                                                                                    <button
                                                                                        button-id="{{ getChatOffer($content->message)["content"]->id }}"
                                                                                        class="btn btn-secondary"
                                                                                        onclick="decline('{{ getChatOffer($content->message)["content"]->id }}')"
                                                                                    >
                                                                                        Decline
                                                                                    </button>
                                                                                    <a
                                                                                        href="{{ route('services.billing.get', ['id'=>getChatOffer($content->message)["content"]->id, 'custom'=>1]) }}"
                                                                                        button-id="{{ getChatOffer($content->message)["content"]->id }}"
                                                                                        class="btn btn-success"
                                                                                        
                                                                                    >
                                                                                        Accept
                                                                                    </a>
                                                                                @endif
                                                                                <button
                                                                                        hidden-decline-button-id="{{ getChatOffer($content->message)["content"]->id }}"
                                                                                        class="btn btn-secondary d-none"
                                                                                        onclick="withdraw('{{ getChatOffer($content->message)["content"]->id }}')"
                                                                                        disabled
                                                                                        style="background-color: #c7cbcf"
                                                                                    >
                                                                                        Offer Not Accepted
                                                                                </button>
                                                                                <button
                                                                                        hidden-accept-button-id="{{ getChatOffer($content->message)["content"]->id }}"
                                                                                        class="btn btn-success d-none"
                                                                                        onclick="withdraw('{{ getChatOffer($content->message)["content"]->id }}')"
                                                                                        disabled
                                                                                    >
                                                                                        Accepted
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    @if ($content->is_message_deleted != 1) <div class="msg-conversation-id" msg-conversation-id="{{$content->id}}">{{$content->message}}</div>
                                                                    @else <em> This message has been removed by the user.</em>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            @endforeach

                                            <div id="media-upload-previews">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="chat-message-left pb-4 ml-30px blocked-msg {{ $isBlocked ? '' : 'd-none' }}">You can no longer message this user.</div>
                        </div>

                        <!--begin::Form-->
                        <!--end::Form-->
                        <div class="flex-grow-0 p-3 border-top">
                            <div class="position-relative w-100">
                                <div class="input-group">
                                    <div class="dropzone dropzone-queue m-0 p-0" id="kt_dropzonejs_example_2">
                                        <!--begin::Controls-->
                                        <div class="dropzone-items wm-200px">
                                            <div class="dropzone-item" style="display:none">
                                                <!--begin::File-->
                                                <div class="dropzone-file">
                                                    <div class="dropzone-filename" title="some_image_file_name.jpg">
                                                        <span data-dz-name>some_image_file_name.jpg</span>
                                                        <strong>(<span data-dz-size>340kb</span>)</strong>
                                                    </div>

                                                    <div class="dropzone-error" data-dz-errormessage></div>
                                                </div>
                                                <!--end::File-->

                                                <!--begin::Progress-->
                                                <div class="dropzone-progress">
                                                    <div class="progress">
                                                        <div
                                                            class="progress-bar bg-primary"
                                                            role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-dz-uploadprogress>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--end::Progress-->

                                                <!--begin::Toolbar-->
                                                <div class="dropzone-toolbar">
                                                    <span class="dropzone-start"><i class="bi d-none bi-play-fill fs-3"></i></span>
                                                    <span class="dropzone-cancel" data-dz-remove style="display: none;"><i class="bi bi-x fs-3"></i></span>
                                                    <span class="dropzone-delete" data-dz-remove><i class="bi bi-x fs-1"></i></span>
                                                </div>
                                                <!--end::Toolbar-->
                                            </div>
                                        </div>
                                        <div class="row m-0">
                                            <input form="uploadFileForm" type="text" id="chat_input"
                                                   class="form-control col-8 col-sm mb-2 mb-sm-0"
                                                   autocomplete="off"
                                                   placeholder="Start typing for reply..." {{ $isBlocked ? 'disabled' : '' }}>
                                            <button class="btn btn-primary dropzone-upload mx-0 me-2 mx-sm-2 col-auto" id="chat_send_btn" {{ $isBlocked ? 'disabled' : '' }}>Send</button>

                                            <a class="dropzone-select btn btn-sm  btn-dark col-auto {{ $isBlocked ? 'disabled-upload' : '' }} py-2 px-4 d-flex justify-content-center align-items-center" id="chat_upload_btn"><i class="fa fa-link"
                                                                                                      aria-hidden="true"></i></a>
                                            <a class="dropzone-upload btn btn-sm btn-light-primary me-2 d-none">Upload All</a>
                                            <a class="dropzone-remove-all btn btn-sm btn-light-primary d-none">Remove All</a>

                                        </div>

                                        @if(Auth::user()->role == 2)
                                            <div class="mt-2">
                                                <a class="btn btn-primary" data-bs-toggle="modal" href="#exampleModalToggle" role="button">Create an Offer</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="modal fade modal-lg first-modal" id="exampleModalToggle" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalToggleLabel">Select a Service</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        @foreach ($services as $service)
                                        <div class="service-item mb-5px" data-id="{{ $service }}" data-img="{{ $service->uploads->getImageOptimizedFullName($service->thumbnail) }}" data-bs-target="#exampleModalToggle2" data-bs-toggle="modal" data-bs-dismiss="modal">
                                            <img src="{{ $service->uploads->getImageOptimizedFullName($service->thumbnail) }}" alt="" width="50">
                                            <span class="fw-500 ml-15px">{{ $service->name }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade modal-lg" id="exampleModalToggle2" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="staticBackdropLabel">Create a single-payment offer</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-500 service-content"></div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div style="width: 35%;">
                                                <img src="" alt="" class="w-100 service-thumb" style="height: 150px;">
                                            </div>
                                            <div style="width: 65%;" class="ml-15px selected-package-modal">
                                                <textarea name="" id="service-description" class="w-100" placeholder="Describe your offer"></textarea>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <div>Define the terms of your offer and what it includes.</div>
                                            <div class="mt-3 row">
                                                <div class="form-group col-4">
                                                    <label for="exampleInputEmail1">Revisions (optional)</label>
                                                    <select class="form-select service-revisions" id="exampleFormControlSelect1">
                                                        <option>Select</option>
                                                        @for ($i = 1; $i <= 20; $i ++)
                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="form-group col-4">
                                                    <label for="exampleInputEmail1">Delivery</label>
                                                    <select class="form-select service-delivery" id="exampleFormControlSelect1">
                                                        <option value="1">1 day</option>
                                                        @for ($i = 2; $i <= 30; $i ++)
                                                            <option value="{{ $i }}">{{ $i }} days</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="form-group col-4">
                                                    <label for="exampleInputEmail1">Price</label>
                                                    <input class="form-control input-price" placeholder="$20000 max" id="service-price" placeholder="1" type="number"/>
                                                    <div class="text-danger price-warning d-none">Hey! Put a price.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer" style="flex-direction: column;">
                                        <div class="d-flex justify-content-between align-items-center w-100">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                                <label class="form-check-label fw-500" for="flexCheckDefault">
                                                    Offer expires in
                                                </label>
                                            </div>
                                            <div class="form-group col-3">
                                                <select class="form-select service-expire" id="exampleFormControlSelect1" disabled>
                                                    <option value="1">1 day</option>
                                                    @for ($i = 2; $i <= 30; $i ++)
                                                        <option value="{{ $i }}">{{ $i }} days</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                        <div class="w-100">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="service-req" checked>
                                                <label class="form-check-label fw-500" for="service-req">
                                                    Request for requirement
                                                </label>
                                            </div>
                                            <div class="ml-5px req-warning d-none">
                                                <span class="fs-13">The order will start immediately upon payment.</span> <br>
                                                <span class="fs-13">Make sure you have all of the required information to start working.</span>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between w-100">
                                            <button class="btn btn-secondary" data-bs-target="#exampleModalToggle" data-bs-toggle="modal" data-bs-dismiss="modal">Back</button>
                                            <button type="button" class="btn btn-success" id="send-offer">Send Offer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    </div>
</section>


            @section('js')
                <script src="https://cdn.ably.com/lib/ably.min-1.js"></script>
                <script src="{{ asset('dropzone/js/dropzone.js') }}"></script>
                <script>
                    Dropzone.autoDiscover = false;

                    // set the dropzone container id
                    const id = "#kt_dropzonejs_example_2";
                    const dropzone = document.querySelector(id);

                    // set the preview element template
                    var previewNode = dropzone.querySelector(".dropzone-item");
                    previewNode.id = "";
                    var previewTemplate = previewNode.parentNode.innerHTML;
                    previewNode.parentNode.removeChild(previewNode);

                    var myDropzone = new Dropzone(id, { // Make the whole body a dropzone
                        method: 'post',
                        url: "{{ route('api_upload') }}",
                        dictDefaultMessage: "",
                        paramName: "file",
                        maxFiles: 13,
                        parallelUploads: 20,
                        maxFilesize: 256, // Max filesize'
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        },
                        previewTemplate: previewTemplate,
                        autoQueue: false, // Make sure the files aren't queued until manually added
                        previewsContainer: id + " .dropzone-items", // Define the container to display the previews
                        clickable: id + " .dropzone-select", // Define the element that should be used as click trigger to select files.
                    });

                    myDropzone.on("addedfile", function (file) {
                        // Hookup the start button
                        file.previewElement.querySelector(id + " .dropzone-start").onclick = function () { myDropzone.enqueueFile(file); };
                        const dropzoneItems = dropzone.querySelectorAll('.dropzone-item');
                        dropzoneItems.forEach(dropzoneItem => {
                            dropzoneItem.style.display = '';
                        });
                        dropzone.querySelector('.dropzone-upload').style.display = "inline-block";
                        dropzone.querySelector('.dropzone-remove-all').style.display = "inline-block";
                    });

                    // Update the total progress bar
                    myDropzone.on("totaluploadprogress", function (progress) {
                        const progressBars = dropzone.querySelectorAll('.progress-bar');
                        progressBars.forEach(progressBar => {
                            progressBar.style.width = progress + "%";
                        });
                    });

                    myDropzone.on("sending", function (file) {
                        // Show the total progress bar when upload starts
                        const progressBars = dropzone.querySelectorAll('.progress-bar');
                        progressBars.forEach(progressBar => {
                            progressBar.style.opacity = "1";
                        });
                        // And disable the start button
                        file.previewElement.querySelector(id + " .dropzone-start").setAttribute("disabled", "disabled");
                    });

                    // Hide the total progress bar when nothing's uploading anymore
                    myDropzone.on("complete", function (progress) {
                        const progressBars = dropzone.querySelectorAll('.dz-complete');

                        setTimeout(function () {
                            progressBars.forEach(progressBar => {
                                progressBar.querySelector('.progress-bar').style.opacity = "1";
                                progressBar.querySelector('.progress').style.opacity = "1";
                                progressBar.querySelector('.dropzone-start').style.opacity = "100";
                            });
                        }, 300);
                    });

                    // Setup the buttons for all transfers
                    dropzone.querySelector(".dropzone-upload").addEventListener('click', function () {
                        myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED));
                    });

                    // Setup the button for remove all files
                    dropzone.querySelector(".dropzone-remove-all").addEventListener('click', function () {
                        dropzone.querySelector('.dropzone-upload').style.display = "block";
                        dropzone.querySelector('.dropzone-remove-all').style.display = "none";
                        myDropzone.removeAllFiles(true);
                    });

                    // On all files completed upload
                    myDropzone.on("queuecomplete", function (progress) {
                        const uploadIcons = dropzone.querySelectorAll('.dropzone-upload');
                        uploadIcons.forEach(uploadIcon => {
                            uploadIcon.style.display = "block";
                        });
                    });

                    // On all files removed
                    myDropzone.on("removedfile", function (file) {
                        if (myDropzone.files.length < 1) {
                            dropzone.querySelector('.dropzone-upload').style.display = "block";
                            dropzone.querySelector('.dropzone-remove-all').style.display = "none";
                        }
                    });


                    myDropzone.on("success", async function (file, responseText) {
                        let message = getMsgBy(`upload_ids:${responseText.id}`);
                        let res = await sendMessage(message)
                        if(res.result){
                            renderMessageAfterUploadFile(res)
                        }
                    })


                    function renderMessageAfterUploadFile(res)
                    {
                        if(res.upload_file)
                        {
                            let user = res.user;
                            let file = res.file;
                            let msg = ` <div class="chat-message-right pb-4">
                                    <div class="ml-10px">
                                        <img src="${userImageUrl}"
                                             class="rounded mr-1 border" alt="Chris Wood" width="40" height="40">
                                        <div class="text-muted small text-nowrap mt-2">${getDateFormat()}</div>
                                    </div>
                                    <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3 msg-box">
                                        <div class="fw-700 mb-1">
                                            Me                                        
                                            <i class="bi bi-chevron-down msg-action" msg-id="${msgId}"></i>
                                        </div>
                                        <div class="msg-displayed-content msg-conversation-id" msg-displayed-id="${msgId}" msg-conversation-id="${msgId}">
                                  `
                            if (file.type == "image")
                            {
                                msg+=`   <img src="${res.upload_file}" width="200" height="auto" />`;
                            } else{
                                msg+= ` <p  class="text-overflow-1"
                                        title="${file['file_original_name']}.${file['extension']}">
                                        ${file['file_original_name']}.${file['extension']}</p>
                                `
                            }
                            msg+=`
                                <a href="${res.link_download}"class="w-100 d-block"><i class="bi bi-download"></i></a>
                                </div></div>
                                <div class="msg-action-group d-none" action-group-id="${msgId}">
                                    <button onclick="deleteMsg('${msgId}')" class="btn btn-outline-secondary">Delete</button>
                                </div>
                                </div>`;
                                $('#chat-content').append(msg); // Append the new message received
                                $(".chat-messages").animate({scrollTop: $('.chat-messages').prop("scrollHeight")}, 10); // Scroll the chat output div

                        }
                    }

                </script>
                <script type="text/javascript">
                    var users = {!! json_encode($side_info) !!};
                    var msgId = parseInt('{{ getMsgId() }}');
                    const userImageUrl = @json(Auth::user()->uploads->getImageOptimizedFullName(100,100));

                    const ably = new Ably.Realtime.Promise("{{env('ABLY_KEY')}}");
                    var client_id = document.getElementById('seller').value;
                    // const ably = new Ably.Realtime.Promise('n-4_Uw.JXK4Fg:Q68j6Dp4ZoeVLbo--o3Mane1kNfcpVckpO-xp-CAGZ4');
                    let ablyConnected = false;
                    let channel;
                    ably.connection.once('connected').then(res => {
                        console.log('ably connected');
                        ablyConnected = true;
                        channel = ably.channels.get('chat-channel');
                        channel.subscribe('chat-{{auth()->id()}}', (msg) => {
                            handleReceivedMessage(msg);
                        })
                    })

                    $('document').ready(function () {

                        $(".chat-messages").animate({scrollTop: $('.chat-messages').prop("scrollHeight")}, 10); // Scroll the chat output div

                        $('.filterDiscussions').click(function () {

                            client_id = $(this).attr('data-id');
                            console.log(client_id);
                            $(location).attr('href', `{{env('APP_URL')}}/chat/${client_id}`);
 
                        })
                        
                    });

                    function formatAMPM(date) {
                        var hours = date.getHours();
                        var minutes = date.getMinutes();
                        var ampm = hours >= 12 ? 'pm' : 'am';
                        hours = hours % 12;
                        hours = hours ? hours : 12; // the hour '0' should be '12'
                        minutes = minutes < 10 ? '0' + minutes : minutes;
                        var strTime = hours + ':' + minutes + ' ' + ampm;
                        return strTime;
                    }

                    function getDateFormat() {
                        return formatAMPM(new Date);
                    }

                    function getMsgBy(message) {
                        return JSON.stringify({
                            'type': 'chat',
                            'user_id': '{{auth()->id()}}',
                            'user_name': '{{auth()->user()->username}}',
                            'chat_msg': message,
                            'conversation_id': client_id,
                            'id': msgId
                        })
                    }

                    $(document).on('click', '.dropzone-upload', function () {
                        senChatMessageWith($('#chat_input').val())
                    })
                    // Bind onkeyup event after connection
                    // Bind onkeydown event after connection
                    $('#chat_input').on('keydown', function (e) {
                        if (e.keyCode === 13 && !e.shiftKey) { 
                            e.preventDefault(); // Prevent the default behavior of the "Return" key (line break in text area)
                            let chat_msg = $(this).val();
                            senChatMessageWith(chat_msg);
                            dropzone.querySelector(".dropzone-upload").click();
                            $(this).val(''); // Clear the input field after submitting the message
                        }
                    });


                    function senChatMessageWith(message) {
                        msgId ++;
                        if(message)
                        {
                            let msg = getMsgBy(message);
                            sendMessage(msg);
                            let content = `
                                    <div class="chat-message-right pb-4">
                                    <div class="ml-10px">
                                        <img src="${userImageUrl}"
                                             class="rounded mr-1 border" alt="Chris Wood" width="40" height="40">
                                        <div class="text-muted small text-nowrap mt-2">${getDateFormat()}</div>
                                    </div>
                                    <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3 msg-box">
                                        <div class="fw-700 mb-1">
                                            Me
                                            <i class="bi bi-chevron-down msg-action" msg-id="${msgId}"></i>
                                        </div>
                                        <div class="msg-displayed-content" msg-displayed-id="${msgId}">                                        
                                            ${message}
                                        </div>
                                    </div>
                                    <div class="msg-action-group d-none" action-group-id="${msgId}">
                                        <button onclick="deleteMsg('${msgId}')" class="btn btn-outline-secondary">Delete</button>
                                    </div>
                                </div>
`;
                            $('#chat-content').append(content);
                            $('#chat_input').val('');
                            $(".chat-messages").animate({scrollTop: $('.chat-messages').prop("scrollHeight")}, 10); // Scroll the chat output div

                        }
                    }                    
                    
                    function withdraw(id) {
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('services.updateCustomPackage') }}",
                            data: {
                                _token: '{{ csrf_token() }}',
                                id: id,
                                status: 3,
                            },
                            dataType: "json",
                            success: function(res) {
                                if (res) {
                                    $('button').each(function(i, item) {
                                        if ($(item).attr('button-id') == id) {
                                            $(item).html('Offer Widthdrawn');
                                            $(item).attr('disabled', 'disabled');
                                            $(item).css('background-color', '#c7cbcf');
                                        }
                                    })
                                }
                            }
                        })
                    }

                    function decline(id) {
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('services.updateCustomPackage') }}",
                            data: {
                                _token: '{{ csrf_token() }}',
                                id: id,
                                status: 2,
                            },
                            dataType: "json",
                            success: function(res) {
                                if (res) {
                                    $('button').each(function(i, item) {
                                        if ($(item).attr('button-id') == id) {
                                            $(item).addClass('d-none');
                                        }
                                    })
                                    $('button').each(function(i, item) {
                                        if ($(item).attr('hidden-decline-button-id') == id) {
                                            $(item).removeClass('d-none');                                            
                                        }
                                    })
                                }
                            }
                        })
                    }

                    function accept(id) {
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('services.updateCustomPackage') }}",
                            data: {
                                _token: '{{ csrf_token() }}',
                                id: id,
                                status: 1,
                            },
                            dataType: "json",
                            success: function(res) {
                                if (res) {
                                    $('button').each(function(i, item) {
                                        if ($(item).attr('button-id') == id) {
                                            $(item).addClass('d-none');
                                        }
                                    })
                                    $('button').each(function(i, item) {
                                        if ($(item).attr('hidden-accept-button-id') == id) {
                                            $(item).removeClass('d-none');
                                        }
                                    })
                                }
                            }
                        })
                    }

                    function sendMessage(msg) {
                        if (channel) {
                            console.log(channel);
                            console.log('clientid: ', client_id);
                            channel.publish('chat-' + client_id, msg);
                            return $.ajax({
                                type: 'POST',
                                url: "{{ route('chat.message_log') }}",
                                data: {
                                    "data": JSON.parse(msg),
                                    "_token": '{{ csrf_token() }}'
                                },
                                dataType: "json",
                            }).then(res => {
                                return res
                            })
                                .catch((resp) => {
                                    var result = resp.responseJSON;
                                    if (result.errors && result.message) {
                                        alert(result.message);
                                        return;
                                    }
                                });
                        }
                    }

                    function getChatFileInformation(file_id, user_id, conversation_id) {
                        return $.ajax({
                            type: 'POST',
                            url: "{{ route('chat.file_information') }}",
                            data: {
                                file_id,
                                user_id,
                                conversation_id,
                                "_token": '{{ csrf_token() }}'
                            },
                            dataType: "json",
                        }).then(res => {
                            return res
                        })
                        .catch((resp) => {
                            var result = resp.responseJSON;
                            if (result.errors && result.message) {
                                alert(result.message);
                                return;
                            }
                        });
                    }

                    function getOfferInformation (package_id, user_id, conversation_id) {
                        return $.ajax({
                            type: 'POST',
                            url: "{{ route('chat.offer_information') }}",
                            data: {
                                package_id,
                                user_id,
                                conversation_id,
                                "_token": '{{ csrf_token() }}'
                            },
                            dataType: "json",
                        }).then(res => {
                            return res
                        })
                        .catch((resp) => {
                            var result = resp.responseJSON;
                            if (result.errors && result.message) {
                                alert(result.message);
                                return;
                            }
                        });
                    }

                    function getConversationInformation (user_id, conversation_id) {
                        return $.ajax({
                            type: 'POST',
                            url: "{{ route('chat.con_information') }}",
                            data: {
                                user_id,
                                conversation_id,
                                "_token": '{{ csrf_token() }}'
                            },
                            dataType: "json",
                        }).then(res => {
                            return res
                        })
                        .catch((resp) => {
                            var result = resp.responseJSON;
                            if (result.errors && result.message) {
                                alert(result.message);
                                return;
                            }
                        });
                    }

                    async function handleReceivedMessage(msg) {
                        const data = JSON.parse(msg.data);
                        let msgArr = data.chat_msg.split(':');
                        
                        if (data.conversation_id == {{auth()->id()}} && data.user_id == {{$conversation_id}}) {
                            let isFile = msgArr?.[0] == "upload_ids" && msgArr?.[1];
                            let isOffer = msgArr?.[0] == "service_packages_custom_id" && msgArr?.[1];
                            let isMsgDeleted = msgArr?.[0] == "msg_deleted_id" && msgArr?.[1];
                            let isBlocked = msgArr?.[0] == "blocked_id" && msgArr?.[1];

                            switch (data.type) {
                                case 'chat':
                                    let msg = "";
                                    // let conversation = chatFileInfo.conversation;
                                    
                                    if (isFile) {
                                        let chatFileInfo = await getChatFileInformation( msgArr?.[1], data.user_id, data.conversation_id);
                                        let user = chatFileInfo.user;

                                        msg = `<div class="chat-message-left pb-4">
                                                <div class="mr-10px">
                                                    <img src="${user.image_url}"
                                                        class="rounded mr-1 border" alt="Sharon Lessman" width="40" height="40">
                                                        <div class="text-muted small text-nowrap mt-2">${getDateFormat()}</div>
                                                </div>
                                                <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
                                                    <div class="fw-700 mb-1">${user.username}</div>
                                                    <div class="msg-conversation-id" msg-conversation-id="${data.id}">
                                        `;

                                        if (chatFileInfo.file)
                                        {
                                            let file = chatFileInfo.file;
                                            if (file.type == "image")
                                            {
                                                msg+=`   <img src="${chatFileInfo.path}" width="200" height="auto" />`;
                                            } else {
                                                msg+= ` <p  class="text-overflow-1"
                                                    title="${file['file_original_name']}.${file['extension']}">
                                                ${file['file_original_name']}.${file['extension']}</p>
                                                `
                                            }
                                            msg+=`    <a href="${chatFileInfo.link_download}" class="w-100 d-block"><i class="bi bi-download"></i></a></div>`;

                                        }
                                    } else if (isOffer) {
                                        let offerInfo = await getOfferInformation( msgArr?.[1], data.user_id, data.conversation_id);
                                        let user = offerInfo.user;
                                        let package = offerInfo.package;

                                        msg = `<div class="chat-message-left pb-4">
                                                <div class="mr-10px">
                                                    <img src="${user.image_url}"
                                                        class="rounded mr-1 border" alt="Sharon Lessman" width="40" height="40">
                                                        <div class="text-muted small text-nowrap mt-2">${getDateFormat()}</div>
                                                </div>
                                                <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
                                                    <div class="fw-700 mb-1">${user.username}</div>
                                        `;

                                        msg += `<div><i class="fs-15">Here's your Custom Offer</i></div>
                                                    <div class="offer-container mt-2">
                                                        <input type="hidden" value="${package.id}" id="customPackageWithDrawId" />
                                                        <div class="fw-500 fs-15 offer-title">
                                                            ${package.name}
                                                            <div class="text-primary">Price: $${(package.price / 100).toFixed(2)}</div>
                                                        </div>                                                        
                                                        <div class="pl-10px pr-10px">
                                                            <div class="offer-job">${package.description}</div>
                                                            <div class="offer-details">
                                                                <div class="mb-1 fw-500 fs-15">Your offer includes</div>
                                                                <div>
                                                                    <span>${package.revisions} Revision </span>
                                                                    <span>${package.delivery_time} 
                                                                        ${package.delivery_time > 1 ? ' days' : ' day'} 
                                                                        Delivery
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="offter-btn text-right">
                                                            <button
                                                                button-id="${package.id}"
                                                                class="btn btn-secondary"
                                                                onclick="decline('${package.id}')"
                                                            >
                                                                Decline
                                                            </button>
                                                            <a
                                                                button-id="${package.id}"
                                                                class="btn btn-success"
                                                                href="/services/checkout/custom/${package.id}/1"                                                                
                                                            >
                                                                Accept
                                                            </a>                                            
                                                        </div>
                                                    </div>
                                                </div>`
                                    } else if (isMsgDeleted) {
                                        $('.msg-conversation-id').each(function(i, item) {
                                            if ($(item).attr('msg-conversation-id') == data.id) {
                                                $(item).html('<em>This message has been removed by the user.</em>');
                                            }
                                        })
                                    } else if (isBlocked) {
                                        console.log('blocked me', data.conversation_id);
                                        $('.blocked-msg').removeClass('d-none');
                                        $('#chat_input').attr('disabled', 'disabled');
                                        $('#chat_send_btn').attr('disabled', 'disabled');
                                        $('#chat_upload_btn').addClass('disabled-upload');

                                    } else {
                                        let conInfo = await getConversationInformation(data.user_id, data.conversation_id);
                                        let user = conInfo.user;

                                        msg = `<div class="chat-message-left pb-4">
                                                <div class="mr-10px">
                                                    <img src="${user.image_url}"
                                                        class="rounded mr-1 border" alt="Sharon Lessman" width="40" height="40">
                                                        <div class="text-muted small text-nowrap mt-2">${getDateFormat()}</div>
                                                </div>
                                                <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
                                                    <div class="fw-700 mb-1">${user.username}</div>
                                        `;
                                        msg +=`<div class="msg-conversation-id" msg-conversation-id="${data.id}">${data.chat_msg}</div>`;
                                    }
                                    msg += `  </div>
                                        </div>`

                                    $('#chat-content').append(msg); // Append the new message received
                                    $(".chat-messages").animate({scrollTop: $('.chat-messages').prop("scrollHeight")}, 10); // Scroll the chat output div
                                    break;
                                case 'socket':
                                    $('#chat-content').append(data.msg);
                                    console.log("Received " + data.msg);
                                    break;
                            }
                        }
                        if (data.conversation_id == {{auth()->id()}} && data.user_id != {{$conversation_id}}) {
                            checkUserList(data.user_name, data.user_id);
                        }
                    }

                    function checkUserList(user_name, conversation_id) {
                        var flag = false;

                        $('.list-group-item').each(function(i, item) {
                            flag = $(item).attr('data-id') == user_name ? true : false;
                        })

                        if (!flag) {
                            $.ajax({
                                type: 'POST',
                                url: "{{ route('chat.check_status') }}",
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    conversation_id: conversation_id
                                },
                                dataType: "json",
                                success: function(res) {
                                    if (res) {
                                        var img_url = res.avatar_url;
                                        var list = `
                                                <a href="{{ url('chat/${user_name}')}}" class="list-group-item list-group-item-action border-0 filter Discussions all unread single"
                                                data-toggle="list" role="tab" data-id="${user_name}">
                                                    <div class="badge bg-success float-right">
                                                        <span>1</span>
                                                    </div>
                                                    <div class="d-flex align-items-start">
                                                        <img
                                                            src="${img_url}"
                                                            data-toggle="tooltip" data-placement="top" title="Janette"
                                                            alt="avatar"
                                                            class="rounded mr-1 border" alt="Vanessa Tucker" width="40" height="40">
                                                        <div class="flex-grow-1 ml-10px">
                                                            ${user_name}
                                                            @if (getOnlineStatus(optional(users_name('${conversation_id}')->first())->last_activity)["status"])
                                                                <div class="small"><span class="fas fa-circle chat-online"></span> Online</div>
                                                            @else
                                                                <div class="small"><span class="fas fa-circle chat-offline"></span> Offline</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </a>`;
                                        
                                        $('#chat-sidebar').append(list);
                                    }
                                }
                            })
                        }
                    }

                    $(document).on('click',  '.msg-action', function() {
                        var clickedMsgbox = $(this).attr('msg-id');
                        $('.msg-action-group').each(function(i, item) {
                            if(clickedMsgbox == $(item).attr('action-group-id')) {
                                // $(item).removeClass('d-none');
                                if ($(item).hasClass('d-none')) {
                                    $(item).removeClass('d-none');
                                } else {
                                    $(item).addClass('d-none');
                                }
                            } else {                                    
                                $(item).addClass('d-none');
                            }
                        })
                    });

                    function sendMessageBlockAction(msg) {
                        if (channel) {
                            channel.publish('chat-' + client_id, msg);
                            return $.ajax({
                                type: 'POST',
                                url: "{{ route('chat.block_user') }}",
                                data: {
                                    "data": JSON.parse(msg),
                                    "_token": '{{ csrf_token() }}'
                                },
                                dataType: "json",
                            }).then(res => {
                                return res
                            })
                            .catch((resp) => {
                                var result = resp.responseJSON;
                                if (result.errors && result.message) {
                                    alert(result.message);
                                    return;
                                }
                            });
                        }
                    }

                    function sendMessageDeleteAction(msg) {
                        if (channel) {
                            channel.publish('chat-' + client_id, msg);
                            return $.ajax({
                                type: 'POST',
                                url: "{{ route('chat.message_delete') }}",
                                data: {
                                    "data": JSON.parse(msg),
                                    "_token": '{{ csrf_token() }}'
                                },
                                dataType: "json",
                            }).then(res => {
                                return res
                            })
                            .catch((resp) => {
                                var result = resp.responseJSON;
                                if (result.errors && result.message) {
                                    alert(result.message);
                                    return;
                                }
                            });
                        }
                    }

                    async function deleteMsg (id) {
                        var msg = JSON.stringify({
                            'type': 'chat',
                            'user_id': '{{auth()->id()}}',
                            'user_name': '{{auth()->user()->username}}',
                            'chat_msg': 'msg_deleted_id:'+ id,
                            'id': id,
                            'conversation_id': client_id
                        });

                        let res = await sendMessageDeleteAction(msg);
                        if (res) {

                            $('.msg-displayed-content').each(function(i, item) {
                                if ($(item).attr('msg-displayed-id') == id) $(item).html('<em>You deleted this message.</em>');
                            });

                            $('.msg-action-group').each(function(i, item) {
                                $(item).addClass('d-none');
                            });
                        }
                    }

                    $('#archive').on('click', function() {
                        $.ajax({
                            url: '{{ route("chat.chat_archive") }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                conversation_id:  client_id,
                            },
                            dataType: 'JSON',
                            success: function(res) {
                                location.href = "{{ route('chat.index') }}";
                            }
                        })
                    })

                    $('#delete_chat').on('click', function() {
                        $.ajax({
                            url: '{{ route("chat.chat_delete") }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                conversation_id:  client_id,
                            },
                            dataType: 'JSON',
                            success: function(res) {
                                location.href = "{{ route('chat.index') }}";
                            }
                        })
                    })

                    $('#block_user').on('click', async function() {
                        var msg = JSON.stringify({
                            'type': 'chat',
                            'user_id': '{{auth()->id()}}',
                            'user_name': '{{auth()->user()->username}}',
                            'chat_msg': 'blocked_id:'+ client_id,
                            'conversation_id': client_id
                        });

                        let res = await sendMessageBlockAction(msg);
                        if (res) location.href = "{{ route('chat.index') }}";
                    })

                    $('#search-user').change(function() {
                        var input = $(this).val();
                        var result = users.filter(item => Object.keys(item).some(key => item['username'].includes(input)));
                        $('#chat-sidebar').html('');

                        result.forEach((item) => {
                            var list = `
                                    <a href="{{ url('chat/${item.username}')}}" class="list-group-item list-group-item-action border-0 filter Discussions all unread single"
                                    data-toggle="list" role="tab" data-id="${item.username}">
                                        <div class="badge bg-success float-right">
                                            <span>0</span>
                                        </div>
                                        <div class="d-flex align-items-start">
                                            <img
                                                src="${item.avatar_url}"
                                                data-toggle="tooltip" data-placement="top" title="Janette"
                                                alt="avatar"
                                                class="rounded mr-1 border" alt="Vanessa Tucker" width="40" height="40">
                                            <div class="flex-grow-1 ml-10px">
                                                ${item.username}
                                                @if (getOnlineStatus(optional(users_name('${item.user_id}')->first())->last_activity)["status"])
                                                    <div class="small"><span class="fas fa-circle chat-online"></span> Online</div>
                                                @else
                                                    <div class="small"><span class="fas fa-circle chat-offline"></span> Offline</div>
                                                @endif
                                            </div>
                                        </div>
                                    </a>`;

                            $('#chat-sidebar').append(list);
                        });        
                    })

                    function showUser(status) {
                        $('#chat-sidebar').html('');

                        if (status == 'all_conv') {
                            $('#filter_text').text('All Conversations');
                        } else if (status == 'archive_show') {
                            $('#filter_text').text('Archived');
                        } else if (status == 'delete_show') {
                            $('#filter_text').text('Deleted');
                        } else if (status == 'blocked_show') {
                            $('#filter_text').text('Blocked');
                        }
                        
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('chat.search_user') }}",
                            data: {
                                _token: '{{ csrf_token() }}',
                                status: status
                            },
                            dataType: "json",
                            success: function(res) {
                                res.forEach((item) => {
                                    var list = `
                                            <a href="{{ url('chat/${item.username}')}}" class="list-group-item list-group-item-action border-0 filter Discussions all unread single"
                                            data-toggle="list" role="tab" data-id="${item.username}">
                                                <div class="badge bg-success float-right">
                                                    <span>0</span>
                                                </div>
                                                <div class="d-flex align-items-start">
                                                    <img
                                                        src="${item.avatar_url}"
                                                        data-toggle="tooltip" data-placement="top" title="Janette"
                                                        alt="avatar"
                                                        class="rounded mr-1 border" alt="Vanessa Tucker" width="40" height="40">
                                                    <div class="flex-grow-1 ml-10px">
                                                        ${item.username}
                                                        @if (getOnlineStatus(optional(users_name('${item.user_id}')->first())->last_activity)["status"])
                                                            <div class="small"><span class="fas fa-circle chat-online"></span> Online</div>
                                                        @else
                                                            <div class="small"><span class="fas fa-circle chat-offline"></span> Offline</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>`;

                                    $('#chat-sidebar').append(list);
                                });
                            }
                        })
                    }
                </script>
                <script type="text/javascript">
                    $('document').ready(function () {
                        var service, offerId;

                        offerId = '{{ getChatOffer($content->message)["length"] }}'; 

                        $(document).on("click", ".first-modal .modal-body .service-item", function () {
                            service = $(this).data('id');
                            var serviceName = "<div>" + service.name + "</div>";                            
                            var thumbnail  = $(this).data('img');
                            $('.service-content').append(serviceName);
                            $('.service-thumb').attr('src', thumbnail);
                        });

                        $('#flexCheckDefault').change(function() {
                            this.checked ? $('.service-expire').removeAttr('disabled') : $('.service-expire').attr('disabled', true);
                        })

                        $('#service-req').change(function() {
                            this.checked ? $('.req-warning').addClass('d-none') : $('.req-warning').removeClass('d-none');
                        })

                        $(".input-price").change(function() {
                            $(this).val() != '' ? $(".price-warning").addClass('d-none') : $(".price-warning").removeClass('d-none');
                        })                        

                        $('#send-offer').click(function(e) {
                            if ($('#service-price').val() == '') {
                                $(".price-warning").removeClass('d-none');
                            } else {
                                offerId ++;
                                var expirationTime = $('#flexCheckDefault').is(':checked') ? $('.service-expire').val(): 0;
                                var requirements_status = $('#service-req').is(':checked') ? 0 : 1;
                                $.ajax({
                                    url: '{{ route("services.createCustomPackage") }}',
                                    method: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        status: 0,
                                        serviceId: service.id,
                                        userId: '{{users_name($conversation_id)->first()->id}}',
                                        name: service.name,
                                        description: $('#service-description').val(),
                                        price: $('#service-price').val(),
                                        revisions: $('.service-revisions').val(),
                                        deliveryTime: $('.service-delivery').val(),
                                        expirationTime: expirationTime,
                                        requirements_status: requirements_status
                                    },
                                    dataType: 'JSON',
                                    success: async function(currentCustomPackageId) {
                                        var msg = JSON.stringify({
                                            'type': 'chat',
                                            'user_id': '{{auth()->id()}}',
                                            'user_name': '{{auth()->user()->username}}',
                                            'chat_msg': 'service_packages_custom_id:'+ currentCustomPackageId,
                                            'conversation_id': $('#seller').val(),
                                        });

                                        let res = await sendMessage(msg);
                                        if (res.result) {                                        
                                            var strDelivery = $('.service-delivery').val() > 1 ? $('.service-delivery').val() + ' days' : $('.service-delivery').val() + ' day';
                                            var offer = `<div class="chat-message-right pb-4">
                                                <div class="ml-10px">
                                                    <img
                                                        src="${userImageUrl}"
                                                        class="rounded mr-1 border" alt="Chris Wood"
                                                        width="40" height="40">
                                                    <div
                                                        class="text-muted small text-nowrap mt-2">${getDateFormat()}</div>
                                                </div>
                                                <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
                                                    <div class="fw-700 mb-1">Me</div>
                                                    
                                                    <div><i class="fs-15">Here's your Custom Offer</i></div>
                                                    <div class="offer-container mt-2">
                                                        <input type="hidden" value=`+ service.id +` id="customPackageWithDrawId" />
                                                        <div class="fw-500 fs-15 offer-title">
                                                            ` + service.name + `
                                                            <div class="text-primary">Price: $${parseInt($('#service-price').val()).toFixed(2)}</div>
                                                        </div>
                                                        <div class="pl-10px pr-10px">
                                                            <div class="offer-job">`+ $('#service-description').val() + `</div>
                                                            <div class="offer-details">
                                                                <div class="mb-1 fw-500 fs-15">Your offer includes</div>
                                                                <div>
                                                                    <span>` + $('.service-revisions').val() + ` Revision </span>
                                                                    <span>` + strDelivery + ` Delivery</span>
                                                                </div>
                                                            </div>
                                                            <div class="offter-btn text-right">
                                                                <button button-id=`+ offerId +` class="btn btn-secondary" onclick="withdraw(`+ offerId +`)">Withdraw Offer</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>`
                                            
                                            $('#chat-content').append(offer);
                                            $(".chat-messages").animate({scrollTop: $('.chat-messages').prop("scrollHeight")}, 10);
                                        }

                                        $('#exampleModalToggle2').modal('hide');
                                    
                                    } 
                                });
                            }
                        });
                    });
                    
                    window.addEventListener('DOMContentLoaded', function() {
    var header = document.querySelector('header');
    var messagesSection = document.querySelector('.messages');

    function setMessagesSectionHeight() {
        var headerHeight = header.offsetHeight;
        var windowHeight = window.innerHeight;
        messagesSection.style.height = (windowHeight - headerHeight) + 'px';
    }

    setMessagesSectionHeight(); // Set initial height

    // Adjust height on window resize
    window.addEventListener('resize', function() {
        setMessagesSectionHeight();
    });
    });

    window.addEventListener('DOMContentLoaded', function() {
    var header = document.querySelector('header');
    var chatSidebar = document.querySelector('#chat-sidebar');

    function setChatSidebarHeight() {
        var headerHeight = header.offsetHeight;
        var windowHeight = window.innerHeight;
        var chatSidebarHeight = windowHeight - headerHeight - 40;
        chatSidebar.style.height = chatSidebarHeight + 'px';
    }

    setChatSidebarHeight(); // Set initial height

    // Adjust height on window resize
    window.addEventListener('resize', function() {
        setChatSidebarHeight();
    });
    });
                </script>
@endsection

</x-chat-layout>   

