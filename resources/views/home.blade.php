@extends('template')
@section('title', 'SimplyCMS - by Alsocube')
@section('content')
<div class="flex lg:flex-row gap-12 w-full max-w-6xl items-start justify-center m-10">
    <!-- Left Panel -->
    <div class="hidden lg:block">
        <div class="flex flex-col items-center justify-center gap-5 ">
            <div class="create-post bg-orange-400 hover:bg-green-800 transition duration-300 shadow-lg md-dark" onclick="toggleCreatePost()">
                <span class="material-icons">create</span>
            </div>
            <div class="menu shadow-lg" id="mainMenu" onclick="toggleMenu()">
                <span class="material-icons">menu</span>
            </div>
        </div>
    </div>

    <!-- Middle Panel / Content and much more -->
    <div class="flex flex-col">
        <div class="content flex flex-col flex-1 overflow-hidden" id="contents">
            @if ($errors->any())
                <div id="alert" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-sm relative mb-4 max-w-full cursor-pointer" onclick="this.remove()">
                    <strong class="font-bold">Error!</strong>
                    <ul class="mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('success'))
                <div id="alert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-sm relative mb-4 cursor-pointer" onclick="this.remove()">
                    <strong class="font-bold">Success!</strong>
                    @if (Auth::check())
                        <span class="block">{{ session('success') }} {{ Auth::user()->username }}</span>
                    @endif
                </div>
            @endif
            <!-- grid posts -->
            <div class="overflow-y-auto hide-scrollbar rounded-3xl justify-center items-center">
                @if (!Auth::check() || Auth::user()->access != "admin")
                <div class="columns-1 sm:columns-1 lg:columns-2 gap-5 pr-5 pl-5 space-y-5">
                    @foreach ($posts as $post)
                        <div class="break-inside-void rounded-3xl overflow-hidden shadow-lg flex flex-col bg-[#3d3d3d] hover:scale-105 transition ease-in-out duration-300 cursor-pointer">
                            <!-- Top Section with Full Image -->
                            <div class="card-top relative bg-[#8c8c8c]">
                                <!-- Image set to full width/height and object-cover -->
                                <img src="{{ $post->file_path }}" class="w-full h-full object-cover" alt="Post Image" onclick="viewPost({{ $post->post_id }})">
                                <!-- Delete Button positioned bottom-right -->
                                @if (Auth::check() && Auth::user()->user_id == $post->user_id)
                                <div class="delete-container absolute bottom-2 right-2 md-dark" onclick="deletePost({{ $post->post_id }})">
                                    <button class="cursor-pointer delete-button">
                                        <span class="material-icons">delete</span>
                                    </button>
                                </div>
                                @endif
                            </div>

                            <!-- Bottom Section for Title -->
                            <div class="card-bottom flex justify-center items-center h-20 px-4" onclick="viewPost({{ $post->post_id }})">
                                <h3 class="text-xl font-bold text-gray-200 truncate">{{ $post->title }}</h3>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-center">
                    <p class="text-gray-400 text-xl italic mt-5">end of posts</p>
                </div>
                @elseif (Auth::check() && Auth::user()->access == "admin")
                <div class="columns-1 sm:columns-1 lg:columns-2 gap-5 pr-5 pl-5 space-y-5">
                    @foreach ($posts as $post)
                        <div class="break-inside-void rounded-3xl overflow-hidden shadow-lg flex flex-col bg-[#3d3d3d] hover:scale-105 transition ease-in-out duration-300 cursor-pointer">
                            <!-- Top Section with Full Image -->
                            <div class="card-top relative bg-[#8c8c8c]">
                                <!-- Image set to full width/height and object-cover -->
                                <img src="{{ $post->file_path }}" class="w-full h-full object-cover" alt="Post Image" onclick="viewPost({{ $post->post_id }})">

                                <!-- Delete Button positioned bottom-right -->
                                <div class="delete-container absolute bottom-2 right-2 md-dark" onclick="deletePost({{ $post->post_id }})">
                                    <button class="cursor-pointer delete-button">
                                        <span class="material-icons">delete</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Bottom Section for Title -->
                            <div class="card-bottom flex justify-center items-center h-20 px-4">
                                <h3 class="text-xl font-bold text-gray-200 truncate">{{ $post->title }}</h3>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-center">
                    <p class="text-gray-400 text-xl italic mt-5">end of posts</p>
                </div>
                @endif
            </div>
        </div>
        <!-- extrapanel -->
        <div class="hidden flex-1" id="extraPanel">
            <!-- full post view panel -->
            <div class="hidden" id="fullPostView">
                <div id="postLoading" class="rounded-3xl shadow-2xl p-8 flex flex-col justify-center d9d9d9 w-[100%] mx-auto">
                    <div class="bg-orange-400 hover:bg-green-800 transition duration-300 rounded-full h-[35px] flex items-center justify-center p-2 cursor-pointer shadow-lg md-dark z-10" onclick="closePostView()">
                        <div class="flex flex-row items-center justify-center">
                            <span class="material-icons">arrow_back</span>
                            <span>Close</span>
                        </div>
                    </div>
                    <div id="postLoading" class="text-center py-10 text-gray-600">Loading...</div>
                </div>
                <div id="postContentArea" class="hidden max-w-[666px] mx-auto flex flex-col justify-center items-center rounded-3xl shadow-2xl overflow-hidden bg-[#8c8c8c] relative">
                    <div class="absolute top-4 left-4 bg-orange-400 hover:bg-green-800 transition duration-300 rounded-full h-[35px] flex items-center justify-center p-2 cursor-pointer shadow-lg md-dark z-10" onclick="closePostView()">
                        <div class="flex flex-row items-center justify-center">
                            <span class="material-icons">arrow_back</span>
                            <span>Close</span>
                        </div>
                    </div>
                    <div class="rounded-tl-3xl rounded-tr-3xl p-5 mt-10">
                        <img src="" id="postFile" class="outline-10 outline-[#8c8c8c] outline-offset-2 outline-solid max-h-[500px]">
                    </div>
                    <div class="flex flex-row items-baseline justify-between gap-3 bg-[#8c8c8c] w-[100%] p-5 pt-0">
                        <h3 id="viewPostTitle" class="text-2xl font-bold text-gray-800"></h3>
                        <span id="postAuthor" class="text-gray-200 italic ml-auto"></span>
                    </div>
                    <div id="viewPostDetails" class="text-gray-200 bg-[#3d3d3d] w-[100%] p-5"></div>
                </div>
            </div>
            <!-- create post panel -->
            <div class="hidden" id="createPost">
                <div class="rounded-3xl shadow-2xl d9d9d9 p-8 flex flex-col justify-center p-5">
                    <h3 class="text-2xl font-bold text-gray-800">Create New Post</h3>
                    <form id="postForm" method="POST" action="/create-post" enctype="multipart/form-data">
                        @csrf
                        @if (Auth::check())
                            <label for="">Display Name</label>
                            <input type="text" name="display_name" placeholder="Display Name" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 mb-4" value="{{ Auth::user()->username }}">
                        @else
                            <label for="">Display Name (Sign in for automatically set)</label>
                            <input type="text" name="display_name" placeholder="Display Name" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 mb-4">
                        @endif
                        <label for="">Title</label>
                        <input type="text" name="post_title" placeholder="Title" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 mb-4">
                        <label for="">Content</label>
                        <textarea name="post_contents" placeholder="Content" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 mb-4 h-40"></textarea>
                        <label for="">Attach Image, Max 4.5MB</label>
                        <input id="post_file" type="file" name="post_file" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400 mb-4">
                        <input type="submit" value="Create Post" class="w-full p-2 rounded-full bg-orange-400 text-gray-200 font-bold cursor-pointer hover:bg-green-800 transition duration-300">
                    </form>
                </div>
            </div>
            <!-- alt profile panel -->
            <div class="hidden" id="altProfilePanel">
                <div class="rounded-3xl shadow-2xl d9d9d9 p-8 flex flex-col justify-center p-5">
                    <h3 class="text-2xl font-bold text-gray-800">Profile</h3>
                    @if (Auth::check())
                        <div class="mt-4">
                            <p class="text-gray-600">Username: {{ Auth::user()->username }}</p>
                            <p class="text-gray-600">Email: {{ Auth::user()->email }}</p>
                            <h3 class="text-2xl font-bold text-gray-800 mb-5">Welcome, {{ Auth::user()->username }}!</h3>
                            <button class="cursor-pointer p-2 bg-red-600 rounded-lg text-gray-200 font-bold hover:bg-orange-400 transition duration-300" onclick="logout()">Logout</button>
                        </div>
                    @else
                        <p class="text-gray-600 mt-4">You are not logged in.</p>
                    @endif
                </div>
                @if (!Auth::check())
                <div id="altLoginForm" class="rounded-3xl shadow-2xl d9d9d9 p-8 flex flex-col justify-center p-5 mt-5 hidden">
                    <h3 class="text-2xl font-bold text-gray-800 mb-5">Sign In</h3>
                    <form action="{{ url('/login') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="text" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="username" name="username">
                        <input type="password" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="passsword" name="password">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" value="1" name="remember-me" class="mr-2">
                            <span class="text-gray-800">Remember Me?</span>
                        </label>
                        <input type="submit" value="Sign In" class="w-full p-2 rounded-full bg-orange-400 text-gray-200 font-bold cursor-pointer hover:bg-green-800 transition duration-300">
                    </form>
                    <span class="hover:text-orange-400 text-green-800 transition duration-300 pointer" onclick="toggleAltForms()">Sign Up</span>
                </div>
                <div id="altRegisterForm" class="rounded-3xl shadow-2xl d9d9d9 p-8 flex flex-col justify-center p-5 mt-5">
                    <h3 class="text-2xl font-bold text-gray-800 mb-5">Sign Up</h3>
                    <form action="{{ url('/register') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="email" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="email" name="email">
                        <input type="text" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="username" name="username">
                        <input type="password" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="passsword" name="password">
                        <input type="password" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="retype passsword" name="confirm_password">
                        <input type="submit" value="Sign Up" class="w-full p-2 rounded-full bg-orange-400 text-gray-200 font-bold cursor-pointer hover:bg-green-800 transition duration-300">
                    </form>
                    <span class="hover:text-orange-400 text-green-800 transition duration-300 pointer" onclick="toggleAltForms()">Sign In</span>
                </div>
                @else
                <div class="rounded-3xl shadow-2xl d9d9d9 p-8 flex flex-col justify-center p-5 mt-5">
                    <h3 class="text-2xl font-bold text-gray-800 mb-5">Edit Profile</h3>
                    <form action="#" class="space-y-4">
                        @csrf
                        <label for="">Email</label>
                        <input type="email" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="email" name="email" value="{{ Auth::user()->email }}">
                        <label for="">Username</label>
                        <input type="text" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="username" name="username" value="{{ Auth::user()->username }}">
                        <label for="">Password</label>
                        <input type="password" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="new passsword" name="password">
                        <input type="password" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="retype new passsword" name="confirm_password">
                        <input type="submit" value="Edit Profile" class="w-full p-2 rounded-full bg-orange-400 text-gray-200 font-bold cursor-pointer hover:bg-green-800 transition duration-300">
                    </form>
                </div>
                @endif
            </div>
        </div>
        <!-- mobile control -->
        <div class="fixed bottom-4 left-1/2 -translate-x-1/2 flex lg:hidden rounded-full bg-orange-400 hover:bg-green-800 transition duration-300 shadow-lg items-center justify-center w-[85px] h-[85px] cursor-pointer z-50 outline-solid outline-2 outline-orange-500 md-dark" onclick="toggleCreatePost()">
            <span class="material-icons">create</span>
        </div>
        <div class="fixed bottom-9 left-1/2 -translate-x-1/2 flex flex-col lg:hidden mt-2 cursor-pointer shadow-2xl w-[90%] mr-5" id="mobileControl">
            <div class="d9d9d9 rounded-full flex flex-row h-[45px] outline-solid outline-2 outline-green-800 justify-center items-center">
                <div class="flex w-full h-full rounded-s-full hover:bg-orange-400 transition duration-300 items-center justify-center" onclick="toggleAltProfilePanel()">
                    <div class="flex gap-2">
                        <span class="material-icons">person</span>
                        @if (Auth::check())
                            <span>{{ Auth::user()->username }}</span>
                        @else
                            <span>Guest</span>
                        @endif
                    </div>
                </div>
                <div class="w-[170px]"></div>
                <div class="flex w-full h-full rounded-e-full hover:bg-orange-400 transition duration-300 items-center justify-center" onclick="logout()">
                    <div class="flex gap-2">
                        @if (Auth::check())
                            <span class="material-icons">logout</span>
                            <span>Logout</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- right panel -->
    <div class="sidebar hidden lg:block" id="right-panel">
        <div class="flex flex-col">
            <div class="sidebar-panel rounded-3xl p-8 gap-5 flex flex-col justify-center items-center shadow-2xl">
                @if (Auth::check())
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-5">Welcome, {{ Auth::user()->username }}!</h3>
                    </div>
                @else
                    <div class="flex flex-col justify-center items-center" id="registrationForm">
                        <h3 class="text-2xl font-bold text-gray-800 mb-5">Sign Up</h3>
                        <form action="{{ url('/register') }}" class="space-y-4" method="POST">
                            @csrf
                            <input type="email" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="email" name="email">
                            <input type="text" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="username" name="username">
                            <input type="password" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="passsword" name="password">
                            <input type="password" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="retype passsword" name="password_confirmation">
                            <input type="submit" value="Sign Up" class="w-full p-2 rounded-full bg-orange-400 text-gray-200 font-bold cursor-pointer hover:bg-green-800 transition duration-300">
                        </form>
                        <span class="hover:text-orange-400 text-green-800 transition duration-300 pointer" onclick="toggleForms()">Sign In</span>
                    </div>
                    <div class="flex flex-col justify-center items-center hidden" id="loginForm">
                        <h3 class="text-2xl font-bold text-gray-800 mb-5">Sign In</h3>
                        <form action="{{ url('/login') }}" class="space-y-4" method="POST">
                            @csrf
                            <input type="text" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="username" name="username">
                            <input type="password" class="w-full p-2 rounded-sm bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="passsword" name="password">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" value="1" name="remember-me" class="mr-2">
                                <span class="text-gray-800">Remember Me?</span>
                            </label>
                            <input type="submit" value="Sign In" class="w-full p-2 rounded-full bg-orange-400 text-gray-200 font-bold cursor-pointer hover:bg-green-800 transition duration-300">
                        </form>
                        <span class="hover:text-orange-400 text-green-800 transition duration-300 pointer" onclick="toggleForms()">Sign Up</span>
                    </div>
                @endif
            </div>
        </div>
        <div class="flex flex-col mt-5">
            <div class="d9d9d9 rounded-3xl p-5 flex flex-col justify-center items-center shadow-2xl">
                <h3 class="text-xl font-bold text-gray-800">ToDo & ToBeDone</h3>
                <h3 class="text-xs font-bold text-orange-400">this web is jerryrigged by Alsocube</h3>
                <li class="list-none mt-5">
                    <script>
                        const toDO = ['ratings','komen mungkin idk', 'list postingan', 'video upload'];
                        for (i = 0; i < toDO.length; i++) {
                            document.write(`
                                <ol>${toDO[i]}</ol>
                            `)
                        }
                    </script>
                </li>
            </div>
        </div>
    </div>
</div>
<script>
const menu = document.getElementById('mainMenu');
let menuItems = [
    { icon: 'menu', script: 'toggleMenu(event)' },
    { icon: 'person', script: 'toggleAltProfilePanel()' },
    { icon: 'logout', script: 'logout()' }
];
function toggleMenu(event) {
    if (event) event.stopPropagation();
    menu.classList.toggle('open-menu');

    if (menu.classList.contains('open-menu')) {
        menu.innerHTML = menuItems.map(item => `
            <div class="menu-item" onclick="${item.script}">
                <span class="material-icons">${item.icon}</span>
            </div>`).join('');
    } else {
        menu.innerHTML = '<span class="material-icons" onclick="toggleMenu(event)">menu</span>';
    }
}
function closeMenu() {
    if (menu.classList.contains('open-menu')) {
        menu.classList.remove('open-menu');
        menu.innerHTML = '<span class="material-icons" onclick="toggleMenu(event)">menu</span>';
    }
}
function showPanel(panelId) {
    const content = document.getElementById('contents');
    const extraPanel = document.getElementById('extraPanel');
    const createPost = document.getElementById('createPost');
    const altProfilePanel = document.getElementById('altProfilePanel');
    content.classList.add('hidden');
    extraPanel.classList.remove('hidden'); 
    createPost.classList.add('hidden');
    altProfilePanel.classList.add('hidden');
    document.getElementById(panelId).classList.remove('hidden');
}
function viewPost(postId) {
    closeMenu();
    showPanel('fullPostView');
    
    event.preventDefault();
    const loading = document.getElementById('postLoading');
    const contentArea = document.getElementById('postContentArea');
    loading.classList.remove('hidden');
    contentArea.classList.add('hidden');

    fetch(`{{ url('/post') }}/${postId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        loading.classList.add('hidden');
        contentArea.classList.remove('hidden');
        document.getElementById('viewPostTitle').innerText = data.title;
        if (data.contents == null) {
            document.getElementById('viewPostDetails').innerHTML = `no detail`;
        }
        else {
            document.getElementById('viewPostDetails').innerHTML = data.contents;
        }
        document.getElementById('postAuthor').innerText = data.display_name;
        document.getElementById('postFile').src = data.file_path;
    })
    .catch(error => {
        console.error('Error:', error);
        loading.innerText = 'Failed to load post.';
    });
}
function closePostView() {
    const fullPostView = document.getElementById('fullPostView');
    document.getElementById('contents').classList.remove('hidden');
    document.getElementById('extraPanel').classList.add('hidden');
    fullPostView.classList.toggle('hidden');
}
function toggleCreatePost() {
    closeMenu();
    const createPost = document.getElementById('createPost');
    if (createPost.classList.contains('hidden')) {
        showPanel('createPost');
    } else {
        document.getElementById('contents').classList.remove('hidden');
        document.getElementById('extraPanel').classList.add('hidden');
        createPost.classList.add('hidden');
    }
}
const form = document.querySelector('#postForm');
const fileInput = document.querySelector('#post_file');

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    let finalFile = null;

    if (fileInput && fileInput.files.length > 0) {
        const file = fileInput.files[0];

        const options = {
            maxSizeMB: 1,
            maxWidthOrHeight: 1920,
            useWebWorker: true
        };

        try {
            finalFile = await imageCompression(file, options);
        } catch (err) {
            console.error("Compression error:", err);
        }
    }

    const formData = new FormData(form);

    if (finalFile) {
        formData.set('post_file', finalFile);
    }

    fetch('/create-post', {
        method: 'POST',
        body: formData
    }).then(() => window.location.reload());
});
function toggleAltProfilePanel() {
    closeMenu();
    const profile = document.getElementById('altProfilePanel');
    const rightPanel = document.getElementById('right-panel');
    if (profile.classList.contains('hidden')) {
        showPanel('altProfilePanel');
        rightPanel.classList.add('blur');
    } else {
        document.getElementById('contents').classList.remove('hidden');
        document.getElementById('extraPanel').classList.add('hidden');
        profile.classList.add('hidden');
        rightPanel.classList.remove('blur');
    }
}
function deletePost(postId) {
    if (!confirm('Are you sure you want to delete this post?')) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ url("/delete-post") }}';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';

    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id';
    idInput.value = postId;

    form.appendChild(csrfInput);
    form.appendChild(methodInput);
    form.appendChild(idInput);
    document.body.appendChild(form);
    form.submit();
}
function toggleForms() {
    const registrationForm = document.getElementById('registrationForm');
    const loginForm = document.getElementById('loginForm');
    if (registrationForm.classList.contains('hidden')) {
        registrationForm.classList.remove('hidden');
        loginForm.classList.add('hidden');
    } else {
        registrationForm.classList.add('hidden');
        loginForm.classList.remove('hidden');
    }
}
function toggleAltForms() {
    const altRegistrationForm = document.getElementById('altRegisterForm');
    const altLoginForm = document.getElementById('altLoginForm');
    if (altRegistrationForm.classList.contains('hidden')) {
        altRegistrationForm.classList.remove('hidden');
        altLoginForm.classList.add('hidden');
    } else {
        altRegistrationForm.classList.add('hidden');
        altLoginForm.classList.remove('hidden');
    }
}
function logout() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ url("/logout") }}';
    form.style.display = 'none';
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    document.body.appendChild(form);
    form.submit();
}
document.addEventListener('DOMContentLoaded', function() {
        const errorAlert = document.getElementById('alert');
        if (errorAlert) {
            setTimeout(() => errorAlert.remove(), 10000);
        }
});
</script>
@endsection