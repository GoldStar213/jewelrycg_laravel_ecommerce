<x-chat-layout page-title="Messages | Jewelry CG">
@section('css')
    <link rel="stylesheet" href="//use.fontawesome.com/releases/v5.0.7/css/all.css">
    <style>
        

        .list-group-item {
            position: relative;
            display: block;
            padding: 0.75rem 1.25rem;
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, .125);
        }

        .chat-online {
            color: #34ce57
        }

        .chat-offline {
            color: #e4606d
        }

        .float-right {
            float: right !important;
        }

    </style>
@endsection
@section('content')
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
    @endphp
            <div class="row border g-0">
                <div class="col-12 col-lg-5 col-xl-3">
                    <div id="chat-sidebar">
                        <div class="px-3 d-none d-md-block">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <input type="text" class="form-control my-3" placeholder="Search..." id="search-user">
                                </div>
                            </div>
                            
                            <div class="dropdown">
                                    <button class="btn border btn-lg px-3 dropdown-toggle text-success" id="filter_text" data-bs-toggle="dropdown" aria-expanded="false">All Conversations</button>
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
                                <a href="{{route('create_chat_room', ['conversation_id'=>$info->username])}}" class="list-group-item list-group-item-action border-0 filter Discussions all unread single"
                                data-toggle="list" role="tab" data-id="{{$info->username}}">
                                    <div class="badge bg-success float-right">
                                        <span>{{$info->cnt > 0 ? $info->cnt :  0}}</span>
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <img
                                            src="{{ $info->avatar_url }}"
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
                    </div>
                    <hr class="d-block d-lg-none mt-1 mb-0">
                </div>
                <div class="col-12 col-lg-7 col-xl-9">
                    <div class="chat-content border-left h-100 d-none d-lg-block"></div>
                </div>
            </div>

    </div>
</section>
@section('js')
<script src="https://cdn.ably.com/lib/ably.min-1.js"></script>
<script type="text/javascript">
    var users = {!! json_encode($side_info) !!}
    const ably = new Ably.Realtime.Promise("{{env('ABLY_KEY')}}");
    let ablyConnected = false;
    let channel;
    ably.connection.once('connected').then(res => {
        ablyConnected = true;
        channel = ably.channels.get('chat-channel');
        channel.subscribe('chat-{{auth()->id()}}', (msg) => {
            handleReceivedMessage(msg);
        })
    })

    function handleReceivedMessage(msg) {
        const data = JSON.parse(msg.data);
        checkUserList(data.user_name, data.user_id);
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
